<?php

require_once "db.php";

function isValidMd5($text){
    return preg_match('/^[a-f0-9]{32}$/', $text);
}

function splitHashPlain($line){
	$h = [];
	$h["md5"] = isValidMd5(substr($line, 0, 32)) ? substr($line, 0, 32) : false;
	$h["plain"] = substr($line, 32, strlen($line));
	$h["type"] = "MD5";

	if(!$h["md5"]){
		return false;
	} else {
		return $h;
	}
}

function splitHashPlainArr($arr){
	$h = [];
	$h["md5"] = isValidMd5($arr[0]) ? $arr[0] : false;
	$h["plain"] = $arr[1];
	$h["type"] = "MD5";

	if(!$h["md5"]){
		return false;
	} else {
		return $h;
	}
}

function saveHash($mysqli, $arr){
	$stmt = $mysqli->prepare("INSERT INTO hashes (hash, plain, type) VALUES (?, ?, ?)");
	$stmt->bind_param("sss", $arr["md5"], $arr["plain"], $arr["type"]);
	$stmt->execute();
	$stmt->close();
}

$path = __DIR__ . DIRECTORY_SEPARATOR . "HASHES" . DIRECTORY_SEPARATOR;
$hashFiles = scandir($path);
$hashFiles = array_diff($hashFiles, array('.', '..'));


///prerusovane davkovani///
$k;

foreach($hashFiles as $key => $file){
	if ($file === "411_056_found_hash_plain.txt"){ // soubor u ktereho to skoncilo //410 055 neni vubec ulozen
		$k = $key;
	}
}

$hashFiles = array_slice($hashFiles, $k-1);

///prerusovane davkovani///


foreach($hashFiles as $hFile){

	echo "[DEBUG] - Working on file: " . $hFile . "\n";
	$won = fopen($path . "workedon.txt", "a");
	fwrite($won, $hFile);
	fclose($won);

	$handle = fopen($path . $hFile, "r");
	$totallines = count(file($path . $hFile, FILE_SKIP_EMPTY_LINES));
	$cnt = 0;

	if ($handle) {
	    while (($line = fgets($handle)) !== false) {
	        $codt = substr_count($line, ":");

	        switch($codt){
	        	case 0:
	        			$arr = splitHashPlain($line);

	        			if ($arr){
	        				saveHash($mysqli, $arr);
	        			}

	        			break;

	        	case 1: 
	        			$arr = split(":", $line);
	        			$arr = splitHashPlainArr($arr);

	        			if ($arr){
	        				saveHash($mysqli, $arr);
	        			}

	        			break;

	        	default: 
	        			echo "[DEBUG] - Invalid hash line. File ". $hFile . "\n";
	        			continue 2;
	        }

	        $cnt++;

	        echo "[DEBUG] - Line number " . $cnt . " / " . $totallines . "\n";
	    }

    	fclose($handle);
	} else {
	    echo "[DEBUG] - Error opening hash file. \n";
	} 
}





?>