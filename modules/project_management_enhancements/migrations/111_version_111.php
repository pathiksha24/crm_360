<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_111 extends App_module_migration {

	protected $module_name = 'project_management_enhancements';
	protected int $version = 111;

	public function up()
	{
		$this->ci->db->query('ALTER TABLE '.db_prefix().'tasks DROP FOREIGN KEY tbltasks_ibfk_1');

		$this->ci->db->query(
			'ALTER TABLE '.db_prefix().'tasks ADD CONSTRAINT FOREIGN KEY(task_type) REFERENCES '.db_prefix(
			).'task_types(id) ON DELETE SET NULL'
		);

		zegaware_add_migration_log($this->module_name, $this->version);
	}

	public function down()
	{
		zegaware_delete_migration_log($this->module_name, $this->version);
	}
}