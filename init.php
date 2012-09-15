<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Initialization for the Ushahidi push plugin
 */


/**
 * Register callback function to be executed when the plugin
 * is activated for the first time
 */
Swiftriver_Plugins::register('ushahidipush', array('Ushahidipush_Installer', 'install'));

/**
 * Add navigation link
 */
Swiftriver_Event::add("swiftriver.dashboard.nav", function() {
	// Get the event data
	$dashboard_tabs = & Swiftriver_Event::$data;
	
	$dashboard_tabs[] = array(
		'id' => 'ushahidi-deployments-link',
		'url' => '/application/ushahidi',
		'label' => __('Ushahidi')
	);
});

/**
 * Route for setting up ushahidi deployments
 */
Route::set('ushahidi_deployments', '<account>/<directory>/ushahidi(/<action>(/<id>))',
    array(
    	'directory' => '(application)'
    ))
    ->defaults(array(
	    'controller' => 'ushahidi',
	    'action' => 'index',
	    'id' => '\d+'
	));
