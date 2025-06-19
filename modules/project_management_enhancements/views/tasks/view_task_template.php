<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-header task-single-header"
     data-task-single-id="<?= e($task->id); ?>"
     data-status="<?= e($task->status); ?>">
	<?php if ($this->input->get('opened_from_lead_id')) { ?>
        <a href="#"
           onclick="init_lead(<?= e($this->input->get('opened_from_lead_id', FALSE)); ?>); return false;"
           class="back-to-from-task" data-placement="left" data-toggle="tooltip"
           data-title="<?= _l('back_to_lead'); ?>">
            <i class="fa fa-tty" aria-hidden="true"></i>
        </a>
	<?php } ?>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">&times;</span></button>
    <h4 class="modal-title tw-flex tw-items-center">
		<?php if (isset($task_type)) : ?>
            <span class="tw-mr-2">
                <span class="label"
                      style="background-color:<?php echo $task_type['label_color'] ?>;border:1px solid <?php echo $task_type['label_color'] ?>;color: <?php echo $task_type['text_color'] ?>"><?php echo $task_type['name'] ?></span>
            </span>
		<?php endif ?>
		<?php echo e($task->name); ?>
		<?php
		if ($task->recurring == 1)
		{
			echo '<span class="label label-info inline-block tw-ml-5">'._l('recurring_task').'</span>';
		}
		echo '<span class="tw-ml-5">'.format_task_status($task->status).'</span>';
		?>
        <span>
            <i style="display: none" class="fa-solid fa-copy"></i>
            <a class="label inline-block tw-ml-2"
               data-toggle="tooltip"
               data-title="<?php echo _l('task_copy_link') ?>"
               style="color:#3b82f6;border:1px solid #b1cdfb;background: #f7faff;"
               onclick="let copyIcon= document.createElement('i');copyIcon.classList.add('fa-solid', 'fa-copy') ;navigator.clipboard.writeText(this.dataset.link);this.innerHTML='Task link copied';setTimeout(() => { this.innerHTML = copyIcon.outerHTML}, 1000);"
               href="javascript:void(0)" data-link="<?php echo admin_url('tasks/view/'.$task->id) ?>">
                <i class="fa-solid fa-copy"></i>
            </a>
        </span>
    </h4>
	<?php if ($task->is_public == 0) { ?>
        <p class="tw-mb-0 tw-mt-1">
        <span
                class="tw-font-medium"><?= _l('task_is_private'); ?></span>
			<?php if (staff_can('edit', 'tasks')) { ?>
                -
                <a href="#"
                   onclick="make_task_public(<?= e($task->id); ?>); return false;">
					<?= _l('task_view_make_public'); ?>
                </a>
			<?php } ?>
        </p>
	<?php } ?>
