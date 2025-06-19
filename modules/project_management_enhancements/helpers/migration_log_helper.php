<?php

if ( ! function_exists('zegaware_add_migration_log'))
{
	function zegaware_add_migration_log($module_name, $version)
	{
		zegaware_create_migration_logs_table();

		$CI = &get_instance();

		$migration_exists = $CI->db->where('version', $version)
			->where('module', $module_name)
			->get(db_prefix().'module_migrations')
			->row();

		if ( ! $migration_exists)
		{
			// Record the applied migration
			$CI->db->insert(db_prefix().'module_migrations', [
				'module' => $module_name,
				'version' => $version,
				'applied_at' => date('Y-m-d H:i:s'),
			]);

			return $CI->db->affected_rows() > 0;
		}

		return FALSE;
	}
}


if ( ! function_exists('zegaware_delete_migration_log'))
{
	function zegaware_delete_migration_log($module_name, $version)
	{
		$CI = &get_instance();

		$migration_exists = $CI->db->where('version', $version)
			->where('module', $module_name)
			->get(db_prefix().'module_migrations')
			->row();

		if ($migration_exists)
		{
			$CI->db->where('module', $module_name);
			$CI->db->where('version', $version);
			$CI->db->delete(db_prefix().'module_migrations');

			return $CI->db->affected_rows() > 0;
		}

		return FALSE;
	}
}

if ( ! function_exists('zegaware_create_migration_logs_table'))
{
	function zegaware_create_migration_logs_table()
	{
		$table_name = 'module_migrations';
		$CI = &get_instance();
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
	}
}