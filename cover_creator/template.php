<?php
//header('Content-Type: text/html; charset=UTF-8');
header('p3p: CP="ALL DSP COR PSAa PSDa OUR NOR ONL UNI COM NAV"');

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

var_dump($fb_user_profile["email"]);

			}
		}
		catch (FacebookApiException $e)
		{
			outLog($LOG_COVER,"uid_".$fb_user."___".$fb_user_name,$e, "error");
		} 
	}


	if ($fb_user){
	}else{
	    //ログインURL取得
		if($device_code == "pc"){
			$par = array(
					'canvas' => 1,
					'fbconnect' => 0,
			    		'redirect_uri' => 'https://apps.facebook.com/cover_create/',
			    		'scope' => $SCOPE_COVER);
		}else{
			$par = array(
					'canvas' => 1,
					'fbconnect' => 0,
			    		'redirect_url' => 'https://apps.facebook.com/cover_create/',
			    		'scope' => $SCOPE_COVER);
		}
		$fb_login_url = $facebook->getLoginUrl($par);
		if ($device_code == "mb"){
			$fb_login_url = str_replace("www.facebook.com", "m.facebook.com", $fb_login_url);
		}

		outLog($LOG_COVER,"uid_".$fb_user."_".$fb_user_name,"no_login", "action");
	}
	closeConnection($con);

?>
<!DOCTYPE HTML>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>COVER CREATOR</title>
<!--[if IE 6]>
	<script src="js/DD_belatedPNG.js"></script>
	<script>
		DD_belatedPNG.fix('img, .col, .table, .col3, .noren');
	</script>
<![endif]-->
<meta name="description" content="">
<meta name="keywords" content="">
<meta name="robots" content="index,nofollow">
<link rel="stylesheet" type="text/css" href="css/common.css">
</head>
<body>
<script type="text/javascript">
<!--
<?php if($fb_login_url){?>
	top.location.href="<?php echo $fb_login_url; ?>";
<?php }?>

//-->
</script>
<div id="wrap" class="index">



<div id="contents03" class="clearfix">
<p><img src="img/others/temp_03.jpg" alt="お好きなテンプレートを選択" width="770" height="101"></p>
  <div id="template" class="clearfix">
<p id="temp_left" class="clearfix"><a href="#"><img src="img/thumb_01.jpg" width="352" height="130"></a></p>
<p id="temp_right" class="clearfix"><a href="#"><img src="img/thumb_02.jpg" width="352" height="130"></a></p>

<p id="temp_left" class="clearfix"><a href="#"><img src="img/thumb_03.jpg" width="352" height="130"></a></p>
<p id="temp_right" class="clearfix"><a href="#"><img src="img/thumb_04.jpg" width="352" height="130"></a></p>
 </div>
 
 <div id="page_btn">
 <div id="btn_back"><a href="category.php"><img src="img/btn_back.jpg" width="275" height="61" alt="BACK"></a>
 </div>
  <div id="btn_next"><a href="album.php"><img src="img/btn_next.jpg" width="275" height="61" alt="NEXT"></a>
  </div>
 
 </div>

</div>
</body>
</html>
