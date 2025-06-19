<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Api_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->services = $this->db->select('*')->from(db_prefix().'leads_services')->get()->result_array();
        $this->languages = $this->db->select('*')->from(db_prefix().'leads_languages')->get()->result_array();
    }

    public function get_table($name, $id)
    {
        // \modules\api\core\Apiinit::check_url('api');
        switch ($name) {
            case 'projects':
                $this->load->model('Projects_model');

                return $this->Projects_model->get($id);
                break;
            case 'tasks':
                $this->load->model('Tasks_model');

                return $this->Tasks_model->get($id);
                break;
            case 'staffs':
                $this->load->model('Staff_model');

                return $this->Staff_model->get($id);
                break;
            case 'tickets':
                $this->load->model('Tickets_model');

                return $this->Tickets_model->get($id);
                break;
            case 'leads':
                $this->load->model('Leads_model');

                return $this->Leads_model->get($id);
                break;
            case 'clients':
                $this->load->model('Clients_model');

                return $this->Clients_model->get($id);
                break;
            case 'contracts':
                $this->load->model('Contracts_model');

                return $this->Contracts_model->get($id);
                break;
            case 'invoices':
                $this->load->model('Invoices_model');
                $data = $this->Invoices_model->get($id);
                if (!empty($data) && !empty($id)) {
                    $data->items = $this->get_api_custom_data($data->items, 'items', '', true);
                }

                return $data;
                break;
            case 'estimates':
                $this->load->model('Estimates_model');
                $data = $this->Estimates_model->get($id);
                if (!empty($data) && !empty($id)) {
                    $data->items = $this->get_api_custom_data($data->items, 'items', '', true);
                }

                return $data;
                break;
            case 'departments':
                $this->load->model('Departments_model');

                return $this->Departments_model->get($id);
                break;
            case 'payments':
                $this->load->model('Payments_model');

                return $this->Payments_model->get($id);
                break;
            case 'roles':
                $this->load->model('Roles_model');

                return $this->Roles_model->get($id);
                break;
            case 'proposals':
                $this->load->model('Proposals_model');
                $data = $this->Proposals_model->get($id);
                if (!empty($data) && !empty($id)) {
                    $data->items = $this->get_api_custom_data($data->items, 'items', '', true);
                }

                return $data;
                break;
            case 'knowledge':
                $this->load->model('Knowledge_base_model');

                return $this->Knowledge_base_model->get($id);
                break;
            case 'goals':
                $this->load->model('Goals_model');

                return $this->Goals_model->get($id);
                break;
            case 'currencies':
                $this->load->model('Currencies_model');

                return $this->Currencies_model->get($id);
                break;
            case 'annex':
                $this->load->model('Annex_model');

                return $this->Annex_model->get($id);
                break;
            case 'contacts':
                $this->load->model('Clients_model');

                return $this->clients_model->get_contact($id);
                break;
            case 'all_contacts':
                $this->load->model('Clients_model');

                return $this->clients_model->get_contacts($id);
                break;
            case 'invoices':
                $this->load->model('invoices_model');

                return $this->invoices_model->get($id);
                break;
            case 'invoice_items':
                $this->load->model('invoice_items_model');

                return $this->invoice_items_model->get($id);
                break;
            case 'milestones':
                return $this->get_milestones_api($id);
                break;
            case 'expenses':
                return $this->get_expenses_api($id);
                break;
            case 'creditnotes':
                $this->load->model('Credit_notes_model');
                $data = $this->Credit_notes_model->get($id);
                if (!empty($data) && !empty($id)) {
                    $data->items = $this->get_api_custom_data($data->items,"items", '', true);
                }
                return $data;
                break;
            default:
                return '';
                break;
        }
    }

    public function value($value)
    {
        if ($value) {
            return $value;
        }

        return '';
    }

    public function matchService($val){
        foreach ($this->services as $service) {
            if (strtolower(trim($service['name'])) == strtolower(trim($val)) || strtolower(trim($service['id'])) == strtolower(trim($val))) {
                return $service['id'];
            }
        }
    }

    public function matchLanguage($val){
        foreach ($this->languages as $language) {
            if (strtolower(trim($language['name'])) == strtolower(trim($val)) || strtolower(trim($language['id'])) == strtolower(trim($val))) {
                return $language['id'];
            }
        }
    }

    public function search($type, $key)
    {
        // \modules\api\core\Apiinit::check_url('api');

        return $this->get_relation_data_api($type, $key);
    }

    public function _search_tickets($q, $limit = 0, $api = false)
    {
        $fields = get_custom_fields('tickets');
        $result = [
            'result'         => [],
            'type'           => 'tickets',
            'search_heading' => _l('support_tickets'),
        ];

        if (is_staff_member() || (!is_staff_member() && 1 == get_option('access_tickets_to_none_staff_members')) || true == $api) {
            $is_admin = is_admin();

            $where = '';
            if (!$is_admin && 1 == get_option('staff_access_only_assigned_departments') && false == $api) {
                $this->load->model('departments_model');
                $staff_deparments_ids = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
                $departments_ids      = [];
                if (0 == count($staff_deparments_ids)) {
                    $departments = $this->departments_model->get();
                    foreach ($departments as $department) {
                        array_push($departments_ids, $department['departmentid']);
                    }
                } else {
                    $departments_ids = $staff_deparments_ids;
                }
                if (count($departments_ids) > 0) {
                    $where = 'department IN (SELECT departmentid FROM tblstaffdepartments WHERE departmentid IN ('.implode(',', $departments_ids).') AND staffid="'.get_staff_user_id().'")';
                }
            }

            $this->db->select();
            $this->db->from('tbltickets');
            $this->db->join('tbldepartments', 'tbldepartments.departmentid = tbltickets.department');
            $this->db->join('tblclients', 'tblclients.userid = tbltickets.userid', 'left');
            $this->db->join('tblcontacts', 'tblcontacts.id = tbltickets.contactid', 'left');

            if (!_startsWith($q, '#')) {
                $where_string = '';
                foreach ($fields as $key => $value) {
                    $this->db->join(db_prefix().'customfieldsvalues as ctable_'.$key.'', db_prefix().'tickets.ticketid = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="tickets" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                    $where_string .= ' OR ctable_'.$key.'.value LIKE "%'.$q.'%"';
                }
                $this->db->where('(
                    ticketid LIKE "'.$q.'%"
                    OR subject LIKE "%'.$q.'%"
                    OR message LIKE "%'.$q.'%"
                    OR tblcontacts.email LIKE "%'.$q.'%"
                    OR CONCAT(firstname, \' \', lastname) LIKE "%'.$q.'%"
                    OR company LIKE "%'.$q.'%"
                    OR vat LIKE "%'.$q.'%"
                    OR tblcontacts.phonenumber LIKE "%'.$q.'%"
                    OR tblclients.phonenumber LIKE "%'.$q.'%"
                    OR city LIKE "%'.$q.'%"
                    OR state LIKE "%'.$q.'%"
                    OR address LIKE "%'.$q.'%"
                    OR tbldepartments.name LIKE "%'.$q.'%"
                    '.$where_string.'
                    )');

                if ('' != $where) {
                    $this->db->where($where);
                }
            } else {
                $this->db->where('ticketid IN
                    (SELECT rel_id FROM tbltags_in WHERE tag_id IN
                    (SELECT id FROM tbltags WHERE name="'.strafter($q, '#').'")
                    AND tbltags_in.rel_type=\'ticket\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
                    ');
            }

            if (0 != $limit) {
                $this->db->limit($limit);
            }
            $this->db->order_by('ticketid', 'DESC');
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function _search_leads($q, $limit = 0, $where = [], $api = false)
    {
        $fields = get_custom_fields('leads');
        $result = [
            'result'         => [],
            'type'           => 'leads',
            'search_heading' => _l('leads'),
        ];

        $has_permission_view = has_permission('leads', '', 'view');
        if (is_staff_member() || true == $api) {
            // Leads
            $this->db->select('tblleads.*');
            $this->db->from('tblleads');

            if (!$has_permission_view && false == $api) {
                $this->db->where('(assigned = '.get_staff_user_id().' OR addedfrom = '.get_staff_user_id().' OR is_public=1)');
            }

            if (!_startsWith($q, '#')) {
                $where_string = '';
                foreach ($fields as $key => $value) {
                    $this->db->join(db_prefix().'customfieldsvalues as ctable_'.$key.'', db_prefix().'leads.id = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="leads" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                    $where_string .= ' OR ctable_'.$key.'.value LIKE "%'.$q.'%"';
                }
                $this->db->where('(name LIKE "%'.$q.'%"
                    OR title LIKE "%'.$q.'%"
                    OR company LIKE "%'.$q.'%"
                    OR zip LIKE "%'.$q.'%"
                    OR city LIKE "%'.$q.'%"
                    OR state LIKE "%'.$q.'%"
                    OR address LIKE "%'.$q.'%"
                    OR email LIKE "%'.$q.'%"
                    OR phonenumber LIKE "%'.$q.'%"
                    '.$where_string.'
                    )');
            } else {
                $this->db->where('id IN
                    (SELECT rel_id FROM tbltags_in WHERE tag_id IN
                    (SELECT id FROM tbltags WHERE name="'.strafter($q, '#').'")
                    AND tbltags_in.rel_type=\'lead\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
                    ');
            }

            $this->db->where('client_id < 1');

            if (0 != $limit) {
                $this->db->limit($limit);
            }
            $this->db->order_by('name', 'ASC');
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function _search_invoices($q, $limit = 0, $where = [], $api = false)
    {
        $fields = get_custom_fields('invoice');
        $result = [
            'result'         => [],
            'type'           => 'invoices',
            'search_heading' => _l('invoices'),
        ];
        $has_permission_view_invoices     = has_permission('invoices', '', 'view');
        $has_permission_view_invoices_own = has_permission('invoices', '', 'view_own');

        if ($has_permission_view_invoices || $has_permission_view_invoices_own || '1' == get_option('allow_staff_view_invoices_assigned') || true == $api) {
            if (is_numeric($q)) {
                $q = trim($q);
                $q = ltrim($q, '0');
            } elseif (startsWith($q, get_option('invoice_prefix'))) {
                $q = strafter($q, get_option('invoice_prefix'));
                $q = trim($q);
                $q = ltrim($q, '0');
            }
            $invoice_fields    = prefixed_table_fields_array(db_prefix().'invoices');
            $clients_fields    = prefixed_table_fields_array(db_prefix().'clients');
            // Invoices
            $this->db->select(implode(',', $invoice_fields).','.implode(',', $clients_fields).','.db_prefix().'invoices.id as invoiceid,'.get_sql_select_client_company());
            $this->db->from(db_prefix().'invoices');
            $this->db->join(db_prefix().'clients', db_prefix().'clients.userid = '.db_prefix().'invoices.clientid', 'left');
            $this->db->join(db_prefix().'currencies', db_prefix().'currencies.id = '.db_prefix().'invoices.currency');
            $this->db->join(db_prefix().'contacts', db_prefix().'contacts.userid = '.db_prefix().'clients.userid AND is_primary = 1', 'left');

            if (!startsWith($q, '#')) {
                $where_string = '';
                foreach ($fields as $key => $value) {
                    $this->db->join(db_prefix().'customfieldsvalues as ctable_'.$key.'', db_prefix().'invoices.id = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="invoice" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                    $where_string .= ' OR ctable_'.$key.'.value LIKE "%'.$q.'%"';
                }
                $this->db->where('(
                '.db_prefix().'invoices.number LIKE "'.$this->db->escape_like_str($q).'"
                OR
                '.db_prefix().'clients.company LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'invoices.clientnote LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.vat LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.phonenumber LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.city LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.state LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.zip LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.address LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'invoices.adminnote LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                CONCAT(firstname,\' \',lastname) LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'invoices.billing_street LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'invoices.billing_city LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'invoices.billing_state LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'invoices.billing_zip LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'invoices.shipping_street LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'invoices.shipping_city LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'invoices.shipping_state LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'invoices.shipping_zip LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.billing_street LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.billing_city LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.billing_state LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.billing_zip LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.shipping_street LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.shipping_city LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.shipping_state LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.shipping_zip LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                '.$where_string.'
                )');
            } else {
                $this->db->where(db_prefix().'invoices.id IN
                (SELECT rel_id FROM '.db_prefix().'taggables WHERE tag_id IN
                (SELECT id FROM '.db_prefix().'tags WHERE name="'.$this->db->escape_str(strafter($q, '#')).'")
                AND '.db_prefix().'taggables.rel_type=\'invoice\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
                ');
            }

            $this->db->order_by('number,YEAR(date)', 'desc');
            if (0 != $limit) {
                $this->db->limit($limit);
            }

            $result['result'] = $this->db->get()->result_array();
            // echo $this->db->last_query();
        }

        return $result;
    }

    public function _search_projects($q, $limit = 0, $where = false, $rel_type = null, $api = false)
    {
        $fields = get_custom_fields('projects');
        $result = [
            'result'         => [],
            'type'           => 'projects',
            'search_heading' => _l('projects'),
        ];

        $projects = has_permission('projects', '', 'view');
        // Projects
        $this->db->select('tblprojects.*');
        $this->db->from('tblprojects');
        if (isset($rel_type) && 'lead' == $rel_type) {
            $this->db->join('tblleads', 'tblleads.id = tblprojects.clientid');
        } else {
            $this->db->join('tblclients', 'tblclients.userid = tblprojects.clientid', 'LEFT');
            $this->db->join('tblleads', 'tblleads.id = tblprojects.clientid', 'LEFT');
        }

        if (!$projects && false == $api) {
            $this->db->where('tblprojects.id IN (SELECT project_id FROM tblprojectmembers WHERE staff_id='.get_staff_user_id().')');
        }
        if (false != $where) {
            $this->db->where($where);
        }
        if (!_startsWith($q, '#')) {
            $where_string = '';
            foreach ($fields as $key => $value) {
                $this->db->join(db_prefix().'customfieldsvalues as ctable_'.$key.'', db_prefix().'projects.id = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="projects" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                $where_string .= ' OR ctable_'.$key.'.value LIKE "%'.$q.'%"';
            }
            $this->db->where('(tblleads.company LIKE "%'.$q.'%"
                OR tblprojects.description LIKE "%'.$q.'%"
                OR tblprojects.name LIKE "%'.$q.'%"
                
                OR tblleads.phonenumber LIKE "%'.$q.'%"
                OR tblleads.city LIKE "%'.$q.'%"
                OR tblleads.zip LIKE "%'.$q.'%"
                OR tblleads.state LIKE "%'.$q.'%"
                OR tblleads.zip LIKE "%'.$q.'%"
                OR tblleads.address LIKE "%'.$q.'%"
                '.$where_string.'
                )');
        } else {
            $this->db->where('id IN
                (SELECT rel_id FROM tbltags_in WHERE tag_id IN
                (SELECT id FROM tbltags WHERE name="'.strafter($q, '#').'")
                AND tbltags_in.rel_type=\'project\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
                ');
        }

        if (0 != $limit) {
            $this->db->limit($limit);
        }

        $this->db->order_by(db_prefix().'projects.name', 'ASC');
        $result['result'] = $this->db->get()->result_array();

        return $result;
    }

    public function _search_staff($q, $limit = 0, $api = false)
    {
        $result = [
            'result'         => [],
            'type'           => 'staff',
            'search_heading' => _l('staff_members'),
        ];

        if (has_permission('staff', '', 'view') || true == $api) {
            // Staff
            $fields = get_custom_fields('staff');
            $this->db->select('staff.*');
            $this->db->from(db_prefix().'staff');
            $this->db->like('firstname', $q);
            $this->db->or_like('lastname', $q);
            $this->db->or_like("CONCAT(firstname, ' ', lastname)", $q, false);
            $this->db->or_like('facebook', $q);
            $this->db->or_like('linkedin', $q);
            $this->db->or_like('phonenumber', $q);
            $this->db->or_like('email', $q);
            $this->db->or_like('skype', $q);
            foreach ($fields as $key => $value) {
                $this->db->join(db_prefix().'customfieldsvalues as ctable_'.$key.'', db_prefix().'staff.staffid = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="staff" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                $this->db->or_like('ctable_'.$key.'.value', $q);
            }

            if (0 != $limit) {
                $this->db->limit($limit);
            }
            $this->db->order_by('firstname', 'ASC');
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function _search_tasks($q, $limit = 0, $api = false)
    {
        $result = [
            'result'         => [],
            'type'           => 'tasks',
            'search_heading' => _l('tasks'),
        ];

        if (has_permission('tasks', '', 'view') || true == $api) {
            // task
            $fields = get_custom_fields('tasks');
            $this->db->select(db_prefix().'tasks.*');
            $this->db->from(db_prefix().'tasks');
            $this->db->like('name', $q);
            $this->db->or_like(db_prefix().'tasks.id', $q);
            foreach ($fields as $key => $value) {
                $this->db->join(db_prefix().'customfieldsvalues as ctable_'.$key.'', db_prefix().'tasks.id = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="tasks" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                $this->db->or_like('ctable_'.$key.'.value', $q);
            }

            if (0 != $limit) {
                $this->db->limit($limit);
            }
            $this->db->order_by('name', 'ASC');
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function get_user($id = '')
    {
        $this->db->select('*');
        if ('' != $id) {
            $this->db->where('id', $id);
        }

        return $this->db->get(db_prefix().'user_api')->result_array();
    }

    public function add_user($data)
    {
        $payload = [
            'user' => $data['user'],
            'name' => $data['name'],
        ];
        // Load Authorization Library or Load in autoload config file
        $this->load->library('Authorization_Token');
        // generate a token
        $data['token'] = $this->authorization_token->generateToken($payload);
        $today         = date('Y-m-d H:i:s');

        $data['expiration_date'] = to_sql_date($data['expiration_date'], true);
        $this->db->insert(db_prefix().'user_api', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New User Added [ID: '.$insert_id.', Name: '.$data['name'].']');
        }

        return $insert_id;
    }

    public function update_user($data, $id)
    {
        $data['expiration_date'] = to_sql_date($data['expiration_date'], true);
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'user_api', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Ticket User Updated [ID: '.$id.' Name: '.$data['name'].']');

            return true;
        }

        return false;
    }

    public function delete_user($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'user_api');
        if ($this->db->affected_rows() > 0) {
            log_activity('User Deleted [ID: '.$id.']');

            return true;
        }

        return false;
    }

    public function check_token($token)
    {
        $this->db->where('token', $token);
        $user = $this->db->get(db_prefix().'user_api')->row();
        if (isset($user)) {
            return true;
        }

        return false;
    }

    public function user_api_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $lead_id          = $this->input->post('lead_id');
                $abbreviated_name = strtoupper($this->input->post('abbreviated_name'));
                if ('' != $lead_id) {
                    $this->db->where('id', $lead_id);
                    $_current_email = $this->db->get('tblleads')->row();
                    if ($_current_email->abbreviated_name == $abbreviated_name) {
                        echo json_encode(true);
                        die();
                    }
                }
                $result_lead   = true;
                $result_client = true;
                $client_id     = $this->input->post('client_id');
                $this->db->where('abbreviated_name', $abbreviated_name);
                if ('' != $client_id) {
                    $arr_id   = [];
                    $arr_id[] = $client_id;
                    $this->db->where_not_in('client_id', $arr_id);
                }

                $total_rows = $this->db->count_all_results('tblleads');

                if ($total_rows > 0) {
                    $result_lead = false;
                } else {
                    $result_lead = true;
                }
                $this->db->where('abbreviated_name', $abbreviated_name);
                if ('' != $client_id) {
                    $arr_id   = [];
                    $arr_id[] = $client_id;
                    $this->db->where_not_in('userid', $arr_id);
                }
                $total_rows = $this->db->count_all_results('tblclients');
                if ($total_rows > 0) {
                    $result_client = false;
                } else {
                    $result_client = true;
                }
                if ($result_lead && $result_client) {
                    echo json_encode(true);
                } else {
                    echo json_encode(false);
                }
                die();
            }
        }
    }

    public function get_relation_data_api($type, $search = '')
    {
        // \modules\api\core\Apiinit::check_url('api');
        $q  = '';
        if ('' != $search) {
            $q = $search;
            $q = trim(urldecode($q));
        }
        $data = [];
        if ('customer' == $type || 'customers' == $type) {
            $where_clients = 'tblclients.active=1';

            if ($q) {
                $where_clients .= ' AND (';
                $where_clients .= 'company LIKE "%'.$q.'%" OR CONCAT(firstname, " ", lastname) LIKE "%'.$q.'%" OR email LIKE "%'.$q.'%"';

                $fields = get_custom_fields('customers');
                foreach ($fields as $key => $value) {
                    $this->db->join(db_prefix().'customfieldsvalues as ctable_'.$key.'', db_prefix().'clients.userid = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="customers" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                    $where_clients .= ' OR ctable_'.$key.'.value LIKE "%'.$q.'%"';
                }
                $where_clients .= ')';
            }
            $this->load->model('clients_model');
            $data = $this->clients_model->get('', $where_clients);
        } elseif ('contacts' == $type) {
            $where_clients = 'tblclients.active=1';
            if ($q) {
                $where_clients .= ' AND (';
                $where_clients .= ' company LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\' OR CONCAT(firstname, " ", lastname) LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\' OR email LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'';

                $fields = get_custom_fields('contacts');
                foreach ($fields as $key => $value) {
                    $this->db->join(db_prefix().'customfieldsvalues as ctable_'.$key.'', db_prefix().'contacts.id = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="contacts" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                    $where_clients .= ' OR ctable_'.$key.'.value LIKE "%'.$q.'%"';
                }

                $where_clients .= ') AND '.db_prefix().'clients.active = 1';
            }

            $this->db->select('contacts.id AS id,clients.*,contacts.*');
            $this->db->join(db_prefix().'clients', ''.db_prefix().'contacts.userid = '.db_prefix().'clients.userid', 'left');

            $this->load->model('clients_model');
            $data = $this->clients_model->get_contacts('', $where_clients);
        // echo $this->db->last_query();
        } elseif ('ticket' == $type) {
            $search = $this->_search_tickets($q, 0, true);
            $data   = $search['result'];
        } elseif ('lead' == $type || 'leads' == $type) {
            $search = $this->_search_leads($q, 0, [
                    'junk' => 0,
                    ], true);
            $data = $search['result'];
        } elseif ('invoice' == $type || 'invoices' == $type) {
            $search = $this->_search_invoices($q, 0, [], true);
            $data   = $search['result'];
        } elseif ('invoice_items' == $type) {
            $this->load->model('invoice_items_model');
            $fields = get_custom_fields('items');
            $this->db->select('rate, items.id, description as name, long_description as subtext');
            $this->db->like('description', $q);
            $this->db->or_like('long_description', $q);
            foreach ($fields as $key => $value) {
                $this->db->join(db_prefix().'customfieldsvalues as ctable_'.$key.'', db_prefix().'items.id = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="items_pr" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                $this->db->or_like('ctable_'.$key.'.value', $q);
            }

            $items = $this->db->get(db_prefix().'items')->result_array();

            foreach ($items as $key => $item) {
                $items[$key]['subtext'] = strip_tags(mb_substr($item['subtext'], 0, 200)).'...';
                $items[$key]['name']    = '('.app_format_number($item['rate']).') '.$item['name'];
            }
            $data = $items;
        } elseif ('project' == $type) {
            $where_projects = '';
            if ($this->input->post('customer_id')) {
                $where_projects .= '(clientid='.$this->input->post('customer_id').' or clientid in (select id from tblleads where client_id='.$this->input->post('customer_id').') )';
            }
            if ($this->input->post('rel_type')) {
                $where_projects .= ' and rel_type="'.$this->input->post('rel_type').'" ';
            }
            $search = $this->_search_projects($q, 0, $where_projects, $this->input->post('rel_type'), true);

            $data   = $search['result'];
        } elseif ('staff' == $type) {
            $search = $this->_search_staff($q, 0, true);
            $data   = $search['result'];
        } elseif ('tasks' == $type) {
            $search = $this->_search_tasks($q, 0, true);
            $data   = $search['result'];
        } elseif ('payments' == $type) {
            $search = $this->_search_payment($q, 0, true);
            $data   = $search['result'];
        } elseif ('proposals' == $type) {
            $search = $this->_search_proposals($q, 0, true);
            $data   = $search['result'];
        } elseif ('estimates' == $type) {
            $search = $this->_search_estimates($q, 0, true);
            $data   = $search['result'];
        } elseif ('expenses' == $type) {
            $search = $this->_search_expenses($q, 0, true);
            $data   = $search['result'];
        } elseif ('creditnotes' == $type) {
            $search = $this->_search_credit_notes($q, 0, true);
            $data   = $search['result'];
        } elseif ('milestones' == $type) {
            $where_milestones = '';
            if ($q) {
                $where_milestones .= '(name LIKE "%'.$q.'%" OR id LIKE "%'.$q.'%")';
            }
            $data = $this->get_milestones_api('', $where_milestones);
        }

        return $data;
    }

    public function get_milestones_api($id = '', $where = [])
    {
        $this->db->select('*, (SELECT COUNT(id) FROM '.db_prefix().'tasks WHERE milestone='.db_prefix().'milestones.id) as total_tasks, (SELECT COUNT(id) FROM '.db_prefix().'tasks WHERE rel_type="project" and milestone='.db_prefix().'milestones.id AND status=5) as total_finished_tasks');
        if ('' != $id) {
            $this->db->where('id', $id);
        }
        if ((is_array($where) && count($where) > 0) || (is_string($where) && '' != $where)) {
            $this->db->where($where);
        }
        $this->db->order_by('milestone_order', 'ASC');
        $milestones = $this->db->get(db_prefix().'milestones')->result_array();

        return $milestones;
    }

    public function get_expenses_api($id, $where=[])
    {
        $this->db->select('*,'.db_prefix().'expenses.id as id,'.db_prefix().'expenses_categories.name as category_name,'.db_prefix().'payment_modes.name as payment_mode_name,'.db_prefix().'taxes.name as tax_name, '.db_prefix().'taxes.taxrate as taxrate,'.db_prefix().'taxes_2.name as tax_name2, '.db_prefix().'taxes_2.taxrate as taxrate2, '.db_prefix().'expenses.id as expenseid,'.db_prefix().'expenses.addedfrom as addedfrom, recurring_from');
        $this->db->from(db_prefix().'expenses');
        $this->db->join(db_prefix().'clients', ''.db_prefix().'clients.userid = '.db_prefix().'expenses.clientid', 'left');
        $this->db->join(db_prefix().'payment_modes', ''.db_prefix().'payment_modes.id = '.db_prefix().'expenses.paymentmode', 'left');
        $this->db->join(db_prefix().'taxes', ''.db_prefix().'taxes.id = '.db_prefix().'expenses.tax', 'left');
        $this->db->join(''.db_prefix().'taxes as '.db_prefix().'taxes_2', ''.db_prefix().'taxes_2.id = '.db_prefix().'expenses.tax2', 'left');
        $this->db->join(db_prefix().'expenses_categories', ''.db_prefix().'expenses_categories.id = '.db_prefix().'expenses.category');
        $this->db->where($where);

        if (is_numeric($id)) {
            $this->db->where(db_prefix().'expenses.id', $id);
            $expense = $this->db->get()->row();
            if ($expense) {
                $expense->attachment            = '';
                $expense->filetype              = '';
                $expense->attachment_added_from = 0;

                $this->db->where('rel_id', $id);
                $this->db->where('rel_type', 'expense');
                $file = $this->db->get(db_prefix().'files')->row();

                if ($file) {
                    $expense->attachment            = $file->file_name;
                    $expense->filetype              = $file->filetype;
                    $expense->attachment_added_from = $file->staffid;
                }

                $this->load->model('projects_model');
                $expense->currency_data = get_currency($expense->currency);
                if (0 != $expense->project_id) {
                    $expense->project_data = $this->projects_model->get($expense->project_id);
                }

                if (null === $expense->payment_mode_name) {
                    // is online payment mode
                    $this->load->model('payment_modes_model');
                    $payment_gateways = $this->payment_modes_model->get_payment_gateways(true);
                    foreach ($payment_gateways as $gateway) {
                        if ($expense->paymentmode == $gateway['id']) {
                            $expense->payment_mode_name = $gateway['name'];
                        }
                    }
                }
            }

            return $expense;
        }
        $this->db->order_by('date', 'desc');

        return $this->db->get()->result_array();
    }

    public function get_api_custom_data($data, $custom_field_type, $id = '', $is_invoice_item = false)
    {
        $this->db->where('active', 1);
        $this->db->where('fieldto', $custom_field_type);

        $this->db->order_by('field_order', 'asc');
        $fields       = $this->db->get(db_prefix().'customfields')->result_array();
        $customfields = [];
        if ('' === $id) {
            foreach ($data as $data_key => $value) {
                $data[$data_key]['customfields'] = [];
                $value_id                        = $value['id'] ?? '';
                if ('customers' == $custom_field_type) {
                    $value_id = $value['userid'];
                }
                if ('tickets' == $custom_field_type) {
                    $value_id = $value['ticketid'];
                }
                if ('staff' == $custom_field_type) {
                    $value_id = $value['staffid'];
                }
                foreach ($fields as $key => $field) {
                    $customfields[$key]        = new StdClass();
                    $customfields[$key]->label = $field['name'];
                    if ('items' == $custom_field_type && !$is_invoice_item) {
                        $custom_field_type = 'items_pr';
                        $value_id          = $value['itemid'] ?? $value['id'];
                    }
                    $customfields[$key]->value = get_custom_field_value($value_id, $field['id'], $custom_field_type, false);
                }
                $data[$data_key]['customfields'] = $customfields;
            }
        }
        if ('' !== $id && is_numeric($id)) {
            $data->customfields = new StdClass();
            foreach ($fields as $key => $field) {
                $customfields[$key]        = new StdClass();
                $customfields[$key]->label = $field['name'];
                if ('items' == $custom_field_type && !$is_invoice_item) {
                    $custom_field_type = 'items_pr';
                }
                $customfields[$key]->value = get_custom_field_value($id, $field['id'], $custom_field_type, false);
            }
            $data->customfields = $customfields;
        }

        return $data;
    }

    public function _search_payment($q, $limit = 0, $api = false)
    {
        $result = [
            'result'         => [],
            'type'           => 'payments',
            'search_heading' => _l('payments'),
        ];

        if (has_permission('payments', '', 'view') || true == $api) {
            $this->db->select(db_prefix().'invoicepaymentrecords.*');
            $this->db->from(db_prefix().'invoicepaymentrecords');
            $this->db->join(db_prefix().'payment_modes', db_prefix().'payment_modes.id='.db_prefix().'invoicepaymentrecords.paymentmode', 'LEFT');
            $this->db->like('name', $q);
            $this->db->or_like(db_prefix().'invoicepaymentrecords.paymentmode', $q);
            $this->db->or_like(db_prefix().'invoicepaymentrecords.amount', $q);

            if (0 != $limit) {
                $this->db->limit($limit);
            }
            $this->db->order_by('name', 'ASC');
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function payment_get($id='')
    {
        $this->db->select('*,'.db_prefix().'invoicepaymentrecords.id as paymentid');
        $this->db->join(db_prefix().'payment_modes', db_prefix().'payment_modes.id = '.db_prefix().'invoicepaymentrecords.paymentmode', 'left');
        $this->db->order_by(db_prefix().'invoicepaymentrecords.id', 'asc');

        if (!empty($id)) {
            $this->db->where(db_prefix().'invoicepaymentrecords.id', $id);
            $payment = $this->db->get(db_prefix().'invoicepaymentrecords')->row();
        } else {
            $payment = $this->db->get(db_prefix().'invoicepaymentrecords')->result();
        }

        if (!$payment) {
            return false;
        }

        $this->load->model('payment_modes_model');
        $payment_gateways = $this->payment_modes_model->get_payment_gateways(true);

        if (!empty($id)) {
            if (null === $payment->id) {
                foreach ($payment_gateways as $gateway) {
                    if ($payment->paymentmode == $gateway['id']) {
                        $payment->name = $gateway['name'];
                    }
                }
            }
        }

        if (empty($id)) {
            foreach ($payment as $key => $pay) {
                if (null === $pay->id) {
                    foreach ($payment_gateways as $gateway) {
                        if ($pay->paymentmode == $gateway['id']) {
                            $payment[$key]->name = $gateway['name'];
                        }
                    }
                }
            }
        }

        return $payment;
    }

    public function _search_proposals($q, $limit = 0, $api = false)
    {
        $fields = get_custom_fields('proposal');
        $result = [
            'result'         => [],
            'type'           => 'proposals',
            'search_heading' => _l('proposals'),
        ];

        $has_permission_view_proposals     = has_permission('proposals', '', 'view');
        $has_permission_view_proposals_own = has_permission('proposals', '', 'view_own');

        if ($has_permission_view_proposals || $has_permission_view_proposals_own || '1' == get_option('allow_staff_view_proposals_assigned') || true == $api) {
            if (is_numeric($q)) {
                $q = trim($q);
                $q = ltrim($q, '0');
            } elseif (startsWith($q, get_option('proposal_number_prefix'))) {
                $q = strafter($q, get_option('proposal_number_prefix'));
                $q = trim($q);
                $q = ltrim($q, '0');
            }

            $where_string = '';
            foreach ($fields as $key => $value) {
                $this->db->join(db_prefix().'customfieldsvalues as ctable_'.$key.'', db_prefix().'proposals.id = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="proposal" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                $where_string .= ' OR ctable_'.$key.'.value LIKE "%'.$q.'%"';
            }

            // Proposals
            $this->db->select('*,'.db_prefix().'proposals.id as id');
            $this->db->from(db_prefix().'proposals');
            $this->db->join(db_prefix().'currencies', db_prefix().'currencies.id = '.db_prefix().'proposals.currency');

            $this->db->where('(
                    '.db_prefix().'proposals.id LIKE "'.$q.'%"
                    OR '.db_prefix().'proposals.subject LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                    OR '.db_prefix().'proposals.content LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                    OR '.db_prefix().'proposals.proposal_to LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                    OR '.db_prefix().'proposals.zip LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                    OR '.db_prefix().'proposals.state LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                    OR '.db_prefix().'proposals.city LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                    OR '.db_prefix().'proposals.address LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                    OR '.db_prefix().'proposals.email LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                    OR '.db_prefix().'proposals.phone LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                    '.$where_string.'
                    )');

            $this->db->order_by(db_prefix().'proposals.id', 'desc');
            if (0 != $limit) {
                $this->db->limit($limit);
            }
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function _search_estimates($q, $limit = 0, $api = false)
    {
        $fields = get_custom_fields('estimate');
        $result = [
            'result'         => [],
            'type'           => 'estimates',
            'search_heading' => _l('estimates'),
        ];

        $has_permission_view_estimates     = has_permission('estimates', '', 'view');
        $has_permission_view_estimates_own = has_permission('estimates', '', 'view_own');

        if ($has_permission_view_estimates || $has_permission_view_estimates_own || '1' == get_option('allow_staff_view_estimates_assigned') || $api = true) {
            if (is_numeric($q)) {
                $q = trim($q);
                $q = ltrim($q, '0');
            } elseif (startsWith($q, get_option('estimate_prefix'))) {
                $q = strafter($q, get_option('estimate_prefix'));
                $q = trim($q);
                $q = ltrim($q, '0');
            }

            $where_string = '';
            foreach ($fields as $key => $value) {
                $this->db->join(db_prefix().'customfieldsvalues as ctable_'.$key.'', db_prefix().'estimates.id = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="estimate" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                $where_string .= ' OR ctable_'.$key.'.value LIKE "%'.$q.'%"';
            }

            // Estimates
            $estimates_fields  = prefixed_table_fields_array(db_prefix().'estimates');
            $clients_fields    = prefixed_table_fields_array(db_prefix().'clients');

            $this->db->select(implode(',', $estimates_fields).','.implode(',', $clients_fields).','.db_prefix().'estimates.id as estimateid,'.get_sql_select_client_company());
            $this->db->from(db_prefix().'estimates');
            $this->db->join(db_prefix().'clients', db_prefix().'clients.userid = '.db_prefix().'estimates.clientid', 'left');
            $this->db->join(db_prefix().'currencies', db_prefix().'currencies.id = '.db_prefix().'estimates.currency');
            $this->db->join(db_prefix().'contacts', db_prefix().'contacts.userid = '.db_prefix().'clients.userid AND is_primary = 1', 'left');

            $this->db->where('(
                '.db_prefix().'estimates.number LIKE "'.$this->db->escape_like_str($q).'"
                OR
                '.db_prefix().'clients.company LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'estimates.clientnote LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.vat LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.phonenumber LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.city LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.state LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.zip LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                address LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'estimates.adminnote LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'estimates.billing_street LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'estimates.billing_city LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'estimates.billing_state LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'estimates.billing_zip LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'estimates.shipping_street LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'estimates.shipping_city LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'estimates.shipping_state LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'estimates.shipping_zip LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.billing_street LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.billing_city LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.billing_state LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.billing_zip LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.shipping_street LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.shipping_city LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.shipping_state LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.shipping_zip LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                '.$where_string.'
                )');

            $this->db->order_by('number,YEAR(date)', 'desc');
            if (0 != $limit) {
                $this->db->limit($limit);
            }
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function _search_expenses($q, $limit = 0, $api=false)
    {
        $fields = get_custom_fields('expenses');
        $result = [
            'result'         => [],
            'type'           => 'expenses',
            'search_heading' => _l('expenses'),
        ];

        $has_permission_expenses_view     = has_permission('expenses', '', 'view');
        $has_permission_expenses_view_own = has_permission('expenses', '', 'view_own');

        if ($has_permission_expenses_view || $has_permission_expenses_view_own || true == $api) {
            // Expenses

            $where_string = '';
            foreach ($fields as $key => $value) {
                $this->db->join(db_prefix().'customfieldsvalues as ctable_'.$key.'', db_prefix().'expenses.id = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="expenses" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                $where_string .= ' OR ctable_'.$key.'.value LIKE "%'.$q.'%"';
            }

            $this->db->select('*,'.db_prefix().'expenses.amount as amount,'.db_prefix().'expenses_categories.name as category_name,'.db_prefix().'payment_modes.name as payment_mode_name,'.db_prefix().'taxes.name as tax_name, '.db_prefix().'expenses.id as expenseid,'.db_prefix().'currencies.name as currency_name');
            $this->db->from(db_prefix().'expenses');
            $this->db->join(db_prefix().'clients', db_prefix().'clients.userid = '.db_prefix().'expenses.clientid', 'left');
            $this->db->join(db_prefix().'payment_modes', db_prefix().'payment_modes.id = '.db_prefix().'expenses.paymentmode', 'left');
            $this->db->join(db_prefix().'taxes', db_prefix().'taxes.id = '.db_prefix().'expenses.tax', 'left');
            $this->db->join(db_prefix().'expenses_categories', db_prefix().'expenses_categories.id = '.db_prefix().'expenses.category');
            $this->db->join(db_prefix().'currencies', ''.db_prefix().'currencies.id = '.db_prefix().'expenses.currency', 'left');

            $this->db->where('(company LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR paymentmode LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR '.db_prefix().'payment_modes.name LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR vat LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR phonenumber LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR city LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR zip LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR address LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR state LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR '.db_prefix().'expenses_categories.name LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR '.db_prefix().'expenses.note LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR '.db_prefix().'expenses.expense_name LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                '.$where_string.'
                )');

            if (0 != $limit) {
                $this->db->limit($limit);
            }
            $this->db->order_by('date', 'DESC');
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function _search_credit_notes($q, $limit = 0, $api=false)
    {
        $fields = get_custom_fields('credit_note');
        $result = [
            'result'         => [],
            'type'           => 'credit_note',
            'search_heading' => _l('credit_notes'),
        ];

        $has_permission_view_credit_notes     = has_permission('credit_notes', '', 'view');
        $has_permission_view_credit_notes_own = has_permission('credit_notes', '', 'view_own');

        if ($has_permission_view_credit_notes || $has_permission_view_credit_notes_own || true == $api) {
            if (is_numeric($q)) {
                $q = trim($q);
                $q = ltrim($q, '0');
            } elseif (startsWith($q, get_option('credit_note_prefix'))) {
                $q = strafter($q, get_option('credit_note_prefix'));
                $q = trim($q);
                $q = ltrim($q, '0');
            }

            $where_string = '';
            foreach ($fields as $key => $value) {
                $this->db->join(db_prefix().'customfieldsvalues as ctable_'.$key.'', db_prefix().'creditnotes.id = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="credit_note" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                $where_string .= ' OR ctable_'.$key.'.value LIKE "%'.$q.'%"';
            }

            $credit_note_fields = prefixed_table_fields_array(db_prefix().'creditnotes');
            $clients_fields     = prefixed_table_fields_array(db_prefix().'clients');
            // Invoices
            $this->db->select(implode(',', $credit_note_fields).','.implode(',', $clients_fields).','.db_prefix().'creditnotes.id as credit_note_id,'.get_sql_select_client_company());
            $this->db->from(db_prefix().'creditnotes');
            $this->db->join(db_prefix().'clients', db_prefix().'clients.userid = '.db_prefix().'creditnotes.clientid', 'left');
            $this->db->join(db_prefix().'currencies', db_prefix().'currencies.id = '.db_prefix().'creditnotes.currency');
            $this->db->join(db_prefix().'contacts', db_prefix().'contacts.userid = '.db_prefix().'clients.userid AND is_primary = 1', 'left');

            $this->db->where('(
                '.db_prefix().'creditnotes.number LIKE "'.$this->db->escape_like_str($q).'"
                OR
                '.db_prefix().'clients.company LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'creditnotes.clientnote LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.vat LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.phonenumber LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.city LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.state LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.zip LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.address LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'creditnotes.adminnote LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                CONCAT(firstname,\' \',lastname) LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                CONCAT(lastname,\' \',firstname) LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'creditnotes.billing_street LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'creditnotes.billing_city LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'creditnotes.billing_state LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'creditnotes.billing_zip LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'creditnotes.shipping_street LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'creditnotes.shipping_city LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'creditnotes.shipping_state LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'creditnotes.shipping_zip LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.billing_street LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.billing_city LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.billing_state LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.billing_zip LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.shipping_street LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.shipping_city LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.shipping_state LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR
                '.db_prefix().'clients.shipping_zip LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                '.$where_string.'
                )');

            $this->db->order_by('number', 'desc');
            if (0 != $limit) {
                $this->db->limit($limit);
            }

            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    private function total_refunds_by_credit_note($id)
    {
        return sum_from_table(db_prefix().'creditnote_refunds', [
                'field' => 'amount',
                'where' => ['credit_note_id' => $id],
            ]);
    }

    private function total_credits_used_by_credit_note($id)
    {
        return sum_from_table(db_prefix().'credits', [
                'field' => 'amount',
                'where' => ['credit_id' => $id],
            ]);
    }
}
