<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$contracts_db = $CI->load->database('contracts_db', true);
$CI->db = $contracts_db; 

$CI->load->model('partial_contract_model');

$agent_id     = $CI->input->post('agent_id');
$service_type = $CI->input->post('service_type');
$period       = $CI->input->post('period');
$date_from    = $CI->input->post('date_from');
$date_to      = $CI->input->post('date_to');

$aColumns = [
    'id',
    'agent_id',
    'full_name',
    'service_type',
    'total_amount',
    'created_at',
];

$sIndexColumn = 'id';
$sTable       = 'tblpartial_contract_applications';

$join  = [];
$where = [];

if ($agent_id !== '') {
    $where[] = 'AND agent_id = ' . $CI->db->escape($agent_id);
}

if ($service_type !== '') {
    $where[] = 'AND service_type = ' . $CI->db->escape($service_type);
}

$CI->contract_webapp_model->apply_period_where($period, $date_from, $date_to, $where);

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where);
$output  = $result['output'];
$rResult = $result['rResult'];

$serviceName = isset($serviceMap[$aRow['service_type']])
    ? $serviceMap[$aRow['service_type']]
    : $aRow['service_type'];



foreach ($rResult as $aRow) {
    $row   = [];
    $row[] = $aRow['id'];              
    $row[] = $aRow['agent_id'];         
    $row[] = $aRow['full_name'];       
    $row[] = $serviceName; 
    $row[] = $aRow['total_amount'];     
    $row[] = _d($aRow['created_at']);  

    $output['aaData'][] = $row;
}

echo json_encode($output);