</div>
<div class="modal-body">
    <input id="taskid" type="hidden" value="<?= $task->id ?>">
    <div class="row">
        <div class="col-md-12 task-single-col-left">
            <div class="no-margin pull-left mright5">
                <a class="task-view-collapse btn btn-default tw-absolute collapsed" href="javascript:void(0)"
                   data-toggle="tooltip" data-title="Task Info">
                    <i class="fa fa-chevron-left" aria-hidden="true"></i>
                    <i class="fa fa-chevron-right" aria-hidden="true"></i>
                </a>
            </div>

            <div class="tw-mb-4">
				<?php if (total_rows(
						db_prefix().'taskstimers',
						['end_time' => NULL, 'staff_id !=' => get_staff_user_id(), 'task_id' => $task->id]
					) > 0)
				{
					$startedTimers = $this->tasks_model->get_timers(
						$task->id,
						['staff_id !=' => get_staff_user_id(), 'end_time' => NULL]
					);

					$usersWorking = '';

					foreach ($startedTimers as $t)
					{
						$usersWorking .= '<b>'.e(get_staff_full_name($t['staff_id'])).'</b>, ';
					}

					$usersWorking = rtrim($usersWorking, ', '); ?>
                    <div class="alert alert-info">
						<?= _l(
							(count($startedTimers) == 1
								? 'task_users_working_on_tasks_single'
								: 'task_users_working_on_tasks_multiple'),
							$usersWorking
						); ?>
                    </div>
				<?php } ?>

				<?php if ($task->billed == 1) { ?>
                    <div class="alert alert-success">
						<?= _l(
							'task_is_billed',
							'<a href="'.admin_url(
								'invoices/list_invoices/'.$task->invoice_id
							).'" class="alert-link" target="_blank">'.e(format_invoice_number($task->invoice_id))
						).'</a>'; ?>
                    </div>
				<?php } ?>
                <p class="tw-mb-0 tw-font-medium task-info-created">
					<?php if (
						($task->addedfrom != 0 && $task->addedfrom != get_staff_user_id()) || $task->is_added_from_contact == 1
					)
					{ ?>
						<?= _l(
						'task_created_by',
						'<span class="tw-font-normal">'.($task->is_added_from_contact == 0 ? e(
							get_staff_full_name($task->addedfrom)
						) : e(get_contact_full_name($task->addedfrom))).'</span>'
					); ?>
                        <i class="fa-regular fa-clock" data-toggle="tooltip"
                           data-title="<?= e(_l('task_created_at', _dt($task->dateadded))); ?>"></i>
					<?php } else { ?>
						<?= _l(
							'task_created_at',
							'<span class="tw-font-normal">'.e(_dt($task->dateadded)).'</span>'
						); ?>
					<?php } ?>
                </p>

				<?php if ( ! empty($task->rel_id))
				{
					echo '<div class="task-single-related-wrapper">';
					$task_rel_data = get_relation_data($task->rel_type, $task->rel_id);
					$task_rel_value = get_relation_values($task_rel_data, $task->rel_type);
					echo '<div><span class="tw-font-medium">'._l('task_single_related').':</span> <a href="'.e(
							$task_rel_value['link']
						).'" target="_blank">'.e($task_rel_value['name']).'</a>';
					if ($task->rel_type == 'project' && $task->milestone != 0)
					{
						echo '<div><span class="tw-font-medium">'._l('task_milestone').':</span> ';
						$milestones = get_project_milestones($task->rel_id);
						if (staff_can('edit', 'tasks') && count($milestones) > 1)
						{ ?>
                            <span class="task-single-menu task-menu-milestones">
                    <span class="trigger pointer manual-popover text-has-action tw-font-normal">
                        <?= e($task->milestone_name); ?>
                    </span>
                    <span class="content-menu hide">
                        <ul>
                            <?php
							foreach ($milestones as $milestone)
							{ ?>
								<?php if ($task->milestone != $milestone['id']) { ?>
                                <li>
                                <a href="#"
                                   onclick="task_change_milestone(<?= e($milestone['id']); ?>,<?= e(
									   $task->id
								   ); ?>); return false;">
                                    <?= e($milestone['name']); ?>
                                </a>
                            </li>
							<?php } ?>
							<?php } ?>
                        </ul>
                    </span>
                </span>
						<?php } else
						{
							echo e($task->milestone_name);
						}
						echo '</div>';
					}
					echo '</div>';
					echo '</div>';
				} ?>

            </div>


            <div class="clearfix"></div>
			<?php if ($task->status != Tasks_model::STATUS_COMPLETE && ($task->current_user_is_assigned || staff_can(
						'edit',
						'tasks'
					) || $task->current_user_is_creator))
			{ ?>
                <p class="no-margin pull-left"
                   style="<?= 'margin-'.(is_rtl() ? 'left' : 'right').':5px !important'; ?>">
                    <a href="#" class="btn btn-primary" id="task-single-mark-complete-btn" autocomplete="off"
                       data-loading-text="<?= _l('wait_text'); ?>"
                       onclick="mark_complete(<?= e($task->id); ?>); return false;"
                       data-toggle="tooltip"
                       title="<?= _l('task_single_mark_as_complete'); ?>">
                        <i class="fa fa-check"></i>
                    </a>
                </p>
			<?php } elseif ($task->status == Tasks_model::STATUS_COMPLETE && ($task->current_user_is_assigned || staff_can(
						'edit',
						'tasks'
					) || $task->current_user_is_creator))
			{ ?>
                <p class="no-margin pull-left"
                   style="<?= 'margin-'.(is_rtl() ? 'left' : 'right').':5px !important'; ?>">
                    <a href="#" class="btn btn-default" id="task-single-unmark-complete-btn" autocomplete="off"
                       data-loading-text="<?= _l('wait_text'); ?>"
                       onclick="unmark_complete(<?= e($task->id); ?>); return false;"
                       data-toggle="tooltip"
                       title="<?= _l('task_unmark_as_complete'); ?>">
                        <i class="fa fa-check"></i>
                    </a>
                </p>
			<?php } ?>
			<?php if (staff_can('create', 'tasks') && count($task->timesheets) > 0) { ?>
                <p class="no-margin pull-left mright5">
                    <a href="#" class="btn btn-default mright5" data-toggle="tooltip"
                       data-title="<?= _l('task_statistics'); ?>"
                       onclick="task_tracking_stats(<?= e($task->id); ?>); return false;">
                        <i class="fa fa-bar-chart"></i>
                    </a>
                </p>
			<?php } ?>
            <p class="no-margin pull-left mright5">
                <a href="#" class="btn btn-default mright5" data-toggle="tooltip"
                   data-title="<?= _l('task_timesheets'); ?>"
                   onclick="slideToggle('#task_single_timesheets'); return false;">
                    <i class="fa fa-th-list"></i>
                </a>
            </p>
			<?php if ($task->billed == 0)
			{
				$is_assigned = $task->current_user_is_assigned;
				if ( ! $this->tasks_model->is_timer_started($task->id))
				{ ?>
                    <p class="no-margin pull-left" <?php if ( ! $is_assigned) { ?> data-toggle="tooltip"
                        data-title="<?= _l('task_start_timer_only_assignee'); ?>"
					<?php } ?>>
                        <a href="#"
                           class="mbot10 btn<?= ! $is_assigned || $task->status == Tasks_model::STATUS_COMPLETE ? ' btn-default disabled' : ' btn-success'; ?>"
                           onclick="timer_action(this, <?= e($task->id); ?>); return false;">
                            <i class="fa-regular fa-clock"></i>
							<?= _l('task_start_timer'); ?>
                        </a>
                    </p>
				<?php } else { ?>
                    <p class="no-margin pull-left">
                        <a href="#" data-toggle="popover"
                           data-placement="<?= is_mobile() ? 'bottom' : 'right'; ?>"
                           data-html="true" data-trigger="manual"
                           data-title="<?= _l('note'); ?>"
                           data-content='<?= render_textarea(
							   'timesheet_note'
						   ); ?><button type="button" onclick="timer_action(this, <?= e(
							   $task->id
						   ); ?>, <?= $this->tasks_model->get_last_timer(
							   $task->id
						   )->id; ?>);" class="btn btn-primary btn-sm"><?= _l('save'); ?></button>'
                           class="mbot10 btn btn-danger<?= ! $is_assigned ? ' disabled' : ''; ?>"
                           onclick="return false;">
                            <i class="fa-regular fa-clock"></i>
							<?= _l('task_stop_timer'); ?>
                        </a>
                    </p>
				<?php } ?>
				<?php
			} ?>
            <div class="clearfix"></div>
            <hr class="hr-10"/>
            <div id="task_single_timesheets"
                 class="<?= ! $this->session->flashdata('task_single_timesheets_open') ? 'hide' : ''; ?>">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th class="tw-text-sm tw-bg-neutral-50">
								<?= _l('timesheet_user'); ?>
                            </th>
                            <th class="tw-text-sm tw-bg-neutral-50">
								<?= _l('timesheet_start_time'); ?>
                            </th>
                            <th class="tw-text-sm tw-bg-neutral-50">
								<?= _l('timesheet_end_time'); ?>
                            </th>
                            <th class="tw-text-sm tw-bg-neutral-50">
								<?= _l('timesheet_time_spend'); ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
						<?php
						$timers_found = FALSE;

						foreach ($task->timesheets as $timesheet)
						{ ?>
						<?php if (staff_can('edit', 'tasks') || staff_can('create', 'tasks') || staff_can(
							'delete',
							'tasks'
						) || $timesheet['staff_id'] == get_staff_user_id())
						{
						$timers_found = TRUE; ?>
                        <tr>
                            <td class="tw-text-sm">
								<?php if ($timesheet['note'])
								{
									echo '<i class="fa fa-comment" data-html="true" data-placement="right" data-toggle="tooltip" data-title="'.e(
											$timesheet['note']
										).'"></i>';
								} ?>
                                <a href="<?= admin_url('staff/profile/'.$timesheet['staff_id']); ?>"
                                   target="_blank">
									<?= e($timesheet['full_name']); ?></a>
                            </td>
                            <td class="tw-text-sm">
								<?= e(_dt($timesheet['start_time'], TRUE)); ?>
                            </td>
                            <td class="tw-text-sm">
								<?php
								if ($timesheet['end_time'] !== NULL)
								{
									echo e(_dt($timesheet['end_time'], TRUE));
								} else
								{
									// Allow admins to stop forgotten timers by staff member
									if ( ! $task->billed && is_admin())
									{ ?>
                                        <a href="#" data-toggle="popover" data-placement="bottom" data-html="true"
                                           data-trigger="manual"
                                           data-title="<?= _l('note'); ?>"
                                           data-content='<?= render_textarea(
											   'timesheet_note'
										   ); ?><button type="button" onclick="timer_action(this, <?= e(
											   $task->id
										   ); ?>, <?= e(
											   $timesheet['id']
										   ); ?>, 1);" class="btn btn-primary btn-sm"><?= _l('save'); ?></button>'
                                           class="text-danger" onclick="return false;">
                                            <i class="fa-regular fa-clock"></i>
											<?= _l('task_stop_timer'); ?>
                                        </a>
										<?php
									}
								} ?>
                            </td>
                            <td class="tw-text-sm">
                                <div class="tw-flex">
                                    <div class="tw-grow">
										<?php
										if ($timesheet['time_spent'] == NULL)
										{
											echo _l('time_h').': '.e(
													seconds_to_time_format(time() - $timesheet['start_time'])
												).'<br />';
											echo _l('time_decimal').': '.e(
													sec2qty(time() - $timesheet['start_time'])
												).'<br />';
										} else
										{
											echo _l('time_h').': '.e(
													seconds_to_time_format($timesheet['time_spent'])
												).'<br />';
											echo _l('time_decimal').': '.e(sec2qty($timesheet['time_spent'])).'<br />';
										} ?>
                                    </div>
									<?php
									if ( ! $task->billed)
									{ ?>
                                        <div
                                                class="tw-flex tw-items-center tw-shrink-0 tw-self-start tw-space-x-1.5 tw-ml-2">
											<?php
											if (staff_can('delete_timesheet', 'tasks') || (staff_can(
														'delete_own_timesheet',
														'tasks'
													) && $timesheet['staff_id'] == get_staff_user_id()))
											{
												echo '<a href="'.admin_url(
														'tasks/delete_timesheet/'.$timesheet['id']
													).'" class="task-single-delete-timesheet text-danger" data-task-id="'.$task->id.'"><i class="fa fa-remove"></i></a>';
											}
											if (staff_can('edit_timesheet', 'tasks') || (staff_can(
														'edit_own_timesheet',
														'tasks'
													) && $timesheet['staff_id'] == get_staff_user_id()))
											{
												echo '<a href="#" class="task-single-edit-timesheet text-info" data-toggle="tooltip" data-title="'._l(
														'edit'
													).'" data-timesheet-id="'.$timesheet['id'].'">
                                    <i class="fa fa-edit"></i>
                                    </a>';
											}
											?>
                                        </div>
									<?php } ?>
                                </div>
                </div>
                </td>
                </tr>
                <tr>
                    <td class="timesheet-edit task-modal-edit-timesheet-<?= $timesheet['id'] ?> hide"
                        colspan="5">
                        <form class="task-modal-edit-timesheet-form">
                            <input type="hidden" name="timer_id"
                                   value="<?= $timesheet['id'] ?>">
                            <input type="hidden" name="task_id"
                                   value="<?= $task->id ?>">
                            <div class="timesheet-start-end-time">
                                <div class="col-md-6">
									<?= render_datetime_input(
										'start_time',
										'task_log_time_start',
										_dt($timesheet['start_time'], TRUE)
									); ?>
                                </div>
                                <div class="col-md-6">
									<?= render_datetime_input(
										'end_time',
										'task_log_time_end',
										_dt($timesheet['end_time'], TRUE)
									); ?>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">
										<?= _l('task_single_log_user'); ?>
                                    </label>
                                    <br/>
                                    <select name="staff_id" class="selectpicker" data-width="100%">
										<?php foreach ($task->assignees as $assignee)
										{
											if ((staff_cant('create', 'task') && staff_cant(
														'edit',
														'task'
													) && $assignee['assigneeid'] != get_staff_user_id()) || ($task->rel_type == 'project' && staff_cant(
														'edit',
														'projects'
													) && $assignee['assigneeid'] != get_staff_user_id()))
											{
												continue;
											}
											$selected = '';
											if ($assignee['assigneeid'] == $timesheet['staff_id'])
											{
												$selected = ' selected';
											} ?>
                                            <option<?= e($selected); ?>
                                                    value="<?= e($assignee['assigneeid']); ?>">
												<?= e($assignee['full_name']); ?>
                                            </option>
											<?php
										} ?>
                                    </select>
                                </div>
								<?= render_textarea(
									'note',
									'note',
									$timesheet['note'],
									['id' => 'note'.$timesheet['id']]
								); ?>
                            </div>
                            <div class="col-md-12 text-right">
                                <button type="button"
                                        class="btn btn-default edit-timesheet-cancel"><?= _l('cancel'); ?></button>
                                <button class="btn btn-success edit-timesheet-submit"></i>
									<?= _l('submit'); ?></button>
                            </div>
                        </form>
                    </td>
                </tr>
				<?php } ?>
				<?php } ?>
				<?php if ($timers_found == FALSE) { ?>
                    <tr>
                        <td colspan="5" class="text-center bold">
							<?= _l('no_timers_found'); ?>
                        </td>
                    </tr>
				<?php } ?>
				<?php if ($task->billed == 0 && ($is_assigned || (count($task->assignees) > 0 && is_admin())) && $task->status != Tasks_model::STATUS_COMPLETE)
				{
					?>
                    <tr class="odd">
                        <td colspan="5" class="add-timesheet">
                            <div class="col-md-12">
                                <p class="font-medium bold mtop5">
									<?= _l('add_timesheet'); ?>
                                </p>
                                <hr class="mtop10 mbot10"/>
                            </div>
                            <div class="timesheet-start-end-time">
                                <div class="col-md-6">
									<?= render_datetime_input('timesheet_start_time', 'task_log_time_start'); ?>
                                </div>
                                <div class="col-md-6">
									<?= render_datetime_input('timesheet_end_time', 'task_log_time_end'); ?>
                                </div>
                            </div>
                            <div class="timesheet-duration hide">
                                <div class="col-md-12">
                                    <i class="fa-regular fa-circle-question pointer pull-left mtop2"
                                       data-toggle="popover"
                                       data-html="true" data-content="
                                    :15 - 15 <?= _l('minutes'); ?><br />
                                    2 - 2 <?= _l('hours'); ?><br />
                                    5:5 - 5 <?= _l('hours'); ?> & 5 <?= _l('minutes'); ?><br />
                                    2:50 - 2 <?= _l('hours'); ?> & 50 <?= _l('minutes'); ?><br />
                                    "></i>
									<?= render_input(
										'timesheet_duration',
										'project_timesheet_time_spend',
										'',
										'text',
										['placeholder' => 'HH:MM']
									); ?>
                                </div>
                            </div>
                            <div class="col-md-12 mbot15 mntop15">
                                <a href="#" class="timesheet-toggle-enter-type">
                                <span class="timesheet-duration-toggler-text switch-to">
                                    <?= _l('timesheet_duration_instead'); ?>
                                </span>
                                    <span class="timesheet-date-toggler-text hide ">
                                    <?= _l('timesheet_date_instead'); ?>
                                </span>
                                </a>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">
										<?= _l('task_single_log_user'); ?>
                                    </label>
                                    <br/>
                                    <select name="single_timesheet_staff_id" class="selectpicker" data-width="100%">
										<?php foreach ($task->assignees as $assignee)
										{
											if ((staff_cant('create', 'tasks') && staff_cant(
														'edit',
														'tasks'
													) && $assignee['assigneeid'] != get_staff_user_id()) || ($task->rel_type == 'project' && staff_cant(
														'edit',
														'projects'
													) && $assignee['assigneeid'] != get_staff_user_id()))
											{
												continue;
											}
											$selected = '';
											if ($assignee['assigneeid'] == get_staff_user_id())
											{
												$selected = ' selected';
											} ?>
                                            <option<?= e($selected); ?>
                                                    value="<?= e($assignee['assigneeid']); ?>">
												<?= e($assignee['full_name']); ?>
                                            </option>
											<?php
										} ?>
                                    </select>
                                </div>
								<?= render_textarea('task_single_timesheet_note', 'note'); ?>
                            </div>
                            <div class="col-md-12 text-right">
								<?php
								$disable_button = '';
								if ($this->tasks_model->is_timer_started_for_task(
									$task->id,
									['staff_id' => get_staff_user_id()]
								))
								{
									$disable_button = 'disabled ';
									echo '<div class="text-right mbot15 text-danger">'._l(
											'add_task_timer_started_warning'
										).'</div>';
								} ?>
                                <button
									<?= e($disable_button); ?>data-task-id="<?= e($task->id); ?>"
                                    class="btn btn-success task-single-add-timesheet"><i class="fa fa-plus"></i>
									<?= _l('submit'); ?></button>
                            </div>
                        </td>
                    </tr>
					<?php
				} ?>
                </tbody>
                </table>
            </div>
            <hr/>
        </div>
        <div class="clearfix"></div>
		<?php hooks()->do_action('before_task_description_section', $task); ?>
        <h4 class="th tw-font-semibold tw-text-base tw-mb-1 pull-left">
			<?= _l('task_view_description'); ?>
        </h4>
		<?php if (staff_can('edit', 'tasks')) { ?>
            <a href="#"
               onclick="edit_task_description_inline(this,<?= e($task->id); ?>); return false;"
               class="pull-left tw-mt-2.5 tw-text-sm tw-ml-2 text-muted">
                <i class="fa-regular fa-pen-to-square"></i>
            </a>
		<?php } ?>
        <div class="clearfix"></div>
		<?php if ( ! empty($task->description))
		{
			echo '<div class="tc-content"><div id="task_view_description">'.check_for_links(
					$task->description
				).'</div></div>';
		} else
		{
			echo '<div class="no-margin tc-content task-no-description" id="task_view_description"><span class="text-muted">'._l(
					'task_no_description'
				).'</span></div>';
		} ?>
        <div class="clearfix"></div>
        <hr/>

        <div class="tw-flex tw-justify-between tw-items-center">
            <h4 class="chk-heading tw-my-0 tw-font-semibold tw-text-base">
				<?= _l('task_checklist_items'); ?>
            </h4>
            <div class="tw-flex tw-items-center tw-space-x-2">
                <div>
                    <div class="chk-toggle-buttons">
						<?php if (count($task->checklist_items) > 0) { ?>
                            <button
                                    class="tw-bg-transparent tw-border-0 tw-text-sm tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 tw-px-0<?= $hide_completed_items == 1 ? ' hide' : '' ?>"
                                    data-hide="1" onclick="toggle_completed_checklist_items_visibility(this)">
								<?= _l('hide_task_checklist_items_completed'); ?>
                            </button>
							<?php $finished = array_filter($task->checklist_items, function ($item) {
								return $item['finished'] == 1;
							}); ?>
                            <button
                                    class="tw-bg-transparent tw-border-0 tw-text-sm tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 tw-px-0<?= $hide_completed_items == 1 ? '' : ' hide' ?>"
                                    data-hide="0" onclick="toggle_completed_checklist_items_visibility(this)">
								<?= _l(
									'show_task_checklist_items_completed',
									'(<span class="task-total-checklist-completed">'.count($finished).'</span>)'
								); ?>
                            </button>
						<?php } ?>
                    </div>
                </div>
                <button type="button" data-toggle="tooltip"
                        data-title="<?= _l('add_checklist_item'); ?>"
                        class="tw-inline-flex tw-bg-transparent tw-border-0 tw-p-1.5 hover:tw-bg-neutral-100 tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 tw-rounded-md ltr:tw-ml-2 rtl:tw-mr-2"
                        onclick="add_task_checklist_item('<?= e($task->id); ?>', undefined, this); return false">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>
        </div>

        <div
                class="[&_button]:!tw-pr-0 [&_.caret]:!tw-right-1.5 [&_.caret]:!tw-mr-px form-group !tw-mt-0 tw-mb-0 checklist-templates-wrapper simple-bootstrap-select task-single-checklist-templates<?= count(
					$checklistTemplates
				) == 0 ? ' hide' : ''; ?>">
            <select id="checklist_items_templates" class="selectpicker checklist-items-template-select"
                    data-none-selected-text="<?= _l('insert_checklist_templates') ?>"
                    data-width="100%" data-live-search="true">
                <option value=""></option>
				<?php foreach ($checklistTemplates as $chkTemplate) { ?>
                    <option
                            value="<?= e($chkTemplate['id']); ?>">
						<?= e($chkTemplate['description']); ?>
                    </option>
				<?php } ?>
            </select>
        </div>

        <div class="clearfix"></div>

        <p class="hide text-muted no-margin" id="task-no-checklist-items">
			<?= _l('task_no_checklist_items_found'); ?>
        </p>

        <div class="row checklist-items-wrapper">
            <div class="col-md-12 ">
                <div id="checklist-items">
					<?php $this->load->view(
						'admin/tasks/checklist_items_template',
						[
							'task_id' => $task->id,
							'current_user_is_creator' => $task->current_user_is_creator,
							'checklists' => $task->checklist_items,
						]
					); ?>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
		<?php if (count($task->attachments) > 0) { ?>
            <div class="row task_attachments_wrapper">
                <div class="col-md-12" id="attachments">
                    <hr/>
                    <h4 class="th tw-font-semibold tw-text-base mbot15">
						<?= _l('task_view_attachments'); ?>
                    </h4>
                    <div class="row">
						<?php
						$i = 1;
						// Store all url related data here
						$comments_attachments = [];
						$attachments_data = [];
						$show_more_link_task_attachments = hooks()->apply_filters('show_more_link_task_attachments', 2);

						foreach ($task->attachments as $attachment)
						{ ?>
							<?php ob_start(); ?>
                            <div data-num="<?= e($i); ?>"
                                 data-commentid="<?= e($attachment['comment_file_id']); ?>"
                                 data-comment-attachment="<?= e($attachment['task_comment_id']); ?>"
                                 data-task-attachment-id="<?= e($attachment['id']); ?>"
                                 class="task-attachment-col col-md-6<?= $i > $show_more_link_task_attachments ? ' hide task-attachment-col-more' : ''; ?>">
                                <ul class="list-unstyled task-attachment-wrapper" data-placement="right"
                                    data-toggle="tooltip"
                                    data-title="<?= e($attachment['file_name']); ?>">
                                    <li
                                            class="mbot10 task-attachment<?= strtotime(
												$attachment['dateadded']
											) >= strtotime('-16 hours') ? ' highlight-bg' : ''; ?>">
                                        <div class="mbot10 pull-right task-attachment-user">
											<?php if ($attachment['staffid'] == get_staff_user_id() || is_admin()) { ?>
                                                <a href="#" class="pull-right"
                                                   onclick="remove_task_attachment(this,<?= e(
													   $attachment['id']
												   ); ?>); return false;">
                                                    <i class="fa fa fa-times"></i>
                                                </a>
											<?php }
											$externalPreview = FALSE;
											$is_image = FALSE;
											$path = get_upload_path_by_type(
													'task'
												).$task->id.'/'.$attachment['file_name'];
											$href_url = site_url(
												'download/file/taskattachment/'.$attachment['attachment_key']
											);
											$isHtml5Video = is_html5_video($path);
											if (empty($attachment['external']))
											{
												$is_image = is_image($path);
												$img_url = site_url(
													'download/preview_image?path='.protected_file_url_by_path(
														$path,
														TRUE
													).'&type='.$attachment['filetype']
												);
											} elseif (( ! empty($attachment['thumbnail_link']) || ! empty($attachment['external']))
												&& ! empty($attachment['thumbnail_link']))
											{
												$is_image = TRUE;
												$img_url = optimize_dropbox_thumbnail($attachment['thumbnail_link']);
												$externalPreview = $img_url;
												$href_url = $attachment['external_link'];
											} elseif ( ! empty($attachment['external']) && empty($attachment['thumbnail_link']))
											{
												$href_url = $attachment['external_link'];
											}
											if ( ! empty($attachment['external']) && $attachment['external'] == 'dropbox' && $is_image)
											{ ?>
                                                <a href="<?= e($href_url); ?>"
                                                   target="_blank" class="" data-toggle="tooltip"
                                                   data-title="<?= _l('open_in_dropbox'); ?>"><i
                                                            class="fa fa-dropbox" aria-hidden="true"></i></a>
											<?php } elseif ( ! empty($attachment['external']) && $attachment['external'] == 'gdrive') { ?>
                                                <a href="<?= e($href_url); ?>"
                                                   target="_blank" class="" data-toggle="tooltip"
                                                   data-title="<?= _l('open_in_google'); ?>"><i
                                                            class="fa-brands fa-google" aria-hidden="true"></i></a>
											<?php }
											if ($attachment['staffid'] != 0)
											{
												echo '<a href="'.admin_url(
														'profile/'.$attachment['staffid']
													).'" target="_blank">'.e(
														get_staff_full_name($attachment['staffid'])
													).'</a> - ';
											} elseif ($attachment['contact_id'] != 0)
											{
												echo '<a href="'.admin_url(
														'clients/client/'.get_user_id_by_contact_id(
															$attachment['contact_id']
														).'?contactid='.$attachment['contact_id']
													).'" target="_blank">'.e(
														get_contact_full_name($attachment['contact_id'])
													).'</a> - ';
											}
											echo '<span class="text-has-action tw-text-sm" data-toggle="tooltip" data-title="'._dt(
													$attachment['dateadded']
												).'">'.e(time_ago($attachment['dateadded'])).'</span>';
											?>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="<?php if ($is_image)
										{
											echo 'preview-image';
										} elseif ( ! $isHtml5Video)
										{
											echo 'task-attachment-no-preview';
										} ?>">
											<?php
											// Not link on video previews because on click on the video is opening new tab
											if ( ! $isHtml5Video)
											{ ?>
                                            <a href="<?= ! $externalPreview ? $href_url : $externalPreview; ?>"
                                               target="_blank"
												<?php if ($is_image) { ?>
                                                    data-lightbox="task-attachment"
												<?php } ?>
                                               class="<?= $isHtml5Video ? 'video-preview' : ''; ?>">
												<?php } ?>
												<?php if ($is_image) { ?>
                                                    <img src="<?= e($img_url); ?>"
                                                         class="img img-responsive">
												<?php } elseif ($isHtml5Video) { ?>
                                                    <video width="100%" height="100%"
                                                           src="<?= site_url(
															   'download/preview_video?path='.protected_file_url_by_path(
																   $path
															   ).'&type='.$attachment['filetype']
														   ); ?>"
                                                           controls>
                                                        Your browser does not support the video tag.
                                                    </video>
												<?php } else { ?>
                                                    <i
                                                            class="<?= get_mime_class($attachment['filetype']); ?>"></i>
													<?= e($attachment['file_name']); ?>
												<?php } ?>
												<?php if ( ! $isHtml5Video) { ?>
                                            </a>
										<?php } ?>
                                        </div>
                                        <div class="clearfix"></div>
                                    </li>
                                </ul>
                            </div>
							<?php
							$attachments_data[$attachment['id']] = ob_get_contents();
							if ($attachment['task_comment_id'] != 0)
							{
								$comments_attachments[$attachment['task_comment_id']][$attachment['id']] = $attachments_data[$attachment['id']];
							}
							ob_end_clean();
							echo $attachments_data[$attachment['id']];
							?>
							<?php
							$i++;
						} ?>
                    </div>
                </div>
                <div class="clearfix"></div>
				<?php if (($i - 1) > $show_more_link_task_attachments) { ?>
                    <div class="col-md-12" id="show-more-less-task-attachments-col">
                        <a href="#" class="task-attachments-more"
                           onclick="slideToggle('.task_attachments_wrapper .task-attachment-col-more', task_attachments_toggle); return false;">
							<?= _l('show_more'); ?>
                        </a>
                        <a href="#" class="task-attachments-less hide"
                           onclick="slideToggle('.task_attachments_wrapper .task-attachment-col-more', task_attachments_toggle); return false;">
							<?= _l('show_less'); ?>
                        </a>
                    </div>
				<?php } ?>
                <div class="col-md-12 text-center">
                    <hr/>
                    <a href="<?= admin_url('tasks/download_files/'.$task->id); ?>"
                       class="bold">
						<?= _l('download_all'); ?>
                        (.zip)
                    </a>
                </div>
            </div>
		<?php } ?>
        <hr/>
        <a href="#" id="taskCommentSlide" onclick="slideToggle('.tasks-comments'); return false;">
            <h4 class="mbot20 tw-font-semibold tw-text-base">
				<?= _l('task_comments'); ?>
            </h4>
        </a>
        <div class="tasks-comments inline-block full-width simple-editor" <?= count(
			$task->comments
		) == 0 ? ' style="display:none"' : ''; ?>>
			<?= form_open_multipart(
				admin_url('project_management_enhancements/tasks/add_task_comment'),
				[
					'id' => 'task-comment-form',
					'class' => 'dropzone dropzone-manual',
					'style' => 'min-height:auto;background-color:#fff;',
				]
			); ?>
            <textarea name="comment"
                      placeholder="<?= _l('task_single_add_new_comment'); ?>"
                      id="task_comment" rows="3" class="form-control ays-ignore"></textarea>
            <div id="dropzoneTaskComment" class="dropzoneDragArea dz-default dz-message hide task-comment-dropzone">
                <span><?= _l('drop_files_here_to_upload'); ?></span>
            </div>
            <div class="dropzone-task-comment-previews dropzone-previews"></div>
            <button type="button" class="btn btn-primary mtop10 pull-right hide" id="addTaskCommentBtn"
                    autocomplete="off"
                    data-loading-text="<?= _l('wait_text'); ?>"
                    onclick="add_task_comment('<?= e($task->id); ?>');"
                    data-comment-task-id="<?= e($task->id); ?>">
				<?= _l('task_single_add_new_comment'); ?>
            </button>
			<?= form_close(); ?>
            <div class="clearfix"></div>
			<?= count($task->comments) > 0 ? '<hr />' : ''; ?>
            <div id="task-comments" class="mtop10">
				<?php
				$comments = '';
				$len = count($task->comments);
				$i = 0;
				$task_comments = $task->comments;
				$task_parent_comments = [];
				$task_child_comments = [];

				foreach ($task_comments as $comment)
				{
					if ($comment['parent_id'])
					{
						$task_child_comments[$comment['parent_id']][] = $comment;
					} else
					{
						$task_parent_comments[] = $comment;
					}
				}

				foreach ($task_parent_comments as $comment)
				{
					$comments .= format_parent_task_comment($task, $comment, $task_child_comments[$comment['id']] ?? [], $comments_attachments ?? [], $attachments_data ?? []);

					if ($i >= 0 && $i != $len - 1)
					{
						$comments .= '<hr class="" />';
					}
					$i++;
				}
				echo $comments;
				?>
            </div>
        </div>
    </div>
    <div class="col-md-4 task-single-col-right">
        <div class="pull-right mbot10 task-single-menu task-menu-options">
            <div class="content-menu hide">
                <ul>
					<?php if (staff_can('edit', 'tasks')) { ?>
                        <li>
                            <a href="#"
                               onclick="edit_task(<?= e($task->id); ?>); return false;">
								<?= _l('task_single_edit'); ?>
                            </a>
                        </li>
					<?php } ?>
					<?php if (staff_can('create', 'tasks')) { ?>
						<?php
						$copy_template = '';
						if (count($task->assignees) > 0)
						{
							$copy_template .= "<div class='checkbox'><input type='checkbox' name='copy_task_assignees' id='copy_task_assignees' checked><label for='copy_task_assignees'>"._l(
									'task_single_assignees'
								).'</label></div>';
						}
						if (count($task->followers) > 0)
						{
							$copy_template .= "<div class='checkbox'><input type='checkbox' name='copy_task_followers' id='copy_task_followers' checked><label for='copy_task_followers'>"._l(
									'task_single_followers'
								).'</label></div>';
						}
						if (count($task->checklist_items) > 0)
						{
							$copy_template .= "<div class='checkbox'><input type='checkbox' name='copy_task_checklist_items' id='copy_task_checklist_items' checked><label for='copy_task_checklist_items'>"._l(
									'task_checklist_items'
								).'</label></div>';
						}
						if (count($task->attachments) > 0)
						{
							$copy_template .= "<div class='checkbox'><input type='checkbox' name='copy_task_attachments' id='copy_task_attachments'><label for='copy_task_attachments'>"._l(
									'task_view_attachments'
								).'</label></div>';
						}

						$copy_template .= '<p>'._l('task_status').'</p>';
						$task_copy_statuses = hooks()->apply_filters('task_copy_statuses', $task_statuses);

						foreach ($task_copy_statuses as $copy_status)
						{
							$copy_template .= "<div class='radio'><input type='radio' value='".$copy_status['id']."' name='copy_task_status' id='copy_task_status_".$copy_status['id']."'".($copy_status['id'] == hooks()->apply_filters(
									'copy_task_default_status',
									1
								) ? ' checked' : '')."><label for='copy_task_status_".$copy_status['id']."'>".e(
									$copy_status['name']
								).'</label></div>';
						}

						$copy_template .= "<div class='text-center'>";
						$copy_template .= "<button type='button' data-task-copy-from='".$task->id."' class='btn btn-success copy_task_action'>"._l(
								'copy_task_confirm'
							).'</button>';
						$copy_template .= '</div>';
						?>
                        <li><a href="#" onclick="return false;" data-placement="bottom" data-toggle="popover"
                               data-content="<?= htmlspecialchars($copy_template); ?>"
                               data-html="true"><?= _l('task_copy'); ?></span></a>
                        </li>
					<?php } ?>
					<?php if (staff_can('delete', 'tasks')) { ?>
                        <li>
                            <a href="<?= admin_url('tasks/delete_task/'.$task->id); ?>"
                               class="_delete task-delete">
								<?= _l('task_single_delete'); ?>
                            </a>
                        </li>
					<?php } ?>
                </ul>
            </div>
			<?php if (staff_can('delete', 'tasks') || staff_can('edit', 'tasks') || staff_can('create', 'tasks')) { ?>
                <a href="#" onclick="return false;" class="trigger text-muted manual-popover mright5">
                    <i class="fa-regular fa-circle"></i>
                    <i class="fa-regular fa-circle"></i>
                    <i class="fa-regular fa-circle"></i>
                </a>
			<?php } ?>
        </div>
        <h4 class="task-info-heading tw-font-semibold tw-text-base tw-mb-0 tw-text-neutral-800">
            <i
                    class="fa-regular fa-circle-question fa-fw fa-lg task-info-icon tw-text-neutral-500 -tw-ml-1.5 tw-mr-1"></i>
			<?= _l('task_info'); ?>
        </h4>
        <div class="clearfix"></div>
        <div class="task-info task-status task-info-status tw-mt-3">
            <h5 class="tw-inline-flex tw-items-center tw-space-x-1.5">
                <i
                        class="fa-regular fa-star fa-fw fa-lg pull-left task-info-icon"></i><?= _l('task_status'); ?>:
				<?php if ($task->current_user_is_assigned || $task->current_user_is_creator || staff_can(
						'edit',
						'tasks'
					))
				{ ?>
                    <span class="task-single-menu task-menu-status">
                    <span class="trigger pointer manual-popover text-has-action tw-text-neutral-800">
                        <?= e(format_task_status($task->status, TRUE, TRUE)); ?>
                    </span>
                    <span class="content-menu hide">
                        <ul>
                            <?php
							$task_single_mark_as_statuses = hooks()->apply_filters(
								'task_single_mark_as_statuses',
								$task_statuses
							);

							foreach ($task_single_mark_as_statuses as $status)
							{ ?>
								<?php if ($task->status != $status['id']) { ?>
                                <li>
                                <a href="#"
                                   onclick="task_mark_as(<?= e($status['id']); ?>,<?= e($task->id); ?>); return false;"
                                   class="tw-block">
                                    <?= e(_l('task_mark_as', $status['name'])); ?>
                                </a>
                            </li>
							<?php } ?>
							<?php } ?>
                        </ul>
                    </span>
                </span>
				<?php } else { ?>
                    <span class="tw-text-neutral-800">
                    <?= format_task_status($task->status, TRUE); ?>
                </span>
				<?php } ?>
            </h5>
        </div>
		<?php if ($task->status == Tasks_model::STATUS_COMPLETE) { ?>
            <div class="task-info task-info-finished">
                <h5 class="tw-inline-flex tw-items-center tw-space-x-1.5">
                    <i class="fa fa-check fa-fw fa-lg task-info-icon pull-left "></i>
					<?= _l('task_single_finished'); ?>:
                    <span data-toggle="tooltip"
                          data-title="<?= e(_dt($task->datefinished)); ?>"
                          data-placement="bottom" class="text-has-action tw-text-neutral-800">
                    <?= e(time_ago($task->datefinished)); ?>
                </span>
                </h5>
            </div>
		<?php } ?>
        <div class="task-info task-single-inline-wrap task-info-start-date">
            <h5 class="tw-inline-flex tw-items-center tw-space-x-1.5">
                <div
                        class="tw-shrink-0<?= $task->status != 5 ? ' tw-grow' : ''; ?>">
                    <i class="fa-regular fa-calendar fa-fw fa-lg fa-margin task-info-icon pull-left tw-mt-2"></i>
					<?= _l('task_single_start_date'); ?>:
                </div>
				<?php if (staff_can('edit', 'tasks') && $task->status != 5) { ?>
                    <input name="startdate" tabindex="-1"
                           value="<?= e(_d($task->startdate)); ?>"
                           id="task-single-startdate"
                           class="task-info-inline-input-edit datepicker pointer task-single-inline-field tw-text-neutral-800">
				<?php } else { ?>
                    <span class="tw-text-neutral-800">
                    <?= e(_d($task->startdate)); ?>
                </span>
				<?php } ?>
            </h5>
        </div>
        <div class="task-info task-info-due-date task-single-inline-wrap<?= ! $task->duedate && staff_cant(
			'tasks',
			'edit'
		) ? ' hide' : ''; ?>"
			<?= ! $task->duedate ? ' style="opacity:0.5"' : ''; ?>>
            <h5 class="tw-inline-flex tw-items-center tw-space-x-1.5">
                <div
                        class="tw-shrink-0<?= $task->status != 5 ? ' tw-grow' : ''; ?>">
                    <i class="fa-regular fa-calendar-check fa-fw fa-lg task-info-icon pull-left tw-mt-2"></i>
					<?= _l('task_single_due_date'); ?>:
                </div>
				<?php if (staff_can('edit', 'tasks') && $task->status != 5) { ?>
                    <input name="duedate" tabindex="-1"
                           value="<?= e(_d($task->duedate)); ?>"
                           id="task-single-duedate"
                           class="task-info-inline-input-edit datepicker pointer task-single-inline-field tw-text-neutral-800"
                           autocomplete="off"
						<?= $project_deadline ? ' data-date-end-date="'.e($project_deadline).'"' : ''; ?>>
				<?php } else { ?>
                    <span class="tw-text-neutral-800">
                    <?= e(_d($task->duedate)); ?>
                </span>
				<?php } ?>
            </h5>
        </div>
        <div class="task-info task-info-priority">
            <h5 class="tw-inline-flex tw-items-center tw-space-x-1.5">
                <i class="fa fa-bolt fa-fw fa-lg task-info-icon pull-left"></i>
				<?= _l('task_single_priority'); ?>:
				<?php if (staff_can('edit', 'tasks') && $task->status != Tasks_model::STATUS_COMPLETE) { ?>
                    <span class="task-single-menu task-menu-priority">
                    <span class="trigger pointer manual-popover text-has-action"
                          style="color:<?= e(task_priority_color($task->priority)); ?>;">
                        <?= e(task_priority($task->priority)); ?>
                    </span>
                    <span class="content-menu hide">
                        <ul>
                            <?php foreach (get_tasks_priorities() as $priority) { ?>
								<?php if ($task->priority != $priority['id']) { ?>
                                    <li>
                                <a href="#"
                                   onclick="task_change_priority(<?= e($priority['id']); ?>,<?= e(
									   $task->id
								   ); ?>); return false;"
                                   class="tw-block">
                                    <?= e($priority['name']); ?>
                                </a>
                            </li>
								<?php } ?>
							<?php } ?>
                        </ul>
                    </span>
                </span>
				<?php } else { ?>
                    <span
                            style="color:<?= e(task_priority_color($task->priority)); ?>;">
                    <?= e(task_priority($task->priority)); ?>
                </span>
				<?php } ?>
            </h5>
        </div>
		<?php if ($task->current_user_is_creator || staff_can('edit', 'tasks')) { ?>
            <div class="task-info task-info-hourly-rate">
                <h5 class="tw-inline-flex tw-items-center tw-space-x-1.5">
                    <i class="fa-regular fa-clock fa-fw fa-lg task-info-icon pull-left"></i>
					<?= _l('task_hourly_rate'); ?>:
                    <span class="tw-text-neutral-800">
                    <?php if ($task->rel_type == 'project' && $task->project_data->billing_type == 2)
					{
						echo e(app_format_number($task->project_data->project_rate_per_hour));
					} else
					{
						echo e(app_format_number($task->hourly_rate));
					} ?>
                </span>
                </h5>
            </div>
            <div class="task-info task-info-billable">
                <h5 class="tw-inline-flex tw-items-center tw-space-x-1.5">
                    <i class="fa-solid fa-dollar-sign fa-fw fa-lg task-info-icon pull-left"></i>
					<?= _l('task_billable'); ?>:
                    <span class="tw-text-neutral-800">
                    <?= $task->billable == 1 ? _l('task_billable_yes') : _l('task_billable_no') ?>
						<?php if ($task->billable == 1) { ?>
                            <b>(<?= $task->billed == 1 ? _l('task_billed_yes') : _l('task_billed_no') ?>)</b>
						<?php } ?>
                </span>
                </h5>
				<?php if ($task->rel_type == 'project' && $task->project_data->billing_type == 1) { ?>
                    <br/>
                    <span class="tw-ml-5 tw-text-sm">
                (<?= _l('project').' '._l('project_billing_type_fixed_cost'); ?>)
            </span>
				<?php } ?>
            </div>
			<?php if ($task->billable == 1
				&& $task->billed == 0
				&& ($task->rel_type != 'project' || ($task->rel_type == 'project' && $task->project_data->billing_type != 1))
				&& staff_can('create', 'invoices'))
			{ ?>
                <div class="task-info task-billable-amount">
                    <h5 class="tw-inline-flex tw-items-center tw-space-x-1.5">
                        <i class="fa fa-regular fa-file-lines fa-fw fa-lg pull-left task-info-icon"></i>
						<?= _l('billable_amount'); ?>:
						<?= e($this->tasks_model->get_billable_amount($task->id)); ?>
                    </h5>
                </div>
			<?php } ?>
		<?php } ?>
		<?php if ($task->current_user_is_assigned || total_rows(
				db_prefix().'taskstimers',
				['task_id' => $task->id, 'staff_id' => get_staff_user_id()]
			) > 0)
		{ ?>
            <div class="task-info task-info-user-logged-time">
                <h5 class="tw-inline-flex tw-items-center">
                    <i class="fa fa-asterisk task-info-icon fa-fw fa-lg" aria-hidden="true"></i>
					<?= _l('task_user_logged_time'); ?>
					<?= e(
						seconds_to_time_format(
							$this->tasks_model->calc_task_total_time($task->id, ' AND staff_id='.get_staff_user_id())
						)
					); ?>
                </h5>
            </div>
		<?php } ?>
		<?php if (staff_can('create', 'tasks')) { ?>
            <div class="task-info task-info-total-logged-time">
                <h5 class="tw-inline-flex tw-items-center tw-space-x-1.5">
                    <i
                            class="fa-regular fa-clock fa-fw fa-lg task-info-icon"></i><?= _l(
						'task_total_logged_time'
					); ?>
                    <span class="text-success">
                    <?= e(seconds_to_time_format($this->tasks_model->calc_task_total_time($task->id))); ?>
                </span>
                </h5>
            </div>
		<?php } ?>
		<?php foreach (get_custom_fields('tasks') as $field) { ?>
			<?php $value = get_custom_field_value($task->id, $field['id'], 'tasks');
			if ($value == '')
			{
				continue;
			} ?>
            <div class="task-info">
                <h5
                        class="task-info-custom-field tw-inline-flex tw-items-center tw-space-x-1.5 task-info-custom-field-<?= e(
							$field['id']
						); ?>">
                    <i class="fa-regular fa-circle fa-fw fa-lg task-info-icon"></i>
					<?= e($field['name']); ?>:
                    <span class="tw-text-neutral-800">
                    <?= $value; ?>
                </span>
                </h5>
            </div>
		<?php } ?>
		<?php if (staff_can('create', 'tasks') || staff_can('edit', 'tasks')) { ?>
            <div id="inputTagsWrapper" class="taskSingleTasks task-info-tags-edit tw-ml-0.5 tw-mt-2">
                <input type="text" class="tagsinput" id="taskTags"
                       data-taskid="<?= e($task->id); ?>"
                       value="<?= prep_tags_input(get_tags_in($task->id, 'task')); ?>"
                       data-role="tagsinput">
            </div>
            <div class="clearfix"></div>
		<?php } else { ?>
            <div class="mtop5 clearfix"></div>
			<?= render_tags(get_tags_in($task->id, 'task')); ?>
            <div class="clearfix"></div>
		<?php } ?>
        <hr class="task-info-separator"/>
        <div class="clearfix"></div>
		<?php if ($task->current_user_is_assigned)
		{
			foreach ($task->assignees as $assignee)
			{
				if ($assignee['assigneeid'] == get_staff_user_id() && get_staff_user_id() != $assignee['assigned_from'] && $assignee['assigned_from'] != 0 || $assignee['is_assigned_from_contact'] == 1)
				{
					if ($assignee['is_assigned_from_contact'] == 0)
					{
						echo '<p class="text-muted task-assigned-from">'._l(
								'task_assigned_from',
								'<a href="'.admin_url('profile/'.$assignee['assigned_from']).'" target="_blank">'.e(
									get_staff_full_name($assignee['assigned_from'])
								)
							).'</a></p>';
					} else
					{
						echo '<p class="text-muted task-assigned-from task-assigned-from-contact">'.e(
								_l('task_assigned_from', get_contact_full_name($assignee['assigned_from']))
							).'<br /><span class="label inline-block mtop5 label-info">'._l(
								'is_customer_indicator'
							).'</span></p>';
					}

					break;
				}
			}
		} ?>
        <div class="tw-flex tw-items-center tw-justify-between tw-space-x-2 rtl:tw-space-x-reverse">
            <h4 class="task-info-heading tw-font-semibold tw-text-base tw-flex tw-items-center tw-text-neutral-800">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="tw-w-5 tw-h-5 tw-text-neutral-500 tw-mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0M3.124 7.5A8.969 8.969 0 015.292 3m13.416 0a8.969 8.969 0 012.168 4.5"/>
                </svg>
				<?= _l('reminders'); ?>
            </h4>

            <button type="button"
                    class="tw-inline-flex tw-bg-transparent tw-border-0 tw-p-1.5 hover:tw-bg-neutral-100 tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 tw-rounded-md ltr:tw-ml-2 rtl:tw-mr-2"
                    onclick="new_task_reminder(<?= e($task->id); ?>); return false;">
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>

		<?php if (count($reminders) == 0) { ?>
            <p class="text-muted tw-mt-0 tw-text-sm">
				<?= _l('no_reminders_for_this_task'); ?>
            </p>
		<?php } else { ?>
            <ul class="mtop10">
				<?php foreach ($reminders as $rKey => $reminder) { ?>
                    <li class="tw-group<?= $reminder['isnotified'] == '1' ? ' tw-line-through' : ''; ?>"
                        data-id="<?= e($reminder['id']); ?>">
                        <div class="mbot15">
                            <div class="tw-flex">
                                <p class="tw-text-neutral-500 tw-font-medium">
									<?= e(_l('reminder_for', [
										get_staff_full_name($reminder['staff']),
										_dt($reminder['date']),
									])); ?>
                                </p>
								<?php if ($reminder['creator'] == get_staff_user_id() || is_admin()) { ?>
                                    <div class="tw-flex tw-space-x-2 rtl:tw-space-x-reverse tw-self-start">
										<?php if ($reminder['isnotified'] == 0) { ?>
                                            <a href="#" class="text-muted tw-opacity-0 group-hover:tw-opacity-100"
                                               onclick="edit_reminder(<?= e($reminder['id']); ?>, this); return false;">
                                                <i class="fa fa-edit"></i>
                                            </a>
										<?php } ?>
                                        <a href="<?= admin_url(
											'tasks/delete_reminder/'.$task->id.'/'.$reminder['id']
										); ?>"
                                           class="text-muted delete-reminder tw-opacity-0 group-hover:tw-opacity-100">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </a>
                                    </div>
								<?php } ?>

                            </div>
                            <div class="tw-truncate">
								<?php if ( ! empty($reminder['description']))
								{
									echo process_text_content_for_display($reminder['description']);
								} else
								{
									echo '<p class="text-muted tw-mb-0">'._l('no_description_provided').'</p>';
								} ?>
                            </div>
							<?php if (count($reminders) - 1 != $rKey) { ?>
                                <hr class="hr-10"/>
							<?php } ?>
                        </div>
                    </li>
					<?php
				} ?>
            </ul>
		<?php } ?>
        <div class="clearfix"></div>
        <div id="newTaskReminderToggle" class="mtop15" style="display:none;">
			<?= form_open('', ['id' => 'form-reminder-task']); ?>
			<?php $this->load->view(
				'admin/includes/reminder_fields',
				['members' => $staff_reminders, 'id' => $task->id, 'name' => 'task']
			); ?>
            <button class="btn btn-primary btn-sm pull-right" type="submit" id="taskReminderFormSubmit">
				<?= _l('create_reminder'); ?>
            </button>
            <div class="clearfix"></div>
			<?= form_close(); ?>
        </div>
        <hr class="task-info-separator"/>
        <div class="clearfix"></div>
        <h4 class="task-info-heading tw-font-semibold tw-text-base tw-flex tw-items-center tw-text-neutral-800 tw-mb-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                 stroke="currentColor" class="tw-w-5 tw-h-5 tw-text-neutral-500 tw-mr-2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
            </svg>
			<?= _l('task_single_assignees'); ?>
        </h4>
		<?php if (staff_can('edit', 'tasks')
			|| ($task->current_user_is_creator && staff_can('create', 'tasks')))
		{ ?>
            <div class="simple-bootstrap-select tw-mb-2">
                <select data-width="100%"
					<?php if ($task->rel_type == 'project') { ?>
                        data-live-search-placeholder="<?= _l('search_project_members'); ?>"
					<?php } ?>
                        data-task-id="<?= e($task->id); ?>"
                        id="add_task_assignees"
                        class="text-muted task-action-select selectpicker"
                        name="select-assignees-modified"
                        data-live-search="true"
                        title='<?= _l('task_single_assignees_select_title'); ?>'
                        data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
					<?php
					$options = '';

					foreach ($staff as $assignee)
					{
						if ( ! in_array($assignee['staffid'], $task->assignees_ids))
						{
							if ($task->rel_type == 'project'
								&& total_rows(
									db_prefix().'project_members',
									['project_id' => $task->rel_id, 'staff_id' => $assignee['staffid']]
								) == 0)
							{
								continue;
							}
							$options .= '<option value="'.$assignee['staffid'].'">'.e(
									$assignee['full_name']
								).'</option>';
						}
					}
					echo $options;
					?>
                </select>
            </div>
		<?php } ?>
        <div class="task_users_wrapper">
			<?php
			$_assignees = '';

			foreach ($task->assignees as $assignee)
			{
				$_remove_assigne = '';
				if (staff_can('edit', 'tasks')
					|| ($task->current_user_is_creator && staff_can('create', 'tasks')))
				{
					$_remove_assigne = ' <a href="#" class="remove-task-user text-danger" onclick="remove_assignee('.$assignee['id'].','.$task->id.'); return false;"><i class="fa fa-remove"></i></a>';
				}
				$_assignees .= '
               <div class="task-user"  data-toggle="tooltip" data-title="'.e($assignee['full_name']).'">
               <a href="'.admin_url('profile/'.$assignee['assigneeid']).'" target="_blank">'.staff_profile_image(
						$assignee['assigneeid'],
						['staff-profile-image-small',]
					).'</a> '.$_remove_assigne.'</span>
               </div>';
			}
			if ($_assignees == '')
			{
				$_assignees = '<div class="text-danger display-block tw-text-sm tw-mb-4">'._l(
						'task_no_assignees'
					).'</div>';
			}
			echo $_assignees;
			?>
        </div>
        <hr class="task-info-separator"/>
        <div class="clearfix"></div>
        <h4 class="task-info-heading tw-font-semibold tw-text-base tw-flex tw-items-center tw-text-neutral-800 tw-mb-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                 stroke="currentColor" class="tw-w-5 tw-h-5 tw-text-neutral-500 tw-mr-2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
            </svg>
			<?= _l('task_single_followers'); ?>
        </h4>
		<?php if (staff_can('edit', 'tasks')
			|| ($task->current_user_is_creator && staff_can('create', 'tasks')))
		{ ?>
            <div class="simple-bootstrap-select tw-mb-2">
                <select data-width="100%"
                        data-task-id="<?= e($task->id); ?>"
                        class="text-muted selectpicker task-action-select"
                        name="select-followers-modified"
                        data-live-search="true"
                        title='<?= _l('task_single_followers_select_title'); ?>'
                        data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
					<?php
					$options = '';

					foreach ($staff as $follower)
					{
						if ( ! in_array($follower['staffid'], $task->followers_ids))
						{
							$options .= '<option value="'.$follower['staffid'].'">'.e(
									$follower['full_name']
								).'</option>';
						}
					}
					echo $options;
					?>
                </select>
            </div>
		<?php } ?>
        <div class="task_users_wrapper">
			<?php
			$_followers = '';

			foreach ($task->followers as $follower)
			{
				$_remove_follower = '';
				if (staff_can('edit', 'tasks')
					|| ($task->current_user_is_creator && staff_can('create', 'tasks')))
				{
					$_remove_follower = ' <a href="#" class="remove-task-user text-danger" onclick="remove_follower('.$follower['id'].','.$task->id.'); return false;"><i class="fa fa-remove"></i></a>';
				}
				$_followers .= '
                <span class="task-user" data-toggle="tooltip" data-title="'.e($follower['full_name']).'">
                <a href="'.admin_url('profile/'.$follower['followerid']).'" target="_blank">'.staff_profile_image(
						$follower['followerid'],
						['staff-profile-image-small',]
					).'</a> '.$_remove_follower.'</span>
                </span>';
			}
			if ($_followers == '')
			{
				$_followers = '<div class="display-block tw-text-neutral-600 tw-text-sm tw-mb-4">'._l(
						'task_no_followers'
					).'</div>';
			}
			echo $_followers;
			?>
        </div>
        <hr class="task-info-separator"/>
		<?= form_open_multipart('admin/tasks/upload_file', ['id' => 'task-attachment', 'class' => 'dropzone tw-mt-5']
		); ?>
		<?= form_close(); ?>
        <div class="tw-my-2 tw-inline-flex tw-items-end tw-w-full tw-flex-col tw-space-y-2 tw-justify-end">
            <button class="gpicker">
                <i class="fa-brands fa-google" aria-hidden="true"></i>
				<?= _l('choose_from_google_drive'); ?>
            </button>
            <div id="dropbox-chooser-task"></div>
        </div>
    </div>
