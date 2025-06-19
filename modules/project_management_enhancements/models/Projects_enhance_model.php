<?php

use app\services\projects\Gantt;
use app\services\projects\AllProjectsGantt;
use app\services\projects\HoursOverviewChart;

defined('BASEPATH') or exit('No direct script access allowed');

class Projects_enhance_model extends Projects_model {
	public function add($data)
	{
		if (isset($data['notify_project_members_status_change']))
		{
			unset($data['notify_project_members_status_change']);
		}
		$send_created_email = FALSE;
		if (isset($data['send_created_email']))
		{
			unset($data['send_created_email']);
			$send_created_email = TRUE;
		}

		$send_project_marked_as_finished_email_to_contacts = FALSE;
		if (isset($data['project_marked_as_finished_email_to_contacts']))
		{
			unset($data['project_marked_as_finished_email_to_contacts']);
			$send_project_marked_as_finished_email_to_contacts = TRUE;
		}

		if (isset($data['settings']))
		{
			$project_settings = $data['settings'];
			unset($data['settings']);
		}
		if (isset($data['custom_fields']))
		{
			$custom_fields = $data['custom_fields'];
			unset($data['custom_fields']);
		}
		if (isset($data['progress_from_tasks']))
		{
			$data['progress_from_tasks'] = 1;
		} else
		{
			$data['progress_from_tasks'] = 0;
		}

		if (isset($data['contact_notification']))
		{
			if ($data['contact_notification'] == 2)
			{
				$data['notify_contacts'] = serialize($data['notify_contacts']);
			} else
			{
				$data['notify_contacts'] = serialize([]);
			}
		}

		$data['project_cost'] = ! empty($data['project_cost']) ? $data['project_cost'] : NULL;
		$data['estimated_hours'] = ! empty($data['estimated_hours']) ? $data['estimated_hours'] : NULL;

		$data['start_date'] = to_sql_date($data['start_date']);

		if ( ! empty($data['deadline']))
		{
			$data['deadline'] = to_sql_date($data['deadline']);
		} else
		{
			unset($data['deadline']);
		}

		$data['project_created'] = date('Y-m-d');
		if (isset($data['project_members']))
		{
			$project_members = $data['project_members'];
			unset($data['project_members']);
		}

		if (isset($data['project_owners']))
		{
			$project_owners = $data['project_owners'];
			unset($data['project_owners']);
		}

		if ($data['billing_type'] == 1)
		{
			$data['project_rate_per_hour'] = 0;
		} elseif ($data['billing_type'] == 2)
		{
			$data['project_cost'] = 0;
		} else
		{
			$data['project_rate_per_hour'] = 0;
			$data['project_cost'] = 0;
		}

		$data['addedfrom'] = get_staff_user_id();


		$items_to_convert = FALSE;
		if (isset($data['items']))
		{
			$items_to_convert = $data['items'];
			$estimate_id = $data['estimate_id'];
			$items_assignees = $data['items_assignee'];
			unset($data['items'], $data['estimate_id'], $data['items_assignee']);
		}

		$data = hooks()->apply_filters('before_add_project', $data);

		$tags = '';
		if (isset($data['tags']))
		{
			$tags = $data['tags'];
			unset($data['tags']);
		}

		$this->db->insert(db_prefix().'projects', $data);
		$insert_id = $this->db->insert_id();
		if ($insert_id)
		{
			handle_tags_save($tags, $insert_id, 'project');

			if (isset($custom_fields))
			{
				handle_custom_fields_post($insert_id, $custom_fields);
			}

			if (isset($project_members))
			{
				$_pm['project_members'] = array_unique(array_merge($project_owners ?? [], $project_members));
				$this->add_edit_members($_pm, $insert_id);
			}

			if (isset($project_owners))
			{
				$project_owners = array_unique($project_owners);
				$this->add_edit_owners($insert_id, $project_owners);
			}

			$original_settings = $this->get_settings();
			if (isset($project_settings))
			{
				$_settings = [];
				$_values = [];
				foreach ($project_settings as $name => $val)
				{
					array_push($_settings, $name);
					$_values[$name] = $val;
				}
				foreach ($original_settings as $setting)
				{
					if ($setting != 'available_features')
					{
						if (in_array($setting, $_settings))
						{
							$value_setting = 1;
						} else
						{
							$value_setting = 0;
						}
					} else
					{
						$tabs = get_project_tabs_admin();
						$tab_settings = [];
						foreach ($_values[$setting] as $tab)
						{
							$tab_settings[$tab] = 1;
						}
						foreach ($tabs as $tab)
						{
							if ( ! isset($tab['collapse']))
							{
								if ( ! in_array($tab['slug'], $_values[$setting]))
								{
									$tab_settings[$tab['slug']] = 0;
								}
							} else
							{
								foreach ($tab['children'] as $tab_dropdown)
								{
									if ( ! in_array($tab_dropdown['slug'], $_values[$setting]))
									{
										$tab_settings[$tab_dropdown['slug']] = 0;
									}
								}
							}
						}
						$value_setting = serialize($tab_settings);
					}
					$this->db->insert(db_prefix().'project_settings', [
						'project_id' => $insert_id,
						'name' => $setting,
						'value' => $value_setting,
					]);
				}
			} else
			{
				foreach ($original_settings as $setting)
				{
					$value_setting = 0;
					$this->db->insert(db_prefix().'project_settings', [
						'project_id' => $insert_id,
						'name' => $setting,
						'value' => $value_setting,
					]);
				}
			}

			if ($items_to_convert && is_numeric($estimate_id))
			{
				$this->convert_estimate_items_to_tasks($insert_id, $items_to_convert, $items_assignees, $data, $project_settings);

				$this->db->where('id', $estimate_id);
				$this->db->set('project_id', $insert_id);
				$this->db->update(db_prefix().'estimates');
			}

			$this->log_activity($insert_id, 'project_activity_created');

			if ($send_created_email == TRUE)
			{
				$this->send_project_customer_email($insert_id, 'project_created_to_customer');
			}

			if ($send_project_marked_as_finished_email_to_contacts == TRUE)
			{
				$this->send_project_customer_email($insert_id, 'project_marked_as_finished_to_customer');
			}

			hooks()->do_action('after_add_project', $insert_id);

			log_activity('New Project Created [ID: '.$insert_id.']');

			return $insert_id;
		}

		return FALSE;
	}

