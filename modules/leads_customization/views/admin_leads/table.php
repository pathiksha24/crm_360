<?php

defined('BASEPATH') or exit('No direct script access allowed');

$this->ci->load->model('gdpr_model');
$lockAfterConvert      = get_option('lead_lock_after_convert_to_customer');
$has_permission_delete = has_permission('leads', '', 'delete');
$custom_fields         = get_table_custom_fields('leads');
$consentLeads          = get_option('gdpr_enable_consent_for_leads');
$statuses              = $this->ci->leads_model->get_status();

$aColumns = [
    '1',
    db_prefix() . 'leads.id as id',
    db_prefix() . 'leads.name as name',
    ];
if (is_gdpr() && $consentLeads == '1') {
    $aColumns[] = '1';
}
$aColumns = array_merge($aColumns, ['company',
    db_prefix() . 'leads.email as email',
    db_prefix() . 'leads.phonenumber as phonenumber',
    'lead_value',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'leads.id and rel_type="lead" ORDER by tag_order ASC LIMIT 1) as tags',
    'firstname as assigned_firstname',
    db_prefix() . 'leads_status.name as status_name',
    db_prefix() . 'leads_sources.name as source_name',
    db_prefix() . 'leads_services.name as service_name',
    db_prefix() . 'leads_languages.name as language_name',
    'whatsapp_number',
    'dateassigned',
    'lastcontact',
    'changeddate',
    'dateadded',
// '(SELECT GROUP_CONCAT(description ORDER BY dateadded SEPARATOR ", ")
//   FROM ' . db_prefix() . 'notes 
//   WHERE rel_type = "lead" AND rel_id = ' . db_prefix() . 'leads.id) AS lead_notes',


]);

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'leads';

$join = [
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'leads.assigned',
    'LEFT JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads_status.id = ' . db_prefix() . 'leads.status',
    'JOIN ' . db_prefix() . 'leads_sources ON ' . db_prefix() . 'leads_sources.id = ' . db_prefix() . 'leads.source',
    'JOIN ' . db_prefix() . 'leads_services ON ' . db_prefix() . 'leads_services.id = ' . db_prefix() . 'leads.service',
    'JOIN ' . db_prefix() . 'leads_languages ON ' . db_prefix() . 'leads_languages.id = ' . db_prefix() . 'leads.language',
];

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'leads.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$where  = [];
$filter = false;
$months_report      = $this->ci->input->post('report_months');
$custom_date_select = '';
$field = 'tblleads.dateassigned';
if ($months_report!=''){$field = 'DATE('.$field.')';
    $custom_date_select = '';
    if ($months_report != '') {
        if ($months_report == 'this_week') {
            $custom_date_select = 'AND (' . $field . ' BETWEEN "' . date('Y-m-d', strtotime('monday this week')) . '" AND "' . date('Y-m-d', strtotime('sunday this week')) . '")';
        } elseif ($months_report == 'today') {
            $custom_date_select = 'AND (' . $field . ' = "' . date('Y-m-d') . '")';
        } elseif ($months_report == 'last_week') {
            $custom_date_select = 'AND (' . $field . ' BETWEEN "' . date('Y-m-d', strtotime('monday last week')) . '" AND "' . date('Y-m-d', strtotime('sunday last week')) . '")';
        } elseif ($months_report == 'this_month') {
            $custom_date_select = 'AND (' . $field . ' BETWEEN "' . date('Y-m-01') . '" AND "' . date('Y-m-t') . '")';
        } elseif ($months_report == 'last_month') {
            $beginLastMonth = to_sql_date(date('Y-m-01', strtotime('-1 MONTH')) );
            $endLastMonth   = to_sql_date(date('Y-m-t', strtotime('-1 MONTH')));
            $custom_date_select = '  ' . $field . ' BETWEEN "' . ($beginLastMonth) . '" AND "' . ($endLastMonth).'"';
        } elseif ($months_report == 'this_year') {
            $custom_date_select = 'AND (' . $field . ' BETWEEN "' .
                date('Y-m-d', strtotime(date('Y-01-01'))) .
                '" AND "' .
                date('Y-m-d', strtotime(date('Y-12-31'))) . '")';
        } elseif ($months_report == 'last_year') {
            $custom_date_select = 'AND (' . $field . ' BETWEEN "' .
                date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01'))) .
                '" AND "' .
                date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))) . '")';
        } elseif ($months_report == 'custom') {
            $from_date = to_sql_date($this->ci->input->post('report_from'));
            $to_date   = to_sql_date($this->ci->input->post('report_to'));
            if ($from_date == $to_date) {
                $custom_date_select = 'AND ' . $field . ' = "' . $from_date . '"';
            } else {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' . $from_date . '" AND "' . $to_date . '")';
            }
        }
    }
}
if (isset($custom_date_select) and $custom_date_select != '') {
    array_push($where, $custom_date_select);
}

