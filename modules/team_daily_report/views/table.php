<?php

defined('BASEPATH') or exit('No direct script access allowed');
$this->ci->db->query("SET sql_mode = ''");
$custom_fields = get_custom_fields('customers');
$aColumns = [
    'agent',
    'leads',
    'calls_connected',
    'calls_not_connected',
    'international_number',
    'not_interested',
    'lost',
    'followup',
    'closing_from_walkin',
    'closing_from_leads',
    'closing_from_reference',
];
if (is_admin()){
    $aColumns[] = 'manager_id';
}

$join         = [
];
$sIndexColumn = 'id';
$sTable       = db_prefix().'manager_records';
$where        = [];
$filter       = [];

if ($date !== 0 && $date !== '0'){
    $date = DateTime::createFromFormat('d-m-Y', $date);
    $date = $date->format('Y-m-d');
    array_push($where, 'AND DATE(created_at) = "' . $date . '"');
}

if ($manager_id !== 0 && $manager_id !== '0' && $manager_id !== ''){
    array_push($where, 'AND (manager_id = ' . $manager_id.' OR related_manager = ' . $manager_id . ')');

}
if (!is_admin()){
    array_push($where, 'AND (manager_id = ' . get_staff_user_id().' OR related_manager = ' . get_staff_user_id() . ')');
}
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'id',
    'related_manager'
]);
$output  = $result['output'];
$rResult = $result['rResult'];
$footer_data['total_leads'] = 0;
$footer_data['total_calls_connected'] = 0;
$footer_data['total_calls_not_connected'] = 0;
$footer_data['total_international_number'] = 0;
$footer_data['total_not_interested'] = 0;
$footer_data['total_lost'] = 0;
$footer_data['total_followup'] = 0;
$footer_data['total_closing_from_walkin'] = 0;
$footer_data['total_closing_from_leads'] = 0;
$footer_data['total_closing_from_reference'] = 0;
foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $aRow['agent'];
    $row[] = $aRow['leads'];
    $row[] = $aRow['calls_connected'];
    $row[] = $aRow['calls_not_connected'];
    $row[] = $aRow['international_number'];
    $row[] = $aRow['not_interested'];
    $row[] = $aRow['lost'];
    $row[] = $aRow['followup'];
    $row[] = $aRow['closing_from_walkin'];
    $row[] = $aRow['closing_from_leads'];
    $row[] = $aRow['closing_from_reference'];
    $footer_data['total_leads'] += $aRow['leads'];
    $footer_data['total_calls_connected'] += $aRow['calls_connected'];
    $footer_data['total_calls_not_connected'] += $aRow['calls_not_connected'];
    $footer_data['total_international_number'] += $aRow['international_number'];
    $footer_data['total_not_interested'] += $aRow['not_interested'];
    $footer_data['total_lost'] += $aRow['lost'];
    $footer_data['total_followup'] += $aRow['followup'];
    $footer_data['total_closing_from_walkin'] += $aRow['closing_from_walkin'];
    $footer_data['total_closing_from_leads'] += $aRow['closing_from_leads'];
    $footer_data['total_closing_from_reference'] += $aRow['closing_from_reference'];
    if(is_admin()){
        $manager = get_staff_full_name($aRow['related_manager']);
        $row[] .= '<a href="' . admin_url('profile/' . $aRow['related_manager']) . '">' .
            staff_profile_image($aRow['related_manager'], [
                'staff-profile-image-small mright5',
            ], 'small', [
                'data-toggle' => 'tooltip',
                'data-title'  => $manager,
            ]) . ' '.$manager.'</a>';
    }
    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
    $options .= '<a onclick="delete_record('.$aRow['id'].');return false;"
        class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
        <i class="fa-regular fa-trash-can fa-lg"></i>
    </a>';
    $options .= '<a onclick="edit_record(this);return false;"
        data-id = '.$aRow['id'].'
        data-agent = '.$aRow['agent'].'
        data-leads = '.$aRow['leads'].'
        data-calls_connected = '.$aRow['calls_connected'].'
        data-calls_not_connected = '.$aRow['calls_not_connected'].'
        data-international_number = '.$aRow['international_number'].'
        data-not_interested = '.$aRow['not_interested'].'
        data-lost = '.$aRow['lost'].'
        data-followup = '.$aRow['followup'].'
        data-closing_from_walkin = '.$aRow['closing_from_walkin'].'
        data-closing_from_leads = '.$aRow['closing_from_leads'].'
        data-closing_from_reference = '.$aRow['closing_from_reference'].'
        data-related_manager = '.$aRow['related_manager'].'
        class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
            <i class="fa-regular fa-pen-to-square fa-lg"></i>
    </a>';
    $options .= '</div>';

    $row[] = $options;
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
$output['sums']              = $footer_data;
