<?php
//header('Content-Type: text/html; charset=UTF-8');
header('p3p: CP="ALL DSP COR PSAa PSDa OUR NOR ONL UNI COM NAV"');
$startTime = microtime(true);

	require './require.php';
	
	if(isset($argv[1])){
		$fb_user = $argv[1];
	}else{
		exit();
	}
	$con = getConnection();
	selectDb($con, "cover");

	if ($fb_user){
		$dirpath = $LOG_PICTURE."tags/".$fb_user;

		if (file_exists($dirpath) && is_dir($dirpath)) {
		}else{
			mkdir($dirpath, 0777);
		}

		$sql = "SELECT * FROM `crt_cover_fb_uid_tags` ";
		$sql .= " WHERE delete_flg = '0' " ;
		$sql .= " AND  `fb_uid` = '".$fb_user."'" ;

		$photos_data = selectList($con, $sql);

		$cnt = 0;
		foreach($photos_data as $data){

			$photos_list[$cnt]["id"] = $data["photos_id"];
			$photos_list[$cnt]["photos_picture"] = $data["photos_picture"];
			$photos_list[$cnt]["photos_source"] = $data["photos_source"];
			$photos_list[$cnt]["photos_source_2"] = $data["photos_source_2"];

			if(file_exists($dirpath."/photo_".$fb_user."_".$data["photos_id"].".jpeg")&&($data["modified_flg"]=="0")){
			}else{
				$cmd_tmp = "php ./batch_tags_photos_detail.php ".$fb_user." ".$data['photos_id']." ".$data['photos_source'] ." > /dev/null & ";
				exec($cmd_tmp) ;

				$sql  = "update crt_cover_fb_uid_tags set ";
				$sql .= " modified_flg = '0'";
				$sql .= " where photos_id = ".$data["photos_id"];

				$ret = execute($con, $sql) ;
			}

			$cnt ++;
		}
	}

$endTime = microtime(true);
	//アクセスログ
	$fb_user_name = "";
	$fb_user_gender = "";
	$birthday = "";
	$device_code = "";
	$ua = "";

	$my365_user_id = "";
	$insta_user_id = "";
	$access_page = "batch_tags_photos";
	$function_time = number_format($endTime - $startTime, C_DECIMALS);
	
	$sql  = " insert into crt_cover_creator_access (fb_uid,my365_user_id,insta_user_id,fb_user_name,sex_type,birthday,device_code,useragent,access_page,function_time,insert_datetime,update_datetime) ";
	$sql .= " values ('".$fb_user."','".$my365_user_id."','".$insta_user_id."','".$fb_user_name."','".$fb_user_gender."','".$birthday."','".$device_code."','".$ua."','".$access_page."','".$function_time."',now(),now())";
	$ret  = execute($con, $sql) ;
	closeConnection($con);
exit();
?>
