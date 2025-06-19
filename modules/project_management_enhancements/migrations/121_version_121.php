<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_121 extends App_module_migration {
	protected $module_name = 'project_management_enhancements';
	protected int $version = 121;

	public function up()
	{
		zegaware_add_migration_log($this->module_name, $this->version);
	}

	public function down()
	{
		zegaware_delete_migration_log($this->module_name, $this->version);
	}
}