<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Partial_contract_model extends App_Model
{
    protected $contracts_db;

    public function __construct()
    {
        parent::__construct();
        $this->contracts_db = $this->load->database('contracts_db', true);
    }

    public function get_agents()
    {
        return $this->db
            ->select('staffid AS agent_id, CONCAT(firstname, " ", lastname) AS agent_name')
            ->from('tblstaff')
            ->where('active', 1)
            ->order_by('firstname', 'ASC')
            ->get()
            ->result();
    }

    public function get_team_leaders()
    {
        return $this->db
            ->select('staffid AS agent_id, CONCAT(firstname, " ", lastname) AS agent_name')
            ->from('tblstaff')
            ->where('is_team_leader', 1)
            ->order_by('firstname', 'ASC')
            ->get()
            ->result();
    }

    public function get_agents_map()
    {
        $rows = $this->get_agents();
        $map = [];
        foreach ($rows as $r) {
            $map[$r->agent_id] = $r->agent_name;
        }
        return $map;
    }

    private function normalize_date($date)
    {
        if (empty($date)) return null;

        $date = str_replace('/', '-', trim($date));
        $ts = strtotime($date);

        return $ts ? date('Y-m-d', $ts) : null;
    }

    public function get_filtered_contracts($filters)
    {
        $db = $this->contracts_db;

        $db->from('tblpartial_contract_applications');
        $db->where('deleted_status', 0);

        if (!empty($filters['agent_id'])) {
            $db->where('agent_id', $filters['agent_id']);
        }

        $period = $filters['period'] ?? 'all';

        switch ($period) {
            case 'today':
                $db->where('DATE(created_date)', date('Y-m-d'));
                break;

            case 'this_week':
                $db->where('YEARWEEK(created_date) =', date('oW'));
                break;

            case 'this_month':
                $db->where('MONTH(created_date)', date('m'));
                $db->where('YEAR(created_date)', date('Y'));
                break;

            case 'last_month':
                $db->where('MONTH(created_date)', date('m') - 1);
                $db->where('YEAR(created_date)', date('Y'));
                break;

            case 'this_year':
                $db->where('YEAR(created_date)', date('Y'));
                break;

            case 'period':
                $from = $this->normalize_date($filters['date_from']);
                $to   = $this->normalize_date($filters['date_to']);

                if ($from) $db->where('DATE(created_date) >=', $from);
                if ($to)   $db->where('DATE(created_date) <=', $to);
                break;
        }

        $db->order_by('created_date', 'DESC');
        $rows = $db->get()->result();

        if (!empty($filters['team_leader_id'])) {

            $filtered = [];
            foreach ($rows as $r) {
                $tl = $this->get_team_leader_id($r->agent_id);
                if ($tl == $filters['team_leader_id']) {
                    $filtered[] = $r;
                }
            }
            return $filtered;
        }

        return $rows;
    }

    public function get_team_leader_id($agent_id)
    {
        $row = $this->db
            ->select('team_leader')
            ->from('tblstaff')
            ->where('staffid', $agent_id)
            ->get()
            ->row();

        return $row ? $row->team_leader : null;
    }

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
