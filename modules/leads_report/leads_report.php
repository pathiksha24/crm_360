<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Leads Report
Description: Leads Report
Version: 1.0.0
Requires at least: 2.3.*
Author: Bekka Codes
Author URI: https://www.fiverr.com/ossamaben
*/

define('LEADS_REPORT_MODULE_NAME', 'leads_report');
$CI = &get_instance();

hooks()->add_filter('get_dashboard_widgets', 'leads_report_add_dashboard_widget');
hooks()->add_action('app_admin_head', 'leads_report_load_js');
/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(LEADS_REPORT_MODULE_NAME, [LEADS_REPORT_MODULE_NAME]);

function leads_report_add_dashboard_widget($widgets)
{
    $widgets[] = [
        'path'      => 'leads_report/leads_report_widget',
        'container' => 'top-12',
    ];
    return $widgets;
}

/**
 * Injects JavaScript
 * @return null
 */
function leads_report_load_js(){
    $CI = &get_instance();
    $CI->load->view('leads_report/widget_js');
}