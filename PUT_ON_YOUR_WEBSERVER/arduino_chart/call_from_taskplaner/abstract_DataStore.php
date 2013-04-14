<?PHP
/*
Project: Arduino simple charts
Author: Matthias Maderer
Date: April 2013

Links:
Description: www.edvler-blog.de/arduino-simple-chart-setup-howto-english
Installationguide: www.edvler-blog.de/arduino-simple-charts-diagramm-visualisierung-messwerte
GitHub: https://github.com/edvler/Arduino_simple_chart

Function of this script
This script call's the db_abstract_DataStore.sql file. 
In the db_abstract_DataStore.sql the DataStore_Value will be averaged to different time ranges.
The time ranges are one hour, one day (24h) and one week.

Howto:
1. Create a job in your taskplaner or on crontab calling this file to average the DataStore Table

*/


// include the database config
include("../database/db_config.php");

// debug on/off
$debug=true;

// execute a SQL script. The run_sql_file is defined in db_run_sql_file.php
$sql_file = run_sql_file("../database/db_abstract_DataStore.sql");	

// debug output: list the number of successfull sql statements
if ($debug==true) {
	print $sql_file["success"] . " SQL statements executed successfully from total " . $sql_file["total"] . " SQL statements<br>";
}

// return 0 on successfull and 1 on error
if(($sql_file["success"] - $sql_file["total"]) == 0) {
	return 0;
} else {
	return 1;
}

?>