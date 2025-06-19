<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'description',
    'date',
    'staffid',
    ];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'staff_logs';

$join = [
];

$where  = [];
if ($this->ci->input->post('staff_logs_date')) {
    array_push($where, 'AND date LIKE "' . $this->ci->db->escape_like_str(to_sql_date($this->ci->input->post('staff_logs_date'))) . '%" ESCAPE \'!\'');
}
$filter = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'id'
]);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    
    $row = [];

    $row[] = $aRow['description'];

    $row[]   = _dt($aRow['date']);
    $row[]   = '<a href="'.admin_url('staff/member/'.$aRow['staffid']).'" target="_blank" >' . get_staff_full_name($aRow['staffid']) . '</a>';

    $row['DT_RowClass'] = 'has-row-options';

    $row = hooks()->apply_filters('staff_logs_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}
