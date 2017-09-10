var GET = get_GET();
var ever_pushed = false;
var initial_url = location.href;
var lang = "de";

$(document).ready(function(e) {
	
	set_language();
	link_listeners();
	foundation_listeners();
	session_listeners();
	new_password_listeners();
	language_listeners();
	set_active_by_url();
	
	window_popstate();
	
	bindings();
});

function set_active_by_url() {
	
	$(".main a").removeClass("active");
	
	$(".main a").each(function() {
		
		if (location.pathname.indexOf($(this).attr("href")) != -1) {
			
			$(this).addClass("active");
		}
	});
}

function set_language() {
	
	lang = $("#language .active").hasClass("de") ? "de" : "en";
}

function language_listeners() {
	
	$(document).on("click","#language a", function() {
		
		$("#language a").removeClass("active");
		
		lang = $(this).attr("class");
		
		$(this).addClass("active");
		
		set_cookie("lang",lang);
		
		location.reload();
	});
}

function session_listeners() {
	
	$(document).on("click",".logout", function() {
		
		user_logout();
	});
	
	$(document).on("click","#login .button", function() {
		
		modal_id = $(this).parents(".modal").attr("id");
		
		user_login($("#login #email").val(),$("#login #password").val());
	});
	
	$(document).on("keydown","#login input", function(e) {
		
		email = $("#login #email").val();
		password = $("#login #password").val();
		
		if (e.keyCode == 13) {
			
			if (email != "" && password != "") {
				
				user_login(email,password);
			}
			else if (email != "") {
				
				$("#login #password").focus();
			}
		}
	});
}

function user_logout() {
	
	$.ajax({
		
		url: host + "php/ajax.php",
		type: "POST",
		dataType: 'json',
		data: {

			"action": "signout"
		},
		success: function(data) {

			$(".page").removeClass("login");
			$("section").html(data.login);
			
		}
	});
}

function new_password_listeners() {
	
	$(document).on("click","#forgot .button",function() {
		
		email = $("#forgot input").val();
		
		if (email != "") {
			
			$.ajax({

				url: host + "php/ajax.php",
				type: "POST",
				dataType: 'json',
				data: {
					"action": "send_new_password",
					"email": email
				},
				success: function(data) {

					if (data.result == "ok") {
					
						$("#forgot fieldset").html("<h2>" + langs[lang].forgot.headline_new_send + "</h2><p>" + langs[lang].forgot.description_new_send + "</p>");
					}
					else {
						
						alert("The email you entered isn't in our database.");
					}
				}
			});
		}
	});
}

function link_listeners() {
	
	$(document).on("click","a[href]",function(e) {

		if (typeof $(this).attr('href') !== 'undefined'
				&& $(this).attr('href') !== false
				&& $(this).attr('href').indexOf("javascript:") == -1
				&& $(this).attr('href').indexOf(host + "/files/") == -1
				&& $(this).attr('target') === undefined) {
			
			e.preventDefault();

			get_page_add_to_history($(this).text(),$(this).attr("href"));
		}
	});
}

function get_page_add_to_history(title,url) {
	
	if (history && history.pushState) {

		history.pushState({page: url}, title, url);

		ever_pushed = true;
		
		set_active_by_url();

		get_page_by_request_uri(location.pathname + location.search);
	}
}

function window_popstate() {
	
	$(window).bind("popstate",function(e) {

		// Ignore inital popstate that some browsers fire on page load
    	var initial_pop = (!ever_pushed && location.href == initial_url);
		
		ever_pushed = true;
		
		if (initial_pop) return;

		//if (location.href.indexOf("#") == -1) {

			get_page_by_request_uri(location.pathname + location.search);
		//}
	});
}

function foundation_bindings() {
	
	$(document).foundation();
}

function foundation_listeners() {
	
	Foundation.Abide.defaults.patterns['password_length'] = /^(.){8,72}$/;
	Foundation.Abide.defaults.validateOn = 'manual';
}

