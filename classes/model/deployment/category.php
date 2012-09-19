<?php defined('SYSPATH') OR die('No direct script access');

/**
 * Model for the deployment_category table
 *
 * PHP version 5
 * LICENSE: This source file is subject to GPLv3 license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/gpl.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    SwiftRiver - http://github.com/ushahidi/SwiftRiver
 * @category   Models
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License v3 (GPLv3) 
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