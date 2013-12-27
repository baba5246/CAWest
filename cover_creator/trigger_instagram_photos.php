<?php
header('p3p: CP="ALL DSP COR PSAa PSDa OUR NOR ONL UNI COM NAV"');
setcookie("safari_test", "1");

	$ua = $_SERVER['HTTP_USER_AGENT'];

	$device_code = "";
	if ((strpos($ua, "iPhone") !== false) || (strpos($ua, "iPod") !== false) || (strpos($ua, "Android") !== false)){
		$device_code = "mb";
	}else{
		$device_code = "pc";
	}

	require './require.php';

	$fb_login_url = "";

	//リクエスト
	$page_id = get_request_value("page_id");
	$category_id = get_request_value("category_id");
	//クラス
	$facebook = new Facebook(array('appId' => $APPID_COVER,
	                              'secret' => $SECRET_COVER,
	                              'cookie' => true,
	                        ));
	//ユーザー認証
	$fb_user_name = "";
	$fb_user_gender = "";

	$con = getConnection();
	selectDb($con, "cover");

	$fb_user = $facebook->getUser();

	if ($fb_user){
		//ユーザープロファイルを取得
		try {
			$fb_user_profile = $facebook->api('/me');
			if($fb_user_profile){
				$fb_user_name = $fb_user_profile["name"];
				$fb_user_first_name = $fb_user_profile["first_name"];
				$fb_user_last_name = $fb_user_profile["last_name"];

				if(($fb_user_first_name)&&($fb_user_last_name)){
					$fb_user_name = $fb_user_first_name." ".$fb_user_last_name;
				}
				$fb_user_gender = $fb_user_profile["gender"];

				$access_token = $facebook->getAccessToken();
			}
		}
		catch (FacebookApiException $e)
		{
			outLog($LOG_COVER,"uid_".$fb_user."___".$fb_user_name,$e, "error");
		} 
	}

	if ($fb_user){
			$INSTAGRAM_CLIENT_ID = "1729ccd580cf49aba478f2a24bf9bc10";
			$INSTAGRAM_CLIENT_SECRET = "4a21e4126e804098a9e76bc8958b046e";
//			$INSTAGRAM_CALLBACK_URL = "https://ca-creative.jp/crt_dev/cover_creator/template_common.php?category_id=instagram";
			$INSTAGRAM_CALLBACK_URL = "https://ca-creative.jp/crt_dev/cover_creator/trigger_instagram_photos.php";
			$insta_error_reason = "";
			$insta_error_flg = false;

			$code = get_request_value("code");

			$curl_url = "https://api.instagram.com/oauth/access_token";
			$curl_post = "client_id=".$INSTAGRAM_CLIENT_ID."&client_secret=".$INSTAGRAM_CLIENT_SECRET."&grant_type=authorization_code&redirect_uri=".$INSTAGRAM_CALLBACK_URL."&code=".$code;

outLog("cover_creator/","curl_url",$curl_url, "debug");
outLog("cover_creator/","curl_post",$curl_post, "debug");

			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,$curl_url);
			curl_setopt($ch,CURLOPT_POST,TRUE);

			curl_setopt($ch,CURLOPT_POSTFIELDS,$curl_post);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);

			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			$result = curl_exec($ch);
			curl_close($ch);

			$oauth_result = json_decode($result,TRUE);


outLog("cover_creator/","debug",$oauth_result["code"], "debug");

			if(isset($oauth_result["code"])==400){
				$login_url = "http://api.instagram.com/oauth/authorize/?client_id=".$INSTAGRAM_CLIENT_ID."&redirect_uri=".$INSTAGRAM_CALLBACK_URL."&response_type=code";

outLog("cover_creator/","login_url",$login_url, "debug");

				header("Location: $login_url");
			}else{
				$access_token = $oauth_result["access_token"];
				$insta_user_id = $oauth_result["user"]["id"];

				$insta_url = "https://api.instagram.com/v1/users/".$insta_user_id."/media/recent?access_token=".$access_token;

outLog("cover_creator/","insta_url",$insta_url, "debug");

				$insta_result = json_decode(file_get_contents($insta_url,true),true);

				if(!isset($insta_result["data"])){
					$insta_error_flg = true;
					$insta_error_reason = "no_data";
				}else{
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

						if($photos_data[0]["id"]){
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
					$cmd_tmp = "php ./batch_instagram_photos.php ".$fb_user." ".$insta_user_id." ".$access_token." ";
					exec($cmd_tmp);
				}
			}
	}


?>
<html>
<head>
<title>Safari Fix</title>
<script type="text/javascript" src="prototype.min.js"></script>
<link rel="stylesheet" href="./css/main_safari.css" type="text/css">
</head>
<body id="safari">

<script type="text/javascript">

setTimeout(function() { 
//	window.opener.location.reload();
	window.opener.doCategorySelect('template_common','_instagram');
	window.close();
}, 3000);

    </script>

<div id="top_contents_safari">
3秒後にリロードされます。
</div>
</body>
</html>