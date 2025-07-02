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
        $data['saved_assignments'] = $this->auto_assign_leads_model->get_saved_assignments();
        $data['saved_services'] = $this->auto_assign_leads_model->get_saved_services();
        $data['edit_services'] = $this->auto_assign_leads_model->edit_saved_assignments();
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

public function save_new_filter() 
{
    if ($this->input->post()) {
        $filter_name = $this->input->post('filter_name');
        $service_ids = $this->input->post('modal_service_ids');
        $staff_ids   = $this->input->post('modal_staff_ids');

        // Validate required inputs
        if (empty($service_ids) || empty($staff_ids)) {
            set_alert('danger', _l('please_select_at_least_one_service_and_staff'));
            redirect(admin_url('auto_assign_leads'));
        }

        $current_user_id = get_staff_user_id();
        $current_datetime = date('Y-m-d H:i:s');

        $assignments_saved = 0;
        $failed_assignments = 0;
        $duplicates = []; // To hold already existing staff-service pairs

        foreach ($staff_ids as $staff_id) {
            $staff_data = $this->auto_assign_leads_model->get_staff_by_id($staff_id);
            $staff_name = isset($staff_data['firstname'], $staff_data['lastname']) 
                          ? $staff_data['firstname'] . ' ' . $staff_data['lastname'] 
                          : 'Unknown Staff';

            foreach ($service_ids as $service_id) {
                $service_data = $this->auto_assign_leads_model->get_service_by_id($service_id);
                $service_name = isset($service_data['name']) ? $service_data['name'] : 'Unknown Service';

                // Check if this staff-service pair with active status already exists
                $exists = $this->auto_assign_leads_model->check_assignment_exists($staff_id, $service_id);

                if ($exists) {
                    $duplicates[] = $staff_name . ' - ' . $service_name;
                    continue; // Skip saving
                }

                $data_to_save = [
                    'staffid'       => $staff_id,
                    'serviceid'     => $service_id,
                    'staffname'     => $staff_name,
                    'service_name'  => $service_name,
                    'status'        => 1, // Active
                    'created_date'  => $current_datetime,
                    'created_id'    => $current_user_id,
                ];

                $insert_id = $this->auto_assign_leads_model->assign_staff_service($data_to_save);

                if ($insert_id) {
                    $assignments_saved++;
                } else {
                    $failed_assignments++;
                }
            }
        }

        // Show flash alerts based on the result
        if ($assignments_saved > 0) {
            set_alert('success', _l('total_assignments_saved', $assignments_saved));
        }

        if ($failed_assignments > 0) {
            set_alert('warning', _l('total_assignments_failed', $failed_assignments));
        }

        if (!empty($duplicates)) {
            $duplicate_msg = implode(', ', $duplicates);
            set_alert('warning', 'The following assignments already exist: ' . $duplicate_msg);
        }

        if ($assignments_saved == 0 && $failed_assignments == 0 && empty($duplicates)) {
            set_alert('info', _l('no_assignments_were_created'));
        }

        redirect(admin_url('auto_assign_leads'));

    } else {
        set_alert('danger', _l('error_saving_filter_invalid_request'));
        redirect(admin_url('auto_assign_leads'));
    }
}

    public function update_assignment_status()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');       // Assignment ID from data-id
            $status = $this->input->post('status'); // New status (0 or 1)

            // Basic validation
            if (!is_numeric($id) || !in_array($status, [0, 1])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => _l('invalid_request')]);
                die();
            }
            $success = $this->auto_assign_leads_model->update_assignment_status($id, $status);
            header('Content-Type: application/json');
            if ($success) {
                echo json_encode(['success' => true, 'message' => _l('assignment_status_updated_successfully')]);
            } else {
                echo json_encode(['success' => false, 'message' => _l('failed_to_update_assignment_status')]);
            }
            die(); // Stop further execution after sending JSON response
        }
        redirect(admin_url('auto_assign_leads')); // Redirect if not an AJAX request
    }
    public function delete_assignment()
{
    if (!has_permission('auto_assign_leads', '', 'delete')) {
        access_denied('auto_assign_leads');
    }
    
    if ($this->input->post()) {
        $id = $this->input->post('id');
        
        $this->db->where('id', $id);
        $success = $this->db->delete(db_prefix() . 'staff_services_assigning');
        
        if ($success) {
            $message = _l('deleted', _l('assignment'));
            echo json_encode([
                'success' => true,
                'message' => $message
            ]);
        } else {
            $message = _l('problem_deleting', _l('assignment'));
            echo json_encode([
                'success' => false,
                'message' => $message
            ]);
        }
        die();
    }
}
public function get_staff_by_services()
{
    $service_ids = $this->input->post('service_ids');

    if (empty($service_ids) || !is_array($service_ids)) {
        echo json_encode([]);
        return;
    }

    $service_ids = array_map('intval', $service_ids);

    $staff = $this->db->select('ssa.staffid, s.firstname as staffname')
        ->from(db_prefix() . 'staff_services_assigning as ssa')
        ->join(db_prefix() . 'staff as s', 'ssa.staffid = s.staffid')
        ->where_in('ssa.serviceid', $service_ids)
        ->where('ssa.status', 1)
        ->group_by('ssa.staffid')
        ->get()
        ->result_array();

    header('Content-Type: application/json');
    echo json_encode($staff);
    exit;
}

