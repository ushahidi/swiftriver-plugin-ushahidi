<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Core helper
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
class Ushahidi_Core {

	/**
	 * Checks whether the deployment referenced by the specified url
	 * is running a version of the platform is compatible with this plugin
	 *
	 * @param  string  $url URL of the Ushahidi deployment
	 * @return bool
	 */
	public static function is_compatible_deployment($deployment_url)
	{
		// Get the platform version
		$min_version = Kohana::$config->load('ushahidi.min_version');
		
		// Endpoint for the version check
		$version_endpoint = Kohana::$config->load('ushahidi.endpoints.version_check');
		
		// Get the request url
		$request_url = self::get_request_url($deployment_url, $version_endpoint);
		
		// Send the request
		$api_response = self::_api_request($request_url);
		
		list($status, $version) = array($api_response['error'], $api_response['payload']['version'][0]);
		
		if ($status['code'] == '0')
		{
			// Get the version of the deployment
			$deployment_version = $version['version'];
			return ($deployment_version >= $min_version);
		}
		else
		{
			Kohana::$log->add(Log::ERROR, "API returned an error - :message",
			    array(":message" => $status['message']));

			return FALSE;
		}
	}

	/**
	 * Given a deployment, gets the list of categories via the Ushahidi API
	 *
	 * @param  Model_Deployment $deployment
	 * @return bool
	 */
	public static function get_categories($deployment)
	{
		// Get the endpoint for fetching the categories
		$categories_endpoint = Kohana::$config->load('ushahidi.endpoints.categories');

		// Get the request url
		$request_url = self::get_request_url($deployment->deployment_url, $categories_endpoint);
		
		// Execute the request and fetch the response
		$api_response = self::_api_request($request_url);
		
		list($status, $categories) = array($api_response['error'], $api_response['payload']['categories']);	

		if ($status['code'] == '0')
		{
			Model_Deployment::add_categories($deployment->id, $categories);
		}
		else
		{
			// API returned an error
			Kohana::$log->add(Log::ERROR, "API returned an error - :message",
			    array(":message" => $status['message']));
		}
	}
	
	/**
	 * Concatentates the deployment url and provided segment to produce
	 * a single request url
	 *
	 * @param  string  $deployment_url Base URL for the deployment
	 * @param  string  $endpoint Segment to be appended to the deployment url
	 * @return string
	 */
	public static function get_request_url($deployment_url, $endpoint)
	{
		$request_url = $deployment_url;
		
		if (substr($request_url, strlen($request_url)-2, 1) !== "/")
		{
			$request_url .= "/";
		}

		// Build out the request cURL
		return $request_url.$endpoint;
		
	}
	
	/**
	 * Executes an API request via cURL and returns the response
	 * as an array
	 *
	 * @param  string $request_url URL for the cURL request
	 * @return array
	 */
	private static function _api_request($request_url)
	{
		$api_response = Request_Client_Curl::factory()
		   ->execute(Request::factory($request_url));
		
		return json_decode($api_response, TRUE);
	}
}