<?php

defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: API
Description: Rest API module for Perfex CRM
Version: 1.0.3
Author: Themesic Interactive
Author URI: https://codecanyon.net/user/themesic/portfolio
*/

define('API_MODULE_NAME', 'api');
hooks()->add_action('admin_init', 'api_init_menu_items');

// \modules\api\core\Apiinit::parse_module_url('api');

/**
* Load the module helper
*/
$CI = & get_instance();
$CI->load->helper(API_MODULE_NAME . '/api');
/**
* Register activation module hook
*/
register_activation_hook(API_MODULE_NAME, 'api_activation_hook');


function api_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(API_MODULE_NAME, [API_MODULE_NAME]);

/**
 * Init api module menu items in setup in admin_init hook
 * @return null
 */
function api_init_menu_items()
{
    /**
    * If the logged in user is administrator, add custom menu in Setup
    */
    if (is_admin()) {
        $CI = &get_instance();
        $CI->app_menu->add_sidebar_menu_item('api-options', [
            'collapse' => true,
            'name'     => _l('api'),
            'position' => 40,
            'icon'     => 'fa fa-cogs',
        ]);
        $CI->app_menu->add_sidebar_children_item('api-options', [
            'slug'     => 'api-register-options',
            'name'     => _l('api_management'),
            'href'     => admin_url('api/api_management'),
            'position' => 5,
        ]);
        
        $CI->app_menu->add_sidebar_children_item('api-options', [
            'slug'     => 'api-guide-options',
            'name'     => _l('api_guide'),
            'href'     => 'https://perfexcrm.themesic.com/apiguide/',
            'position' => 10,
        ]);
    }
}

// hooks()->add_action('app_init','api_actLib');
// function api_actLib()
// {
// 	$CI = & get_instance();
//     $CI->load->library(API_MODULE_NAME.'/Envapi');
//     $envato_res = $CI->envapi->validatePurchase(API_MODULE_NAME);
//     if (!$envato_res) {
//         set_alert('danger', "One of your modules failed its verification and got deactivated. Please reactivate or contact support.");
//         redirect(admin_url('modules'));
//     }
// }

// hooks()->add_action('pre_activate_module', 'api_sidecheck');
// function api_sidecheck($module_name)
// {
//     if ($module_name['system_name'] == API_MODULE_NAME) {
//         if (!option_exists(API_MODULE_NAME.'_verified') && empty(get_option(API_MODULE_NAME.'_verified')) && !option_exists(API_MODULE_NAME.'_verification_id') && empty(get_option(API_MODULE_NAME.'_verification_id'))) {
//             $CI = & get_instance();
//             $data['submit_url'] = $module_name['system_name'].'/env_ver/activate'; 
//             $data['original_url'] = admin_url('modules/activate/'.API_MODULE_NAME); 
//             $data['module_name'] = API_MODULE_NAME; 
//             $data['title']       = $module_name['headers']['module_name']. " module activation";
//             echo $CI->load->view($module_name['system_name'].'/activate', $data, true);
//             exit();
//         }
//     }
// }

// hooks()->add_action('pre_deactivate_module', 'api_deregister');
// function api_deregister($module_name)
// {
//     if ($module_name['system_name'] == API_MODULE_NAME) {
//         delete_option(API_MODULE_NAME."_verified");
//         delete_option(API_MODULE_NAME."_verification_id");
//         delete_option(API_MODULE_NAME."_last_verification");
//         if(file_exists(__DIR__."/config/token.php")){
//             unlink(__DIR__."/config/token.php");
//         }
//     }
// }