<?php

/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Project Management Enhancements
Description: Project Management Enhancements
Version: 1.2.6
Requires at least: 1.0.*
*/

const PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME = 'project_management_enhancements';

$CI = &get_instance();
/**
 * Load the module helper
 */
$CI->load->helper(PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME.'/task_comment');
$CI->load->helper(PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME.'/migration_log');


register_language_files('project_management_enhancements', ['project_management_enhancements']);

function pme_module_activation_hook()
{
	$CI = &get_instance();
	require_once(__DIR__.'/install.php');
}

register_activation_hook(PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME, 'pme_module_activation_hook');

function pme_module_register_uninstall_hook()
{
	if (get_option('pme_migrated_database'))
	{
		require_once APP_MODULES_PATH.'project_management_enhancements/migrations/100_version_100.php';

		$migration = new Migration_Version_100();
		$migration->down();
	}
}

register_uninstall_hook(PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME, 'pme_module_register_uninstall_hook');

function app_init_required_services()
{
	require_once APP_MODULES_PATH.PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME.'/libraries/Zegaware_license.php';
}

hooks()->add_action('app_init', 'app_init_required_services');

function pme_redirect_task_routes()
{
	$schema = ($_SERVER['HTTPS'] ?? '') == 'off' ? 'http' : 'https';
	$request_uri = sprintf('%s://%s%s', $schema, $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
	if (str_contains($request_uri, '/admin/tasks/get_task_data') && empty($_POST ?? ''))
	{
		$request_uri = str_replace(
			admin_url('tasks/get_task_data'),
			admin_url('project_management_enhancements/tasks/get_task_data'),
			$request_uri
		);

		redirect($request_uri);
		exit();
	}

	if (str_contains($request_uri, '/admin/tasks/task') && empty($_POST ?? ''))
	{
		$request_uri = str_replace(
			admin_url('tasks/task'),
			admin_url('project_management_enhancements/tasks/task'),
			$request_uri
		);

		redirect($request_uri);
		exit();
	}
}

hooks()->add_action('admin_init', 'pme_redirect_task_routes');


function pme_redirect_project_routes()
{
	$schema = ($_SERVER['HTTPS'] ?? '') == 'off' ? 'http' : 'https';
	$request_uri = sprintf('%s://%s%s', $schema, $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
	if (str_contains($request_uri, '/admin/projects/project') && empty($_POST ?? ''))
	{
		$request_uri = str_replace(
			admin_url('projects/project'),
			admin_url('project_management_enhancements/projects/project'),
			$request_uri
		);

		redirect($request_uri);
		exit();
	}
}

hooks()->add_action('admin_init', 'pme_redirect_project_routes');

function project_management_enhancements_add_custom_styles()
{ ?>
    <style>
        .task-view-collapse {
            top: 1rem;
            right: 1rem;
        }

        .task-view-collapse:not(.collapsed) i.fa-chevron-left {
            display: none;
        }

        .task-view-collapse.collapsed i.fa-chevron-right {
            display: none;
        }

        .task-single-col-right {
            display: none;
        }

        @media (max-width: 991px) {
            .task-view-collapse {
                display: none;
            }
        }

        @media (max-width: 991px) {
            .task-view-collapse {
                display: none;
            }

            .task-single-col-right {
                display: block;
            }
        }

        .tc-content.task-comment.active,
        .tc-content.task-parent-comment.active,
        .tc-content.task-child-comment.active {
            background-color: #fffb006b;
        }

        #task-comment-form iframe #tinymce img {
            width: 100% !important;
        }

        .task-child-comment {
            padding: 5px 10px;
        }
    </style>
	<?php
}

hooks()->add_action('app_admin_head', 'project_management_enhancements_add_custom_styles');

function project_management_enhancements_add_custom_scripts()
{
	?>
    <script>
        $(function () {
            $(document).ready(function () {
                $('body').on('click', '.task-view-collapse', function () {
                    const $parent = $(this).closest('.row');
                    $(this).toggleClass("collapsed");
                    if ($(this).hasClass("collapsed")) {
                        $parent.find('.task-single-col-left').removeClass('col-md-8').addClass('col-md-12');
                        $parent.find('.task-single-col-right').removeClass('col-md-4');
                        $parent.find('.task-single-col-right').hide();
                    } else {
                        $parent.find('.task-single-col-left').removeClass('col-md-12').addClass('col-md-8');
                        $parent.find('.task-single-col-right').addClass('col-md-4');
                        $parent.find('.task-single-col-right').show();
                    }
                })

                // Assign task to staff member
                $("body").on("change", 'select[name="select-assignees-modified"]', function () {
                    $("body").append('<div class="dt-loader"></div>');
                    var data = {};
                    data.assignee = $('select[name="select-assignees-modified"]').val();
                    if (data.assignee !== "") {
                        data.taskid = $(this).attr("data-task-id");
                        $.post(admin_url + "project_management_enhancements/tasks/add_task_assignees", data).done(function (
                            response
                        ) {
                            $("body").find(".dt-loader").remove();
                            response = JSON.parse(response);
                            reload_tasks_tables();
                            init_task_modal(data.taskid)
                        });
                    }
                });

                // Add follower to task
                $("body").on("change", 'select[name="select-followers-modified"]', function () {
                    var data = {};
                    data.follower = $('select[name="select-followers-modified"]').val();
                    if (data.follower !== "") {
                        data.taskid = $(this).attr("data-task-id");
                        $("body").append('<div class="dt-loader"></div>');
                        $.post(admin_url + "project_management_enhancements/tasks/add_task_followers", data).done(function (
                            response
                        ) {
                            response = JSON.parse(response);
                            $("body").find(".dt-loader").remove();
                            _task_append_html(response.taskHtml);
                            init_task_modal(data.taskid)
                        });
                    }
                });
            });
        });
    </script>
	<?php
}

hooks()->add_action('app_admin_footer', 'project_management_enhancements_add_custom_scripts');

function project_management_enhancements_licence_menu_item()
{
	if (is_admin())
	{
		$CI = &get_instance();

		$CI->app_menu->add_sidebar_menu_item('pme-license', [
			'name' => _l('pme_zegaware_license'),
			// The name if the item
			'href' => admin_url(PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME.'/license'),
			// URL of the item
			'position' => 35,
			'icon' => 'fa fa-key',
			// Font awesome icon
		]);
	}
}

hooks()->add_action('admin_init', 'project_management_enhancements_licence_menu_item', 20);

function zegaware_pme_check_license()
{
	$request_uri = $_SERVER['REQUEST_URI'];

	if (str_contains($request_uri, '/admin/'.PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME)
		|| str_contains($request_uri, '/admin/tasks'))
	{
		$is_activated = Zegaware_license::is_activated(PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME);

		if ( ! $is_activated
			&& ! str_contains($request_uri, '/admin/'.PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME.'/license'))
		{
			redirect(admin_url(PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME.'/license'));
			exit();
		}

		if ($is_activated)
		{
			$last_validate = get_option(PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME.'_last_validate');

			if (empty($last_validate))
			{
				validate_zegaware_pme_license();
			} else
			{
				$last_validate = json_decode($last_validate);

				if ( ! isset($last_validate->date) || $last_validate->date !== date('Y-m-d'))
				{
					validate_zegaware_pme_license();
				}
			}
		}
	}
}

hooks()->add_action('admin_init', 'zegaware_pme_check_license');

function validate_zegaware_pme_license(): bool
{
	$validated = Zegaware_license::validate_current_license(PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME);
	if ( ! $validated)
	{
		update_option(
			PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME.'_last_validate',
			json_encode(['date' => date('Y-m-d'), 'msg' => 'error'])
		);
		set_alert('danger', _l('require_license'));
		redirect(admin_url(PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME.'/license'));
	}

	return $validated;
}

function pme_add_project_tab()
{
	$CI = &get_instance();

	$CI->app_tabs->add_project_tab('settings', [
		'name' => _l('settings_group_project_management_enhancements_settings'),
		'icon' => 'fa-solid fa-gear',
		'position' => 70,
		'collapse' => TRUE,
	]);


	$CI->app_tabs->add_project_tab_children_item('settings', [
		'slug' => 'task_types',
		'name' => _l('settings_group_project_management_enhancements'),
		'view' => PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME.'/projects/task_types_view',
		'position' => 5,
		'icon' => 'fa-solid fa-tag',
	]);
}

hooks()->add_action('admin_init', 'pme_add_project_tab', 11);

function add_init_data_table_for_task_types()
{
	if ( ! isset($_REQUEST['group']) || $_REQUEST['group'] != 'task_types')
	{
		return;
	}
	?>
    <script>
        $(function () {
            var project_id = $('input[name="project_id"]').val();
            initDataTable(
                ".table-project-task-types",
                admin_url + "project_management_enhancements/projects/task_types/" + project_id,
                undefined,
                undefined,
                "undefined",
                [1, "desc"]
            );


            appValidateForm(
                $("#task_type_form"),
                {
                    name: "required",
                    label_color: "required",
                    sort_order: "required",
                    project_id: "required",
                },
                manage_task_type
            );

            function manage_task_type(form) {
                var data = $(form).serialize();
                var url = form.action;
                $.post(url, data).done(function (response) {
                    response = JSON.parse(response);
                    if (response.success == true) {
                        alert_float("success", response.message);
                    } else if (response.message) {
                        alert_float("danger", response.message);
                    }

                    $(".table-project-task-types").DataTable().ajax.reload(null, false)
                    $("#task_type").modal("hide")
                    $("#task_type_form").find('button[type="submit"]').button("reset")

                    $('#task_type_form').trigger('reset')
                    $('#task_type_form input[name="label_color"]').trigger('change')
                    $('#task_type_form input[name="text_color"]').trigger('change')
                });
                return false;
            }
        })
    </script>
	<?php
}

hooks()->add_action('app_admin_footer', 'add_init_data_table_for_task_types');

function add_project_default_task_types($project_id): void
{
	$CI = &get_instance();

	$CI->db->reset_query();

	$default_types = array(
		array(
			'name' => 'Bug',
			'color' => '#FF5861',
			'sort_order' => 1,
		),
		array(
			'name' => 'Feature',
			'color' => '#00B6FF',
			'sort_order' => 2,
		),
		array(
			'name' => 'Task',
			'color' => '#00A96E',
			'sort_order' => 3,
		),
	);

	foreach ($default_types as $type)
	{
		$query = sprintf(
			"INSERT INTO %stask_types (`name`, `label_color`, `sort_order`, `editable`) VALUES ('%s','%s',%d,1)",
			db_prefix(),
			$type['name'],
			$type['color'],
			$type['sort_order'],
		);
		$CI->db->query($query);
		$insert_id = $CI->db->insert_id();
		$CI->db->reset_query();

		$query = sprintf(
			"INSERT INTO %sproject_task_types (`project_id`, `task_type_id`) VALUES (%s,%s)",
			db_prefix(),
			$project_id,
			$insert_id
		);
		$CI->db->query($query);

		$CI->db->reset_query();
	}
}

hooks()->add_action('after_add_project', 'add_project_default_task_types');

function add_task_type_to_tasks_table_columns($table_data)
{
	$task_type = _l('project_task_type');
	array_splice($table_data, 3, 0, $task_type);

	return $table_data;
}

hooks()->add_filter('tasks_table_columns', 'add_task_type_to_tasks_table_columns');
hooks()->add_filter('tasks_related_table_columns', 'add_task_type_to_tasks_table_columns');

function add_task_type_to_tasks_table($aColumns)
{
	$select_type_name = 'SELECT GROUP_CONCAT('.db_prefix().'task_types.name)';
	$select_label_color = 'SELECT GROUP_CONCAT('.db_prefix().'task_types.label_color)';
	$select_text_color = 'SELECT GROUP_CONCAT('.db_prefix().'task_types.text_color)';

	$task_types_table = db_prefix().'task_types';

	$aColumns[] = "( $select_label_color FROM $task_types_table WHERE $task_types_table.project_id = rel_id AND $task_types_table.id = task_type ) as task_type_label_color";
	$aColumns[] = "( $select_text_color FROM $task_types_table WHERE $task_types_table.project_id = rel_id AND $task_types_table.id = task_type ) as task_type_text_color";

	$task_type_name_column = array("( $select_type_name FROM $task_types_table WHERE $task_types_table.project_id = rel_id AND $task_types_table.id = task_type ) as task_type_name");

	array_splice($aColumns, 3, 0, $task_type_name_column);


	$_POST['columns'][10] = array
	(
		'data' => 10,
		'name' => '',
		'searchable' => TRUE,
		'orderable' => TRUE,
		'search' => array
		(
			'value' => '',
			'regex' => FALSE,
		),
	);

	$_POST['columns'][11] = array
	(
		'data' => 10,
		'name' => '',
		'searchable' => TRUE,
		'orderable' => TRUE,
		'search' => array
		(
			'value' => '',
			'regex' => FALSE,
		),
	);

	return $aColumns;
}

hooks()->add_filter('tasks_table_sql_columns', 'add_task_type_to_tasks_table');
hooks()->add_filter('tasks_related_table_sql_columns', 'add_task_type_to_tasks_table');

function add_task_type_to_tasks_table_row_data($row, $aRow)
{
	$read_recent = hooks()->apply_filters('user_read_recent_change_status', FALSE, $aRow['id'], get_staff_user_id());

	if ( ! empty($aRow['task_type_label_color']) && ! empty($aRow['task_type_name']))
	{
		$task_type = array(
			sprintf(
				'<span class="label '.($read_recent ? '' : 'recent-change').'" style="background-color: %s;border:1px solid %s;color:%s">%s</span>%s',
				$aRow['task_type_label_color'],
				$aRow['task_type_label_color'],
				$aRow['task_type_text_color'],
				$aRow['task_type_name'],
				'<script>$("span.recent-change").each(function(){$(this).closest("tr").addClass("recent-change")});</script>'
			),
		);
	} else
	{
		$task_type = array('<span class="'.($read_recent ? '' : 'recent-change').'"></span>');
	}

	array_splice($row, 3, 0, $task_type);

	return $row;
}

hooks()->add_filter('tasks_table_row_data', 'add_task_type_to_tasks_table_row_data', 10, 2);
hooks()->add_filter('tasks_related_table_row_data', 'add_task_type_to_tasks_table_row_data', 10, 2);

function add_copy_task_to_tasks_table_row_data($row, $aRow)
{
	$hasPermissionEdit = staff_can('edit', 'tasks');
	$hasPermissionDelete = staff_can('delete', 'tasks');

	$outputName = '';

	if ($aRow['not_finished_timer_by_current_staff'])
	{
		$outputName .= '<span class="pull-left text-danger"><i class="fa-regular fa-clock fa-fw tw-mr-1"></i></span>';
	}

	$outputName .= '<a href="'.admin_url(
			'tasks/view/'.$aRow['id']
		).'" class="main-tasks-table-href-name tw-truncate tw-max-w-xs tw-block tw-min-w-0 tw-font-medium'.(! empty($aRow['rel_id']) ? ' mbot5' : '').'" onclick="init_task_modal('.$aRow['id'].'); return false;" title="'.e(
			$aRow['task_name']
		).'">'.e($aRow['task_name']).'</a>';

	if ($aRow['rel_name'])
	{
		$relName = task_rel_name($aRow['rel_name'], $aRow['rel_id'], $aRow['rel_type']);

		$link = task_rel_link($aRow['rel_id'], $aRow['rel_type']);

		$outputName .= '<span class="hide"> - </span><a class="tw-text-neutral-700 task-table-related tw-text-sm" data-toggle="tooltip" title="'._l(
				'task_related_to'
			).'" href="'.$link.'">'.e($relName).'</a>';
	}

	if ($aRow['recurring'] == 1)
	{
		$outputName .= '<br /><span class="label label-primary inline-block mtop4"> '._l('recurring_task').'</span>';
	}

	$outputName .= '<div class="row-options">';

	$class = 'text-success bold';
	$style = '';

	$tooltip = '';
	if ($aRow['billed'] == 1 || ! $aRow['is_assigned'] || $aRow['status'] == Tasks_model::STATUS_COMPLETE)
	{
		$class = 'text-dark disabled';
		$style = 'style="opacity:0.6;cursor: not-allowed;"';
		if ($aRow['status'] == Tasks_model::STATUS_COMPLETE)
		{
			$tooltip = ' data-toggle="tooltip" data-title="'.e(format_task_status($aRow['status'], FALSE, TRUE)).'"';
		} elseif ($aRow['billed'] == 1)
		{
			$tooltip = ' data-toggle="tooltip" data-title="'._l('task_billed_cant_start_timer').'"';
		} elseif ( ! $aRow['is_assigned'])
		{
			$tooltip = ' data-toggle="tooltip" data-title="'._l('task_start_timer_only_assignee').'"';
		}
	}

	if ($aRow['not_finished_timer_by_current_staff'])
	{
		$outputName .= '<a href="#" class="text-danger tasks-table-stop-timer" onclick="timer_action(this,'.$aRow['id'].','.$aRow['not_finished_timer_by_current_staff'].'); return false;">'._l(
				'task_stop_timer'
			).'</a>';
	} else
	{
		$outputName .= '<span'.$tooltip.' '.$style.'>
        <a href="#" class="'.$class.' tasks-table-start-timer" onclick="timer_action(this,'.$aRow['id'].'); return false;">'._l(
				'task_start_timer'
			).'</a>
        </span>';
	}

	if ($hasPermissionEdit)
	{
		$outputName .= '<span class="tw-text-neutral-300"> | </span><a href="#" onclick="edit_task('.$aRow['id'].'); return false">'._l(
				'edit'
			).'</a>';
	}

	if ($hasPermissionDelete)
	{
		$outputName .= '<span class="tw-text-neutral-300"> | </span><a href="'.admin_url(
				'tasks/delete_task/'.$aRow['id']
			).'" class="text-danger _delete task-delete">'._l('delete').'</a>';
	}

	$outputName .= '<span><span class="tw-text-neutral-300"> | </span><a class="text-primary"
              data-copy-text="'._l('task_copy_link').'"
              data-copied-text="'._l('task_link_copied').'"
               onclick="navigator.clipboard.writeText(this.dataset.link);this.innerHTML=this.dataset.copiedText;setTimeout(() => { this.innerHTML = this.dataset.copyText}, 1000);"
               href="javascript:void(0)" data-link="'.admin_url('tasks/view/'.$aRow['id']).'">'._l(
			'task_copy_link'
		).'</a></span>';

	$outputName .= '</div>';

	$row[2] = $outputName;

	return $row;
}

