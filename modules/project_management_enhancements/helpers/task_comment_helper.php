<?php

function format_parent_task_comment($task, $comment, $children, $comments_attachments, $attachments_data)
{
	$comment_content = '';
	$comment_content .= '<div id="comment_'.$comment['id'].'" data-commentid="'.$comment['id'].'" data-task-attachment-id="'.$comment['file_id'].'" class="tc-content tw-group/comment task-comment task-parent-comment">';
	$comment_content .= '<a data-task-comment-href-id="'.$comment['id'].'" href="'.admin_url(
			'tasks/view/'.$task->id
		).'#comment_'.$comment['id'].'" class="task-date-as-comment-id"><span class="tw-text-sm">'.$comment['dateadded'].'</span></a>';

	if ($comment['staffid'] != 0)
	{
		$comment_content .= '<a href="'.admin_url(
				'profile/'.$comment['staffid']
			).'" target="_blank">'.staff_profile_image($comment['staffid'], [
				'staff-profile-image-small',
				'media-object img-circle pull-left mright10',
			]).'</a>';
	} elseif ($comment['contact_id'] != 0)
	{
		$comment_content .= '<img src="'.e(
				contact_profile_image_url($comment['contact_id'])
			).'" class="client-profile-image-small media-object img-circle pull-left mright10">';
	}
	if ($comment['staffid'] == get_staff_user_id() || is_admin())
	{
		$comment_added = strtotime($comment['dateadded']);
		$minus_1_hour = strtotime('-1 hours');
		if (get_option('client_staff_add_edit_delete_task_comments_first_hour') == 0 || (get_option(
					'client_staff_add_edit_delete_task_comments_first_hour'
				) == 1 && $comment_added >= $minus_1_hour) || is_admin())
		{
			$comment_content .= '<span class="pull-right tw-opacity-0 group-hover/comment:tw-opacity-100"><a href="#" onclick="remove_task_comment('.$comment['id'].'); return false;" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"><i class="fa fa-trash-can"></i></span></a>';
			$comment_content .= '<span class="pull-right tw-mx-2.5 tw-opacity-0 group-hover/comment:tw-opacity-100"><a href="#" onclick="edit_task_comment('.$comment['id'].'); return false;" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"><i class="fa-regular fa-pen-to-square"></i></span></a>';
		}
	}

	$copied_text = _l('comment_copied_successfully');
	$action = "navigator.clipboard.writeText(this.dataset.link);let originalText=this.innerHTML;this.innerHTML='$copied_text';setTimeout(() => { this.innerHTML = originalText}, 1000);";

	$comment_url = admin_url('tasks/view/'.$task->id).'#comment_'.$comment['id'];
	$copy_action = '<span class="pull-right tw-opacity-0 group-hover/comment:tw-opacity-100"><a href="#"  onclick="'.$action.'" data-link="'.$comment_url.'" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"><i class="fa-regular fa-copy"></i></span></a>';

	$comment_content .= $copy_action;

	$comment_content .= '<div class="comment-wrapper parent-comment">';
	$comment_content .= '<div class="mleft40">';

	if ($comment['staffid'] != 0)
	{
		$comment_content .= '<a href="'.admin_url('profile/'.$comment['staffid']).'" target="_blank">'.e(
				$comment['staff_full_name']
			).'</a> <br />';
	} elseif ($comment['contact_id'] != 0)
	{
		$comment_content .= '<span class="label label-info mtop5 mbot5 inline-block">'._l(
				'is_customer_indicator'
			).'</span><br /><a href="'.admin_url(
				'clients/client/'.get_user_id_by_contact_id(
					$comment['contact_id']
				).'?contactid='.$comment['contact_id']
			).'" class="pull-left" target="_blank">'.e(
				get_contact_full_name($comment['contact_id'])
			).'</a> <br />';
	}

	$comment_content .= '<div data-edit-comment="'.$comment['id'].'" class="hide edit-task-comment"><textarea rows="5" id="task_comment_'.$comment['id'].'" class="ays-ignore form-control">'.str_replace(
			'[task_attachment]',
			'',
			$comment['content']
		).'</textarea>
                  <div class="clearfix mtop20"></div>
                  <button type="button" class="btn btn-primary pull-right" onclick="save_edited_comment('.$comment['id'].','.$task->id.')">'._l(
			'submit'
		).'</button>
                  <button type="button" class="btn btn-default pull-right mright5" onclick="cancel_edit_comment('.$comment['id'].')">'._l(
			'cancel'
		).'</button>
                  </div>';
	if ($comment['file_id'] != 0)
	{
		$comment['content'] = str_replace(
			'[task_attachment]',
			'<div class="clearfix"></div>'.$attachments_data[$comment['file_id']],
			$comment['content']
		);
		// Replace lightbox to prevent loading the image twice
		$comment['content'] = str_replace(
			'data-lightbox="task-attachment"',
			'data-lightbox="task-attachment-comment-'.$comment['id'].'"',
			$comment['content']
		);
	} elseif (count($comment['attachments']) > 0 && isset($comments_attachments[$comment['id']]))
	{
		$comment_attachments_html = '';

		foreach ($comments_attachments[$comment['id']] as $comment_attachment)
		{
			$comment_attachments_html .= trim($comment_attachment);
		}
		$comment['content'] = str_replace(
			'[task_attachment]',
			'<div class="clearfix"></div>'.$comment_attachments_html,
			$comment['content']
		);
		// Replace lightbox to prevent loading the image twice
		$comment['content'] = str_replace(
			'data-lightbox="task-attachment"',
			'data-lightbox="task-comment-files-'.$comment['id'].'"',
			$comment['content']
		);
		$comment['content'] .= '<div class="clearfix"></div>';
		$comment['content'] .= '<div class="text-center download-all">
                   <hr class="hr-10" />
                   <a href="'.admin_url('tasks/download_files/'.$task->id.'/'.$comment['id']).'" class="bold">'._l(
				'download_all'
			).' (.zip)
                   </a>
                   </div>';
	}
	$comment_content .= '<div class="comment-content mtop10 mbot10">'.app_happy_text(
			check_for_links($comment['content'])
		).'</div>';
	$comment_content .= '</div>';

	if ( ! empty($children))
	{
		usort($children, function ($a, $b) {
			return strtotime($a['dateadded']) <=> strtotime($b['dateadded']);
		});
		$children_count = count($children);
		$see_reply = sprintf("%d %s", $children_count, $children_count === 1 ? "reply" : "replies");
		$comment_content .= '<div class="mleft20 mbot5 tw-px-4 tw-py-2">';
		$comment_content .= '<a href="javascript:void(0)" class="view-children-comment tw-rounded-full tw-px-4 tw-py-2 hover:tw-bg-neutral-300" onclick="slideToggle(\'#task_children_comments_' . $comment['id'] . '\'); this.querySelectorAll(\'i\').forEach(icon => {icon.classList.toggle(\'hide\');})"><i class="fa fa-chevron-up hide"></i><i class="fa fa-chevron-down"></i><span class="tw-ml-3 tw-font-medium">'.$see_reply.'</span></a>';
		$comment_content .= '</div>';
		$comment_content .= '<div class="hide children-comments-wrapper mtop5" id="task_children_comments_'.$comment['id'].'">';

		foreach ($children as $child)
		{
			$comment_content .= format_child_task_comment($task, $child);
		}
	}

	$comment_content .= '<div class="mleft30 mtop10">';
	$comment_content .= '<textarea name="reply_comment" data-comment-id="'.$comment['id'].'" placeholder="Reply in thread" id="task_reply_comment_'.$comment['id'].'" rows="2" class="form-control ays-ignore"></textarea>';

	$comment_content .= '<div data-reply-comment="'.$comment['id'].'" class="hide reply-task-comment">';
	$comment_content .= '<textarea rows="5" id="task_reply_comment_editor_'.$comment['id'].'" class="ays-ignore form-control"></textarea>';
	$comment_content .= '<div class="clearfix mtop20"></div>';
	$comment_content .= '<button type="button" class="btn btn-primary pull-right" data-loading-text="'._l('wait_text').'"  onclick="save_reply_comment('.$comment['id'].','.$task->id.')">'._l('task_single_add_new_comment').'</button>';
	$comment_content .= '<button type="button" class="btn btn-default pull-right mright5" onclick="cancel_reply_comment('.$comment['id'].')">'._l('cancel').'</button>';
	$comment_content .= '<div class="clearfix mbot20"></div>';
	$comment_content .= '</div>'; // class="mleft30 mtop10"
	$comment_content .= '</div>'; // class="hide children-comments-wrapper mtop5"
	$comment_content .= '</div>';
	$comment_content .= '</div>';

	if ( ! empty($children))
	{
		$comment_content .= '</div>';
	}
	return $comment_content;
}

