<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contract_webapp_model extends App_Model
{
    /** @var CI_DB_mysqli_driver */
    protected $contracts_db;

    public function __construct()
    {
        parent::__construct();

        // Connect to SECOND DB: "contracts_db" from application/config/database.php
        $this->contracts_db = $this->load->database('contracts_db', true);
    }

    /**
     * Agents dropdown – DISTINCT agent_id from tblcontract_applications
     */
  public function get_agents()
{
    // Step 1: Get distinct agent IDs from contracts DB
    $agent_ids = $this->contracts_db
        ->select('DISTINCT(agent_id) AS agent_id', false)
        ->from('tblcontract_applications')
        ->where('agent_id IS NOT NULL', null, false)
        ->get()
        ->result();

    if (empty($agent_ids)) {
        return [];
    }

    // Convert IDs to array
    $ids = array_map(fn($r) => $r->agent_id, $agent_ids);

    // Step 2: Use MAIN Perfex DB to get staff names
    return $this->db
        ->select('staffid as agent_id, CONCAT(firstname, " ", lastname) as agent_name')
        ->from('tblstaff')
        ->where_in('staffid', $ids)
        ->order_by('firstname', 'ASC')
        ->get()
        ->result();
}


    /**
     * Services dropdown – DISTINCT service_type from tblcontract_applications
     * We alias service_type as "id" so the view code can still use $srv->id
     */
 public function get_services()
{
    // Services come from contracts DB, table: tblleads_services (id, name)
    return $this->contracts_db
        ->select('id, name')
        ->from('tblleads_services')
        ->order_by('name', 'ASC')
        ->get()
        ->result();
}
 public function get_services_map()
    {
        $rows = $this->contracts_db
            ->select('id, name')
            ->from('tblleads_services')
            ->get()
            ->result();

        $map = [];
        foreach ($rows as $row) {
            $map[$row->id] = $row->name;
        }

        return $map;
    }

   // MAIN FUNCTION: fetch contracts with filters
    public function get_filtered_contracts($filters)
    {
        $db = $this->contracts_db;

        $db->from('tblcontract_applications');
        $db->where('deleted_status', 0);
        // Agent filter
        if (!empty($filters['agent_id'])) {
            $db->where('agent_id', $filters['agent_id']);
        }

        // Service filter
        if (!empty($filters['service_type'])) {
            $db->where('service_type', $filters['service_type']);
        }

        // Period filter
        switch ($filters['period']) {
            case 'today':
                $db->where('DATE(created_at)', date('Y-m-d'));
                break;

            case 'this_week':
                $db->where('YEARWEEK(created_at) =', date('oW'));
                break;

            case 'last_week':
                $db->where('YEARWEEK(created_at) =', date('oW') - 1);
                break;

            case 'this_month':
                $db->where('MONTH(created_at)', date('m'));
                $db->where('YEAR(created_at)', date('Y'));
                break;

            case 'last_month':
                $db->where('MONTH(created_at)', date('m') - 1);
                $db->where('YEAR(created_at)', date('Y'));
                break;

            case 'this_year':
                $db->where('YEAR(created_at)', date('Y'));
                break;

            case 'last_year':
                $db->where('YEAR(created_at)', date('Y') - 1);
                break;

            // Custom range
            case 'period':
                if (!empty($filters['date_from'])) {
                    $db->where('DATE(created_at) >=', $filters['date_from']);
                }
                if (!empty($filters['date_to'])) {
                    $db->where('DATE(created_at) <=', $filters['date_to']);
                }
                break;
        }

        return $db->get()->result();
    }
    public function get_agent_name($agent_id)
{
    if (!$agent_id) return '';

    $q = $this->db
        ->select('CONCAT(firstname, " ", lastname) AS name')
        ->from('tblstaff')
        ->where('staffid', $agent_id)
        ->get()
        ->row();

    return $q ? $q->name : $agent_id;
}

}

