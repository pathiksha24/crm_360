<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_110 extends App_module_migration {
	protected $module_name = 'project_management_enhancements';
	protected int $version = 110;

	public function up()
	{
		$this->ci->db->where('editable', 0);
		$this->ci->db->delete(db_prefix().'task_types');
		zegaware_add_migration_log($this->module_name, $this->version);
	}

	public function down()
	{
		zegaware_delete_migration_log($this->module_name, $this->version);
	}
}