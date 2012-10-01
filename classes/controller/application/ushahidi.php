<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * This controller allows a user to add ushahidi deployments to which
 * they would like to push drops to
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
class Controller_Application_Ushahidi extends Controller_User {
	
	public function before()
	{
		parent::before();
		
		if ( ! $this->owner)
		{
			$this->request->redirect($this->dashboard_url);
		}
	}
	
	/**
	 * Renders the landing page for this controller; list of
	 * Ushahidi deployments
	 */	
	public function action_index()
	{
		$this->active = 'ushahidi-deployments-link';
		$this->template->content->view_type = 'settings';
		
		$this->sub_content = View::factory('pages/user/applications/ushahidi')
		    ->bind('deployments', $deployments)
		    ->bind('action_url', $action_url);
		
		$action_url = URL::site($this->account->account_path.'/application/ushahidi/manage');
		$deployments = json_encode(Model_Deployment::get_deployments_array($this->user->id));
	}
	
	/**
	 * REST endpoint for adding, edititing and deleting
	 * ushahidi deployments configured by the user
	 */
	public function action_manage()
	{
		$this->template = '';
		$this->auto_render = FALSE;
		
		switch ($this->request->method())
		{
			case "POST":
			// Create new deployment
			$post_data = json_decode($this->request->body(), TRUE);
			try
			{
				$deployment = Model_Deployment::add_deployment($this->user->id, $post_data);
				if ($deployment)
				{
					echo json_encode($deployment->as_array());
				}
				else
				{
					// Error message
					$error_message = __("The \":name\" deployment could not be saved. Verify that the URL is valid "
					    ."and this deployment doesn't already exist in your list of deployments", 
					array(":name" => $post_data['deployment_name']));

					$this->response->status(400);
					echo $error_message;
				}
			}
			catch (Ushahidi_Exception $e)
			{
				$this->response->status(400);
				echo $e->getMessage();
			}
			break;
			
			case "PUT":
			// Update existing deployment
			$deployment_id = $this->request->param('id', 0);
			$deployment_orm = ORM::factory('deployment', $deployment_id);
			if ( ! $deployment_orm->loaded())
			{
				throw HTTP_Exception_404(__("The specified deployment does not exist"));
			}
			
			$update_data = json_decode($this->request->body());

			$deployment_orm->deployment_name = $update_data->deployment_name;
			$deployment_orm->deployment_url = $update_data->deployment_url;
			$deployment_orm->deployment_token_key = $update_data->deployment_token_key;
			$deployment_orm->deployment_token_secret = $update_data->deployment_token_secret;
			$deployment_orm->update();
			break;
			
			case "DELETE":
			$deployment_id = $this->request->param('id', 0);
			$deployment_orm = ORM::factory('deployment', $deployment_id);
			if ($deployment_orm->loaded())
			{
				$deployment_orm->remove('users', $this->user);
				$deployment_orm->delete();
			}
			break;
		}
	}
}