	public function add_edit_owners($id, $new_project_owners, $current_owners = NULL): void
	{
		$this->db->where('project_id', $id);
		$this->db->delete(db_prefix().'project_owners');

		foreach ($new_project_owners as $staff_id)
		{
			if (empty($staff_id))
			{
				continue;
			}
			$this->db->insert(db_prefix().'project_owners', [
				'project_id' => $id,
				'staff_id' => $staff_id,
			]);
		}
		$tasks = $this->get_tasks($id);

		if ($current_owners && ! empty(array_diff($current_owners, $new_project_owners)))
		{
			$should_remove_members = array_diff($current_owners, $new_project_owners);
			foreach ($should_remove_members as $member_id)
			{
				$this->remove_team_member($id, $member_id);
			}
		}

		hooks()->do_action('after_add_edit_project_owners', $this->get($id), $tasks, $new_project_owners, $current_owners);
	}

	public function update($data, $id)
	{
		$this->db->select('status');
		$this->db->where('id', $id);
		$old_status = $this->db->get(db_prefix().'projects')->row()->status;

		$send_created_email = FALSE;
		if (isset($data['send_created_email']))
		{
			unset($data['send_created_email']);
			$send_created_email = TRUE;
		}

		$send_project_marked_as_finished_email_to_contacts = FALSE;
		if (isset($data['project_marked_as_finished_email_to_contacts']))
		{
			unset($data['project_marked_as_finished_email_to_contacts']);
			$send_project_marked_as_finished_email_to_contacts = TRUE;
		}

		$original_project = $this->get($id);

		if (isset($data['notify_project_members_status_change']))
		{
			$notify_project_members_status_change = TRUE;
			unset($data['notify_project_members_status_change']);
		}
		$affectedRows = 0;
		if ( ! isset($data['settings']))
		{
			$this->db->where('project_id', $id);
			$this->db->update(db_prefix().'project_settings', [
				'value' => 0,
			]);
			if ($this->db->affected_rows() > 0)
			{
				$affectedRows++;
			}
		} else
		{
			$_settings = [];
			$_values = [];

			foreach ($data['settings'] as $name => $val)
			{
				array_push($_settings, $name);
				$_values[$name] = $val;
			}

			unset($data['settings']);
			$original_settings = $this->get_project_settings($id);

			foreach ($original_settings as $setting)
			{
				if ($setting['name'] != 'available_features')
				{
					if (in_array($setting['name'], $_settings))
					{
						$value_setting = 1;
					} else
					{
						$value_setting = 0;
					}
				} else
				{
					$tabs = get_project_tabs_admin();
					$tab_settings = [];
					foreach ($_values[$setting['name']] as $tab)
					{
						$tab_settings[$tab] = 1;
					}
					foreach ($tabs as $tab)
					{
						if ( ! isset($tab['collapse']))
						{
							if ( ! in_array($tab['slug'], $_values[$setting['name']]))
							{
								$tab_settings[$tab['slug']] = 0;
							}
						} else
						{
							foreach ($tab['children'] as $tab_dropdown)
							{
								if ( ! in_array($tab_dropdown['slug'], $_values[$setting['name']]))
								{
									$tab_settings[$tab_dropdown['slug']] = 0;
								}
							}
						}
					}
					$value_setting = serialize($tab_settings);
				}

				$this->db->where('project_id', $id);
				$this->db->where('name', $setting['name']);
				$this->db->update(db_prefix().'project_settings', [
					'value' => $value_setting,
				]);

				if ($this->db->affected_rows() > 0)
				{
					$affectedRows++;
				}
			}
		}

		$data['project_cost'] = ! empty($data['project_cost']) ? $data['project_cost'] : NULL;
		$data['estimated_hours'] = ! empty($data['estimated_hours']) ? $data['estimated_hours'] : NULL;

		if ($old_status == 4 && $data['status'] != 4)
		{
			$data['date_finished'] = NULL;
		} elseif (isset($data['date_finished']))
		{
			$data['date_finished'] = to_sql_date($data['date_finished'], TRUE);
		}

		if (isset($data['progress_from_tasks']))
		{
			$data['progress_from_tasks'] = 1;
		} else
		{
			$data['progress_from_tasks'] = 0;
		}

		if (isset($data['custom_fields']))
		{
			$custom_fields = $data['custom_fields'];
			if (handle_custom_fields_post($id, $custom_fields))
			{
				$affectedRows++;
			}
			unset($data['custom_fields']);
		}

		if ( ! empty($data['deadline']))
		{
			$data['deadline'] = to_sql_date($data['deadline']);
		} else
		{
			$data['deadline'] = NULL;
		}

		$data['start_date'] = to_sql_date($data['start_date']);
		if ($data['billing_type'] == 1)
		{
			$data['project_rate_per_hour'] = 0;
		} elseif ($data['billing_type'] == 2)
		{
			$data['project_cost'] = 0;
		} else
		{
			$data['project_rate_per_hour'] = 0;
			$data['project_cost'] = 0;
		}
		if (isset($data['project_members']))
		{
			$project_members = $data['project_members'];
			unset($data['project_members']);
		}

		if (isset($data['project_owners']))
		{
			$project_owners = $data['project_owners'];
			unset($data['project_owners']);
		}

		$_pm = [];
		$_pm['project_members'] = array_unique(array_merge($project_owners ?? [], $project_members ?? []));
		$current_owners = $this->get_project_owners($id);
		$current_owners = array_map(function ($member) {
			return $member['staff_id'];
		}, $current_owners);

		if ($this->add_edit_members($_pm, $id))
		{
			$affectedRows++;
		}

		if (isset($project_owners))
		{
			$project_owners = array_unique($project_owners);
			$this->add_edit_owners($id, $project_owners, $current_owners);
		}

		if (isset($data['mark_all_tasks_as_completed']))
		{
			$mark_all_tasks_as_completed = TRUE;
			unset($data['mark_all_tasks_as_completed']);
		}

		if (isset($data['tags']))
		{
			if (handle_tags_save($data['tags'], $id, 'project'))
			{
				$affectedRows++;
			}
			unset($data['tags']);
		}

		if (isset($data['cancel_recurring_tasks']))
		{
			unset($data['cancel_recurring_tasks']);
			$this->cancel_recurring_tasks($id);
		}

		if (isset($data['contact_notification']))
		{
			if ($data['contact_notification'] == 2)
			{
				$data['notify_contacts'] = serialize($data['notify_contacts']);
			} else
			{
				$data['notify_contacts'] = serialize([]);
			}
		}

		$data = hooks()->apply_filters('before_update_project', $data, $id);

		$this->db->where('id', $id);
		$this->db->update(db_prefix().'projects', $data);

		if ($this->db->affected_rows() > 0)
		{
			if (isset($mark_all_tasks_as_completed))
			{
				$this->_mark_all_project_tasks_as_completed($id);
			}
			$affectedRows++;
		}

		if ($send_created_email == TRUE)
		{
			if ($this->send_project_customer_email($id, 'project_created_to_customer'))
			{
				$affectedRows++;
			}
		}

		if ($send_project_marked_as_finished_email_to_contacts == TRUE)
		{
			if ($this->send_project_customer_email($id, 'project_marked_as_finished_to_customer'))
			{
				$affectedRows++;
			}
		}
		if ($affectedRows > 0)
		{
			$this->log_activity($id, 'project_activity_updated');
			log_activity('Project Updated [ID: '.$id.']');

			if ($original_project->status != $data['status'])
			{
				hooks()->do_action('project_status_changed', [
					'status' => $data['status'],
					'project_id' => $id,
				]);
				// Give space this log to be on top
				sleep(1);
				if ($data['status'] == 4)
				{
					$this->log_activity($id, 'project_marked_as_finished');
					$this->db->where('id', $id);
					$this->db->update(db_prefix().'projects', ['date_finished' => date('Y-m-d H:i:s')]);
				} else
				{
					$this->log_activity($id, 'project_status_updated', '<b><lang>project_status_'.$data['status'].'</lang></b>');
				}

				if (isset($notify_project_members_status_change))
				{
					$this->_notify_project_members_status_change($id, $original_project->status, $data['status']);
				}
			}
			hooks()->do_action('after_update_project', $id);

			return TRUE;
		}

		return FALSE;
	}

