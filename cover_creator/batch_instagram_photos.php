<?php
//header('Content-Type: text/html; charset=UTF-8');
header('p3p: CP="ALL DSP COR PSAa PSDa OUR NOR ONL UNI COM NAV"');
$startTime = microtime(true);

	require './require.php';

	$fb_user = "";
	$insta_user_id = "";
	$access_token = "";

	if((isset($argv[1]))&&(isset($argv[2]))&&(isset($argv[3]))){
		$fb_user = $argv[1];
		$insta_user_id = $argv[2];
		$access_token = $argv[3];
	}else{
		exit();
	}

	$con = getConnection();
	selectDb($con, "cover");

	if ($fb_user){
		$dirpath = $LOG_PICTURE."instagram/".$fb_user;

		if (file_exists($dirpath) && is_dir($dirpath)) {
		}else{
			mkdir($dirpath, 0777);
		}

		$insta_url = "https://api.instagram.com/v1/users/".$insta_user_id."/media/recent?access_token=".$access_token;
		$insta_result = json_decode(file_get_contents($insta_url,true),true);

		if(!isset($insta_result["data"])){
			$insta_error_flg = true;
			$insta_error_reason = "no_data";
		}else{
			$cnt = 0;
			foreach($insta_result["data"] as $data){

				if(file_exists($dirpath."/photo_".$fb_user."_".$data["id"].".jpeg")){
				}else{
					$cmd_tmp = "php ./batch_instagram_photos_detail.php ".$fb_user." ".$data["id"]." ".$data["images"]["thumbnail"]["url"] ." > /dev/null & ";
					exec($cmd_tmp) ;
				}
			$cnt ++;
			}
		}
		
		$next_url_flg = 0;
		if($insta_result["pagination"]["next_url"]){
			$next_url_flg = 1;

		}else{
			$next_url_flg = 1;
		}
		while($next_url_flg <= 1){
			
			if(isset($insta_result["pagination"]["next_url"])){
				$insta_url = $insta_result["pagination"]["next_url"];
			}
			
			if(isset($insta_url)){
				$insta_result = json_decode(file_get_contents($insta_url,true),true);
			}else{
				exit();
			}

			if(!isset($insta_result["data"])){
				$insta_error_flg = true;
				$insta_error_reason = "no_data";
			}else{
				foreach($insta_result["data"] as $data){

					if(file_exists($dirpath."/photo_".$fb_user."_".$data["id"].".jpeg")){
					}else{
						$cmd_tmp = "php ./batch_instagram_photos_detail.php ".$fb_user." ".$data["id"]." ".$data["images"]["thumbnail"]["url"] ." > /dev/null & ";
						exec($cmd_tmp) ;
					}
				$cnt ++;
				}
			}

			if(isset($insta_result["pagination"]["next_url"])){
			}else{
				$next_url_flg ++;
			}
		}
	}



$endTime = microtime(true);

	//アクセスログ
	$my365_user_id = "";
	$fb_user_name = "";
	$fb_user_gender = "";
	$birthday = "";
	$device_code = "";
	$ua = "";
	$access_page = "batch_instagram_photos";
	$function_time = number_format($endTime - $startTime, C_DECIMALS);
	$sql  = " insert into crt_cover_creator_access (fb_uid,my365_user_id,insta_user_id,fb_user_name,sex_type,birthday,device_code,useragent,access_page,function_time,insert_datetime,update_datetime) ";
	$sql .= " values ('".$fb_user."','".$my365_user_id."','".$insta_user_id."','".$fb_user_name."','".$fb_user_gender."','".$birthday."','".$device_code."','".$ua."','".$access_page."','".$function_time."',now(),now())";
	$ret  = execute($con, $sql) ;
	closeConnection($con);


exit();
?>
