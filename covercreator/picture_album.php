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
	$action_flg = get_request_value("action_flg");
	$template_id = get_request_value("template_id");

	$photos_id = get_request_value("photos_id");
	$photo_source = get_request_value("photo_source");
	$cover_file_suffix = get_request_value("cover_file_suffix");
	$page_id = get_request_value("page_id");
	if($page_id){}else{
		$page_id = 1;
	}
	$page_no = get_request_value("page_no");
	if($page_no){}else{
		$page_no = 1;
	}

	$page_no_album = get_request_value("page_no_album");
	if($page_no_album){}else{
		$page_no_album = 1;
	}


	$photo_place = get_request_value("photo_place");
	if(!$photo_place){
		$photo_place = 1;
	}

	//クラス
	$facebook = new Facebook(array('appId' => $APPID_COVER,
	                              'secret' => $SECRET_COVER,
	                              'cookie' => true,
	                        ));
	//ユーザー認証
	$fb_user_name = "";
	$fb_user_gender = "";

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
		if(($template_id)){
			$query_url = 'https://graph.facebook.com/fql?q=SELECT+aid,object_id,cover_pid,cover_object_id,name,size,modified+FROM+album+WHERE+owner+=+me()&access_token='. $access_token;
			$query_result = json_decode(file_get_contents($query_url),true);
			$cnt = 0;

			$COVER_CREATOR_NAME = "カバークリエイター Photos";
			$COVER_ALBUM_NAME = "Cover Photos";

			foreach($query_result["data"] as $data){
				if($data["cover_object_id"] == "0"){
					continue;
				}elseif($data["name"] == $COVER_CREATOR_NAME){
					continue;
				}elseif($data["name"] == $COVER_ALBUM_NAME){
					continue;
				}

				$album_list[$cnt]["id"] = $data["object_id"];

				$album_list[$cnt]["albums_name"] = $data["name"];
				$album_list[$cnt]["modified"] = $data["modified"];
				$album_list[$cnt]["albums_url"] = $fb_user."/thumb_".$fb_user."_".$data["object_id"];
				if($album_list[$cnt]["albums_url"]){
				}else{
				}
			$cnt ++;
			}

			foreach($album_list as $key => $row){
				$sort_modified[$key] = $row["modified"];
			}
			array_multisort($sort_modified,SORT_DESC,$album_list);

		}

		//ページング
		$data_cnt = count($album_list);
		$_limit = 4;
		$total_page = (int)ceil($data_cnt/$_limit);
		$rest_page = $data_cnt % $_limit;

		$page = $page_id;
		$offset = (($_limit*$page_id) - $_limit);

		if($action_flg=="photos_select"){
				if($photo_place == 1){
					//ファイル名の接尾語を設定：ブラウザキャッシュへの対応
					$cover_file_suffix = "ver_".date("mdHis");

					$screen = imagecreatefromjpeg($photo_source); 
					$size_photo_source = getimagesize($photo_source);
					//720×480→770×285:widthで合わせる。
					//720×480→770×*:widthで合わせる。
					$height_new = ((770*$size_photo_source[1])/$size_photo_source[0]);
					$screen_new = imagecreatetruecolor(770,$height_new);
					$ret = imagecopyresampled($screen_new, $screen, 0, 0, 0, 0, 770,$height_new, $size_photo_source[0],$size_photo_source[1]);
					$ret = imagejpeg($screen_new,$LOG_PICTURE."album/".$fb_user."/photo_".$fb_user."_".$photos_id."_".$photo_place.".jpeg");

					$photo_picture_path = $photos_id."_".$photo_place;
					$photo_place = 1;
				}else{}	
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
var photos_select_flg = "";
var ctrl = null;
var page_no = 1;

var album_id_select = "";
var album_id_prev = "";


	function doAlbumCheck_Select(fb_user,album_id,access_token,page_id,action){
		$(function(){
			var data = {fb_user:fb_user,album_id:album_id,access_token:access_token};
			$.ajax({
				type: "POST",
				url: "./batch_album_photos_ajax.php",
				data: data,
				success: function(){
//					alert('success');
				}
			});
		});
	
		var obj = document.getElementById("thumb_"+album_id);
		var oElements = obj.childNodes; 

		obj.style.opacity = 0.5;
		obj.style.mozOpacity = 0.5;
		obj.style.filter = "alpha(opacity="+50+")";
		$(obj).css('-ms-filter',"alpha(opacity=50)");

		album_id_select = album_id;

		if (ctrl == null) {
			ctrl = getBusyOverlay(document.getElementById("cvi_busy"), {opacity:0.25, color:"gray", text:""});
		}else{
			ctrl.remove();
			ctrl = null;
		}

		document.form01.action='./'+page_id+'.php';
		document.form01.action_flg.value=action;
		document.form01.album_id.value=album_id_select;
		document.form01.page_no_album.value=page_no;
		document.form01.submit();
	}

	function doAlbumCheck(fb_user,album_id,access_token){
		$(function(){
			var data = {fb_user:fb_user,album_id:album_id,access_token:access_token};
			$.ajax({
				type: "POST",
				url: "./batch_album_photos_ajax.php",
				data: data,
				success: function(){
//					alert('success');
				}
			});
		});

		if(album_id_prev){
			var obj = document.getElementById("thumb_"+album_id_prev);
			var oElements = obj.childNodes; 

				obj.style.opacity = 1.0;
				obj.style.mozOpacity = 1.0;
				obj.style.filter = "alpha(opacity="+100+")";
				$(obj).css('-ms-filter',"alpha(opacity=100)");
		}
		if(album_id_prev == album_id){
			var obj = document.getElementById("thumb_"+album_id);
			var oElements = obj.childNodes; 

				obj.style.opacity = 0.5;
				obj.style.mozOpacity = 0.5;
				obj.style.filter = "alpha(opacity="+50+")";
				$(obj).css('-ms-filter',"alpha(opacity=50)");

				album_id_select = album_id;
				album_id_prev = album_id;

		}else{
			var obj = document.getElementById("thumb_"+album_id);
			var oElements = obj.childNodes; 

				obj.style.opacity = 0.5;
				obj.style.mozOpacity = 0.5;
				obj.style.filter = "alpha(opacity="+50+")";
				$(obj).css('-ms-filter',"alpha(opacity=50)");

				album_id_select = album_id;
				album_id_prev = album_id;
		}
	}

	function doAlbumSelect(page_id,action){

		if(album_id_select == ""){
alert('アルバムを選択してください。');
		}else{

			if (ctrl == null) {
				ctrl = getBusyOverlay(document.getElementById("cvi_busy"), {opacity:0.25, color:"gray", text:""});
			}else{

				ctrl.remove();
				ctrl = null;
			}

			document.form01.action='./'+page_id+'.php';
			document.form01.action_flg.value=action;
			document.form01.album_id.value=album_id_select;
			document.form01.page_no_album.value=page_no;
			document.form01.submit();
		}
	}

	function doSearchDefault(page_no_param){
		page_no = page_no_param;

		document.getElementById("page_no").innerHTML="";
		document.getElementById("page_no").innerHTML="<p>ページ:"+page_no+"/&nbsp;<?php echo($total_page);?>&nbsp;&nbsp;<img src=\"img/btn-prev01.png\" alt=\"前のページへ\" border=\"0\" onClick=\"javascript:doSearch(\'prev\')\" style=\"cursor:pointer;\"><img src=\"img/btn-next01.png\" alt=\"次のページへ\" border=\"0\" onClick=\"javascript:doSearch(\'next\')\" style=\"cursor:pointer;\"></p>"

		<?php 
		$total_page_plus = $total_page + 1 ;
		$rest_page_plus = $rest_page + 1 ;
		?>
		
<?php if($total_page ==1){ ?>

<?php }else{ ?>
		<?php //非表示制御?>
		for (i = 1; i < <?php echo($total_page);?>; i = i +1){
			for (ii = 1; ii < (<?php echo($_limit);?>+1); ii = ii +1){
				var ele = document.getElementById("cover_photo_"+i+"_"+ii);
				ele.style.display = "none";
				//var ele_name = document.getElementById("cover_name_"+i+"_"+ii);
				//ele_name.style.display = "none";
			}
		}

		<?php if($rest_page > 0){ ?>
		<?php //非表示制御:最終ページ:rest部分?>

			for (i = 1; i < <?php echo($rest_page_plus);?>; i = i +1){
				var ele = document.getElementById("cover_photo_"+<?php echo($total_page);?>+"_"+i);
				ele.style.display = "none";
				//var ele_name = document.getElementById("cover_name_"+<?php echo($total_page);?>+"_"+i);
				//ele_name.style.display = "none";
			}
		<?php }else{ ?>
		<?php //非表示制御:最終ページ?>
			for (ii = 1; ii < (<?php echo($_limit);?>+1); ii = ii +1){
				var ele = document.getElementById("cover_photo_"+<?php echo($total_page);?>+"_"+ii);
				ele.style.display = "none";
				//var ele_name = document.getElementById("cover_name_"+<?php echo($total_page);?>+"_"+ii);
				//ele_name.style.display = "none";
			}
		<?php } ?>

		
		<?php if($rest_page == 0){ ?>
		<?php //表示制御:rest部分無?>
			for (ii = 1; ii < (<?php echo($_limit);?>+1); ii = ii +1){
				var ele = document.getElementById("cover_photo_"+page_no+"_"+ii);
				ele.style.display = "block";
				//var ele_name = document.getElementById("cover_name_"+page_no+"_"+ii);
				//ele_name.style.display = "block";
			}
		<?php }else{ ?>
		<?php //表示制御:rest部分有?>

			if(page_no == <?php echo($total_page);?>){
			<?php //最終ページ?>
				for (i = 1; i < <?php echo($rest_page_plus);?>; i = i +1){

					var ele = document.getElementById("cover_photo_"+page_no+"_"+i);
					ele.style.display = "block";
					//var ele_name = document.getElementById("cover_name_"+page_no+"_"+i);
					//ele_name.style.display = "block";
				}
			}else{
				for (ii = 1; ii < (<?php echo($_limit);?>+1); ii = ii +1){
					var ele = document.getElementById("cover_photo_"+page_no+"_"+ii);
					ele.style.display = "block";
					//var ele_name = document.getElementById("cover_name_"+page_no+"_"+ii);
					//ele_name.style.display = "block";
				}
			}
		<?php } ?>
<?php } ?>

	}

	function doSearch(paging){

		if(paging == "prev"){
			if(page_no == 1){
				page_no = 1;
			}else{
				page_no = page_no - 1;
			}

		}else if(paging == "next"){
			if(page_no == <?php echo($total_page);?>){
				page_no = <?php echo($total_page);?>;
			}else{
				page_no = page_no + 1 ;
			}
		}
		
		document.getElementById("page_no").innerHTML="";
		document.getElementById("page_no").innerHTML="<p>ページ:"+page_no+"/&nbsp;<?php echo($total_page);?>&nbsp;&nbsp;<img src=\"img/btn-prev01.png\" alt=\"前のページへ\" border=\"0\" onClick=\"javascript:doSearch(\'prev\')\" style=\"cursor:pointer;\"><img src=\"img/btn-next01.png\" alt=\"次のページへ\" border=\"0\" onClick=\"javascript:doSearch(\'next\')\" style=\"cursor:pointer;\"></p>"


		<?php 
		$total_page_plus = $total_page + 1 ;
		$rest_page_plus = $rest_page + 1 ;
		?>
		
<?php if($total_page ==1){ ?>

<?php }else{ ?>
		<?php //非表示制御?>
		for (i = 1; i < <?php echo($total_page);?>; i = i +1){
			for (ii = 1; ii < (<?php echo($_limit);?>+1); ii = ii +1){
				var ele = document.getElementById("cover_photo_"+i+"_"+ii);
				ele.style.display = "none";
				//var ele_name = document.getElementById("cover_name_"+i+"_"+ii);
				//ele_name.style.display = "none";
			}
		}

		<?php if($rest_page > 0){ ?>
		<?php //非表示制御:最終ページ:rest部分?>

			for (i = 1; i < <?php echo($rest_page_plus);?>; i = i +1){
				var ele = document.getElementById("cover_photo_"+<?php echo($total_page);?>+"_"+i);
				ele.style.display = "none";
				//var ele_name = document.getElementById("cover_name_"+<?php echo($total_page);?>+"_"+i);
				//ele_name.style.display = "none";
			}
		<?php }else{ ?>
		<?php //非表示制御:最終ページ?>
			for (ii = 1; ii < (<?php echo($_limit);?>+1); ii = ii +1){
				var ele = document.getElementById("cover_photo_"+<?php echo($total_page);?>+"_"+ii);
				ele.style.display = "none";
				//var ele_name = document.getElementById("cover_name_"+<?php echo($total_page);?>+"_"+ii);
				//ele_name.style.display = "none";
			}
		<?php } ?>

		
		<?php if($rest_page == 0){ ?>
		<?php //表示制御:rest部分無?>
			for (ii = 1; ii < (<?php echo($_limit);?>+1); ii = ii +1){
				var ele = document.getElementById("cover_photo_"+page_no+"_"+ii);
				ele.style.display = "block";
				//var ele_name = document.getElementById("cover_name_"+page_no+"_"+ii);
				//ele_name.style.display = "block";
			}
		<?php }else{ ?>
		<?php //表示制御:rest部分有?>

			if(page_no == <?php echo($total_page);?>){
			<?php //最終ページ?>
				for (i = 1; i < <?php echo($rest_page_plus);?>; i = i +1){

					var ele = document.getElementById("cover_photo_"+page_no+"_"+i);
					ele.style.display = "block";
					//var ele_name = document.getElementById("cover_name_"+page_no+"_"+i);
					//ele_name.style.display = "block";
				}
			}else{
				for (ii = 1; ii < (<?php echo($_limit);?>+1); ii = ii +1){
					var ele = document.getElementById("cover_photo_"+page_no+"_"+ii);
					ele.style.display = "block";
					//var ele_name = document.getElementById("cover_name_"+page_no+"_"+ii);
					//ele_name.style.display = "block";
				}
			}
		<?php } ?>
<?php } ?>


	}
//-->
</script>
<style type="text/css">
    <!--
	#btn_next img{
		cursor:pointer;
	}
    -->
