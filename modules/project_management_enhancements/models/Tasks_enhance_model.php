<?php

use app\services\AbstractKanban;
use app\services\tasks\TasksKanban;

defined('BASEPATH') or exit('No direct script access allowed');

class Tasks_enhance_model extends Tasks_model {

	/**
	 * Get task comment
	 * @param mixed $id task id
	 * @return array
	 */
	public function get_task_comments($id)
	{
		$task_comments_order = hooks()->apply_filters('task_comments_order', 'DESC');

		$this->db->select(
			'id,dateadded,content,'.db_prefix().'staff.firstname,'.db_prefix().'staff.lastname,'.db_prefix().'task_comments.staffid,'.db_prefix().'task_comments.contact_id as contact_id, '.db_prefix(
			).'task_comments.parent_id, file_id,CONCAT(firstname, " ", lastname) as staff_full_name'
		);
		$this->db->from(db_prefix().'task_comments');
		$this->db->join(db_prefix().'staff', db_prefix().'staff.staffid = '.db_prefix().'task_comments.staffid', 'left');
		$this->db->where('taskid', $id);
		$this->db->order_by('dateadded', $task_comments_order);

		$comments = $this->db->get()->result_array();

		$ids = [];
		foreach ($comments as $key => $comment)
		{
			array_push($ids, $comment['id']);
			$comments[$key]['attachments'] = [];
		}

		if (count($ids) > 0)
		{
			$allAttachments = $this->get_task_attachments($id, 'task_comment_id IN ('.implode(',', $ids).')');
			foreach ($comments as $key => $comment)
			{
				foreach ($allAttachments as $attachment)
				{
					if ($comment['id'] == $attachment['task_comment_id'])
					{
						$comments[$key]['attachments'][] = $attachment;
					}
				}
			}
		}

		return $comments;
	}

	/**
	 * Add new task comment
	 * @param array $data comment $_POST data
	 * @return boolean
	 */
	public function add_task_comment($data)
	{
		if (is_client_logged_in())
		{
			$data['staffid'] = 0;
			$data['contact_id'] = get_contact_user_id();
		} else
		{
			$data['staffid'] = get_staff_user_id();
			$data['contact_id'] = 0;
		}

		$this->db->insert(db_prefix().'task_comments', [
			'taskid' => $data['taskid'],
			'content' => is_client_logged_in() ? _strip_tags($data['content']) : $data['content'],
			'staffid' => $data['staffid'],
			'contact_id' => $data['contact_id'],
			'dateadded' => date('Y-m-d H:i:s'),
			'parent_id' => $data['parent_id'] ?? NULL,
		]);

		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			$this->db->select('rel_type,rel_id,name,visible_to_client');
			$this->db->where('id', $data['taskid']);
			$task = $this->db->get(db_prefix().'tasks')->row();

			$description = 'not_task_new_comment';
			$additional_data = serialize([
				$task->name,
			]);

			if ($task->rel_type == 'project')
			{
				$this->projects_model->log_activity($task->rel_id, 'project_activity_new_task_comment', $task->name, $task->visible_to_client);
			}

			$regex = "/data\-mention\-id\=\"(\d+)\"/";
			if (preg_match_all($regex, $data['content'], $mentionedStaff, PREG_PATTERN_ORDER))
			{
				$this->_send_task_mentioned_users_notification(
					$description,
					$data['taskid'],
					$mentionedStaff[1],
					'task_new_comment_to_staff',
					$additional_data,
					$insert_id
				);
			} else
			{
				$this->_send_task_responsible_users_notification(
					$description,
					$data['taskid'],
					FALSE,
					'task_new_comment_to_staff',
					$additional_data,
					$insert_id
				);

				$this->db->where('project_id', $task->rel_id);
				$this->db->where('name', 'view_task_comments');
				$project_settings = $this->db->get(db_prefix().'project_settings')->row();

				if ($project_settings && $project_settings->value == 1)
				{
					$this->_send_customer_contacts_notification($data['taskid'], 'task_new_comment_to_customer');
				}
			}

			hooks()->do_action('task_comment_added', ['task_id' => $data['taskid'], 'comment_id' => $insert_id]);

			return $insert_id;
		}

