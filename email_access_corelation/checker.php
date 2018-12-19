<?php

require_once "library.php";

$shortopts = "f:p:e:";
$options = getopt($shortopts);

if (!isset($options["f"]) || !isset($options["p"]) || !isset($options["e"])){
	echo "Usage: checker.php -f PATH -p (int) password_column -e (int) email_column\n";
	die();
}

if (file_exists($options["f"])){
	$bn = basename($options["f"], ".csv");
	$handle = fopen($options["f"], "r");
	$result = fopen(__DIR__ . DIRECTORY_SEPARATOR . "results" . 
							  DIRECTORY_SEPARATOR . $bn . "_results.txt", "a");

	if ($handle && $result) {

		$flag = false;

	    while (($line = fgetcsv($handle)) !== false) {
	    	$password = $line[$options["p"]];
	    	$email = $line[$options["e"]];

	    	//resume from
	    	if ($email === "mautinkkka@seznam.cz"){
	    		$flag = true;
	    	}

	    	if($flag){

		    	debug("Checking email: " . $email . " with pass: " . $password);

		    	$output = trim(shell_exec("php get.php -h " . $password));

		    	if(!empty($output)){
		    		debug("Hash found!");
		    		debug("Writing result to file.");
		    		fwrite($result, $email . ":" . $output . "\n");
		    	}
	    	}
	    }
	}

	fclose($handle);
	fclose($result);
}

?>