</style>

</head>
<?php if($page_no_album > 1){?>
<body onLoad="javascript:doSearchDefault(<?php echo($page_no_album);?>)">
<?php }else{ ?>
<body >
<?php } ?>
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
<p><img src="img/album/app_alb_01.jpg" alt="お好きなアルバムを選択" width="770" height="101"></p>
<p><img src="img/album/thumb_770_<?php echo($template_id);?>.jpg" alt="" width="770" height="285"></p>

<div id="album" class="clearfix">

<div id="alb_box" class="clearfix">
<?php 
	$page_no = 1;
	$data_no = 1;
	$data_display = "";
	//$_limit = 7;
	foreach($album_list as $data){
		if($page_no == 1){
			$data_display = "";
		}else{
			$data_display = "none";
		}
	?>
<span id="cover_photo_<?php echo($page_no);?>_<?php echo($data_no);?>" style="display:<?php echo($data_display);?>;"><div id="alb_left" class="clearfix">
<p class="alb_thumb"><img id="thumb_<?php echo($data["id"]);?>" src="<?php echo($LOG_PICTURE);?>album/<?php echo($data["albums_url"]);?>.jpeg" width="165" height="110" onClick="javascript:doAlbumCheck_Select('<?php echo($fb_user);?>','<?php echo($data["id"]);?>','<?php echo($access_token);?>','picture_album_photo','check_album_photo')" style="cursor:pointer;"></p>
<p class="alb_name"><?php echo($data["albums_name"]);?></p>
</div></span>
<?php 	
		$data_no ++;
		if($data_no > $_limit){
			$data_no = 1;
			$page_no ++;
		}
	}?>
