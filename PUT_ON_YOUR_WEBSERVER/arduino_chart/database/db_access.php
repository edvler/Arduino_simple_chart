<?php
/**
* This file loads content from four different data tables depending on the required time range.
* The stockquotes table containts 1.7 million data points. Since we are loading OHLC data and
* MySQL has no concept of first and last in a data group, we have extracted groups by hours, days
* and months into separate tables. If we were to load a line series with average data, we wouldn't
* have to do this.
*
* @param callback {String} The name of the JSONP callback to pad the JSON within
* @param start {Integer} The starting point in JS time
* @param end {Integer} The ending point in JS time
*/

// config
include('../database/db_config.php');
$debug = true;

// get the parameters from url
// example URL: http://127.0.0.1/arduino_chart/db_access.php?callback=test&_=1365321560263 [HTTP/1.1 200 OK 32ms]
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

//if (!$end) $end = time() * 1000;




// set timezone data
date_default_timezone_set('UTC');

//$utc_time= new DateTimeZone('UTC');
$de_time = new DateTimeZone('Europe/Berlin');
//$en_time = new DateTimeZone('Europe/London');
$range = $end - $start;

$startTime = new DateTime(gmstrftime('%Y-%m-%d %H:%M:%S', $start / 1000));
$endTime = new DateTime(gmstrftime('%Y-%m-%d %H:%M:%S', $end / 1000));
$startTime->setTimezone($de_time);
$endTime->setTimezone($de_time);


// find the right table
// two days range loads minute data
if ($range < 12 * 3600 * 1000) {
$table = 'DataStore';
// one month range loads hourly data
} elseif ($range < 31 * 24 * 3600 * 1000) {
$table = 'DataStore_hour';
// one year range loads daily data
} elseif ($range < 15 * 31 * 24 * 3600 * 1000) {
$table = 'DataStore_day';
// greater range loads monthly data
} else {
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
	DataStore_TIME >= '" . $startTime->format('Y-m-d H:i:s') . "'
	and DataStore_TIME <= '" . $endTime->format('Y-m-d H:i:s') . "'
order by 
	Sensor.Sensor_Order,
	Sensor.Sensor_Name,
	DataStore_TIME
";

if ($debug == true) {
	$timestamp = time();
	$srv_date = date("d.m.Y",$timestamp);
	$srv_time = date("H:i",$timestamp);

	echo "/* server date: " . $srv_date . "
server time utc: " . $srv_time . "
parameter start: " . $start . "
parameter start format: " . $startTime->format('Y-m-d H:i:s') . "
parameter end: " . $end . "
parameter end format: " . $endTime->format('Y-m-d H:i:s') . "*/\n";

	echo "/* SQL string: " . $sql . "*/";
}

// execute query
$result = mysql_query($sql) or die("MySQL ERROR: " . mysql_error());




// build json output

/*
example JSON string

jQuery18206418166322484766_1365283688694([
{"name":"FB",
"dataGrouping": { "enabled" : "false" },
"data":[
[1358855941000,0.48],
[1358942341000,1]
]},
{"name":"Vorl_Matt",
"dataGrouping": { "enabled" : "false" },
"data":[
[1350302342000,16.35]
]}
]);

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
	echo $callback . "(" . join("\n", $rows) . ")";
} else {
	echo $callback . "(" . join("", $rows) . ")";
}
?>