hooks()->add_filter('tasks_table_row_data', 'add_copy_task_to_tasks_table_row_data', 9, 2);

function add_copy_task_to_tasks_related_table_row_data($row, $aRow)
{
	$hasPermissionEdit = staff_can('edit', 'tasks');
	$hasPermissionDelete = staff_can('delete', 'tasks');
	$outputName = '';

	if ($aRow['not_finished_timer_by_current_staff'])
	{
		$outputName .= '<span class="pull-left text-danger"><i class="fa-regular fa-clock fa-fw tw-mr-1"></i></span>';
	}

	$outputName .= '<a href="'.admin_url(
			'tasks/view/'.$aRow['id']
		).'" class="main-tasks-table-href-name tw-font-medium" onclick="init_task_modal('.$aRow['id'].'); return false;" title="'.e(
			$aRow['task_name']
		).'">'.e($aRow['task_name']).'</a>';

	if ($aRow['recurring'] == 1)
	{
		$outputName .= '<span class="label label-primary inline-block mtop4"> '._l('recurring_task').'</span>';
	}

	$outputName .= '<div class="row-options">';

	$class = 'text-success bold';
	$style = '';

	$tooltip = '';
	if ($aRow['billed'] == 1 || ! $aRow['is_assigned'] || $aRow['status'] == Tasks_model::STATUS_COMPLETE)
	{
		$class = 'text-dark disabled';
		$style = 'style="opacity:0.6;cursor: not-allowed;"';
		if ($aRow['status'] == Tasks_model::STATUS_COMPLETE)
		{
			$tooltip = ' data-toggle="tooltip" data-title="'.e(format_task_status($aRow['status'], FALSE, TRUE)).'"';
		} elseif ($aRow['billed'] == 1)
		{
			$tooltip = ' data-toggle="tooltip" data-title="'._l('task_billed_cant_start_timer').'"';
		} elseif ( ! $aRow['is_assigned'])
		{
			$tooltip = ' data-toggle="tooltip" data-title="'._l('task_start_timer_only_assignee').'"';
		}
	}

	if ($aRow['not_finished_timer_by_current_staff'])
	{
		$outputName .= '<a href="#" class="text-danger tasks-table-stop-timer" onclick="timer_action(this,'.$aRow['id'].','.$aRow['not_finished_timer_by_current_staff'].'); return false;">'._l(
				'task_stop_timer'
			).'</a>';
	} else
	{
		$outputName .= '<span'.$tooltip.' '.$style.'>
        <a href="#" class="'.$class.' tasks-table-start-timer" onclick="timer_action(this,'.$aRow['id'].'); return false;">'._l(
				'task_start_timer'
			).'</a>
        </span>';
	}

	if ($hasPermissionEdit)
	{
		$outputName .= '<span class="tw-text-neutral-300"> | </span><a href="#" onclick="edit_task('.$aRow['id'].'); return false">'._l(
				'edit'
			).'</a>';
	}

	if ($hasPermissionDelete)
	{
		$outputName .= '<span class="tw-text-neutral-300"> | </span><a href="'.admin_url(
				'tasks/delete_task/'.$aRow['id']
			).'" class="text-danger _delete task-delete">'._l('delete').'</a>';
	}

	$outputName .= '<span><span class="tw-text-neutral-300"> | </span><a class="text-primary"
              data-copy-text="'._l('task_copy_link').'"
              data-copied-text="'._l('task_link_copied').'"
               onclick="navigator.clipboard.writeText(this.dataset.link);this.innerHTML=this.dataset.copiedText;setTimeout(() => { this.innerHTML = this.dataset.copyText}, 1000);"
               href="javascript:void(0)" data-link="'.admin_url('tasks/view/'.$aRow['id']).'">'._l(
			'task_copy_link'
		).'</a></span>';

	$outputName .= '</div>';

	$row[2] = $outputName;

	return $row;
}

