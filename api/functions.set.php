<?php

	function set_user($id,$new_email,$new_firstname,$new_lastname) {
	
		global $api_session_token;
		global $is_token_valid;
		global $dblink;

		if (!empty($api_session_token) && $is_token_valid) {
		
			if (isset($new_email) and $new_email != "") {
				
				$sql = "SELECT * FROM user WHERE email='".$dblink->real_escape_string($new_email)."' AND id!='".$dblink->real_escape_string($id)."'";
				
				$qresult = $dblink->query($sql);
				
				if ($qresult->num_rows == 0) {
					
					$password_plain = $new_password;
					$password = hash_password($new_password);

					$sql = "UPDATE user SET ";
					$sql.= "email='".$dblink->real_escape_string($new_email)."',";
					$sql.= "firstname='".$dblink->real_escape_string($new_firstname)."',";
					$sql.= "lastname='".$dblink->real_escape_string($new_lastname)."' ";
					$sql.= "WHERE id='".$dblink->real_escape_string($id)."'";

					$qresult = $dblink->query($sql);

					if ($qresult === true) {

						return array("success" => true, "user" => array("id" => $id, "email" => $new_email));

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