function modal_listeners() {
	
	$(document).on("click",".modal .close", function() {
		
		hide_box($(this).parents(".modal").attr("id"));
	});
	
	$(document).on("click",".modal", function(e) {
		
		if ($(e.target).parents(".box").length == 0) {
			
			hide_box($(this).attr("id"));
		}
	});
}

function get_template(template,data,callback) {

	$.ajax({

		url: host + "php/ajax.php",
		type: "POST",
		dataType: 'json',
		data: {

			"action": "get_template",
			"template": template,
			"data": data
		},
		success: callback
	});
}

function show_box(data) {

	get_template("modal",data,function(response) {

		$("body").append(response.html);
	
		foundation_bindings();

		top_value = "50%";

		$("#" + data.id + " .box").animate({

			top: top_value
		},500);
		
		$("#" + data.id).fadeIn(500);
		$("#" + data.id + " #email").focus();
	});
}

function animate_box(data) {
	
	switch (data.type) {
			
		case "show_waiter":
			
			$("#" + data.id + " " + data.hide).slideUp(300);
			$("#" + data.id + " " + data.hide).after(get_waiter());
			break;
			
		case "hide_waiter":
			
			$("#" + data.id + " " + data.hide).slideUp(300);
			$("#" + data.id + " " + data.hide).after(get_waiter());
			break;
		
		case "shake":
			
			$("#" + data.id + " " + data.hide).slideDown(300);
			$("#" + data.id + " .waiter").remove();
			
			$("#" + data.id).animate({
				left: "-=20"
			},{
				duration: 100,
				queue: true
			}).animate({
				left: "+=40"
			},{
				duration: 100,
				queue: true
			}).animate({
				left: "-=40"
			},{
				duration: 100,
				queue: true
			}).animate({
				left: "+=40"
			},{
				duration: 100,
				queue: true
			}).animate({
				left: "-=20"
			},{
				duration: 100,
				queue: true
			});
			break;
	}
}

function hide_box(id) {
	
	$("#" + id).fadeOut(500,function() {
		
		$(this).remove();
	});
}

function user_login(email,password) {
	
	data = {"id": "login",
			"type": "show_waiter",
		    "hide": "fieldset"}
	
	animate_box(data);
	
	$.ajax({
		
		url: host + "php/ajax.php",
		type: "POST",
		dataType: 'json',
		data: {

			"action": "signin",
			"request": location.pathname + location.search,
			"email": email,
			"password": password
		},
		success: function(data) {

			if (data.success) {

				hide_box("login");
				
				login = true;
				
				load_script(host + "js/all_login.js");
				
				$(".page").addClass("login");
				$(".page .dark .medium-6:last").html(data.account);
				$(".page .dark .main").html(data.navi);
				
				get_page_add_to_history(langs[lang].breadcrumbs["/users"].name,"/users");
			}
			else {

				data = {"id": "login",
					   "type": "shake",
					   "hide": "fieldset"}

				animate_box(data);
				
				$("#login").addClass("error");
			}
		}
	});
}

function bindings() {
	
	foundation_bindings();
	
	try {
		
		//Set bindings here
		
	} catch(err) {
		
		
	}
}

function get_GET() {
   
   var GET = new Array();
   
   if(location.search.length > 0) {
      var get_param_str = location.search.substring(1, location.search.length);
      var get_params = get_param_str.split("&");
      for(i = 0; i < get_params.length; i++) {
         var key_value = get_params[i].split("=");
         if(key_value.length == 2) {
            var key = key_value[0];
            var value = key_value[1];
            GET[key] = value;
         }
      }
   }
   return(GET);
}

function get_domain() {
	
	if (window.location.hostname.indexOf("crud.dev") == -1) {
		
		domain_parts = window.location.hostname.split(".")
		
		return domain_parts[1] + "." + domain_parts[2];
	}
	else {
		
		return false;
	}
}


