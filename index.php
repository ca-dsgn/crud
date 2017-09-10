<?php

	$path_up = "";

	include($path_up."php/helper.php");
	
	$urlparts = get_urlparts();

	if ($domain == "crud.dev") {
		
		get_one_css();
		
		get_one_js("all");
		get_one_js("all_login");
	}

	$data = array("language" => "de",
				  "cookie" => $_COOKIE,
				  "langs" => $langs,
				  "host" => $host,
				  "request" => $_SERVER['REQUEST_URI'],
				  "has_session" => user_has_session(),
				  "meta" => generate_meta_information($urlparts),
				  "content" => get_contents($urlparts));

	print getTemplate("main.html",$data);
	
?>

