<?php

defined('BASEPATH') or exit('No direct script access allowed');

function app_init_admin_sidebar_menu_items()
{
    $CI = &get_instance();

    $CI->app_menu->add_sidebar_menu_item('dashboard', [
        'name'     => _l('als_dashboard'),
        'href'     => admin_url(),
        'position' => 1,
        'icon'     => 'fa-regular fa-object-group',
        'badge'    => [],
    ]);
    if (is_admin()) {
    $CI->app_menu->add_sidebar_menu_item('custom-dashboard', [
    'name'     => _l('custom_dashboard'),
    'href'     => admin_url('custom_dashboard'),
    'position' => 2.01, 
    'icon'     => 'fa-solid fa-gauge-high',
    'badge'    => [],
]);
}


    if (
        staff_can('view',  'customers')
        || (have_assigned_customers()
            || (!have_assigned_customers() && staff_can('create',  'customers')))
    ) {
        $CI->app_menu->add_sidebar_menu_item('customers', [
            'name'     => _l('als_clients'),
            'href'     => admin_url('clients'),
            'position' => 5,
            'icon'     => 'fa-regular fa-user',
            'badge'    => [],
        ]);
    }

    $CI->app_menu->add_sidebar_menu_item('sales', [
        'collapse' => true,
        'name'     => _l('als_sales'),
        'position' => 10,
        'icon'     => 'fa-solid fa-bolt',
        'badge'    => [],
    ]);

    if ((staff_can('view',  'proposals') || staff_can('view_own',  'proposals'))
        || (staff_has_assigned_proposals() && get_option('allow_staff_view_proposals_assigned') == 1)
    ) {
        $CI->app_menu->add_sidebar_children_item('sales', [
            'slug'     => 'proposals',
            'name'     => _l('proposals'),
            'href'     => admin_url('proposals'),
            'position' => 5,
            'badge'    => [],
        ]);
    }

    if ((staff_can('view',  'estimates') || staff_can('view_own',  'estimates'))
        || (staff_has_assigned_estimates() && get_option('allow_staff_view_estimates_assigned') == 1)
    ) {
        $CI->app_menu->add_sidebar_children_item('sales', [
            'slug'     => 'estimates',
            'name'     => _l('estimates'),
            'href'     => admin_url('estimates'),
            'position' => 10,
            'badge'    => [],
        ]);
    }

    if ((staff_can('view',  'invoices') || staff_can('view_own',  'invoices'))
        || (staff_has_assigned_invoices() && get_option('allow_staff_view_invoices_assigned') == 1)
    ) {
        $CI->app_menu->add_sidebar_children_item('sales', [
            'slug'     => 'invoices',
            'name'     => _l('invoices'),
            'href'     => admin_url('invoices'),
            'position' => 15,
            'badge'    => [],
        ]);
    }

    if (
        staff_can('view',  'payments') || staff_can('view_own',  'invoices')
        || (get_option('allow_staff_view_invoices_assigned') == 1 && staff_has_assigned_invoices())
    ) {
        $CI->app_menu->add_sidebar_children_item('sales', [
            'slug'     => 'payments',
            'name'     => _l('payments'),
            'href'     => admin_url('payments'),
            'position' => 20,
            'badge'    => [],
        ]);
    }

    if (staff_can('view',  'credit_notes') || staff_can('view_own',  'credit_notes')) {
        $CI->app_menu->add_sidebar_children_item('sales', [
            'slug'     => 'credit_notes',
            'name'     => _l('credit_notes'),
            'href'     => admin_url('credit_notes'),
            'position' => 25,
            'badge'    => [],
        ]);
    }

    if (staff_can('view',  'items')) {
        $CI->app_menu->add_sidebar_children_item('sales', [
            'slug'     => 'items',
            'name'     => _l('items'),
            'href'     => admin_url('invoice_items'),
            'position' => 30,
            'badge'    => [],
        ]);
    }

    if (staff_can('view',  'subscriptions') || staff_can('view_own',  'subscriptions')) {
        $CI->app_menu->add_sidebar_menu_item('subscriptions', [
            'name'     => _l('subscriptions'),
            'href'     => admin_url('subscriptions'),
            'icon'     => 'fa fa-repeat',
            'position' => 15,
            'badge'    => [],
        ]);
    }

    if (staff_can('view',  'expenses') || staff_can('view_own',  'expenses')) {
        $CI->app_menu->add_sidebar_menu_item('expenses', [
            'name'     => _l('expenses'),
            'href'     => admin_url('expenses'),
            'icon'     => 'fa-regular fa-file-lines',
            'position' => 20,
            'badge'    => [],
        ]);
    }

    if (staff_can('view',  'contracts') || staff_can('view_own',  'contracts')) {
        $CI->app_menu->add_sidebar_menu_item('contracts', [
            'name'     => _l('contracts'),
            'href'     => admin_url('contracts'),
            'icon'     => 'fa-regular fa-note-sticky',
            'position' => 25,
            'badge'    => [],
        ]);
    }

    $CI->app_menu->add_sidebar_menu_item('projects', [
        'name'     => _l('projects'),
        'href'     => admin_url('projects'),
        'icon'     => 'fa-solid fa-chart-gantt',
        'position' => 30,
        'badge'    => [],
    ]);

    $CI->app_menu->add_sidebar_menu_item('tasks', [
        'name'     => _l('als_tasks'),
        'href'     => admin_url('tasks'),
        'icon'     => 'fa-regular fa-circle-check',
        'position' => 35,
        'badge'    => [],
    ]);

    if ((!is_staff_member() && get_option('access_tickets_to_none_staff_members') == 1) || is_staff_member()) {
        $enable_badge = get_option('enable_support_menu_badges');
        $CI->app_menu->add_sidebar_menu_item('support', [
            'collapse' => $enable_badge ? true : null,
            'name'     => _l('support'),
            'href'     => admin_url('tickets'),
            'icon'     => 'fa-regular fa-life-ring',
            'position' => 40,
            'badge'    => [],
        ]);

        $CI->load->model('tickets_model');
        $statuses = $CI->tickets_model->get_ticket_status();

        if ($enable_badge) {
            foreach ($statuses as $status) {
                $CI->app_menu->add_sidebar_children_item('support', [
                    'slug'     => 'support-' . $status['ticketstatusid'],
                    'name'     => ticket_status_translate($status['ticketstatusid']),
                    'href'     => admin_url('tickets/index/' . $status['ticketstatusid']),
                    'position' => $status['statusorder'],
                    'badge'    => [
                        'value' => $CI->tickets_model->ticket_count($status['ticketstatusid']),
                        'color' => $status['statuscolor'],
                    ],
                ]);
            }
        }
    }

    if (is_staff_member()) {
        $CI->app_menu->add_sidebar_menu_item('leads', [
            'name'     => _l('als_leads'),
            'href'     => admin_url('leads'),
            'icon'     => 'fa-solid fa-crosshairs',
            'position' => 45,
            'badge'    => [],
        ]);
    }

    if ((staff_can('view',  'estimate_request') || staff_can('view_own',  'estimate_request'))) {
        $CI->app_menu->add_sidebar_menu_item('estimate_request', [
            'name'     => _l('estimate_request'),
            'href'     => admin_url('estimate_request'),
            'position' => 46,
            'icon'     => 'fa-regular fa-file',
            'badge'    => [],
        ]);
    }

    if (staff_can('view',  'knowledge_base')) {
        $CI->app_menu->add_sidebar_menu_item('knowledge-base', [
            'name'     => _l('als_kb'),
            'href'     => admin_url('knowledge_base'),
            'icon'     => 'fa-regular fa-question-circle',
            'position' => 50,
            'badge'    => [],
        ]);
    }

    // Utilities
    $CI->app_menu->add_sidebar_menu_item('utilities', [
        'collapse' => true,
        'name'     => _l('als_utilities'),
        'position' => 55,
        'icon'     => 'fa-regular fa-circle-dot',
        'badge'    => [],
    ]);

    $CI->app_menu->add_sidebar_children_item('utilities', [
        'slug'     => 'media',
        'name'     => _l('als_media'),
        'href'     => admin_url('utilities/media'),
        'position' => 5,
        'badge'    => [],
    ]);

    if (staff_can('view',  'bulk_pdf_exporter')) {
        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug'     => 'bulk-pdf-exporter',
            'name'     => _l('bulk_pdf_exporter'),
            'href'     => admin_url('utilities/bulk_pdf_exporter'),
            'position' => 10,
            'badge'    => [],
        ]);
    }

    $CI->app_menu->add_sidebar_children_item('utilities', [
        'slug'     => 'calendar',
        'name'     => _l('als_calendar_submenu'),
        'href'     => admin_url('utilities/calendar'),
        'position' => 15,
        'badge'    => [],
    ]);


    if (is_admin()) {
        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug'     => 'announcements',
            'name'     => _l('als_announcements_submenu'),
            'href'     => admin_url('announcements'),
            'position' => 20,
            'badge'    => [],
        ]);

        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug'     => 'activity-log',
            'name'     => _l('als_activity_log_submenu'),
            'href'     => admin_url('utilities/activity_log'),
            'position' => 25,
            'badge'    => [],
        ]);

        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug'     => 'ticket-pipe-log',
            'name'     => _l('ticket_pipe_log'),
            'href'     => admin_url('utilities/pipe_log'),
            'position' => 30,
            'badge'    => [],
        ]);
    }

    if (staff_can('view-timesheets', 'reports') || staff_can('view', 'reports')) {
        $CI->app_menu->add_sidebar_menu_item('reports', [
            'collapse' => true,
            'name'     => _l('als_reports'),
            'href'     => admin_url('reports'),
            'icon'     => 'fa-solid fa-chart-line',
            'position' => 60,
            'badge'    => [],
        ]);
    }

    if (staff_can('view-timesheets', 'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'timesheets-reports',
            'name'     => _l('timesheets_overview'),
            'href'     => admin_url('staff/timesheets?view=all'),
            'position' => 25,
            'badge'    => [],
        ]);
    }

    if (staff_can('view',  'reports')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'sales-reports',
            'name'     => _l('als_reports_sales_submenu'),
            'href'     => admin_url('reports/sales'),
            'position' => 5,
            'badge'    => [],
        ]);
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'expenses-reports',
            'name'     => _l('als_reports_expenses'),
            'href'     => admin_url('reports/expenses'),
            'position' => 10,
            'badge'    => [],
        ]);
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'expenses-vs-income-reports',
            'name'     => _l('als_expenses_vs_income'),
            'href'     => admin_url('reports/expenses_vs_income'),
            'position' => 15,
            'badge'    => [],
        ]);
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'leads-reports',
            'name'     => _l('als_reports_leads_submenu'),
            'href'     => admin_url('reports/leads'),
            'position' => 20,
            'badge'    => [],
        ]);
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'knowledge-base-reports',
            'name'     => _l('als_kb_articles_submenu'),
            'href'     => admin_url('reports/knowledge_base_articles'),
            'position' => 30,
            'badge'    => [],
        ]);
    }

    // Setup menu
    if (staff_can('view',  'staff')) {
        $CI->app_menu->add_setup_menu_item('staff', [
            'name'     => _l('als_staff'),
            'href'     => admin_url('staff'),
            'position' => 5,
            'badge'    => [],
        ]);
    }

    if (is_admin()) {
        $CI->app_menu->add_setup_menu_item('customers', [
            'collapse' => true,
            'name'     => _l('clients'),
            'position' => 10,
            'badge'    => [],
        ]);

        $CI->app_menu->add_setup_children_item('customers', [
            'slug'     => 'customer-groups',
            'name'     => _l('customer_groups'),
            'href'     => admin_url('clients/groups'),
            'position' => 5,
            'badge'    => [],
        ]);
        $CI->app_menu->add_setup_menu_item('support', [
            'collapse' => true,
            'name'     => _l('support'),
            'position' => 15,
            'badge'    => [],
        ]);

        $CI->app_menu->add_setup_children_item('support', [
            'slug'     => 'departments',
            'name'     => _l('acs_departments'),
            'href'     => admin_url('departments'),
            'position' => 5,
            'badge'    => [],
        ]);
        $CI->app_menu->add_setup_children_item('support', [
            'slug'     => 'tickets-predefined-replies',
            'name'     => _l('acs_ticket_predefined_replies_submenu'),
            'href'     => admin_url('tickets/predefined_replies'),
            'position' => 10,
            'badge'    => [],
        ]);
        $CI->app_menu->add_setup_children_item('support', [
            'slug'     => 'tickets-priorities',
            'name'     => _l('acs_ticket_priority_submenu'),
            'href'     => admin_url('tickets/priorities'),
            'position' => 15,
            'badge'    => [],
        ]);
        $CI->app_menu->add_setup_children_item('support', [
            'slug'     => 'tickets-statuses',
            'name'     => _l('acs_ticket_statuses_submenu'),
            'href'     => admin_url('tickets/statuses'),
            'position' => 20,
            'badge'    => [],
        ]);

        $CI->app_menu->add_setup_children_item('support', [
            'slug'     => 'tickets-services',
            'name'     => _l('acs_ticket_services_submenu'),
            'href'     => admin_url('tickets/services'),
            'position' => 25,
            'badge'    => [],
        ]);
        $CI->app_menu->add_setup_children_item('support', [
            'slug'     => 'tickets-spam-filters',
            'name'     => _l('spam_filters'),
            'href'     => admin_url('spam_filters/view/tickets'),
            'position' => 30,
            'badge'    => [],
        ]);

        $CI->app_menu->add_setup_menu_item('leads', [
            'collapse' => true,
            'name'     => _l('acs_leads'),
            'position' => 20,
            'badge'    => [],
        ]);
        $CI->app_menu->add_setup_children_item('leads', [
            'slug'     => 'leads-sources',
            'name'     => _l('acs_leads_sources_submenu'),
            'href'     => admin_url('leads/sources'),
            'position' => 5,
            'badge'    => [],
        ]);
        $CI->app_menu->add_setup_children_item('leads', [
            'slug'     => 'leads-statuses',
            'name'     => _l('acs_leads_statuses_submenu'),
            'href'     => admin_url('leads/statuses'),
            'position' => 10,
            'badge'    => [],
        ]);
        $CI->app_menu->add_setup_children_item('leads', [
            'slug'     => 'leads-email-integration',
            'name'     => _l('leads_email_integration'),
            'href'     => admin_url('leads/email_integration'),
            'position' => 15,
            'badge'    => [],
        ]);
        $CI->app_menu->add_setup_children_item('leads', [
            'slug'     => 'web-to-lead',
            'name'     => _l('web_to_lead'),
            'href'     => admin_url('leads/forms'),
            'position' => 20,
            'badge'    => [],
        ]);

        $CI->app_menu->add_setup_menu_item('finance', [
            'collapse' => true,
            'name'     => _l('acs_finance'),
            'position' => 25,
            'badge'    => [],
        ]);
        $CI->app_menu->add_setup_children_item('finance', [
            'slug'     => 'taxes',
            'name'     => _l('acs_sales_taxes_submenu'),
            'href'     => admin_url('taxes'),
            'position' => 5,
            'badge'    => [],
        ]);
        $CI->app_menu->add_setup_children_item('finance', [
            'slug'     => 'currencies',
            'name'     => _l('acs_sales_currencies_submenu'),
            'href'     => admin_url('currencies'),
            'position' => 10,
            'badge'    => [],
        ]);
        $CI->app_menu->add_setup_children_item('finance', [
            'slug'     => 'payment-modes',
            'name'     => _l('acs_sales_payment_modes_submenu'),
            'href'     => admin_url('paymentmodes'),
            'position' => 15,
            'badge'    => [],
        ]);
        $CI->app_menu->add_setup_children_item('finance', [
            'slug'     => 'expenses-categories',
            'name'     => _l('acs_expense_categories'),
            'href'     => admin_url('expenses/categories'),
            'position' => 20,
            'badge'    => [],
        ]);

        $CI->app_menu->add_setup_menu_item('contracts', [
            'collapse' => true,
            'name'     => _l('acs_contracts'),
            'position' => 30,
            'badge'    => [],
        ]);
        $CI->app_menu->add_setup_children_item('contracts', [
            'slug'     => 'contracts-types',
            'name'     => _l('acs_contract_types'),
            'href'     => admin_url('contracts/types'),
            'position' => 5,
            'badge'    => [],
        ]);

        $modulesNeedsUpgrade = $CI->app_modules->number_of_modules_that_require_database_upgrade();

        $CI->app_menu->add_setup_menu_item('modules', [
            'href'     => admin_url('modules'),
            'name'     => _l('modules'),
            'position' => 35,
            'badge'    => [
                'value' => $modulesNeedsUpgrade > 0 ? $modulesNeedsUpgrade : null,
                'type' => 'warning',
            ],
        ]);

        $CI->app_menu->add_setup_menu_item('custom-fields', [
            'href'     => admin_url('custom_fields'),
            'name'     => _l('asc_custom_fields'),
            'position' => 45,
            'badge'    => [],
        ]);

        $CI->app_menu->add_setup_menu_item('gdpr', [
            'href'     => admin_url('gdpr'),
            'name'     => _l('gdpr_short'),
            'position' => 50,
            'badge'    => [],
        ]);

        $CI->app_menu->add_setup_menu_item('roles', [
            'href'     => admin_url('roles'),
            'name'     => _l('acs_roles'),
            'position' => 55,
            'badge'    => [],
        ]);

        /*             $CI->app_menu->add_setup_menu_item('api', [
                          'href'     => admin_url('api'),
                          'name'     => 'API',
                          'position' => 65,
                  ]);*/
    }

    if (staff_can('view',  'settings')) {
        $CI->app_menu->add_setup_menu_item('settings', [
            'href'     => admin_url('settings'),
            'name'     => _l('acs_settings'),
            'position' => 200,
            'badge'    => [],
        ]);
    }

    if (staff_can('view',  'email_templates')) {
        $CI->app_menu->add_setup_menu_item('email-templates', [
            'href'     => admin_url('emails'),
            'name'     => _l('acs_email_templates'),
            'position' => 40,
            'badge'    => [],
        ]);
    }

    if (staff_can('view',  'settings')) {
        $CI->app_menu->add_setup_menu_item('estimate_request', [
            'collapse' => true,
            'name'     => _l('acs_estimate_request'),
            'position' => 34,
            'badge'    => [],
        ]);
    }

    $CI->app_menu->add_setup_children_item('estimate_request', [
        'slug'     => 'estimate-request-forms',
        'name'     => _l('acs_estimate_request_forms'),
        'href'     => admin_url('estimate_request/forms'),
        'position' => 5,
        'badge'    => [],
    ]);

    $CI->app_menu->add_setup_children_item('estimate_request', [
        'slug'     => 'estimate-request-statuses',
        'name'     => _l('acs_estimate_request_statuses_submenu'),
        'href'     => admin_url('estimate_request/statuses'),
        'position' => 10,
        'badge'    => [],
    ]);
}