if ($this->ci->input->post('custom_view')) {
    $filter = $this->ci->input->post('custom_view');
    if ($filter == 'lost') {
        array_push($where, 'AND lost = 1');
    } elseif ($filter == 'junk') {
        array_push($where, 'AND junk = 1');
    } elseif ($filter == 'not_assigned') {
        array_push($where, 'AND assigned = 0');
    } elseif ($filter == 'contacted_today') {
        array_push($where, 'AND lastcontact LIKE "' . date('Y-m-d') . '%"');
    } elseif ($filter == 'created_today') {
        array_push($where, 'AND dateadded LIKE "' . date('Y-m-d') . '%"');
    } elseif ($filter == 'public') {
        array_push($where, 'AND is_public = 1');
    } elseif (startsWith($filter, 'consent_')) {
        array_push($where, 'AND ' . db_prefix() . 'leads.id IN (SELECT lead_id FROM ' . db_prefix() . 'consents WHERE purpose_id=' . $this->ci->db->escape_str(strafter($filter, 'consent_')) . ' and action="opt-in" AND date IN (SELECT MAX(date) FROM ' . db_prefix() . 'consents WHERE purpose_id=' . $this->ci->db->escape_str(strafter($filter, 'consent_')) . ' AND lead_id=' . db_prefix() . 'leads.id))');
    }
}

if (!$filter || ($filter && $filter != 'lost' && $filter != 'junk')) {
    array_push($where, 'AND lost = 0 AND junk = 0');
}

if (/*has_permission('leads', '', 'view') && */$this->ci->input->post('assigned')) {
    array_push($where, 'AND assigned =' . $this->ci->db->escape_str($this->ci->input->post('assigned')));
}else if(!is_admin()){
    $where = hooks()->apply_filters('leads_table_assigned_where', $where);
}

if ($this->ci->input->post('status')
    && count($this->ci->input->post('status')) > 0
    && ($filter != 'lost' && $filter != 'junk')) {
    array_push($where, 'AND ' . db_prefix() . 'leads.status IN (' . implode(',', $this->ci->db->escape_str($this->ci->input->post('status'))) . ')');
}

if ($this->ci->input->post('source')) {
    array_push($where, 'AND source =' . $this->ci->db->escape_str($this->ci->input->post('source')));
}
if ($this->ci->input->post('service')) {
    array_push($where, 'AND service =' . $this->ci->db->escape_str($this->ci->input->post('service')));
}
if ($this->ci->input->post('language')) {
    array_push($where, 'AND language =' . $this->ci->db->escape_str($this->ci->input->post('language')));
}

if ($this->ci->input->post('has_notes')) {
    $has_notes = $this->ci->input->post('has_notes');

    if ($has_notes == '1') { // Leads with notes
        array_push($where, 'AND ' . db_prefix() . 'leads.id IN (SELECT rel_id FROM ' . db_prefix() . 'notes WHERE rel_type = "lead")');
    } elseif ($has_notes == '2') { // Leads without notes
        array_push($where, 'AND ' . db_prefix() . 'leads.id NOT IN (SELECT rel_id FROM ' . db_prefix() . 'notes WHERE rel_type = "lead")');
    }
}

//if (!has_permission('leads', '', 'view')) {
//    array_push($where, 'AND (assigned =' . get_staff_user_id() . ' OR addedfrom = ' . get_staff_user_id() . ' OR is_public = 1)');
//}

