<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for the deployment_users table
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
class Model_Deployment_User extends ORM {

	/**
	 * "Belongs to" relationship definition
	 * @var array
	 */
	protected $_belongs_to = array(
		'user' => array(),
		'deployment' => array()
	);
	
	/**
	 * Validation rules
	 *
	 * @return array
	 */
	public function rules()
	{
		return array(
			'deployment_name' => array(array('not_empty')),
			'client_id' => array(array('not_empty')),
			'client_secret' => array(array('not_empty'))		
		);
	}
}