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
			}
		}
		catch (FacebookApiException $e)
		{
			outLog($LOG_COVER,"uid_".$fb_user."___".$fb_user_name,$e, "error");
		} 
	}

	if ($fb_user){

		$query_url = 'https://graph.facebook.com/me/photos?&access_token='. $access_token;
		$tag_photos_query_result = json_decode(file_get_contents($query_url),true);
		$cnt = 0;

		foreach($tag_photos_query_result["data"] as $data){
			$photo_list_id = $data["id"];
			$photos_list[$cnt]["id"] = $data["id"];
			$photos_list[$cnt]["name"] = $data["name"];
			$photos_list[$cnt]["picture"] = $data["picture"];
			$photos_list[$cnt]["source"] = $data["source"];
			$photos_list[$cnt]["width"] = $data["width"];
			$photos_list[$cnt]["height"] = $data["height"];
			$photos_list[$cnt]["images_2_source"] = $data["images"][5]["source"];
			$photos_list[$cnt]["images_2_width"] = $data["images"][5]["width"];
			$photos_list[$cnt]["images_2_height"] = $data["images"][5]["height"];

			$sql = "SELECT * FROM `crt_cover_fb_uid_tags` ";
			$sql .= " WHERE delete_flg = '0' " ;
			$sql .= " AND  `photos_id` = '".$data["id"]."'" ;

			$photos_data = selectList($con, $sql);

			if($photos_data[0]["id"]){
			}else{
				$value_fb_user = $fb_user;
				$value_fb_user_name = mysql_real_escape_string($fb_user_name);
				$value_albums_id = '0';
				$value_photos_id = $data["id"];

				$value_photos_name = $data["name"];
				$value_photos_name = str_replace("\r", "", $value_photos_name);
				$value_photos_name = str_replace("\n", "", $value_photos_name);
				$value_photos_name = str_replace("\t", "", $value_photos_name);

				$value_photos_picture = mysql_real_escape_string($data["picture"]);
				$value_photos_source = mysql_real_escape_string($data["source"]);
				$value_photos_source_2 = mysql_real_escape_string($data["images"][5]["source"]);
				$value_photos_source_width = $data["width"];
				$value_photos_source_height = $data["height"];
				$value_photos_source_2_width = $data["images"][5]["width"];
				$value_photos_source_2_height = $data["images"][5]["height"];

				$sql  = " insert into `crt_cover_fb_uid_tags` (`fb_uid`,`fb_user_name`,`albums_id`,`photos_id`,`photos_name`,`photos_picture`,`photos_source`,`photos_source_2`,`photos_source_width`,`photos_source_height`,`photos_source_2_width`,`photos_source_2_height`,`insert_datetime`,`update_datetime`) ";
				$sql .= " values ('".$value_fb_user."','".$value_fb_user_name."','".$value_albums_id."','".$value_photos_id."','".$value_photos_name."','".$value_photos_picture."','".$value_photos_source."','".$value_photos_source_2."','".$value_photos_source_width."','".$value_photos_source_height."','".$value_photos_source_2_width."','".$value_photos_source_2_height."',now(),now())";
				$ret  = execute($con, $sql) ;
			}

			$cnt ++;
		}

		$cmd_tmp = "php ./batch_tags_photos.php ".$fb_user." ";
		exec($cmd_tmp) ;

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
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js" type="text/javascript"></script>
<link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/ui-lightness/jquery-ui.css" rel="stylesheet" type="text/css" />  
<script src="./js/cvi_busy_lib.js"></script>

<link rel="stylesheet" type="text/css" href="css/common.css">
<style type="text/css">


</style>

<script type="text/javascript">
<!--

var template_id_select = "";
var template_id_prev = "";
var ctrl = null;

	function doTemplateCheck(template_id){
		if(template_id_prev){
			var obj = document.getElementById("thumb_"+template_id_prev);
			var oElements = obj.childNodes; 

				obj.style.opacity = 1.0;
				obj.style.mozOpacity = 1.0;
				obj.style.filter = "alpha(opacity="+100+")";
				$(obj).css('-ms-filter',"alpha(opacity=100)");

				//obj.style.-ms-filter = "progid:DXImageTransform.Microsoft.Alpha(opacity="+100+")";
				//obj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=100)';

		}
		if(template_id_prev == template_id){
			var obj = document.getElementById("thumb_"+template_id);
			var oElements = obj.childNodes; 

				obj.style.opacity = 0.5;
				obj.style.mozOpacity = 0.5;
				obj.style.filter = "alpha(opacity="+50+")";
				//obj.style.-ms-filter = "alpha(opacity=50)";
				$(obj).css('-ms-filter',"alpha(opacity=50)");

				//obj.style.-ms-filter = "progid:DXImageTransform.Microsoft.Alpha(Opacity=50)";
				//obj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=50)';
				template_id_select = template_id;
				template_id_prev = template_id;

		}else{
			var obj = document.getElementById("thumb_"+template_id);
			var oElements = obj.childNodes; 

				obj.style.opacity = 0.5;
				obj.style.mozOpacity = 0.5;
				obj.style.filter = "alpha(opacity="+50+")";
				$(obj).css('-ms-filter',"alpha(opacity=50)");

				//obj.style.-ms-filter = "progid:DXImageTransform.Microsoft.Alpha(Opacity=50)";
				//obj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=50)';
				template_id_select = template_id;
				template_id_prev = template_id;
		}
	}

	function doTemplateSelect(page_id,action){

		if(template_id_select == ""){
alert('テンプレートを選択してください。');
		}else{
			if (ctrl == null) {
				ctrl = getBusyOverlay(document.getElementById("cvi_busy"), {opacity:0.25, color:"gray", text:""});
			}else{

				ctrl.remove();
				ctrl = null;
			}

			document.form01.action='./'+page_id+'.php';
			document.form01.action_flg.value=action;
			document.form01.template_id.value=template_id_select;
			document.form01.submit();
		}
	}
//-->
</script>
<style type="text/css">
    <!--
	#thumb_01,#thumb_02,#thumb_03,#thumb_04 {
		-ms-filter: "alpha(opacity=100)";
		zoom:1.0;
		width:352;
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
<div id="cvi_busy">
<div id="wrap" class="index">



<div id="contents03" class="clearfix">
<p><img src="img/others/temp_03.jpg" alt="お好きなテンプレートを選択" width="770" height="101"></p>
  <div id="template" class="clearfix">
<p id="temp_left" class="clearfix"><a href="#"><img id="thumb_01" src="img/thumb_01.jpg" width="352" height="130" onClick="javascript:doTemplateCheck('01')"></a></p>
<p id="temp_right" class="clearfix"><a href="#"><img id="thumb_02" src="img/thumb_02.jpg" width="352" height="130" onClick="javascript:doTemplateCheck('02')"></a></p>
<p id="temp_left" class="clearfix"><a href="#"><img id="thumb_03" src="img/thumb_03.jpg" width="352" height="130" onClick="javascript:doTemplateCheck('03')"></a></p>
<p id="temp_right" class="clearfix"><a href="#"><img  id="thumb_04" src="img/thumb_04.jpg" width="352" height="130" onClick="javascript:doTemplateCheck('04')"></a></p>

 </div>
 
 <div id="page_btn">
 <div id="btn_back"><a href="category.php"><img src="img/btn_back.jpg" width="275" height="61" alt="BACK"></a>
 </div>
  <div id="btn_next"><a href="#"><img src="img/btn_next.jpg" width="275" height="61" alt="NEXT" onClick="javascript:doTemplateSelect('picture_tag','check_tag')"></a>
  </div>
 
 </div>

</div>
<input type="hidden" name="action_flg" value="" />
<input type="hidden" name="template_id" value="" />
</div><!--cvi_busy-->
</form>
</body>
</html>
