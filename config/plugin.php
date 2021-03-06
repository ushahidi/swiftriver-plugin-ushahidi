<?php defined('SYSPATH') OR die('No direct script access');

/**
 * Configuration for the Ushahidi-Push Plugin
 * This plugin allows drops in a bucket to be perodically pushed
 * to an Ushahidi deployment. It is designed to work with version 2.4+
 * of the Ushahidi Platform and requires the SwiftRiver plugin to be enabled
 * on Ushahidi.
 *
 * PHP Version 5.3+
 * LICENSE: This source file is subject to the AGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/agpl.html
 *
 * @author     Ushahidi Team
 * @package    SwiftRiver - https://github.com/ushahidi/SwiftRiver
 * @category   Plugins
 * @copyright  Ushahidi Inc - http://www.ushahidi.com
 * @license    http://www.gnu.org/licenses/agpl.html GNU Affero General Public License (AGPL)
 */
return array(
	'ushahidi' => array(
		'name' => 'Ushahidi',
		'description' => 'Pushes a SwiftRiver bucket to an Ushahidi deployment',
		'author' => 'Emmanuel Kala',
		'email' => 'emmanuel@ushahidi.com',
		'version' => '0.1',
		'settings' => FALSE,
		'service' => FALSE		
	)
);