</div>
</div>
<style>
    span.mention {
        background-color: #eeeeee;
        padding: 3px;
    }

    img {
        width: 100%;
        max-width: 100%;
    }

    .task-comment {

    }
</style>
<script>

    // On click on task comment textarea make it tinymce, by default is plain textarea
    $("body").on("click focus", "#task_comment", function (e) {
        init_new_task_comment();
    });

    $('body .task-view-collapse').click();

    function init_new_task_comment(manual) {
        if (tinymce.get('task_comment')) {
            tinymce.remove("#task_comment");
        }

        if (typeof taskCommentAttachmentDropzone != "undefined") {
            taskCommentAttachmentDropzone.destroy();
        }

        $("#dropzoneTaskComment").removeClass("hide");
        $("#addTaskCommentBtn").removeClass("hide");

        taskCommentAttachmentDropzone = new Dropzone(
            "#task-comment-form",
            appCreateDropzoneOptions({
                uploadMultiple: true,
                clickable: "#dropzoneTaskComment",
                previewsContainer: ".dropzone-task-comment-previews",
                autoProcessQueue: false,
                addRemoveLinks: true,
                parallelUploads: 20,
                maxFiles: 20,
                paramName: "file",
                sending: function (file, xhr, formData) {
                    formData.append(
                        "taskid",
                        $("#addTaskCommentBtn").attr("data-comment-task-id")
                    );
                    if (tinyMCE.activeEditor) {
                        formData.append("content", tinyMCE.activeEditor.getContent());
                    } else {
                        formData.append("content", $("#task_comment").val());
                    }
                },
                success: function (files, response) {
                    response = JSON.parse(response);
                    if (
                        this.getUploadingFiles().length === 0 &&
                        this.getQueuedFiles().length === 0
                    ) {
                        _task_append_html(response.taskHtml);
                        tinymce.remove("#task_comment");
                    }
                },
            })
        );

        var editorConfig = _image_pasting_editor_config();

        editorConfig.toolbar_sticky = true

        if (typeof manual == "undefined" || manual === false) {
            editorConfig.auto_focus = true;
        }

        var taskid = $("#task-modal #taskid").val();
        editorConfig.setup = function (editor) {
            initializeTinyMceMentions(editor, function () {
                return $.getJSON(
                    admin_url + "tasks/get_staff_names_for_mentions/" + taskid
                )
            })
        };

        init_editor("#task_comment", editorConfig)
    }

    // Init task edit comment
    function edit_task_comment(id) {
        var edit_wrapper = $('[data-edit-comment="' + id + '"]');
        edit_wrapper.next().addClass("hide");
        edit_wrapper.removeClass("hide");

        tinymce.remove("#task_comment_" + id);
        var editorConfig = _image_pasting_editor_config();
        editorConfig.auto_focus = "task_comment_" + id;
        editorConfig.toolbar_sticky = true;
        editorConfig.height = 400;
        editorConfig.content_style =
            "span.mention {\
				background-color: #eeeeee;\
				padding: 3px;\
			}\
			img {\
			    width: 90%;\
			    max-width: 90%;\
			    padding: 0 5%;\
			}";
        init_editor("#task_comment_" + id, editorConfig);
        tinymce.triggerSave();
    }

    if (typeof (targetComment) == 'undefined') {
        let targetComment = window.location.hash;
        if (targetComment.includes("#comment_")) {
            $(targetComment).addClass('active');
            $(targetComment).closest('.children-comments-wrapper').removeClass('hide').show();
            $(targetComment).closest('.parent-comment').find('i.fa-chevron-up,i.fa-chevron-down').toggleClass('hide');
            console.log($(targetComment).closest('.parent-comment').find('i'))
        }
    } else if (targetComment.includes("#comment_")) {
        $(targetComment).addClass('active');
        $(targetComment).closest('.children-comments-wrapper').removeClass('hide').show();
        $(targetComment).closest('.parent-comment').find('i.fa-chevron-up,i.fa-chevron-down').toggleClass('hide');
        console.log($(targetComment).closest('.parent-comment').find('i'))

    }

    if (typeof (commonTaskPopoverMenuOptions) == 'undefined') {
        var commonTaskPopoverMenuOptions = {
            html: true,
            placement: 'bottom',
            trigger: 'click',
            template: '<div class="popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"></div></div></div>',
        };
    }

    // Clear memory leak
    if (typeof (taskPopoverMenus) == 'undefined') {
        var taskPopoverMenus = [{
            selector: '.task-menu-options',
            title: "<?= _l('actions'); ?>",
        },
            {
                selector: '.task-menu-status',
                title: "<?= _l('ticket_single_change_status'); ?>",
            },
            {
                selector: '.task-menu-priority',
                title: "<?= _l('task_single_priority'); ?>",
            },
            {
                selector: '.task-menu-milestones',
                title: "<?= _l('task_milestone'); ?>",
            },
        ];
    }

    for (var i = 0; i < taskPopoverMenus.length; i++) {
        $(taskPopoverMenus[i].selector + ' .trigger').popover($.extend({}, commonTaskPopoverMenuOptions, {
            title: taskPopoverMenus[i].title,
            content: $('body').find(taskPopoverMenus[i].selector + ' .content-menu').html()
        }));
    }

    if (typeof (Dropbox) != 'undefined') {
        document.getElementById("dropbox-chooser-task").appendChild(Dropbox.createChooseButton({
            success: function (files) {
                taskExternalFileUpload(files,
                    'dropbox', <?= e($task->id); ?>);
            },
            linkType: "preview",
            extensions: app.options.allowed_files.split(','),
        }));
    }

    init_selectpicker();
    init_datepicker();
    init_lightbox();

    tinyMCE.remove('#task_view_description');

    if (typeof (taskAttachmentDropzone) != 'undefined') {
        taskAttachmentDropzone.destroy();
        taskAttachmentDropzone = null;
    }

    taskAttachmentDropzone = new Dropzone("#task-attachment", appCreateDropzoneOptions({
        uploadMultiple: true,
        parallelUploads: 20,
        maxFiles: 20,
        paramName: 'file',
        sending: function (file, xhr, formData) {
            formData.append("taskid",
                '<?= e($task->id); ?>');
        },
        success: function (files, response) {
            response = JSON.parse(response);
            if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                _task_append_html(response.taskHtml);
            }
        }
    }));

    $('#task-modal').find('.gpicker').googleDrivePicker({
        onPick: function (pickData) {
            taskExternalFileUpload(pickData,
                'gdrive', <?= e($task->id); ?>);
        }
    });

    $('.edit-timesheet-cancel').click(function () {
        $('.timesheet-edit').addClass('hide');
        $('.add-timesheet').removeClass('hide');
    });

    $('.task-single-edit-timesheet').click(function () {
        var edit_timesheet_id = $(this).data('timesheet-id');
        $('.timesheet-edit, .add-timesheet').addClass('hide');
        $('.task-modal-edit-timesheet-' + edit_timesheet_id).removeClass('hide');
    });

    $('.task-modal-edit-timesheet-form').submit(event => {
        event.preventDefault();
        $('.edit-timesheet-submit').prop('disabled', true);

        var form = new FormData(event.target);
        var data = {};

        data.timer_id = form.get('timer_id');
        data.start_time = form.get('start_time');
        data.end_time = form.get('end_time');
        data.timesheet_staff_id = form.get('staff_id');
        data.timesheet_task_id = form.get('task_id');
        data.note = form.get('note');

        $.post(admin_url + 'project_management_enhancements/tasks/update_timesheet', data).done(function (response) {
            response = JSON.parse(response);
            if (response.success === true || response.success == 'true') {
                init_task_modal(data.timesheet_task_id);
                alert_float('success', response.message);
            } else {
                alert_float('warning', response.message);
            }
            $('.edit-timesheet-submit').prop('disabled', false);
        });
    });

    // Add new task comment from the modal
    function add_task_comment(task_id) {
        var data = {};

        if (taskCommentAttachmentDropzone.files.length > 0) {
            taskCommentAttachmentDropzone.processQueue(task_id);
            return;
        }
        if (tinymce.activeEditor) {
            data.content = tinyMCE.activeEditor.getContent();
        } else {
            data.content = $("#task_comment").val();
            data.no_editor = true;
        }
        data.taskid = task_id;
        $.post(admin_url + "project_management_enhancements/tasks/add_task_comment", data).done(function (response) {
            response = JSON.parse(response);
            _task_append_html(response.taskHtml);
            // init_task_modal(task_id, )
            // Remove task comment editor instance
            // Causing error because of are you sure you want to leave this page, the plugin still sees as active and dirty.
            tinymce.remove("#task_comment");
        });
    }

    // Save task edited comment
    function save_edited_comment(id, task_id) {
        tinymce.triggerSave();
        var data = {};
        data.id = id;
        data.task_id = task_id;
        data.content = $('[data-edit-comment="' + id + '"]')
            .find("textarea")
            .val();
        if (is_ios()) {
            data.no_editor = true;
        }
        $.post(admin_url + "project_management_enhancements/tasks/edit_comment", data).done(function (response) {
            response = JSON.parse(response);
            if (response.success === true || response.success == "true") {
                alert_float("success", response.message);
                _task_append_html(response.taskHtml);
            } else {
                cancel_edit_comment(id);
            }
            tinymce.remove('[data-edit-comment="' + id + '"] textarea');
        });
    }

    function task_mark_as(status, task_id, url) {
        url =
            typeof url == "undefined" ? "project_management_enhancements/tasks/mark_as/" + status + "/" + task_id : url;
        var taskModalVisible = $("#task-modal").is(":visible");
        url += "?single_task=" + taskModalVisible;
        $("body").append('<div class="dt-loader"></div>');
        requestGetJSON(url).done(function (response) {
            $("body").find(".dt-loader").remove();
            if (response.success === true || response.success == "true") {
                reload_tasks_tables();
                if (taskModalVisible) {
                    _task_append_html(response.taskHtml);
                }
                if (
                    status == 5 &&
                    typeof _maybe_remove_task_from_project_milestone == "function"
                ) {
                    _maybe_remove_task_from_project_milestone(task_id);
                }
                if ($(".tasks-kanban").length === 0) {
                    alert_float("success", response.message);
                }
            }
        });
    }


    // Change task priority from sigle modal
    function task_change_priority(priority_id, task_id) {
        url = "project_management_enhancements/tasks/change_priority/" + priority_id + "/" + task_id;
        var taskModalVisible = $("#task-modal").is(":visible");
        url += "?single_task=" + taskModalVisible;
        requestGetJSON(url).done(function (response) {
            if (response.success === true || response.success == "true") {
                reload_tasks_tables();
                if (taskModalVisible) {
                    _task_append_html(response.taskHtml);
                }
            }
        });
    }

    // Deletes task comment from database
    function remove_task_comment(commentid) {
        if (confirm_delete()) {
            requestGetJSON("project_management_enhancements/tasks/remove_comment/" + commentid).done(function (
                response
            ) {
                if (response.success === true || response.success == "true") {
                    $('[data-commentid="' + commentid + '"]').remove();
                    $('[data-comment-attachment="' + commentid + '"]').remove();
                    _task_attachments_more_and_less_checks();
                }
            });
        }
    }

    // Remove task assignee
    function remove_assignee(id, task_id) {
        if (confirm_delete()) {
            requestGetJSON("project_management_enhancements/tasks/remove_assignee/" + id + "/" + task_id).done(
                function (response) {
                    if (response.success === true || response.success == "true") {
                        alert_float("success", response.message);
                        _task_append_html(response.taskHtml);
                    }
                }
            );
        }
    }

    // Remove task follower
    function remove_follower(id, task_id) {
        if (confirm_delete()) {
            requestGetJSON("project_management_enhancements/tasks/remove_follower/" + id + "/" + task_id).done(
                function (response) {
                    if (response.success === true || response.success == "true") {
                        alert_float("success", response.message);
                        _task_append_html(response.taskHtml);
                    }
                }
            );
        }
    }

    // Marking task as complete
    function mark_complete(task_id) {
        task_mark_as(5, task_id);
    }

    // Unmarking task as complete
    function unmark_complete(task_id) {
        task_mark_as(4, task_id, "project_management_enhancements/tasks/unmark_complete/" + task_id);
    }


    // Task single edit description with inline editor, used from task single modal
    function edit_task_description_inline(e, id) {
        tinyMCE.remove("#task_view_description");

        if ($(e).hasClass("editor-initiated")) {
            $(e).removeClass("editor-initiated");
            return;
        }

        $(e).addClass("editor-initiated");

        $.Shortcuts.stop();

        tinymce.init({
            branding: false,
            toolbar: false,
            menubar: false,
            inline: true,
            cache_suffix: '?v=' + app.version,
            selector: "#task_view_description",
            theme: "silver",
            directionality: isRTL == "true" ? "rtl" : "",
            auto_focus: "task_view_description",
            plugins: ['quickbars', 'link', 'table', (isRTL == "true" ? " directionality" : "")],
            contextmenu: "link table paste pastetext",
            quickbars_insert_toolbar: "quicktable",
            quickbars_selection_toolbar: "bold italic | quicklink h2 h3 blockquote",
            table_default_styles: {
                width: "100%",
            },
            setup: function (editor) {
                editor.on("blur", function (e) {
                    if (editor.isDirty()) {
                        $.post(admin_url + "project_management_enhancements/tasks/update_task_description/" + id, {
                            description: editor.getContent(),
                        });
                    }

                    setTimeout(function () {
                        editor.remove();
                        $.Shortcuts.start();
                    }, 500);
                });
            },
        });
    }


    // Action for task timer start/stop
    function timer_action(e, task_id, timer_id, adminStop) {
        timer_id = typeof timer_id == "undefined" ? "" : timer_id;

        var $timerSelectTask = $("#timer-select-task");
        if (task_id === "" && $timerSelectTask.is(":visible")) {
            return;
        }
        if (timer_id !== "" && task_id == "0") {
            var popupData = {};
            popupData.content = "";
            popupData.content += '<div class="row">';
            popupData.content += '<div class="form-group">';
            if (app.options.has_permission_create_task == "1") {
                popupData.content +=
                    '<div class="input-group" style="margin:0 auto;width:60%;">';
            }
            popupData.content +=
                '<select id="timer_add_task_id" data-empty-title="' +
                app.lang.search_tasks +
                '" data-width="60%" class="ajax-search" data-live-search="true">';
            popupData.content += "</select>";
            if (app.options.has_permission_create_task == "1") {
                popupData.content +=
                    '<div class="input-group-addon" style="opacity: 1;">';
                popupData.content +=
                    '<a href="#" onclick="new_task(\'tasks/task\',' +
                    timer_id +
                    '); return false;"><i class="fa fa-plus"></i></a>';
                popupData.content += "</div>";
            }
            popupData.content += "</div></div>";
            popupData.content += '<div class="form-group">';
            popupData.content +=
                '<textarea id="timesheet_note" placeholder="' +
                app.lang.note +
                '" style="margin:0 auto;width:60%;" rows="4" class="form-control"></textarea>';
            popupData.content += "</div>";
            popupData.content +=
                "<button type='button' onclick='timer_action(this,document.getElementById(\"timer_add_task_id\").value," +
                timer_id +
                ");return false;' class='btn btn-primary'>" +
                app.lang.confirm +
                "</button>";
            popupData.message = app.lang.task_stop_timer;
            var $popupHTML = system_popup(popupData);
            $popupHTML.attr("id", "timer-select-task");
            init_ajax_search(
                "tasks",
                "#timer_add_task_id",
                undefined,
                admin_url + "tasks/ajax_search_assign_task_to_timer"
            );
            return false;
        }

        $(e).addClass("disabled");

        var data = {};
        data.task_id = task_id;
        data.timer_id = timer_id;
        data.note = $("body").find("#timesheet_note").val();
        if (!data.note) {
            data.note = "";
        }
        var taskModalVisible = $("#task-modal").is(":visible");
        var reqUrl =
            admin_url + "project_management_enhancements/tasks/timer_tracking?single_task=" + taskModalVisible;
        if (adminStop) {
            reqUrl += "&admin_stop=" + adminStop;
        }
        $.post(reqUrl, data).done(function (response) {
            response = JSON.parse(response);

            // Timer action, stopping from staff/member/id
            if ($("body").hasClass("member")) {
                window.location.reload();
            }

            if (taskModalVisible) {
                _task_append_html(response.taskHtml);
            }

            if ($timerSelectTask.is(":visible")) {
                $timerSelectTask.find(".system-popup-close").click();
            }

            _init_timers_top_html(JSON.parse(response.timers));

            $(".popover-top-timer-note").popover("hide");
            reload_tasks_tables();
        });
    }

    // On click on task comment textarea make it tinymce, by default is plain textarea
    $("body").on("click focus", 'textarea[name="reply_comment"]', function (e) {
        reply_task_comment($(this).data('comment-id'));
    });

    // Init task edit comment
    function reply_task_comment(id) {
        $("#task_reply_comment_" + id).addClass('hide');

        var edit_wrapper = $('[data-reply-comment="' + id + '"]');
        edit_wrapper.next().addClass("hide");
        edit_wrapper.removeClass("hide");
        tinymce.remove("#task_reply_comment_editor_" + id);
        var editorConfig = _image_pasting_editor_config();
        editorConfig.content_style =
            "span.mention {\
				background-color: #eeeeee;\
				padding: 3px;\
			}\
			img {\
			    width: 90%;\
			    max-width: 90%;\
			    padding: 0 5%;\
			}";
        editorConfig.auto_focus = "task_reply_comment_editor_" + id;
        editorConfig.toolbar_sticky = true;
        init_editor("#task_reply_comment_editor_" + id, editorConfig);
        tinymce.triggerSave();
    }


    // Cancel editing commment after clicked on edit href
    function cancel_reply_comment(id) {
        $("#task_reply_comment_" + id).removeClass('hide');

        var edit_wrapper = $('[data-reply-comment="' + id + '"]');
        tinymce.remove('[data-reply-comment="' + id + '"] textarea');
        edit_wrapper.addClass("hide");
        edit_wrapper.next().removeClass("hide");
    }

    // Save task edited comment
    function save_reply_comment(id, task_id, comment_id) {
        tinymce.triggerSave();
        var data = {};
        data.parent_id = id;
        data.taskid = task_id;
        data.content = $('[data-reply-comment="' + id + '"]')
            .find("textarea")
            .val();
        if (is_ios()) {
            data.no_editor = true;
        }
        $.post(admin_url + "project_management_enhancements/tasks/reply_task_comment", data).done(function (response) {
            response = JSON.parse(response);
            if (response.success === true || response.success == "true") {
                alert_float("success", response.message);
                _task_append_html(response.taskHtml);
            } else {
                cancel_reply_comment(id);
            }
            tinymce.remove('[data-reply-comment="' + id + '"] textarea');
        });
    }

    function _image_pasting_editor_config() {
        return {
            toolbar_sticky: true,
            height: 400,
            content_style:
                "span.mention {\
					background-color: #eeeeee;\
					padding: 3px;\
				}\
				img {\
					width: 90%;\
					max-width: 90%;\
					padding: 0 5%;\
				}",
            forced_root_block: "p",
            menubar: false,
            autoresize_bottom_margin: 15,
            plugins: [
                "quickbars", "table", "advlist", "codesample", "autosave", "lists", "link", "image", "media",
            ],
            toolbar: "blocks image media alignleft aligncenter alignright bullist numlist restoredraft",
            quickbars_insert_toolbar: 'image quicktable | hr',
            quickbars_selection_toolbar: "bold italic | forecolor backcolor | quicklink | h2 h3 | codesample",
            browser_spellcheck: true,
            paste_data_images: true,
            paste_preprocess: (plugin, args) => {
                if (args.content.includes('<img src="blob:')) {
                    args.preventDefault()

                    const editor = tinymce.get(args.target.id);
                    let form = $('#' + args.target.id).closest('.simple-editor').find('form');
                    if (form === undefined || form.length === 0) {
                        form = $('#task-form');
                    }
                    let csrfToken = $(form).find('input[name="csrf_token_name"]').val();
                    let taskId = $(form).find('button').last().data("comment-task-id");

                    if (
                        (taskId === null || taskId === undefined)
                        && (
                            form === null
                            || form === undefined
                            || form.length === 0
                        )
                    ) {
                        return false;
                    }

                    let imageWrapper = document.createElement('div');
                    imageWrapper.innerHTML = args.content;
                    let imageElement = imageWrapper.querySelector('img');
                    let imageSrc = imageElement.src;

                    args.content = "";
                    let getImageFile = createFile(imageSrc)
                    getImageFile.then((image) => {
                        if (taskId !== undefined || form !== undefined) {
                            let formData = new FormData();
                            formData.append('taskid', taskId);
                            formData.append('csrf_token_name', csrfToken);
                            formData.append('image', image);
                            jQuery.ajax({
                                url: admin_url + 'project_management_enhancements/tasks/add_task_comment_image',
                                data: formData,
                                cache: false,
                                contentType: false,
                                processData: false,
                                method: 'POST',
                                success: function (data) {
                                    let response = JSON.parse(data);
                                    setContentData(response);
                                },
                                error: function (data) {
                                    console.log(data)
                                }
                            });
                        } else {
                            let convert = toBase64(image);
                            convert.then((content) => {
                                let dataToSet = {
                                    success: true,
                                    url: content
                                }
                                setContentData(dataToSet);
                            })
                        }
                    });

                    async function createFile(localBlob) {
                        let response = await fetch(localBlob);
                        let data = await response.blob();
                        let metadata = {
                            type: 'image/png'
                        };
                        return new File([data], "comment_img.jpg", metadata);
                    }

                    const toBase64 = file => new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = () => resolve(reader.result);
                        reader.onerror = reject;
                    });

                    function setContentData(data) {
                        if (data.success) {
                            const image = document.createElement('img');
                            image.src = data.url;
                            let formattedImage = '<a class="comment-image" href="' + data.url + '" data-lightbox="comment-images">' + image.outerHTML + "</a>";
                            editor.insertContent(formattedImage);
                        } else {
                            args.content = "";
                        }
                    }
                } else if (args.content.includes('loom.com/share/')) {
                    args.preventDefault();
                    tinymce.activeEditor.windowManager.confirm('Would you like to embed media from this site?', (state) => {
                        if (state) {
                            let videoUrl = args.content;
                            videoUrl = videoUrl.replace('loom.com/share/', 'loom.com/embed/');
                            if (!videoUrl.includes('?sid=')){
                                videoUrl += '?sid=' + uuidv4();
                            }
                            let embedded = '<p><iframe src="' + videoUrl + '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen style="top: 0; left: 0; bottom:0; right:0;min-width: 100%; min-height: 400px; width: 100%; height: 100%;"></iframe></p>';
                            embedded = "<p>&nbsp;</p>" + embedded + "<p>&nbsp;</p>";
                            console.log(embedded);
                            tinymce.get(args.target.id).insertContent(embedded);
                        } else {
                            tinymce.get(args.target.id).insertContent(args.content);
                        }
                    });
                }
            }
        };
    }

    function uuidv4() {
        return "10000000-1000-4000-8000-100000000000".replace(/[018]/g, c =>
            (+c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> +c / 4).toString(16)
        );
    }

</script>