hooks()->add_filter('tasks_related_table_row_data', 'add_copy_task_to_tasks_related_table_row_data', 9, 2);

function notice_task_recent_change($task)
{
	$CI = &get_instance();

	$assignees_ids = $task->assignees_ids;
	$followers_ids = $task->followers_ids;

	$will_notice = array_merge(array_values($followers_ids), array_values($assignees_ids));
	$will_notice = array_unique($will_notice);

	foreach ($will_notice as $user_id)
	{
		$CI->db->reset_query();
		$CI->db->select();
		$CI->db->where('user_id', $user_id);
		$CI->db->where('task_id', $task->id);
		$num_rows = $CI->db->get(db_prefix().'task_recent_changes')->num_rows();

		$CI->db->reset_query();
		if ($num_rows > 0)
		{
			$CI->db->update(db_prefix().'task_recent_changes', ['read' => 0], ['task_id' => $task->id, 'user_id' => $user_id]);
		} else
		{
			$CI->db->insert(db_prefix().'task_recent_changes', ['task_id' => $task->id, 'user_id' => $user_id]);
		}
	}
}

hooks()->add_action('task_recent_change', 'notice_task_recent_change');

function update_user_read_recent_change_status($task_id, $user_id)
{
	$CI = &get_instance();

	$CI->db->update(db_prefix().'task_recent_changes', ['read' => 1], ['task_id' => $task_id, 'user_id' => $user_id]);
}

