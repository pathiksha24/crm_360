<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Task_types_model extends App_Model {
	public function get_project_task_types($project_id)
	{
		$this->db->where('project_id', $project_id);
		$this->db->order_by('sort_order', 'desc');

		return $this->db->get(db_prefix().'task_types')->result_array();
	}

	public function get_task_type($id)
	{
		$this->db->where('id', $id);

		return $this->db->get(db_prefix().'task_types')->row_array();
	}

	public function get_task_type_by_task_type_id($project_id, $task_type_id)
	{
		$this->db->select('task_types.*');
		$this->db->where('project_id', $project_id);
		$this->db->where('id', $task_type_id);

		return $this->db->get(db_prefix().'task_types')->row_array();
	}

	public function get_task_type_by_name($project_id, $task_type_name)
	{
		return $this->db
			->query("SELECT * FROM ".db_prefix()."task_types WHERE `project_id`=$project_id AND LOWER(`name`) = LOWER('$task_type_name')")
			->row_array();
	}

	public function add_project_task_type($data)
	{
		$this->db->insert(db_prefix().'task_types', $data);

		return $this->db->insert_id();
	}

	/**
	 * @param $project_id
	 * @param $task_type_id
	 * @param $data
	 * @return bool
	 */
	public function update_project_task_type($project_id, $task_type_id, $data)
	{
		$this->db->where('id', $task_type_id);
		$this->db->where('project_id', $project_id);
		$this->db->update(db_prefix().'task_types', $data);

		return $this->db->affected_rows() > 0;
	}

	public function delete_project_task_type($project_id, $task_type_id)
	{
		$this->db->where('id', $task_type_id);
		$this->db->where('project_id', $project_id);

		return $this->db->delete(db_prefix().'task_types');
	}
}