<?php
//header('Content-Type: text/html; charset=UTF-8');
header('p3p: CP="ALL DSP COR PSAa PSDa OUR NOR ONL UNI COM NAV"');

	require './require.php';

	$fb_user = "";
	$albums_id = "";
	$albums_cover_photo_url = "";

	if(isset($argv[1]) && isset($argv[2]) && isset($argv[3])){
		$fb_user = $argv[1];
		$albums_id = $argv[2];
		$albums_cover_photo_url = $argv[3];
	}else{
		exit();
	}

	$con = getConnection();
	selectDb($con, "cover");

	if(($fb_user)&&($albums_id)&&($albums_cover_photo_url)){
		$dirpath = $LOG_PICTURE."album/".$fb_user;

		if (file_exists($dirpath) && is_dir($dirpath)) {
		}else{
			mkdir($dirpath, 0777);
		}

		$filepath = $dirpath."/album_".$fb_user."_".$albums_id.".jpeg";

			$screen_photo_source = imagecreatefromjpeg($albums_cover_photo_url); 
			
			if(!$screen_photo_source){
				outLog($LOG_COVER,"uid_".$fb_user,"albums_cover_photo_url failed to open stream", "error");


				$sql  = "update crt_cover_fb_uid_albums set ";
				$sql .= "delete_flg = '1' ";
				$sql .= " where albums_id = ".$albums_id;

				$ret = execute($con, $sql) ;

				closeConnection($con);
				exit();
			}else{
				$ret = imagejpeg($screen_photo_source,$filepath);
			}

			//thumbnail
			$filepath = $dirpath."/thumb_".$fb_user."_".$albums_id.".jpeg";

				//width="165" height="110"
				//720*480¨165:110=720:480
				//640*640¨165:110=640:426
				//720*960¨165:110=720:xxx
				$size_photo_source = getimagesize($albums_cover_photo_url);
				$height_new = ((110*$size_photo_source[0])/165);

				$screen_new = imagecreatetruecolor($size_photo_source[0],$height_new);
				$ret = imagecopyresampled($screen_new, $screen_photo_source, 0, 0, 0, 0, $size_photo_source[0],$height_new, $size_photo_source[0],$height_new);
				$ret = imagejpeg($screen_new,$filepath);
	}

	closeConnection($con);
exit();
?>
