        <?php

        defined('BASEPATH') or exit('No direct script access allowed');

        class Call_center_dashboard extends AdminController
        {
            public function __construct()
            {
                parent::__construct();
                $this->load->helper('custom_widgets');
                $this->lang->load('custom', 'english');
                // Access control
                if (!is_admin()) {
                    access_denied('Call Center Lead Dashboard');  
                }
            }

            public function index()
            {

                $data['title'] = _l('custom_dashboard');
                $data['team_data'] = $this->build_team_data();
                $this->load->view('admin/callcenter_dashboard/index', $data);
            }

        public function ajax_callcenter_summary()
        {
            $from = $this->input->post('from_date');
            if (!$from || !preg_match('/\d{4}-\d{2}-\d{2}/', $from)) {
                $from = null;
            }

            $data['team_data'] = $this->build_team_data($from);

            // Debug logging:

            $this->load->view('admin/custom_widgets/cc_dashboard_table', $data);
        }

private function build_team_data($from_date = null)
{
    $CI =& get_instance();

    // -----------------------------------
    // 0. Call Center Staff IDs
    // -----------------------------------
    $staff_ids = [14, 59, 55, 216, 214, 72, 20, 34, 163];
    $callcenter_ids = $staff_ids;

    // -----------------------------------
    // 1. ASSIGNED LEAD COUNT (to CC Staff)
    // -----------------------------------
    $CI->db->select('la.leadid, la.additional_data, la.date');
    $CI->db->from('tbllead_activity_log la');
    $CI->db->join('tblleads l', 'l.id = la.leadid');
    $CI->db->where('la.description', 'not_lead_activity_assigned_to');

    if ($from_date) {
        $CI->db->where('DATE(la.date)', $from_date);
    }

    $assigned_logs = $CI->db->get()->result_array();

    $assigned_counts = [];

    foreach ($assigned_logs as $log) {
        if (preg_match('/profile\/(\d+)/', $log['additional_data'], $match)) {
            $sid = (int) $match[1];
            if (in_array($sid, $staff_ids)) {
                $assigned_counts[$sid] = ($assigned_counts[$sid] ?? 0) + 1;
            }
        }
    }

    // -----------------------------------
    // 2. TRANSFERRED LEAD COUNT (from CC to Sales)
    // -----------------------------------
    $CI->db->select('LAL.*');
    $CI->db->from(db_prefix() . 'lead_activity_log AS LAL');
    $CI->db->join(db_prefix() . 'leads AS TL', 'TL.id = LAL.leadid', 'inner');
    $CI->db->where('LAL.description', 'not_lead_activity_assigned_to');
    $CI->db->where_in('LAL.staffid', $callcenter_ids);
    $CI->db->where('LAL.additional_data !=', '');
    $CI->db->where_in('TL.status', [2, 7, 8, 5]);
    $CI->db->where_not_in('TL.service', [198, 168]);
    $CI->db->where("NOT EXISTS (
        SELECT 1 
        FROM " . db_prefix() . "taggables AS TT
        WHERE TT.rel_type = 'lead' 
          AND TT.rel_id = TL.id 
          AND TT.tag_id IN (145, 146)
    )", null, false);

    if ($from_date) {
        $CI->db->where('DATE(LAL.date)', $from_date);
    }

    $transfer_logs = $CI->db->get()->result_array();

    $transferred_counts = [];

    foreach ($transfer_logs as $log) {
        $assigner_id = $log['staffid'];
        $data = @unserialize($log['additional_data']);

        if (!is_array($data) || !isset($data[1])) {
            continue;
        }

        if (!preg_match('/profile\/(\d+)/', $data[1], $id_match)) {
             
            continue;
        }

        $assignee_id = (int)$id_match[1];

        // Skip internal CC assignments or self-assignments
        if ($assigner_id == $assignee_id || in_array($assignee_id, $callcenter_ids)) {
            continue;
        }

        $transferred_counts[$assigner_id] = ($transferred_counts[$assigner_id] ?? 0) + 1;
    }

    // -----------------------------------
    // 3. PENDING LEADS (assigned to CC, no status change)
    // -----------------------------------
    $CI->db->select('assigned');
    $CI->db->from(db_prefix() . 'leads');
    $CI->db->where_in('assigned', $staff_ids);
    $CI->db->where('last_status_change IS NULL', null, false);

    if ($from_date) {
        $CI->db->where('DATE(dateadded)', $from_date);
    }

    $pending_rows = $CI->db->get()->result_array();

    $pending_counts = [];

    foreach ($pending_rows as $row) {
        $sid = (int)$row['assigned'];
        $pending_counts[$sid] = ($pending_counts[$sid] ?? 0) + 1;
    }

    // -----------------------------------
    // 4. Fetch Staff Names
    // -----------------------------------
    $staff_name_map = [];
    $staff_list = $CI->db->select('staffid, CONCAT(firstname, " ", lastname) AS name')
                         ->from('tblstaff')
                         ->where_in('staffid', $staff_ids)
                         ->get()
                         ->result_array();

    foreach ($staff_list as $staff) {
        $staff_name_map[$staff['staffid']] = $staff['name'];
    }

    // -----------------------------------
    // 5. Build Final Output
    // -----------------------------------
    $output = [
        0 => [
            'leader_name' => 'Fixed Group',
            'members' => []
        ]
    ];

    foreach ($staff_ids as $sid) {
        $assigned    = $assigned_counts[$sid] ?? 0;
        $transferred = $transferred_counts[$sid] ?? 0;
        $pending     = $pending_counts[$sid] ?? 0;

        $output[0]['members'][] = [
            'staff_id'    => $sid,
            'name'        => $staff_name_map[$sid] ?? 'Unknown',
            'assigned'    => $assigned,
            'transferred' => $transferred,
            'pending'     => $pending,
            'total'       => $assigned + $transferred + $pending,
        ];
    }

    return $output;
}

        }