function format_child_task_comment($task, $comment)
{
	$comment_content = '<div id="comment_'.$comment['id'].'" data-commentid="'.$comment['id'].'" data-task-attachment-id="'.$comment['file_id'].'" class="tc-content mleft30 task-child-comment">';
	$comment_content .= '<hr class="no-mtop mbot15"/>';
	$comment_content .= '<a data-task-comment-href-id="'.$comment['id'].'" href="'.admin_url(
			'tasks/view/'.$task->id
		).'#comment_'.$comment['id'].'" class="task-date-as-comment-id"><span class="tw-text-sm">'.$comment['dateadded'].'</span></a>';

	if ($comment['staffid'] != 0)
	{
		$comment_content .= '<a href="'.admin_url(
				'profile/'.$comment['staffid']
			).'" target="_blank">'.staff_profile_image($comment['staffid'], [
				'staff-profile-image-small',
				'media-object img-circle pull-left mright10',
			]).'</a>';
	} elseif ($comment['contact_id'] != 0)
	{
		$comment_content .= '<img src="'.e(
				contact_profile_image_url($comment['contact_id'])
			).'" class="client-profile-image-small media-object img-circle pull-left mright10">';
	}
	if ($comment['staffid'] == get_staff_user_id() || is_admin())
	{
		$comment_added = strtotime($comment['dateadded']);
		$minus_1_hour = strtotime('-1 hours');
		if (get_option('client_staff_add_edit_delete_task_comments_first_hour') == 0 || (get_option(
					'client_staff_add_edit_delete_task_comments_first_hour'
				) == 1 && $comment_added >= $minus_1_hour) || is_admin())
		{
			$comment_content .= '<span class="pull-right tw-opacity-0 group-hover/comment:tw-opacity-100"><a href="#" onclick="remove_task_comment('.$comment['id'].'); return false;" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"><i class="fa fa-trash-can"></i></span></a>';
			$comment_content .= '<span class="pull-right tw-mx-2.5 tw-opacity-0 group-hover/comment:tw-opacity-100"><a href="#" onclick="edit_task_comment('.$comment['id'].'); return false;" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"><i class="fa-regular fa-pen-to-square"></i></span></a>';
		}
	}

	$copied_text = _l('comment_copied_successfully');
	$action = "navigator.clipboard.writeText(this.dataset.link);let originalText=this.innerHTML;this.innerHTML='$copied_text';setTimeout(() => { this.innerHTML = originalText}, 1000);";

	$comment_url = admin_url('tasks/view/'.$task->id).'#comment_'.$comment['id'];
	$copy_action = '<span class="pull-right tw-opacity-0 group-hover/comment:tw-opacity-100"><a href="#"  onclick="'.$action.'" data-link="'.$comment_url.'" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"><i class="fa-regular fa-copy"></i></span></a>';

	$comment_content .= $copy_action;

	$comment_content .= '<div class="comment-wrapper">';
	$comment_content .= '<div class="mleft40">';

	if ($comment['staffid'] != 0)
	{
		$comment_content .= '<a class="tw-mt-1" href="'.admin_url('profile/'.$comment['staffid']).'" target="_blank">'.e(
				$comment['staff_full_name']
			).'</a> <br />';
	} elseif ($comment['contact_id'] != 0)
	{
		$comment_content .= '<span class="label label-info mtop5 mbot5 inline-block">'._l(
				'is_customer_indicator'
			).'</span><br /><a href="'.admin_url(
				'clients/client/'.get_user_id_by_contact_id(
					$comment['contact_id']
				).'?contactid='.$comment['contact_id']
			).'" class="pull-left" target="_blank">'.e(
				get_contact_full_name($comment['contact_id'])
			).'</a> <br />';
	}

	$comment_content .= '<div data-edit-comment="'.$comment['id'].'" class="hide edit-task-comment"><textarea rows="5" id="task_comment_'.$comment['id'].'" class="ays-ignore form-control">'.str_replace(
			'[task_attachment]',
			'',
			$comment['content']
		).'</textarea>
                  <div class="clearfix mtop20"></div>
                  <button type="button" class="btn btn-primary pull-right" onclick="save_edited_comment('.$comment['id'].','.$task->id.')">'._l(
			'submit'
		).'</button>
                  <button type="button" class="btn btn-default pull-right mright5" onclick="cancel_edit_comment('.$comment['id'].')">'._l(
			'cancel'
		).'</button>
                  <div class="clearfix mbot20"></div>
                  
                  </div>';

	$comment_content .= '<div class="comment-content mtop10">'.app_happy_text(
			check_for_links($comment['content'])
		).'</div>';
	$comment_content .= '</div>';
	$comment_content .= '</div>';
	$comment_content .= '</div>';

	return $comment_content;
}

/**
 * @param array $image
 * @param int $taskId
 *
 * @return bool|string
 */
function handle_task_comment_image_upload($image, $taskId): bool|string
{
	// Get the temp file path
	$tmpFilePath = $image['tmp_name'];

	// Make sure we have a filepath
	if ( ! empty($tmpFilePath) && $tmpFilePath != '')
	{
		if (_perfex_upload_error($image['error'])
			|| ! _upload_extension_allowed($image['name']))
		{
			return FALSE;
		}
		$path = TASKS_ATTACHMENTS_FOLDER.$taskId.'/';

		_maybe_create_upload_path($path);

		$filename = 'task_'.$taskId.'_upload_'.time().'_'.rand(0, 100000).'.png';
		$filename = unique_filename($path, $filename);
		$newFilePath = $path.$filename;

		if (move_uploaded_file($tmpFilePath, $newFilePath))
		{
			return site_url(
				'download/preview_image?path='.protected_file_url_by_path(
					$newFilePath,
					TRUE
				).'&type='.($image['filetype'] ?? $image['type'])
			);
		}
	}

	return FALSE;
}