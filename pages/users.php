<?php

	global $urlparts;

	if (user_has_session()) {
			
		if (isset($urlparts[1]) and $urlparts[1] == "edit") {

			if (isset($_GET["id"])) {

				$user = crud_api("/get/user",array("id" => $_GET["id"]));

				$array = array("user" => $user);
			}
			else {

				$array = array();
			}

			$html_results = getTemplate("users.edit.html",$array);
		}
		else {

			$users = crud_api("/get/users",array("page" => isset($_GET["page"]) ? $_GET["page"] : 1));

			$html_results = getTemplate("users.html",array("users" => $users));
		}
	}
	else {
		
		$html_results = getTemplate("login.html",array());
	}

	print $html_results;
?>