$aColumns = hooks()->apply_filters('leads_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit

if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$additionalColumns = hooks()->apply_filters('leads_table_additional_columns_sql', [
    'junk',
    'lost',
    'color',
    db_prefix().'leads.status',
    'assigned',
    'lastname as assigned_lastname',
    db_prefix() . 'leads.addedfrom as addedfrom',
    '(SELECT count(leadid) FROM ' . db_prefix() . 'clients WHERE ' . db_prefix() . 'clients.leadid=' . db_prefix() . 'leads.id) as is_converted',
    'zip',
]);




//anju , gopi, marketing team, juli assigned lead hiiden for all except cc staffs, pololmy , call center 1 , call center 5 , anju
if (!has_access_to_unassigned_leads()) {
    $where[] = 'AND ' . db_prefix() . 'leads.assigned <> 0';
}

// 2) Anju’s leads visibility (assigned = 17); only whitelisted staff can see
$allowed_for_anju = [17, 58, 14, 59, 55, 216, 214, 72, 20, 34, 163, 54, 56]; // keep 77 OUT if Quality should not see
if (!in_array((int) get_staff_user_id(), $allowed_for_anju, true)) {
     $where[] = 'AND ' . db_prefix() . 'leads.assigned NOT IN (17,210,174,228)';
}

/* ---------- Country code starts-with override for whatsapp_number & phonenumber ---------- */
$globalSearch = $this->ci->input->post('search');
$globalTerm   = isset($globalSearch['value']) ? trim($globalSearch['value']) : '';

if ($globalTerm !== '' && preg_match('/^\+?\d{2,3}$/', $globalTerm)) {
    // Normalize +91 -> 91
    $cc  = ltrim($globalTerm, '+');
    $esc = $this->ci->db->escape_like_str($cc);

    // Match numbers that START with the country code (with or without '+')
    $clauses = [];
    $clauses[] = db_prefix() . 'leads.phonenumber LIKE "' . $esc . '%"';
    $clauses[] = db_prefix() . 'leads.phonenumber LIKE "+' . $esc . '%"';
    $clauses[] = 'whatsapp_number LIKE "' . $esc . '%"';
    $clauses[] = 'whatsapp_number LIKE "+' . $esc . '%"';

    $where[] = 'AND (' . implode(' OR ', $clauses) . ')';

    // Disable default global fuzzy search so 'contains' matches (e.g., 971) don't leak in
    $_POST['search']['value'] = '';
}
/* ---------- /override ---------- */

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalColumns);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    if ($aRow['assigned'] == 0){
        if (!has_access_to_unassigned_leads()) continue;
    }


     // call center staffs, poluolny, call center 1  and anju can see the leads which is assigned to anju
    //  if ($aRow['assigned'] == 17 && !in_array(get_staff_user_id(), [17, 58, 14, 59, 55, 216, 214, 72, 20, 34, 163, 54, 56])) {
    //     continue;
    // }


    $row = [];

    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['id'] . '"><label></label></div>';

    $hrefAttr = 'href="' . admin_url('leads/index/' . $aRow['id']) . '" onclick="init_lead(' . $aRow['id'] . ');return false;"';
    $row[]    = '<a ' . $hrefAttr . '>' . $aRow['id'] . '</a>';

    $nameRow = '<a ' . $hrefAttr . '>' . $aRow['name'] . '</a>';

    $nameRow .= '<div class="row-options">';
    $nameRow .= '<a ' . $hrefAttr . '>' . _l('view') . '</a>';

    $locked = false;

    if ($aRow['is_converted'] > 0) {
        $locked = ((!is_admin() && $lockAfterConvert == 1) ? true : false);
    }

    if (!$locked) {
        $nameRow .= ' | <a href="' . admin_url('leads/index/' . $aRow['id'] . '?edit=true') . '" onclick="init_lead(' . $aRow['id'] . ', true);return false;">' . _l('edit') . '</a>';
    }

    if ($aRow['addedfrom'] == get_staff_user_id() || $has_permission_delete) {
        $nameRow .= ' | <a href="' . admin_url('leads/delete/' . $aRow['id']) . '" class="_delete text-danger">' . _l('delete') . '</a>';
    }
    //delete access from polulomy and call cener 1 remove 
        //     $blocked_delete_staff = [54, 56]; // example
        // if (
        //     ($aRow['addedfrom'] == get_staff_user_id() || $has_permission_delete) && !in_array((int) get_staff_user_id(), $blocked_delete_staff, true)
        // ) {
        //     $nameRow .= ' | <a href="' . admin_url('leads/delete/' . $aRow['id']) . '" class="_delete text-danger">' . _l('delete') . '</a>';
        // }

    $nameRow .= '</div>';


    $row[] = $nameRow;

    if (is_gdpr() && $consentLeads == '1') {
        $consentHTML = '<p class="bold"><a href="#" onclick="view_lead_consent(' . $aRow['id'] . '); return false;">' . _l('view_consent') . '</a></p>';
        $consents    = $this->ci->gdpr_model->get_consent_purposes($aRow['id'], 'lead');

        foreach ($consents as $consent) {
            $consentHTML .= '<p style="margin-bottom:0px;">' . $consent['name'] . (!empty($consent['consent_given']) ? '<i class="fa fa-check text-success pull-right"></i>' : '<i class="fa fa-remove text-danger pull-right"></i>') . '</p>';
        }
        $row[] = $consentHTML;
    }
    $row[] = $aRow['company'];

    $row[] = ($aRow['email'] != '' ? '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>' : '');

    $row[] = ($aRow['phonenumber'] != '' ? '<a href="tel:' . $aRow['phonenumber'] . '">' . $aRow['phonenumber'] . '</a>' : '');

    $base_currency = get_base_currency();
    $row[]         = ($aRow['lead_value'] != 0 ? app_format_money($aRow['lead_value'], $base_currency->id) : '');

    $row[] .= render_tags($aRow['tags']);

    $assignedOutput = '';
    if ($aRow['assigned'] != 0) {
        $full_name = $aRow['assigned_firstname'] . ' ' . $aRow['assigned_lastname'];

        $assignedOutput = '<a data-toggle="tooltip" data-title="' . $full_name . '" href="' . admin_url('profile/' . $aRow['assigned']) . '">' . staff_profile_image($aRow['assigned'], [
            'staff-profile-image-small',
            ]) . '</a>';

        // For exporting
        $assignedOutput .= '<span class="hide">' . $full_name . '</span>';
    }

    $row[] = $assignedOutput;

    if ($aRow['status_name'] == null) {
        if ($aRow['lost'] == 1) {
            $outputStatus = '<span class="label label-danger">' . _l('lead_lost') . '</span>';
        } elseif ($aRow['junk'] == 1) {
            $outputStatus = '<span class="label label-warning">' . _l('lead_junk') . '</span>';
        }
    } else {
        $outputStatus = '<span class="lead-status-' . $aRow['status'] . ' label' . (empty($aRow['color']) ? ' label-default': '') . '" style="color:' . $aRow['color'] . ';border:1px solid ' . adjust_hex_brightness($aRow['color'], 0.4) . ';background: ' . adjust_hex_brightness($aRow['color'], 0.04) . ';">' . $aRow['status_name'];

        if (!$locked) {
            $outputStatus .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
            $outputStatus .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsStatus-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
            $outputStatus .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa-solid fa-chevron-down tw-opacity-70"></i></span>';
            $outputStatus .= '</a>';

            $outputStatus .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' . $aRow['id'] . '">';
            foreach ($statuses as $leadChangeStatus) {
                if ($aRow['status'] != $leadChangeStatus['id']) {
                    $outputStatus .= '<li>
                  <a href="#" onclick="lead_mark_as(' . $leadChangeStatus['id'] . ',' . $aRow['id'] . '); return false;">
                     ' . $leadChangeStatus['name'] . '
                  </a>
               </li>';
                }
            }
            $outputStatus .= '</ul>';
            $outputStatus .= '</div>';
        }
        $outputStatus .= '</span>';
    }

    $row[] = $outputStatus;

    $row[] = $aRow['source_name'];
    $row[] = $aRow['service_name'];
    $row[] = $aRow['language_name'];
    $row[] = $aRow['whatsapp_number'];

    $row[] = ($aRow['dateassigned'] == '0000-00-00 00:00:00' || !is_date($aRow['dateassigned']) ? '' : '<span data-toggle="tooltip" data-title="' . _dt($aRow['dateassigned']) . '" class="text-has-action is-date">' . time_ago($aRow['dateassigned']) . '</span>');
    $row[] = ($aRow['lastcontact'] == '0000-00-00 00:00:00' || !is_date($aRow['lastcontact']) ? '' : '<span data-toggle="tooltip" data-title="' . _dt($aRow['lastcontact']) . '" class="text-has-action is-date">' . time_ago($aRow['lastcontact']) . '</span>');
    $row[] = ($aRow['changeddate'] == '0000-00-00 00:00:00' || !is_date($aRow['changeddate']) ? '' : '<span data-toggle="tooltip" data-title="' . _dt($aRow['changeddate']) . '" class="text-has-action is-date">' . time_ago($aRow['changeddate']) . '</span>');

    $row[] = '<span data-toggle="tooltip" data-title="' . _dt($aRow['dateadded']) . '" class="text-has-action is-date">' . time_ago($aRow['dateadded']) . '</span>';

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }
    // $row[] = nl2br($aRow['lead_notes'] ?? '');



    $row['DT_RowId'] = 'lead_' . $aRow['id'];

    if ($aRow['assigned'] == get_staff_user_id()) {
        $row['DT_RowClass'] = 'info';
    }

    if (isset($row['DT_RowClass'])) {
        $row['DT_RowClass'] .= ' has-row-options';
    } else {
        $row['DT_RowClass'] = 'has-row-options';
    }

    $row = hooks()->apply_filters('leads_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}