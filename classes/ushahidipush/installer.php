<?php defined('SYSPATH') OR die('No direct script access');

class Ushahidipush_Installer {

	/**
	 * Executes the SQL script to create schema objects used by
	 * this plugin
	 *
	 * @return bool
	 */
	public static function install()
	{
		// Check if the file exists
		if (($install_script = Kohana::find_file('install', 'ushahidipush', 'sql')) !== FALSE)
		{
			Kohana::$log->add(Log::INFO, "Found setup SQL script - :install_script",
			    array(":install_script" => $install_script));

			// Get the contents of the script
			$setup_sql = file_get_contents(realpath($install_script));

			try
			{
				// Independently execute the DDL for each schema object
				foreach (explode(";", preg_replace('/\--\s.*/i', '', $setup_sql)) as $create_table)
				{
					$create_table = trim($create_table);
					if ( ! empty($create_table))
					{
						DB::query(NULL, $create_table)->execute();
					}
				}

				Kohana::$log->add(Log::INFO, "Setup script successfully executed");
			}
			catch (Database_Exception $e)
			{
				Kohana::$log->add(Log::ERROR, $e->getMessage());
				return FALSE;
			}

			return TRUE;
		}
		return FALSE;
	}
}