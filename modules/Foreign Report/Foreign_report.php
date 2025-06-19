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

define('Foreign Report_MODULE_NAME', 'Foreign Report');

$ci = & get_instance();
$ci->load->helper(Foreign Report_MODULE_NAME.'/Foreign Report');

/**
 * Register activation module hook
 */
register_activation_hook(Foreign Report_MODULE_NAME, 'Foreign Report_activation_hook');
function Foreign Report_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

register_language_files(Foreign Report_MODULE_NAME, [Foreign Report_MODULE_NAME]);

hooks()->add_action('admin_init', 'Foreign Report_init_menu_items');
hooks()->add_action('admin_init', 'Foreign Report_init_permissions');

function Foreign Report_init_menu_items() {
    $CI = &get_instance();
    if (has_permission('Foreign Report','','Foreign Report')) {
        $CI->app_menu->add_sidebar_menu_item('Foreign Report', [
            'name' => _l('Foreign Report'),
            'icon' => 'fa fa-table',
            'href' => admin_url('Foreign Report'),
            'position' => 40,
        ]);
    }
}

function Foreign Report_init_permissions()
{
    $capabilities['capabilities'] = [
        'Foreign Report'   => _l('Foreign Report'),
    ];
    register_staff_capabilities('Foreign Report', $capabilities, _l('Foreign Report'));
}
