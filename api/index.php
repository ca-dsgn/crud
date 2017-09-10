<?php

	$path_up = "../";

	if (isset($_GET["key"]) and $_GET["key"] != "") {

		require_once($path_up."php/helper.php");
		require_once($path_up."api/functions.main.php");
		require_once($path_up."api/functions.user.php");

		$is_valid = check_key($_GET["key"]);

		if (!$is_valid) {

			/* Key is invalid */
			$response = array("status"	=> "error",
							  "message"	=> "Invalid API key");
		}
		else {

			/* Key is valid check for token! */
			$api_session_token = isset($_GET["token"]) ? $_GET["token"] : NULL;
			$is_token_valid = verify($api_session_token)["success"];

			/* main actions */
			switch ($urlparts[1]) {

				case "user":

					switch($urlparts[2]) {
							
						case "forgot_password":
							
							$response = forgot_password(
								isset($_GET["email"]) ? $_GET["email"] : NULL
							);
							break;

						case "signin":

							$response = signin_user(
								isset($_GET["email"]) ? $_GET["email"] : NULL,
								isset($_GET["password"]) ? $_GET["password"] : NULL,
								isset($_GET["persistent"]) && $_GET["persistent"] == "true" ? true : false
							);
							break;
							
						case "register_send_mail":
							
							$response = register_user_send_mail(
								isset($_GET["email"]) ? $_GET["email"] : NULL
							);
							break;

						case "verify":

							$response = verify(isset($_GET["token"]) ? $_GET["token"] : NULL);
							break;

						case "info":

							$response = get_info();
							break;

						case "update_email":

							$response = update_email_with_token(isset($_GET["email"]) ? $_GET["email"] : NULL);
							break;

						case "destroy":

							$response = destroy_session();
							break;

						case "check":

							$response = check_user(
								isset($_GET["email"]) ? $_GET["email"] : NULL,
								isset($_GET["password"]) ? $_GET["password"] : NULL
							);
							break;

						default:

							$response = array("status"	=> "error",
											  "message"	=> "No action for user");
							break;
					}
					break;

				case "get":

					require_once($path_up."api/functions.get.php");

					/* get actions */
					switch ($urlparts[2]) {

						case "faqs":

							$response = get_faqs();
							break;

						case "user":

							$response = get_user(isset($_GET["id"]) ? $_GET["id"] : NULL);
							break;

						case "users":

							$response = get_users(isset($_GET["page"]) ? $_GET["page"] : 1,
												  isset($_GET["items_per_page"]) ? $_GET["items_per_page"] : 10,
												  isset($_GET["search_for"]) ? $_GET["search_for"] : null);
							break;
							
						default:

							$response = array("status"	=> "error",
							  				  "message"	=> "No action for get");
							break;
					}
					break;

				case "set":

					require_once($path_up."api/functions.set.php");

					/* set actions */
					switch ($urlparts[2]) {
							
						case "user":
							
							$response = set_user(isset($_GET["id"]) ? $_GET["id"] : null,
												 isset($_GET["email"]) ? $_GET["email"] : null,
												 isset($_GET["firstname"]) ? $_GET["firstname"] : null,
												 isset($_GET["lastname"]) ? $_GET["lastname"] : null);
							break;
							
						default:

							$response = array("status"	=> "error",
							  				  "message"	=> "No action for set");
							break;
					}
					break;

				case "create":

					require_once($path_up."api/functions.create.php");

					/* set actions */
					switch ($urlparts[2]) {

						case "user":

							$response = create_user(isset($_GET["email"]) ? $_GET["email"] : null,
													isset($_GET["firstname"]) ? $_GET["firstname"] : null,
													isset($_GET["lastname"]) ? $_GET["lastname"] : null,
												    isset($_GET["password"]) ? $_GET["password"] : null);
							break;
							
						default:

							$response = array("status"	=> "error",
							  				  "message"	=> "No action for create");
							break;
					}
					break;

				case "remove":

					require_once($path_up."api/functions.remove.php");

					/* set actions */
					switch ($urlparts[2]) {
							
						case "user":

							$response = remove_user(isset($_GET["id"]) ? $_GET["id"] : null);
							break;

						default:

							$response = array("status"	=> "error",
							  				  "message"	=> "No action for remove");
							break;
					}
					break;
					
				default:
					
					$response = array("status"	=> "error",
							  		  "message"	=> "Please pass an action like /user/login");
					break;
			}
		}
	}
	else {

		/* No key */
		$response = array("status"	=> "error",
						  "message"	=> "No API key passed to api");
	}

	if (isset($_GET["resultType"]) and $_GET["resultType"] == "array") {

		print_r($response);
	}
	else {

		print json_encode($response);
	}

?>
