<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * This controller allows a user to configure the settings for pushing a
 * bucket to an Ushahidi deployment
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
class Controller_Bucket_Ushahidi extends Controller_Bucket_Settings {
	
	/**
	 * Loads the settings page page for the configuring the ushahidi deployment
	 * to which drops in the current bucket should be sync'd to
	 */
	public function action_index()
	{
		$this->active = 'ushahidi';
		$this->template->header->title = $this->bucket->bucket_name .'~' .__("Push to Ushahidi");
		$this->settings_content = View::factory('pages/bucket/settings/ushahidi')
		    ->bind('deployments_list', $deployments_list)
		    ->bind('deployment_categories', $deployment_categories)
		    ->bind('push_drop_count', $push_drop_count)
		    ->bind('fetch_url', $fetch_url)
		    ->bind('push_settings', $push_settings);
		
		// Get the push settings for this deployment
		$push_settings = Model_Deployment_Push_Setting::get_settings($this->bucket->id);
		
		// List of deployments
		$deployments_list = json_encode(Model_Deployment::get_deployments_array($this->user->id));
		
		// List of categories for the currently selected deployment
		$deployment_categories = $push_settings->loaded()
		    ? json_encode($push_settings->deployment->get_categories_array())
		    : json_encode(array());
		
		// Setting for the no. of drops to be pushed for each sync
		$push_drop_count = $push_settings->loaded()
		    ? $push_settings->push_drop_count
		    : 20;
		
		$fetch_url = $this->bucket_base_url.'/settings/ushahidi/categories';
		
		// Form submission
		if ($_POST AND CSRF::valid($this->request->post('form_auth_id')))
		{
			// Save the deployment settings
			if ( ! $push_settings->loaded())
			{
				$push_settings = new Model_Deployment_Push_Setting();
				$push_settings->bucket_id = $this->bucket->id;
			}

			try
			{
				$push_settings->deployment_id = $this->request->post('deployment_id');
				$push_settings->deployment_category_id = $this->request->post('deployment_category_id');
				$push_settings->push_drop_count = $this->request->post('push_drop_count');
				$push_settings->save();
				
				// Display the success message
				$this->settings_content->success = TRUE;

			}
			catch (ORM_Validation_Exception $e)
			{
				// Show error message
				$this->settings_content->errors = __("An error occurred when updating the settings - :message",
				    array(":message" => $e->getMessage()));
				Kohana::$log->add(Log::ERROR, $e->getMessage());
			}
		}
	}
	
	/**
	 * REST endpoint for fetching the categories of the selected
	 * deployment
	 */
	public function action_categories()
	{
		$this->template = '';
		$this->auto_render = FALSE;
		
		switch ($this->request->method())
		{
			case "GET":
			// Get the post data
			$deployment_id = $this->request->query('id');
			$deployment = ORM::factory('Deployment', $deployment_id);
			
			if ($deployment->loaded())
			{
				// Get the categories for the selected deployment
				$deployment_categories = $deployment->get_categories_array();
			
				echo json_encode($deployment_categories);
			}
			else
			{
				throw new HTTP_Exception_404(__("The specified deployment is invalid"));
			}
			break;
		}
	}
}