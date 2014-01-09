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
	$photo_picture_path = "";
	$cover_position_top = 0;

	//リクエスト
	$action_flg = get_request_value("action_flg");
	$template_id = get_request_value("template_id");
	$album_id = get_request_value("album_id");

	$photos_id = get_request_value("photos_id");
	$photo_source = get_request_value("photo_source");
	$cover_file_suffix = get_request_value("cover_file_suffix");
	$page_id = get_request_value("page_id");
	if($page_id){}else{
		$page_id = 1;
	}

	$page_no_album = get_request_value("page_no_album");
	if($page_no_album){
	}else{
		$page_no_album = 1;
	}

	$page_no_photo = get_request_value("page_no_photo");
	if($page_no_photo){
	}else{
		$page_no_photo = 1;
	}

	$page_no = get_request_value("page_no");
	if($page_no){
	}else{
		$page_no = 1;
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
		if(($template_id)&&($album_id)){

			$query_url = 'https://graph.facebook.com/fql?q=SELECT+object_id,pid,aid,caption,src,src_width,src_height,src_small,src_small_width,src_small_height,src_big,src_big_width,src_big_height,created,object_id,album_object_id+FROM+photo+WHERE+album_object_id+=+'.$album_id.'+ORDER+BY+created+DESC&access_token='. $access_token;
			$query_result = json_decode(file_get_contents($query_url),true);
			if($query_result["data"]){
				$photos_list = array();
				$cnt = 0;
				foreach($query_result["data"] as $data){

					$tmp_url = "https://graph.facebook.com/".$data["object_id"]."?access_token=".$access_token;
					$tmp_result = json_decode(file_get_contents($tmp_url),true);
				
					$photos_list[$cnt]["id"] = $data["pid"];
					$photos_list[$cnt]["albums_id"] = $data["album_object_id"];
					$photos_list[$cnt]["caption"] = $data["caption"];
					$photos_list[$cnt]["src_big"] = $tmp_result["images"][0]["source"];
					//$photos_list[$cnt]["src_big"] = $data["src_big"];
					$photos_list[$cnt]["photos_url"] = $fb_user."/thumb_".$fb_user."_".$data["pid"];
					$photos_list[$cnt]["created"] = date("Y-m-d",$data["created"]);
					if($photos_list[$cnt]["photos_url"]){
					}else{
					}
					$cnt ++;
				}
			}
		}

		//ページング
		$data_cnt = count($photos_list);
		$_limit = 7;
		$total_page = (int)ceil($data_cnt/$_limit);
		$rest_page = $data_cnt % $_limit;

		$page = $page_id;
		$offset = (($_limit*$page_id) - $_limit);

		if($action_flg=="photos_select"){

				if($photo_place == 1){
					//ファイル名の接尾語を設定：ブラウザキャッシュへの対応
					$cover_file_suffix = "ver_".date("mdHis");

					//表示用
					$screen = imagecreatefromjpeg($photo_source); 
					$size_photo_source = getimagesize($photo_source);
					//720×480→770×285:widthで合わせる。
					//720×480→770×*:widthで合わせる。
					$height_new = ((770*$size_photo_source[1])/$size_photo_source[0]);
					$screen_new = imagecreatetruecolor(770,$height_new);
					$ret = imagecopyresampled($screen_new, $screen, 0, 0, 0, 0, 770,$height_new, $size_photo_source[0],$size_photo_source[1]);
					$ret = imagejpeg($screen_new,$LOG_PICTURE."photo/".$fb_user."/display_photo_".$fb_user."_".$photos_id."_".$photo_place.".jpeg");
					imagedestroy($screen);
					imagedestroy($screen_new);

					//出力用
					$screen = imagecreatefromjpeg($photo_source); 
					$size_photo_source = getimagesize($photo_source);
					//720×480→851×*:widthで合わせる。
					$height_new = ((851*$size_photo_source[1])/$size_photo_source[0]);
					$screen_new = imagecreatetruecolor(851,$height_new);
					$ret = imagecopyresampled($screen_new, $screen, 0, 0, 0, 0, 851,$height_new, $size_photo_source[0],$size_photo_source[1]);
					$ret = imagejpeg($screen_new,$LOG_PICTURE."photo/".$fb_user."/photo_".$fb_user."_".$photos_id."_".$photo_place.".jpeg");
					imagedestroy($screen);
					imagedestroy($screen_new);

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
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js" type="text/javascript"></script>
<link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/ui-lightness/jquery-ui.css" rel="stylesheet" type="text/css" />  
<script src="./js/cvi_busy_lib.js"></script>
<script src="./js/jquery.overscroll.js"></script>
<link rel="stylesheet" type="text/css" href="css/common.css">
<link href="./css/jquery.dragscroll.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
<!--
var photos_select_flg = "";
var ctrl = null;
var page_no = 1;
var cover_position_top = 0;
var cover_position_left = 0;
var overscroll_position_top = 100;

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
			}
		}

		<?php if($rest_page > 0){ ?>
		<?php //非表示制御:最終ページ:rest部分?>

			for (i = 1; i < <?php echo($rest_page_plus);?>; i = i +1){
				var ele = document.getElementById("cover_photo_"+<?php echo($total_page);?>+"_"+i);
				ele.style.display = "none";
			}
		<?php }else{ ?>
		<?php //非表示制御:最終ページ?>
			for (ii = 1; ii < (<?php echo($_limit);?>+1); ii = ii +1){
				var ele = document.getElementById("cover_photo_"+<?php echo($total_page);?>+"_"+ii);
				ele.style.display = "none";
			}
		<?php } ?>

		
		<?php if($rest_page == 0){ ?>
		<?php //表示制御:rest部分無?>
			for (ii = 1; ii < (<?php echo($_limit);?>+1); ii = ii +1){
				var ele = document.getElementById("cover_photo_"+page_no+"_"+ii);
				ele.style.display = "block";
			}
		<?php }else{ ?>
		<?php //表示制御:rest部分有?>

			if(page_no == <?php echo($total_page);?>){
			<?php //最終ページ?>
				for (i = 1; i < <?php echo($rest_page_plus);?>; i = i +1){

					var ele = document.getElementById("cover_photo_"+page_no+"_"+i);
					ele.style.display = "block";
				}
			}else{
				for (ii = 1; ii < (<?php echo($_limit);?>+1); ii = ii +1){
					var ele = document.getElementById("cover_photo_"+page_no+"_"+ii);
					ele.style.display = "block";
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
			}
		}

		<?php if($rest_page > 0){ ?>
		<?php //非表示制御:最終ページ:rest部分?>

			for (i = 1; i < <?php echo($rest_page_plus);?>; i = i +1){
				var ele = document.getElementById("cover_photo_"+<?php echo($total_page);?>+"_"+i);
				ele.style.display = "none";
			}
		<?php }else{ ?>
		<?php //非表示制御:最終ページ?>
			for (ii = 1; ii < (<?php echo($_limit);?>+1); ii = ii +1){
				var ele = document.getElementById("cover_photo_"+<?php echo($total_page);?>+"_"+ii);
				ele.style.display = "none";
			}
		<?php } ?>

		
		<?php if($rest_page == 0){ ?>
		<?php //表示制御:rest部分無?>
			for (ii = 1; ii < (<?php echo($_limit);?>+1); ii = ii +1){
				var ele = document.getElementById("cover_photo_"+page_no+"_"+ii);
				ele.style.display = "block";
			}
		<?php }else{ ?>
		<?php //表示制御:rest部分有?>

			if(page_no == <?php echo($total_page);?>){
			<?php //最終ページ?>
				for (i = 1; i < <?php echo($rest_page_plus);?>; i = i +1){

					var ele = document.getElementById("cover_photo_"+page_no+"_"+i);
					ele.style.display = "block";
				}
			}else{
				for (ii = 1; ii < (<?php echo($_limit);?>+1); ii = ii +1){
					var ele = document.getElementById("cover_photo_"+page_no+"_"+ii);
					ele.style.display = "block";
				}
			}
		<?php } ?>
<?php } ?>


	}

	function doPhotosSelect(page_id,action,template_id,photo_id,photo_place,photo_source,page_id_num){
		if(page_id == "check_album"){
			if(photo_source){
				photos_select_flg = "true";
			}else{
				alert("写真を選択して下さい。");
			}
			
		}else if(page_id == "picture_album_photo"){
				photos_select_flg = "true";
		}
		if(photos_select_flg){
			if (ctrl == null) {
				ctrl = getBusyOverlay(document.getElementById("cvi_busy"), {opacity:0.25, color:"gray", text:""});
			}else{

				ctrl.remove();
				ctrl = null;
			}

			document.form01.action='./'+page_id+'.php';
			document.form01.action_flg.value=action;
			document.form01.template_id.value=template_id;
			document.form01.photos_id.value=photo_id;
			document.form01.photo_source.value=photo_source;
			document.form01.cover_position_top.value=cover_position_top;
			document.form01.page_no.value=page_no;

			if(page_id == "check_album"){
			}else{
				document.form01.page_no_photo.value=page_no;
			}

			document.form01.submit();
		}else{
		}
	}

