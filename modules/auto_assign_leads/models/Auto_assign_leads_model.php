<?php
class Auto_assign_leads_model extends App_Model
{
     public function get_staff_by_id($staff_id)
    {
        $this->db->where('staffid', $staff_id);
        return $this->db->get('tblstaff')->row_array(); // Assuming staff table is tblstaff
    }
     public function get_service_by_id($service_id)
    {
        $this->db->where('id', $service_id);
        return $this->db->get('tblleads_services')->row_array(); // Assuming services table is tblservices
    }
    public function assign_staff_service($data)
    {
        $this->db->insert('tblstaff_services_assigning', $data);
        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }
        return false;
    }
     public function get_saved_assignments()
    {
        // Order by created_date to show newest first, or by staffname/service_name
        $this->db->order_by('created_date', 'desc');
        return $this->db->get('tblstaff_services_assigning')->result_array();
    }
    public function update_assignment_status($id, $status)
{
    $this->db->where('id', $id);
    $this->db->update('tblstaff_services_assigning', ['status' => $status]); // Update 'status' column
    return $this->db->affected_rows() > 0;
}
public function check_assignment_exists($staff_id, $service_id)
{
    $this->db->where('staffid', $staff_id);
    $this->db->where('serviceid', $service_id);
    $this->db->where('status', 1);
    $query = $this->db->get('tblstaff_services_assigning');

    return $query->num_rows() > 0;
}
public function get_saved_services()
    {
        // Order by created_date to show newest first, or by staffname/service_name
        $this->db->order_by('created_date', 'desc');
        $this->db->group_by('serviceid');
        return $this->db->get('tblstaff_services_assigning')->result_array();
    }
 public function edit_saved_assignments()
    {
        $this->db->order_by('created_date', 'desc');
        $this->db->where('status', 1);
        return $this->db->get('tblstaff_services_assigning')->result_array();
    }
       public function bulk_update_status(array $ids, int $status): bool
    {
    if (empty($ids)) {
        return false;
    }
    // Update all matching IDs
    $this->db->where_in('id', $ids);
    return (bool) $this->db
                     ->update(db_prefix() . 'staff_services_assigning', ['status' => $status]);
    }

}