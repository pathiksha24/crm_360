<?php

defined('BASEPATH') or exit('No direct script access allowed');

use app\services\tasks\TasksKanban;

/**
 * @property-read Staff_model $staff_model
 * @property-read Tasks_model $tasks_model
 * @property-read projects_enhance_model $projects_model
 * @property-read Projects_enhance_model $projects_enhance_model
 */
class Projects extends AdminController {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('task_types_model');
		$this->load->model('projects_enhance_model');
	}

	public function project($id = '')
	{
		if (staff_cant('edit', 'projects') && staff_cant('create', 'projects')) {
			access_denied('Projects');
		}

		if ($this->input->post()) {
			$data                = $this->input->post();
			$data['description'] = html_purify($this->input->post('description', false));
			if ($id == '') {
				if (staff_cant('create', 'projects')) {
					access_denied('Projects');
				}
				$id = $this->projects_enhance_model->add($data);
				if ($id) {
					set_alert('success', _l('added_successfully', _l('project')));
					redirect(admin_url('projects/view/' . $id));
				}
			} else {
				if (staff_cant('edit', 'projects')) {
					access_denied('Projects');
				}
				$success = $this->projects_enhance_model->update($data, $id);
				if ($success) {
					set_alert('success', _l('updated_successfully', _l('project')));
				}
				redirect(admin_url('projects/view/' . $id));
			}
		}
		if ($id == '') {
			$title                            = _l('add_new', _l('project'));
			$data['auto_select_billing_type'] = $this->projects_enhance_model->get_most_used_billing_type();

			if ($this->input->get('via_estimate_id')) {
				$this->load->model('estimates_model');
				$data['estimate'] = $this->estimates_model->get($this->input->get('via_estimate_id'));
			}
		} else {
			$data['project']                               = $this->projects_enhance_model->get($id);
			$data['project']->settings->available_features = unserialize($data['project']->settings->available_features);

			$data['project_members'] = $this->projects_enhance_model->get_project_members($id);
			$data['project_owners'] = $this->projects_enhance_model->get_project_owners($id);
			$title                   = _l('edit', _l('project'));
		}

		if ($this->input->get('customer_id')) {
			$data['customer_id'] = $this->input->get('customer_id');
		}

		$data['last_project_settings'] = $this->projects_enhance_model->get_last_project_settings();

		if (count($data['last_project_settings'])) {
			$key                                          = array_search('available_features', array_column($data['last_project_settings'], 'name'));
			$data['last_project_settings'][$key]['value'] = unserialize($data['last_project_settings'][$key]['value']);
		}

		$data['settings'] = $this->projects_enhance_model->get_settings();
		$data['statuses'] = $this->projects_enhance_model->get_project_statuses();
		$data['staff']    = $this->staff_model->get('', ['active' => 1]);

		$data['title'] = $title;
		$this->load->view('projects/project', $data);
	}

	public function view($id)
	{
		if (staff_can('view', 'projects') || $this->projects_model->is_member($id)) {
			close_setup_menu();
			$project = $this->projects_model->get($id);

			if (!$project) {
				blank_page(_l('project_not_found'));
			}

			$project->settings->available_features = unserialize($project->settings->available_features);
			$data['statuses']                      = $this->projects_model->get_project_statuses();

			$group = !$this->input->get('group') ? 'project_overview' : $this->input->get('group');

			if (strpos($group, '#') !== false) {
				$group = str_replace('#', '', $group);
			}

			$data['tabs'] = get_project_tabs_admin();
			$data['tab']  = $this->app_tabs->filter_tab($data['tabs'], $group);

			if (!$data['tab']) {
				show_404();
			}

			$this->load->model('payment_modes_model');
			$data['payment_modes'] = $this->payment_modes_model->get('', [], true);

			$data['project']  = $project;
			$data['currency'] = $this->projects_model->get_currency($id);

			$data['project_total_logged_time'] = $this->projects_model->total_logged_time($id);

			$data['staff']   = $this->staff_model->get('', ['active' => 1]);
			$percent         = $this->projects_model->calc_progress($id);
			$data['members'] = $this->projects_model->get_project_members($id);
			foreach ($data['members'] as $key => $member) {
				$data['members'][$key]['total_logged_time'] = 0;
				$member_timesheets                          = $this->tasks_model->get_unique_member_logged_task_ids($member['staff_id'], ' AND task_id IN (SELECT id FROM ' . db_prefix() . 'tasks WHERE rel_type="project" AND rel_id="' . $this->db->escape_str($id) . '")');

				foreach ($member_timesheets as $member_task) {
					$data['members'][$key]['total_logged_time'] += $this->tasks_model->calc_task_total_time($member_task->task_id, ' AND staff_id=' . $member['staff_id']);
				}
			}
			$data['bodyclass'] = '';

			$this->app_scripts->add(
				'projects-js',
				base_url($this->app_scripts->core_file('assets/js', 'projects.js')) . '?v=' . $this->app_scripts->core_version(),
				'admin',
				['app-js', 'jquery-comments-js', 'frappe-gantt-js', 'circle-progress-js']
			);

			if ($group == 'project_overview') {
				$data['project_total_days']        = round((human_to_unix($data['project']->deadline . ' 00:00') - human_to_unix($data['project']->start_date . ' 00:00')) / 3600 / 24);
				$data['project_days_left']         = $data['project_total_days'];
				$data['project_time_left_percent'] = 100;
				if ($data['project']->deadline) {
					if (human_to_unix($data['project']->start_date . ' 00:00') < time() && human_to_unix($data['project']->deadline . ' 00:00') > time()) {
						$data['project_days_left']         = round((human_to_unix($data['project']->deadline . ' 00:00') - time()) / 3600 / 24);
						$data['project_time_left_percent'] = $data['project_days_left'] / $data['project_total_days'] * 100;
						$data['project_time_left_percent'] = round($data['project_time_left_percent'], 2);
					}
					if (human_to_unix($data['project']->deadline . ' 00:00') < time()) {
						$data['project_days_left']         = 0;
						$data['project_time_left_percent'] = 0;
					}
				}

				$__total_where_tasks = 'rel_type = "project" AND rel_id=' . $this->db->escape_str($id);
				if (staff_cant('view', 'tasks')) {
					$__total_where_tasks .= ' AND ' . db_prefix() . 'tasks.id IN (SELECT taskid FROM ' . db_prefix() . 'task_assigned WHERE staffid = ' . get_staff_user_id() . ')';

					if (get_option('show_all_tasks_for_project_member') == 1) {
						$__total_where_tasks .= ' AND (rel_type="project" AND rel_id IN (SELECT project_id FROM ' . db_prefix() . 'project_members WHERE staff_id=' . get_staff_user_id() . '))';
					}
				}

				$__total_where_tasks = hooks()->apply_filters('admin_total_project_tasks_where', $__total_where_tasks, $id);

				$where = ($__total_where_tasks == '' ? '' : $__total_where_tasks . ' AND ') . 'status != ' . Tasks_model::STATUS_COMPLETE;

				$data['tasks_not_completed'] = total_rows(db_prefix() . 'tasks', $where);
				$total_tasks                 = total_rows(db_prefix() . 'tasks', $__total_where_tasks);
				$data['total_tasks']         = $total_tasks;

				$where = ($__total_where_tasks == '' ? '' : $__total_where_tasks . ' AND ') . 'status = ' . Tasks_model::STATUS_COMPLETE . ' AND rel_type="project" AND rel_id="' . $id . '"';

				$data['tasks_completed'] = total_rows(db_prefix() . 'tasks', $where);

				$data['tasks_not_completed_progress'] = ($total_tasks > 0 ? number_format(($data['tasks_completed'] * 100) / $total_tasks, 2) : 0);
				$data['tasks_not_completed_progress'] = round($data['tasks_not_completed_progress'], 2);

				@$percent_circle        = $percent / 100;
				$data['percent_circle'] = $percent_circle;

				$data['project_overview_chart'] = (new HoursOverviewChart(
					$id,
					($this->input->get('overview_chart') ? $this->input->get('overview_chart') : 'this_week')
				))->get();
			} elseif ($group == 'project_invoices') {
				$this->load->model('invoices_model');

				$data['invoiceid']   = '';
				$data['status']      = '';
				$data['custom_view'] = '';

				$data['invoices_years']       = $this->invoices_model->get_invoices_years();
				$data['invoices_sale_agents'] = $this->invoices_model->get_sale_agents();
				$data['invoices_statuses']    = $this->invoices_model->get_statuses();
				$data['invoices_table'] = App_table::find('project_invoices');
			} elseif ($group == 'project_gantt') {
				$gantt_type         = (!$this->input->get('gantt_type') ? 'milestones' : $this->input->get('gantt_type'));
				$taskStatus         = (!$this->input->get('gantt_task_status') ? null : $this->input->get('gantt_task_status'));
				$data['gantt_data'] = (new Gantt($id, $gantt_type))->forTaskStatus($taskStatus)->get();
			} elseif ($group == 'project_milestones') {
				$data['bodyclass'] .= 'project-milestones ';
				$data['milestones_exclude_completed_tasks'] = $this->input->get('exclude_completed') && $this->input->get('exclude_completed') == 'yes' || !$this->input->get('exclude_completed');

				$data['total_milestones'] = total_rows(db_prefix() . 'milestones', ['project_id' => $id]);
				$data['milestones_found'] = $data['total_milestones'] > 0 || (!$data['total_milestones'] && total_rows(db_prefix() . 'tasks', ['rel_id' => $id, 'rel_type' => 'project', 'milestone' => 0]) > 0);
			} elseif ($group == 'project_files') {
				$data['files'] = $this->projects_model->get_files($id);
			} elseif ($group == 'project_expenses') {
				$this->load->model('taxes_model');
				$this->load->model('expenses_model');
				$data['taxes']              = $this->taxes_model->get();
				$data['expense_categories'] = $this->expenses_model->get_category();
				$data['currencies']         = $this->currencies_model->get();
				$data['expenses_table'] = App_table::find('project_expenses');
			} elseif ($group == 'project_activity') {
				$data['activity'] = $this->projects_model->get_activity($id);
			} elseif ($group == 'project_notes') {
				$data['staff_notes'] = $this->projects_model->get_staff_notes($id);
			} elseif ($group == 'project_contracts') {
				$this->load->model('contracts_model');
				$data['contract_types'] = $this->contracts_model->get_contract_types();
				$data['years']          = $this->contracts_model->get_contracts_years();
				$data['contracts_table'] = App_table::find('project_contracts');
			} elseif ($group == 'project_estimates') {
				$this->load->model('estimates_model');
				$data['estimates_years']       = $this->estimates_model->get_estimates_years();
				$data['estimates_sale_agents'] = $this->estimates_model->get_sale_agents();
				$data['estimate_statuses']     = $this->estimates_model->get_statuses();
				$data['estimates_table'] = App_table::find('project_estimates');
				$data['estimateid']            = '';
				$data['switch_pipeline']       = '';
			} elseif ($group == 'project_proposals') {
				$this->load->model('proposals_model');
				$data['proposal_statuses']     = $this->proposals_model->get_statuses();
				$data['proposals_sale_agents'] = $this->proposals_model->get_sale_agents();
				$data['years']                 = $this->proposals_model->get_proposals_years();
				$data['proposals_table'] = App_table::find('project_proposals');
				$data['proposal_id']           = '';
				$data['switch_pipeline']       = '';
			} elseif ($group == 'project_tickets') {
				$data['chosen_ticket_status'] = '';
				$this->load->model('tickets_model');
				$data['ticket_assignees'] = $this->tickets_model->get_tickets_assignes_disctinct();

				$this->load->model('departments_model');
				$data['staff_deparments_ids']          = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
				$data['default_tickets_list_statuses'] = hooks()->apply_filters('default_tickets_list_statuses', [1, 2, 4]);
			} elseif ($group == 'project_timesheets') {
				// Tasks are used in the timesheet dropdown
				// Completed tasks are excluded from this list because you can't add timesheet on completed task.
				$data['tasks']                = $this->projects_model->get_tasks($id, 'status != ' . Tasks_model::STATUS_COMPLETE . ' AND billed=0');
				$data['timesheets_staff_ids'] = $this->projects_model->get_distinct_tasks_timesheets_staff($id);
			}

			// Discussions
			if ($this->input->get('discussion_id')) {
				$data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
				$data['discussion']                        = $this->projects_model->get_discussion($this->input->get('discussion_id'), $id);
				$data['current_user_is_admin']             = is_admin();
			}

			$data['percent'] = $percent;

			$this->app_scripts->add('circle-progress-js', 'assets/plugins/jquery-circle-progress/circle-progress.min.js');

			$other_projects       = [];
			$other_projects_where = 'id != ' . $id;

			$statuses = $this->projects_model->get_project_statuses();

			$other_projects_where .= ' AND (';
			foreach ($statuses as $status) {
				if (isset($status['filter_default']) && $status['filter_default']) {
					$other_projects_where .= 'status = ' . $status['id'] . ' OR ';
				}
			}

			$other_projects_where = rtrim($other_projects_where, ' OR ');

			$other_projects_where .= ')';

			if (staff_cant('view', 'projects')) {
				$other_projects_where .= ' AND ' . db_prefix() . 'projects.id IN (SELECT project_id FROM ' . db_prefix() . 'project_members WHERE staff_id=' . get_staff_user_id() . ')';
			}

			$data['other_projects'] = $this->projects_model->get('', $other_projects_where);
			$data['title']          = $data['project']->name;
			$data['bodyclass'] .= 'project estimates-total-manual';
			$data['project_status'] = get_project_status_by_id($project->status);

			$this->load->view('admin/projects/view', $data);
		} else {
			access_denied('Project View');
		}
	}
	public function add_task_type()
	{
		if ( ! $this->input->post())
		{
			access_denied('method not allowed');
			exit();
		}

		$project_id = trim($this->input->post('project_id'));
		$type_name = trim($this->input->post('name'));
		$label_color = trim($this->input->post('label_color'));
		$text_color = trim($this->input->post('text_color'));
		$sort_order = trim($this->input->post('sort_order'));

		$task_type = $this->task_types_model->get_task_type_by_name($project_id, $type_name);
		if ( ! empty($task_type))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('task_type_exists'),
			]);
			exit();
		}

		$data = [
			'name' => $type_name,
			'label_color' => $label_color,
			'text_color' => $text_color,
			'sort_order' => $sort_order,
			'project_id' => $project_id,
		];

		$this->task_types_model->add_project_task_type($data);

		echo json_encode([
			'success' => TRUE,
			'message' => _l('added_new_task_type'),
		]);
		exit();
	}
	public function edit_task_type()
	{
		if ( ! $this->input->post())
		{
			access_denied('method not allowed');
			exit();
		}

		$project_id = trim($this->input->post('project_id'));
		$task_type_id = trim($this->input->post('edit_task_type'));
		$type_name = trim($this->input->post('name'));
		$label_color = trim($this->input->post('label_color'));
		$text_color = trim($this->input->post('text_color'));
		$sort_order = trim($this->input->post('sort_order'));

		$data = [
			'name' => $type_name,
			'label_color' => $label_color,
			'text_color' => $text_color,
			'sort_order' => $sort_order,
		];

		$result = $this->task_types_model->update_project_task_type($project_id, $task_type_id, $data);

		if ($result)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('updated_task_type'),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('update_task_type_failed'),
			]);
		}
	}
	public function delete_task_type()
	{
		if ( ! $this->input->post())
		{
			access_denied('method not allowed');
			exit();
		}

		$project_id = trim($this->input->post('project_id'));
		$type_id = trim($this->input->post('task_type_id'));

		$task_type = $this->task_types_model->get_task_type($type_id);
		if ($task_type && $task_type['editable'] == 0)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('can_not_delete_this_task_type'),
			]);
			exit();
		}

		$this->task_types_model->delete_project_task_type($project_id, $type_id);

		echo json_encode([
			'success' => TRUE,
			'message' => _l('deleted_task_type'),
		]);
		exit();
	}

	public function task_types($project_id)
	{
		if ($this->projects_enhance_model->is_member($project_id) || staff_can('view', 'projects'))
		{
			if ($this->input->is_ajax_request())
			{
				$this->get_table_data('project_task_types', [
					'project_id' => $project_id,
				]);
			}
		}
	}

	public function get_table_data($table, $params = [])
	{
		$customFieldsColumns = [];

		foreach ($params as $key => $val)
		{
			${$key} = $val;
		}

		include_once APP_MODULES_PATH.PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME.'/views/tables/'.$table.'.php';

		echo json_encode($output);

		exit;
	}
}
