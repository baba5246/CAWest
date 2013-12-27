<?php
header('p3p: CP="ALL DSP COR PSAa PSDa OUR NOR ONL UNI COM NAV"');
setcookie("safari_test", "1");

	require './require.php';

	$ua = $_SERVER['HTTP_USER_AGENT'];

	$device_os = array();
	$device_os = get_device_os($ua);
	$device_code =$device_os["device_code"];
	$os_code =$device_os["os_code"];

	$con = getConnection();
	selectDb($con, "cover");
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
	window.opener.doLoading('category');
	//window.opener.doCategorySelect('template_common','safari_instagram');
	//window.opener.location.reload();
	window.close();
}, 3000);

    </script>
<div id="wrap" class="index">



<div id="contents04" class="clearfix">
<p><img src="./img/others/app_loading.jpg" alt="リロード中です。そのままお待ちください。" width="770" height="215"></p>

</div>
</div>
<?php 
$endTime = microtime(true);
	//アクセスログ
	$fb_user = "";
	$fb_user_name = "";
	$fb_user_gender = "";
	$birthday = "";
	$my365_user_id = "";
	$insta_user_id = "";
	$access_page = "safari_cookie_fix";
	$function_time = number_format($endTime - $startTime, C_DECIMALS);
	
	$sql  = " insert into crt_cover_creator_access (fb_uid,my365_user_id,insta_user_id,fb_user_name,sex_type,birthday,device_code,useragent,access_page,function_time,insert_datetime,update_datetime) ";
	$sql .= " values ('".$fb_user."','".$my365_user_id."','".$insta_user_id."','".$fb_user_name."','".$fb_user_gender."','".$birthday."','".$device_code."','".$ua."','".$access_page."','".$function_time."',now(),now())";
	$ret  = execute($con, $sql) ;

	closeConnection($con);
?>
</body>
</html>
