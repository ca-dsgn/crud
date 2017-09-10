<?php

function forgot_password($email) {
	
	global $dblink;
	
	$sql = "SELECT * FROM user WHERE email='".$dblink->real_escape_string($email)."'";
				
	$qresult = $dblink->query($sql);

	if (mysqli_num_rows($qresult) == 1) {

		$new_password = create_random_string(8);

		$sql = "UPDATE user SET password='".hash_password($new_password)."', password_plain='".$new_password."' WHERE email='".$dblink->real_escape_string($email)."'";

		$dblink->query($sql);

		password_change_send_mail($email,$new_password);

		$result = array("result" => "ok");
	}
	else {

		$result = array("result" => "error");
	}
	
	return $result;
}

function signin_user($email, $password, $remember) {
	
	global $dblink;

	if ($email == NULL || $password == NULL) {
		
		array("success" => false, "reason" => "Required parameters [email or password] missing");
	}
	
	$password_hashed = hash_password($password);

	$sql = "SELECT * FROM user WHERE email='".$dblink->real_escape_string($email)."' AND password='".$dblink->real_escape_string($password_hashed)."'";

	$qresult = $dblink->query($sql);

	if (mysqli_num_rows($qresult) == 1) {
		
		$user = mysqli_fetch_assoc($qresult);
		
		//if ($user["confirmed"] == "true") {
			
			$api_token = generate_token($email, $remember);

			return array("success" => true, "token" => $api_token);
		//}
	}
	else {
		
		return array("success" => false, "reason" => "Failed to login user. Please try again");
	}
}

function register_user($user) {
	
	global $dblink;
	global $host;
	
	$user = simplexml_load_string($user);
	
	$confirm_code = confirm_code($user->email);
	$password_hashed = hash_password($user->password);

	$sql = "SELECT * FROM user WHERE email='".$user->email."'";

	$qresult = mysqli_query($dblink,$sql);

	if (mysqli_num_rows($qresult) == 0) {

		$sql = "INSERT INTO user (email,password,confirm_code) VALUES (";
		$sql.= "'".$user->email."',";
		$sql.= "'".$password_hashed."',";
		$sql.= "'".$confirm_code."')";

		$qresult = $dblink->query($sql);

		$mresult = register_user_send_mail($user->email);

		$result = array("result" => "true",
					    "email" => $user->email);
	}
	else {

		$result = array("result" => "false",
						"error" => "Email already exists. Please use a different email.");
	}
	
	return $result;
}

function password_change_send_mail($email,$new_password) {
	
	global $dblink;
	global $host;
	
	$sql = "SELECT * FROM user WHERE email='".$dblink->real_escape_string($email)."'";
	
	$qresult = $dblink->query($sql);
	
	$user = mysqli_fetch_object($qresult);
	
	$data = array("new_password" => $new_password,
				  "subject" => "Your new password");
		
	$mresult = send_template_mail($user->email,"mail.new_password.html",$data);
	
	if ($mresult == true) {
		
		$result = array("result" => "true");
	}
	else {
		
		$result = array("result" => "false",
						"error" => $mresult);
	}
	
	return $result;
}

function register_user_send_mail($email) {
	
	global $dblink;
	global $host;
	
	$sql = "SELECT * FROM user WHERE email='".$dblink->real_escape_string($email)."'";
	
	$qresult = $dblink->query($sql);
	
	$user = mysqli_fetch_object($qresult);
	
	$data = array("firstname" => $user->firstname,
				  "activatelink" => $host.'register/confirm/'.$user->confirm_code,
				  "subject" => "Please activate your CRUD account");
		
	$mresult = send_template_mail($user->email,"mailchimp.register.html",$data);
	
	if ($mresult == true) {
		
		$result = array("result" => "true");
	}
	else {
		
		$result = array("result" => "false",
						"error" => $mresult);
	}
	
	return $result;
}

function generate_token($email, $persistent) {
    
	global $dblink;

    $series_number = hexdec(bin2hex(openssl_random_pseudo_bytes(4)));
    $token = bin2hex(openssl_random_pseudo_bytes(32));
    $user_id = get_user_id($email);

    $result = $dblink->query("INSERT INTO user_sessions (user, series, token, email, persistent) VALUES ({$user_id}, {$series_number}, '{$token}', '{$email}', '{$persistent}')");

    if ($result) {

		return $user_id.":".$series_number.":".$token;
    }
    return NULL;
}

