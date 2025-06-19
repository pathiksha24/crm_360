<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_120 extends App_module_migration {
	protected $module_name = 'project_management_enhancements';
	protected int $version = 120;

	public function up()
	{
		$this->ci->db->query(
			'CREATE TABLE IF NOT EXISTS '.db_prefix().'project_owners (
            `id` INT NOT NULL AUTO_INCREMENT,
            `project_id` INT NOT NULL,
            `staff_id` INT NOT NULL,
            PRIMARY KEY (`id`),
            KEY `project_id` (`project_id`),
  			KEY `staff_id` (`staff_id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;'
		);

		zegaware_add_migration_log($this->module_name, $this->version);
	}

	public function down()
	{
		$this->ci->db->query("DROP TABLE if EXISTS ".db_prefix()."project_owners;");

		zegaware_delete_migration_log($this->module_name, $this->version);
	}
}