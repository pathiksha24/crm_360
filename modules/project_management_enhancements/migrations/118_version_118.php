<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_118 extends App_module_migration {
	protected $module_name = 'project_management_enhancements';
	protected int $version = 118;

	public function up()
	{
		$this->ci->db->query(
			'CREATE TABLE IF NOT EXISTS '.db_prefix().'task_recent_changes (
            `id` INT NOT NULL AUTO_INCREMENT,
            `task_id` INT NOT NULL,
            `user_id` INT NOT NULL,
            `read` TINYINT(1) DEFAULT 0,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;'
		);

		zegaware_add_migration_log($this->module_name, $this->version);
	}

	public function down()
	{
		$this->ci->db->query("DROP TABLE if EXISTS ".db_prefix()."task_recent_changes;");

		zegaware_delete_migration_log($this->module_name, $this->version);
	}
}