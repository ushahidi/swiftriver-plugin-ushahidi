# Ushahidi Plugin for SwiftRiver
A plugin to enable users to push a bucket to an Ushahidi deployment. This plugin is only compatible with  the 2.x track (version 2.4 or higher) of the Ushahidi platform. 
The drops are pushed to the configured deployment via a background job (cron job). 

## Installation
 * Activate the plugin from the __Plugins__ tab in the __Site Settings__ section

## Configuring the drops poster
The drops poster pushes buckets that have been configured to push their drops to an Ushahidi deployment.
To schedule the posting of drops to Ushahidi every day at midnight, add the following lines to your crontab:

    * 0 * * * cd <app home>; php5 index.php --uri=post2ushahidi >> application/logs/post2ushahidi.log 2>&1