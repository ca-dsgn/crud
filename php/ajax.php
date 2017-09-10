<?php

	$path_up = "../";

	include($path_up."php/helper.php");
	
	if (isset($_POST["action"])) {
		
		switch ($_POST["action"]) {
			
			case "send_new_password":
			
				$email = $_POST["email"];
				
				$result = crud_api("/user/forgot_password",array("email" => $email));
				break;
				
			case "signin":
				
				$email = $_POST["email"];
				$password = $_POST["password"];
				$request = $_POST["request"];
			
				$result = crud_api("/user/signin",array("email" => $email, "password" => $password));
				
				if ($result["success"] == "true") {
					
					$_SESSION["created"] = time();
					$_SESSION["last_activity"] = time();
					$_SESSION["api_token"] = $result["token"];
					
					$user_info = get_user_info();
					
					//Common user information
					$_SESSION["email"] = $email;
					$_SESSION["userid"] = $user_info["userid"];
					
					set_cookie("firstname", $user_info["firstname"]);
					set_cookie("lastname", $user_info["lastname"]);
					
					//Set a variable for user to check if he wants to be stayed as logged in
					$remember_me = true;
					
					if ($remember_me === "true") { // Create a secure auth cookie for a persistent 30 day login
					
						set_cookie("auth", $result["token"]);
					}
					
					$result = array("success" => "true",
									"account" => getTemplate("main.account.html",array()),
								    "navi" => get_navi($request));
				}
				break;
				
			case "signout":
				
				session_destroy();
				
				$result = array("result" => "true",
							    "login" => getTemplate("login.html",array()));
				break;
				
			case "register_user_send_mail":
				
				$email = $_POST["email"];
				
				$result = crud_api("/user/register_send_mail",array("email" => $email));
				break;
				
			case "get_data":
			
				$request = $_POST["request"];
				
				parse_str(parse_url($request, PHP_URL_QUERY), $_GET);
				
				$urlparts = get_urlparts($request);
				
				$result = array("result" => get_contents($urlparts));
				break;
								   
			case "get_template":
				
				$template = $_POST["template"];
				$data = $_POST["data"];
								   
				$html = getTemplate($template.".html",$data);

				$result = array("result" => "ok",
								"html" => $html);
				break;			   
		}
		
		print json_encode($result);
	}

?>