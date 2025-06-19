<?php

class Auto_assign_leads extends AdminController
{


    private $auto_assign_leads_permission;

    public function __construct()
    {
        parent::__construct();
        $this->auto_assign_leads_permission = has_permission('auto_assign_leads', '', 'auto_assign_leads');
        $this->load->model('auto_assign_leads_model');
    }

    public function index()
    {
        if (!$this->auto_assign_leads_permission) access_denied('auto_assign_leads');
        $data['title'] = _l('auto_assign_leads');
        $users = $this->staff_model->get();
        $data['staff'] = $users;
        $data['services']  = $this->db->select('*')->from(db_prefix() . 'leads_services')->get()->result_array();
        $data['sources']   = $this->db->select('*')->from(db_prefix() . 'leads_sources')->get()->result_array();
        $this->load->view('auto_assign_leads/index', $data);
    }

    public function auto_assign()
    {
        if (!$this->auto_assign_leads_permission) {
            access_denied('auto_assign_leads');
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            $staff_ids   = $data['staff_ids'];
            $service_ids = $data['service_ids'];
            $source_ids  = $data['source_ids'];
            $max_leads_to_distribute = $data['max_leads_to_distribute'];
            $max_leads_for_each_staff = $data['max_leads_for_each_staff'];
            update_option('max_leads_to_distribute', $max_leads_to_distribute);
            update_option('max_leads_for_each_staff', $max_leads_for_each_staff);

            $this->load->model('leads_model');

//            $this->db->where('assigned', 0);
//            $this->db->order_by('dateadded', 'asc');
//            $leads_not_assigned = $this->db->select('id')->get('tblleads')->result_array();
            $this->db->where_in('source',$source_ids);
            $this->db->order_by('dateadded', 'ASC');
            $leads_not_assigned = $this->leads_model->get('', ['assigned' => 0]);


            $leads = [];
            $total_all_leads = 0;

            $leadIds = [];
            $leadLogIds = [];
            $logLeadActivity = [];

            // Group leads by service
            foreach ($leads_not_assigned as $lead) {

                if (in_array($lead['service'], $service_ids)) {
                    $leads[$lead['service']][] = $lead;
                }
            }

            $total_assigned_leads = 0;
            $staff_each = [];

            // Distribute leads among staff for each service
            foreach ($service_ids as $service_id) {

                if (!isset($leads[$service_id])) {
                    continue; // No leads for this service
                }

                $total_leads = count($leads[$service_id]);
                $leads_per_staff = intdiv($total_leads, count($staff_ids)); // Integer division to avoid decimals
                $remaining_leads = $total_leads % count($staff_ids); // Handling remaining leads after division

                $lead_index = 0;

                foreach ($staff_ids as $staff_id) {

                    // Track how many leads each staff has been assigned
                    if (!isset($staff_each[$staff_id])) {
                        $staff_each[$staff_id] = 0;
                    }

                    $assign_count = $leads_per_staff + ($remaining_leads > 0 ? 1 : 0); // Assign extra lead to some staff if remainder exists
                    $assign_count = min($assign_count, $max_leads_for_each_staff - $staff_each[$staff_id]); // Ensure the staff does not exceed max allowed
                    $remaining_leads--;

                    // Assign leads to this staff
                    for ($i = 0; $i < $assign_count && $lead_index < $total_leads; $i++, $lead_index++) {
                        $lead = $leads[$service_id][$lead_index];
                        $this->db->where('id', $lead['id']);
                        $this->db->update(db_prefix() . 'leads', ['assigned' => $staff_id, 'dateassigned' => date('Y-m-d H:i:s')]);
                        $not_additional_data = [
                            get_staff_full_name($staff_id),
                            '<a href="' . admin_url('profile/' . $staff_id) . '" target="_blank">' . get_staff_full_name($staff_id) . '</a>',
                        ];
                        $not_additional_data = serialize($not_additional_data);
                        $lead_log_activity_id =  $this->leads_model->log_lead_activity($lead['id'], 'not_lead_activity_assigned_to', false, $not_additional_data);

                        array_push($logLeadActivity, $lead_log_activity_id);

                        $log = [
                            'admin' => get_staff_user_id(),
                            'lead_id' => $lead['id'],
                            'staff_id' => $staff_id,
                            'service_id' => $service_id,
                            'date' => to_sql_date(date('Y-m-d H:i:s'), true)
                        ];

                        $this->db->insert(db_prefix() . 'auto_assign_leads_log', $log);

                        $lead_log_id = $this->db->insert_id();

                        array_push($leadIds, $lead['id']);
                        array_push($leadLogIds, $lead_log_id);
                        
                        $staff_each[$staff_id]++; // Increment the count for this staff
                        $total_assigned_leads++; // Increment the total assigned leads

                        // Stop assigning if we reach the max leads to distribute
                        if ($total_assigned_leads >= $max_leads_to_distribute) {
                            break 3; // Exit both loops once max leads are distributed
                        }
                    }

                }
            }

            if (count($leadIds)) {
                update_option('auto_assign_leads', json_encode($leadIds), '1');
                update_option('auto_assign_leads_logs', json_encode($leadLogIds), '1');
                update_option('auto_assign_leads_activity_logs', json_encode($logLeadActivity), '1');
            }

            $text = $total_assigned_leads . ' Assigned To ' . count($staff_ids) . ' Staff';
            set_alert('success', $text);
            redirect('auto_assign_leads');
        }
    }

