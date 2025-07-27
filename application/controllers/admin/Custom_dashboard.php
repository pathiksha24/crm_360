    <?php

    defined('BASEPATH') or exit('No direct script access allowed');

    class Custom_dashboard extends AdminController
    {
        public function __construct()
        {
            parent::__construct();
            $this->load->helper('custom_widgets');
            $this->lang->load('custom', 'english');
            // Access control
            if (!is_admin()) {
                access_denied('Custom Dashboard');  
            }
        }

        public function index()
        {
            $data['title'] = _l('custom_dashboard');
            $data['team_data'] = $this->build_team_data();
            $this->load->view('admin/custom_dashboard/index', $data);
        }

    public function ajax_team_task_summary()
    {
        $from = $this->input->post('from_date');
        if (!$from || !preg_match('/\d{4}-\d{2}-\d{2}/', $from)) {
            $from = null;
        }

        $data['team_data'] = $this->build_team_data($from);

        // Debug logging:
        log_message('error', 'AJAX build_team_data() triggered for date: ' . $from);

        $this->load->view('admin/custom_widgets/task_summary_table', $data);
    }


    private function build_team_data($from_date = null)
{
    // Team leader → Staff mapping
    $team_leaders = [
        1   => 'Khaled',
        73  => 'Amith',
        159 => 'Faiza',
        160 => 'Shivam',
        78  => 'Ilyas',
        94  => 'Khuwaja',
        87  => 'Ziya',
    ];

    $team_members = [
        1   => [80, 16, 83, 82],
        73  => [107, 63, 97, 226, 177],
        159 => [71, 108, 79, 198],
        160 => [53, 102,44, 142, 60],
        78  => [62, 69, 9, 27, 21, 25],
        94  => [5, 86, 65, 8, 68],
        87  => [15, 93, 29, 202, 175],
    ];

    $all_staff_ids = array_merge(...array_values($team_members));

    // Fetch all "work permit" service IDs dynamically
    $work_permit_services = $this->db->select('id')
        ->from('tblleads_services')
        ->like('name', 'work permit', 'both')
        ->get()
        ->result_array();

    $work_permit_ids = array_column($work_permit_services, 'id');

    // Define other categories
    $service_id_to_category = [
        127 => 'business_setup',
        146 => 'business_setup_europe',
    ];

    // Fetch activity logs from the selected date
    $this->db->select('la.additional_data, la.date, l.service');
    $this->db->from('tbllead_activity_log la');
    $this->db->join('tblleads l', 'la.leadid = l.id');
    $this->db->where('la.description', 'not_lead_activity_assigned_to');
     // Include only these staff IDs
   $this->db->where_in('la.staffid', [59, 55, 14, 72, 163, 216, 34, 20, 214, 225]);
    if ($from_date) {
        // Use strict date match (e.g., only 2025-07-15)
        $this->db->where('DATE(la.date)', $from_date);
    }
    // Exclude leads with tags 145 or 146 (tag exclude wtsp)
    $this->db->where("NOT EXISTS (
        SELECT 1
        FROM tbltaggables tg
        WHERE tg.rel_id = l.id
        AND tg.rel_type = 'lead'
        AND tg.tag_id IN (145, 146)
    )", null, false);

    // Exclude certain services
    $this->db->where_not_in('l.service', [198, 168]);
    //$this->db->where_not_in('l.source', [5, 45]);// wlkin and reference

    $logs = $this->db->get()->result_array();

    $counts_by_staff = [];

    foreach ($logs as $log) {
        $service_id = (int) $log['service'];

        // Determine category
        if (in_array($service_id, $work_permit_ids)) {
            $category = 'work_permit';
        } elseif (isset($service_id_to_category[$service_id])) {
            $category = $service_id_to_category[$service_id];
        } else {
            $category = 'other';
        }

        // Extract staff ID from additional_data via profile URL
        if (preg_match('/profile\/(\d+)/', $log['additional_data'], $match)) {
            $staff_id = (int) $match[1];

            // Only include defined staff members
            if (!in_array($staff_id, $all_staff_ids)) continue;

            if (!isset($counts_by_staff[$staff_id])) {
                $counts_by_staff[$staff_id] = [
                    'business_setup'         => 0,
                    'business_setup_europe'  => 0,
                    'work_permit'            => 0,
                    'other'                  => 0,
                ];
            }

            $counts_by_staff[$staff_id][$category]++;
        }
    }

    // Get staff names
    $staff_names = $this->db->select('staffid, CONCAT(firstname, " ", lastname) AS name')
        ->get('tblstaff')
        ->result_array();
    $staff_name_map = array_column($staff_names, 'name', 'staffid');

    // Group results by team leader
    $grouped = [];

    foreach ($team_members as $leader_id => $staff_ids) {
        $grouped[$leader_id]['leader_name'] = $team_leaders[$leader_id];
        $grouped[$leader_id]['members'] = [];

        foreach ($staff_ids as $sid) {
            $c = $counts_by_staff[$sid] ?? [
                'business_setup'         => 0,
                'business_setup_europe'  => 0,
                'work_permit'            => 0,
                'other'                  => 0,
            ];

            $grouped[$leader_id]['members'][] = [
                'name'     => $staff_name_map[$sid] ?? 'Unknown',
                'setup'    => $c['business_setup'],
                'setup_eu' => $c['business_setup_europe'],
                'permit'   => $c['work_permit'],
                'other'    => $c['other'],
                'total'    => array_sum($c),
            ];
        }
    }

    return $grouped;
}


    }
