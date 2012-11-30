# Ushahidi Plugin for SwiftRiver
The Ushahidi plugin for SwiftRiver is a tool that enables SwiftRiver users to push their buckets to an Ushahidi deployment. It provides a web UI where a user can:

* Manage the deployments that they would like to push drops to
* Configure a bucket to push its drops to a specific deployment and report category

This plugin is only compatible with 2.5+ of the [Ushahidi platform](https://github.com/ushahidi/Ushahidi_Web).


## Installing and using the plugin
 * To install/activate the plugin, go to the the __Plugins__ tab in the __Site Settings__ section

   ![Activating the plugin](https://dl.dropbox.com/u/2635815/enable-plugin.png)

	Once activated, an __Ushahidi__ tab will be added on your dashboard. It is from this tab (_Ushahidi_) that you add the Ushahidi deployments. The deployments
	listed on this tab will be available to all your buckets.

	![User dashboard - Ushahidi tab](https://dl.dropbox.com/u/2635815/dashboard-tab-ushahidi.png)

	__NOTE__: To add a deployment, the [SwiftRiver plugin for Ushahidi](https://github.com/ushahidi/ushahidi-plugin-swiftriver) must be installed on the deployment(s) 
	you want to push drops do

* Next, select the bucket (from the buckets listing) you want to push to Ushahidi and go to the __Bucket Settings__ page. On the bucket's page, select the __Ushahidi__ tab and configure the deployment and report category you to push to.

	![Configuring a bucket to push its drops to Ushahidi](https://dl.dropbox.com/u/2635815/bucket-settings-ushahidi-tab.png)

	__NOTE__: A bucket can only push to one report category


## Configuring the drops poster
The drops poster pushes buckets that have been configured to push their drops to an Ushahidi deployment.
To schedule the posting of drops to Ushahidi every day at midnight, add the following lines to your crontab:

    * 0 * * * cd <app home>; php5 index.php --task=ushahidi:push >> application/logs/post2ushahidi.log 2>&1


## Contributing
To contribute to this plugin, please fork the repository and submit a pull request (tagged against an issue #).


## Copyright and license
Copyright 2012 Ushahidi Inc

Licensed under Affero General Public License (AGPL), version 3.0: http://www.gnu.org/licenses/agpl.html