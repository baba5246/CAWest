<?php
//header('Content-Type: text/html; charset=UTF-8');
header('p3p: CP="ALL DSP COR PSAa PSDa OUR NOR ONL UNI COM NAV"');

	require './require.php';

	$my365_user_id = "";
	$diary_id = "";
	$url_150 = "";

	if(isset($argv[1]) && isset($argv[2]) && isset($argv[3])){
		$my365_user_id = $argv[1];
		$diary_id = $argv[2];
		$url_150 = $argv[3];
	}else{
		exit();
	}

	if(($my365_user_id)&&($diary_id)&&($url_150)){

		$my365_user_id = $my365_user_id;
		$dirpath = $LOG_PICTURE."my365/".$my365_user_id;

		if (file_exists($dirpath) && is_dir($dirpath)) {
		}else{
			mkdir($dirpath, 0777);
		}

		$filepath = $dirpath."/photo_".$my365_user_id."_".$diary_id.".jpeg";
		
		if (file_exists($filepath)){
		}else{
			$screen_photo_source = imagecreatefromjpeg($url_150); 
			$ret = imagejpeg($screen_photo_source,$filepath);
		}
	}
exit();
?>