hooks()->add_action('user_read_recent_change', 'update_user_read_recent_change_status', 10, 2);

function filter_user_read_recent_change_status($is_read, $task_id, $user_id)
{
	$CI = &get_instance();

	$CI->db->reset_query();
	$CI->db->select('read');
	$CI->db->where('task_id', $task_id);
	$CI->db->where('user_id', $user_id);
	$row = $CI->db->get(db_prefix().'task_recent_changes')->row_array();

	return ($row['read'] ?? 1) == 1;
}

hooks()->add_filter('user_read_recent_change_status', 'filter_user_read_recent_change_status', 10, 3);

function add_recent_change_styles()
{
	?>
    <style>
        .table-tasks tr.recent-change,
        .table-rel-tasks tr.recent-change {
            background-color: #EDFAFF;
        }

        .table-tasks tr.recent-change *,
        .table-rel-tasks tr.recent-change * {
            font-weight: 700 !important;
        }
    </style>
	<?php
}

hooks()->add_action('app_admin_head', 'add_recent_change_styles');

function add_project_owners_to_project_overview($project)
{
	$CI = &get_instance();

	$CI->db->reset_query();
	$CI->db->where('project_id', $project->id);
	$CI->db->join(db_prefix().'staff', db_prefix().'staff.staffid='.db_prefix().'project_owners.staff_id');
	ob_start();

	$owners = $CI->db->get(db_prefix().'project_owners')->result_array();
	include_once module_dir_path('project_management_enhancements', 'views/projects/more_overview.php');

	ob_end_flush();
}

