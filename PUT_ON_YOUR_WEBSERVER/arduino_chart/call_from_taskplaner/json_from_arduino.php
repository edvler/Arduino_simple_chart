<?php
// config
include('../database/db_config.php');
$debug = true;


if ($argc > 0)
{
  for ($i=1;$i < $argc;$i++)
  {
    parse_str($argv[$i],$tmp);
    $_REQUEST = array_merge($_REQUEST, $tmp);
  }
}

$arduino_id = $_REQUEST["id"];
$arduino_url = $_REQUEST["url"];

if ($debug==true) {
	print "arduino id: " . $arduino_id . "\n";
	print "arduino url: " . $arduino_url . "\n\n";
}

// the arduino url
// if the url is called, it has to return a pure json string!
//$arduino_url = "http://firewall01:81/heizung/json_test_data.php";

$json_string = file_get_contents($arduino_url);
if ($debug==true) {
	print "RAW retrived string:\n <br>";
	print $json_string;
}

$json_array = json_decode($json_string,true);
if ($debug==true) {
	print "<br><br>After json_decode:\n <br>";
	var_dump($json_array);

	print "<br><br>Write to DB:\n";
}



// check if the Arduino Device exists in the database
$query = "SELECT Arduino_device_id as c from Arduino_device where Arduino_device_ID=" . $arduino_id;
$result = mysql_execute_query($query);
if (mysql_num_rows($result) == 0) {
	$query = 'INSERT INTO Arduino_device (Arduino_device_ID, Arduino_device_Name) values (' . $arduino_id . ',"NO_NAME_GIVEN")';
	mysql_execute_query($query);
}

foreach ($json_array['DATA'] as $key => $value) { // This will search in the 2 jsons
	if ($debug==true) {
		echo "<br>Group: \n" . $key;
	}
	$category_id = -1;

	// check if the Category exists in the database
	$query = "SELECT Category_id as id from Category where Category_NAME=\"" . $key . "\"";
	$result = mysql_execute_query($query);
	if (mysql_num_rows($result) == 0) {		
		$query = 'INSERT INTO Category (Category_NAME,Arduino_device_id) values ("' . $key . '",' . $arduino_id . ')';
		mysql_execute_query($query);

		$query = "SELECT Category_id as id from Category where Category_NAME=\"" . $key . "\"";
		$result = mysql_execute_query($query);		
	}
	
	$category_id = mysql_result($result,0,"id");

	foreach ($value as $key2 => $value2) {
		if ($debug==true) {
			echo "<br>Sensor: " . $key2 . " Value: " . $value2 . "\n";
		}

		// check if the Sensor exists in the database
		$query = "SELECT Sensor_id as id from Sensor where Sensor_NAME=\"" . $key2 . "\"";
		$result = mysql_execute_query($query);
		if (mysql_num_rows($result) == 0) {		
			$query = 'INSERT INTO Sensor (Sensor_NAME,Category_ID) values ("' . $key2 . '",' . $category_id . ')';
			mysql_execute_query($query);

			$query = "SELECT Sensor_id as id from Sensor where Sensor_NAME=\"" . $key2 . "\"";
			$result = mysql_execute_query($query);		
		}
		
		$sensor_id = mysql_result($result,0,"id");
		
		// insert data into DataStore
		
		$query = 'insert into DataStore (Sensor_ID,DataStore_VALUE,DataStore_TIME) values (' . $sensor_id . ',' . $value2 . ',current_timestamp())';
		mysql_execute_query($query);
	}
}

return 0;


function mysql_execute_query($sql_query) {
	$result = mysql_query($sql_query)
	or die ("<br><font color=\"red\">MYSQL ERROR in json_from_arduino.php: <br> " . $sql_query. "<br>" . mysql_error() . "</font>");
	return $result;
}

?>