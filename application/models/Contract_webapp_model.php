<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contract_webapp_model extends App_Model
{
    /** @var CI_DB_mysqli_driver */
    protected $contracts_db;

    public function __construct()
    {
        parent::__construct();

        // SECOND DB: "contracts_db" from application/config/database.php
        $this->contracts_db = $this->load->database('contracts_db', true);
    }

    /* -------------------------------------------------------------
     *   DROPDOWNS
     * ---------------------------------------------------------- */

    // All agents from main CRM DB (u619291874_quickplus_crm.tblstaff)
    public function get_agents()
    {
        return $this->db
            ->select('staffid AS agent_id, CONCAT(firstname, " ", lastname) AS agent_name')
            ->from('tblstaff')
            ->where('active', 1) // only active staff
            ->order_by('firstname', 'ASC')
            ->get()
            ->result();
    }
    public function get_team_leader()
    {
        return $this->db
            ->select('staffid AS agent_id, CONCAT(firstname, " ", lastname) AS agent_name')
            ->from('tblstaff')
            ->where('is_team_leader', 1)
            ->order_by('firstname', 'ASC')
            ->get()
            ->result();
    }

    // Map staffid => name to avoid N queries
    public function get_agents_map()
    {
        $rows = $this->get_agents();
        $map  = [];
        foreach ($rows as $r) {
            $map[$r->agent_id] = $r->agent_name;
        }
        return $map;
    }

    // Services for dropdown (contracts DB)
    public function get_services()
    {
        return $this->contracts_db
            ->select('id, name')
            ->from('tblleads_services')
            ->order_by('name', 'ASC')
            ->get()
            ->result();
    }

    // Map service id => name
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

    /* -------------------------------------------------------------
     *   MAIN QUERY
     * ---------------------------------------------------------- */

    // Helper: convert datepicker (24-08-2025) to SQL (2025-08-24)
    private function normalize_date($date)
    {
        if (empty($date)) {
            return null;
        }

        $date = str_replace('/', '-', trim($date));
        $ts   = strtotime($date);

        if (!$ts) {
            return null;
        }

        return date('Y-m-d', $ts);
    }

    // Fetch contracts with filters
    public function get_filtered_contracts($filters)
    {
        $db = $this->contracts_db;

        $db->from('tblcontract_applications');

        // show only not-deleted
        $db->where('deleted_status', 0);

        // Agent filter
        if (!empty($filters['agent_id'])) {
            $db->where('agent_id', $filters['agent_id']);
        }

        // Service filter (multi-select)
        if (!empty($filters['service_type'])) {
            if (is_array($filters['service_type'])) {
                $db->where_in('service_type', $filters['service_type']);
            } else {
                $db->where('service_type', $filters['service_type']);
            }
        }
        // Period filter
        $period = isset($filters['period']) ? $filters['period'] : 'all';

        switch ($period) {
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

            case 'period': // custom period from datepickers
                $from = $this->normalize_date($filters['date_from'] ?? '');
                $to   = $this->normalize_date($filters['date_to'] ?? '');

                if ($from) {
                    $db->where('DATE(created_at) >=', $from);
                }
                if ($to) {
                    $db->where('DATE(created_at) <=', $to);
                }
                break;

            case 'all':
            default:
                // no extra where
                break;
        }

        // newest contracts first
        $db->order_by('created_at', 'DESC');
        $rows = $db->get()->result();
        if (!empty($filters['team_leader_id'])) {
            $filtered = [];
            foreach ($rows as $row) {
                $teamLeaderId = $this->get_team_leader_id($row->agent_id);
                if ((string) $teamLeaderId === (string) $filters['team_leader_id']) {
                    $filtered[] = $row;
                }
            }

            return $filtered;
        }

        // no team leader filter → return all rows
        return $rows;
    }


    /* -------------------------------------------------------------
     *   Single agent helper (not strictly needed now,
     *   but you can keep if used elsewhere)
     * ---------------------------------------------------------- */
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
    // Get TEAM LEADER ID for a given agent
    public function get_team_leader_id($agent_id)
    {
        if (!$agent_id) return null;

        $row = $this->db
            ->select('team_leader')
            ->from('tblstaff')
            ->where('staffid', $agent_id)
            ->get()
            ->row();

        return $row ? $row->team_leader : null;
    }

    // Convert team leader id → name
    public function get_team_leader_name($team_leader_id)
    {
        if (!$team_leader_id) return '';

        $row = $this->db
            ->select('CONCAT(firstname, " ", lastname) AS name')
            ->from('tblstaff')
            ->where('staffid', $team_leader_id)
            ->get()
            ->row();

        return $row ? $row->name : '';
    }
}
