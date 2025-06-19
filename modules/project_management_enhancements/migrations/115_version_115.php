<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_115 extends App_module_migration {
	protected $module_name = 'project_management_enhancements';
	protected int $version = 115;

	public function up()
	{
		if ( ! $this->ci->db->field_exists('parent_id', db_prefix().'task_comments'))
		{
			$this->ci->db->query("ALTER TABLE ".db_prefix()."task_comments ADD COLUMN `parent_id` INT DEFAULT NULL AFTER `taskid` ;");
		}
		zegaware_add_migration_log($this->module_name, $this->version);
	}

	public function down()
	{
		$this->ci->db->query('ALTER TABLE '.db_prefix().'task_comments DROP COLUMN `parent_id`');
		zegaware_delete_migration_log($this->module_name, $this->version);
	}

}