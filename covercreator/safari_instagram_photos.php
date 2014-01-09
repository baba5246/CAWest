<?php
header('p3p: CP="ALL DSP COR PSAa PSDa OUR NOR ONL UNI COM NAV"');
//setcookie("safari_test", "1");
$startTime = microtime(true);

	require './require.php';

	$ua = $_SERVER['HTTP_USER_AGENT'];

	$device_os = array();
	$device_os = get_device_os($ua);
	$device_code =$device_os["device_code"];
	$os_code =$device_os["os_code"];

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
	$insta_user_id = "";
	$access_token =  "";

	$con = getConnection();
	selectDb($con, "_cover");

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
				$birthday = $fb_user_profile["birthday"];

				$access_token = $facebook->getAccessToken();
			}
		}
		catch (FacebookApiException $e)
		{
			outLog($LOG_COVER,"uid_".$fb_user."___".$fb_user_name,$e, "error");
		} 
	}

	if ($fb_user){
		$limit_date = date("Y-m-d H:i:s",strtotime("-5 minute"));
		$sql = "SELECT * FROM `crt_cover_fb_instagram_photos` ";
		$sql .= " WHERE delete_flg = '0' " ;
		$sql .= " AND  `fb_uid` = '".$fb_user."'" ;
		$sql .= " AND  `insert_datetime` > '".$limit_date."'" ;

		$photos_data = selectList($con, $sql);
		if(isset($photos_data[0]["id"])){
			
		}else{

			$code = get_request_value("code");
			$oauth_result = array();
			$oauth_result = get_instagram_oauth_data($con,$INSTAGRAM_CLIENT_ID_FOR_SAFARI,$INSTAGRAM_CLIENT_SECRET_FOR_SAFARI,$INSTAGRAM_CALLBACK_URL_FOR_SAFARI,$code,$fb_user);

			if(isset($oauth_result)){
				$insta_user_id = $oauth_result["user"]["id"];
				$access_token = $oauth_result["access_token"];

				$cmd_tmp = "php ./batch_instagram_data.php ".$fb_user." ".$insta_user_id." ".$access_token." > /dev/null & ";
				exec($cmd_tmp);

				$cmd_tmp = "php ./batch_instagram_photos.php ".$fb_user." ".$insta_user_id." ".$access_token." > /dev/null & ";
				exec($cmd_tmp);

				$sql  = "update `crt_cover_fb_instagram_photos` ";
				$sql .= " set delete_flg = '1' ";
				$sql .= " WHERE delete_flg = '0' " ;
				$sql .= " AND  `fb_uid` = '".$fb_user."'" ;
				$ret = execute($con, $sql) ;


				//アクセスログ：crt_cover_creator_user
				$sql = "SELECT * FROM `crt_cover_creator_user` ";
				$sql .= " WHERE delete_flg = '0' " ;
				$sql .= " AND  `fb_uid` = '".$fb_user."'" ;
				$sql .= " AND  `useragent` = '".$ua."'" ;

				$user_data = selectList($con, $sql);
				if(isset($user_data[0]["id"])){
					$sql   = "update crt_cover_creator_user set access_count = access_count+1,update_datetime = now() ";
					$sql .= " ,insta_user_id = '".$insta_user_id."' " ;
					$sql .= " WHERE delete_flg = '0' " ;
					$sql .= " AND  `fb_uid` = '".$fb_user."'" ;
					$sql .= " AND  `useragent` = '".$ua."'" ;
					$ret  = execute($con, $sql) ;
				}else{
				}
			}else{
				outLog($LOG_COVER,"uid_".$fb_user."___".$fb_user_name,"set_instagram_data_no_oauth_result", "error");
			}
		}
	}

$endTime = microtime(true);
	//アクセスログ
	$my365_user_id = "";
	$access_page = "safari_instagram_photos";
	$function_time = number_format($endTime - $startTime, C_DECIMALS);
	
	$sql  = " insert into crt_cover_creator_access (fb_uid,my365_user_id,insta_user_id,fb_user_name,sex_type,birthday,device_code,useragent,access_page,function_time,insert_datetime,update_datetime) ";
	$sql .= " values ('".$fb_user."','".$my365_user_id."','".$insta_user_id."','".$fb_user_name."','".$fb_user_gender."','".$birthday."','".$device_code."','".$ua."','".$access_page."','".$function_time."',now(),now())";
	$ret  = execute($con, $sql) ;
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
<script Language="JavaScript"><!--
function hideScrollBar()
{
	document.body.style.overflow = "hidden";
}
// --></script>
</head>
<body onLoad="hideScrollBar()">
<script type="text/javascript">

setTimeout(function() { 
//	window.opener.location.reload();

<?php if($device_code == "pc"){ ?>
	window.opener.doCategorySelect('template_common','safari_instagram');
	window.close();
<?php }elseif($device_code == "mb"){ ?>
	top.location.href="./template_common.php?category_id=safari_instagram";
<?php } ?>
}, 3000);

</script>

<div id="wrap" class="index">



<div id="contents04" class="clearfix">
<p><img src="img/others/app_loading.jpg" alt="リロード中です。そのままお待ちください。" width="770" height="215"></p>

</div>
</div>
</body>
</html>
