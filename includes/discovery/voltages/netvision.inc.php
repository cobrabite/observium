<?php

global $valid_sensor;

if ($device['os'] == "netvision")
{
  for($i = 1; $i <= 3 ;$i++)
  {
    $volt_oid   = "1.3.6.1.4.1.4555.1.1.1.1.3.3.1.2.$i";
    $descr = "Input Phase $i";
    $volt = snmp_get($device, $volt_oid, "-Oqv");
    $type = "netvision";
    $divisor = 10;
    $index = $i;
    $lowlimit = 0;
    $limit = NULL;

    discover_sensor($valid_sensor, 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $volt);
  }

  for($i = 1; $i <= 3 ;$i++)
  {
    $volt_oid   = "1.3.6.1.4.1.4555.1.1.1.1.4.4.1.2.$i";
    $descr = "Output Phase $i";
    $volt = snmp_get($device, $volt_oid, "-Oqv");
    $type = "netvision";
    $divisor = 10;
    $index = 100+$i;
    $lowlimit = 0;
    $limit = NULL;

    discover_sensor($valid_sensor, 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $volt);
  }
}

?>