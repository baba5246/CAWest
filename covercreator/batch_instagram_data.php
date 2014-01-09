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
	selectDb($con, "_cover");

	$insta_url = "https://api.instagram.com/v1/users/".$insta_user_id."/media/recent?access_token=".$access_token;
	$insta_result = json_decode(file_get_contents($insta_url,true),true);

	if(!isset($insta_result["data"])){
		$insta_error_flg = true;
		$insta_error_reason = "no_data";
	}else{
		$photos_list = array();
		$cnt = 0;
		foreach($insta_result["data"] as $data){
			$photos_list[$cnt]["id"] = $data["id"];
			$photos_list[$cnt]["created_time"] = date("Y-m-d",$data["caption"]["created_time"]);
			$photos_list[$cnt]["text"] = $data["caption"]["text"];
			$photos_list[$cnt]["thumbnail_url"] = $data["images"]["thumbnail"]["url"];
			$photos_list[$cnt]["standard_url"] = $data["images"]["standard_resolution"]["url"];
			$photos_list[$cnt]["link"] = $data["link"];
			
			$sql = "SELECT * FROM `crt_cover_fb_instagram_photos` ";
			$sql .= " WHERE delete_flg = '0' " ;
			$sql .= " AND  `photos_id` = '".$data["id"]."'" ;
			$sql .= " AND  `fb_uid` = '".$fb_user."'" ;

			$photos_data = selectList($con, $sql);

			if(isset($photos_data[0]["id"])){
			}else{
				$value_fb_uid = $fb_user;
				$value_instagram_uid = $insta_user_id;
				$value_photos_id = $data["id"];
				$value_photos_thumbnail_url = $data["images"]["thumbnail"]["url"];
				$value_photos_standard_url = $data["images"]["standard_resolution"]["url"];
				$value_photos_created_time = date("Y-m-d",$data["caption"]["created_time"]);

				$sql  = "insert into `crt_cover_fb_instagram_photos` "; 
				$sql .= " (`fb_uid`,`instagram_uid`,`photos_id`,`photos_thumbnail_url`,`photos_standard_url`,`photos_created_time`,`insert_datetime`,`update_datetime`) ";
				$sql .= "value ('".$value_fb_uid."','".$value_instagram_uid."','".$value_photos_id."','".$value_photos_thumbnail_url."','".$value_photos_standard_url."','".$value_photos_created_time."',now(),now())";
				$ret = execute($con, $sql) ;
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
		$insta_url = $insta_result["pagination"]["next_url"];
		if(isset($insta_url)){
			$insta_result = json_decode(file_get_contents($insta_url,true),true);
		}else{
			break;
		}

		if(!isset($insta_result["data"])){
			$insta_error_flg = true;
			$insta_error_reason = "no_data";
		}else{
			foreach($insta_result["data"] as $data){
				$photos_list[$cnt]["id"] = $data["id"];
				$photos_list[$cnt]["created_time"] = date("Y-m-d",$data["caption"]["created_time"]);
				$photos_list[$cnt]["text"] = $data["caption"]["text"];
				$photos_list[$cnt]["thumbnail_url"] = $data["images"]["thumbnail"]["url"];
				$photos_list[$cnt]["standard_url"] = $data["images"]["standard_resolution"]["url"];
				$photos_list[$cnt]["link"] = $data["link"];
					
				$sql = "SELECT * FROM `crt_cover_fb_instagram_photos` ";
				$sql .= " WHERE delete_flg = '0' " ;
				$sql .= " AND  `photos_id` = '".$data["id"]."'" ;
				$sql .= " AND  `fb_uid` = '".$fb_user."'" ;

				$photos_data = selectList($con, $sql);

				if(isset($photos_data[0]["id"])){
				}else{
					$value_fb_uid = $fb_user;
					$value_instagram_uid = $insta_user_id;
					$value_photos_id = $data["id"];
					$value_photos_thumbnail_url = $data["images"]["thumbnail"]["url"];
					$value_photos_standard_url = $data["images"]["standard_resolution"]["url"];
					$value_photos_created_time = date("Y-m-d",$data["caption"]["created_time"]);

					$sql  = "insert into `crt_cover_fb_instagram_photos` "; 
					$sql .= " (`fb_uid`,`instagram_uid`,`photos_id`,`photos_thumbnail_url`,`photos_standard_url`,`photos_created_time`,`insert_datetime`,`update_datetime`) ";
					$sql .= "value ('".$value_fb_uid."','".$value_instagram_uid."','".$value_photos_id."','".$value_photos_thumbnail_url."','".$value_photos_standard_url."','".$value_photos_created_time."',now(),now())";
					$ret = execute($con, $sql) ;
				}
			$cnt ++;
			}
		}

		if(isset($insta_result["pagination"]["next_url"])){
		}else{
			$next_url_flg ++;
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
	$access_page = "batch_instagram_data";
	$function_time = number_format($endTime - $startTime, C_DECIMALS);
	$sql  = " insert into crt_cover_creator_access (fb_uid,my365_user_id,insta_user_id,fb_user_name,sex_type,birthday,device_code,useragent,access_page,function_time,insert_datetime,update_datetime) ";
	$sql .= " values ('".$fb_user."','".$my365_user_id."','".$insta_user_id."','".$fb_user_name."','".$fb_user_gender."','".$birthday."','".$device_code."','".$ua."','".$access_page."','".$function_time."',now(),now())";
	$ret  = execute($con, $sql) ;
	closeConnection($con);


exit();
?>