</div><!-- id="alb_box" class="clearfix" -->

<div class="btn-pn">
<div id="page_no"><p>ページ:1/&nbsp;<?php echo($total_page);?>&nbsp;&nbsp;<img src="img/btn-prev01.png" alt="前のページへ" border="0" onClick="javascript:doSearch('prev')" style="cursor:pointer;"><img src="img/btn-next01.png" alt="次のページへ" border="0" onClick="javascript:doSearch('next')" style="cursor:pointer;"></p></div>
</div><!-- class="btn-pn" -->
</div><!--id="album" class="clearfix"-->


<div id="page_btn">
<div id="btn_back"><a href="template_common.php?category_id=album"><img src="img/btn_back.jpg" width="275" height="61" alt="BACK"></a>
</div>
<div id="btn_next"><img src="img/btn_next.jpg" width="275" height="61" alt="NEXT" onClick="javascript:doAlbumSelect('picture_album_photo','check_album_photo')">
</div>

<input type="hidden" name="action_flg" value="" />
<input type="hidden" name="template_id" value="<?php echo($template_id);?>" />
<input type="hidden" name="album_id" value="" />
<input type="hidden" name="page_no_album" value="" />
</div><!-- id="page_btn" -->

</div><!-- id="contents03" class="clearfix" -->
</div><!-- id="wrap" class="index" -->
</div><!-- id="cvi_busy" -->
</form>
</form>
<?php 
$endTime = microtime(true);

	//アクセスログ
	$my365_user_id = "";
	$insta_user_id = "";
	$access_page = "picture_album_".$action_flg;
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
