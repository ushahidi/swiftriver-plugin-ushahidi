<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for the deployments table
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
class Model_Deployment extends ORM {
	
	/**
	 * A deployment belongs to a user
	 * @var array
	 */
	protected $_belongs_to = array(
	    'user' => array()
	);
	
	/**
	 * One-to-many relationship definition
	 * @var array
	 */
	protected $_has_many = array(
	    'deployment_categories' => array(),
	    'deployment_push_settings' => array(),
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
	 * Overload default saving behaviour
	 */
	public function save(Validation $validation = NULL)
	{
		// New deployment
		if ( ! $this->loaded())
		{
			// Compatibility check
			if (Ushahidipush_Core::is_compatible_deployment($this->deployment_url))
			{
				$deployment = parent::save();
				
				// Get the categories for the deployment
				Ushahidipush_Core::get_categories($deployment);
				
				return $deployment;
			}
			else
			{
				throw new Ushahidipush_Exception(__("The :name deployment is running a version that is not compatible with this plugin",
				    array(":name" => $this->deployment_name)));
			}
		}
		else
		{
			return parent::save();
		}
	}

	/**
	 * Overloads the default delete behaviour
	 */
	public function delete()
	{
		// Delete the categories for this deployment
		DB::delete('deployment_categories')
		    ->where('deployment_id', '=', $this->id)
		    ->execute();

		return parent::delete();
	}
	
	/**
	 * Gets a list of all categories for the current deployment
	 * 
	 * @param  bool $parents_only Whether to fetch all or only the parent categories. Default is TRUE
	 * @return array
	 */
	public function get_categories_array($parents_only = TRUE)
	{
		$categories = array();
		$deployment_categories = ($parents_only)
		   ? $this->deployment_categories->where('deployment_parent_category_id', '=', 0)->find_all()
		   : $this->deployment_categires->find_all();

		foreach ($deployment_categories as $category)
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
		// Columns to insert
		$columns = array(
		    'deployment_id',
		    'deployment_category_id',
		    'deployment_parent_category_id',
		    'deployment_category_name'
		);

		$insert_query = DB::insert('deployment_categories', $columns);
		foreach ($categories as $entry)
		{
			$category = $entry['category'];
			$insert_query->values(array(
				'deployment_id' => $deployment_id,
				'deployment_category_id' => $category['id'],
				'deployment_parent_category_id' => $category['parent_id'],
				'deployment_category_name' => $category['title']
			));
		}

		// Execute the query
		$insert_query->execute();
	}
	
	/**
	 * Gets the list of all deployments as an array
	 *
	 * @return array
	 */
	public static function get_deployments_array($user_id)
	{
		$deployments = array();
		foreach (ORM::factory('deployment')->where('user_id', '=', $user_id)->find_all() as $deployment)
		{
			$deployments[] = $deployment->as_array();
		}
		
		return $deployments;
	}
	
	/**
	 * Given the deployment name, url, token key and secret, creates a new
	 * entry in the deployments table
	 *
	 * @param  int    $user_id ID of the user adding the deployment
	 * @param  array  $data Properties of the deployment being added
	 * @return mixed  Model_Deployment on success, FALSE otherwise
	 */
	public static function add_deployment($user_id, $data)
	{
		try
		{
			$deployment = new Model_Deployment();
			$deployment->user_id = $user_id;
			$deployment->deployment_name = $data['deployment_name'];
			$deployment->deployment_url = $data['deployment_url'];
			$deployment->deployment_token_key = $data['deployment_token_key'];
			$deployment->deployment_token_secret = $data['deployment_token_secret'];
			
			return $deployment->save();
		}
		catch (Database_Exception $e)
		{
			// Log the error
			Kohana::$log->add(Log::ERROR, "Error adding deployment :name for user :id (:error)", array(
			    ":name" => $data['deployment_name'],
			    ":id" => $user_id,
			    ":error" => $e->getMessage()
			));

			return FALSE;
		}
		
	}
}