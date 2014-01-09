<?php
//header('Content-Type: text/html; charset=UTF-8');
header('p3p: CP="ALL DSP COR PSAa PSDa OUR NOR ONL UNI COM NAV"');

	require './require.php';

	$fb_user = "";
	$photos_id = "";
	$photos_url = "";

	if(isset($argv[1]) && isset($argv[2]) && isset($argv[3])){
		$fb_user = $argv[1];
		$photos_id = $argv[2];
		$photos_url = $argv[3];
	}else{
		exit();
	}

	if(($fb_user)&&($photos_id)&&($photos_url)){

		$dirpath = $LOG_PICTURE."instagram/".$fb_user;

		if (file_exists($dirpath) && is_dir($dirpath)) {
		}else{
			mkdir($dirpath, 0777);
		}

		$filepath = $dirpath."/photo_".$fb_user."_".$photos_id.".jpeg";
		
		if (file_exists($filepath)){
		}else{
			$screen_photo_source = imagecreatefromjpeg($photos_url); 
			$ret = imagejpeg($screen_photo_source,$filepath);
		}
	}
exit();
?>
