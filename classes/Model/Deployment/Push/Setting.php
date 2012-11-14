<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for the deployment_push_settings table
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
class Model_Deployment_Push_Setting extends ORM {
	
	/**
	 * Membership definition
	 * @var array
	 */
	protected $_belongs_to = array(
		'bucket' => array(),
		'deployment' => array()
	);
	
	/**
	 * Validation rules for this model
	 * @return array
	 */
	public function rules()
	{
		return array(
			'push_drop_count' => array(
				array('not_empty'),
				array('digit')
			),
		);
	}
	
	/**
	 * Returns the settings for the specified bucket
	 *
	 * @param  int $bucket_id  ID of the bucket
	 * @return Model_Deployment_Setting
	 */
	public static function get_settings($bucket_id)
	{
		return ORM::factory('Deployment_Push_Setting')
			->where('bucket_id','=', $bucket_id)
			->find();
	}
	
	/**
	 * Gets the buckets that are ready to have their drops pushed to Ushahidi.
	 * These are buckets with the following property:
	 *      pending_drop_count >= push_drop_count
	 *      pending_drop_count > 0 
	 *
 	 * i.e. the no. of pending drops is equal to or more than the configured
	 * batch size for pushing drops to a deployment
	 * 
	 * @return array
	 */
	public static function get_eligible_buckets()
	{
		$bucket_ids = array();
		$eligible_buckets = ORM::factory('Deployment_Push_Setting')
			->where('pending_drop_count', '>=', 'push_drop_count')
			->where('pending_drop_count', '>', 0)
			->find_all();

		foreach ($eligible_buckets as $entry)
		{
			$bucket_ids[] = $entry->bucket_id;
		}
		
		return $bucket_ids;
	}
	
	/**
	 * Gets the REST endpoints for posting drops for each of the specified
	 * buckets
	 *
	 * @param  array $bucket_ids List of bucket ids
	 * @return array
	 */
	public static function get_push_targets($bucket_ids)
	{
		// Store for the push targets
		$push_targets = array();

		// Get the 
		$all_targets = DB::select('buckets.id', 'deployment_url', 'deployment_users.client_id', 'deployment_users.client_secret')
		    ->from('deployment_push_settings')
		    ->join('buckets', 'INNER')
		    ->on('buckets.id', '=', 'deployment_push_settings.bucket_id')
		    ->join('deployments', 'INNER')
		    ->on('deployment_push_settings.deployment_id', '=', 'deployments.id')
		    ->join('accounts', 'INNER')
		    ->on('accounts.id', '=', 'buckets.account_id')
		    ->join('deployment_users', 'INNER')
		    ->on('deployment_users.deployment_id', '=', 'deployments.id')
		    ->where('accounts.user_id', '=', DB::expr('deployment_users.user_id'))
		    ->where('buckets.id', 'IN', $bucket_ids)
		    ->execute()
		    ->as_array();

		// Get the endpoint segment for posting drops
		$drops_endpoint = Kohana::$config->load("ushahidi.endpoints.drops");

		foreach ($all_targets as $target)
		{
			$push_targets[$target['id']] = array(
				'url' => Ushahidi_Core::get_request_url($target['deployment_url'], $drops_endpoint),
				'client_id' => $target['client_id'],
				'client_secret' => $target['client_secret']
			);
		}

		return $push_targets;		
	}

}