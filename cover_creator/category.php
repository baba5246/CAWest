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
	$birthday = "";
	$my365_error_reason = "";

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
		//my365 IDチェック
		$my365_error_flg = false;
		$my365_error_reason = "";

		$my365_user_id = $fb_user;

//暫定テストユーザー
if($fb_user=='100003706654531'){
$my365_user_id = "1155734596";
}

		//$my365_date_from = "2011-11-01";
		$my365_date_to = date("Y-m-d");

		$my365_url = $MY365_API_URL."?facebook_id=".$my365_user_id."&to=".$my365_date_to."&key=".$MY365_API_KEY;
		//$my365_url = $MY365_API_URL."?facebook_id=".$my365_user_id."&from=".$my365_date_from."&to=".$my365_date_to."&key=".$MY365_API_KEY;
		$my365_result = json_decode(file_get_contents($my365_url,true),true);
		if(isset($my365_result["error"])){
			$my365_error_flg = true;
			$my365_error_reason = "no_id";

outLog($LOG_COVER,"uid_".$fb_user."_".$fb_user_name,$my365_error_reason, "error");

		}elseif(!isset($my365_result["response"])){
			$my365_error_flg = true;
			$my365_error_reason = "no_data";

outLog($LOG_COVER,"uid_".$fb_user."_".$fb_user_name,$my365_error_reason, "error");

		}else{
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
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js" type="text/javascript"></script>
<link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/ui-lightness/jquery-ui.css" rel="stylesheet" type="text/css" />  
<script src="./js/cvi_busy_lib.js"></script>
<link rel="stylesheet" type="text/css" href="css/common.css">
<script type="text/javascript">
<!--

var ctrl = null;

	function doCategorySelect(page_id,category_id){
		//var obj = document.getElementById("buttom_"+category_id);
		//var oElements = obj.childNodes; 

		//obj.style.opacity = 0.2;
		//obj.style.mozOpacity = 0.2;
		//obj.style.filter = "alpha(opacity="+20+")";
		//$(obj).css('-ms-filter',"alpha(opacity=20)");

		if (ctrl == null) {
			ctrl = getBusyOverlay(document.getElementById("cvi_busy"), {opacity:0.25, color:"gray", text:""});
		}else{
			ctrl.remove();
			ctrl = null;
		}

		document.form01.action='./'+page_id+'.php';
		document.form01.category_id.value=category_id;
		document.form01.submit();
	}


//-->
</script>
<style type="text/css">
    <!--
	.categ img{
		cursor:pointer;
	}

    -->
</style>

</head>
<body>
<form name="form01" method="post" action="" id="form01" enctype="multipart/form-data">
<script type="text/javascript">
<!--
<?php if($fb_login_url){?>
	top.location.href="<?php echo $fb_login_url; ?>";
<?php }?>

//-->
</script>
<div id="fb-root">
</div>
<script src="https://connect.facebook.net/en_US/all.js" type="text/javascript"></script>
<script type="text/javascript">
window.fbAsyncInit = function() {
      FB._https = true;
      FB.init({
        appId  : '474345462580085',
        status : true, // check login status
        cookie : true, // enable cookies to allow the server to access the session
        xfbml  : false  // parse XFBML
      });
	//ex.FB.Canvas.setSize({ width: 520, height: 800 });
	FB.Canvas.setSize();
	FB.Canvas.setAutoGrow();
};
</script>

<div id="cvi_busy">
<div id="wrap" class="index">



<div id="contents02" class="clearfix">
  <div id="category" class="clearfix">
  <section>
<p><img src="img/category/app_cat_01.jpg" alt="写真のカテゴリーを選択" width="670" height="101"></p>
<p class="categ"><img id="buttom_tag" src="img/category/app_cat_02.jpg" alt="あなたが写っている写真から" width="670" height="150" onClick="javascript:doCategorySelect('template_common','tag')" ></p>
<p class="categ"><img id="buttom_album" src="img/category/app_cat_03.jpg" alt="あなたのアルバムから" width="670" height="150" onClick="javascript:doCategorySelect('template_common','album')" style="cursor: pointer"></p>
<p class="categ">
<?php if($my365_error_reason == "no_id"){ 
		$app_url = "";
		if($os_code == "iPhone" ){
			$app_url = "http://itunes.apple.com/jp/app/my365/id472931728?mt=8";
		}elseif($os_code == "Android" ){
			$app_url = "https://play.google.com/store/apps/details?id=in.my365";
		}else{
			$app_url = "https://my365.in/";
		}
?>
<a href="<?php echo($app_url);?>" target="_blank"><img src="img/category/app_cat_04.jpg" alt="my365で撮った写真から" width="670" height="150"></a>
<?php }else{ ?>
<img id="buttom_my365" src="img/category/app_cat_04.jpg" alt="my365で撮った写真から" width="670" height="150" onClick="javascript:doCategorySelect('template_common','my365')" style="cursor: pointer">
<?php } ?>
</p>
<p class="categ">
<?php 
	if(($device_code == "pc")||($device_code == "mb")){ 
	//if(($os_code == "5.1.5 Safari")||($os_code == "MSIE 9.0")||($os_code == "MSIE 8.0")||($os_code == "MSIE 7.0")){
	?>
<a href="safari_instagram_photos.php" target="_blank" onclick="window.open('./safari_instagram_photos.php', '', 'width=770,height=300'); return false;"><img src="img/category/app_cat_05.jpg" alt="Instagramで撮った写真から" width="670" height="150" ></a>
<?php 
	}else{ ?>
<img id="buttom_instagram" src="img/category/app_cat_05.jpg" alt="Instagramで撮った写真から" width="670" height="150" onClick="javascript:doCategorySelect('template_common','instagram')" style="cursor: pointer">
<?php	} ?>
</p>
<?php if($device_code == "pc"){?>
<p class="banner"><img src="img/category/app_cat_08.jpg" alt="今すぐMy365を無料ダウンロード" width="670" height="170" border="0" usemap="#Map">
  <map name="Map">
    <area shape="rect" coords="269,105,400,159" href="http://itunes.apple.com/jp/app/my365/id472931728?mt=8" target="_blank" alt="iPhone">
    <area shape="rect" coords="409,106,541,158" href="https://play.google.com/store/apps/details?id=in.my365" target="_blank" alt="Android">
  </map>
</p>
<?php }elseif($device_code == "mb"){
		$app_url = "";
		if($os_code == "iPhone" ){
			$app_url = "http://itunes.apple.com/jp/app/my365/id472931728?mt=8";
		}elseif($os_code == "Android" ){
			$app_url = "https://play.google.com/store/apps/details?id=in.my365";
		}
?>
<p class="banner"><a href="<?php echo($app_url);?>"><img src="img/category/app_cat_08.jpg" alt="今すぐMy365を無料ダウンロード" width="670" height="170" border="0" usemap="#Map"></a>
</p>
<?php }?>
</section>


  </div>
</div>

</div>
</div>
<input type="hidden" name="category_id" value="" />
</form>
<?php 
$endTime = microtime(true);
	//アクセスログ
	$my365_user_id = "";
	$insta_user_id = "";
	$access_page = "category";
	$function_time = number_format($endTime - $startTime, C_DECIMALS);
	
	$sql  = " insert into crt_cover_creator_access (fb_uid,my365_user_id,insta_user_id,fb_user_name,sex_type,birthday,device_code,useragent,access_page,function_time,insert_datetime,update_datetime) ";
	$sql .= " values ('".$fb_user."','".$my365_user_id."','".$insta_user_id."','".$fb_user_name."','".$fb_user_gender."','".$birthday."','".$device_code."','".$ua."','".$access_page."','".$function_time."',now(),now())";
	$ret  = execute($con, $sql) ;
	closeConnection($con);
?>
<script type="text/javascript">
	FB.Canvas.scrollTo(0, 0);
</script>
</body>
</html>
