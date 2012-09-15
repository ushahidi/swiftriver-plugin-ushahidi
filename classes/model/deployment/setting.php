<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for the deployment_settings table
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
class Model_Deployment_Setting extends ORM {
	
	/**
	 * Membership definition
	 * @var array
	 */
	protected $_belongs_to = array('bucket', 'deployment');
	
	/**
	 * Validation rules for this model
	 * @return array
	 */
	public function rules()
	{
		return array(
			'push_drop_count' => array(
				array('not_empty'),
				array('digit')
			),
		);
	}
	
}