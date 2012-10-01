<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * This controller posts drops to the configured ushahidi deployments
 *
 * PHP version 5
 * LICENSE: This source file is subject to the AGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/agpl.html
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    SwiftRiver - http://github.com/ushahidi/SwiftRiver
 * @category   Controllers
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/licenses/agpl.html GNU Affero General Public License (AGPL)
 */
class Controller_Ushahidi_Poster extends Controller {
	
	public function action_run()
	{
		if (php_sapi_name() !== 'cli')
		{
			Kohana::$log->add(Log::ERROR, "Push to ushahidi must be run in CLI mode");
			exit;
		}
		
		Kohana::$log->add(Log::INFO, "Preparing to execute bucket push");
		
		// Get the buckets that are ready to post drops to Ushahidi
		$bucket_ids = Model_Deployment_Push_Setting::get_eligible_buckets();

		// If no buckets were found, exit
		if (empty($bucket_ids))
		{
			Kohana::$log->add(Log::INFO, "There are no buckets to push to Ushahidi. Exiting...")
			exit;
		}

		Kohana::$log->add(Log::INFO, "Found :count buckets. Fetching the drops to push",
		    array(":count" => count($bucket_ids)));
		
		// Get the drops to push - only those with place tags
		$pending_drops = Model_Deployment_Push_Log::get_pending_drops($bucket_ids);

		// Get the push URLs and client ID for each push target
		$push_targets = $this->_get_push_targets(array_keys($pending_drops));
		
		// Store for api responses for endpoint
		$api_responses = array();

		// Push each bucket to its respective deployment
		foreach ($push_target as $bucket_id => $metadata)
		{
			// Get the payload for each push target
			$drops_payload = json_encode($pending_drops[$bucket_id]['drops']);
			
			$checksum = hash_hmac("sha256", $drops_payload, $metadata['client_secret']);

			$payload = array(
			    "drops" => $drops_payload,
			    "checksum" => $checksum,
			    "client_id" => $metadata['client_id']
			);

			// Submit the payload
			$response = $this->_post_drops($metadata['url'], $payload);
			
			// Store the response
			$api_reponses[$bucket_id] = $response;
		}
		
		// TODO: Update the push log for each of the buckets in $api_responses
		// Also upate the pending drop count
	}
	
	/**
	 * Gets the REST endpoints for posting drops for each of the specified
	 * buckets
	 *
	 * @param  array $bucket_ids List of bucket ids
	 * @return array
	 */
	private function _get_push_targets($bucket_ids)
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
		    ->on('account.id', '=', 'buckets.account_id')
		    ->join('deployment_users', 'INNER')
		    ->on('deployment_users.deployment_id', '=', 'deployments.id')
		    ->where('accounts.user_id', '=', 'deployment_users.user_id')
		    ->where('buckets.id', 'IN', $bucket_ids)
		    ->execute();

		// Get the endpoint segment for posting drops
		$drops_endpoint = Kohana::$config->load("ushahidi.endpoints.drops");

		foreach ($all_targets as $target)
		{
			$push_targets[$target->id] => array(
			    'url' => Ushahidi_Core::get_request_url($target->deployment_url, $drops_endpoint),
			    'client_id' => $target->client_id,
			    'client_secret' => $target->client_secret
			);
		}
		
		return $push_targets;
	}
	
	/**
	 * POSTs the specified payload to the provided URL
	 *
	 * @param  string $url URL to post the drops to
	 * @param  string $payload URL encoded data to be posted
	 * @return array
	 */
	private function _post_drops($url, $payload)
	{
		// Create a new request
		$request = Request::factory($url);

		// Set the HTTP POST parameters
		foreach ($payload as $param => $values)
		{
			$request->post($param, $values);
		}

		// Execute the request
		$response = Request_Client_Curl::factory()->execute($request);
		
		return json_decode($response, TRUE);
	}
}