		return FALSE;
	}

	/**
	 * Send notifications on new task comment
	 *
	 * @param string $description
	 * @param int $taskid
	 * @param array $staff
	 * @param string $email_template
	 * @param array $notification_data
	 * @param int $comment_id
	 *
	 * @return void
	 */
	private function _send_task_mentioned_users_notification($description, $taskid, $staff, $email_template, $notification_data, $comment_id)
	{
		$staff = array_unique($staff, SORT_NUMERIC);

		$this->load->model('staff_model');
		$notifiedUsers = [];

		foreach ($staff as $staffId)
		{
			if ( ! is_client_logged_in())
			{
				if ($staffId == get_staff_user_id())
				{
					continue;
				}
			}

			$member = $this->staff_model->get($staffId);

			$link = '#taskid='.$taskid;

			if ($comment_id)
			{
				$link .= '#comment_'.$comment_id;
			}

			$notified = add_notification([
				'description' => $description,
				'touserid' => $member->staffid,
				'link' => $link,
				'additional_data' => $notification_data,
			]);

			if ($notified)
			{
				array_push($notifiedUsers, $member->staffid);
			}

			if ($email_template != '')
			{
				send_mail_template($email_template, $member->email, $member->staffid, $taskid);
			}
		}

		pusher_trigger_notification($notifiedUsers);
	}

	/**
	 * Send notification on task activity to creator,follower/s,assignee/s
	 * @param string $description notification description
	 * @param mixed $taskid task id
	 * @param boolean $excludeid excluded staff id to not send the notifications
	 * @return boolean
	 */
	private function _send_task_responsible_users_notification(
		$description,
		$taskid,
		$excludeid = FALSE,
		$email_template = '',
		$additional_notification_data = '',
		$comment_id = FALSE
	) {
		$this->load->model('staff_model');

		$staff = $this->staff_model->get('', ['active' => 1]);

		$notifiedUsers = [];
		foreach ($staff as $member)
		{
			if (is_numeric($excludeid))
			{
				if ($excludeid == $member['staffid'])
				{
					continue;
				}
			}
			if ( ! is_client_logged_in())
			{
				if ($member['staffid'] == get_staff_user_id())
				{
					continue;
				}
			}

			if ($this->should_staff_receive_notification($member['staffid'], $taskid))
			{
				$link = '#taskid='.$taskid;

				if ($comment_id)
				{
					$link .= '#comment_'.$comment_id;
				}

				$notified = add_notification([
					'description' => $description,
					'touserid' => $member['staffid'],
					'link' => $link,
					'additional_data' => $additional_notification_data,
				]);

				if ($notified)
				{
					array_push($notifiedUsers, $member['staffid']);
				}

				if ($email_template != '')
				{
					send_mail_template($email_template, $member['email'], $member['staffid'], $taskid);
				}
			}
		}

		pusher_trigger_notification($notifiedUsers);
	}

	/**
	 * Check whether the given staff should receive notification for
	 * the given task
	 *
	 * @param int $staffid
	 * @param int $taskid [description]
	 *
	 * @return boolean
	 */
	private function should_staff_receive_notification($staffid, $taskid)
	{
		if ( ! $this->can_staff_access_task($staffid, $taskid))
		{
			return FALSE;
		}

		return hooks()->apply_filters(
			'should_staff_receive_task_notification',
			($this->is_task_assignee($staffid, $taskid)
				|| $this->is_task_follower($staffid, $taskid)
				|| $this->is_task_creator($staffid, $taskid)
				|| $this->staff_has_commented_on_task($staffid, $taskid)),
			['staff_id' => $staffid, 'task_id' => $taskid]
		);
	}

	/**
	 * Check and update project's owners to task
	 *
	 * @param $task_id
	 * @return void
	 */
	public function update_project_owners($task_id, $owners)
	{
		foreach ($owners as $owner)
		{
			$staff_id = $owner['staff_id'];

			$this->db->select('id');
			$this->db->where('staffid', $staff_id);
			$this->db->where('taskid', $task_id);

			$result = $this->db->get(db_prefix().'task_followers')->result_array();

			if ( ! empty($result))
			{
				continue;
			}

			$this->db->reset_query();
			$this->db->insert(db_prefix().'task_followers', [
				'staffid' => $staff_id,
				'taskid' => $task_id,
			]);
		}
	}
}
