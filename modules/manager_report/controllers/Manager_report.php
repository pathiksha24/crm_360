<?php

class Manager_report extends AdminController
{


    private $manager_report_permission;

    public function __construct()
    {
        parent::__construct();
        $this->manager_report_permission = has_permission('manager_report','','manager_report');
        $this->load->model('manager_report_model');
    }

    public function index($manager_id= 0, $date = 0)
    {
        if (!$this->manager_report_permission) access_denied('manager_report');
        $data['title'] = _l('manager_report');
        $users = $this->staff_model->get();
        $managers = [];
        foreach ($users as $manager) {
            if (has_permission('manager_report',$manager['staffid'],'manager_report')){
                $managers[] = $manager;
            }
        }
        $data['managers'] = $managers;
        $data['services']  = $this->db->select('*')->from(db_prefix().'leads_services')->get()->result_array();
        $this->load->view('manager_report/index', $data);
    }

    public function table(){
        if ($this->input->is_ajax_request()) {
            $select = [
                'CONCAT(tblstaff.firstname, " ", tblstaff.lastname) as name',
                'COUNT(tblleads.id) as leads',
                'COUNT(CASE WHEN tblleads_status.name = "Call Connected" THEN tblleads.id END) as total_calls_connected_leads',
                'COUNT(CASE WHEN tblleads_status.name = "International Number" THEN tblleads.id END) as total_international_number_leads',
                'COUNT(CASE WHEN tblleads_status.name = "Lost" THEN tblleads.id END) as total_lost_leads',
                'COUNT(CASE WHEN tblleads_status.name = "Follow up" THEN tblleads.id END) as total_follow_up_leads',
                'COUNT(CASE WHEN (tblleads_status.name = "Closings" AND tblleads_sources.name = "Walk in") THEN tblleads.id END) as total_closing_from_walking_leads',
                'COUNT(CASE WHEN (tblleads_status.name = "Closings" AND tblleads_sources.name NOT IN ("Walk in", "Walk in from Ref.")) THEN tblleads.id END) as total_closing_from_leads',
                'COUNT(CASE WHEN (tblleads_status.name = "Closings" AND tblleads_sources.name = "Walk in from Ref.") THEN tblleads.id END) as total_closing_from_reference_leads',
                ];
            $where = [];
            $aColumns     = $select;
            $sIndexColumn = 'staffid';
            $sTable       = 'tblstaff';
            $join         = [
                'LEFT JOIN tblleads ON tblstaff.staffid = tblleads.assigned',
                'LEFT JOIN tblleads_status ON tblleads.status = tblleads_status.id',
                'LEFT JOIN tblleads_sources ON tblleads.source = tblleads_sources.id',
            ];
            $groupBu = 'GROUP BY tblstaff.staffid';
            $filter = [];
            $months_report      = $this->input->post('report_months');
            $date_by      = $this->input->post('date_by');
            $manager_id      = $this->input->post('manager_id');
            $service_id      = $this->input->post('service_id');

            $custom_date_select = '';
            $field = 'tblleads.'.$date_by;
            if ($months_report!=''){
                $custom_date_select = $this->get_where_report_period('DATE('.$field.')',$months_report);
            }
            if (isset($custom_date_select) and $custom_date_select != '') {
                array_push($filter, $custom_date_select);
            }

            if ($manager_id !== 0 && $manager_id !== '0' && $manager_id !== '' && is_admin()){
                array_push($where, 'AND (tblstaff.manager_id = ' . $manager_id.')');
            }

            if (!empty($service_id) && is_admin()){
                array_push($where, 'AND (tblleads.service IN(' . implode(' ,',$service_id).'))');
            }
            if (!is_admin()){
                array_push($where, 'AND (tblstaff.manager_id = ' . get_staff_user_id().')');
            }
            if (count($filter) > 0) {
                array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
            }

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'tblstaff.staffid as staffid'
            ], $groupBu);

            $output  = $result['output'];
            $rResult = $result['rResult'];
            $footer_data['total_leads'] = 0;
            $footer_data['total_calls_connected'] = 0;
            $footer_data['total_international_number'] = 0;
            $footer_data['total_lost'] = 0;
            $footer_data['total_followup'] = 0;
            $footer_data['total_closing_from_walkin'] = 0;
            $footer_data['total_closing_from_leads'] = 0;
            $footer_data['total_closing_from_reference'] = 0;
            foreach ($rResult as $aRow) {
                $row = [];
                $row[] = $aRow['name'];
                $row[] = $aRow['leads'];
                $row[] = $aRow['total_calls_connected_leads'];
                $row[] = $aRow['total_international_number_leads'];
                $row[] = $aRow['total_lost_leads'];
                $row[] = $aRow['total_follow_up_leads'];
                $row[] = $aRow['total_closing_from_walking_leads'];
                $row[] = $aRow['total_closing_from_leads'];
                $row[] = $aRow['total_closing_from_reference_leads'];
                $row['DT_RowClass'] = 'has-row-options';
                $footer_data['total_leads'] += $aRow['leads'];
                $footer_data['total_calls_connected'] += $aRow['total_calls_connected_leads'];
                $footer_data['total_international_number'] += $aRow['total_international_number_leads'];
                $footer_data['total_lost'] += $aRow['total_lost_leads'];
                $footer_data['total_followup'] += $aRow['total_follow_up_leads'];
                $footer_data['total_closing_from_walkin'] += $aRow['total_closing_from_walking_leads'];
                $footer_data['total_closing_from_leads'] += $aRow['total_closing_from_leads'];
                $footer_data['total_closing_from_reference'] += $aRow['total_closing_from_reference_leads'];
                $output['aaData'][] = $row;
            }
            $output['sums']              = $footer_data;


            echo json_encode($output);
            die();
        }
    }

    private function get_where_report_period($field = 'date',$months_report='this_month')
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