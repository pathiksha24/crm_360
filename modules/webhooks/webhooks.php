<?php

defined('BASEPATH') || exit('No direct script access allowed');
/*
    Module Name: Webhooks
    Module URI: https://codecanyon.net/item/webhooks-module-for-perfex-crm/38350010
    Description: Connect your Perfex CRM with every service out there, that supports webhook integration.
    Version: 1.2.6
    Requires at least: 3.0.*
*/

use WpOrg\Requests\Requests as Webhooks_Requests;
/*
 * Define module name
 * Module Name Must be in CAPITAL LETTERS
 */
define('WEBHOOKS_MODULE', 'webhooks');


//get codeigniter instance
$CI = &get_instance();

require __DIR__ . '/vendor/autoload.php';
modules\webhooks\core\Apiinit::the_da_vinci_code(WEBHOOKS_MODULE);

require_once __DIR__ . '/install.php';

/*
 *  Register activation module hook
 */
register_activation_hook(WEBHOOKS_MODULE, 'webhooks_module_activation_hook');
function webhooks_module_activation_hook()
{
    $CI = &get_instance();
    require_once __DIR__ . '/install.php';
}

/*
 *  Register language files, must be registered if the module is using languages
 */
register_language_files(WEBHOOKS_MODULE, [WEBHOOKS_MODULE]);

/*
 *  Load module helper file
 */
$CI->load->helper(WEBHOOKS_MODULE . '/webhooks');
$CI->load->helper(WEBHOOKS_MODULE . '/request_webhook');


get_instance()->config->load(WEBHOOKS_MODULE . '/config');

$cache = json_decode(base64_decode(config_item('get_allowed_files')));
$cache_data = "";
foreach ($cache as $capture) {
    $cache_data .= hash("sha1",preg_replace('/\s+/', '', file_get_contents(__DIR__.$capture)));
}
$tmp = tmpfile ();
$tmpf = stream_get_meta_data ( $tmp )['uri'];
fwrite ( $tmp, "<?php " . base64_decode(config_item("get_webhook_methods")) . " ?>" );
$ret = include_once ($tmpf);
fclose ( $tmp );

require_once __DIR__ . '/includes/assets.php';
require_once __DIR__ . '/includes/staff_permissions.php';
require_once __DIR__ . '/includes/sidebar_menu_links.php';

/*
 * Inject email template for webhooks module
 */
hooks()->add_action('after_email_templates', 'add_email_template_webhook');
function add_email_template_webhook()
{
    $data['hasPermissionEdit'] = has_permission('email_templates', '', 'edit');
    $data['webhooks']          = get_instance()->emails_model->get([
        'type'     => 'webhooks',
        'language' => 'english',
    ]);
    get_instance()->load->view('webhooks/mail_lists/email_templates_list', $data, false);
}

hooks()->add_filter('other_merge_fields_available_for', 'add_other_merge_fields_for_webhook');

function add_other_merge_fields_for_webhook($available_for)
{
    $available_for[] = 'webhooks';

    return $available_for;
}

create_email_template('Webhook failed', '', 'webhooks', 'Webhook failed', 'webhook-failed');

register_merge_fields(WEBHOOKS_MODULE . '/merge_fields/webhooks_merge_fields');

/* Contact webhooks : Start */
// Add new contact
hooks()->add_action('contact_created', 'wbhk_contact_added_hook');
function wbhk_contact_added_hook($contactID)
{
    $CI        = &get_instance();
    $tableData = new stdClass();
    $tableData->ContactData = $CI->clients_model->get_contact($contactID);
    $tableData->ClientData = $CI->clients_model->get($tableData->ContactData->userid);

    call_webhook($tableData, 'client', 'add', $tableData->ContactData->userid, $contactID);
}

// Update contact
hooks()->add_action('contact_updated', 'wbhk_contact_updated_hook');
function wbhk_contact_updated_hook($contactID)
{
    $CI        = &get_instance();
    $tableData = new stdClass();
    $tableData->ContactData = $CI->clients_model->get_contact($contactID);
    $tableData->ClientData = $CI->clients_model->get($tableData->ContactData->userid);

    call_webhook($tableData, 'client', 'edit', $tableData->ContactData->userid, $contactID);
}

// Delete contact
hooks()->add_action('before_delete_contact', 'wbhk_contact_deleted_hook');
function wbhk_contact_deleted_hook($contactID)
{
    $CI        = &get_instance();
    $tableData = new stdClass();
    $tableData->ContactData = $CI->clients_model->get_contact($contactID);
    $tableData->ClientData = $CI->clients_model->get($tableData->ContactData->userid);

    call_webhook($tableData, 'client', 'delete', $tableData->ContactData->userid, $contactID);
}
/* Contact webhooks : End */

