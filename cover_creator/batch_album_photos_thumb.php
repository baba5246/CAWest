<?php
//header('Content-Type: text/html; charset=UTF-8');
header('p3p: CP="ALL DSP COR PSAa PSDa OUR NOR ONL UNI COM NAV"');
$startTime = microtime(true);

	require './require.php';

	$fb_user = "";
	$photos_id = "";
	$photos_source = "";

	if(isset($argv[1]) && isset($argv[2]) && isset($argv[3])){
		$fb_user = $argv[1];
		$photos_id = $argv[2];
		$photos_source = $argv[3];
	}else{
		exit();
	}

	if(($fb_user)&&($photos_id)&&($photos_source)){
		$dirpath = $LOG_PICTURE."photo/".$fb_user;

		if (file_exists($dirpath) && is_dir($dirpath)) {
		}else{
			mkdir($dirpath, 0777);
		}


//		$filepath = $dirpath."/photo_".$fb_user."_".$photos_id.".jpeg";
		$screen_photo_source = imagecreatefromjpeg($photos_source); 
//		$ret = imagejpeg($screen_photo_source,$filepath);

		//thumbnail
		$filepath = $dirpath."/thumb_".$fb_user."_".$photos_id.".jpeg";

		//width="105" height="70"
		//105*70=x*y y=(70*x)/105
		//width="165" height="110"
		//720*480¨165:110=720:480
		//640*640¨165:110=640:426
		//720*960¨165:110=720:xxx
		$size_photo_source = getimagesize($photos_source);
		$height_new = ((70*$size_photo_source[0])/105);

		$screen_new = imagecreatetruecolor($size_photo_source[0],$height_new);
		$ret = imagecopyresampled($screen_new, $screen_photo_source, 0, 0, 0, 0, $size_photo_source[0],$height_new, $size_photo_source[0],$height_new);
		$ret = imagejpeg($screen_new,$filepath);
	}

exit();
?>
