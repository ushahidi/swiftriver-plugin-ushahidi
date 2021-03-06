<?php defined('SYSPATH') OR die('No direct script access');

/**
 * Model for the deployment_category table
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
class Model_Deployment_Category extends ORM {
	
	/**
	 * Belongs-to relationship definition
	 * @var array
	 */
	protected $_belongs_to = array(
		'deployment' => array()
	);
}