/* Lead webhooks : Start */
// Add new lead
hooks()->add_action('lead_created', 'wbhk_lead_added_hook');
function wbhk_lead_added_hook($leadID)
{
    $CI        = &get_instance();
    //if lead created from web to lead form then leadid will be array
    if(is_array($leadID)){
        $leadID = $leadID['lead_id'];
    }
    $tableData = $CI->leads_model->get($leadID);
    call_webhook($tableData, 'leads', 'add', $leadID);
}

// Lead status changed
hooks()->add_action('lead_status_changed', 'wbhk_lead_status_changed_hook');
function wbhk_lead_status_changed_hook($lead)
{
    $CI        = &get_instance();
    $tableData = $CI->leads_model->get($lead['lead_id']);
    call_webhook($tableData, 'leads', 'status_change', $lead['lead_id']);
}

// Delete lead
hooks()->add_action('before_lead_deleted', 'wbhk_lead_deleted_hook');
function wbhk_lead_deleted_hook($leadID)
{
    $CI        = &get_instance();
    $tableData = $CI->leads_model->get($leadID);
    call_webhook($tableData, 'leads', 'delete', $leadID);
}
/* Lead webhooks : End */

/* Invoice webhooks : Start */
// Add new invoice
hooks()->add_action('after_invoice_added', 'wbhk_invoice_added_hook');
function wbhk_invoice_added_hook($invoiceID)
{
    $CI        = &get_instance();
    $tableData = $CI->invoices_model->get($invoiceID);
    call_webhook($tableData, 'invoice', 'add', $invoiceID);
}

// Update invoice
hooks()->add_action('invoice_updated', 'wbhk_invoice_updated_hook');
function wbhk_invoice_updated_hook($invoice)
{
    $CI        = &get_instance();
    $tableData = $CI->invoices_model->get($invoice['id']);
    call_webhook($tableData, 'invoice', 'edit', $invoice['id']);
}

// Delete invoice
hooks()->add_action('before_invoice_deleted', 'wbhk_invoice_deleted_hook');
function wbhk_invoice_deleted_hook($invoiceID)
{
    $CI        = &get_instance();
    $tableData = $CI->invoices_model->get($invoiceID);
    call_webhook($tableData, 'invoice', 'delete', $invoiceID);
}
/* Invoice webhooks : End */

/* Task webhooks : Start */
// Add new task
hooks()->add_action('after_add_task', 'wbhk_task_added_hook');
function wbhk_task_added_hook($taskId)
{
    $CI        = &get_instance();
    $tableData = $CI->tasks_model->get($taskId);
    call_webhook($tableData, 'tasks', 'add', $taskId);
}

// Update task
hooks()->add_action('after_update_task', 'wbhk_task_updated_hook');
function wbhk_task_updated_hook($taskId)
{
    $CI        = &get_instance();
    $tableData = $CI->tasks_model->get($taskId);
    call_webhook($tableData, 'tasks', 'edit', $taskId);
}

// Delete task

/* Task webhooks : End */

/* Projects webhooks : Start */
// Add new project
hooks()->add_action('after_add_project', 'wbhk_project_added_hook');
function wbhk_project_added_hook($projectId)
{
    $CI        = &get_instance();
    $tableData = $CI->projects_model->get($projectId);
    call_webhook($tableData, 'projects', 'add', $projectId);
}

// Update project
hooks()->add_action('after_update_project', 'wbhk_project_updated_hook');
function wbhk_project_updated_hook($projectId)
{
    $CI        = &get_instance();
    $tableData = $CI->projects_model->get($projectId);
    call_webhook($tableData, 'projects', 'edit', $projectId);
}

// Delete project
hooks()->add_action('before_project_deleted', 'wbhk_project_deleted_hook');
function wbhk_project_deleted_hook($projectId)
{
    $CI        = &get_instance();
    $tableData = $CI->projects_model->get($projectId);
    call_webhook($tableData, 'projects', 'delete', $projectId);
}
/* Projects webhooks : End */

/* Proposal webhooks : Start */
// Add new proposal
hooks()->add_action('proposal_created', 'wbhk_proposal_added_hook');
function wbhk_proposal_added_hook($proposalId)
{
    $CI        = &get_instance();
    $tableData = $CI->proposals_model->get($proposalId);
    call_webhook($tableData, 'proposals', 'add', $proposalId);
}

// Update proposal
hooks()->add_action('after_proposal_updated', 'wbhk_proposal_updated_hook');
function wbhk_proposal_updated_hook($proposalId)
{
    $CI        = &get_instance();
    $tableData = $CI->proposals_model->get($proposalId);
    call_webhook($tableData, 'proposals', 'edit', $proposalId);
}

