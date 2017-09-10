<?php

function check_key($key) {

	global $dblink;

	$qresult = $dblink->query("SELECT * FROM api_key WHERE apikey='".$dblink->real_escape_string($key)."'");

	if (mysqli_num_rows($qresult) == 1) {

		return true;
	}
	else {

		return false;
	}
}

function hash_password($password) {
	
	return hash("sha256",$password.".elsevier2017");
}

function confirm_code($email) {
	
	return hash("sha256",$email.".elsevier");
}

?>