function get_waiter() {
	
	html = '<div class="waiter">';
	html+= '<div class="waiter-circle1 waiter-circle"></div>';
	html+= '<div class="waiter-circle2 waiter-circle"></div>';
	html+= '<div class="waiter-circle3 waiter-circle"></div>';
	html+= '<div class="waiter-circle4 waiter-circle"></div>';
	html+= '<div class="waiter-circle5 waiter-circle"></div>';
	html+= '<div class="waiter-circle6 waiter-circle"></div>';
	html+= '<div class="waiter-circle7 waiter-circle"></div>';
	html+= '<div class="waiter-circle8 waiter-circle"></div>';
	html+= '<div class="waiter-circle9 waiter-circle"></div>';
	html+= '<div class="waiter-circle10 waiter-circle"></div>';
	html+= '<div class="waiter-circle11 waiter-circle"></div>';
	html+= '<div class="waiter-circle12 waiter-circle"></div>';
	html+= '</div>';
	
	return html;
}

function get_cookie(name) {
	
	var cookiename = name + "="; 
	var ca = document.cookie.split(';'); 
	
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(cookiename) == 0) return c.substring(cookiename.length,c.length);
	}
	return null;
}

function set_cookie(name, value) {

	ablauf = new Date();
	zeit = ablauf.getTime() + (14 * 24 * 60 * 60 * 1000); //14 days
	ablauf.setTime(zeit);

	document.cookie = name + "=" + value + "; expires=" + ablauf.toGMTString() + "; " + (get_domain() ? "domain=." + get_domain() + "; " : "") +  "path=/";
}

function scroll_to(position) {

	if ($(window).scrollTop() != position) {
		
		$('html, body').animate({ 
		   scrollTop: position}, 
		   500, 
		   "easeOutQuint"
		);	
	}
}

function responsive_listeners() {
	
	responsive_design();
	
	$(window).resize(function() {
		
		responsive_design();
	});
}

function responsive_design() {
	
	window_width = $(this).width();
		
	/* Desktop */
	if (window_width < 1200) {
		
		$("body").removeClass("desktop");
		
		/* Tablet */
		if (window_width < 980) {
		
			$("body").addClass("tablet");
			
			/* Smartphone */
			if (window_width <= 770) {
				
				$("body").addClass("mobile");
			}
			else {
				
				$("body").removeClass("mobile");
			}
		}
		else {
			
			$("body").removeClass("tablet");
			$("body").removeClass("mobile");
		}
	}
	else {
		
		$("body").addClass("desktop");
	}
}


function get_page_by_request_uri(uri) {
	
	$(".main li").removeClass("active");
	
	$(".main li").each(function() {
		
		if (uri.indexOf($(this).find("a").attr("href")) != -1) {
			
			$(this).addClass("active");
		}
	});
	
	$("section").addClass("wait");
	
	//ga('send', 'pageview', {'page': uri});
	
	$("section").append(get_waiter());
	
	$.ajax({
		
		url: host + "php/ajax.php",
		type: "POST",
		dataType: 'json',
		data: {
			
			"action": "get_data",
			"request": uri
		},
		success: function(data) {
			
			GET = get_GET();
			
			$("section").removeClass("wait");
			$("section").html(data.result);
			$(".breadcrumb ul").replaceWith(data.breadcrumb);
			scroll_to(0);
			bindings();
		}
	});
}

function load_script(url, callback) {
    // adding the script tag to the head as suggested before
    var head = document.getElementsByTagName('head')[0];
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = url;

    if(!callback) callback = function(){};

    // bind the event to the callback function 
    if(script.addEventListener) {
      script.addEventListener("load", callback, false); // IE9+, Chrome, Firefox
    } 
    else if(script.readyState) {
      script.onreadystatechange = callback; // IE8
    }

    // fire the loading
    head.appendChild(script);
}