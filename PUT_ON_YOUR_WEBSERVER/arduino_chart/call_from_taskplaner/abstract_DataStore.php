<?PHP
include("../database/db_config.php");
$debug=false;

$sql_file = run_sql_file("../database/db_abstract_DataStore.sql");	

if ($debug==true) {
	print $sql_file["success"] . " SQL statements executed successfully from total " . $sql_file["total"] . " SQL statements<br>";
}

if(($sql_file["success"] - $sql_file["total"]) == 0) {
	return 0;
} else {
	return 1;
}

?>