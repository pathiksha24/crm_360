<?php

defined('BASEPATH') or exit('No direct script access allowed');

add_option('zpme_is_activated', FALSE);
add_option('zpme_license_key', FALSE);
add_option('zpme_activated_at', FALSE);
add_option('zpme_last_validate', FALSE);
add_option('pme_migrated_database', FALSE);

if ( ! get_option('pme_migrated_database'))
{
	$CI = &get_instance();
	$table_exist = $CI->db->query("SHOW TABLES LIKE '%task_types'")->num_rows();

	if ($table_exist <= 0)
	{
		require_once APP_MODULES_PATH.'project_management_enhancements/migrations/100_version_100.php';

		$migration = new Migration_Version_100();
		$migration->up();
	}
	update_option('pme_migrated_database', TRUE);
}

$CI = &get_instance();

$module_name = 'project_management_enhancements';
$table_name = 'module_migrations';

if ( ! $CI->db->table_exists($table_name))
{
	$CI->db->query(
		"
                CREATE TABLE `".db_prefix().$table_name."` (
                    `module` VARCHAR(50) NOT NULL,
                    `version` INT(11) NOT NULL,
                    `applied_at` DATETIME NOT NULL,
                    PRIMARY KEY (`module`, `version`)
                );
            "
	);
}

// Path to migration files
$migration_path = APP_MODULES_PATH.$module_name.'/migrations/*';
$migration_files = glob($migration_path);

// Sort migration files by version
usort($migration_files, function ($a, $b) {
	return intval(preg_replace('/\D/', '', basename($a))) - intval(preg_replace('/\D/', '', basename($b)));
});

foreach ($migration_files as $file)
{
	$version = substr(basename($file), 0, 3);

	$migration_exists = $CI->db->where('version', $version)
		->where('module', $module_name)
		->get(db_prefix().'module_migrations')
		->row();

	if (empty($migration_exists))
	{
		require_once $file;
		$class_name = 'Migration_Version_'.$version;

		if (class_exists($class_name) && intval($version) > 100)
		{
			$migration = new $class_name();
			if (method_exists($migration, 'up'))
			{
				$migration->up();
			}
		}
	}
}