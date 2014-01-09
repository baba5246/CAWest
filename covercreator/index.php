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

	$page_id = get_request_value("page_id");

	$facebook = new Facebook(array('appId' => $APPID_COVER,
	                              'secret' => $SECRET_COVER,
	                              'cookie' => true,
	                        ));

	$fb_user_name = "";
	$fb_user_gender = "";
	$birthday = "";

	$con = getConnection();
	selectDb($con, "_cover");

	$fb_user = $facebook->getUser();

	if ($fb_user){
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
			outLog($LOG_COVER,"uid_".$fb_user."_".$fb_user_name,$e, "error");
		} 
	}

	if ($fb_user){
	}else{
		$fb_login_url = get_fb_login_url($facebook,$SCOPE_COVER,$device_code);
	}

	//アプリURLからのアクセスをページタブへ強制遷移
	$REFERER = $_SERVER["HTTP_REFERER"];

//PC：アプリリンク：
//	https://apps.facebook.com/cover_create/
//	$PAGE_TAB_URL
//PC：フィードリンク：
//	https://s-static.ak.facebook.com/platform/page_proxy.php?v=5
//	$PAGE_TAB_URL
//MB：アプリリンク：★やっかい
//	https://m.facebook.com/apps/cover_create/apps/application.php?id=474345462580085&fb_source=feed&ref=feed&refid=7&_ft_=qid.5761246613130864105%3Amf_story_key.-5102391130148163551
//	https://ca-creative.jp/cover_creator/apps/application.php
//	$PAGE_TAB_URL
//	https://s-static.ak.facebook.com/platform/page_proxy.php?v=5
//	$PAGE_TAB_URL
//MB：フィードリンク：
//	https://s-static.ak.facebook.com/platform/page_proxy.php?v=5
//	$PAGE_TAB_URL


	if(strstr($REFERER,"//apps.facebook.com/CoverCreator")){
		$fb_login_url = $PAGE_TAB_URL;
	}elseif(is_null($REFERER)){
		$fb_login_url = $PAGE_TAB_URL;
	}elseif(strstr($REFERER,"m.facebook.com")){
		$fb_login_url = $PAGE_TAB_URL;
	}elseif((strstr($REFERER,"s-static.ak.facebook.com"))&&($device_code == "mb")){
	}

	//ファンゲート制御
	$signed_request = $facebook->getSignedRequest();
	$page_id = $signed_request["page"]["id"];
	$page_admin = $signed_request["page"]["admin"];
	$like_status = $signed_request["page"]["liked"];
	$country = $signed_request["user"]["country"];
	$locale = $signed_request["user"]["locale"];
	$user_id = $signed_request["user_id"];


?>
<!DOCTYPE HTML>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />

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
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js" type="text/javascript"></script>  
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js" type="text/javascript"></script>
<link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/ui-lightness/jquery-ui.css" rel="stylesheet" type="text/css" />  
<script src="./js/cvi_busy_lib.js"></script>
<link rel="stylesheet" type="text/css" href="css/common.css">
</head>
<body>
<form name="form01" method="post" action="" id="form01" enctype="multipart/form-data">
<script type="text/javascript">
<!--
<?php if($fb_login_url){?>
	top.location.href="<?php echo $fb_login_url; ?>";
<?php }?>

var ctrl = null;

	function doLoading(page_id){
		
		if (ctrl == null) {
			ctrl = getBusyOverlay(document.getElementById("cvi_busy"), {opacity:0.25, color:"gray", text:""});
		}else{
			ctrl.remove();
			ctrl = null;
		}

		document.form01.action='./'+page_id+'.php';
		document.form01.submit();
	}

