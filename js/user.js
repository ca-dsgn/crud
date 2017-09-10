$(document).ready(function() {
	
	user_management_listeners();
	paging_listners();
});

function user_management_listeners() {
	
	$(document).on("click","#user_new .save",function() {
		
		data = {"action": "create_user",
				"email": $("#email").val(),
				"firstname": $("#firstname").val(),
				"lastname": $("#lastname").val(),
				"password": $("#password").val()
		};
		
		make_user_call(data,function(response) {
			
			if (response.success == true) {

				get_page_add_to_history(langs[lang].breadcrumbs["/users"].name,"/users");
			}
			else {
				
				alert(response.reason);
			}
		});
	});
	
	
	$(document).on("click","#user_edit .save",function() {
		
		data = {"action": "set_user",
				"id": GET["id"],
				"email": $("#email").val(),
				"firstname": $("#firstname").val(),
				"lastname": $("#lastname").val()
		};
		
		make_user_call(data,function(response) {
			
			if (response.success == true) {

				get_page_add_to_history(langs[lang].breadcrumbs["/users"].name,"/users");
			}
			else {
				
				alert(response.reason);
			}
		});
	});
	
	$(document).on("click","#users .remove",function() {
		
		if (confirm(langs[lang].user.confirm_delete)) {
			
			var li = $(this).parents("li[data-id]");
			
			data = {"action": "remove_user",
					"id": $(li).attr("data-id")
			};
			
			make_user_call(data,function(response) {
			
			if (response.success == true) {

				$(li).slideUp(300, function() {
					
					$(this).remove();
				});
			}
			else {
				
				alert(response.reason);
			}
		});
		}
	});
	
	$(document).on("keydown","#user_search input",function(e) {
		
		if (e.keyCode == 13) {
		
			user_search($(this).val());
		}
	});
	
	$(document).on("click","#user_search .button",function() {
		
		user_search($("#user_search input").val());
	});
}

function user_search(search_for) {
	
	data = {"action": "search_users",
			"search_for": search_for
	};
	
	$("#users .items").html(get_waiter());

	make_user_call(data,function(response) {

		if (response.success == true) {

			$("#users .items").replaceWith(response.html_items);
			
			if ($("#users").next().length > 0) {
				
				$("#users").next().replaceWith(response.html_paging);
			}
			else {
				
				$("#users").after(response.html_paging);
			}
		}
		else {

			alert(response.reason);
		}
	});
}

function paging_listners() {
	
	$(document).on("click",".paging .next",function() {
		
		if ($(".paging .active").parents("li").next().is(":last-child")) {
			
			$(".paging li a[href]").first().click();
		}
		else {
			
			$(".paging .active").parents("li").next().find("a").click();
		}
	});
	
	$(document).on("click",".paging .prev",function() {
		
		if ($(".paging .active").parents("li").prev().is(":first-child")) {
			
			$(".paging li a[href]").last().click();
		}
		else {
			
			$(".paging .active").parents("li").prev().find("a").click();
		}
	});
}

function make_user_call(data,callback) {
	
	$.ajax({

		url: host + "php/user.php",
		type: "POST",
		dataType: 'json',
		data: data,
		success: callback
	});
}