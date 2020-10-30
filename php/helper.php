<?php

	ini_set('session.use_only_cookies', 1);

	session_start();
	
	global $path_up;
		
	require $path_up.'php/vendor/autoload.php';

	use MatthiasMullie\Minify;
		
	$domain = $_SERVER['HTTP_HOST'];

	$host = $_SERVER["HTTP_HOST"] != "" ? get_protocol().$_SERVER["HTTP_HOST"]."/" : ""; // Standard host;
	
	$dbconf = parse_ini_file($path_up."php/inc/credentials.ini", true);
	$dblink = connect_db();

	$langs = json_decode(file_get_contents($path_up."js/langs.json"));

	$urlparts = get_urlparts();
	
	$cookie_expire = time() + (24 * 60 * 60) * 30; //30 days
	$app_folder = "";

	$api_key = "182b2cdb500ff44b08e61246b41eb6f6883bcb911544b58f4628e6518e1f69ef";

	function get_one_css() {
		
		global $path_up;
		
		cache_less();
		
		$minifier = new Minify\CSS();
		
		$minifier->add($path_up."css/jquery.jscrollpane.css");
		$minifier->add($path_up."css/foundation.min.css");
		$minifier->add($path_up."css/slick.css");
		$minifier->add($path_up."css/entypo.css");
		$minifier->add($path_up."css/less.css");
		
		$minifier->minify($path_up."css/all.css");
	}

	function get_one_js($type) {
		
		global $path_up;
		
		$minifier = new Minify\JS();
		
		switch ($type) {
				
			case "all_login":
			
				$minifier->add($path_up."js/slick.min.js");
				$minifier->add($path_up."js/user.js");
				break;
				
			default:
				
				$minifier->add($path_up."bower_components/jquery/dist/jquery.js");
				$minifier->add($path_up."js/jquery-ui.min.js");
				$minifier->add($path_up."js/jquery.ui.touch-punch.min.js");
				$minifier->add($path_up."js/jquery.mousewheel.js");
				$minifier->add($path_up."js/jquery.jscrollpane.min.js");
				$minifier->add($path_up."js/foundation.min.js");
				$minifier->add($path_up."js/header.js");
				break;
		}
		
		
		$minifier->minify($path_up."js/".$type.".js");
	}

	function cache_less() {
		
		global $path_up;
				
		$less = new lessc;
		
		$less_css = $less->compileFile($path_up."css/index.less");
		
		$handle = fopen($path_up."css/less.css","w");
			
		fwrite($handle,$less_css);
		
		fclose($handle);
	}

	function get_protocol() {
		
		if(!empty($_SERVER['pTP_X_FORWARDED_PROTOCOL'])) {
			
			return $_SERVER['HTTP_X_FORWARDED_PROTOCOL']."://";
			
		} else if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
			
			return $_SERVER['HTTP_X_FORWARDED_PROTO']."://";
		}
		else {
			
			return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";
		}
	}

	function crud_api($route, $params = NULL, $needsToken = false, $put_data = NULL) {

		global $api_key;
		global $host;

		$url = $host."api".$route.(strpos($route,"?") !== false ? '&' : '?')."key=".$api_key;

		if ($params) {
			
			foreach ($params as $key => $value) {

				$url .= "&".$key."=".urlencode($value);
			}
		}

		if ($needsToken and isset($_SESSION["api_token"])) {
			
			$url .= "&token=".$_SESSION["api_token"];
		}

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $put_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$cookies = '';
		
		foreach ($_COOKIE as $name=>$value) {
			
			if ($name != "PHPSESSID") {
				
				if ($cookies) {

					$cookies.= ';';
				}
				$cookies.= $name.'='.addslashes($value);
			}
		}
		
		curl_setopt ($ch, CURLOPT_COOKIE, $cookies);

		$result = curl_exec($ch);

		curl_close($ch);

		$response = json_decode($result, true);

		return $response;
	}

	function get_urlparts($request_uri=NULL) {

		global $host;
		global $app_folder;

		if ($request_uri == NULL) {

			$request_uri = $_SERVER['REQUEST_URI'];
		}

		$request_uri = substr($request_uri,1,strlen($request_uri));

		if (strpos($request_uri,"?") !== false) {

			$request_uri = substr($request_uri,0,strpos($request_uri,"?"));
		}
		/* Remove folder from request uri */
		$request_uri = preg_replace("/".str_replace("/","\/",$app_folder)."/","",$request_uri,1);

		$result = array();

		if ($request_uri != "") {

			$urlparts = explode("/", $request_uri);

			foreach($urlparts as $urlpart) {

				array_push($result,$urlpart);
			}
		}

		return $result;
	}
	
	function get_content($URL) {
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $URL);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	function innerXML($xml) {
		
		$innerXML= '';
		foreach (dom_import_simplexml($xml)->childNodes as $child) {
			
			$innerXML .= $child->ownerDocument->saveXML( $child );
		}
		return $innerXML;
	}
	
	function create_random_string($length) {

        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz023456789";
        srand((double) microtime() * 1000000);
        $i = 0;
        $pass = '';

        while ($i <= $length) {
            $num = rand() % 60;
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }

        return $pass;
	}
	
	function pretty_date($datestr='') {
		
		$now = time();
		$date = strtotime($datestr);
		$d = $now-$date;
		if( $d < 60 ) {
			$d = round($d);
			return 'vor '.($d==1?'einer Sekunde':$d.' Sekunden');
		}
		$d = $d/60;
		if( $d < 12.5 ) {
			$d = round($d);
			return 'vor '.($d==1?'einer Minute':$d.' Minuten');
		}
		switch( round($d/15) ) {
			case 1:
				return 'vor einer viertel Stunde';
			case 2:
				return 'vor einer halben Stunde';
			case 3:
				return 'vor einer dreiviertel Stunde';
		}
		$d = $d/60;
		if( $d < 6 ) {
			$d = round($d);
			return 'vor '.($d==1?'einer Stunde':$d.' Stunden');
		}
		if( $d < 36 ) {
			// ein Tag beginnt um 5 Uhr morgens
			$day_start = 5;
			if( date('j',($now-$day_start*3600)) == date('j',($date-$day_start*3600)) )
				$r = 'heute';
			elseif( date('j',($now-($day_start+24)*3600)) == date('j',($date-$day_start*3600)) )
				$r = 'gestern';
			else
				$r = 'vorgestern';
			$hour_date = intval(date('G',$date)) + (intval(date('i',$date))/60);
			$hour_now = intval(date('G',$now)) + (intval(date('i',$now))/60);
			if( $hour_date>=22.5 || $hour_date<$day_start ) {
				$r = $r=='gestern' ? 'letzte Nacht' : $r.' Nacht';
			}
			elseif( $hour_date>=$day_start && $hour_date<9 )
				$r .= ' Morgen';
			elseif( $hour_date>=9 && $hour_date<11.5 )
				$r .= ' Vormittag';
			elseif( $hour_date>=11.5 && $hour_date<13.5 )
				$r .= ' Mittag';
			elseif( $hour_date>=13.5 && $hour_date<18 )
				$r .= ' Nachmittag';
			elseif( $hour_date>=18 && $hour_date<22.5 )
				$r .= ' Abend';
			return $r;
		}
		$d = $d/24;
		if( $d < 7 ) {
			$d = round($d);
			return 'vor '.($d==1?'einem Tag':$d.' Tagen');
		}
		$d_weeks = $d/7;
		if( $d_weeks<4 ) {
			$d = round($d_weeks);
			return 'vor '.($d==1?'einer Woche':$d.' Wochen');
		}
		$d = $d/30;
		if( $d<12 ) {
			$d = round($d);
			return 'vor '.($d==1?'einem Monat':$d.' Monaten');
		}
		if( $d<18 )
			return 'vor einem Jahr';
		if( $d<21 )
			return 'vor eineinhalb Jahren';
		$d = round($d/12);
		return 'vor '.$d.' Jahren';
	}
	
	function connect_db() {
		
		global $dbconf;
		
		$mysqli = new mysqli($dbconf["crud"]["host"],
							 $dbconf["crud"]["username"],
							 $dbconf["crud"]["pass"],
							 $dbconf["crud"]["db_name"]);

		if (!$mysqli->set_charset("utf8mb4")) {

			printf("Error loading character set utf8: %s\n", $mysqli->error);
		}

		if ($mysqli->connect_errno) {

			echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}

		return $mysqli;
	}
	
	function set_cookie($name,$value) {
		
		global $domain;
		global $cookie_expire;
		
		$_COOKIE[$name] = $value;
		
		setcookie($name,$value,$cookie_expire,"/",$_SERVER['HTTP_HOST'] != "crud" ? $domain : false);
	}
	
	function start_session($id,$email,$firstname,$lastname) {
		
		$_COOKIE["email"] = $email;
		$_COOKIE["firstname"] = $firstname;
		$_COOKIE["lastname"] = $lastname;
		$_COOKIE["id"] = $id;
		
		set_cookie("id",$id);
		set_cookie("email",$email);
		set_cookie("firstname",$firstname);
		set_cookie("lastname",$lastname);
		
		set_cookie("login",sha1($_COOKIE["email"].$_COOKIE["id"]));
	}
	
	function getTextBetweenTags($string, $tagname) {
	  
		$pattern = "/<$tagname ?.*>(.*)<\/$tagname>/";
		preg_match($pattern, $string, $matches);
		return $matches[1];
	}
	 
	function getTemplate($file,$data=NULL) {
		
		global $path_up;
		global $langs;

		if ($data === NULL) {
			
			//Let this stuff exist if there is no $data array passed
			ob_start();
			include $file;
			$template = ob_get_contents();
			ob_end_clean();
			return $template;
		}
		else {
			
			Twig_Autoloader::register();
			
			$loader = new Twig_Loader_Filesystem($path_up."pages/templates/");
			$twig = new Twig_Environment($loader, array(
				'debug' => true
			));
			
			$twig->addExtension(new Twig_Extension_Debug());
			$twig->addExtension(new Twig_Extensions_Extension_Date());
			
			$filter = new Twig_SimpleFilter('preg_replace',function ($string,$pattern,$replacement="") {

				return preg_replace($pattern,$replacement,$string);
			});

			$twig->addFilter($filter);
			
			$filter = new Twig_SimpleFilter('ireplace', function($input, array $replace) {
				
				return str_ireplace(array_keys($replace), array_values($replace), $input);
			});
			
			$twig->addFilter($filter);
			
			$filter = new Twig_SimpleFilter('cast_to_array', function ($stdClassObject) {
				
				$response = array();
				
				foreach ($stdClassObject as $key => $value) {
					
					$response[] = array($key, $value);
				}
				return $response;
			});
			
			$twig->addFilter($filter);
			
			$template = $twig->loadTemplate($file);
			
			/* Standard value for lang */
			if (!isset($_COOKIE["lang"])) {
				
				set_cookie("lang","en");
			}
			elseif (isset($_GET["lang"])) {

				set_cookie("lang",$_GET["lang"]);
			}
			
			/* Global vars for $data */
			$data["cookie"] = $_COOKIE;
			$data["langs"] = $langs;
			
			return $template->render($data);
		}

	}

	function get_breadcrumb($request) {
		
		global $langs;
		
		$data = array("request" => $request);

		return getTemplate("main.breadcrumb.html",$data);
	}

	function get_navi($request) {
		
		global $langs;
		
		$data = array("request" => $request);

		return getTemplate("main.navi.html",$data);
	}
	
	function get_contents($urlparts) {
		
		global $path_up;
		global $host;
		global $dblink;
				
		if (count($urlparts) > 0) {
			
			if (isset($urlparts[0]) and $urlparts[0] != "") {
				
				if (isset($urlparts[1]) and file_exists($path_up."pages/".$urlparts[0])) {
					
					$file = "pages/".$urlparts[0]."/".$urlparts[1].".php";
				}
				else {
					
					$file = "pages/".$urlparts[0].".php";
				}
			}
			else {
				
				$file = "pages/index.php";
			}
			
			if (file_exists($path_up.$file)) {
				
				$return = getTemplate($path_up.$file);
			}
			else {
				
				$return = site_does_not_exist();
			}
		}
		else {
			
			$return = getTemplate($path_up."pages/index.php");
		}
		
		return $return;
	}
	
	function please_signin() {
		
		$return = '<div class="heading">';
		$return.= '<div class="background">';
		$return.= '</div>';
		$return.= '<div class="wrapper">';
		$return.= '<h2>Login erforderlich</h2>';
		$return.= '</div>';
		$return.= '</div>';
		$return.= '<div class="arrow_border">';
		$return.= '<div class="left">';
		$return.= '</div>';
		$return.= '<div class="right">';
		$return.= '</div>';
		$return.= '</div>';
		$return.= '<div class="wrapper">';
		$return.= '<p>Bitte melde dich an um die angeforderte Seite aufzurufen</p>';
		$return.= '<p><a class="button login">Jetzt anmelden</a></p>';
		$return.= '</div>';
		
		return $return;
	}
	
	function generate_meta_information($urlparts) {
		
		global $path_up;
		global $xml_points;
		global $host;
		global $dblink;
		
		$title = "CRUD Example";
		$description = "";
		$image = $host."css/big_icon.png";
		
		$result = array("title" => $title,
						"description" => $description,
						"image" => $image);
			
		return $result;
	}
	
	function site_does_not_exist() {
		
		global $host;
		
		return getTemplate("page_not_exists.html",array("host" => $host));
	}

	function get_user_info() {
		
		if (!isset($_SESSION["api_token"])) {
			
			return array();
		}

		return crud_api("/user/info", array(), true);
	}

	function get_user_id($email) {

		global $dblink;
		
		$sql_test = "SELECT * FROM user WHERE email = '".$email."'";

		$test_result = $dblink->query($sql_test);

		if (mysqli_num_rows($test_result) > 0) {

			$user_result = mysqli_fetch_object($test_result);

			return $user_result->id;
		}
		else {

			return false;
		}
	}

	function verify_token($token = NULL) {
		
		$api_token = $token;

		if (empty($api_token)) {
			
			$api_token = $_SESSION["api_token"];
		}

		return crud_api("/user/verify", array("token" => $api_token), false);
	}
	
	function user_has_session() {
		
		global $dblink;
		
		$has_session = false;
		
		if (isset($_SESSION["last_activity"]) && isset($_SESSION["created"])) {
			
			if ((time() - $_SESSION["last_activity"] > 3600)) {
				
				// last request was more than 60 minutes ago - destroy session
				session_unset();
				session_destroy();
				$has_session =  false;
				
			} else {
				
				$_SESSION["last_activity"] = time();

				// Prevent session fixation
				if (time() - $_SESSION["created"] > 3600) {
					
					session_regenerate_id(true);
					$_SESSION["created"] = time();
				}

				$has_session = (isset($_SESSION["email"]) && !empty($_SESSION["email"]));
			}
		}
		
		if (!$has_session && isset($_COOKIE["auth"])) {

			$verification = verify_token($_COOKIE["auth"]);

			if (!empty($verification["success"])) {
				
				session_unset();
				session_regenerate_id(true);
				
				$_SESSION["created"] = time();
				$_SESSION["last_activity"] = time();
				$_SESSION["api_token"] = $_COOKIE["auth"];

				$user_info = get_user_info();
				
				$_SESSION["email"] = $user_info["email"];
				$_SESSION["firstname"] = $user_info["firstname"];
				$_SESSION["lastname"] = $user_info["lastname"];

				$has_session = true;
				
			} else {

				unset($_COOKIE['auth']);
				setcookie('auth', '', time() - 3600, '/');
				$has_session = false;

			}
		}
		
		return $has_session;
	}
	
	function is_crawler() {
		
		$tolower_browser = strtolower($_SERVER['HTTP_USER_AGENT']);
		
		// check crawler and bot 
		if(strpos($tolower_browser, 'crawler') !== false ||  
			strpos($tolower_browser, 'bot') !== false) { 
				return true; 
		}
		return false; 
	}
	
	function gen_id($text,$without = NULL) {
	
		$replace = " ~_|?~|!~|:~|,~|.~|–~-|&~u|/~_|%~|ü~ue|ä~ae|ö~oe|Ü~ue|Ä~ae|Ö~oe|ß~ss";
	
		$without_array = array();
	
		if ($without != NULL) {
			
			$without_array = explode("|",$without);
		}
		$replace_array = explode("|",$replace);
		
		foreach($replace_array as $char) {
			
			$replace_rule = explode("~",$char);
			
			$replace_it = true;
			
			foreach ($without_array as $without_char) {
				
				if ($without_char == $replace_rule[0]) {
					
					$replace_it = false;
				}
			}
			if ($replace_it) {
				
				$text = str_replace($replace_rule[0],$replace_rule[1],$text);
			}
		}
		$text = strtolower($text);
		
		return $text;	
	}

	function send_template_mail($email,$template,$data) {
		
		$mail = new PHPMailer;
		
		global $host;
		global $domain;
		
		$eol = PHP_EOL;
		
		$html = getTemplate($template,$data);
		
		if (isset($html)) {
		
			$mail_message = $html;
			//$mail->SMTPDebug = 1;
			$mail->SMTPOptions = array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				)
			);
			
			$mail->CharSet = 'UTF-8';
			$mail->isSMTP();
			$mail->Host = 'mail@crud.de';
			$mail->SMTPAuth = true;
			$mail->Username = 'mail@crud.de';
			$mail->Password = '';
			$mail->SMTPSecure = 'tls';
			$mail->Port = 25;
			$mail->Sender = 'mail@crud.de';
			$mail->setFrom('mail@crud.de', 'CRUD Example');
			$mail->addAddress($email);
			//$mail->addReplyTo('info@example.com', 'Information');
			//$mail->addAttachment('/var/tmp/file.tar.gz'); 
			$mail->isHTML(true);
			$mail->Subject = $data["subject"];
			$mail->Body    = $mail_message;
			$mail->AltBody = 'Please use an HTML mail client to view this mail.';
			
			if(!$mail->send()) {
				
				return $mail->ErrorInfo;
				
			} else {
				
				return true;
			}
		}
		else {
			
			return "Keine Template Informationen";
		}
	}
	
	function rrmdir($dir) {
		
	   if (is_dir($dir)) {
		 $objects = scandir($dir);
		 foreach ($objects as $object) {
		   if ($object != "." && $object != "..") {
			 if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
		   }
		 }
		 reset($objects);
		 rmdir($dir);
	   }
	}

?>