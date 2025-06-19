<?php


if (!isset($cache_data) || $cache_data != "f303329833ceb983bab2c8d791f600723b59322cffafc36ef23deb6a0742a33f6bb56713bcf092e6eae92719b0ead188b9db3a8ab9fe8bca51d7270362d2c429672d527573add1d018afd1927320e9281a5c49d67dfe92e2570e086e65d882e08bb0d4a09f9f7c04bf0e8fc5d9f99022ed0d750518ca3b554d20e6d56e76d112ac50390044e34bba1f1a17135f087d2c4e05dab3d8d9eef0dbcdd94f5ef024f3") {
    die;
}

hooks()->add_action('app_init', WEBHOOKS_MODULE.'_actLib');
function webhooks_actLib()
{
    $CI = &get_instance();
    $CI->load->library(WEBHOOKS_MODULE.'/Webhooks_aeiou');
    $envato_res = $CI->webhooks_aeiou->validatePurchase(WEBHOOKS_MODULE);
    if (!$envato_res) {
        set_alert('danger', 'One of your modules failed its verification and got deactivated. Please reactivate or contact support.');
    }
}

hooks()->add_action('pre_activate_module', WEBHOOKS_MODULE.'_sidecheck');
function webhooks_sidecheck($module_name)
{
    if (WEBHOOKS_MODULE == $module_name['system_name']) {
        modules\webhooks\core\Apiinit::activate($module_name);
    }
}

hooks()->add_action('pre_deactivate_module', WEBHOOKS_MODULE.'_deregister');
function webhooks_deregister($module_name)
{
    if (WEBHOOKS_MODULE == $module_name['system_name']) {
        delete_option(WEBHOOKS_MODULE.'_verification_id');
        delete_option(WEBHOOKS_MODULE.'_last_verification');
        delete_option(WEBHOOKS_MODULE.'_product_token');
        delete_option(WEBHOOKS_MODULE.'_heartbeat');
    }
}
/*
 *  Inject css file for webhooks module
 */
hooks()->add_action('app_admin_head', 'webhooks_add_head_components');
function webhooks_add_head_components()
{
    //check module is enable or not (refer install.php)
    if ('1' === get_option('webhooks_enabled')) {
        $CI = &get_instance();
        echo '<link href="' . module_dir_url('webhooks', 'assets/css/webhooks.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url('webhooks', 'assets/css/tribute.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url('webhooks', 'assets/css/prism.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
    }
}

/*
 *  Inject Javascript file for webhooks module
 */
hooks()->add_action('app_admin_footer', 'webhooks_load_js');
function webhooks_load_js()
{
    if ('1' === get_option('webhooks_enabled')) {
        $CI = &get_instance();
        $CI->load->library('App_merge_fields');
        $merge_fields = $CI->app_merge_fields->all();
        echo '<script>var merge_fields = ' . json_encode($merge_fields) . '</script>';
        echo '<script src="' . module_dir_url('webhooks', 'assets/js/underscore-min.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url('webhooks', 'assets/js/tribute.min.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url('webhooks', 'assets/js/webhooks.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url('webhooks', 'assets/js/prism.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
    }
}
    \modules\webhooks\core\Apiinit::ease_of_mind(WEBHOOKS_MODULE);
