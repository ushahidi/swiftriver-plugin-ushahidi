<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for the deployments table
 *
 * PHP version 5
 * LICENSE: This source file is subject to GPLv3 license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/gpl.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    SwiftRiver - http://github.com/ushahidi/Swiftriver_v2
 * @category   Models
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License v3 (GPLv3) 
 */
class Model_Deployment extends ORM {
	
	/**
	 * A deployment belongs to a user
	 * @var array
	 */
	protected $_belongs_to = array('user');
	
	/**
	 * One-to-many relationship definition
	 * @var array
	 */
	protected $_has_many = array(
		'deployment_categories' => array(),
		'deployment_settings' => array(),
	);
	
	/**
	 * Validation rules for this model
	 * @return array
	 */
	public function rules()
	{
		return array(
			// Deployment name must be specified
			'deployment_name' => array(array('not_empty')),
			
			// URL of the deployment must be valid
			'deployment_url' => array(
				array('not_empty'),
				array('url')
			),
			
			'deployment_token_key' => array(array('not_empty')),
			
			'deployment_token_secret' => array(array('not_empty'))
		);
	}
	
	/**
	 * Returns the list of categories for the current deployment
	 * @return array
	 */
	public function get_categories_array()
	{
		$categories = array();
		foreach ($this->deployment_categories as $category)
		{
			$categories[] = array(
				'id' => $category->id,
				'category_name' => $category->deployment_category_name
			);
		}		
		return $categories;
	}
	
	/**
	 * Adds a list of categories to a deployment
	 *
	 * @param  int   $deployment_id ID of the deployment
	 * @param  array $categories  List of categories to be added to the deployment
	 * @return bool  TRUE on success, FALSE otherwise
	 */
	public static function add_categories($deployment_id, $categories)
	{
		return FALSE;
	}
	
	/**
	 * Gets the list of all deployments as an array
	 *
	 * @return array
	 */
	public static function get_deployments_array()
	{
		return array();
		// return ORM::factory('deployment')->find_all()->as_array();
	}
	
	/**
	 * Given the deployment name, url, token key and secret, creates a new
	 * entry in the deployments table
	 *
	 * @param  array  $data Properties of the deployment being added
	 * @return Model_Deployment
	 */
	public static function add_deployment($data)
	{
		$deployment = new Model_Deployment();
		$deployment->deployment_name = $data['deployment_name'];
		$deployment->deployment_url = $data['deployment_url'];
		$deployment->deployment_token_key = $data['deployment_token_key'];
		$deployment->deployment_token_secret = $data['deployment_token_secret'];

		return $deployment->save();
	}
}