<?php defined('SYSPATH') OR die('No direct script access');

/**
 * Config for ushahidipush
 *
 * PHP version 5
 * LICENSE: This source file is subject to the AGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/agpl.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package	   SwiftRiver - http://github.com/ushahidi/SwiftRiver
 * @subpackage Ushahidipush config
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/licenses/agpl.html GNU Affero General Public License (AGPL)
 */
return array(

	// Version of the ushahidi deployment
	'min_version' => '2.4',

	// Endpoints for the Ushahidi platform API
	'endpoints' => array(
		
		// Platform version API
		'version_check' => "api?task=version",
		
		// Categories API
		'categories' => 'api?task=categories',	
		
		// 	Drops posting API
		'drops' => 'api/swiftriver/drops'
	)
);