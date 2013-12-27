<?php
//header('Content-Type: text/html; charset=UTF-8');
header('p3p: CP="ALL DSP COR PSAa PSDa OUR NOR ONL UNI COM NAV"');
$startTime = microtime(true);

	require './require.php';

	$ua = $_SERVER['HTTP_USER_AGENT'];

	$device_os = array();
	$device_os = get_device_os($ua);
	$device_code =$device_os["device_code"];
	$os_code =$device_os["os_code"];

	$fb_login_url = "";

	//リクエスト
	$action_flg = get_request_value("action_flg");
	$category_id = get_request_value("category_id");
	$template_id = get_request_value("template_id");
	$cover_file_suffix = get_request_value("cover_file_suffix");

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
				$birthday = $fb_user_profile["birthday"];

				$access_token = $facebook->getAccessToken();
			}
		}
		catch (FacebookApiException $e)
		{
		} 
	}


//暫定ユーザー
if($category_id == "my365"){
//暫定テストユーザー
if($fb_user=='100003706654531'){
$my365_user_id = "1155734596";
$fb_user = "1155734596";
}
}

	if ($fb_user){
		if($action_flg=="photos_wall"){
				try {

					$upload_file = $LOG_PICTURE."/output/photo_".$fb_user."_".$cover_file_suffix.".jpeg";

					$message  = "【ＣＯＶＥＲ＋ＣＲＥＡＴＯＲ】\n";
					$message .= "あなたのアルバムにカバーフォト画像を追加しました。\n";
					$message .= "https://www.facebook.com/cyberagent.CRT?sk=app_474345462580085&ref=feed";
					$message .= "\n";
					$message .= "\n";

					//$message .= "ＴＨＡＮＫ ＹＯＵ！！\n";
					//$message .= "カバー画像があなたの写真アルバムに追加されました。\n";
					//$message .= "カバー右下の「カバーを変更」をクリックしてさっそくアップ！\n";

					$file = file_get_contents($upload_file);
					$tmpfname = tempnam('/tmp', 'source_');

					$handle = fopen($tmpfname, "wb");
					fwrite($handle, $file);
					fclose($handle);
					$source = '@' . realpath($tmpfname);

					//投稿オプションをセットする
					$attachment = array(
						'access_token' => $facebook->getAccessToken(),
						'message' => $message,
						'source' => $source
					);

					$facebook->setFileUploadSupport(true);
					$statusUpdate = $facebook->api('/me/photos', 'POST', $attachment);
					unlink($tmpfname);
				
					$photo_upload = "success";
				}
				catch (FacebookApiException $e)
				{
outLog("cover_creator/","",$e, "error");
				}
		}

	}else{
		//ログインURL取得
		$fb_login_url = get_fb_login_url($facebook,$SCOPE_COVER,$device_code);
	}
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
<script type="text/javascript">
<!--
	function goIndex(){
		top.location.href="<?php echo $PAGE_TAB_URL; ?>";
	}

//-->
</script>
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
<p><img src="img/others/app_thank_01.jpg" alt="THANK YOU" width="770" height="80"></p>
<p><img src="img/others/app_thank_02.jpg" alt="カバー画像があなたの写真アルバムに追加されました。" width="770" height="72"></p>
<div id="thanks"><img src="img/others/app_thank_03.jpg" width="597" height="443"></div>


 
 
 

<div id="btn_top"><img src="img/btn_top.jpg" width="345" height="61" alt="アプリTOPへ戻る" onClick="javascript:goIndex()" style="cursor: pointer">
</div>

</div>
<?php 
$endTime = microtime(true);

	//アクセスログ
	$my365_user_id = "";
	$insta_user_id = "";
	$access_page = "thanks_".$category_id."_".$template_id;
	$function_time = number_format($endTime - $startTime, C_DECIMALS);
	$sql  = " insert into crt_cover_creator_access (fb_uid,my365_user_id,insta_user_id,fb_user_name,sex_type,birthday,device_code,useragent,access_page,function_time,insert_datetime,update_datetime) ";
	$sql .= " values ('".$fb_user."','".$my365_user_id."','".$insta_user_id."','".$fb_user_name."','".$fb_user_gender."','".$birthday."','".$device_code."','".$ua."','".$access_page."','".$function_time."',now(),now())";
	$ret  = execute($con, $sql) ;
	closeConnection($con);
?>
</body>
</html>
