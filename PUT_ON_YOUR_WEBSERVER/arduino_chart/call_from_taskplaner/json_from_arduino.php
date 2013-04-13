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
1. Create a job in your taskplaner or crontab calling this file to collect data from your arduinos
2. Don't forget to add a id and a url
3. Thr url have to return a JSON string. For a example please open the file json_test_data.file under the webroot folder.


Function of this script
This file loads content from four different data tables depending on the required time range.

@param id {Integer} The id of the arduino
@param url {String} The URL of the arduino which should be accsed for data
*/

// config
include('../database/db_config.php');
$debug = true;

// parse commandline arguments
if ($argc > 0)
{
  for ($i=1;$i < $argc;$i++)
  {
    parse_str($argv[$i],$tmp);
    $_REQUEST = array_merge($_REQUEST, $tmp);
  }
}

// check if commandline parameters exists
if(!(array_key_exists('id',$_REQUEST)) || !(array_key_exists('url',$_REQUEST))) {
	die "please call this script with the following parameters:\n -id : Unique id for the Arduino\n -url : Url of the Arduino JSON website\n example: json_form_arduino.php id=1 url=\"http://192.168.0.123\"";
}

$arduino_id = $_REQUEST["id"];
$arduino_url = $_REQUEST["url"];

// debug output
if ($debug==true) {
	print "arduino id: " . $arduino_id . "\n";
	print "arduino url: " . $arduino_url . "\n\n";
}

// the arduino url
// if the url is called, it has to return a pure JSON string!
// For example use url="http://<WEBSERVER_IP>/arduino_chart/json_test_data.php";
$json_string = file_get_contents($arduino_url);
if ($debug==true) {
	print "RAW retrived string:\n <br>";
	print $json_string;
}

// JSON string decode
$json_array = json_decode($json_string,true);
if ($debug==true) {
	print "<br><br>After json_decode:\n <br>";
	var_dump($json_array);

	print "<br><br>Write to DB:\n";
}

// check if the Arduino ID exists in the database
$query = "SELECT Arduino_device_id as c from Arduino_device where Arduino_device_ID=" . $arduino_id;
$result = mysql_execute_query($query);
if (mysql_num_rows($result) == 0) {
	// if the Arduino ID don't exists in the database, insert it
	$query = 'INSERT INTO Arduino_device (Arduino_device_ID, Arduino_device_Name) values (' . $arduino_id . ',"NO_NAME_GIVEN")';
	mysql_execute_query($query);
}

// loop through the Category array 
foreach ($json_array['DATA'] as $key => $value) {
	if ($debug==true) {
		echo "<br>Group: \n" . $key;
	}
	$category_id = -1;

	// check if the Category_NAME exists in the database
	$query = "SELECT Category_id as id from Category where Category_NAME=\"" . $key . "\"";
	$result = mysql_execute_query($query);
	if (mysql_num_rows($result) == 0) {
		// if the name is unknown insert a new Category
		$query = 'INSERT INTO Category (Category_NAME,Arduino_device_id) values ("' . $key . '",' . $arduino_id . ')';
		mysql_execute_query($query);

		// select the id of the new Category
		$query = "SELECT Category_id as id from Category where Category_NAME=\"" . $key . "\"";
		$result = mysql_execute_query($query);		
	}
	
	$category_id = mysql_result($result,0,"id");

	// loop trough the Sensor of the Category
	foreach ($value as $key2 => $value2) {
		if ($debug==true) {
			echo "<br>Sensor: " . $key2 . " Value: " . $value2 . "\n";
		}

		// check if the Sensor exists in the database
		$query = "SELECT Sensor_id as id from Sensor where Sensor_NAME=\"" . $key2 . "\"";
		$result = mysql_execute_query($query);
		if (mysql_num_rows($result) == 0) {
			// if the Sensor is unknown insert a new Sensor
			$query = 'INSERT INTO Sensor (Sensor_NAME,Category_ID) values ("' . $key2 . '",' . $category_id . ')';
			mysql_execute_query($query);

			// select the id of the new sensor
			$query = "SELECT Sensor_id as id from Sensor where Sensor_NAME=\"" . $key2 . "\"";
			$result = mysql_execute_query($query);		
		}
		
		$sensor_id = mysql_result($result,0,"id");
		
		// insert the value from the Sensor into the DataStore table
		$query = 'insert into DataStore (Sensor_ID,DataStore_VALUE,DataStore_TIME) values (' . $sensor_id . ',' . $value2 . ',current_timestamp())';
		mysql_execute_query($query);
	} // foreach Sensor
} // foreach Category

return 0; // exit script


function mysql_execute_query($sql_query) {
	$result = mysql_query($sql_query)
	or die ("<br><font color=\"red\">MYSQL ERROR in json_from_arduino.php: <br> " . $sql_query. "<br>" . mysql_error() . "</font>");
	return $result;
}

?>