hooks()->add_action('admin_project_overview_end_of_project_overview_left', 'add_project_owners_to_project_overview');

function add_filter_for_task_label($result)
{

	$CI = &get_instance();
	$data = $CI->input->post();

}

//hooks()->add_filter('use_match_for_custom_fields_table_search', 'add_filter_for_task_label');

function remove_followers_after_add_edit_project_owners($project, $tasks, $new_owner_ids, $current_owners_id)
{
	$CI = &get_instance();
	$should_remove_owners = array_diff($current_owners_id, $new_owner_ids);
	foreach ($tasks as $task)
	{
		if ( ! empty($should_remove_owners))
		{
			$CI->db->reset_query();
			$CI->db->where('taskid', $task['id']);
			$CI->db->where_in('staffid', $should_remove_owners);
			$CI->db->delete(db_prefix().'task_followers');
		}

		foreach ($new_owner_ids as $owner_id)
		{
			$CI->db->reset_query();
			$CI->db->select('id');
			$CI->db->where('staffid', $owner_id);
			$CI->db->where('taskid', $task['id']);
			$result = $CI->db->get(db_prefix().'task_followers')->result_array();

			if ( ! empty($result))
			{
				continue;
			}

			$CI->db->reset_query();
			$CI->db->insert(db_prefix().'task_followers', [
				'staffid' => $owner_id,
				'taskid' => $task['id'],
			]);
		}
	}

}

hooks()->add_action('after_add_edit_project_owners', 'remove_followers_after_add_edit_project_owners', 10, 4);