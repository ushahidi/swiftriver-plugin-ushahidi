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
	 * One-to-many relationship definition
	 * @var array
	 */
	protected $_has_many = array(
		'deployment_categories' => array(),
		'deployment_push_settings' => array(),
		
		// A deployment can have one or more users
		'users' => array(
			'model' => 'user',
			'through' => 'deployment_users',
			'far_key' => 'user_id'
		)
	);
	
	/**
	 * Date column to be updated when a new record is created
	 * @var array
	 */
	protected $_created_column = array(
		'column' => 'deployment_date_add',
		'format' => 'Y-m-d H:i:s'
	);
	
	/**
	 * Validation rules for this model
	 * @return array
	 */
	public function rules()
	{
		return array(
			// URL of the deployment must be valid
			'deployment_url' => array(
				array('not_empty'),
				array('url')
			)
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
			if (Ushahidi_Core::is_compatible_deployment($this->deployment_url))
			{
				$deployment = parent::save();
				
				// Get the categories for the deployment
				Ushahidi_Core::get_categories($deployment);
				
				return $deployment;
			}
			else
			{
				throw new Ushahidi_Exception(":url is either running a version of Ushahidi "
				    . "that is incompatible with this plugin or it's not a valid deployment.",
				    array(":url" => $this->deployment_url));
			}
		}
		else
		{
			return parent::save();
		}
	}

	/**
	 * Overrides the default delete behaviour
	 */
	public function delete()
	{
		// Delete the categories for this deployment
		if ($this->users->count_all() == 0)
		{
			DB::delete('deployment_categories')
			    ->where('deployment_id', '=', $this->id)
			    ->execute();

			return parent::delete();
		}
		
		return FALSE;
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
		   : $this->deployment_categories->find_all();

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
		foreach (ORM::factory('deployment_user')->where('user_id', '=', $user_id)->find_all() as $entry)
		{
			$deployments[] = array(
				"id" => $entry->id,
				"deployment_name" => $entry->deployment_name,
				"deployment_url" => $entry->deployment->deployment_url,
				"client_id" => $entry->client_id,
				"client_secret" => $entry->client_secret
			);
		}
		
		return $deployments;
	}
	
	/**
	 * Given the deployment name, url, token key and secret, creates a new
	 * entry in the deployments table
	 *
	 * @param  int    $user_id ID of the user adding the deployment
	 * @param  array  $data Properties of the deployment being added
	 * @return mixed  Model_Deployment_User on success, FALSE otherwise
	 */
	public static function add_deployment($user_id, $data)
	{
		try
		{
			$deployment = self::get_deployment_by_url($data['deployment_url']);
			if ( ! $deployment->loaded())
			{
				$deployment = new Model_Deployment();
				$deployment->deployment_url = $data['deployment_url'];
				$deployment->save();
			}
			
			// Add the user entry for the deployment
			$user_deployment = new Model_Deployment_User();
			$user_deployment->deployment_id = $deployment->id;
			$user_deployment->user_id = $user_id;
			$user_deployment->deployment_name = $data['deployment_name'];
			$user_deployment->client_id = $data['client_id'];
			$user_deployment->client_secret = $data['client_secret'];
			$user_deployment->save();
			
			return $user_deployment;
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
	
	/**
	 * Gets a deployment using its url. Each deployment has a unique URL
	 *
	 * @param  string $url URL of the deployment
	 * @return Model_Deployment
	 */
	public static function get_deployment_by_url($url)
	{
		return ORM::factory('deployment')->where('deployment_url', '=', $url)->find();
	}
	
	/**
	 * Gets the full URL of the endpoint to be used for posting drops
	 *
	 * @return string
	 */
	public function get_drop_posting_url()
	{
		$drops_endpoint = Kohana::$config->load("ushahidi.endpoints.drops");
		return Ushahidi_Core::get_request_url($this->deployment_url, $drops_endpoint);
	}
}