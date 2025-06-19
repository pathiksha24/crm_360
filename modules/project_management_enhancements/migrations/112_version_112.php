<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_112 extends App_module_migration {
	protected $module_name = 'project_management_enhancements';
	protected int $version = 112;

	public function up()
	{
		$this->ci->db->query('ALTER TABLE '.db_prefix().'task_comments MODIFY COLUMN content longtext');

		zegaware_add_migration_log($this->module_name, $this->version);
	}

	public function down()
	{
		zegaware_delete_migration_log($this->module_name, $this->version);
	}
}