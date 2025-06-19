<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Custom Links
Description: Add your custom links to perfex main menu and setup menu
Version: 1.1.5
Requires at least: 2.3.*
*/

define('CUSTOM_LINKS_MODULE_VERSION', '1.1.5');
define('CUSTOM_LINKS_MODULE_NAME', 'custom_links');
define('CUSTOM_LINKS_TABLE_NAME', db_prefix().'custom_links');

$CI = &get_instance();
/**
 * Load the module helper
 */
$CI->load->helper(CUSTOM_LINKS_MODULE_NAME . '/custom_links');

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(CUSTOM_LINKS_MODULE_NAME, [CUSTOM_LINKS_MODULE_NAME]);

// Adding permission for module
hooks()->add_action('staff_permissions', 'custom_links_staff_permissions', 10, 2);
// Adding setup menu item for module
hooks()->add_action('admin_init', 'add_setup_menu_custom_links_link');
// Adding client menu items
hooks()->add_action('clients_init', 'add_client_menu_custom_links');

/**
 * Register activation module hook
 */
register_activation_hook(CUSTOM_LINKS_MODULE_NAME, 'custom_links_activation_hook');

function custom_links_activation_hook(){
    require_once(__DIR__ . '/install.php');
}

/**
 * Register deactivation module hook
 */
register_deactivation_hook(CUSTOM_LINKS_MODULE_NAME, 'custom_links_de_activation_hook');

function custom_links_de_activation_hook(){
    require_once(__DIR__ . '/deactivate.php');
}

/**
 * Register uninstall module hook
 */
register_uninstall_hook(CUSTOM_LINKS_MODULE_NAME, 'custom_links_uninstall_hook');

function custom_links_uninstall_hook(){
    require_once(__DIR__ . '/uninstall.php');
}


// ADDING SCRIPT FILES
hooks()->add_action('before_compile_scripts_assets', 'add_custom_links_scripts');
// ADDING CSS FILES
hooks()->add_action('before_compile_css_assets', 'add_custom_links_css');