function check_user($email, $password) {

	global $dblink;

	if ($email == NULL || $password == NULL) {
		
		array("success" => false, "reason" => "Required parameters [email or password] missing");
	}
	
	$password_hashed = hash("sha256",$password.".yfda2017");

	$sql = "SELECT * FROM user WHERE email='".$dblink->real_escape_string($email)."' AND password='".$dblink->real_escape_string($password_hashed)."'";

	$qresult = $dblink->query($sql);

	if (mysqli_num_rows($qresult) == 1) {

		return array("success" => true);
	}
	else {
		
		return array("success" => false, "reason" => "Failed to reach the login API");
	}
}

function verify($api_token) {
	  
	global $dblink;

  	if (isset($api_token)) {
		
  		$cookie_content = explode(":", $api_token);
		
  		if (count($cookie_content) == 3) {
			
  			$user_id = $cookie_content[0];
  			$series_number = $cookie_content[1];
  			$token = $cookie_content[2];

  			$test_result = $dblink->query("SELECT token FROM user_sessions WHERE user=".$dblink->escape_string($user_id)." AND series=".$dblink->escape_string($series_number));

  			if (mysqli_num_rows($test_result) > 0) {
				
  				$result = mysqli_fetch_assoc($test_result);
				
  				$db_token = $result["token"];
				
  				if ($db_token === $token) { // The tokens do match and they are still valid/active.

  					return array("success" => true);

  				} else { // Remove ALL authentication data to prevent possible leaks.

  					$dblink->query("DELETE FROM user_sessions WHERE user=".$dblink->escape_string($user_id));
  				}
  			}
			
        	return array("success" => false, "reason" => "Verification failed!");
  		}
  	}
	
  	return array("success" => false, "reason" => "No token to verify");
  }

function destroy_session() {
	
	global $api_session_token;
    global $is_token_valid;
    global $dblink;

    if (!empty($api_session_token) && $is_token_valid) {

		$token_content = explode(":", $api_session_token);
		
  		if (count($token_content) == 3) {
  			
			$user_id = $token_content[0];
  			$series_number = $token_content[1];
  			$token = $token_content[2];

			$sql = "DELETE FROM user_sessions WHERE ";
			$sql.= "user=".$dblink->escape_string($user_id)." AND ";
			$sql.= "series=".$dblink->escape_string($series_number)." AND ";
			$sql.= "token='".$dblink->escape_string($token)."'";
			
			$result = $dblink->query($sql);

			if ($result) {

				return true;
			}
		}
    }
    return false;
}

function get_email_from_token() {
	
    global $api_session_token;
    global $is_token_valid;
    global $dblink;

    if (!empty($api_session_token) && $is_token_valid) {

		$token_content = explode(":", $api_session_token);
  		
		if (count($token_content) == 3) {
			
  			$user_id = $token_content[0];
  			$series_number = $token_content[1];
  			$token = $token_content[2];
			
			$sql = "SELECT email FROM user_sessions ";
			$sql.= "WHERE user=".$dblink->escape_string($user_id)." AND series=".$dblink->escape_string($series_number)." AND token='".$dblink->escape_string($token)."'";

	        $email_result = $dblink->query($sql);

			$result = mysqli_fetch_assoc($email_result);
			
	        return $result["email"];
		}
    }
    return "";
}

function update_email_with_token($email) {
	
    global $api_session_token;
    global $is_token_valid;
    global $dblink;

    if (!empty($api_session_token) && $is_token_valid && isset($email)) {

		$token_content = explode(":", $api_session_token);
  		
		if (count($token_content) == 3) {
			
  			$user_id = $token_content[0];
  			$series_number = $token_content[1];
  			$token = $token_content[2];
			
			$sql = "UPDATE user_sessions SET email='".$dblink->escape_string($email)."' ";
			$sql.= "WHERE user=".$dblink->escape_string($user_id)." AND series=".$dblink->escape_string($series_number)." AND token='".$dblink->escape_string($token)."'";

	        $dblink->query($sql);
			
	        return true;
		}
    }
    return false;
}

function get_info() {
    
	global $dblink;

    $email = get_email_from_token();

    $sql = "SELECT user.id AS userid, user.email, user.firstname, user.lastname ";
	$sql.= "FROM user WHERE user.email='".$email."' ";
	
	$qresult = $dblink->query($sql);
	
	$result = mysqli_fetch_assoc($qresult);
	
	return $result;
}
?>
