<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * This controller allows a user to configure the settings for pushing a
 * bucket to an Ushahidi deployment
 *
 * PHP version 5
 * LICENSE: This source file is subject to GPLv3 license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/gpl.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    SwiftRiver - http://github.com/ushahidi/Swiftriver_v2
 * @subpackage Controllers
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License v3 (GPLv3) 
 */
class Controller_Bucket_Settings_Ushahidi extends Controller_Bucket_Settings {
	
	/**
	 * Loads the settings page page for the configuring the ushahidi deployment
	 * to which drops in the current bucket should be sync'd to
	 */
	public function action_index()
	{
		$this->template->header->title = $this->bucket->bucket_name .'~' .__("Push to Ushahidi");
		$this->settings_content = View::factory('pages/bucket/settings/ushahidi')
		    ->bind('deployment_list', $deployment_list)
		    ->bind('deployment_categories', $deployment_categories)
		    ->bind('push_drop_count', $push_drop_count);
		    ->bind('fetch_url', $fetch_url)
		
		// Get the push settings for this deployment
		$push_settings = $this->bucket->deployment_settings;
		
		// List of deployments
		$deployment_list = json_encode(Model_Deployment::get_deployments_array());
		
		// List of categories for the currently selected deployment
		$deployment_categories = ! empty($push_settings)
		    ? json_encode($push_settings->deployment->get_categories_array())
		    : json_encode(array());
		
		// Setting for the no. of drops to be pushed for each sync
		$push_drop_count = ! empty ($push_settings)
		    ? $push_settings->push_drop_count
		    : 0;
		
		$fetch_url = URL::site('bucket/settings/ushahidi/categories');
		
		// Form submission
		if ($_POST AND CSRF::valid($_POST['form-auth-id']))
		{
			// Save the deployment settings
			if (empty($push_settings))
			{
				$push_settings = new Model_Bucket_Setting();
			}
			
			$push_settings->deployment_id = $_POST['deployment_id'];
			$push_settings->deployment_category_id = $_POST['deployment_category_id'];
			$push_settings->push_drop_count = $_POST['push_drop_count'];
			
			try
			{
				$push_settings->save();

				// Show success message
			}
			catch (ORM_Validation_Exception $e)
			{
				// Show error message
				$this->settings_content->errors = array("An error occurred when updating the settings");
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
		
		switch ($this->method->request())
		{
			case "GET":
			// Get the post data
			$deployment_id = $this->request->param('id', 0);
			
			// Get the categories for the selected deployment
			$deployment_categories = Model_Deployment::get_categories_array($deployment_id);
			
			echo json_encode($deployment_categories);
			break;
		}
	}
}