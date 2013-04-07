<?php
include("db_run_sql_file.php");

// WARNING!!!!!!!
// setting $setup = true will drop the existing database and create a emtpy schema. 
// don't use if you already collected data that you don't want to loose!
$setup = false;


$mysql_server = "127.0.0.1";
$mysql_port = "3306";
$mysql_user = "arduino_chart_ro";
$mysql_password = "display";
$mysql_db_name = "ardu_chart";

mysql_connect($mysql_server . ":" . $mysql_port, $mysql_user, $mysql_password) or die("MySQL ERROR: " . mysql_error());



if ($setup == true) {
	mysql_query("DROP SCHEMA IF EXISTS " . $mysql_db_name) or die("MySQL DB SETUP ERROR: " . mysql_error());
	mysql_query("CREATE SCHEMA IF NOT EXISTS " . $mysql_db_name . " DEFAULT CHARACTER SET latin1 COLLATE latin1_german1_ci ;") or die("MySQL DB SETUP ERROR: " . mysql_error());
	mysql_select_db($mysql_db_name) or die("MySQL DB SETUP ERROR: " . mysql_error());
	
	$sql_file = run_sql_file("db_schema.sql");	
	
	print $sql_file["success"] . " SQL statements executed successfully from total " . $sql_file["total"] . " SQL statements<br>";
	
	die ("Database setup end. If no error occured please set the \$setup variable to false in the file db_config.php");
} else {
	// connect to database
	mysql_select_db($mysql_db_name) or die("MySQL ERROR: " . mysql_error());
}
?>