<?php

/**
 * This script is called when a process is supposed to be started from
 * the WordPress interface, from the commandline or using a scheduler
 * such as WordPress' built in scheduler, or a cronjob. The context of
 * the commandline does matter, as it needs to be able to require the
 * correct files from the correct directories. Script needs to be run
 * from within the directory below.
 * "wordpress/wp-content/plugins/vizzit-analytics-for-wordpress/"
 *
 * This script is also triggered by the "Process Manually" button in
 * the admin interface in WordPress, under the "Schedule" page.
 *
 * It uses the following script to run it, and should be emulated by
 * any crontab scripts that run it. Especially navigating to the
 * correct directory before running the "php ..." command.
 *
 * $dir = dirname(__FILE__) . '/';
 * exec("cd $dir; php vizzit-analytics.cron.php MANUAL > /dev/null 2>/dev/null &1");
 * 
 * Example usage: "$ php vizzit-analytics.cron.php MANUAL"
 */

// Map supported methods for starting the process
$exec_methods = array(
	'MANUAL',
	'SCHEDULER',
);

// Ensure we get execution method, and that they are supported
if(!isset($argv[1]) || !in_array($argv[1], $exec_methods))
{
	$supported = implode('/', $exec_methods);
	die("Usage: php vizzit-analytics.cron.php [$supported]" . PHP_EOL);
}

// Require the necessary files
require_once('../../../wp-load.php');
require_once('vizzit_analytics_admin.class.php');

// Initiate the Core and start a process
$vaCore = new Vizzit_Analytics_Core();
$vaCore->debug_log('Starting from cron script');
$vaCore->vizzit_analytics_process($argv[1]);