public function bulk_update_service() {
    $staff_ids       = $this->input->post('staff_ids');
    $new_service_ids = $this->input->post('new_service_id'); // array of service IDs
    $old_service_id  = $this->input->post('old_service_id');
    
    if ($staff_ids && $old_service_id) {
        $updated = 0;
        $reactivated = 0;
        $deleted = 0;
        $skipped_staff = [];

        // Check if the filtered service is still selected in new_service_ids
        $filtered_service_selected = in_array($old_service_id, $new_service_ids);
        
        if (!$filtered_service_selected) {
            // If filtered service is NOT selected, DELETE staff from that service
            foreach ($staff_ids as $staff_id) {
                $this->db->where('staffid', $staff_id);
                $this->db->where('serviceid', $old_service_id);
                $result = $this->db->delete('tblstaff_services_assigning');
                
                if ($result) {
                    $deleted++;
                }
            }
        }

        // Add staff to selected services (including keeping them in filtered service if selected)
        if (!empty($new_service_ids)) {
            foreach ($new_service_ids as $new_service_id) {
                if (empty($new_service_id)) continue;
                
                // Get the service details
                $this->db->where('id', $new_service_id);
                $service = $this->db->get('tblleads_services')->row();
                
                if (!$service) {
                    continue;
                }

                foreach ($staff_ids as $staff_id) {
                    // Check if staff is already assigned to this service
                    $this->db->where('staffid', $staff_id);
                    $this->db->where('serviceid', $new_service_id);
                    $existing_assignment = $this->db->get('tblstaff_services_assigning')->row();

                    $staff_fullname = $this->get_staff_fullname($staff_id);

                    if (!$existing_assignment) {
                        // Insert new assignment
                        $data_to_save = [
                            'staffid'      => $staff_id,
                            'serviceid'    => $new_service_id,
                            'staffname'    => $staff_fullname,
                            'service_name' => $service->name,
                            'status'       => 1,
                            'created_date' => date('Y-m-d H:i:s'),
                            'created_id'   => get_staff_user_id(),
                        ];

                        $this->db->insert('tblstaff_services_assigning', $data_to_save);
                        $updated++;
                    } else {
                        // Reactivate/update existing assignment
                        $update_data = [
                            'staffname'    => $staff_fullname,
                            'service_name' => $service->name,
                            'status'       => 1,
                            'created_date' => date('Y-m-d H:i:s'),
                            'created_id'   => get_staff_user_id(),
                        ];

                        $this->db->where('id', $existing_assignment->id);
                        $this->db->update('tblstaff_services_assigning', $update_data);

                        $reactivated++;

                        if ($new_service_id != $old_service_id) {
                            $skipped_staff[$staff_id][] = $service->name . ' (reactivated)';
                        }
                    }
                }
            }
        }

        // Show appropriate messages
        $messages = [];
        if ($deleted > 0) {
            $messages[] = "$deleted staff member(s) removed from filtered service";
        }
        if ($updated > 0) {
            $messages[] = "$updated new service assignment(s) added";
        }
        if ($reactivated > 0) {
            $messages[] = "$reactivated existing assignment(s) reactivated";
        }
        if (!empty($skipped_staff)) {
            $messages[] = "Some staff were already assigned to selected services";
        }

        if (!empty($messages)) {
            set_alert('success', implode('. ', $messages) . '.');
        } else {
            set_alert('info', 'No changes were made.');
        }

    } else {
        set_alert('warning', 'Please select staff and a service to filter.');
    }

    redirect(admin_url('auto_assign_leads'));
}