$(document).ready(function () {

<?php if ($device_code == "pc"){?>
	<?php if (($os_code == "Chrome")||($os_code == "Firefox")){?>
		var innerWidth = window.innerWidth;
		var innerHeight = window.innerHeight;
		var var_left = 0;

		var_left = ((innerWidth/100)*50)-100;
		//var obj = document.getElementById("container");
		//obj.style.left = var_left;
		$("#container").css("left",var_left);

	<?php }else{?>
		var innerWidth = document.documentElement.scrollWidth;
		var innerHeight = document.documentElement.scrollHeight;
		var var_left = 0;

		var_left = ((innerWidth/100)*50)-100;
		$("#container").css("left",var_left);


	<?php } ?>
<?php } ?>

<?php if($device_code == "pc"){?>
	$('#scroll').dragScroll();
<?php }?>

});

<?php if($device_code == "pc"){?>
(function() {
	$.fn.dragScroll = function() {
	var target = this;
	$(this).mousedown(function (event) {
		$(this)
		.data('down', true)
		.data('x', event.clientX)
		.data('y', event.clientY)
		.data('scrollLeft', this.scrollLeft)
		.data('scrollTop', this.scrollTop);

		return false;
	}).mouseup(function (event) {
cover_position_top = this.scrollTop;
	}).css({
		'overflow': 'hidden', // スクロールバー非表示
		'cursor': 'move'
	});


	// ウィンドウから外れてもイベント実行
	$(document).mousemove(function (event) {
		if ($(target).data('down') == true) {
		// スクロール
		target.scrollLeft($(target).data('scrollLeft') + $(target).data('x') - event.clientX);
		target.scrollTop($(target).data('scrollTop') + $(target).data('y') - event.clientY);
		return false; // 文字列選択を抑止
		}
	}).mouseup(function (event) {
		$(target).data('down', false);
	});

		return this;
	}

<?php }else{?>
$(function() {
	$("#scroll").overscroll({
	//cancelOn: '.no-drag',
	//hoverThumbs: true,
	//persistThumbs: true,
	//showThumbs: false,
	//scrollLeft: 200,
	//dragHold:'true',
	scrollTop: overscroll_position_top
	}).on('overscroll:dragstart overscroll:dragend overscroll:driftstart overscroll:driftend', function(event){
overscroll_position_top = this.scrollTop;
cover_position_top = this.scrollTop;

	});

<?php }?>

})(jQuery);

//-->
</script>
<style type="text/css">
    <!--
	h2 {
		font-size: 10px;
		line-height: 10px;
		font-family: Helvetica, sans-serif;
		font-weight: bold;
		text-align: center;
		text-transform: uppercase;
		margin-top: 10px;
	}
 
	#container {
		position: absolute;
		top: 100px;
		background-color: white;
		filter:alpha(opacity=50);
		-moz-opacity: 0.5;
		opacity: 0.5;
		width: 200px;
		margin: 100px auto;
		border: 1px dashed #21303b;
		left: 305px;

		/*shadow*/
		-webkit-box-shadow: 10px 10px 10px #000;
		-moz-box-shadow: 10px 10px 10px #000;
		box-shadow: 10px 10px 10px #000;
		/*rounded corners*/
		-webkit-border-radius: 20px;
		-moz-border-radius: 20px;
		border-radius: 20px;
	}

	#btn_next img{
		cursor:pointer;
	}

    -->
