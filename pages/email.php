<?php

	global $urlparts;

	if (user_has_session()) {
		
		$html_results = getTemplate("email.html",array());
	}
	else {
		
		$html_results = getTemplate("login.html",array());
	}

	print $html_results;
?>