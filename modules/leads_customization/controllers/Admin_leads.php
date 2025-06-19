<?php
use app\services\imap\Imap;
use app\services\LeadProfileBadges;
use app\services\leads\LeadsKanban;
use app\services\imap\ConnectionErrorException;
use Ddeboer\Imap\Exception\MailboxDoesNotExistException;
include_once __DIR__.'/../../../application/controllers/admin/Leads.php';
class Admin_leads extends Leads
{
    public function switch_kanban($set = 0)
    {
        if ($set == 1) {
            $set = 'false';
        } else {
            $set = 'false';
        }
        $this->session->set_userdata([
            'leads_kanban_view' => $set,
        ]);
        redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
    }

    public function index($id = '')
    {
        close_setup_menu();

        if (!is_staff_member()) {
            access_denied('Leads');
        }

        $data['switch_kanban'] = false;

//        if ($this->session->userdata('leads_kanban_view') == 'true') {
//            $data['switch_kanban'] = false;
//            $data['bodyclass']     = 'kan-ban-body';
//        }

        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $data['staff'] = hooks()->apply_filters('leads_manager_staff_data', $data['staff']);
        if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
            $this->load->model('gdpr_model');
            $data['consent_purposes'] = $this->gdpr_model->get_consent_purposes();
        }
        //$data['summary']  = get_leads_summary();
        $data['summary']  = [];
        $data['statuses'] = $this->leads_model->get_status();
        $data['sources']  = $this->leads_model->get_source();
        $this->load->model('leads_customization_model');
        $data['services']  =  $this->leads_customization_model->get_services();
        $data['languages']  =  $this->leads_customization_model->get_languages();
        $data['title']    = _l('leads');
        // in case accesed the url leads/index/ directly with id - used in search
        $data['leadid']   = $id;
        $data['isKanBan'] = false;

