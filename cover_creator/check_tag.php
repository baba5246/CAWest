<?php
//header('Content-Type: text/html; charset=UTF-8');
header('p3p: CP="ALL DSP COR PSAa PSDa OUR NOR ONL UNI COM NAV"');
$startTime = microtime(true);

	$ua = $_SERVER['HTTP_USER_AGENT'];

	require './require.php';

	$device_os = array();
	$device_os = get_device_os($ua);
	$device_code =$device_os["device_code"];
	$os_code =$device_os["os_code"];

	$fb_login_url = "";

	//リクエスト
	$page_id = get_request_value("page_id");
	$action_flg = get_request_value("action_flg");
	$page_id = get_request_value("page_id");
	$template_id = get_request_value("template_id");

	$photos_id = get_request_value("photos_id");
	$photo_source = get_request_value("photo_source");

	$page_no_photo = get_request_value("page_no_photo");
	if($page_no_photo){
	}else{
		$page_no_photo = 1;
	}

	$cover_file_suffix = get_request_value("cover_file_suffix");
	$photo_picture_path = get_request_value("photo_picture_path");
	$cover_position_top = get_request_value("cover_position_top");

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
		if($action_flg=="check_tag"){
			$ret = make_image_photo($fb_user,$fb_user_name,$template_id,$photo_picture_path,$cover_position_top,$cover_file_suffix,$LOG_PICTURE);
		}
	}else{
		//ログインURL取得
		$fb_login_url = get_fb_login_url($facebook,$SCOPE_COVER,$device_code);
	}

	function make_image_photo($fb_user,$fb_user_name,$template_id,$photo_picture_path,$cover_position_top=null,$cover_file_suffix,$log_picture) {

		//表示用：770*285
		$screen = imagecreatefrompng("./img/picture/thumb_png_".$template_id.".png"); 
		$icon_photo_cover = imagecreatefrompng("./img/picture/thumb_png_".$template_id.".png"); 
		$size_photo_cover = getimagesize("./img/picture/thumb_png_".$template_id.".png");

		$photo_picture_path_list = explode(",",$photo_picture_path);

		if($template_id){
			$icon_photo_picture_0 = imagecreatefromjpeg($log_picture."tags/".$fb_user."/display_photo_".$fb_user."_".$photo_picture_path_list[0].".jpeg"); 
			$size_photo_picture_0 = getimagesize($log_picture."tags/".$fb_user."/display_photo_".$fb_user."_".$photo_picture_path_list[0].".jpeg");
			if(!$cover_position_top){
				$cover_position_top = 0;
			}
			$ret = ImageCopy($screen,$icon_photo_picture_0,0,0,0,$cover_position_top,$size_photo_picture_0[0],$size_photo_picture_0[1]);
		}else{
		}
		
		$ret = ImageCopy($screen,$icon_photo_cover,0,0,0,0,$size_photo_cover[0],$size_photo_cover[1]);
		$ret = imagejpeg($screen,$log_picture."output/display_photo_".$fb_user."_".$cover_file_suffix.".jpeg");
		imagedestroy($icon_photo_cover);
		imagedestroy($screen);

		//出力用：851*315
		$screen = imagecreatefrompng("./img/cover/template_".$template_id.".png"); 
		$icon_photo_cover = imagecreatefrompng("./img/cover/template_".$template_id.".png"); 
		$size_photo_cover = getimagesize("./img/cover/template_".$template_id.".png");

		$photo_picture_path_list = explode(",",$photo_picture_path);

		if($template_id){
			$icon_photo_picture_0 = imagecreatefromjpeg($log_picture."tags/".$fb_user."/photo_".$fb_user."_".$photo_picture_path_list[0].".jpeg"); 
			$size_photo_picture_0 = getimagesize($log_picture."tags/".$fb_user."/photo_".$fb_user."_".$photo_picture_path_list[0].".jpeg");

			if(!$cover_position_top){
				$cover_position_top = 0;
			}
			$ret = ImageCopy($screen,$icon_photo_picture_0,0,0,0,$cover_position_top,$size_photo_picture_0[0],$size_photo_picture_0[1]);
		
		}else{
		}
		
		$ret = ImageCopy($screen,$icon_photo_cover,0,0,0,0,$size_photo_cover[0],$size_photo_cover[1]);
		$ret = imagejpeg($screen,$log_picture."output/photo_".$fb_user."_".$cover_file_suffix.".jpeg");

		imagedestroy($icon_photo_cover);
		imagedestroy($screen);


		return true ;
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

	function doPhotosSelect(page_id,action,template_id,page_no_photo){
		
		if (ctrl == null) {
			ctrl = getBusyOverlay(document.getElementById("cvi_busy"), {opacity:0.25, color:"gray", text:""});
		}else{
			ctrl.remove();
			ctrl = null;
		}

		document.form01.action='./'+page_id+'.php';
		document.form01.action_flg.value=action;
		document.form01.template_id.value=template_id;
		document.form01.page_no.value=page_no_photo;
		document.form01.submit();
	}

//-->
</script>
<style type="text/css">
    <!--
	#btn_back img,#btn_next img{
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

<div id="contents03" class="clearfix">
<p><img src="img/others/app_check_01.jpg" alt="こちらのカバーでよろしいでしょうか？" width="770" height="101"></p>
<p><img src="<?php echo($LOG_PICTURE."output/display_photo_".$fb_user."_".$cover_file_suffix.".jpeg")?>" width="770" height="285"></p>

<div id="page_btn">
<div id="btn_back"><img src="img/btn_back.jpg" width="275" height="61" alt="BACK" onClick="javascript:doPhotosSelect('picture_tag','photos_select','<?php echo($template_id);?>','<?php echo($page_no_photo);?>')">
</div>

<div id="btn_next"><img src="img/btn_save.jpg" width="275" height="61" alt="SAVE" onClick="javascript:doPhotosSelect('thanks','photos_wall','<?php echo($template_id);?>','<?php echo($page_no_photo);?>')">
</div>
 
</div><!-- id="page_btn" -->

<input type="hidden" name="action_flg" value="" />
<input type="hidden" name="template_id" value="" />
<input type="hidden" name="photos_id" value="" />
<input type="hidden" name="category_id" value="tag" />
<input type="hidden" name="photo_source" value="<?php echo($photo_source);?>" />
<input type="hidden" name="cover_file_suffix" value="<?php echo($cover_file_suffix);?>" />
<input type="hidden" name="page_id" value="" />
<input type="hidden" name="photo_picture_path" value="<?php echo($photo_picture_path);?>" />
<input type="hidden" name="page_no" value="" />
</div><!-- id="contents03" class="clearfix" -->
</div><!--id="wrap" class="index"-->
</div><!--cvi_busy-->
</form>
<?php 
$endTime = microtime(true);

	//アクセスログ
	$my365_user_id = "";
	$insta_user_id = "";
	$access_page = "check_tag_".$action_flg."_".$template_id;
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
