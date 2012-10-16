<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Model for the deployment_push_log table
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
		'deployment' => array(),
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
		$settings_orm = Model_Deployment_Push_Setting::get_settings($bucket_id);

		if ($settings_orm->loaded())
		{
			$log_entry_orm = new Model_Deployment_Push_Log();		
			$log_entry_orm->deployment_id = $settings_orm->deployment_id;
			$log_entry_orm->bucket_id = $bucket_id;
			$log_entry_orm->droplet_id = $droplet_id;
			$log_entry_orm->droplet_push_status = 0;
			$log_entry_orm->save();
			
			$settings_orm->pending_drop_count += 1;
			$settings_orm->save();
			
			// Check pending_drop_count >= push_drop_count
			$push_drop_count = $settings_orm->push_drop_count;
			$pending = $settings_orm->pending_drop_count;
			if ($pending > 0 AND ($pending >= $push_drop_count))
			{
				// Notify observers listening on this event that the bucket is ready
				// for pushing to Ushahidi
				Kohana::$log->add(Log::INFO, "Preparing to push bucket :id to ushahidi",
					array(":id" => $bucket_id));
				Swiftriver_Event::run("swiftriver.bucket.ushahidi.push", $bucket_id);
			}
			
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
		$settings_orm = Model_Deployment_Push_Setting::get_settings($bucket_id);
		
		if ($settings_orm->loaded() AND $settings_orm->pending_drop_count > 0)
		{
			// Delete the entry from the push log
			$entry = ORM::factory('deployment_push_log')
			    ->where('bucket_id', '=', $bucket_id)
			    ->where('droplet_id', '=', $droplet_id)
			    ->where('deployment_id', '=', $settings_orm->deployment_id)
			    ->find();
			
			if ($entry->loaded())
			{
				if ($entry->droplet_push_status == 0)
				{
					$settings_orm->pending_drop_count -= 1;
					$settings_orm->save();
				}

				$entry->delete();
			}
		}		
	}
	
	/**
	 * Given a set of buckets, returns the list of drops
	 * that are yet to be pushed to their respective deployments.
	 *
	 * @param  array $bucket_ids IDs of buckets that have been configured to 
	 *                           push drops to an Ushahidi deployment
	 * @return array
	 */
	public static function get_pending_drops($bucket_ids)
	{
		// Return value array
		$pending_drops = array();

		// Get the drops to be pushed
		$query = DB::select('dpl.bucket_id', 'buckets.account_id', 'droplets.id',
					'droplets.droplet_hash', 'droplets.droplet_title',
					'droplets.droplet_content', 'identity_name', 'identity_avatar',
					array('dc.deployment_category_id', 'category_id'), 'droplets.droplet_date_add')
			->from(array('deployment_push_logs', 'dpl'))
			->join('droplets', 'INNER')
			->on('dpl.droplet_id', '=', 'droplets.id')
			->join('identities', 'INNER')
			->on('droplets.identity_id', '=', 'identities.id')
			->join('buckets', 'INNER')
			->on('dpl.bucket_id', '=', 'buckets.id')
			->join(array('deployment_push_settings', 'dps'), 'INNER')
			->on('dps.bucket_id', '=', 'dpl.bucket_id')
			->join(array('deployment_categories', 'dc'), 'INNER')
			->on('dps.deployment_category_id', '=', 'dc.id')
			->where('dc.deployment_id', '=', DB::expr('dps.deployment_id'))
			->where('dps.deployment_id', '=', DB::expr('dpl.deployment_id'))
			->where('dpl.droplet_push_status', '=', 0)
			->where('buckets.id', 'IN', $bucket_ids)
			->group_by('dpl.bucket_id', 'droplets.droplet_hash');

		$droplets = $query->execute()->as_array();

		// UTF8 encode the drop title and content
		foreach ($droplets as & $droplet)
		{
			Model_Droplet::utf8_encode($droplet);
		}
				
		// Group the drops (in the return array) per bucket and account
		foreach ($droplets as $droplet)
		{
			$bucket_id = $droplet['bucket_id'];
			if ( ! array_key_exists($bucket_id, $pending_drops))
			{
				$pending_drops[$bucket_id] = array(
				    'account_id' => $droplet['account_id'],
				    'drops' => array()
				);
			}
			
			$pending_drops[$bucket_id]['drops'][] = $droplet;
		}

		// Store for the drops without place tags
		$no_place_tags = array();

		// Populate each bucket's drops with the drop metadata
		foreach ($pending_drops as $bucket_id => & $data)
		{
			Model_Droplet::populate_metadata($data['drops'], $data['account_id']);
			
			// Exclude drops that don't have place tags
			foreach ($data['drops'] as $k => & $drop)
			{
				if (empty($drop['places']))
				{
					if ( ! array_key_exists($bucket_id, $no_place_tags))
					{
						$no_place_tags[$bucket_id] = array();
					}
					
					$no_place_tags[$bucket_id][] = $drop['id'];
					unset ($data['drops'][$k]);
				}
				else
				{
					$place = $drop['places'][0];
					$drop['place_hash'] = $place['place_hash'];
					$drop['place_name'] = $place['place_name'];
					$drop['latitude'] = $place['latitude'];
					$drop['longitude'] = $place['longitude'];
				}
			}
		}

		// Mark the drops without place tags as pushed
		if ( ! empty($no_place_tags))
		{
			// Log
			Kohana::$log->add(Log::INFO, "Found :count buckets with drops that have no place tags",
			    array(":count" => count(array_keys($no_place_tags))));

			// Update the pending_drop_count for each bucket in $no_place_tags
			$drop_count_query = array();
			$push_log_query = array();

			foreach ($no_place_tags as $bucket_id => $drop_ids)
			{
				$query = sprintf("SELECT %d AS bucket_id, %d AS drop_count", $bucket_id, count($drop_ids));
				$drop_count_query[] = $query;
				foreach ($drop_ids as $drop_id)
				{
					$push_log_query[] = sprintf("SELECT %d AS `bucket_id`, %d AS `droplet_id`", $bucket_id, $drop_id);
				}
			}

			// Update the push log
			$log_update_query = "UPDATE `deployment_push_logs` AS a JOIN (%s) AS b "
			    . "ON b.bucket_id = a.bucket_id "
			    . "SET droplet_push_status = 1, "
			    . "a.droplet_date_push = '%s' "
			    . "WHERE a.droplet_id = b.droplet_id";

			// Log
			Kohana::$log->add(Log::INFO, "Marking drops without place tags as pushed");

			$log_update_query = sprintf($log_update_query, implode("UNION ALL ", $push_log_query), gmdate("Y-m-d H:i:s"));
			DB::query(Database::UPDATE, $log_update_query)->execute();

			// Update the pending drop count
			$count_update_query = "UPDATE `deployment_push_settings` AS a JOIN (%s) AS b "
			    . "ON b.bucket_id = a.bucket_id "
			    . "SET a.pending_drop_count = (a.pending_drop_count - b.drop_count)";

			// Log
			Kohana::$log->add(Log::INFO, "Updating the drop count");

			$count_update_query = sprintf($count_update_query, implode("UNION ALL ", $drop_count_query));
			DB::query(Database::UPDATE, $count_update_query)->execute();
		}

		return $pending_drops;
	}

}