	public function get_project_owners($id, $with_name = FALSE)
	{
		if ($with_name)
		{
			$this->db->select('firstname,lastname,email,project_id,staff_id');
		} else
		{
			$this->db->select('email,project_id,staff_id');
		}
		$this->db->join(db_prefix().'staff', db_prefix().'staff.staffid='.db_prefix().'project_owners.staff_id');
		$this->db->where('project_id', $id);

		return $this->db->get(db_prefix().'project_owners')->result_array();
	}

	private function _mark_all_project_tasks_as_completed($id)
	{
		$this->db->where('rel_type', 'project');
		$this->db->where('rel_id', $id);
		$this->db->update(db_prefix().'tasks', [
			'status' => 5,
			'datefinished' => date('Y-m-d H:i:s'),
		]);
		$tasks = $this->get_tasks($id);
		foreach ($tasks as $task)
		{
			$this->db->where('task_id', $task['id']);
			$this->db->where('end_time IS NULL');
			$this->db->update(db_prefix().'taskstimers', [
				'end_time' => time(),
			]);
		}
		$this->log_activity($id, 'project_activity_marked_all_tasks_as_complete');
	}

	private function _notify_project_members_status_change($id, $old_status, $new_status)
	{
		$members = $this->get_project_members($id);
		$notifiedUsers = [];
		foreach ($members as $member)
		{
			if ($member['staff_id'] != get_staff_user_id())
			{
				$notified = add_notification([
					'fromuserid' => get_staff_user_id(),
					'description' => 'not_project_status_updated',
					'link' => 'projects/view/'.$id,
					'touserid' => $member['staff_id'],
					'additional_data' => serialize([
						'<lang>project_status_'.$old_status.'</lang>',
						'<lang>project_status_'.$new_status.'</lang>',
					]),
				]);
				if ($notified)
				{
					array_push($notifiedUsers, $member['staff_id']);
				}
			}
		}
		pusher_trigger_notification($notifiedUsers);
	}


}