        $this->load->view('leads_customization/admin_leads/manage_leads', $data);
    }

    public function table()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }
        $this->app->get_table_data(module_views_path('leads_customization', 'admin_leads/table'));
    }

    public function lead($id = '')
    {
        if (!is_staff_member() || ($id != '' && !$this->leads_model->staff_can_access_lead($id))) {
            ajax_access_denied();
        }

        if ($this->input->post()) {
            if ($id == '') {
                $id      = $this->leads_model->add($this->input->post());
                $message = $id ? _l('added_successfully', _l('lead')) : '';

                echo json_encode([
                    'success'  => $id ? true : false,
                    'id'       => $id,
                    'message'  => $message,
                    'leadView' => $id ? $this->_get_lead_data($id) : [],
                ]);
            } else {
                $emailOriginal   = $this->db->select('email')->where('id', $id)->get(db_prefix() . 'leads')->row()->email;
                $proposalWarning = false;
                $message         = '';
                $success         = $this->leads_model->update($this->input->post(), $id);

                if ($success) {
                    $emailNow = $this->db->select('email')->where('id', $id)->get(db_prefix() . 'leads')->row()->email;

                    $proposalWarning = (total_rows(db_prefix() . 'proposals', [
                            'rel_type' => 'lead',
                            'rel_id'   => $id, ]) > 0 && ($emailOriginal != $emailNow) && $emailNow != '') ? true : false;

                    $message = _l('updated_successfully', _l('lead'));
                }
                echo json_encode([
                    'success'          => $success,
                    'message'          => $message,
                    'id'               => $id,
                    'proposal_warning' => $proposalWarning,
                    'leadView'         => $this->_get_lead_data($id),
                ]);
            }
            die;
        }

        echo json_encode([
            'leadView' => $this->_get_lead_data($id),
        ]);
    }

    private function _get_lead_data($id = '')
    {
        $reminder_data         = '';
        $data['lead_locked']   = false;
        $data['openEdit']      = $this->input->get('edit') ? true : false;
        $data['members']       = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);
        $data['status_id']     = $this->input->get('status_id') ? $this->input->get('status_id') : get_option('leads_default_status');
        $data['base_currency'] = get_base_currency();

        if (is_numeric($id)) {
            $leadWhere = (has_permission('leads', '', 'view') ? [] : '(assigned = ' . get_staff_user_id() . ' OR addedfrom=' . get_staff_user_id() . ' OR is_public=1)');
            $this->load->model('leads_customization_model');
            $lead = $this->leads_customization_model->getLead($id, $leadWhere);

            if (!$lead) {
                header('HTTP/1.0 404 Not Found');
                echo _l('lead_not_found');
                die;
            }

            if (total_rows(db_prefix() . 'clients', ['leadid' => $id ]) > 0) {
                $data['lead_locked'] = ((!is_admin() && get_option('lead_lock_after_convert_to_customer') == 1) ? true : false);
            }

            $reminder_data = $this->load->view('admin/includes/modals/reminder', [
                'id'             => $lead->id,
                'name'           => 'lead',
                'members'        => $data['members'],
                'reminder_title' => _l('lead_set_reminder_title'),
            ], true);

            $data['lead']          = $lead;
            $data['mail_activity'] = $this->leads_model->get_mail_activity($id);
            $data['notes']         = $this->misc_model->get_notes($id, 'lead');
            $data['activity_log']  = $this->leads_model->get_lead_activity_log($id);

            if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
                $this->load->model('gdpr_model');
                $data['purposes'] = $this->gdpr_model->get_consent_purposes($lead->id, 'lead');
                $data['consents'] = $this->gdpr_model->get_consents(['lead_id' => $lead->id]);
            }

            $leadProfileBadges         = new LeadProfileBadges($id);
            $data['total_reminders']   = $leadProfileBadges->getCount('reminders');
            $data['total_notes']       = $leadProfileBadges->getCount('notes');
            $data['total_attachments'] = $leadProfileBadges->getCount('attachments');
            $data['total_tasks']       = $leadProfileBadges->getCount('tasks');
            $data['total_proposals']   = $leadProfileBadges->getCount('proposals');
        }

        $this->load->model('leads_customization_model');
        $data['statuses'] = $this->leads_model->get_status();
        $data['sources']  = $this->leads_model->get_source();
        $data['services']  =  $this->leads_customization_model->get_services();
        $data['languages']  =  $this->leads_customization_model->get_languages();
        $data = hooks()->apply_filters('lead_view_data', $data);

        $data['members']       = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        return [
            'data'          => $this->load->view('leads_customization/admin_leads/lead', $data, true),
            'reminder_data' => $reminder_data,
        ];
    }

    public function service(){
        $this->load->model('leads_customization_model');
        if ($this->input->post()){
            $data = $this->input->post();
            unset($data['inline']);
            $id = $this->leads_customization_model->services_crud('add', $data);
            if ($id) {
                echo json_encode(['success' => $id ? true : false, 'id' => $id]);
            }
        }
    }

    public function language(){
        $this->load->model('leads_customization_model');
        if ($this->input->post()){
            $data = $this->input->post();
            unset($data['inline']);
            $id = $this->leads_customization_model->languages_crud('add', $data);
            if ($id) {
                echo json_encode(['success' => $id ? true : false, 'id' => $id]);
            }
        }
    }

    public function bulk_action()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }

        hooks()->do_action('before_do_bulk_action_for_leads');
        $total_deleted = 0;
        if ($this->input->post()) {
            $ids                   = $this->input->post('ids');
            $status                = $this->input->post('status');
            $source                = $this->input->post('source');
            $service               = $this->input->post('service');
            $language              = $this->input->post('language');
            $assigned              = $this->input->post('assigned');
            $visibility            = $this->input->post('visibility');
            $tags                  = $this->input->post('tags');
            $last_contact          = $this->input->post('last_contact');
            $lost                  = $this->input->post('lost');
            $has_permission_delete = has_permission('leads', '', 'delete');
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->input->post('mass_delete')) {
                        if ($has_permission_delete) {
                            if ($this->leads_model->delete($id)) {
                                $total_deleted++;
                            }
                        }
                    } else {
                        if ($status || $source || $assigned || $last_contact || $visibility || $service || $language) {
                            $update = [];
                            $log_message = '';
                            $not_additional_data = [];
                            if ($status) {
                                // We will use the same function to update the status
                                $this->leads_model->update_lead_status([
                                    'status' => $status,
                                    'leadid' => $id,
                                ]);
                                $log_message = 'not_lead_activity_status_updated';
                            }
                            if ($source) {
                                $update['source'] = $source;
                            }
                            if ($service) {
                                $update['service'] = $service;
                            }
                            if ($language) {
                                $update['language'] = $language;
                            }
                            if ($source) {
                                $update['source'] = $source;
                            }
                            if ($assigned) {
                                $update['assigned'] = $assigned;
                                $not_additional_data = [
                                    get_staff_full_name(),
                                    '<a href="' . admin_url('profile/' . $assigned) . '" target="_blank">' . get_staff_full_name($assigned) . '</a>',
                                ];
                                $log_message = 'not_lead_activity_assigned_to';
                            }
                            if ($last_contact) {
                                $last_contact          = to_sql_date($last_contact, true);
                                $update['lastcontact'] = $last_contact;
                            }

                            if ($visibility) {
                                if ($visibility == 'public') {
                                    $update['is_public'] = 1;
                                } else {
                                    $update['is_public'] = 0;
                                }
                            }

                            if (count($update) > 0) {
                                $this->db->where('id', $id);
                                $this->db->update(db_prefix() . 'leads', $update);
                                $this->log_lead_activity($id, $log_message, false, serialize($not_additional_data));
                            }
                        }
                        if ($tags) {
                            handle_tags_save($tags, $id, 'lead');
                        }//277414
                        if ($lost == 'true') {
                            $this->leads_model->mark_as_lost($id);
                        }
                    }
                }
            }
        }

        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('total_leads_deleted', $total_deleted));
        }
    }

    public function log_lead_activity($id, $description, $integration = false, $additional_data = '')
    {
        $log = [
            'date'            => date('Y-m-d H:i:s'),
            'description'     => $description,
            'leadid'          => $id,
            'staffid'         => get_staff_user_id(),
            'additional_data' => $additional_data,
            'full_name'       => get_staff_full_name(get_staff_user_id()),
        ];
        if ($integration == true) {
            $log['staffid']   = 0;
            $log['full_name'] = '[CRON]';
        }

        $this->db->insert(db_prefix() . 'lead_activity_log', $log);

        return $this->db->insert_id();
    }

    public function import()
    {
        if (!is_admin() && get_option('allow_non_admin_members_to_import_leads') != '1') {
            access_denied('Leads Import');
        }

        $dbFields = $this->db->list_fields(db_prefix() . 'leads');
        array_push($dbFields, 'tags');

        $this->load->library('leads_customization/import_leads', [], 'import');
        $this->import->setDatabaseFields($dbFields)
            ->setCustomFields(get_custom_fields('leads'));

        if ($this->input->post('download_sample') === 'true') {
            $this->import->downloadSample();
        }

        if ($this->input->post()
            && isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != '') {
            $this->import->setSimulation($this->input->post('simulate'))
                ->setTemporaryFileLocation($_FILES['file_csv']['tmp_name'])
                ->setFilename($_FILES['file_csv']['name'])
                ->perform();

            $data['total_rows_post'] = $this->import->totalRows();

            if (!$this->import->isSimulation()) {
                set_alert('success', _l('import_total_imported', $this->import->totalImported()));
            }
        }

        $data['statuses'] = $this->leads_model->get_status();
        $data['sources']  = $this->leads_model->get_source();
        $data['members']  = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        $data['title'] = _l('import');
        $this->load->view('leads_customization/admin_leads/import', $data);
    }

    public function validate_unique_field()
    {
        if ($this->input->post()) {
            // First we need to check if the field is the same
            $lead_id = $this->input->post('lead_id');
            $field   = $this->input->post('field');
            $value   = $this->input->post($field);

            if ($lead_id != '') {
                $this->db->select($field);
                $this->db->where('id', $lead_id);
                $row = $this->db->get(db_prefix() . 'leads')->row();

                if ($field === 'whatsapp_number') {
                    if (substr($row->whatsapp_number, -9) == substr($value, -9)) {
                        echo json_encode(true);
                        die();
                    }
                } else if ($field === 'phonenumber') {
                    if (substr($row->phonenumber, -9) == substr($value, -9)) {
                        echo json_encode(true);
                        die();
                    }
                }else {
                    if ($row->{$field} == $value) {
                        echo json_encode(true);
                        die();
                    }
                }
            }

            $this->db->from(db_prefix() . 'leads');

            if ($field === 'whatsapp_number' || $field === 'phonenumber') {
                // Apply OR condition for 'whatsapp_number' and 'phonenumber', checking the last 9 digits
                $this->db->group_start();
                $this->db->where("SUBSTRING(whatsapp_number, -9) =", substr($value, -9));
                $this->db->or_where("SUBSTRING(phonenumber, -9) =", substr($value, -9));
                $this->db->group_end();
            } else {
                // Apply direct WHERE condition for other fields
                $this->db->where($field, $value);
            }

            // Get the count of rows
            $total_rows = $this->db->count_all_results();

            // Return 'false' if any matching rows are found, otherwise 'true'
            echo $total_rows > 0 ? 'false' : 'true';

        }
    }

}