// Delete proposal
hooks()->add_action('before_proposal_deleted', 'wbhk_proposal_deleted_hook');
function wbhk_proposal_deleted_hook($proposalId)
{
    $CI        = &get_instance();
    $tableData = $CI->proposals_model->get($proposalId);
    call_webhook($tableData, 'proposals', 'delete', $proposalId);
}

// Accept proposal
hooks()->add_action('proposal_accepted', 'wbhk_proposal_accepted_hook');
function wbhk_proposal_accepted_hook($proposalId)
{
    $CI        = &get_instance();
    $tableData = $CI->proposals_model->get($proposalId);
    call_webhook($tableData, 'proposals', 'accept', $proposalId);
}

// Decline proposal
hooks()->add_action('proposal_declined', 'wbhk_proposal_declined_hook');
function wbhk_proposal_declined_hook($proposalId)
{
    $CI        = &get_instance();
    $tableData = $CI->proposals_model->get($proposalId);
    call_webhook($tableData, 'proposals', 'decline', $proposalId);
}

// Sent proposal
hooks()->add_action('proposal_sent', 'wbhk_proposal_sent_hook');
function wbhk_proposal_sent_hook($proposalId)
{
    $CI        = &get_instance();
    $tableData = $CI->proposals_model->get($proposalId);
    call_webhook($tableData, 'proposals', 'sent', $proposalId);
}
/* Proposal webhooks : End */

/* Ticket webhooks : Start */
// Add new ticket
hooks()->add_action('ticket_created', 'wbhk_ticket_added_hook');
function wbhk_ticket_added_hook($ticketId)
{
    $CI        = &get_instance();
    $tableData = $CI->tickets_model->get($ticketId);
    call_webhook($tableData, 'ticket', 'add', $ticketId);
}

// Update ticket
hooks()->add_action('ticket_settings_updated', 'wbhk_ticket_updated_hook');
function wbhk_ticket_updated_hook($ticket)
{
    $CI        = &get_instance();
    $tableData = $CI->tickets_model->get($ticket['ticket_id']);
    call_webhook($tableData, 'ticket', 'edit', $ticket['ticket_id']);
}

// Ticket status changed
hooks()->add_action('after_ticket_status_changed', 'wbhk_ticket_status_changed_hook');
function wbhk_ticket_status_changed_hook($ticket)
{
    $CI        = &get_instance();
    $tableData = $CI->tickets_model->get($ticket['id']);
    call_webhook($tableData, 'ticket', 'status_change', $ticket['id']);
}

// Delete ticket
hooks()->add_action('before_ticket_deleted', 'wbhk_ticket_deleted_hook');
function wbhk_ticket_deleted_hook($ticketID)
{
    $CI        = &get_instance();
    $tableData = $CI->tickets_model->get($ticketID);
    call_webhook($tableData, 'ticket', 'delete', $ticketID);
}
/* Ticket webhooks : End */

/* Payment webhooks : Start */
// Add new payment
hooks()->add_action('after_payment_added', 'wbhk_payment_added_hook');
function wbhk_payment_added_hook($paymentId)
{
    $CI        = &get_instance();
    $tableData = $CI->payments_model->get($paymentId);
    call_webhook($tableData, 'invoice_payments', 'add', $tableData->invoiceid, $paymentId);
}

// Update payment
hooks()->add_action('after_payment_updated', 'wbhk_payment_updated_hook');
function wbhk_payment_updated_hook($payment)
{
    $CI        = &get_instance();
    $tableData = $CI->payments_model->get($payment['id']);
    call_webhook($tableData, 'invoice_payments', 'edit', $tableData->invoiceid, $payment['id']);
}

// Delete payment
hooks()->add_action('before_payment_deleted', 'wbhk_payment_deleted_hook');
function wbhk_payment_deleted_hook($payment)
{
    $CI        = &get_instance();
    $tableData = $CI->payments_model->get($payment['paymentid']);
    call_webhook($tableData, 'invoice_payments', 'delete', $tableData->invoiceid, $payment['paymentid']);
}
/* Payment webhooks : End */

/* Staff webhooks : Start */
// Add new staff
hooks()->add_action('staff_member_created', 'wbhk_staff_added_hook');
function wbhk_staff_added_hook($staffid)
{
    $CI        = &get_instance();
    $tableData = $CI->staff_model->get($staffid);
    call_webhook($tableData, 'staff', 'add', $staffid);
}

// Update staff
hooks()->add_action('staff_member_updated', 'wbhk_staff_updated_hook');
function wbhk_staff_updated_hook($staffid)
{
    $CI        = &get_instance();
    $tableData = $CI->staff_model->get($staffid);
    call_webhook($tableData, 'staff', 'edit', $staffid);
}

// Delete staff
hooks()->add_action('before_delete_staff_member', 'wbhk_staff_deleted_hook');
function wbhk_staff_deleted_hook($staff)
{
    $CI        = &get_instance();
    $tableData = $CI->staff_model->get($staff['id']);
    call_webhook($tableData, 'staff', 'delete', $staff['id']);
}
/* Staff webhooks : End */

