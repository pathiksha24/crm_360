<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Leads Admins
Description: Leads Admins
Version: 1.0.0
Requires at least: 2.3.*
Author: Bekka Codes
Author URI: https://www.fiverr.com/ossamaben
*/

define('LEADS_ADMINS_MODULE_NAME', 'leads_admins');
$CI = &get_instance();

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(LEADS_ADMINS_MODULE_NAME, [LEADS_ADMINS_MODULE_NAME]);


register_activation_hook(LEADS_ADMINS_MODULE_NAME, 'leads_admins_module_activation_hook');
function leads_admins_module_activation_hook()
{
    $CI = &get_instance();
    /*$sourceFile = __DIR__.'/views/my_member.php';
    $destinationFile =  __DIR__.'/../../application/views/admin/staff';

    // Copy the file
    if (!copy($sourceFile, $destinationFile)) {
        log_activity("File could not be copied.");
    }*/
    //install required fields to database
    require_once(__DIR__ . '/install.php');
}

/*register_deactivation_hook(LEADS_ADMINS_MODULE_NAME, 'leads_admins_module_deactivation_hook');
function leads_admins_module_deactivation_hook()
{
    $destinationFile =  __DIR__.'/../../application/views/admin/staff';
    if (file_exists($destinationFile)) {
        // Delete the file
        if (!unlink($destinationFile)) {
            log_activity("File could not be deleted.");
        }
    } else {
        log_activity("File does not exist.");
    }
}*/

hooks()->add_filter('staff_admins_input', 'leads_admins_staff_admins_input');
hooks()->add_action('lead_view_data', 'leads_admins_lead_view_data');
hooks()->add_action('leads_manager_staff_data', 'leads_admins_leads_manager_staff_data');
hooks()->add_action('before_update_staff_member', 'leads_admins_before_update_staff_member', 1, 2);
hooks()->add_action('leads_table_assigned_where', 'leads_admins_leads_table_assigned_where');
function leads_admins_leads_table_assigned_where($where)
{
    $ci = &get_instance();
    $current_staff = $ci->staff_model->get(get_staff_user_id());
    $selected = json_decode($current_staff->leads_staff);

    if ($selected == null) $selected = [get_staff_user_id()];

    array_push($where, 'AND assigned IN(' . implode(',', $selected) . ')');
    return $where;
}

function leads_admins_staff_admins_input($member)
{
    $ci = &get_instance();
    $ci->load->model('staff_model');
    $current_staff = $ci->staff_model->get(get_staff_user_id());
    if (is_admin()) {
        $staff = $ci->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);
        $selected = json_decode($member->leads_staff);
        echo render_select('leads_staff[]', $staff, array('staffid', array('firstname', 'lastname')), 'leads_staff', $selected, array('multiple' => true), array(), '', '', false);
    }
}

function leads_admins_before_update_staff_member($data, $id)
{
    $ci = &get_instance();
    $ci->load->model('staff_model');
    $current_staff = $ci->staff_model->get(get_staff_user_id());
    if (is_admin()) {
        $data['leads_staff'] = json_encode($data['leads_staff']);
    }
    return $data;
}

function leads_admins_lead_view_data($data)
{
    $ci = &get_instance();
    $ci->load->model('staff_model');
    $current_staff = $ci->staff_model->get(get_staff_user_id());
    $data['members'] = [];
    $staff = $ci->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);
    $selected = json_decode($current_staff->leads_staff);
    foreach ($selected as $staff) {
        $ci->db->where('staffid', $staff);
        $staff = $ci->db->get(db_prefix() . 'staff')->row_array();
        $data['members'][] = $staff;
    }
    return $data;
}

function leads_admins_leads_manager_staff_data($data)
{
    $ci = &get_instance();
    $ci->load->model('staff_model');
    $current_staff = $ci->staff_model->get(get_staff_user_id());
    $data = [];
    $staff = $ci->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);
    $selected = json_decode($current_staff->leads_staff);
    foreach ($selected as $staff) {
        $ci->db->where('staffid', $staff);
        $staff = $ci->db->get(db_prefix() . 'staff')->row_array();
        $data[] = $staff;
    }
    return $data;
}