// Helper function to get full staff name
protected function get_staff_fullname($staff_id) {
    $this->db->select('firstname, lastname');
    $this->db->where('staffid', $staff_id);
    $staff = $this->db->get('tblstaff')->row();

    if ($staff) {
        return $staff->firstname . ' ' . $staff->lastname;
    }

    return '';
}

public function get_unassigned_staff_for_service()
{
    $service_id = $this->input->post('service_id');
    if (!$service_id) {
        echo json_encode([]);
        return;
    }

    $query = $this->db->query("
        SELECT s.staffid, s.firstname, s.lastname
        FROM " . db_prefix() . "staff s
        WHERE s.staffid NOT IN (
            SELECT staffid
            FROM " . db_prefix() . "staff_services_assigning
            WHERE serviceid = ? AND status = 1
        )
        AND (
            s.staffid NOT IN (
                SELECT staffid
                FROM " . db_prefix() . "staff_services_assigning
                WHERE serviceid = ?
            )
            OR s.staffid IN (
                SELECT staffid
                FROM " . db_prefix() . "staff_services_assigning
                WHERE serviceid = ? AND status = 0
            )
        )
    ", [$service_id, $service_id, $service_id]);

    $result = $query->result_array();

    $staff = array_map(function ($s) {
        return [
            'staffid'   => $s['staffid'],
            'staffname' => $s['firstname'] . ' ' . $s['lastname'],
        ];
    }, $result);

    echo json_encode($staff);
}



public function delete_staff_service_assignment()
{
    $staff_id   = $this->input->post('staff_id');
    $service_id = $this->input->post('service_id');

    if (!$staff_id || !$service_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        return;
    }

    $this->db->where('staffid', $staff_id);
    $this->db->where('serviceid', $service_id);
    $deleted = $this->db->delete(db_prefix() . 'staff_services_assigning');

    if ($deleted) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
public function bulk_update_status()
{
    if ($this->input->is_ajax_request()) {
        // Expect an array of IDs and a single status
        $ids    = $this->input->post('ids');    
        $status = $this->input->post('status');

        // Validation
        if (empty($ids) || !is_array($ids) || !in_array($status, ['0', '1'], true)) {
            return $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            'success' => false,
                            'message' => _l('invalid_request')
                        ]));
        }

        // Cast status to integer
        $status = (int) $status;

        // Call the model
        $updated = $this->auto_assign_leads_model->bulk_update_status($ids, $status);

        return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'success' => $updated,
                        'message' => $updated 
                            ? _l('bulk_status_update_success') 
                            : _l('bulk_status_update_failed')
                    ]));
    }

    // Fallback redirect for non-AJAX
    redirect(admin_url('auto_assign_leads'));
}


}