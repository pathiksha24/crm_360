<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// Use second DB for this table
$contracts_db = $CI->load->database('contracts_db', true);
$CI->db = $contracts_db; // use contracts DB for this request

$CI->load->model('contract_webapp_model');

// Filters from AJAX
$agent_id     = $CI->input->post('agent_id');
$service_type = $CI->input->post('service_type');
$period       = $CI->input->post('period');
$date_from    = $CI->input->post('date_from');
$date_to      = $CI->input->post('date_to');

/**
 * Columns in tblcontract_applications we want to show:
 * id, agent_id, full_name, service_type, total_amount, created_at
 */
$aColumns = [
    'id',
    'agent_id',
    'full_name',
    'service_type',
    'total_amount',
    'created_at',
];

$sIndexColumn = 'id';
$sTable       = 'tblcontract_applications';

$join  = [];
$where = [];

// Filter: Agent
if ($agent_id !== '') {
    $where[] = 'AND agent_id = ' . $CI->db->escape($agent_id);
}

// Filter: Service
if ($service_type !== '') {
    $where[] = 'AND service_type = ' . $CI->db->escape($service_type);
}

// Filter: Period / Dates
$CI->contract_webapp_model->apply_period_where($period, $date_from, $date_to, $where);

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where);
$output  = $result['output'];
$rResult = $result['rResult'];

$serviceName = isset($serviceMap[$aRow['service_type']])
    ? $serviceMap[$aRow['service_type']]
    : $aRow['service_type']; // fallback to ID if somehow missing



foreach ($rResult as $aRow) {
    $row   = [];
    $row[] = $aRow['id'];               // ID
    $row[] = $aRow['agent_id'];         // Agent
    $row[] = $aRow['full_name'];        // Customer
    $row[] = $serviceName; // Service (numeric code for now)
    $row[] = $aRow['total_amount'];     // Total Amount
    $row[] = _d($aRow['created_at']);  // Date

    $output['aaData'][] = $row;
}

echo json_encode($output);
