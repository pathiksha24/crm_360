<?php

class Team_daily_report extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('team_daily_report_model');
        $this->load->model('staff_model');
        $this->load->model('leads_model');
    }

    public function team_daily_form($id = '')
    {
        if ($id != '' && (!is_admin() && !is_team_leader())) access_denied('team_daily_form');
        if ($this->input->post()) {
            $data = $this->input->post();
//            $dateTime = DateTime::createFromFormat('d-m-Y', $data['date']);
//            $formattedDate = $dateTime->format('d-m-Y H:i:s');
            if ($id){
                if (isset($data['date'])){
                    //$dateTime = DateTime::createFromFormat('d-m-Y', $data['date']);
                    $data['date'] = to_sql_date($data['date'], true);
                }
                $record_id  = $this->team_daily_report_model->update_team_daily_report($id, $data);
                if ($record_id) {
                    set_alert('success', _l('team_daily_form_submitted'));
                }else{
                    set_alert('danger', _l('team_daily_form_not_submitted'));
                }
                redirect(admin_url('team_daily_report/report'));
            }else{
                $data['date'] = to_sql_date(date('d-m-Y H:i:s'), true);
                $data['staff_id'] = get_staff_user_id();
                if(is_admin() || is_team_leader()) $data['team_leader'] = get_staff_team_leader();
                $data['createddate'] = to_sql_date(date('Y-m-d H:i:s'), true);
                $record_id  = $this->team_daily_report_model->insert_team_daily_report($data);
            }

            if ($record_id) {
                set_alert('success', _l('team_daily_form_submitted'));
            }else{
                set_alert('danger', _l('team_daily_form_not_submitted'));
            }
            redirect(admin_url('team_daily_report/team_daily_form'));
        }

        $data = [];
        $data['client_nationalities'] = get_team_daily_dropdown('team_daily_form_client_nationalities');
        $data['services'] = get_team_daily_dropdown('team_daily_form_services');
        $data['sources'] = get_team_daily_dropdown('team_daily_form_sources');
        $data['team_leaders'] = $this->staff_model->get('', ['is_team_leader' => 1]);
        $data['cities'] = get_team_daily_dropdown('team_daily_form_cities');
        $data['title'] = _l('team_daily_form');
        if (!is_admin() && !is_team_leader()) {
            $where =  ['assigned' => get_staff_user_id()];
            $data['leads'] = $this->leads_model->get('', $where);
        }

        if (!is_admin() && is_team_leader()){
            $this->db->select('*,' . db_prefix() . 'leads.name, ' . db_prefix() . 'leads.id,'. db_prefix() . 'leads.phonenumber');
            $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid=' . db_prefix() . 'leads.assigned', 'left');
            $where =  ['staff.team_leader' => get_staff_user_id()];
            $this->db->where($where);
            $data['leads'] = $this->db->get(db_prefix() . 'leads')->result_array();
        }
        if (is_admin()){
            if ($id){
                $entry = $this->team_daily_report_model->get_entry($id);
                $this->db->select('*,' . db_prefix() . 'leads.name, ' . db_prefix() . 'leads.id,'. db_prefix() . 'leads.phonenumber');
                $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid=' . db_prefix() . 'leads.assigned', 'left');
                $where =  ['staff.team_leader' => $entry['team_leader']];
                $this->db->where($where);
                $data['leads'] = $this->db->get(db_prefix() . 'leads')->result_array();
            }else{
                $where =  ['assigned' => get_staff_user_id()];
                $data['leads'] = $this->leads_model->get('', $where);
            }
        }

        if (!is_admin() && !is_team_leader()){
            $staff = $this->staff_model->get(get_staff_user_id());
            $team_leader_1 = $staff->team_leader;
            $team_leader_2 = $staff->team_leader_2;
            $data['team_leaders'] = [];
            if ($team_leader_1) $data['team_leaders'][] = ['id' => $team_leader_1, 'name' => get_staff_full_name($team_leader_1)];
            if ($team_leader_2) $data['team_leaders'][] = ['id' => $team_leader_2, 'name' => get_staff_full_name($team_leader_2)];
        }

        if ($id){
            $data['entry'] = $this->team_daily_report_model->get_entry($id);
        }
        $this->load->view('team_daily_report/team_daily_form', $data);
    }

    public function widget_creator()
    {
        if ($this->input->post()) {
            $data['widget_name'] = $this->input->post('widget_name');
            $filters = [];
            if ($this->input->post('team_leader')) $filters['team_leader'] = $this->input->post('team_leader');
            if ($this->input->post('staff')) $filters['staff'] = $this->input->post('staff');
            if ($this->input->post('months-report')) $filters['date']['months-report'] = $this->input->post('months-report');
            if ($this->input->post('report-from')) $filters['date']['report-from'] = $this->input->post('report-from');
            if ($this->input->post('report-to')) $filters['date']['report-to'] = $this->input->post('report-to');
            if ($this->input->post('service_name')) $filters['service_name'] = $this->input->post('service_name');
            $filters = json_encode($filters);

            $data['filters'] =  $filters;
            $record_id  = $this->team_daily_report_model->create_widget($data);
            if ($record_id) {
                set_alert('success', _l('widget_created_successfully'));
            }else{
                set_alert('danger', _l('widget_not_created_successfully'));
            }
            redirect(admin_url('team_daily_report/widget_creator'));
        }
        if (!is_super_admin()) access_denied('Team Daily Report Widget Creator');
        $data['title'] = _l('widget_creator');
        $data['services'] = get_team_daily_dropdown('team_daily_form_services');
        $data['team_leaders'] = $this->staff_model->get('', ['is_team_leader' => 1]);
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);

        $this->load->view('team_daily_report/widget_creator', $data);
    }

    public function report()
    {
        if (!is_super_admin() && !is_admin() && !is_team_leader()) access_denied('Team Daily Report');
        $data['title'] = _l('team_daily_report');
        $data['services'] = get_team_daily_dropdown('team_daily_form_services');
        $data['team_leaders'] = $this->staff_model->get('', ['is_team_leader' => 1]);

        if (!is_admin() && is_team_leader()){
            $this->db->where('team_leader', get_staff_user_id());
            $this->db->or_where('team_leader_2', get_staff_user_id());
            $data['staff'] = $this->db->get(db_prefix() . 'staff')->result_array();
        }
        if (is_admin()){
            $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        }
        $data['sources'] = get_team_daily_dropdown('team_daily_form_sources');

        $this->load->view('team_daily_report/report', $data);
    }

    public function delete($id)
    {
        $this->db->delete('tblteam_daily_report', ['id' => $id]);
        set_alert('success', _l('record_deleted', _l('team_daily_report')));
        redirect(admin_url('team_daily_report/report'));
    }

    public function team_daily_report_table(){
        if ($this->input->is_ajax_request()) {
            $team_lead = $this->staff_model->get(get_staff_user_id());
            $select = [
                'id',
                'CONCAT(tblstaff.firstname, " ", tblstaff.lastname) as staff',
                'tblteam_daily_report.team_leader as team_leader',
                'CONCAT(client_firstname, " ", client_lastname) as customer',
                'service_name',
                'closing_source',
                'client_nationality',
                'city',
                'deposit_gross',
                'net_amount',
                'lead_id',
                'date',
                ];
            $where = [];
            $aColumns     = $select;
            $sIndexColumn = 'id';
            $sTable       = 'tblteam_daily_report';
            $join         = [
                'LEFT JOIN tblstaff ON tblstaff.staffid = tblteam_daily_report.staff_id',
            ];
            $filter = [];

            $months_report      = $this->input->post('report_months');
            //$date_by      = $this->input->post('date_by');
            $date_by      = 'date';
            $team_leader      = $this->input->post('team_leader');
            $service_name      = $this->input->post('service_name');
            $staff      = $this->input->post('staff');
            $closing_source_name      = $this->input->post('closing_source');

            $custom_date_select = '';
            $field = $date_by;
            if ($months_report!=''){
                $custom_date_select = $this->get_where_report_period_date('DATE('.$field.')',$months_report);
                //echo '<pre>';print_r($custom_date_select);echo '</pre>';die();

            }
            if (isset($custom_date_select) and $custom_date_select != '') {
                array_push($filter, $custom_date_select);
            }

            if ($team_leader !== 0 && $team_leader !== '0' && $team_leader !== '' && is_admin()){
                array_push($where, 'AND (tblteam_daily_report.team_leader = ' . $team_leader.')');
            }
            $leads_staff = [];
            if (is_team_leader() && !is_admin()){
               // array_push($where, 'AND (tblteam_daily_report.team_leader = ' . get_staff_user_id().')');
                $leads_staff = json_decode($team_lead->leads_staff);
                if (!is_array($leads_staff)){
                    $leads_staff = [];
                }
            }

            if (!empty($service_name)){
                array_push($where, 'AND (service_name IN(' . implode(',', array_map(function($name) {
                        return "'" . addslashes($name) . "'";
                    }, $service_name)) . '))');
            }

            if (!empty($staff) && (is_admin() || is_team_leader())){
                array_push($where, 'AND (tblteam_daily_report.staff_id IN(' . implode(',', $staff) . '))');
            }

            if (!empty($closing_source_name)){
                array_push($where, 'AND (closing_source IN(' . implode(',', array_map(function($name) {
                        return "'" . addslashes($name) . "'";
                    }, $closing_source_name)) . '))');
            }


//            if (!is_admin()){
//                array_push($where, 'AND (tblstaff.manager_id = ' . get_staff_user_id().')');
//            }
            if (count($filter) > 0) {
                array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
            }

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where,
                [
                    'tblteam_daily_report.staff_id as staff_id',
                ]
            );

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data['total_closing'] = 0;
            $footer_data['total_gross'] = 0;
            $footer_data['total_net'] = 0;

            foreach ($rResult as $aRow) {
                // removed this block because team daily report of team leader should get all data 
                // if (!is_admin() && is_team_leader()){
                //     $second_team_leader = get_second_team_leader($aRow['staff_id']);
                //     if ($second_team_leader != get_staff_user_id() && $aRow['team_leader'] != get_staff_user_id()){
                //         continue;
                //     }
                //     if (!in_array($aRow['staff_id'], $leads_staff)){
                //         continue;
                //     }
                // }
                $row = [];
                $row[] = $aRow['id'];
                $row[] = $aRow['staff'];
                $row[] = get_staff_full_name($aRow['team_leader']);
                $row[] = $aRow['customer'];
                $row[] = $aRow['service_name'];
                $row[] = $aRow['closing_source'];
                $row[] = $aRow['client_nationality'];
                $row[] = $aRow['city'];
                $row[] = $aRow['deposit_gross'];
                $row[] = $aRow['net_amount'];
                $row[] = '<a href="'.admin_url('leads/index/' . $aRow['lead_id']).'" target="_blank">'.get_lead_phonenumber($aRow['lead_id']).'</a>';
                $row[] = _dt($aRow['date']);
                $action = '<a href="'.admin_url('team_daily_report/team_daily_form/' . $aRow['id']).'">Edit</a>';
                $action .= '<br><a class="text-danger _delete" href="'.admin_url('team_daily_report/delete/' . $aRow['id']).'">Delete</a>';
                $row[] = $action;
                $row['DT_RowClass'] = 'has-row-options';
                $footer_data['total_closing'] += 1;
                $footer_data['total_gross'] += (int)$aRow['deposit_gross'];
                $footer_data['total_net'] += (int)$aRow['net_amount'];
                $output['aaData'][] = $row;
            }
            $output['sums']              = $footer_data;


            echo json_encode($output);
            die();
        }
    }

    public function team_daily_report_widgets_table(){
        if ($this->input->is_ajax_request()) {
            $select = [
                'id',
                'widget_name',
                'filters',
                ];
            $where = [];
            $aColumns     = $select;
            $sIndexColumn = 'id';
            $sTable       = 'tblteam_daily_report_widgets';
            $join         = [
            ];

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where);

            $output  = $result['output'];
            $rResult = $result['rResult'];
            foreach ($rResult as $aRow) {
                $row = [];
                $row[] = $aRow['id'];
                $row[] = $aRow['widget_name'];
                $filters = json_decode($aRow['filters'], true);
                $widget_detail = '';

                foreach ($filters as $key => $value) {
                    switch ($key) {
                        case 'staff':
                            if (!empty($value) && is_array($value)) {
                                $staff_names = array_map('get_staff_full_name', $value);
                                $widget_detail .= '<b class="text-success">Staff :</b> ' . implode(', ', $staff_names) . '<br>';
                            }
                            break;

                        case 'service_name':
                            if (!empty($value)) {
                                $widget_detail .= '<b class="text-success">Service Name :</b> ' . implode(', ', $value) . '<br>';
                            }
                            break;

                        case 'team_leader':
                            if (!empty($value) && is_array($value)) {
                                $team_leaders_names = array_map('get_staff_full_name', $value);
                                $widget_detail .= '<b class="text-success">Team Leaders :</b> ' . implode(', ', $team_leaders_names) . '<br>';
                            }
                            break;

                        case 'date':
                            if (!empty($value) && is_array($value)) {
                                if (isset($value['months-report'])) {
                                    if ($value['months-report'] === 'custom') {
                                        $widget_detail .= '<b class="text-success">Custom Date :</b> ' . $value['report-from'] . ' - ' . $value['report-to'] . '<br>';
                                    } else {
                                        $widget_detail .= '<b class="text-success">Custom Date :</b> ' . convertToTitleCase($value['months-report']) . '<br>';
                                    }
                                }
                            }
                            break;

                        default:
                            $widget_detail .= '<b>' . convertToTitleCase($key) . ':</b> ' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '<br>';
                            break;
                    }
                }
                $row[] = $widget_detail;
                $row[] = '<a href="'.admin_url('team_daily_report/delete_widget/'.$aRow['id']).'" class="label label-danger _delete"><i class="fa fa-trash" aria-hidden="true"></i></a>';
                $row['DT_RowClass'] = 'has-row-options';
                $output['aaData'][] = $row;
            }

            echo json_encode($output);
            die();
        }
    }

    public function widget_table($widget_id)
    {
        if ($this->input->is_ajax_request()) {
            $this->db->where('id', $widget_id);
            $widget = $this->db->get('tblteam_daily_report_widgets')->row_array();

            $select = [
                'CONCAT(tblstaff.firstname, " ", tblstaff.lastname) as staff',
                'COUNT(tblteam_daily_report.id) as closings',
                'SUM(tblteam_daily_report.deposit_gross) as gross',
                'SUM(tblteam_daily_report.net_amount) as net',
            ];
            $where = [];
            $filter = [];
            $filters = json_decode($widget['filters'], true);

            foreach ($filters as $key => $value) {
                switch ($key) {
                    case 'staff':
                        if (!empty($value) && is_array($value)) {
                            $where[] = 'AND (tblteam_daily_report.staff_id IN(' . implode(',', $value) . '))';
                        }
                        break;

                    case 'service_name':
                        if (!empty($value)) {
                            $where[] = 'AND (service_name IN(' . implode(',', array_map(function($name) {
                                    return "'" . addslashes($name) . "'";
                                }, $value)) . '))';
                        }
                        break;

                    case 'team_leader':
                        if (!empty($value) && is_array($value)) {
                            $where[] .= 'AND (tblteam_daily_report.team_leader IN(' . implode(',', $value) . '))';
                        }
                        break;

                    case 'date':
                        if (!empty($value) && is_array($value)) {

                            $custom_date_select = '';
                            $field = 'createddate';
                            if (isset($value['months-report']) && $value['months-report'] != ''){
                                $custom_date_select = $this->get_where_report_period('('.$field.')', $value['months-report'],$value['report-from'] ?? '', $value['report-to'] ?? '');
                            }
                            if (isset($custom_date_select) and $custom_date_select != '') {
                                array_push($filter, $custom_date_select);
                            }
                        }
                        break;

                    default:
                        break;
                }
            }


            if (count($filter) > 0) {
                array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
            }


            $aColumns     = $select;
            $sIndexColumn = 'staff_id';
            $sTable       = 'tblteam_daily_report';
            $join         = [
                'LEFT JOIN tblstaff ON tblstaff.staffid = tblteam_daily_report.staff_id',
            ];
            $groupBY = 'GROUP BY tblteam_daily_report.staff_id';

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [], $groupBY);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data['total_closing'] = 0;
            $footer_data['total_gross'] = 0;
            $footer_data['total_net'] = 0;

            foreach ($rResult as $aRow) {
                $row = [];
                $row[] = $aRow['staff'];
                $row[] = $aRow['closings'];
                $row[] = $aRow['gross'];
                $row[] = $aRow['net'];

                $footer_data['total_closing'] += $aRow['closings'];
                $footer_data['total_gross'] += $aRow['gross'];
                $footer_data['total_net'] += $aRow['net'];

                $row['DT_RowClass'] = 'has-row-options';
                $output['aaData'][] = $row;
            }
            $output['sums']              = $footer_data;

            echo json_encode($output);
            die();
        }
    }

    public function delete_widget($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'team_daily_report_widgets');
        set_alert('success', _l('deleted', _l('team_daily_report_widget_deleted_successfully')));
        redirect(admin_url('team_daily_report/widget_creator'));

    }

    private function get_where_report_period($field = 'date', $months_report='this_month', $from_date = '', $to_date = '')
    {
        $custom_date_select = '';
        if ($months_report != '') {
            if ($months_report == 'this_week') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' . date('Y-m-d H:i:s', strtotime('monday this week')) . '" AND "' . date('Y-m-d H:i:s', strtotime('sunday this week')) . '")';
            } elseif ($months_report == 'today') {
                $custom_date_select = 'AND (' . $field . ' = "' . date('Y-m-d H:i:s') . '")';
            } elseif ($months_report == 'last_week') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' . date('Y-m-d H:i:s', strtotime('monday last week')) . '" AND "' . date('Y-m-d H:i:s', strtotime('sunday last week')) . '")';
            } elseif ($months_report == 'this_month') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' . date('Y-m-01') . '" AND "' . date('Y-m-t') . '")';
            } elseif ($months_report == 'last_month') {
                $beginLastMonth = to_sql_date(date('Y-m-01', strtotime('-1 MONTH')) );
                $endLastMonth   = to_sql_date(date('Y-m-t', strtotime('-1 MONTH')));
                $custom_date_select = '  ' . $field . ' BETWEEN "' . ($beginLastMonth) . '" AND "' . ($endLastMonth).'"';
            } elseif ($months_report == 'this_year') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' .
                    date('Y-m-d H:i:s', strtotime(date('Y-01-01'))) .
                    '" AND "' .
                    date('Y-m-d H:i:s', strtotime(date('Y-12-31'))) . '")';
            } elseif ($months_report == 'last_year') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' .
                    date('Y-m-d H:i:s', strtotime(date(date('Y', strtotime('last year')) . '-01-01'))) .
                    '" AND "' .
                    date('Y-m-d H:i:s', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))) . '")';
            } elseif ($months_report == 'custom') {
                $from_date = to_sql_date($from_date ?? $this->input->post('report_from'), true);
                $to_date   = to_sql_date($to_date ?? $this->input->post('report_to'), true);
                if ($from_date == $to_date) {
                    $custom_date_select = 'AND ' . $field . ' = "' . $from_date . '"';
                } else {
                    $custom_date_select = 'AND (' . $field . ' BETWEEN "' . $from_date . '" AND "' . $to_date . '")';
                }
            }
        }

        return $custom_date_select;
    }

    private function get_where_report_period_date($field = 'date', $months_report='this_month')
    {
        $custom_date_select = '';
        if ($months_report != '') {
            if ($months_report == 'this_week') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' . date('Y-m-d', strtotime('monday this week')) . '" AND "' . date('Y-m-d', strtotime('sunday this week')) . '")';
            } elseif ($months_report == 'today') {
                $custom_date_select = 'AND (' . $field . ' = "' . date('Y-m-d') . '")';
            } elseif ($months_report == 'last_week') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' . date('Y-m-d', strtotime('monday last week')) . '" AND "' . date('Y-m-d', strtotime('sunday last week')) . '")';
            } elseif ($months_report == 'this_month') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' . date('Y-m-01') . '" AND "' . date('Y-m-t') . '")';
            } elseif ($months_report == 'last_month') {
                $beginLastMonth = to_sql_date(date('Y-m-01', strtotime('-1 MONTH')) );
                $endLastMonth   = to_sql_date(date('Y-m-t', strtotime('-1 MONTH')));
                $custom_date_select = '  ' . $field . ' BETWEEN "' . ($beginLastMonth) . '" AND "' . ($endLastMonth).'"';
            } elseif ($months_report == 'this_year') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' .
                    date('Y-m-d', strtotime(date('Y-01-01'))) .
                    '" AND "' .
                    date('Y-m-d', strtotime(date('Y-12-31'))) . '")';
            } elseif ($months_report == 'last_year') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' .
                    date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01'))) .
                    '" AND "' .
                    date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))) . '")';
            } elseif ($months_report == 'custom') {
                $from_date = to_sql_date($this->input->post('report_from'));
                $to_date   = to_sql_date($this->input->post('report_to'));
                if ($from_date == $to_date) {
                    $custom_date_select = 'AND ' . $field . ' = "' . $from_date . '"';
                } else {
                    $custom_date_select = 'AND (' . $field . ' BETWEEN "' . $from_date . '" AND "' . $to_date . '")';
                }
            }
        }

        return $custom_date_select;
    }


}