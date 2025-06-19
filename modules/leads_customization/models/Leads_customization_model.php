<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Leads_customization_model extends App_Model
{
    public function get_services(){
        return $this->db->select('*')->from(db_prefix().'leads_services')->get()->result_array();
    }
    public function services_crud($operation, $data){
        if ($operation == 'add'){
            $this->db->insert(db_prefix() . 'leads_services', $data);
            $insert_id = $this->db->insert_id();
            return $insert_id;
        }else{
            $id = $data['id'];
            unset($data['id']);
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'leads_services', $data);
            if ($this->db->affected_rows() > 0) {
                return true;
            }
            return false;
        }
    }
    public function get_languages(){
        return $this->db->select('*')->from(db_prefix().'leads_languages')->get()->result_array();
    }
    public function languages_crud($operation, $data){
        if ($operation == 'add'){
            $this->db->insert(db_prefix() . 'leads_languages', $data);
            $insert_id = $this->db->insert_id();
            return $insert_id;
        }else{
            $id = $data['id'];
            unset($data['id']);
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'leads_languages', $data);
            if ($this->db->affected_rows() > 0) {
                return true;
            }
            return false;
        }
    }

    public function getLead($id = '', $where = [])
    {
        $this->db->select('*,' . db_prefix() . 'leads.name
        , ' . db_prefix() . 'leads.id,
        ' . db_prefix() . 'leads_status.name as status_name,
        ' . db_prefix() . 'leads_sources.name as source_name,
        ' . db_prefix() . 'leads_services.name as service_name,
        ' . db_prefix() . 'leads_languages.name as language_name
        ');
        $this->db->join(db_prefix() . 'leads_status', db_prefix() . 'leads_status.id=' . db_prefix() . 'leads.status', 'left');
        $this->db->join(db_prefix() . 'leads_sources', db_prefix() . 'leads_sources.id=' . db_prefix() . 'leads.source', 'left');
        $this->db->join(db_prefix() . 'leads_services', db_prefix() . 'leads_services.id=' . db_prefix() . 'leads.service', 'left');
        $this->db->join(db_prefix() . 'leads_languages', db_prefix() . 'leads_languages.id=' . db_prefix() . 'leads.language', 'left');

        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'leads.id', $id);
            $lead = $this->db->get(db_prefix() . 'leads')->row();
            if ($lead) {
                if ($lead->from_form_id != 0) {
                    $lead->form_data = $this->get_form([
                        'id' => $lead->from_form_id,
                    ]);
                }
                $lead->attachments = $this->get_lead_attachments($id);
                $lead->public_url  = leads_public_url($id);
            }

            return $lead;
        }

        return $this->db->get(db_prefix() . 'leads')->result_array();
    }

    public function get_lead_attachments($id = '', $attachment_id = '', $where = [])
    {
        $this->db->where($where);
        $idIsHash = !is_numeric($attachment_id) && strlen($attachment_id) == 32;
        if (is_numeric($attachment_id) || $idIsHash) {
            $this->db->where($idIsHash ? 'attachment_key' : 'id', $attachment_id);

            return $this->db->get(db_prefix() . 'files')->row();
        }
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'lead');
        $this->db->order_by('dateadded', 'DESC');

        return $this->db->get(db_prefix() . 'files')->result_array();
    }
}