</style>
</head>
<?php if($page_no > 1){?>
<body onLoad="javascript:doSearchDefault(<?php echo($page_no);?>)">
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
<p><img src="img/picture/app_pic_01.jpg" alt="お好きな写真を選択" width="770" height="101"></p>

<?php if(($template_id)&&($action_flg=="photos_select")){
			if(isset($cover_position_top)){
				$cover_position_top = 0;
			}
			$size_photo_source = getimagesize($LOG_PICTURE."photo/".$fb_user."/display_photo_".$fb_user."_".$photos_id."_".$photo_place.".jpeg");

?>
		<div id="scroll" style="width:770px;height:285px;overflow:hidden;cursor:move;position:relative;">
		<?php if($device_code == "pc"){?>
		<img id="draggable1" src="<?php echo($LOG_PICTURE."photo/".$fb_user."/display_photo_".$fb_user."_".$photos_id."_".$photo_place.".jpeg");?>" alt="" width="<?php echo($size_photo_source[0]);?>px" height="<?php echo($size_photo_source[1]);?>px"/><div id="container" ><h2>カバーをドラックして、再配置</h2></div>
		<?php }else{?>
		<ul style="width:<?php echo($size_photo_source[0]);?>px;height:<?php echo($size_photo_source[1]);?>px;margin:0;padding:0;">
		<img id="draggable1" src="<?php echo($LOG_PICTURE."photo/".$fb_user."/display_photo_".$fb_user."_".$photos_id."_".$photo_place.".jpeg");?>" alt="" width="<?php echo($size_photo_source[0]);?>px" height="<?php echo($size_photo_source[1]);?>px"/><div id="container" ><h2>カバーをドラックして、再配置</h2></div>
		</ul>
		<?php }?>
		</div>
<?php }else{ ?>
<p><img src="img/picture/thumb_png_<?php echo($template_id);?>.png" width="770" height="285"></p>
<?php }?>


<div id="picture" class="clearfix">

<div id="pic_box" class="clearfix">
<?php 
	$page_no = 1;
	$data_no = 1;
	$data_display = "";
	//$_limit = 7;
	foreach($photos_list as $data){
		if($page_no == 1){
			$data_display = "";
		}else{
			$data_display = "none";
		}
	
	//	function doPhotosSelect(page_id,action,template_id,photo_id,photo_place,photo_source,page_id_num)
		//photo_100003231024714_100003231024714_396591.jpeg
		$tmp_photo_source = ""; 
		$dirpath = $LOG_PICTURE."photo/".$fb_user;
		$filepath = $dirpath . "/photo_".$fb_user."_".$data["id"].".jpeg";

		if (file_exists($filepath)){
			$photo_source = $filepath;
		}else{
			$photo_source = $data["src_big"];
		}
	
	?>
<p class="pic_thumb" id="cover_photo_<?php echo($page_no);?>_<?php echo($data_no);?>" style="display:<?php echo($data_display);?>;"><img src="<?php echo($LOG_PICTURE);?>photo/<?php echo($data["photos_url"]);?>.jpeg" width="105" height="70" onClick="javascript:doPhotosSelect('picture_album_photo','photos_select','<?php echo($template_id);?>','<?php echo($data["id"]);?>','<?php echo($photo_place);?>','<?php echo($photo_source);?>',<?php echo($page_id);?>)" style="cursor:pointer;"></p>
<?php 	
		$data_no ++;
		if($data_no > $_limit){
			$data_no = 1;
			$page_no ++;
		}
	}?>
</div><!-- id="pic_box" class="clearfix" -->

<div class="btn-pn">
<div id="page_no"><p>ページ:1/&nbsp;<?php echo($total_page);?>&nbsp;&nbsp;<img src="img/btn-prev01.png" alt="前のページへ" border="0" onClick="javascript:doSearch('prev')" style="cursor:pointer;"><img src="img/btn-next01.png" alt="次のページへ" border="0" onClick="javascript:doSearch('next')" style="cursor:pointer;"></p></div>
</div><!-- class="btn-pn" -->

</div><!-- id="picture" class="clearfix" -->
 
 
 
<div id="page_btn">
<div id="btn_back"><a href="picture_album.php?page_no_album=<?php echo($page_no_album);?>&template_id=<?php echo($template_id);?>"><img src="img/btn_back.jpg" width="275" height="61" alt="BACK"></a>
</div>
<div id="btn_next"><img src="img/btn_next.jpg" width="275" height="61" alt="NEXT" onClick="javascript:doPhotosSelect('check_album','check_album','<?php echo($template_id);?>','<?php echo($photos_id);?>','<?php echo($photo_place);?>','<?php echo($photo_source);?>',<?php echo($page_id);?>)">
</div>
</div><!-- id="page_btn" -->

<input type="hidden" name="action_flg" value="" />
<input type="hidden" name="album_id" value="<?php echo($album_id);?>" />
<input type="hidden" name="template_id" value="" />
<input type="hidden" name="cover_position_top" value="" />
<input type="hidden" name="photos_id" value="" />
<input type="hidden" name="photo_source" value="" />
<input type="hidden" name="cover_file_suffix" value="<?php echo($cover_file_suffix);?>" />
<input type="hidden" name="photo_place" value="<?php echo($photo_place);?>" />
<input type="hidden" name="page_no_album" value="<?php echo($page_no_album);?>" />
<input type="hidden" name="page_id" value="" />
<input type="hidden" name="page_no" value="" />
<input type="hidden" name="page_no_photo" value="<?php echo($page_no_photo);?>" />
<input type="hidden" name="photo_picture_path" value="<?php echo($photo_picture_path);?>" />

</div><!-- id="contents03" class="clearfix" -->
</div><!-- id="wrap" class="index" -->
</div><!--cvi_busy-->
</form>
<?php 
$endTime = microtime(true);

	//アクセスログ
	$my365_user_id = "";
	$insta_user_id = "";
	$access_page = "picture_album_photo_".$action_flg."_".$template_id;
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
