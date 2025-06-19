<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Leads_customization extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('leads_customization_model');
    }

    public function services(){
        if(!is_admin()){
            access_denied('leads');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(leads_customization, 'tables/services'));
        }
        $data['title'] = _l('services');
        $data['services'] = $this->leads_customization_model->get_services();
        $this->load->view('leads_customization/services',$data);
    }

    public function get_services(){
        $services = $this->leads_customization_model->get_services();
        echo json_encode([
            'success' => true,
            'services' => $services
        ]);
    }

    public function report_table(){
        if ($this->input->is_ajax_request()) {
            $closed = '"Closings"';
            $select = [
                'CONCAT(tblstaff.firstname, " ", tblstaff.lastname) as name',
                'COUNT(CASE WHEN tblleads_status.name IN ('.$closed.') THEN tblleads.id END) as total_closed_leads',
            ];
            $where = [];
            $aColumns     = $select;
            $sIndexColumn = 'staffid';
            $sTable       = 'tblstaff';
            $join         = [
                'LEFT JOIN tblleads ON tblstaff.staffid = tblleads.assigned',
                'LEFT JOIN tblleads_status ON tblleads.status = tblleads_status.id',
            ];
            $groupBu = 'GROUP BY tblstaff.staffid';
            $filter = [];
            $months_report      = $this->input->post('report_months');
            $service      = $this->input->post('service');
            $custom_date_select = '';
            $field = 'tblleads.dateclosed';
            if ($months_report!=''){
                $custom_date_select = $this->get_where_report_period('DATE('.$field.')',$months_report);
            }
            if ($service!=''){
                array_push($filter, 'AND tblleads.service = '.$service);
            }
            array_push($filter, "AND tblleads.dateclosed is not null");
            if (isset($custom_date_select) and $custom_date_select != '') {
                array_push($filter, $custom_date_select);
            }
            if (count($filter) > 0) {
                array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
            }

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'tblstaff.staffid as staffid'
            ], $groupBu);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            foreach ($rResult as $aRow) {
                $row = [];
                if (is_admin($aRow['staffid'])) continue;
                $row[] = $aRow['name'];
                $row[] = $aRow['total_closed_leads'];
                $row[] = '';
                $row['DT_RowClass'] = 'has-row-options';
                $output['aaData'][] = $row;
            }

            echo json_encode($output);
            die();
        }
    }
    public function services_crud(){
        if ($this->input->post()){
            $data = $this->input->post();
            if (!$this->input->post('id')) {
                $id = $this->leads_customization_model->services_crud('add', $data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('leads_service')));
                }
            } else {
                $success = $this->leads_customization_model->services_crud('update', $data);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('leads_service')));
                }
            }
        }
    }
    public function delete_service($id){
        if (is_reference_in_table('service', db_prefix() . 'leads', $id)) {
            set_alert('warning', _l('is_referenced', _l('leads_service')));
        }else{
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'leads_services');
            if ($this->db->affected_rows() > 0) {
                set_alert('success', _l('deleted', _l('leads_service')));
            }else{
                set_alert('warning', _l('problem_deleting', _l('leads_service')));
            }
        }
        redirect(admin_url('leads_customization/services'));
    }

    public function languages(){
        if(!is_admin()){
            access_denied('leads');
        }
        $data['languages'] = $this->leads_customization_model->get_languages();
        $data['title'] = _l('languages');
        $this->load->view('leads_customization/languages',$data);
    }

    public function languages_crud(){
        if ($this->input->post()){
            $data = $this->input->post();
            if (!$this->input->post('id')) {
                $id = $this->leads_customization_model->languages_crud('add', $data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('leads_language')));
                }
            } else {
                $success = $this->leads_customization_model->languages_crud('update', $data);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('leads_language')));
                }
            }
        }
    }

    public function delete_language($id){
        if (is_reference_in_table('language', db_prefix() . 'leads', $id)) {
            set_alert('warning', _l('is_referenced', _l('leads_language')));
        }else{
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'leads_languages');
            if ($this->db->affected_rows() > 0) {
                set_alert('success', _l('deleted', _l('leads_language')));
            }else{
                set_alert('warning', _l('problem_deleting', _l('leads_language')));
            }
        }
        redirect(admin_url('leads_customization/languages'));
    }

    private function get_where_report_period($field = 'date',$months_report='this_month')
    {
        $custom_date_select = '';
        if ($months_report != '') {
            if ($months_report == 'this_week') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' . date('Y-m-d', strtotime('monday this week')) . '" AND "' . date('Y-m-d', strtotime('sunday this week')) . '")';
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