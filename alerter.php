#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2013 Adam Armstrong
 *
 */

chdir(dirname($argv[0]));

include("includes/defaults.inc.php");
include("config.php");
include("includes/definitions.inc.php");
include("includes/functions.php");
include("includes/polling/functions.inc.php");
include("html/includes/functions.inc.php");
include_once($config['install_dir'] . "/includes/pear/Mail/Mail.php");


$start = utime();

$options = getopt("h:m:i:n:r:s::d::a::qV");

if (isset($options['V']))
{
  print_message("Observium ".$config['version']);
  exit;
}

if (isset($options['s']))
{
  // User has asked for spam. LETS MAKE THE SPAM. (sends alerts even if they have already been sent)
  $spam = TRUE;
}

if (!isset($options['q']))
{
  print_message("%gObservium v".$config['version'].PHP_EOL."%WAlerter\n%n", 'color');
}

if ($options['h'] == "all")  { $where = " "; $doing = "all"; }
elseif ($options['h'])
{
  if (is_numeric($options['h']))
  {
    $where = "AND `device_id` = '".$options['h']."'";
    $doing = $options['h'];
  }
  else
  {
    $where = "AND `hostname` LIKE '".str_replace('*','%',mres($options['h']))."'";
    $doing = $options['h'];
  }
}

if (!$where)
{
  print_message("USAGE:
poller.php [-drqV] [-i instances] [-n number] [-m module] [-h device]

EXAMPLE:
-h <device id> | <device hostname wildcard>  Poll single device
-h odd                                       Poll odd numbered devices  (same as -i 2 -n 0)
-h even                                      Poll even numbered devices (same as -i 2 -n 1)
-h all                                       Poll all devices
-h new                                       Poll all devices that have not had a discovery run before

-i <instances> -n <number>                   Poll as instance <number> of <instances>
                                             Instances start at 0. 0-3 for -n 4

OPTIONS:
 -h                                          Device hostname, id or key odd/even/all/new.
 -i                                          Poll instance.
 -n                                          Poll number.
 -q                                          Quiet output.
 -V                                          Show version and exit.

DEBUGGING OPTIONS:
 -r                                          Do not create or update RRDs
 -d                                          Enable debugging output.
 -m                                          Specify module(s) (separated by commas) to be run.

%rInvalid arguments!%n", 'color');
  exit;
}

if (isset($options['d']))
{
  echo("DEBUG!\n");
  $debug = TRUE;
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  ini_set('log_errors', 1);
  ini_set('error_reporting', E_ALL ^ E_NOTICE);
} else {
  $debug = FALSE;
#  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
#  ini_set('error_reporting', 0);
}

echo("Starting alerter run:\n\n");
$polled_devices = 0;
if (!isset($query))
{
  $query = "SELECT `device_id` FROM `devices` WHERE `disabled` = 0 $where ORDER BY `device_id` ASC";
}

$alert_rules = cache_alert_rules();
$alert_assoc = cache_alert_assoc();

foreach (dbFetch($query) as $device)
{
   $device = dbFetchRow("SELECT * FROM `devices` WHERE `device_id` = '".$device['device_id']."'");

   humanize_device($device);

   // Overwrite the autogenerated base_url with external_url when we can't guess.
   ## FIXME -- Do this automatically when we know we're not running in a webserver.
   $config['base_url'] = $config['external_url'];

   process_alerts($device);

}

?>
