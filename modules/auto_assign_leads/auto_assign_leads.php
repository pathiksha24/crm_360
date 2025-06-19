<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Auto Assign Leads
Description: Managers Reports
Version: 1.0.0
Requires at least: 2.3.*
Author: Bekka Codes
Author URI: https://www.fiverr.com/ossamaben
*/

define('AUTO_ASSIGN_LEADS_MODULE_NAME', 'auto_assign_leads');

$ci = & get_instance();
$ci->load->helper(AUTO_ASSIGN_LEADS_MODULE_NAME.'/auto_assign_leads');

/**
 * Register activation module hook
 */
register_activation_hook(AUTO_ASSIGN_LEADS_MODULE_NAME, 'auto_assign_leads_activation_hook');
function auto_assign_leads_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

register_language_files(AUTO_ASSIGN_LEADS_MODULE_NAME, [AUTO_ASSIGN_LEADS_MODULE_NAME]);

hooks()->add_action('admin_init', 'auto_assign_leads_init_menu_items');
hooks()->add_action('admin_init', 'auto_assign_leads_init_permissions');

function auto_assign_leads_init_menu_items() {
    $CI = &get_instance();
    if (has_permission('auto_assign_leads','','auto_assign_leads')) {
        $CI->app_menu->add_sidebar_menu_item('auto_assign_leads', [
            'name' => _l('auto_assign_leads'),
            'icon' => 'fa fa-users',
            'href' => admin_url('auto_assign_leads'),
            'position' => 40,
        ]);
    }
}

function auto_assign_leads_init_permissions()
{
    $capabilities['capabilities'] = [
        'auto_assign_leads'   => _l('auto_assign_leads'),
    ];
    register_staff_capabilities('auto_assign_leads', $capabilities, _l('auto_assign_leads'));
}

