<?php
/**
Project: Arduino simple charts
Author: Matthias Maderer
Date: April 2013

Links:
Description: www.edvler-blog.de/arduino-simple-chart-setup-howto-english
Installationguide: www.edvler-blog.de/arduino-simple-charts-diagramm-visualisierung-messwerte
GitHub: www.github.com/edvler/arduino_simple_charts


Howto:
1. Choose the right timezone if Europe/Berlin is not the right one

This file loads content from four different data tables depending on the required time range.
The DataStore table can contain million data points. Since we are loading OHLC data and
MySQL has no concept of first and last in a data group, we have extracted groups by hours, days
and week into separate tables. If we were to load a line series with average data, we wouldn't
have to do this.

@param callback {String} The name of the JSONP callback to pad the JSON within
@param start {Integer} The starting point in JS time
@param end {Integer} The ending point in JS time
*/

// config
include('../database/db_config.php');
$debug = true;

$time_zone_mysql = new DateTimeZone('Europe/Berlin');
//$time_zone_mysql = new DateTimeZone('UTC');
//$time_zone_mysql = new DateTimeZone('Europe/London');

// get the parameters from url
// example URL: http://127.0.0.1/arduino_chart/db_access.php?callback=test
if (array_key_exists('callback',$_GET)) {
	$callback = $_GET['callback'];
} else {
	die('Please provide a callback parameter!');
}
if (!preg_match('/^[a-zA-Z0-9_]+$/', $callback)) {
	die('Invalid callback name');
}

if (array_key_exists('start',$_GET)) {
	$start = $_GET['start'];
} else {
	$start = 0;
}
if ($start && !preg_match('/^[0-9]+$/', $start)) {
	die("Invalid start parameter: $start");
}

if (array_key_exists('end',$_GET)) {
	$end = $_GET['end'];
} else {
	$end = time() * 1000;
}
if ($end && !preg_match('/^[0-9]+$/', $end)) {
	die("Invalid end parameter: $end");
}

// set timezone data
date_default_timezone_set('UTC');
$range = $end - $start;

$startTime = new DateTime(gmstrftime('%Y-%m-%d %H:%M:%S', $start / 1000));
$endTime = new DateTime(gmstrftime('%Y-%m-%d %H:%M:%S', $end / 1000));
$startTime->setTimezone($time_zone_mysql);
$endTime->setTimezone($time_zone_mysql);


// how many datasets have to exist in table to use the table
// formular: count sensors * how many datasets
$sql = "select count(*) as c from DataStore";
$result = mysql_execute_query($sql);
$count_DataStore = mysql_result($result,0,"c");

$sql = "select count(*) as c from DataStore_hour";
$result = mysql_execute_query($sql);
$count_DataStore_hour = mysql_result($result,0,"c");

$sql = "select count(*) as c from DataStore_day";
$result = mysql_execute_query($sql);
$count_DataStore_day = mysql_result($result,0,"c");

$sql = "select count(*) as c from DataStore_week";
$result = mysql_execute_query($sql);
$count_DataStore_week = mysql_result($result,0,"c");

$sql = "select count(*) as c from Sensor";
$result = mysql_execute_query($sql);
$count_sensor = mysql_result($result,0,"c");


$how_many_ds_from_each_sensor = 5;

// find the right table
if (($range < 12 * 3600 * 1000) || ($count_DataStore_hour < ($count_sensor * $how_many_ds_from_each_sensor)) ) { // two days range loads minute data
	$table = 'DataStore';
} elseif ($range < 31 * 24 * 3600 * 1000 || ($count_DataStore_day < ($count_sensor * $how_many_ds_from_each_sensor)) ) { // one month range loads hourly data
	$table = 'DataStore_hour';	
} elseif ($range < 12 * 31 * 24 * 3600 * 1000 || ($count_DataStore_week < ($count_sensor * $how_many_ds_from_each_sensor)) ) { // one year range loads daily data
	$table = 'DataStore_day';
} else { // greater range loads monthly data
	$table = 'DataStore_week';
}

