<?php

	function get_users($page,$items_per_page,$search_for) {
		
		global $dblink;
		
		$sql = "SELECT user.* FROM user ";
		
		if (isset($search_for) and $search_for != "") {
			
			$sql.= "WHERE (";
			$sql.= "user.email LIKE '%".$dblink->real_escape_string($search_for)."%' OR ";
			$sql.= "user.firstname LIKE '%".$dblink->real_escape_string($search_for)."%' OR ";
			$sql.= "user.lastname LIKE '%".$dblink->real_escape_string($search_for)."%'";
			$sql.= ")";
		}
		
		$sql.= "ORDER BY user.created DESC";
		
		$limit = " LIMIT ".$dblink->real_escape_string($items_per_page)." OFFSET ".($items_per_page*($page-1));
	
		$qresult = $dblink->query($sql.$limit);
		$qresult_all = $dblink->query($sql);

		$items = array();
		
		while($item = mysqli_fetch_assoc($qresult)) {
			
			array_push($items,$item);
		}
		
		$result = array("success" => true,
						"total" => $qresult_all->num_rows,
					    "page" => $page,
						"search_for" => $search_for,
					    "items_per_page" => $items_per_page,
					    "items" => $items);
		
		return $result;
	}

	function get_user($id) {
		
		global $dblink;
		
		$sql = "SELECT user.* FROM user WHERE user.id=".$dblink->real_escape_string($id);
	
		$qresult = $dblink->query($sql);

		$items = array();
		
		$item = mysqli_fetch_assoc($qresult);
		
		$result = array("success" => true,
						"item" => $item);
		
		return $result;
	}

	function get_faqs() {
		
		global $dblink;
		
		$sql = "SELECT * FROM faq";
	
		$qresult = $dblink->query($sql);

		$items = array();
		
		while($item = mysqli_fetch_assoc($qresult)) {
			
			array_push($items,$item);
		}
		
		$result = array("success" => true,
					    "items" => $items);
		
		return $result;
	}

?>