<?php


	function get_instagram_oauth_data($con,$INSTAGRAM_CLIENT_ID,$INSTAGRAM_CLIENT_SECRET,$INSTAGRAM_CALLBACK_URL,$code,$fb_user) {
		$insta_error_reason = "";
		$insta_error_flg = false;

		$curl_url = "https://api.instagram.com/oauth/access_token";
		$curl_post = "client_id=".$INSTAGRAM_CLIENT_ID."&client_secret=".$INSTAGRAM_CLIENT_SECRET."&grant_type=authorization_code&redirect_uri=".$INSTAGRAM_CALLBACK_URL."&code=".$code;

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$curl_url);
		curl_setopt($ch,CURLOPT_POST,TRUE);

		curl_setopt($ch,CURLOPT_POSTFIELDS,$curl_post);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);

		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		$result = curl_exec($ch);
		curl_close($ch);

		$oauth_result = json_decode($result,TRUE);

		if(isset($oauth_result["code"])==400){
			$login_url = "http://api.instagram.com/oauth/authorize/?client_id=".$INSTAGRAM_CLIENT_ID."&redirect_uri=".$INSTAGRAM_CALLBACK_URL."&response_type=code";
outLog("cover_creator/","aa",$login_url, "debug");

			header("Location: $login_url");
		}else{
			return $oauth_result;
		}
		
	}

	function set_instagram_data($con,$INSTAGRAM_CLIENT_ID,$INSTAGRAM_CLIENT_SECRET,$INSTAGRAM_CALLBACK_URL,$code,$fb_user) {
		$insta_error_reason = "";
		$insta_error_flg = false;

		$curl_url = "https://api.instagram.com/oauth/access_token";
		$curl_post = "client_id=".$INSTAGRAM_CLIENT_ID."&client_secret=".$INSTAGRAM_CLIENT_SECRET."&grant_type=authorization_code&redirect_uri=".$INSTAGRAM_CALLBACK_URL."&code=".$code;

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$curl_url);
		curl_setopt($ch,CURLOPT_POST,TRUE);

		curl_setopt($ch,CURLOPT_POSTFIELDS,$curl_post);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);

		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		$result = curl_exec($ch);
		curl_close($ch);

		$oauth_result = json_decode($result,TRUE);

		if(isset($oauth_result["code"])==400){
			$login_url = "http://api.instagram.com/oauth/authorize/?client_id=".$INSTAGRAM_CLIENT_ID."&redirect_uri=".$INSTAGRAM_CALLBACK_URL."&response_type=code";

			header("Location: $login_url");
		}else{
			$access_token = $oauth_result["access_token"];
			$insta_user_id = $oauth_result["user"]["id"];

			$insta_url = "https://api.instagram.com/v1/users/".$insta_user_id."/media/recent?access_token=".$access_token;
			$insta_result = json_decode(file_get_contents($insta_url,true),true);

				if(!isset($insta_result["data"])){
					$insta_error_flg = true;
					$insta_error_reason = "no_data";
				}else{
					$photos_list = array();
					$cnt = 0;
					foreach($insta_result["data"] as $data){
						$photos_list[$cnt]["id"] = $data["id"];
						$photos_list[$cnt]["created_time"] = date("Y-m-d",$data["caption"]["created_time"]);
						$photos_list[$cnt]["text"] = $data["caption"]["text"];
						$photos_list[$cnt]["thumbnail_url"] = $data["images"]["thumbnail"]["url"];
						$photos_list[$cnt]["standard_url"] = $data["images"]["standard_resolution"]["url"];
						$photos_list[$cnt]["link"] = $data["link"];
					
						$sql = "SELECT * FROM `crt_cover_fb_instagram_photos` ";
						$sql .= " WHERE delete_flg = '0' " ;
						$sql .= " AND  `photos_id` = '".$data["id"]."'" ;

						$photos_data = selectList($con, $sql);

						if(isset($photos_data[0]["id"])){
						}else{
							$value_fb_uid = $fb_user;
							$value_instagram_uid = $insta_user_id;
							$value_photos_id = $data["id"];
							$value_photos_thumbnail_url = $data["images"]["thumbnail"]["url"];
							$value_photos_standard_url = $data["images"]["standard_resolution"]["url"];
							$value_photos_created_time = date("Y-m-d",$data["caption"]["created_time"]);

							$sql  = "insert into `crt_cover_fb_instagram_photos` "; 
							$sql .= " (`fb_uid`,`instagram_uid`,`photos_id`,`photos_thumbnail_url`,`photos_standard_url`,`photos_created_time`,`insert_datetime`,`update_datetime`) ";
							$sql .= "value ('".$value_fb_uid."','".$value_instagram_uid."','".$value_photos_id."','".$value_photos_thumbnail_url."','".$value_photos_standard_url."','".$value_photos_created_time."',now(),now())";
							$ret = execute($con, $sql) ;
						}
						$cnt ++;
					}
				}

				$next_url_flg = 0;
				if(isset($insta_result["pagination"]["next_url"])){
				}else{
					$next_url_flg = 1;
				}
				while($next_url_flg <= 1){
					$insta_url = $insta_result["pagination"]["next_url"];
					if(isset($insta_url)){
						$insta_result = json_decode(file_get_contents($insta_url,true),true);
					}else{
						break;
					}

					if(!isset($insta_result["data"])){
						$insta_error_flg = true;
						$insta_error_reason = "no_data";
					}else{
						foreach($insta_result["data"] as $data){
							$photos_list[$cnt]["id"] = $data["id"];
							$photos_list[$cnt]["created_time"] = date("Y-m-d",$data["caption"]["created_time"]);
							$photos_list[$cnt]["text"] = $data["caption"]["text"];
							$photos_list[$cnt]["thumbnail_url"] = $data["images"]["thumbnail"]["url"];
							$photos_list[$cnt]["standard_url"] = $data["images"]["standard_resolution"]["url"];
							$photos_list[$cnt]["link"] = $data["link"];
					
							$sql = "SELECT * FROM `crt_cover_fb_instagram_photos` ";
							$sql .= " WHERE delete_flg = '0' " ;
							$sql .= " AND  `photos_id` = '".$data["id"]."'" ;

							$photos_data = selectList($con, $sql);

							if(isset($photos_data[0]["id"])){
							}else{
									$value_fb_uid = $fb_user;
									$value_instagram_uid = $insta_user_id;
									$value_photos_id = $data["id"];
									$value_photos_thumbnail_url = $data["images"]["thumbnail"]["url"];
									$value_photos_standard_url = $data["images"]["standard_resolution"]["url"];
									$value_photos_created_time = date("Y-m-d",$data["caption"]["created_time"]);

									$sql  = "insert into `crt_cover_fb_instagram_photos` "; 
									$sql .= " (`fb_uid`,`instagram_uid`,`photos_id`,`photos_thumbnail_url`,`photos_standard_url`,`photos_created_time`,`insert_datetime`,`update_datetime`) ";
									$sql .= "value ('".$value_fb_uid."','".$value_instagram_uid."','".$value_photos_id."','".$value_photos_thumbnail_url."','".$value_photos_standard_url."','".$value_photos_created_time."',now(),now())";
									$ret = execute($con, $sql) ;
							}
							$cnt ++;
						}
					}
					if(isset($insta_result["pagination"]["next_url"])){

					}else{
						$next_url_flg ++;
					}
				}
		}
		
		if(isset($oauth_result)){
			return $oauth_result;
		}else{
			return ;
		}
	}

	function get_fb_login_url($facebook,$SCOPE_COVER,$device_code) {
		
		if($device_code == "pc"){
			$par = array(
					'canvas' => 1,
					'fbconnect' => 0,
			    		'redirect_uri' => 'https://www.facebook.com/cyberagent.CRT?sk=app_474345462580085&ref=feed',
					//'redirect_uri' => 'https://apps.facebook.com/cover_create/',
			    		'scope' => $SCOPE_COVER);
		}else{
			$par = array(
					'canvas' => 1,
					'fbconnect' => 0,
			    		'redirect_url' => 'https://www.facebook.com/cyberagent.CRT?sk=app_474345462580085&ref=feed',
			    		'scope' => $SCOPE_COVER);
		}
		$fb_login_url = $facebook->getLoginUrl($par);
		
		if ($device_code == "mb"){
			$fb_login_url = str_replace("www.facebook.com", "m.facebook.com", $fb_login_url);
		}
		
		return $fb_login_url;
		
	}

	function get_device_os($ua) {
		$value = array();
		if ((strpos($ua, "iPhone") !== false) || (strpos($ua, "iPod") !== false) || (strpos($ua, "Android") !== false)){
			$value["device_code"] = "mb";
		}else{
			$value["device_code"] = "pc";
		}

		if ((strpos($ua, "iPhone") !== false) || (strpos($ua, "iPod") !== false)){
			$value["os_code"] = "iPhone";

		}elseif((strpos($ua, "Android") !== false)){
			$value["os_code"] = "Android";

		}elseif((strpos($ua, "Chrome") !== false)){
			$value["os_code"] = "Chrome";	

		}elseif((strpos($ua, "Firefox") !== false)){
			$value["os_code"] = "Firefox";

		}elseif((strpos($ua, "5.1.5 Safari") !== false)){
			$value["os_code"] = "5.1.5 Safari";

		}elseif((strpos($ua, "5.1.7 Safari") !== false)){
			$value["os_code"] = "5.1.5 Safari";

		}elseif((strpos($ua, "MSIE 9.0") !== false)){
			$value["os_code"] = "MSIE 9.0";

		}elseif((strpos($ua, "MSIE 8.0") !== false)){
			$value["os_code"] = "MSIE 8.0";

		}elseif((strpos($ua, "MSIE 7.0") !== false)){
			$value["os_code"] = "MSIE 7.0";
		}else{
			$value["os_code"] = "other";
		}

		return $value;
	}
?>