    public function table()
    {

        if ($this->input->is_ajax_request()) {
            $select = [
                "tblauto_assign_leads_log.staff_id as agent",
                "tblauto_assign_leads_log.service_id as service_assigned",
                "COUNT(lead_id) as total_assigned_leads",
                "DATE_FORMAT(date, '%Y-%m-%d') as assigned_date"
            ];
            $where = [];
            $aColumns     = $select;
            $sIndexColumn = 'id';
            $sTable       = 'tblauto_assign_leads_log';
            $join         = [
                'LEFT JOIN tblstaff ON tblstaff.staffid = tblauto_assign_leads_log.staff_id',
                'LEFT JOIN tblleads_services ON tblleads_services.id = tblauto_assign_leads_log.service_id',
            ];
            $groupBy = 'GROUP BY tblauto_assign_leads_log.staff_id, tblauto_assign_leads_log.service_id, DATE_FORMAT(date, "%Y-%m-%d")';
            
            if ($this->input->post('lead_log_date')) {
                array_push($where, 'AND DATE(date) = "' . to_sql_date($this->input->post('lead_log_date')) . '"');
            }


            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'tblstaff.firstname as firstname',
                'tblstaff.lastname as lastname',
                'tblleads_services.name as service_name',
            ], $groupBy);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            foreach ($rResult as $aRow) {
                $row = [];
                $row[] = $aRow['firstname'] . ' ' . $aRow['lastname'];
                $row[] = $aRow['service_name'];
                $row[] = $aRow['total_assigned_leads'];
                $row[] = $aRow['assigned_date'];
                $row['DT_RowClass'] = 'has-row-options';
                $output['aaData'][] = $row;
            }

            echo json_encode($output);
            die();
        }
    }

    public function revert_last_auto_assign()
    {

        if (get_option('auto_assign_leads')) {

            $leadIds = json_decode(get_option('auto_assign_leads'));
            $leadLogIds = json_decode(get_option('auto_assign_leads_logs'));
            $logLeadActivity = json_decode(get_option('auto_assign_leads_activity_logs'));


            $this->db->where_in('id', $leadIds);
            $this->db->update(db_prefix() . 'leads', ['assigned' => 0]);


            $this->db->where_in('id', $leadLogIds);
            $this->db->delete(db_prefix() . 'auto_assign_leads_log');


            $this->db->where_in('id', $logLeadActivity);
            $this->db->delete(db_prefix() . 'lead_activity_log');

            delete_option('auto_assign_leads');
            delete_option('auto_assign_leads_logs');
            delete_option('auto_assign_leads_activity_logs');

            set_alert('success', _l('undo_successfully'));
        }




        redirect(admin_url('auto_assign_leads'));
    }
}
