<?php defined('SYSPATH') OR die('No direct script access.');
// 
// Routing setup and registration of event callbacks
// 

/**
 * Register callback function to be executed when the plugin
 * is activated for the first time
 */
Swiftriver_Plugins::register('ushahidipush', array('Ushahidipush_Installer', 'install'));

/**
 * Add navigation link on the user's dashboard
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
 * Add navigation link in the bucket settings section
 */
Swiftriver_Event::add("swiftriver.bucket.settings.nav", function(){
	list($base_url, $active) = Swiftriver_Event::$data;

	$active_css = ($active === "ushahidi") ? "active" : "";
	$link_url = $base_url.'/settings/ushahidi';
	
	// Build the HTML for the nav item
	$nav_html = '<li class="touchcarousel-item %s"><a href="%s">%s</a></li>';

	// Display
	echo sprintf($nav_html, $active_css, $link_url, __("Ushahidi"));
});

// Add drop to the push log
Swiftriver_Event::add('swiftriver.bucket.droplet.add', array('Model_Deployment_Push_Log', 'add_entry'));

// Remove drop from the push log
Switriver_Event::add('swiftriver.bucket.droplet.remove', array('Model_Deployment_Push_Log', 'remove_entry'));

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
