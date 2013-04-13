<?php
/*
Project: Arduino simple charts
Author: Matthias Maderer
Date: April 2013

Links:
www.edvler-blog.de
www.github.com/edvler


Function of this script
This script holds all configuration which belongs to the MySQL Setup.

Howto:
1. Create a user in your MySQL which can create a schema (maybe you use phpmyadmin or MySQL Workbench)
2. Setup the connection parameters in the db_config.php script
3. Change the $setup parameter to true!
4. Call this script in your browser. If the script finished you will see how many SQL commands are executed and how many executed successfully.
5. Change the $setup back to false!

FINISHED DB Setup. You are ready to store data!

*/


include("db_run_sql_file.php");

// WARNING!!!!!!!
// setting $setup = true will drop the existing database and create a emtpy schema. 
// don't use if you already collected data that you don't want to loose!
$setup = false;

// connection parameters - see header for a little howto
$mysql_server = "127.0.0.1";
$mysql_port = "3306";
$mysql_user = "arduino_chart_ro";
$mysql_password = "display";
$mysql_db_name = "ardu_chart";

// connect to MySQL Server
mysql_connect($mysql_server . ":" . $mysql_port, $mysql_user, $mysql_password) or die("MySQL ERROR: " . mysql_error());

// check if the database setup should be done
if ($setup == true) {

	// create Database schema
	mysql_query("DROP SCHEMA IF EXISTS " . $mysql_db_name) or die("MySQL DB SETUP ERROR: " . mysql_error());
	mysql_query("CREATE SCHEMA IF NOT EXISTS " . $mysql_db_name . " DEFAULT CHARACTER SET latin1 COLLATE latin1_german1_ci ;") or die("MySQL DB SETUP ERROR: " . mysql_error());
	// select the created Database
	mysql_select_db($mysql_db_name) or die("MySQL DB SETUP ERROR: " . mysql_error());
	
	// run a sql file to create tables
	$sql_file = run_sql_file("db_schema.sql");	
	
	// print informations about the table creation
	print $sql_file["success"] . " SQL statements executed successfully from total " . $sql_file["total"] . " SQL statements<br>";
	
	// stop here
	die ("Database setup end. If no error occured please set the \$setup variable to false in the file db_config.php");
} else {

	// select the Database
	mysql_select_db($mysql_db_name) or die("MySQL ERROR: " . mysql_error());
}
?>