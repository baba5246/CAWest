<?php
header('p3p: CP="ALL DSP COR PSAa PSDa OUR NOR ONL UNI COM NAV"');
$startTime = microtime(true);

	require './require.php';

	$ua = $_SERVER['HTTP_USER_AGENT'];

	$device_os = array();
	$device_os = get_device_os($ua);
	$device_code =$device_os["device_code"];
	$os_code =$device_os["os_code"];

	$fb_login_url = "";
	$my365_user_id = "";
	$insta_user_id = "";

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
		if($category_id == "tag"){

			$query_url = 'https://graph.facebook.com/me/photos?&access_token='. $access_token;
			$tag_photos_query_result = json_decode(file_get_contents($query_url),true);

outLog($LOG_COVER,"uid_".$fb_user."_".$fb_user_name,$query_url, "error");

			$cnt = 0;
			foreach($tag_photos_query_result["data"] as $data){
				$photo_list_id = $data["id"];
				$photos_list[$cnt]["id"] = $data["id"];
				if(isset($data["name"])){
					$photos_list[$cnt]["name"] = $data["name"];
				}else{
					$photos_list[$cnt]["name"] = "";
				}
				$photos_list[$cnt]["picture"] = $data["picture"];
				$photos_list[$cnt]["source"] = $data["images"][0]["source"];
				//$photos_list[$cnt]["source"] = $data["source"];
				$photos_list[$cnt]["width"] = $data["width"];
				$photos_list[$cnt]["height"] = $data["height"];
				$photos_list[$cnt]["images_2_source"] = $data["images"][5]["source"];
				$photos_list[$cnt]["images_2_width"] = $data["images"][5]["width"];
				$photos_list[$cnt]["images_2_height"] = $data["images"][5]["height"];

				$sql = "SELECT * FROM `crt_cover_fb_uid_tags` ";
				$sql .= " WHERE delete_flg = '0' " ;
				$sql .= " AND  `photos_id` = '".$data["id"]."'" ;
				$sql .= " AND  `modified_unixtime` = '".strtotime($data["updated_time"])."'" ;

				$photos_data = selectList($con, $sql);

				if(isset($photos_data[0]["id"])){
				}else{
					$value_fb_user = $fb_user;
					$value_fb_user_name = mysql_real_escape_string($fb_user_name);
					$value_albums_id = '0';
					$value_photos_id = $data["id"];

					if(isset($data["name"])){
						$value_photos_name = mysql_real_escape_string($data["name"]);
						$value_photos_name = str_replace("\r", "", $value_photos_name);
						$value_photos_name = str_replace("\n", "", $value_photos_name);
						$value_photos_name = str_replace("\t", "", $value_photos_name);
					}else{
						$value_photos_name = "";
					}

					$value_photos_picture = mysql_real_escape_string($data["picture"]);
					$value_photos_source = mysql_real_escape_string($data["images"][0]["source"]);
					//$value_photos_source = mysql_real_escape_string($data["source"]);
					$value_photos_source_2 = mysql_real_escape_string($data["images"][5]["source"]);
					$value_photos_source_width = $data["width"];
					$value_photos_source_height = $data["height"];
					$value_photos_source_2_width = $data["images"][5]["width"];
					$value_photos_source_2_height = $data["images"][5]["height"];

					$value_modified_unixtime = strtotime($data["updated_time"]);
					$value_modified_flg = "0";

					//photos_idの存在チェック
					$sql = "SELECT * FROM `crt_cover_fb_uid_tags` ";
					$sql .= " WHERE delete_flg = '0' " ;
					$sql .= " AND  `photos_id` = '".$data["id"]."'" ;
					$albums_data = selectList($con, $sql);
					
					if($albums_data[0]["id"]){
						$sql  = "update crt_cover_fb_uid_tags set delete_flg = '1' ";
						$sql .= " WHERE delete_flg = '0' " ;
						$sql .= " AND  `photos_id` = '".$data["id"]."'" ;

						$ret = execute($con, $sql) ;
						$value_modified_flg = "1";
					}

					$sql  = " insert into `crt_cover_fb_uid_tags` (`fb_uid`,`fb_user_name`,`albums_id`,`photos_id`,`photos_name`,`photos_picture`,`photos_source`,`photos_source_2`,`photos_source_width`,`photos_source_height`,`photos_source_2_width`,`photos_source_2_height`,`insert_datetime`,`update_datetime`) ";
					$sql .= " values ('".$value_fb_user."','".$value_fb_user_name."','".$value_albums_id."','".$value_photos_id."','".$value_photos_name."','".$value_photos_picture."','".$value_photos_source."','".$value_photos_source_2."','".$value_photos_source_width."','".$value_photos_source_height."','".$value_photos_source_2_width."','".$value_photos_source_2_height."',now(),now())";
					$ret  = execute($con, $sql) ;
				}

				$cnt ++;
			}

			$cmd_tmp = "php ./batch_tags_photos.php ".$fb_user." ";
			exec($cmd_tmp) ;

		}elseif($category_id == "album"){
			//アルバムの取得
			$query_url = 'https://graph.facebook.com/fql?q=SELECT+aid,object_id,cover_pid,cover_object_id,name,size,modified+FROM+album+WHERE+owner+=+me()+ORDER+BY+modified+DESC&access_token='. $access_token ;
			$query_result = json_decode(file_get_contents($query_url),true);


if(!isset($query_result)){
outLog($LOG_COVER,"uid_".$fb_user."_".$fb_user_name,$query_url, "error");
}

			$COVER_CREATOR_NAME = "カバークリエイター Photos";
			$COVER_ALBUM_NAME = "Cover Photos";

			if($query_result["data"]){
				$sql_where  = "";
				$sql_where .= "('";
				$cnt = 0;
				$albums_update_flg = false;
				$albums_list = array();
				foreach($query_result["data"] as $data){
					
					//albums_id/modified_unixtimeの存在チェック
					$sql = "SELECT * FROM `crt_cover_fb_uid_albums` ";
					$sql .= " WHERE delete_flg = '0' " ;
					$sql .= " AND  `albums_id` = '".$data["object_id"]."'" ;
					$sql .= " AND  `modified_unixtime` = '".$data["modified"]."'" ;
					
					$albums_data = selectList($con, $sql);

					if((isset($albums_data[0]["id"]))||($data["cover_object_id"]==0)){
						$cnt ++ ;
						//continue;
					}else if($data["name"] == $COVER_CREATOR_NAME){
						$cnt ++ ;
					}else if($data["name"] == $COVER_ALBUM_NAME){
						$cnt ++ ;
					}else{
						$albums_list[$data["object_id"]]["id"] = $data["object_id"];

						//サムネイル作成
						$cmd_tmp = "php ./batch_album_all_photos.php ".$fb_user." ".$data["object_id"]." ".$access_token." > /dev/null & ";
						exec($cmd_tmp);

						$albums_list[$data["object_id"]]["name"] = $data["name"];
						$albums_list[$data["object_id"]]["cover_photo"] = $data["cover_object_id"];
						//$albums_list[$data["object_id"]]["type"] = $data["type"];
						$albums_list[$data["object_id"]]["albums_photos_number"] = $data["size"];

						$albums_list[$data["object_id"]]["cover_photo_url"] = "";
						$albums_list[$data["object_id"]]["cover_photo_url_width"] = 0;
						$albums_list[$data["object_id"]]["cover_photo_url_height"] = 0;

						$value_fb_uid = $fb_user;
						$value_albums_id = $data["object_id"];
						
						$value_albums_name = mysql_real_escape_string($data["name"]);
						$value_albums_name = str_replace("\r", "", $value_albums_name);
						$value_albums_name = str_replace("\n", "", $value_albums_name);
						$value_albums_name = str_replace("\t", "", $value_albums_name);

						$value_albums_cover_photo_id = $data["cover_object_id"];
						$value_albums_photos_number = $data["size"];
						$value_modified_unixtime = $data["modified"];
						$value_modified_flg = "0";
						
						//albums_idの存在チェック
						$sql = "SELECT * FROM `crt_cover_fb_uid_albums` ";
						$sql .= " WHERE delete_flg = '0' " ;
						$sql .= " AND  `albums_id` = '".$data["object_id"]."'" ;
						$albums_data = selectList($con, $sql);
						
						if(isset($albums_data[0]["id"])){
							$sql  = "update crt_cover_fb_uid_albums set delete_flg = '1' ";
							$sql .= " WHERE delete_flg = '0' " ;
							$sql .= " AND  `albums_id` = '".$data["object_id"]."'" ;

							$ret = execute($con, $sql) ;

							$value_modified_flg = "1";
						}
						
						$sql  = "insert into crt_cover_fb_uid_albums(fb_uid,albums_id,albums_name,albums_cover_photo_id,albums_photos_number,modified_unixtime,modified_flg,insert_datetime,update_datetime) ";
						$sql .= " values ('".$value_fb_uid."','".$value_albums_id."','".$value_albums_name."','".$value_albums_cover_photo_id."','".$value_albums_photos_number."','".$value_modified_unixtime."','".$value_modified_flg."',now(),now())";
						$ret = execute($con, $sql) ;

						$sql_where .= $data["cover_object_id"];
						$albums_update_flg = true;
	
						$cnt ++ ;
					}

					if(count($query_result["data"])==$cnt){
						$sql_where .= "')";
					}elseif((isset($albums_data[0]["id"]))||($data["cover_object_id"]==0)||($data["name"] == $COVER_ALBUM)){
						$sql_where .= "";
					}else{
						$sql_where .= "','";
					}
				}

				if($albums_update_flg){
					$query_url = 'https://graph.facebook.com/fql?q=SELECT+pid,aid,src_big,src_big_width,src_big_height,src,src_width,src_height,object_id,album_object_id+FROM+photo+WHERE+object_id+in+'.$sql_where.'&access_token='. $access_token;
					$query_result = json_decode(file_get_contents($query_url),true);

if(!isset($query_result)){
outLog($LOG_COVER,"uid_".$fb_user."_".$fb_user_name,$query_url, "error");
}

					foreach($query_result["data"] as $data ){

						$albums_list[$data["album_object_id"]]["cover_photo_url"] = $data["src_big"];
						$albums_list[$data["album_object_id"]]["cover_photo_url_width"] = $data["src_big_width"];
						$albums_list[$data["album_object_id"]]["cover_photo_url_height"] = $data["src_big_height"];

						$value_albums_cover_photo_url = $data["src_big"];
						$value_albums_cover_photo_width = $data["src_big_width"];
						$value_albums_cover_photo_height = $data["src_big_height"];

						$sql  = "update crt_cover_fb_uid_albums set ";
						$sql .= "albums_cover_photo_url = '".$value_albums_cover_photo_url."',albums_cover_photo_width = ".$value_albums_cover_photo_width.",albums_cover_photo_height = ".$value_albums_cover_photo_height;
						$sql .= " where albums_cover_photo_id = ".$data["object_id"];

						$ret = execute($con, $sql) ;
					}
				}
				/*
				if(isset($albums_list)){
					foreach($albums_list as $key => $row){
						$albums_photos_number[$key] = $row["albums_photos_number"];
					}
					array_multisort($albums_photos_number,SORT_DESC,$albums_list);
				}
				*/
			}
		
			$cmd_tmp = "php ./batch_album.php ".$fb_user." ";
			exec($cmd_tmp) ;

		}elseif($category_id == "my365"){
			$fb_login_url = "";
			$my365_error_flg = false;
			$my365_error_reason = "";

			$my365_user_id = $fb_user;

//暫定テストユーザー
if($fb_user=='100003706654531'){
$my365_user_id = "1155734596";
$fb_user = "1155734596";
}
			$my365_date_from = "2011-01-01";
			$my365_date_to = date("Y-m-d");

			//$my365_url = $MY365_API_URL."?facebook_id=".$my365_user_id."&to=".$my365_date_to."&key=".$MY365_API_KEY;
			$my365_url = $MY365_API_URL."?facebook_id=".$my365_user_id."&from=".$my365_date_from."&to=".$my365_date_to."&key=".$MY365_API_KEY;
	
			$my365_result = json_decode(file_get_contents($my365_url,true),true);

if(!isset($my365_result)){
outLog($LOG_COVER,"uid_".$fb_user."_".$fb_user_name,$my365_url, "error");
}

			if(isset($my365_result["error"])){
				$my365_error_flg = true;
				$my365_error_reason = "no_id";
			}elseif(!isset($my365_result["response"])){
				$my365_error_flg = true;
				$my365_error_reason = "no_data";
			}else{
			}

			if(isset($my365_result["response"])){
				$cnt = 0;
				foreach($my365_result["response"] as $data){
					$photos_list[$cnt]["id"] = $data["diary_id"];
					$photos_list[$cnt]["diary_date"] = $data["diary_date"];
					$photos_list[$cnt]["url_600"] = $data["url_600"];
					$photos_list[$cnt]["url_150"] = $data["url_150"];

					$sql = "SELECT * FROM `crt_cover_fb_my365_photos` ";
					$sql .= " WHERE delete_flg = '0' " ;
					$sql .= " AND  `diary_id` = '".$data["diary_id"]."'" ;

					$photos_data = selectList($con, $sql);

					if(isset($photos_data[0]["id"])){
					}else{
						$value_fb_uid = $fb_user;
						$value_diary_id = $data["diary_id"];
						$value_diary_date = $data["diary_date"];
						$value_url_600 = $data["url_600"];
						$value_url_150 = $data["url_150"];

						$sql  = "insert into `crt_cover_fb_my365_photos` "; 
						$sql .= " (`fb_uid`,`diary_id`,`diary_date`,`url_600`,`url_150`,`insert_datetime`,`update_datetime`) ";
						$sql .= "value ('".$value_fb_uid."','".$value_diary_id."','".$value_diary_date."','".$value_url_600."','".$value_url_150."',now(),now())";
						$ret = execute($con, $sql) ;
					}
					$cnt ++;
				}
				$cmd_tmp = "php ./batch_my365_photos.php ".$my365_user_id." ".$MY365_API_KEY." ".$my365_date_from." ".$my365_date_to." ";

				exec($cmd_tmp) ;

				//アクセスログ：crt_cover_creator_user
				$sql = "SELECT * FROM `crt_cover_creator_user` ";
				$sql .= " WHERE delete_flg = '0' " ;
				$sql .= " AND  `fb_uid` = '".$fb_user."'" ;
				$sql .= " AND  `useragent` = '".$ua."'" ;

				$user_data = selectList($con, $sql);
				if(isset($user_data[0]["id"])){
					$sql   = "update crt_cover_creator_user set access_count = access_count+1,update_datetime = now() ";
					$sql .= " ,my365_user_id = '".$my365_user_id."' " ;
					$sql .= " WHERE delete_flg = '0' " ;
					$sql .= " AND  `fb_uid` = '".$fb_user."'" ;
					$sql .= " AND  `useragent` = '".$ua."'" ;
					$ret  = execute($con, $sql) ;
				}else{
				}
			}
			
		}elseif($category_id == "instagram"){
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
				$oauth_result = get_instagram_oauth_data($con,$INSTAGRAM_CLIENT_ID,$INSTAGRAM_CLIENT_SECRET,$INSTAGRAM_CALLBACK_URL,$code,$fb_user);

outLog("cover_creator/","aa_".$fb_user,$code,"debug");

				if(isset($oauth_result)){
					$insta_user_id = $oauth_result["user"]["id"];
					$access_token = $oauth_result["access_token"];

if(!isset($access_token)){
outLog($LOG_COVER,"uid_".$fb_user."_".$fb_user_name,$insta_user_id, "error");
}


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
				}
			}


		}elseif($category_id == "safari_instagram"){
			$category_id = "instagram";
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
<style type="text/css">


</style>

<script type="text/javascript">
<!--

var template_id_select = "";
var template_id_prev = "";
var ctrl = null;

	function doTemplateCheck_Select(page_id,action,template_id){
		var obj = document.getElementById("thumb_"+template_id);
		var oElements = obj.childNodes; 

		obj.style.opacity = 0.2;
		obj.style.mozOpacity = 0.2;
		obj.style.filter = "alpha(opacity="+20+")";
		$(obj).css('-ms-filter',"alpha(opacity=20)");
		template_id_select = template_id;

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

	function doTemplateCheck(template_id){
		if(template_id_prev){
			var obj = document.getElementById("thumb_"+template_id_prev);
			var oElements = obj.childNodes; 

				obj.style.opacity = 1.0;
				obj.style.mozOpacity = 1.0;
				obj.style.filter = "alpha(opacity="+100+")";
				$(obj).css('-ms-filter',"alpha(opacity=100)");
		}
		if(template_id_prev == template_id){
			var obj = document.getElementById("thumb_"+template_id);
			var oElements = obj.childNodes; 

				obj.style.opacity = 0.2;
				obj.style.mozOpacity = 0.2;
				obj.style.filter = "alpha(opacity="+20+")";
				$(obj).css('-ms-filter',"alpha(opacity=20)");

				template_id_select = template_id;
				template_id_prev = template_id;

		}else{
			var obj = document.getElementById("thumb_"+template_id);
			var oElements = obj.childNodes; 

				obj.style.opacity = 0.2;
				obj.style.mozOpacity = 0.2;
				obj.style.filter = "alpha(opacity="+20+")";
				$(obj).css('-ms-filter',"alpha(opacity=20)");

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
		zoom:1.0;
		width:352;
	}

	#temp_left img,#temp_right img,#btn_next img{
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
<p><img src="img/others/temp_03.jpg" alt="お好きなテンプレートを選択" width="770" height="101"></p>
  <div id="template" class="clearfix">
<?php if($category_id == "tag"){ ?>
<p id="temp_left" class="clearfix"><img id="thumb_01" src="img/thumb_01.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','01')"></p>
<p id="temp_right" class="clearfix"><img id="thumb_02" src="img/thumb_02.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','02')"></p>
<p id="temp_left" class="clearfix"><img id="thumb_03" src="img/thumb_03.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','03')"></p>
<p id="temp_right" class="clearfix"><img  id="thumb_04" src="img/thumb_04.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','04')"></p>
<p id="temp_left" class="clearfix"><img  id="thumb_05" src="img/thumb_05.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','05')"></p>
<p id="temp_right" class="clearfix"><img  id="thumb_06" src="img/thumb_06.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','06')"></p>
<p id="temp_left" class="clearfix"><img  id="thumb_07" src="img/thumb_07.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','07')"></p>
<p id="temp_right" class="clearfix"><img  id="thumb_08" src="img/thumb_08.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','08')"></p>
<p id="temp_left" class="clearfix"><img  id="thumb_09" src="img/thumb_09.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','09')"></p>
<p id="temp_right" class="clearfix"><img  id="thumb_10" src="img/thumb_10.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','10')"></p>
<?php }elseif($category_id == "album"){ ?>
<p id="temp_left" class="clearfix"><img id="thumb_01" src="img/thumb_01.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','01')"></p>
<p id="temp_right" class="clearfix"><img id="thumb_02" src="img/thumb_02.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','02')"></p>
<p id="temp_left" class="clearfix"><img id="thumb_03" src="img/thumb_03.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','03')"></p>
<p id="temp_right" class="clearfix"><img  id="thumb_04" src="img/thumb_04.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','04')"></p>
<p id="temp_left" class="clearfix"><img  id="thumb_05" src="img/thumb_05.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','05')"></p>
<p id="temp_right" class="clearfix"><img  id="thumb_06" src="img/thumb_06.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','06')"></p>
<p id="temp_left" class="clearfix"><img  id="thumb_07" src="img/thumb_07.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','07')"></p>
<p id="temp_right" class="clearfix"><img  id="thumb_08" src="img/thumb_08.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','08')"></p>
<p id="temp_left" class="clearfix"><img  id="thumb_09" src="img/thumb_09.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','09')"></p>
<p id="temp_right" class="clearfix"><img  id="thumb_10" src="img/thumb_10.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','10')"></p>
<?php }elseif($category_id == "my365"){ ?>
<p id="temp_left" class="clearfix"><img  id="thumb_365_01" src="img/temp_365_01.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','365_01')"></p>
<p id="temp_right" class="clearfix"><img  id="thumb_365_02" src="img/temp_365_02.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','365_02')"></p>

<?php }elseif($category_id == "instagram"){ ?>
<p id="temp_left" class="clearfix"><img  id="thumb_inst_01" src="img/thumb_inst_01.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','inst_01')"></p>
<p id="temp_right" class="clearfix"><img  id="thumb_inst_02" src="img/thumb_inst_02.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','inst_02')"></p>
<p id="temp_left" class="clearfix"><img  id="thumb_inst_03" src="img/thumb_inst_03.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','inst_03')"></p>
<p id="temp_right" class="clearfix"><img  id="thumb_inst_04" src="img/thumb_inst_04.jpg" width="352" height="130" onClick="javascript:doTemplateCheck_Select('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>','inst_04')"></p>

<?php } ?>
 </div>
 
 <div id="page_btn">
 <div id="btn_back"><a href="category.php"><img src="img/btn_back.jpg" width="275" height="61" alt="BACK"></a>
 </div>
  <div id="btn_next"><img src="img/btn_next.jpg" width="275" height="61" alt="NEXT" onClick="javascript:doTemplateSelect('picture_<?php echo($category_id);?>','check_<?php echo($category_id);?>')">
  </div>
 
 </div>

</div>
<input type="hidden" name="action_flg" value="" />
<input type="hidden" name="template_id" value="" />
</div><!--cvi_busy-->
</form>
<?php 
$endTime = microtime(true);

	//アクセスログ
	$my365_user_id = "";
	$insta_user_id = "";
	$access_page = "template_common_".$category_id;
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
