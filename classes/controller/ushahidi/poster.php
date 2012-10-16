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
			Kohana::$log->add(Log::INFO, "There are no buckets to push to Ushahidi. Exiting...");
			exit;
		}

		Kohana::$log->add(Log::INFO, "Found :count buckets. Fetching the drops to push",
		    array(":count" => count($bucket_ids)));
		
		// Get the drops to push - only those with place tags
		$pending_drops = Model_Deployment_Push_Log::get_pending_drops($bucket_ids);

		if ( ! count($pending_drops))
		{
			Kohana::$log->add(Log::INFO, "No pending drops found");
			exit;
		}
		// Get the push URLs and client ID for each push target
		$push_targets = Model_Deployment_Push_Setting::get_push_targets(array_keys($pending_drops));
		
		// Store for the buckets succesfully pushed to Ushahidi
		$posted_buckets = array();

		// Push each bucket to its respective deployment
		foreach ($push_targets as $bucket_id => $metadata)
		{
			// Get the payload for each push target
			$drops_array = $pending_drops[$bucket_id]['drops'];
			$drops_payload = json_encode($drops_array);
			$checksum = hash_hmac("sha256", $drops_payload, $metadata['client_secret']);

			Kohana::$log->add(Log::DEBUG, "Checksum for bucket :id with :num drops - :checksum",
				array(":id" => $bucket_id, ":checksum" => $checksum, ":num" => count($drops_array)
			));

			// Base64 encode the drops so that the data remain intact without modification during
			// transport to the API endpoint
			$payload = array(
				"drops" => base64_encode($drops_payload),
				"checksum" => $checksum,
				"client_id" => $metadata['client_id']
			);

			// Submit the payload
			$response = $this->_post_drops($metadata['url'], $payload);
			
			// Store the response
			if ($response["status"] === "OK")
			{
				$posted_buckets[$bucket_id] = count($drops_array);
				Kohana::$log->add(Log::INFO, "Drops from bucket :id successfuly posted",
					array(":id" => $bucket_id));
			}
		}
		
		// Check if any buckets were posted
		if ( ! count($posted_buckets))
		{
			Kohana::$log->add(Log::INFO, "An error occured. No buckets were posted to the deployments");
			exit;
		}

		// 
		// Update the push log and pending drop count for each of the buckets
		// in $posted_buckets
		// 
		$push_log_query = array();
		$drop_count_query = array();
		foreach ($posted_buckets as $bucket_id => $bucket_drop_count)
		{
			// Queries to update the pending drop count
			$drop_count_query[] = sprintf("SELECT %d AS `bucket_id`, %d AS `posted_drop_count`",
			    $bucket_id, $bucket_drop_count);

			// Queries to update the push log
			foreach ($pending_drops[$bucket_id]['drops'] as $drop)
			{
				$push_log_query[] = sprintf("SELECT %d AS `bucket_id`, %d AS `droplet_id`", $bucket_id, $drop['id']);
			}
		}
		
		// Update the push log
		$log_update_query = "UPDATE `deployment_push_logs` AS a JOIN (%s) AS b "
		    . "ON b.bucket_id = a.bucket_id "
		    . "SET a.droplet_push_status = 1, "
		    . "a.droplet_date_push = '%s' "
		    . "WHERE a.droplet_id = b.droplet_id";

		$log_update_query = sprintf($log_update_query, implode("UNION ALL ", $push_log_query), gmdate("Y-m-d H:i:s"));
		DB::query(Database::UPDATE, $log_update_query)->execute();

		// Update the pending drop count
		$count_update_query = "UPDATE `deployment_push_settings` AS a JOIN (%s) AS b "
		    . "ON b.bucket_id = a.bucket_id "
		    . "SET a.pending_drop_count = a.pending_drop_count - b.posted_drop_count "
		    . "WHERE a.pending_drop_count > 0";
	
		$count_update_query = sprintf($count_update_query, implode("UNION ALL ", $drop_count_query));
		DB::query(Database::UPDATE, $count_update_query)->execute();

		// Cleanup
		unset ($pending_drops, $drop_count_query, $push_log_query);
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
		// Create a new request and set the body
		$request = Request::factory($url)
		    ->method("POST")
		    ->client(Request_Client_Curl::factory());

		foreach ($payload as $param => $data)
		{
			$request->post($param, $data);
		}

		// Execute the request
		$response = $request->execute();

		Kohana::$log->add(Log::INFO, "API responded with status :status",
			array(":status" => $response->status()));

		return json_decode($response->body(), TRUE);
	}
}
