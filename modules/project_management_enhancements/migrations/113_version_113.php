<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_113 extends App_module_migration {

	protected $module_name = 'project_management_enhancements';
	protected int $version = 113;

	public function up()
	{
		if ( ! $this->ci->db->field_exists('project_id', db_prefix().'task_types'))
		{
			$this->ci->db->query('ALTER TABLE '.db_prefix().'task_types ADD COLUMN `project_id` INT NULL');
		}

		if ( ! $this->ci->db->field_exists('text_color', db_prefix().'task_types'))
		{
			$this->ci->db->query("ALTER TABLE ".db_prefix()."task_types ADD COLUMN `text_color` VARCHAR(10) DEFAULT '#000000'");
		}

		/** @var CI_DB_query_builder $db */
		$db = $this->ci->db;
		$existed_types = $db->get(db_prefix().'project_task_types')->result_array();

		if ( ! empty($existed_types))
		{
			foreach ($existed_types as $type)
			{
				$db->reset_query();
				$db->update(db_prefix().'task_types', array('project_id' => $type['project_id']), array('id' => $type['task_type_id']));
			}
		}

		zegaware_add_migration_log($this->module_name, $this->version);
	}

	public function down()
	{
		$this->ci->db->query('ALTER TABLE '.db_prefix().'task_types DROP COLUMN `project_id`');
		zegaware_delete_migration_log($this->module_name, $this->version);
	}
}