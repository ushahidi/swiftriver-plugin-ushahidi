<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * This controller allows a user to add ushahidi deployments to which
 * they would like to push drops to
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
		$deployments = json_encode(Model_Deployment::get_deployments_array());
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
			$post_data = json_decode($this->request->body(), TRUE);
			$deployment = Model_Deployment::add_deployment($post_data);
			
			echo json_encode($deployment->as_array());
			break;
			
			case "PUT":
			break;
			
			case "DELETE":
			break;
		}
	}
}