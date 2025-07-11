<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Leads Customization
Description: Leads Customization
Version: 1.0.0
Requires at least: 2.3.*
Author: Bekka Codes
Author URI: https://www.fiverr.com/ossamaben
*/

define('LEADS_CUSTOM_MODULE_NAME', 'leads_customization');
$CI = &get_instance();
$CI->load->helper(LEADS_CUSTOM_MODULE_NAME . '/leads_customization');
hooks()->add_action('app_admin_head', 'leads_customization_load_js');
hooks()->add_filter('get_dashboard_widgets', 'leads_customization_add_dashboard_widget');
hooks()->add_filter('get_dashboard_widgets', 'callcenter_leads_staff_widget');
hooks()->add_filter('lead_available_dupicate_validation_fields_option', 'leads_customization_lead_available_dupicate_validation_fields_option');


function leads_customization_lead_available_dupicate_validation_fields_option($fields)
{
    $whatsapp_number = [
        'value'=>'whatsapp_number',
        'name'=>_l('whatsapp_number'),
    ];
    $fields[] = $whatsapp_number;
    return $fields;
}
function leads_customization_add_dashboard_widget($widgets)
{
    $widgets[] = [
        'path'      => 'leads_customization/leads_customization_widget',
        'container' => 'top-12',
    ];
    return $widgets;
}
hooks()->add_filter('get_dashboard_widgets', 'callcenter_leads_staff_widget');

function callcenter_leads_staff_widget($widgets)
{
   $widgets[] = [
  'path'      => 'callcenter_leads/callcenter_staffs_widget',
  'container' => 'top-12',
];

    return $widgets;
}

function leads_customization_load_js(){
        echo '<script src="' . module_dir_url(LEADS_CUSTOM_MODULE_NAME, 'assets/js/global.js') .'?v=' . time(). '"></script>';
    $CI = &get_instance();
    $CI->load->view('leads_customization/widget_js');
}
/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(LEADS_CUSTOM_MODULE_NAME, [LEADS_CUSTOM_MODULE_NAME]);


register_activation_hook(LEADS_CUSTOM_MODULE_NAME, 'leads_customization_module_activation_hook');
function leads_customization_module_activation_hook()
{
    $CI = &get_instance();
    //install required fields to database
    require_once(__DIR__ . '/install.php');
    //install required fields to database
    $content = '<?php
$route["admin/leads"] = "leads_customization/admin_leads/index";
$route["admin/leads/import"] = "leads_customization/admin_leads/import";
$route["admin/leads/lead/(:any)"] = "leads_customization/admin_leads/lead/$1";
$route["admin/leads/table"] = "leads_customization/admin_leads/table";
$route["admin/leads/lead"] = "leads_customization/admin_leads/lead";
$route["admin/leads/service"] = "leads_customization/admin_leads/service";
$route["admin/leads/language"] = "leads_customization/admin_leads/language";
    ';
    $filename = APPPATH . 'config/my_routes.php';
    file_put_contents($filename, $content);
}

register_deactivation_hook(LEADS_CUSTOM_MODULE_NAME, 'leads_customization_module_deactivation_hook');
function leads_customization_module_deactivation_hook()
{
    $filename = APPPATH . 'config/my_routes.php';
    unlink($filename);
}

hooks()->add_action('admin_init', 'leads_customization_init_menu_items');
function leads_customization_init_menu_items()
{
    $CI = &get_instance();
    if(is_admin()){
        $CI->app_menu->add_setup_children_item('leads', [
            'slug'     => 'leads_customization-services',
            'href'     => admin_url('leads_customization/services'),
            'name'     => _l('services'),
            'position' => 20,
        ]);
        $CI->app_menu->add_setup_children_item('leads', [
            'slug'     => 'leads_customization-languages',
            'href'     => admin_url('leads_customization/languages'),
            'name'     => _l('languages'),
            'position' => 40,
        ]);

        if (get_staff_user_id() == 42) {
            $CI->app_tabs->add_settings_tab('leads_unassigned_settings', [
                'name'     => '' . _l('leads_unassigned_settings') . '',
                'view'     => 'leads_customization/admin_leads/settings',
                'position' => 36,
            ]);
        }
    }

}

hooks()->add_action('app_init', 'restrict_staff_to_login');

function restrict_staff_to_login()
{
//    exit;
//    if (!is_admin() && is_staff_logged_in()){
//        $redirect_url = admin_url('authentication/logout');
//        $current_url = current_url();
//        date_default_timezone_set('Asia/Dubai');
//        $current_time = date('H:i');
//        $start_time = '08:00';
//        $end_time = '20:00';
//
//        if ($current_time >= $start_time && $current_time <= $end_time) {
//            $is_allowed = true;
//        } else {
//            $is_allowed = false;
//        }
//
//        if (!$is_allowed){
//            $allowed_prefixes = [
//                admin_url('authentication/logout'),
//            ];
//            foreach ($allowed_prefixes as $prefix) {
//                if (strpos($current_url, $prefix) === 0) {
//                    $is_allowed = true;
//                    break;
//                }
//            }
//        }
//
//        if (!$is_allowed) {
//            redirect($redirect_url);
//            exit;
//        }
//    }
}