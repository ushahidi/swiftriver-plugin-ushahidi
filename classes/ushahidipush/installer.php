<?php defined('SYSPATH') OR die('No direct script access');

/**
 * Installer helper for the Ushahidi Push plugin
 *
 * PHP version 5
 * LICENSE: This source file is subject to the AGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/agpl.html
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    SwiftRiver - http://github.com/ushahidi/SwiftRiver
 * @category   Helpers
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/licenses/agpl.html GNU Affero General Public License (AGPL)
 */
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