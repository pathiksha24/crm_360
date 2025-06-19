<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Staff logs
Description: To manage Staff logs
Version: 1.0.0
*/

define('STAFF_LOGS', 'staff_logs');


hooks()->add_action('admin_init', 'add_staff_logs_menu_items');

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(STAFF_LOGS, [STAFF_LOGS]);


/**
 * Initiate a new instance
 */
$CI = &get_instance();

/**
 * Loads the module function helper
 */
$CI->load->helper(STAFF_LOGS . '/staff_logs');

/**
 * Activate the work_orders module
 */
register_activation_hook(STAFF_LOGS, 'staff_logs_activation_hook');
function staff_logs_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

function add_staff_logs_menu_items()
{
    if (is_admin()) {
        $CI = &get_instance();
        $CI->app_menu->add_sidebar_menu_item('staff_logs', [
            'name'     => _l('staff_logs'),
            'href'     => admin_url('staff_logs'),
            'icon'     => 'fa fa-history',
            'position' => 5,
            'badge'    => [],
        ]);
    }
}

hooks()->add_action('app_admin_footer', 'staff_logs_load_admin_js');
function staff_logs_load_admin_js()
{
    $CI = &get_instance();
    echo '<script src="' . module_dir_url('staff_logs', 'assets/js/staff_logs.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';

    echo '<link rel="stylesheet" href="' . module_dir_url('staff_logs', 'assets/css/staff_logs.css') . '?v=' . $CI->app_scripts->core_version() . '">';
}
