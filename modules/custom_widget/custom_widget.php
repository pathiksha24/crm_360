<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Custom Widget
Description: Custom Widget
Version: 2.3.0
Requires at least: 2.3.*
*/

define('CUSTOM_WIDGET_MODULE_NAME', 'custom_widget');

$CI = &get_instance();
register_language_files(CUSTOM_WIDGET_MODULE_NAME, [CUSTOM_WIDGET_MODULE_NAME]);
$CI->load->helper(CUSTOM_WIDGET_MODULE_NAME . '/custom_widget');
hooks()->add_action('app_admin_footer', 'custom_widget_load_footer_js');

function custom_widget_load_footer_js()
{
    $CI = &get_instance();
    $CI->load->view('custom_widget/widget_js');
}
hooks()->add_filter('get_dashboard_widgets', 'custom_widget_add_dashboard_widget');
function custom_widget_add_dashboard_widget($widgets)
{
    $widgets[] = [
        'path'      => 'custom_widget/widget',
        'container' => 'top-12',
    ];
    return $widgets;
}