// build the sql string
$sql = "
select 
	Sensor_NAME, 
	unix_timestamp(DataStore_TIME) * 1000 as datetime, 
	DataStore_VALUE
from 
	$table inner join Sensor on (Sensor.Sensor_ID = $table.Sensor_ID)
where 
	DataStore_TIME >= str_to_date('" . $startTime->format('Y-m-d H:i:s') . "','%Y-%m-%d %H:%i:%s')
	and DataStore_TIME <= str_to_date('" . $endTime->format('Y-m-d H:i:s') . "','%Y-%m-%d %H:%i:%s')
order by 
	Sensor.Sensor_Order,
	Sensor.Sensor_Name,
	DataStore_TIME
";


// debug output 
if ($debug == true) {
	$timestamp = time();
	$srv_date = date("d.m.Y",$timestamp);
	$srv_time = date("H:i",$timestamp);

	echo "/* server date: " . $srv_date . "
server time utc: " . $srv_time . "
parameter start: " . $start . "
parameter start format: " . $startTime->format('Y-m-d H:i:s') . "
parameter end: " . $end . "
parameter end format: " . $endTime->format('Y-m-d H:i:s') . "\n\n\n";

	echo "choosen table: " . $table . "\n";
	echo "dataset count DataStore: " . $count_DataStore . "\n";
	echo "dataset count DataStore_hour: " . $count_DataStore_hour . "\n";
	echo "dataset count DataStore_day: " . $count_DataStore_day . "\n";
	echo "dataset count DataStore_week: " . $count_DataStore_week . "\n";
	echo "sensor count: " . $count_sensor . "\n";
	echo "formular for minimum datasets needed = count sensors * how_many_ds_from_each_sensor\n";
	echo "minimum datasets needed to use the table: " . $count_sensor * $how_many_ds_from_each_sensor . "\n\n\n";

	echo "/* SQL string: " . $sql . "*/\n\n\n";
}

// execute query
$result = mysql_query($sql) or die("MySQL ERROR: " . mysql_error());




// build json output

/*
example JSON string
[
	{"name":"Sensor 1",
		"dataGrouping": { 
			"enabled" : "false" 
		},
		"data":[
			[1358855941000,0.48],
			[1358942341000,1]
		]
	},
	{"name":"Sensor 2",
		"dataGrouping": { 
			"enabled" : "false" 
		},
		"data":[
			[1350302342000,16.35]
		]
	}
];

*/


$last_sensor="";
$rows[] = "[";
while ($row = mysql_fetch_assoc($result)) {
	extract($row);

	// a new sensor needs a new header
	if ($last_sensor != $Sensor_NAME) {
		$anzahl = count($rows);
		
		if ($anzahl>1) {
			// remove the , sign on the last dataset
			$rows[$anzahl-1] = substr($rows[$anzahl-1],0,strlen($rows[$anzahl-1])-1);
			$rows[]="]},";
		}
	
		// place the header
      	$rows[] = "{\"name\":\"" . $Sensor_NAME . "\",";
		$rows[] = "\"dataGrouping\": { \"enabled\" : \"false\" },";
		$rows[] = "\"data\":[";
	}

	$last_sensor = $Sensor_NAME;
	
	// list sensor data
	$rows[] = "[$datetime,$DataStore_VALUE],";
}

// remove the , on the last dataset
$anzahl = count($rows);
$rows[$anzahl-1] = substr($rows[$anzahl-1],0,strlen($rows[$anzahl-1])-1);
$rows[]="]}]";

// print JSON data
header('Content-Type: text/javascript');
if ($debug == true) {
	echo $callback . "(" . join("\n", $rows) . ")"; // better for reading
} else {
	echo $callback . "(" . join("", $rows) . ")"; // faster transfer
}

// function for execute MySQL querys
function mysql_execute_query($sql_query) {
	$result = mysql_query($sql_query)
	or die ("<br><font color=\"red\">MYSQL ERROR in db_access.php: <br> " . $sql_query. "<br>" . mysql_error() . "</font>");
	return $result;
}
?>
