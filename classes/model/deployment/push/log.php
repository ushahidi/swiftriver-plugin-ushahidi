<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for the deployment_log table
 *
 * PHP version 5
 * LICENSE: This source file is subject to the AGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/agpl.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    SwiftRiver - http://github.com/ushahidi/SwiftRiver
 * @category   Models
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/licenses/agpl.html GNU Affero General Public License (AGPL)
 */
class Model_Deployment_Push_Log extends ORM {
	
	/**
	 * Belongs-to relatioship definition
	 * @var array
	 */
	protected $_belongs_to = array(
	    'bucket' => array(),
		'deployment' => array()
	);
	
	/**
	 * Event callback to add an entry to the push log. The bucket specified
	 * in the event data must be configured to push drops to a deployment
	 */
	public static function add_entry()
	{
		// Get the event data
		$event_data = Swiftriver_Event::$data;
		
		list($bucket_id, $droplet_id) = array($event_data['bucket_id'], $event_data['droplet_id']);

		// Get the push settings for the bucket
		$settings_orm = Model_Deployment_Push_Setting::get_setting($bucket_id);
		
		if ($settings_orm->loaded())
		{
			$log_entry_orm = new Model_Deployment_Push_Log();		
			$log_entry_orm->deployment_id = $deployment_orm->deployment_id;
			$log_entry_orm->bucket_id = $bucket_id;
			$log_entry_orm->droplet_id = $droplet_id;
			$log_entry_orm->droplet_push_status = 0;
			$log_entry_orm->save();
		}
		else
		{
			// Log
			Kohana::$log->add(Log::INFO, "Bucket :bucket_id is not configured to push drops to an Ushahidi deployment",
			    array(":bucket_id" => $bucket_id));
		}
	}
	
	/**
	 * Event callback to remove an entry from the push log. The bucket
	 * specified in the event data must be configured to push drops to a
	 * deployment
	 */
	public static function remove_entry()
	{
		// Get the event data
		$event_data = Swiftriver_Event::$data;
		
		list($bucket_id, $droplet_id) = array($event_data['bucket_id'], $event_data['droplet_id']);

		// Get the push settings for the bucket
		$settings_orm = Model_Deployment_Push_Setting::get_setting($bucket_id);
		
		if ($settings_orm->loaded())
		{
			// Delete the entry from the push log
			ORM::factory('deployment_push_log')
			    ->where('bucket_id', '=', $bucket_id)
			    ->where('droplet_id', '=', $droplet_id)
			    ->where('deployment_id', '=', $settings_orm->deployment_id)
			    ->delete();
		}
		
	}
}