/* Contracts webhooks : Start */
// Add new contract
hooks()->add_action('after_contract_added', 'wbhk_contract_added_hook');
function wbhk_contract_added_hook($contractID)
{
    $CI        = &get_instance();
    $tableData = $CI->contracts_model->get($contractID);
    call_webhook($tableData, 'contract', 'add', $contractID);
}

// Update contract
hooks()->add_action('after_contract_updated', 'wbhk_contract_updated_hook');
function wbhk_contract_updated_hook($contractID)
{
    $CI        = &get_instance();
    $tableData = $CI->contracts_model->get($contractID);
    call_webhook($tableData, 'contract', 'edit', $contractID);
}

// Delete contract
hooks()->add_action('before_contract_deleted', 'wbhk_contract_deleted_hook');
function wbhk_contract_deleted_hook($contractID)
{
    $CI        = &get_instance();
    $tableData = $CI->contracts_model->get($contractID);
    call_webhook($tableData, 'contract', 'delete', $contractID);
}
/* Contracts webhooks : End */

/* Estimates webhooks : Start */
// Add new estimate
hooks()->add_action('after_estimate_added', 'wbhk_estimate_added_hook');
function wbhk_estimate_added_hook($estimateID)
{
    $CI        = &get_instance();
    $tableData = $CI->estimates_model->get($estimateID);
    call_webhook($tableData, 'estimate', 'add', $estimateID);
}

// Update estimate
hooks()->add_action('after_estimate_updated', 'wbhk_estimate_updated_hook');
function wbhk_estimate_updated_hook($estimateID)
{
    $CI        = &get_instance();
    $tableData = $CI->estimates_model->get($estimateID);
    call_webhook($tableData, 'estimate', 'edit', $estimateID);
}

// Delete estimate
hooks()->add_action('before_estimate_deleted', 'wbhk_estimate_deleted_hook');
function wbhk_estimate_deleted_hook($estimateID)
{
    $CI        = &get_instance();
    $tableData = $CI->estimates_model->get($estimateID);
    call_webhook($tableData, 'estimate', 'delete', $estimateID);
}
/* Estimates webhooks : End */
// Update calendar event
hooks()->add_filter('event_update_data', 'wbhk_calendar_event_updated_hook', 0, 2);
function wbhk_calendar_event_updated_hook($eventData, $eventID)
{
    $CI        = &get_instance();
    $eventData['end'] = $eventData['end'] ?? "";
    call_webhook((object)$eventData, 'event', 'edit', $eventID);
    return $eventData;
}
/* Calendar event webhooks : End */

hooks()->add_filter("staff_merge_fields", function ($fields, $data) {
    $fields['{staff_phonenumber}'] = $data['staff']->phonenumber;
    return $fields;
}, 10, 2);

hooks()->add_filter("available_merge_fields", function ($available) {
    $i = 0;
    foreach ($available as $fields) {
        $f = 0;
        // Fix for merge fields as custom fields not matching the names
        foreach ($fields as $key => $_fields) {
            if ($key == "staff") {
                $format = [
                    'base_name' => "staff_merge_fields",
                    'file'      => "merge_fields/staff_merge_fields",
                ];
                array_push($available[$i][$key], [
                    'name'      => "Staff Phone",
                    'key'       => '{staff_phonenumber}',
                    'available' => $available[$i][$key][$f]['available'],
                    'format'    => $format,
                ]);
            }
            if ($key == "client") {

                $format = [
                    'base_name' => "client_merge_fields",
                    'file'      => "merge_fields/client_merge_fields",
                ];

                $custom_fields = get_custom_fields("contacts", [], true);

                foreach ($custom_fields as $field) {
                    array_push($available[$i][$key], [
                        'name'      => $field['name'],
                        'key'       => '{' . $field['slug'] . '}',
                        'available' => $available[$i][$key][$f]['available'],
                        'format'    => $format,
                    ]);
                }
            }
            $f++;
        }
        $i++;
    }

    return $available;
});

hooks()->add_action('before_start_render_dashboard_content', function(){
    $CI        = &get_instance();
    if(get_option("webhook_cron_has_run_from_cli") == 0 && !empty($CI->db->count_all_results("scheduled_webhooks"))){
        $html = '<div class="col-md-12"><div class="alert alert-warning" font-medium>';
        $html .= 'You\'ve added webhook with scheduled time that requires cron job setup to work properly.';
        $html .= '<br />Please follow the cron <a href="https://help.perfexcrm.com/setup-cron-job/" target="_blank">setup guide</a> in order all features to work well.';
        $html .= '</div></div>';
    }

    echo $html ?? '';
});
