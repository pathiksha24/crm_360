<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Lead Reminder Pop-Ups
Description: To manage Story
Version: 1.0.0
*/

define('LEADREMINDER_MODULE_NAME', 'leadreminder');

hooks()->add_action('admin_init', 'get_lead_reminders');


/**
 * Initiate a new instance
 */
$CI = &get_instance();

/**
 * Loads the module function helper
 */
$CI->load->helper(LEADREMINDER_MODULE_NAME . '/leadreminder');

/**
 * Activate the leadreminder_active module
 */
register_activation_hook(LEADREMINDER_MODULE_NAME, 'leadreminder_activation_hook');
function leadreminder_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    $alreadyInstalled = get_option('leadreminder_active');
    if ($alreadyInstalled != "" && $alreadyInstalled != null) {
        update_option('leadreminder_active', 1);
    } else {
        add_option('leadreminder_active', 1);
    }
}

/**
 * Deactivation module hook
 *
 *
 */
register_deactivation_hook(LEADREMINDER_MODULE_NAME, 'leadreminder_deactivation_hook');
function leadreminder_deactivation_hook()
{
    update_option('leadreminder_active', 0, 1);
}
/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(LEADREMINDER_MODULE_NAME, [LEADREMINDER_MODULE_NAME]);

function get_lead_reminders()
{
    $CI = &get_instance();
    $CI->load->model('leadreminder/leadreminder_model');

    // $leadreminders = $CI->leadreminder_model->get_leadreminders();
    // $CI->load->view('leadreminder/leadreminder', $leadreminders);
}
hooks()->add_action('app_admin_footer', 'leadreminder_load_customers_js');

function leadreminder_load_customers_js()
{
    $CI = &get_instance();

    if (get_option('leadreminder_active') == '1') {
        echo '<script src="' . module_dir_url('leadreminder', 'assets/js/leadreminder.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
    }
}
