<?php defined('SYSPATH') OR die('No direct script access');

/**
 * Config for ushahidipush
 *
 * PHP version 5
 * LICENSE: This source file is subject to GPLv3 license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/gpl.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package	   SwiftRiver - http://github.com/ushahidi/SwiftRiver
 * @subpackage Ushahidipush config
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License v3 (GPLv3) 
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
	)
);