//-->
</script>
<div id="fb-root">
</div>
<script src="https://connect.facebook.net/en_US/all.js" type="text/javascript"></script>
<script type="text/javascript">
window.fbAsyncInit = function() {
      FB._https = true;
      FB.init({
        appId  : '242539655906759',
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
<?php if ($like_status) {?>
<div id="wrap" class="index">

<div id="contents01" class="clearfix">
  <div id="main" class="clearfix">
  <section>
<p><img src="img/app_top_01.jpg" alt="COVER + CREATOR" width="769" height="355" border="0" usemap="#Map">
  <map name="Map">
<?php 
	if($os_code == "5.1.5 Safari"){
		if((count($_COOKIE) > 0)){
			$link_url = "category.php";
			$link_target_tag_pc = "_self";
			$link_onclick = "";
		}else{
			$link_url = "safari_cookie_fix.php";
			$link_target_tag_pc = "_blank";
			$link_onclick = " onclick=\"window.open('./safari_cookie_fix.php', '', 'width=770,height=215'); return false;\"";
		}
	}else{
		$link_url = "category.php";
		$link_target_tag_pc = "\"_self\"";
		$link_onclick = "";
	}
?>

<area shape="rect" coords="365,187,757,235" href="<?php echo($link_url);?>" target="<?php echo($link_target_tag_pc);?>" <?php echo($link_onclick);?> alt="Let's CREATE">
  </map>
</p>
<p><img src="img/app_top_02.jpg" alt="Division" width="769" height="33"></p>
</section>
<section>
<article>
<table width="769" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td colspan="3"><img src="img/app_top_365.jpg" width="384" height="49"></td>
    <td colspan="3"><img src="img/app_top_inst.jpg" width="385" height="49"></td>
    </tr>
  <tr>
    <td><img src="img/app_top_05.jpg" width="10" height="130"></td>
    <td><img src="img/temp_365_02.jpg" width="352" height="130"></td>
    <td><img src="img/app_top_06.jpg" width="22" height="130"></td>
    <td><img src="img/app_top_07.jpg" width="23" height="130"></td>
    <td><img src="img/thumb_inst_03.jpg" width="352" height="130"></td>
    <td><img src="img/app_top_08.jpg" width="10" height="130"></td>
  </tr>
</table>
</article>
<article>
<table width="769" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td colspan="3"><img src="img/app_top_10.jpg" width="384" height="67"></td>
    <td colspan="3"><img src="img/app_top_11.jpg" width="385" height="67"></td>
    </tr>
  <tr>
    <td><img src="img/app_top_05.jpg" width="10" height="130"></td>
    <td><img src="img/thumb_03.jpg" width="352" height="130"></td>
    <td><img src="img/app_top_06.jpg" width="22" height="130"></td>
    <td><img src="img/app_top_07.jpg" width="23" height="130"></td>
    <td><img src="img/thumb_04.jpg" width="352" height="130"></td>
    <td><img src="img/app_top_08.jpg" width="10" height="130"></td>
  </tr>
</table>
</article>
<article>
<table width="769" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td colspan="3"><img src="img/app_top_10.jpg" width="384" height="67"></td>
    <td colspan="3"><img src="img/app_top_11.jpg" width="385" height="67"></td>
    </tr>
  <tr>
    <td><img src="img/app_top_05.jpg" width="10" height="130"></td>
    <td><img src="img/thumb_01.jpg" width="352" height="130"></td>
    <td><img src="img/app_top_06.jpg" width="22" height="130"></td>
    <td><img src="img/app_top_07.jpg" width="23" height="130"></td>
    <td><img src="img/thumb_02.jpg" width="352" height="130"></td>
    <td><img src="img/app_top_08.jpg" width="10" height="130"></td>
  </tr>
  <tr>
    <td colspan="6"><img src="img/app_top_09.jpg" width="769" height="10"></td>
    </tr>
</table>
</article>
</section>

  </div>
  <section>
<p id="btn">
<a href="<?php echo($link_url);?>" target="<?php echo($link_target_tag_pc);?>" <?php echo($link_onclick);?> >
<img src="img/btn.jpg" alt="Let's CREATE" width="425" height="66"></a>
</p>
  </section>
</div>

</div>
<?php 
$endTime = microtime(true);
	$my365_user_id = "";
	$insta_user_id = "";
	$access_page = "index";
	$function_time = number_format($endTime - $startTime, C_DECIMALS);
	
	$sql  = " insert into crt_cover_creator_access (fb_uid,my365_user_id,insta_user_id,fb_user_name,sex_type,birthday,device_code,useragent,access_page,function_time,insert_datetime,update_datetime) ";
	$sql .= " values ('".$fb_user."','".$my365_user_id."','".$insta_user_id."','".$fb_user_name."','".$fb_user_gender."','".$birthday."','".$device_code."','".$ua."','".$access_page."','".$function_time."',now(),now())";
	$ret  = execute($con, $sql) ;

	//アクセスログ：crt_cover_creator_user
	$sql = "SELECT * FROM `crt_cover_creator_user` ";
	$sql .= " WHERE delete_flg = '0' " ;
	$sql .= " AND  `fb_uid` = '".$fb_user."'" ;
	$sql .= " AND  `useragent` = '".$ua."'" ;

	$user_data = selectList($con, $sql);
	if(isset($user_data[0]["id"])){
		$sql   = "update crt_cover_creator_user set access_count = access_count+1,update_datetime = now()";
		$sql .= " WHERE delete_flg = '0' " ;
		$sql .= " AND  `fb_uid` = '".$fb_user."'" ;
		$sql .= " AND  `useragent` = '".$ua."'" ;
		$ret  = execute($con, $sql) ;
	}else{
		$sql  = " insert into crt_cover_creator_user (fb_uid,my365_user_id,insta_user_id,fb_user_name,sex_type,birthday,useragent,access_count,insert_datetime,update_datetime) ";
		$sql .= " values ('".$fb_user."','".$my365_user_id."','".$insta_user_id."','".$fb_user_name."','".$fb_user_gender."','".$birthday."','".mysql_real_escape_string($ua)."',1,now(),now())";
		$ret  = execute($con, $sql) ;
	}

	closeConnection($con);

?>
<?php }else{?>
<div id="wrap" class="index">

<div id="contents01" class="clearfix">
  <div id="main" class="clearfix">

    <section>
    <p><img src="img/no_fan.jpg" width="769" height="1116"></p>
    </section>
    </div>
  </div>
</div>


<?php 
$endTime = microtime(true);
	$my365_user_id = "";
	$insta_user_id = "";
	$access_page = "index_no_like";
	$function_time = number_format($endTime - $startTime, C_DECIMALS);
	
	$sql  = " insert into crt_cover_creator_access (fb_uid,my365_user_id,insta_user_id,fb_user_name,sex_type,birthday,device_code,useragent,access_page,function_time,insert_datetime,update_datetime) ";
	$sql .= " values ('".$fb_user."','".$my365_user_id."','".$insta_user_id."','".$fb_user_name."','".$fb_user_gender."','".$birthday."','".$device_code."','".$ua."','".$access_page."','".$function_time."',now(),now())";
	$ret  = execute($con, $sql) ;

	closeConnection($con);
?>
<?php }?>    
</div>
</form>
</body>
<script type="text/javascript">
	FB.Canvas.scrollTo(0, 0);
</script>
</html>
