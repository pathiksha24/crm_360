<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Managers Reports
Description: Managers Reports
Version: 1.0.0
Requires at least: 2.3.*
Author: Bekka Codes
Author URI: https://www.fiverr.com/ossamaben
*/

define('MANAGER_REPORT_MODULE_NAME', 'manager_report');

$ci = & get_instance();
$ci->load->helper(MANAGER_REPORT_MODULE_NAME.'/manager_report');

/**
 * Register activation module hook
 */
register_activation_hook(MANAGER_REPORT_MODULE_NAME, 'manager_report_activation_hook');
function manager_report_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

register_language_files(MANAGER_REPORT_MODULE_NAME, [MANAGER_REPORT_MODULE_NAME]);

hooks()->add_action('admin_init', 'manager_report_init_menu_items');
hooks()->add_action('admin_init', 'manager_report_init_permissions');
hooks()->add_action('staff_render_managers', 'staff_render_managers');
hooks()->add_action('lead_status_changed', 'manager_report_lead_status_changed');

function manager_report_init_menu_items() {
    $CI = &get_instance();
    if (has_permission('manager_report','','manager_report')) {
        $CI->app_menu->add_sidebar_menu_item('manager_report', [
            'name' => _l('manager_report'),
            'icon' => 'fa fa-table',
            'href' => admin_url('manager_report'),
            'position' => 40,
        ]);
    }
}

function manager_report_init_permissions()
{
    $capabilities['capabilities'] = [
        'manager_report'   => _l('manager_report'),
    ];
    register_staff_capabilities('manager_report', $capabilities, _l('manager_report'));
}


function staff_render_managers($member)
{
    $selected = '';
    if ($member) $selected = $member->manager_id;
    $ci = &get_instance();
    $users = $ci->staff_model->get();
    $managers = [];
    foreach ($users as $manager) {
        if (has_permission('manager_report',$manager['staffid'],'manager_report') && ($manager['staffid'] != $member->staffid)) {
            $managers[] = $manager;
        }
    }
    echo render_select('manager_id', $managers, ['staffid', ['firstname', 'lastname']], 'Manager', $selected, [], [], '', '', true);

}

function manager_report_lead_status_changed($data)
{
    $old_status = $data['old_status'];
    $lead_id = $data['lead_id'];
    if (strtolower($old_status) == 'new' || $old_status == 2){
        $ci = &get_instance();
        $lead = $ci->leads_model->get($lead_id);
        if ($lead){
            $ci->db->where('id', $lead_id);
            $update['changeddate'] = date('Y-m-d H:i:s');
            $ci->db->update(db_prefix().'leads',$update);
        }
    }
}