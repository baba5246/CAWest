<?php
//header('Content-Type: text/html; charset=UTF-8');
header('p3p: CP="ALL DSP COR PSAa PSDa OUR NOR ONL UNI COM NAV"');
$startTime = microtime(true);

	require './require.php';

	$fb_user = "";
	$diary_id = "";
	$url_150 = "";

	if(isset($argv[1]) && isset($argv[2])){
		$fb_user = $argv[1];
		$photos_id = $argv[2];
		$photos_source = $argv[3];
	}else{
		exit();
	}
	$con = getConnection();
	selectDb($con, "cover");

	if(($fb_user)&&($photos_id)&&($photos_source)){
		$dirpath = $LOG_PICTURE."tags/".$fb_user;

		if (file_exists($dirpath) && is_dir($dirpath)) {
		}else{
			mkdir($dirpath, 0777);
		}

		$filepath = $dirpath."/photo_".$fb_user."_".$photos_id.".jpeg";
			$screen_photo_source = imagecreatefromjpeg($photos_source); 
			$ret = imagejpeg($screen_photo_source,$filepath);

			//thumbnail
			$filepath = $dirpath."/thumb_".$fb_user."_".$photos_id.".jpeg";

				//width="105" height="70"
				//720*480→105:70=720:480
				//640*640→105:70=640:426
				//720*960→105:70=720:xxx
				$size_photo_source = getimagesize($photos_source);
				$height_new = ((70*$size_photo_source[0])/105);

				$screen_new = imagecreatetruecolor($size_photo_source[0],$height_new);
				$ret = imagecopyresampled($screen_new, $screen_photo_source, 0, 0, 0, 0, $size_photo_source[0],$height_new, $size_photo_source[0],$height_new);
				$ret = imagejpeg($screen_new,$filepath);

	}

$endTime = microtime(true);
	//アクセスログ
/*
	$fb_user_name = "";
	$fb_user_gender = "";
	$birthday = "";
	$device_code = "";
	$ua = "";

	$my365_user_id = "";
	$insta_user_id = "";
	$access_page = "batch_tags_photos_detail";
	$function_time = number_format($endTime - $startTime, C_DECIMALS);
	
	$sql  = " insert into crt_cover_creator_access (fb_uid,my365_user_id,insta_user_id,fb_user_name,sex_type,birthday,device_code,useragent,access_page,function_time,insert_datetime,update_datetime) ";
	$sql .= " values ('".$fb_user."','".$my365_user_id."','".$insta_user_id."','".$fb_user_name."','".$fb_user_gender."','".$birthday."','".$device_code."','".$ua."','".$access_page."','".$function_time."',now(),now())";
	$ret  = execute($con, $sql) ;
*/
	closeConnection($con);
exit();
?>
