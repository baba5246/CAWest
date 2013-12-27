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
		$dirpath = $LOG_PICTURE."album/".$fb_user;

		if (file_exists($dirpath) && is_dir($dirpath)) {
		}else{
			mkdir($dirpath, 0777);
		}

		$sql = "SELECT * FROM `crt_cover_fb_uid_albums` ";
		$sql .= " WHERE delete_flg = '0' " ;
		$sql .= " AND  `fb_uid` = '".$fb_user."'" ;

		$album_data = selectList($con, $sql);

		$cnt = 0;
		foreach($album_data as $data){

			$album_list[$cnt]["id"] = $data["albums_id"];
			$album_list[$cnt]["albums_cover_photo_id"] = $data["albums_cover_photo_id"];
			$album_list[$cnt]["albums_cover_photo_url"] = $data["albums_cover_photo_url"];

			if(file_exists($dirpath."/album_".$fb_user."_".$data["albums_id"].".jpeg")&&($data["modified_flg"]=="0")){
			}else{
				$cmd_tmp = "php ./batch_album_detail.php ".$fb_user." ".$data['albums_id']." ".$data['albums_cover_photo_url'] ." > /dev/null & ";
				exec($cmd_tmp) ;
			
				$sql  = "update crt_cover_fb_uid_albums set ";
				$sql .= " modified_flg = '0'";
				$sql .= " where albums_id = ".$data["albums_id"];

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
	$access_page = "batch_album";
	$function_time = number_format($endTime - $startTime, C_DECIMALS);
	
	$sql  = " insert into crt_cover_creator_access (fb_uid,my365_user_id,insta_user_id,fb_user_name,sex_type,birthday,device_code,useragent,access_page,function_time,insert_datetime,update_datetime) ";
	$sql .= " values ('".$fb_user."','".$my365_user_id."','".$insta_user_id."','".$fb_user_name."','".$fb_user_gender."','".$birthday."','".$device_code."','".$ua."','".$access_page."','".$function_time."',now(),now())";
	$ret  = execute($con, $sql) ;
	closeConnection($con);
exit();
?>
