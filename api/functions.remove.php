<?php

	function remove_user($id) {
	
		global $api_session_token;
		global $is_token_valid;
		global $dblink;

		if (!empty($api_session_token) && $is_token_valid) {
		
			if (isset($id) and $id != "") {
				
				$sql = "DELETE FROM user WHERE id='".$dblink->real_escape_string($id)."'";
				
				$qresult = $dblink->query($sql);
				
				if ($qresult === true) {

					return array("success" => true);

				}
				else {

					return array("success" => false, "reason" => $dblink->error);
				}
			}
			else {
				
				return array("success" => false, "reason" => "No ID for user");
			}
		}
		else {
			
			return array("success" => false, "reason" => "Token invalid");
		}
	}
	
?>