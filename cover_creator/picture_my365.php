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
	$template_id = get_request_value("template_id");

	$photos_id = get_request_value("photos_id");
	$photo_source = get_request_value("photo_source");
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
			outLog($LOG_COVER,"uid_".$fb_user."___".$fb_user_name,$e, "error");
		} 
	}


//暫定テストユーザー
if($fb_user=='100003706654531'){
$my365_user_id = "1155734596";
$fb_user = "1155734596";
}

	if ($fb_user){
		if(($template_id)){
			$sql = "SELECT * FROM `crt_cover_fb_my365_photos` ";
			$sql .= " WHERE delete_flg = '0' " ;
			$sql .= " AND  `fb_uid` = '".$fb_user."'" ;
			
			$photos_data = selectList($con, $sql);

			$cnt = 0;
			foreach($photos_data as $data){
				if(file_exists($LOG_PICTURE."my365/".$fb_user."/photo_".$fb_user."_".$data["diary_id"].".jpeg")){
				}else{
					continue;
				}

				$photos_list[$cnt]["id"] = $data["diary_id"];
				$photos_list[$cnt]["photos_url"] = $fb_user."/photo_".$fb_user."_".$data["diary_id"];
				if($photos_list[$cnt]["photos_url"]){
				}else{
				}
			$cnt ++;
			}
		}

		if($template_id == "365_01"){
			//ファイル名の接尾語を設定：ブラウザキャッシュへの対応
			$cover_file_suffix = "ver_".date("mdHis");
			$loop_cnt = 14;

			if(count($photos_list) < $loop_cnt){
				for($a = 0; $a < $loop_cnt ; $a++){
					$rand_cnt = rand(0,count($photos_list)-1);
					$cover_photos_list[$a] = $photos_list[$rand_cnt];
				}
			}else{
				$cnt = 0;
				$id_array_temp = array();
				while($cnt < $loop_cnt){
					$rand_cnt = rand(0,count($photos_list)-1);
					if(in_array($rand_cnt,$id_array_temp)){
						continue;
					}else{
						$cover_photos_list[$cnt] = $photos_list[$rand_cnt];
						array_push($id_array_temp,$rand_cnt);
						$cnt ++ ;
					}
				}
			}
			//表示用：770*285
			//$screen = imagecreatefromjpeg("./img/picture/thumb_770_".$template_id.".jpg"); 
			//$icon_photo_cover = imagecreatefromjpeg("./img/picture/thumb_770_".$template_id.".jpg"); 
			//$size_photo_cover = getimagesize("./img/picture/thumb_770_".$template_id.".jpg");

			$screen = imagecreatefrompng("./img/picture/thumb_png_".$template_id.".png"); 
			$icon_photo_cover = imagecreatefrompng("./img/picture/thumb_png_".$template_id.".png"); 
			$size_photo_cover = getimagesize("./img/picture/thumb_png_".$template_id.".png");

			$photo_size_list = array(
				"0" => array("width"=>"57","height"=>"57","x"=>"8","y"=>"146","width_org"=>"150","height_org"=>"150"),
				"1" => array("width"=>"57","height"=>"57","x"=>"8","y"=>"216","width_org"=>"150","height_org"=>"150"),
				"2" => array("width"=>"57","height"=>"57","x"=>"78","y"=>"146","width_org"=>"150","height_org"=>"150"),
				"3" => array("width"=>"57","height"=>"57","x"=>"78","y"=>"216","width_org"=>"150","height_org"=>"150"),
				"4" => array("width"=>"57","height"=>"57","x"=>"147","y"=>"146","width_org"=>"150","height_org"=>"150"),
				"5" => array("width"=>"57","height"=>"57","x"=>"147","y"=>"216","width_org"=>"150","height_org"=>"150"),
				"6" => array("width"=>"57","height"=>"57","x"=>"217","y"=>"146","width_org"=>"150","height_org"=>"150"),
				"7" => array("width"=>"57","height"=>"57","x"=>"217","y"=>"216","width_org"=>"150","height_org"=>"150"),
				"8" => array("width"=>"57","height"=>"57","x"=>"286","y"=>"146","width_org"=>"150","height_org"=>"150"),
				"9" => array("width"=>"57","height"=>"57","x"=>"286","y"=>"216","width_org"=>"150","height_org"=>"150"),
				"10" => array("width"=>"57","height"=>"57","x"=>"356","y"=>"146","width_org"=>"150","height_org"=>"150"),
				"11" => array("width"=>"57","height"=>"57","x"=>"356","y"=>"216","width_org"=>"150","height_org"=>"150"),
				"12" => array("width"=>"57","height"=>"57","x"=>"425","y"=>"146","width_org"=>"150","height_org"=>"150"),
				"13" => array("width"=>"57","height"=>"57","x"=>"425","y"=>"216","width_org"=>"150","height_org"=>"150")
			);

			foreach($photo_size_list as $key => $value){
				if(file_exists($LOG_PICTURE."my365/".$fb_user."/photo_".$fb_user."_".$cover_photos_list[$key]["id"].".jpeg")){
					$screen_photo_source = imagecreatefromjpeg($LOG_PICTURE."my365/".$fb_user."/photo_".$fb_user."_".$cover_photos_list[$key]["id"].".jpeg"); 
				}else{
					$screen_photo_source = imagecreatefromjpeg($cover_photos_list[$key]["url_150"]); 
					$ret = imagejpeg($screen_photo_source,$LOG_PICTURE."my365/".$fb_user."/photo_".$fb_user."_".$cover_photos_list[$key]["id"].".jpeg");
				}
				$screen_photo_source_new = imagecreatetruecolor($value["width"],$value["height"]);
				$ret = imagecopyresampled($screen_photo_source_new, $screen_photo_source, 0, 0, 0, 0, $value["width"],$value["height"],$value["width_org"],$value["height_org"]);
				$ret = ImageCopy($screen,$screen_photo_source_new,$value["x"],$value["y"],0,0,$value["width"],$value["height"]);
				imagedestroy($screen_photo_source);
				imagedestroy($screen_photo_source_new);
			}

			$ret = imagecopy($screen,$icon_photo_cover,0,0,0,0,$size_photo_cover[0],$size_photo_cover[1]);
			$ret = imagejpeg($screen,$LOG_PICTURE."output/display_photo_".$fb_user."_".$cover_file_suffix.".jpeg");
			imagedestroy($icon_photo_cover);
			imagedestroy($screen);

			//出力用
			$screen = imagecreatefrompng("./img/cover/template_".$template_id.".png"); 
			$icon_photo_cover = imagecreatefrompng("./img/cover/template_".$template_id.".png"); 
			$size_photo_cover = getimagesize("./img/cover/template_".$template_id.".png");

			$photo_size_list = array(
				"0" => array("width"=>"62","height"=>"62","x"=>"9","y"=>"161","width_org"=>"150","height_org"=>"150"),
				"1" => array("width"=>"62","height"=>"62","x"=>"9","y"=>"239","width_org"=>"150","height_org"=>"150"),
				"2" => array("width"=>"62","height"=>"62","x"=>"86","y"=>"161","width_org"=>"150","height_org"=>"150"),
				"3" => array("width"=>"62","height"=>"62","x"=>"86","y"=>"239","width_org"=>"150","height_org"=>"150"),
				"4" => array("width"=>"62","height"=>"62","x"=>"163","y"=>"161","width_org"=>"150","height_org"=>"150"),
				"5" => array("width"=>"62","height"=>"62","x"=>"163","y"=>"239","width_org"=>"150","height_org"=>"150"),
				"6" => array("width"=>"62","height"=>"62","x"=>"240","y"=>"161","width_org"=>"150","height_org"=>"150"),
				"7" => array("width"=>"62","height"=>"62","x"=>"240","y"=>"239","width_org"=>"150","height_org"=>"150"),
				"8" => array("width"=>"62","height"=>"62","x"=>"316","y"=>"161","width_org"=>"150","height_org"=>"150"),
				"9" => array("width"=>"62","height"=>"62","x"=>"316","y"=>"239","width_org"=>"150","height_org"=>"150"),
				"10" => array("width"=>"62","height"=>"62","x"=>"393","y"=>"161","width_org"=>"150","height_org"=>"150"),
				"11" => array("width"=>"62","height"=>"62","x"=>"393","y"=>"239","width_org"=>"150","height_org"=>"150"),
				"12" => array("width"=>"62","height"=>"62","x"=>"470","y"=>"161","width_org"=>"150","height_org"=>"150"),
				"13" => array("width"=>"62","height"=>"62","x"=>"470","y"=>"239","width_org"=>"150","height_org"=>"150")
			);

			foreach($photo_size_list as $key => $value){
				if(file_exists($LOG_PICTURE."my365/".$fb_user."/photo_".$fb_user."_".$cover_photos_list[$key]["id"].".jpeg")){
					$screen_photo_source = imagecreatefromjpeg($LOG_PICTURE."my365/".$fb_user."/photo_".$fb_user."_".$cover_photos_list[$key]["id"].".jpeg"); 
				}else{
					$screen_photo_source = imagecreatefromjpeg($cover_photos_list[$key]["url_150"]); 
					$ret = imagejpeg($screen_photo_source,$LOG_PICTURE."my365/".$fb_user."/photo_".$fb_user."_".$cover_photos_list[$key]["id"].".jpeg");
				}
				$screen_photo_source_new = imagecreatetruecolor($value["width"],$value["height"]);
				$ret = imagecopyresampled($screen_photo_source_new, $screen_photo_source, 0, 0, 0, 0, $value["width"],$value["height"],$value["width_org"],$value["height_org"]);
				$ret = ImageCopy($screen,$screen_photo_source_new,$value["x"],$value["y"],0,0,$value["width"],$value["height"]);
				imagedestroy($screen_photo_source);
				imagedestroy($screen_photo_source_new);
			}
			$ret = imagecopy($screen,$icon_photo_cover,0,0,0,0,$size_photo_cover[0],$size_photo_cover[1]);
			$ret = imagejpeg($screen,$LOG_PICTURE."output/photo_".$fb_user."_".$cover_file_suffix.".jpeg");
			imagedestroy($icon_photo_cover);
			imagedestroy($screen);

		}elseif($template_id == "365_02"){

			//ファイル名の接尾語を設定：ブラウザキャッシュへの対応
			$cover_file_suffix = "ver_".date("mdHis");
			$loop_cnt = 7;

			if(count($photos_list) < $loop_cnt){
				for($a = 0; $a < $loop_cnt ; $a++){
					$rand_cnt = rand(0,count($photos_list)-1);
					$cover_photos_list[$a] = $photos_list[$rand_cnt];
				}
			}else{
				$cnt = 0;
				$id_array_temp = array();
				while($cnt < $loop_cnt){
					$rand_cnt = rand(0,count($photos_list)-1);
					if(in_array($rand_cnt,$id_array_temp)){
						continue;
					}else{
						$cover_photos_list[$cnt] = $photos_list[$rand_cnt];
						array_push($id_array_temp,$rand_cnt);
						$cnt ++ ;
					}
				}
			}

			//表示用：770*285
			$screen = imagecreatefrompng("./img/picture/thumb_png_".$template_id.".png"); 
			$icon_photo_cover = imagecreatefrompng("./img/picture/thumb_png_".$template_id.".png"); 
			$size_photo_cover = getimagesize("./img/picture/thumb_png_".$template_id.".png");

			$photo_size_list = array(
				"0" => array("width"=>"55","height"=>"55","x"=>"9","y"=>"147","width_org"=>"150","height_org"=>"150"),
				"1" => array("width"=>"55","height"=>"55","x"=>"9","y"=>"216","width_org"=>"150","height_org"=>"150"),
				"2" => array("width"=>"123","height"=>"123","x"=>"80","y"=>"147","width_org"=>"150","height_org"=>"150"),
				"3" => array("width"=>"55","height"=>"55","x"=>"218","y"=>"147","width_org"=>"150","height_org"=>"150"),
				"4" => array("width"=>"55","height"=>"55","x"=>"288","y"=>"147","width_org"=>"150","height_org"=>"150"),
				"5" => array("width"=>"123","height"=>"123","x"=>"220","y"=>"221","width_org"=>"150","height_org"=>"150"),
				"6" => array("width"=>"123","height"=>"123","x"=>"359","y"=>"147","width_org"=>"150","height_org"=>"150")
			);

			foreach($photo_size_list as $key => $value){
				if(file_exists($LOG_PICTURE."my365/".$fb_user."/photo_".$fb_user."_".$cover_photos_list[$key]["id"].".jpeg")){
					$screen_photo_source = imagecreatefromjpeg($LOG_PICTURE."my365/".$fb_user."/photo_".$fb_user."_".$cover_photos_list[$key]["id"].".jpeg"); 
				}else{
					$screen_photo_source = imagecreatefromjpeg($cover_photos_list[$key]["url_150"]); 
					$ret = imagejpeg($screen_photo_source,$LOG_PICTURE."my365/".$fb_user."/photo_".$fb_user."_".$cover_photos_list[$key]["id"].".jpeg");
				}
				
				$screen_photo_source_new = imagecreatetruecolor($value["width"],$value["height"]);
				$ret = imagecopyresampled($screen_photo_source_new, $screen_photo_source, 0, 0, 0, 0, $value["width"],$value["height"],$value["width_org"],$value["height_org"]);
				$ret = ImageCopy($screen,$screen_photo_source_new,$value["x"],$value["y"],0,0,$value["width"],$value["height"]);
				imagedestroy($screen_photo_source);
				imagedestroy($screen_photo_source_new);
			}

			$ret = imagecopy($screen,$icon_photo_cover,0,0,0,0,$size_photo_cover[0],$size_photo_cover[1]);
			$ret = imagejpeg($screen,$LOG_PICTURE."output/display_photo_".$fb_user."_".$cover_file_suffix.".jpeg");
			imagedestroy($icon_photo_cover);
			imagedestroy($screen);

			//出力用
			$screen = imagecreatefrompng("./img/cover/template_".$template_id.".png"); 
			$icon_photo_cover = imagecreatefrompng("./img/cover/template_".$template_id.".png"); 
			$size_photo_cover = getimagesize("./img/cover/template_".$template_id.".png");

			$photo_size_list = array(
				"0" => array("width"=>"62","height"=>"62","x"=>"10","y"=>"162","width_org"=>"150","height_org"=>"150"),
				"1" => array("width"=>"62","height"=>"62","x"=>"10","y"=>"238","width_org"=>"150","height_org"=>"150"),
				"2" => array("width"=>"133","height"=>"133","x"=>"89","y"=>"162","width_org"=>"150","height_org"=>"150"),
				"3" => array("width"=>"62","height"=>"62","x"=>"241","y"=>"162","width_org"=>"150","height_org"=>"150"),
				"4" => array("width"=>"62","height"=>"62","x"=>"318","y"=>"162","width_org"=>"150","height_org"=>"150"),
				"5" => array("width"=>"139","height"=>"139","x"=>"241","y"=>"243","width_org"=>"150","height_org"=>"150"),
				"6" => array("width"=>"137","height"=>"137","x"=>"395","y"=>"162","width_org"=>"150","height_org"=>"150")
			);

			foreach($photo_size_list as $key => $value){
				if(file_exists($LOG_PICTURE."my365/".$fb_user."/photo_".$fb_user."_".$cover_photos_list[$key]["id"].".jpeg")){
					$screen_photo_source = imagecreatefromjpeg($LOG_PICTURE."my365/".$fb_user."/photo_".$fb_user."_".$cover_photos_list[$key]["id"].".jpeg"); 
				}else{
					$screen_photo_source = imagecreatefromjpeg($cover_photos_list[$key]["url_150"]); 
					$ret = imagejpeg($screen_photo_source,$LOG_PICTURE."my365/".$fb_user."/photo_".$fb_user."_".$cover_photos_list[$key]["id"].".jpeg");
				}
				
				$screen_photo_source_new = imagecreatetruecolor($value["width"],$value["height"]);
				$ret = imagecopyresampled($screen_photo_source_new, $screen_photo_source, 0, 0, 0, 0, $value["width"],$value["height"],$value["width_org"],$value["height_org"]);
				$ret = ImageCopy($screen,$screen_photo_source_new,$value["x"],$value["y"],0,0,$value["width"],$value["height"]);
				imagedestroy($screen_photo_source);
				imagedestroy($screen_photo_source_new);
			}
			$ret = imagecopy($screen,$icon_photo_cover,0,0,0,0,$size_photo_cover[0],$size_photo_cover[1]);
			$ret = imagejpeg($screen,$LOG_PICTURE."output/photo_".$fb_user."_".$cover_file_suffix.".jpeg");
			imagedestroy($icon_photo_cover);
			imagedestroy($screen);
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
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js" type="text/javascript"></script>
<link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/ui-lightness/jquery-ui.css" rel="stylesheet" type="text/css" />  
<script src="./js/cvi_busy_lib.js"></script>
<link rel="stylesheet" type="text/css" href="css/common.css">
<link href="./css/jquery.dragscroll.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
<!--

var ctrl = null;

	function doPhotosSelect(page_id,action,template_id,category_id,cover_file_suffix){
		
		if (ctrl == null) {
			ctrl = getBusyOverlay(document.getElementById("cvi_busy"), {opacity:0.25, color:"gray", text:""});
		}else{
			ctrl.remove();
			ctrl = null;
		}

		document.form01.action='./'+page_id+'.php';
		document.form01.action_flg.value=action;
		document.form01.template_id.value=template_id;
		document.form01.category_id.value=category_id;
		document.form01.cover_file_suffix.value=cover_file_suffix;

		document.form01.submit();
	}


//-->
</script>
<style type="text/css">
    <!--
	#page_btn img,#btn_next img{
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
<p><img src="<?php echo($LOG_PICTURE."output/display_photo_".$fb_user."_".$cover_file_suffix.".jpeg");?>" width="770" height="285"></p>

<div class="btn_check">
<p><img src="img/others/btn_change.jpg" alt="CHANGE PHOTOS" width="165" height="30" border="0" onClick="javascript:doPhotosSelect('picture_my365','HOGE','<?php echo($template_id);?>','my365','<?php echo($cover_file_suffix);?>')" style="cursor:pointer;"></p>
</div>

<div id="page_btn">
<div id="btn_back"><img src="img/btn_back.jpg" width="275" height="61" alt="BACK" onClick="javascript:doPhotosSelect('template_common','HOGE','<?php echo($template_id);?>','my365','<?php echo($cover_file_suffix);?>')">
</div>
<div id="btn_next"><img src="img/btn_save.jpg" width="275" height="61" alt="SAVE" onClick="javascript:doPhotosSelect('thanks','photos_wall','<?php echo($template_id);?>','my365','<?php echo($cover_file_suffix);?>')">
</div>
 
</div><!--id="page_btn"-->

<input type="hidden" name="action_flg" value="" />
<input type="hidden" name="template_id" value="" />
<input type="hidden" name="category_id" value="" />
<input type="hidden" name="page_id" value="" />
<input type="hidden" name="cover_file_suffix" value="" />
</div><!--id="contents03" class="clearfix"-->
</div><!--id="wrap" class="index"-->
</div><!--cvi_busy-->
</form>
<?php 
$endTime = microtime(true);

	//アクセスログ
	$my365_user_id = "";
	$insta_user_id = "";
	$access_page = "picture_my365_".$template_id;
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
