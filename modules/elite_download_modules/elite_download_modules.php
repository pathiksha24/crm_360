<?php

defined('BASEPATH') or exit('No direct script access allowed');
/*
  Module Name: Elite Download Modules
  Version: 1.0.0
  Author: wpeliteplugins
  Author URI: http://wpeliteplugins.com/
  Description: Allows admin to download installed modules ZIP files directly from your admin dashboard without using FTP
  Requires at least: 1.*
 */

define('ELITE_DM_MODULE_NAME', 'elite_download_modules');
define('ELITE_DM_PREFIX', 'elitedm');

$CI = &get_instance();

/**
 * Register module activation hook
 * 
 * Setup inital values on module activation
 */
register_activation_hook(ELITE_DM_MODULE_NAME, 'elite_dm_activation_hook');

function elite_dm_activation_hook() {
    update_option(ELITE_DM_PREFIX . '_module', 'enable');
}

/**
 * Register module deactivation hook
 * 
 * Disable code on module deactivation
 */
register_deactivation_hook(ELITE_DM_MODULE_NAME, 'elite_dm_deactivation_hook');

function elite_dm_deactivation_hook() {
    update_option(ELITE_DM_PREFIX . '_module', 'disable');
}

/**
 * Register module uninstall hook
 * 
 * Disable code on module uninstall. Also empty all code area
 */
register_uninstall_hook(ELITE_DM_MODULE_NAME, 'elite_dm_uninstall_hook');

function elite_dm_uninstall_hook() {
    update_option(ELITE_DM_PREFIX . '_module', 'disable');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(ELITE_DM_MODULE_NAME, [ELITE_DM_MODULE_NAME]);

/**
 * Actions for inject the individual module download zip
 */
hooks()->add_filter('module_elite_download_modules_action_links', 'module_elite_download_modules_action_links');
hooks()->add_action('admin_init', ELITE_DM_PREFIX . '_add_action_link');

/**
 * Add additional settings for this module in the module list area
 * @param  array $actions current actions
 * @return array
 */
function module_elite_download_modules_action_links($actions) {
    $actions[] = '<a href="http://documents.wpeliteplugins.com/elite-download-modules" target="_blank">' . _l('elite_document') . '</a>';
    return $actions;
}

function elitedm_add_action_link() {
    if (get_option(ELITE_DM_PREFIX . '_module') == 'enable') {
        $CI = &get_instance();
        $modules = $CI->app_modules->get();

        if (!empty($modules)) {
            foreach ($modules as $module) {
                $system_name = $module['system_name'];
                hooks()->add_filter("module_{$system_name}_action_links", function ($actions) use ($system_name) {
                    $actions[] = "<a href='" . admin_url(ELITE_DM_MODULE_NAME) . "/download_module/" . $system_name . "'><span style='color:green;'>" . _l('elite_download_zip') . "</span></a>";
                    return $actions;
                });
            }
        }
    }
}