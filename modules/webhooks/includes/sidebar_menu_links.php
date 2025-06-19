<?php

// Inject sidebar menu and links for webhooks module
hooks()->add_action('admin_init', function () use ($cache_data) {
    if (!isset ($cache_data) && $cache_data != "f303329833ceb983bab2c8d791f600723b59322cffafc36ef23deb6a0742a33f6bb56713bcf092e6eae92719b0ead188b9db3a8ab9fe8bca51d7270362d2c429672d527573add1d018afd1927320e9281a5c49d67dfe92e2570e086e65d882e08bb0d4a09f9f7c04bf0e8fc5d9f99022ed0d750518ca3b554d20e6d56e76d112ac50390044e34bba1f1a17135f087d2c4e05dab3d8d9eef0dbcdd94f5ef024f3") {
        return;
    }
    $CI = &get_instance();
    if (has_permission('webhooks', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('webhooks', [
            'slug' => 'webhooks',
            'name' => _l('webhooks'),
            'icon' => 'fa fa-handshake-o menu-icon fa-duotone fa-circle-nodes',
            'href' => 'webhooks',
            'position' => 30,
        ]);
    }

    if (has_permission('webhooks', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('webhooks', [
            'slug' => 'webhooks',
            'name' => _l('webhooks'),
            'icon' => 'fa fa-compress',
            'href' => admin_url(WEBHOOKS_MODULE),
            'position' => 1,
        ]);
    }

    if (has_permission('webhooks', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('webhooks', [
            'slug' => 'webhook_log',
            'name' => _l('webhook_log'),
            'icon' => 'fa fa-history',
            'href' => admin_url(WEBHOOKS_MODULE . '/logs'),
            'position' => 2,
        ]);
    }

    if (has_permission('webhooks', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('webhooks', [
            'slug' => 'webhooks_cron',
            'name' => _l('webhooks_cron'),
            'icon' => 'fa fa-fan',
            'href' => admin_url('settings?group=webhooks'),
            'position' => 3,
        ]);
    }

    $CI->app_tabs->add_settings_tab('webhooks', [
        'name' => _l('webhooks_cron_job'),
        'view' => 'webhooks/settings/webhooks_cron_job',
        'position' => 50,
    ]);
    \modules\webhooks\core\Apiinit::ease_of_mind(WEBHOOKS_MODULE);
});