<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contract_webapp extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('contract_webapp_model');
    }

    public function index()
    {
        $data['title']    = 'Contract Webapp Dashboard';

        $data['agents']   = $this->contract_webapp_model->get_agents();
        $data['services'] = $this->contract_webapp_model->get_services();

        $this->load->view('admin/contract_webapp/dashboard', $data);
    }

    // DataTable JSON endpoint
    public function table()
    {
        $filters = [
            'agent_id'     => $this->input->post('agent_id'),
            'service_type' => $this->input->post('service_type'),
            'period'       => $this->input->post('period'),
            'date_from'    => $this->input->post('date_from'),
            'date_to'      => $this->input->post('date_to'),
        ];

        $rows       = $this->contract_webapp_model->get_filtered_contracts($filters);
        $serviceMap = $this->contract_webapp_model->get_services_map();
        $agentsMap  = $this->contract_webapp_model->get_agents_map();

        $output = [
            'draw'            => (int) $this->input->post('draw'),
            'recordsTotal'    => count($rows),
            'recordsFiltered' => count($rows),
            'aaData'          => [],
        ];

       foreach ($rows as $r) {

    // Agent Name Fix: Blank if zero or not found
    if (!empty($r->agent_id) && isset($agentsMap[$r->agent_id])) {
        $agentName = $agentsMap[$r->agent_id];
    } else {
        $agentName = '';
    }

    // Service Name
    $serviceName = isset($serviceMap[$r->service_type])
        ? $serviceMap[$r->service_type]
        : '';
     $date_only = date('d-m-Y', strtotime($r->created_at)); 
    $output['aaData'][] = [
        $r->id,
        $agentName,
        $r->full_name,
        $serviceName,
        $r->total_amount,
        $date_only,   
    ];
}


        echo json_encode($output);
        die;
    }
}
