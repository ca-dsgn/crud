<?php

	function create_user($email,$firstname,$lastname,$password) {
	
		global $api_session_token;
		global $is_token_valid;
		global $dblink;

		if (!empty($api_session_token) && $is_token_valid) {
		
			if (isset($email) and $email != "" and isset($password) and $password != "") {
				
				$sql = "SELECT * FROM user WHERE email='".$dblink->real_escape_string($email)."'";
				
				$qresult = $dblink->query($sql);
				
				if ($qresult->num_rows == 0) {
				
					$password_plain = $password;
					$password = hash_password($password);

					$sql = "INSERT INTO user (email,firstname,lastname,password,password_plain) VALUES ";
					$sql.= "(";
					$sql.= "'".$dblink->real_escape_string($email)."',";
					$sql.= "'".$dblink->real_escape_string($firstname)."',";
					$sql.= "'".$dblink->real_escape_string($lastname)."',";
					$sql.= "'".$dblink->real_escape_string($password)."',";
					$sql.= "'".$dblink->real_escape_string($password_plain)."'";
					$sql.= ")";

					$qresult = $dblink->query($sql);
					
					$id = $dblink->insert_id;

					if ($qresult === true) {

						return array("success" => true, "user" => array("id" => $id, "email" => $email, "password_plain" => $password_plain));

					}
					else {

						return array("success" => false, "reason" => $dblink->error);
					}
				}
				else {
					
					return array("success" => false, "reason" => "Email already exists");
				}
			}
			else {
				
				return array("success" => false, "reason" => "No email/password");
			}
		}
		else {
			
			return array("success" => false, "reason" => "Token invalid");
		}
	}

?>