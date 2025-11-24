<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Partial_contract extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('partial_contract_model');
    }

    public function index()
    {
        $data['title']        = 'Partial Contract Dashboard';
        $data['agents']       = $this->partial_contract_model->get_agents();
        $data['team_leaders'] = $this->partial_contract_model->get_team_leaders();

        $this->load->view('admin/partial_contract/dashboard', $data);
    }

    public function table()
    {
        $filters = [
            'team_leader_id' => $this->input->post('team_leader_id'),
            'agent_id'       => $this->input->post('agent_id'),
            'period'         => $this->input->post('period'),
            'date_from'      => $this->input->post('date_from'),
            'date_to'        => $this->input->post('date_to'),
        ];

        $rows = $this->partial_contract_model->get_filtered_contracts($filters);
        $agentsMap = $this->partial_contract_model->get_agents_map();

        $output = [
            'draw'            => (int) $this->input->post('draw'),
            'recordsTotal'    => count($rows),
            'recordsFiltered' => count($rows),
            'aaData'          => [],
        ];

        foreach ($rows as $r) {

            $team_leader_id = $this->partial_contract_model->get_team_leader_id($r->agent_id);
            $teamLeaderName = $this->partial_contract_model->get_team_leader_name($team_leader_id);

            $agentName = isset($agentsMap[$r->agent_id]) ? $agentsMap[$r->agent_id] : '';

            $date_only = date('d-m-Y', strtotime($r->created_date));

            $output['aaData'][] = [
                $r->id,
                $teamLeaderName,
                $agentName,
                $r->full_name,
                $r->id_number,
                $r->total_payment,
                $date_only,
            ];
        }

        echo json_encode($output);
        die;
    }
}
