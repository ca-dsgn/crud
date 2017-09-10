<?php

	$path_up = "../";

	include($path_up."php/helper.php");
	
	if (isset($_POST["action"])) {
		
		if (user_has_session()) {
		
			switch ($_POST["action"]) {
					
				case "create_user":
					
					$email = $_POST["email"];
					$firstname = $_POST["firstname"];
					$lastname = $_POST["lastname"];
					$password = $_POST["password"];
					
					$result = crud_api("/create/user",array("email" => $email,
															"firstname" => $firstname,
															"lastname" => $lastname,
															"password" => $password),true);
					
					if (isset($result["user"])) {
					
						$result["html"] = getTemplate("users.item.html",array("user" => $result["user"]));
					}
					break;
					
				case "search_users":
					
					$search_for = $_POST["search_for"];
					
					$result = crud_api("/get/users",array("search_for" => $search_for),true);
					
					if (isset($result["items"])) {
					
						$result["html_items"] = getTemplate("users.items.html",array("users" => $result));
						$result["html_paging"] = getTemplate("paging.html",array("items" => $result));
					}
					break;
					
				case "set_user":
					
					$id = $_POST["id"];
					$email = $_POST["email"];
					$firstname = $_POST["firstname"];
					$lastname = $_POST["lastname"];
					
					$result = crud_api("/set/user",array("id" => $id,
														 "email" => $email,
														 "firstname" => $firstname,
														 "lastname" => $lastname),true);
					
					if (isset($result["user"])) {
					
						$result["html"] = getTemplate("users.item.html",array("user" => $result["user"]));
					}
					break;
					
				case "remove_user":
					
					$id = $_POST["id"];
					
					$result = crud_api("/remove/user",array("id" => $id),true);
					break;
			}
		}
		else {
			
			$result = array("result" => "false",
							"error" => "Please signin");
		}
		
		print json_encode($result);
	}

?>