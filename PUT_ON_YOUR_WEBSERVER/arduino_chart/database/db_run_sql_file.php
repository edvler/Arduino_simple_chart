<?PHP
// function to execute a .sql file with more sql statements
function run_sql_file($location){
    //load file
    $commands = file_get_contents($location);

    //delete comments
    $lines = explode("\n",$commands);
    $commands = '';
    foreach($lines as $line){
        $line = trim($line);
        if( $line && (substr($line,0,2) != '--')){
            $commands .= $line . "\n";
        }
    }

    //convert to array
    $commands = explode(";", $commands);

    //run commands
    $total = $success = 0;
    foreach($commands as $command){
        if(trim($command)){
			$query_return = (@mysql_query($command)==false ? 0 : 1);
            if ($query_return == 0) {
				die ("Error: <br>" . $command . "<br>" . mysql_error());
			}	
		
			$success += $query_return;
            $total += 1;
        }
    }

    //return number of successful queries and total number of queries found
    return array(
        "success" => $success,
        "total" => $total
    );
}
?>