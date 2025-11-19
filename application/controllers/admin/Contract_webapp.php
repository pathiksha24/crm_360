<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Contract_webapp extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Contract_webapp_model');
    }

    public function index()
    {
        $data['title']    = 'Contract Webapp Dashboard';

        // Load dropdown data
        $data['agents']   = $this->Contract_webapp_model->get_agents();
        $data['services'] = $this->Contract_webapp_model->get_services();

        // Load the view
        $this->load->view('admin/contract_webapp/dashboard', $data);
    }

    // DataTable endpoint
public function table()
{
    $filters = [
        'agent_id'     => $this->input->post('agent_id'),
        'service_type' => $this->input->post('service_type'),
        'period'       => $this->input->post('period'),
        'date_from'    => $this->input->post('date_from'),
        'date_to'      => $this->input->post('date_to'),
    ];

    $rows = $this->Contract_webapp_model->get_filtered_contracts($filters);

    // 🔹 Get [service_id => name] once
    $serviceMap = $this->Contract_webapp_model->get_services_map();

    $output = [
        "draw"            => intval($this->input->post("draw")),
        "recordsTotal"    => count($rows),
        "recordsFiltered" => count($rows),
        "aaData"          => []
    ];

    foreach ($rows as $r) {

        // 🔹 Convert ID → name
        $serviceName = isset($serviceMap[$r->service_type])
            ? $serviceMap[$r->service_type]
            : $r->service_type; // fallback

        $agentName = $this->Contract_webapp_model->get_agent_name($r->agent_id);

        $output['aaData'][] = [
            $r->id,
            $agentName,          // Agent name
            $r->full_name,       // Customer
            $serviceName,        // ✅ Service name
            $r->total_amount,
            _d($r->created_at),
        ];
    }

    echo json_encode($output);
}

}
