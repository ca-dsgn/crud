<?php

	global $urlparts;

	if (user_has_session()) {
		
		$faqs = crud_api("/get/faqs",array());

		$html_results = getTemplate("faq.html",array("faqs" => $faqs));
	}
	else {
		
		$html_results = getTemplate("login.html",array());
	}

	print $html_results;
?>