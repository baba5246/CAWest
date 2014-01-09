<?php
//header('Content-Type: text/html; charset=UTF-8');
header('p3p: CP="ALL DSP COR PSAa PSDa OUR NOR ONL UNI COM NAV"');
$startTime = microtime(true);

	require './require.php';
	
	$my365_user_id = "";
	$my365_api_key = "";
	$my365_date_from = "";
	$my365_date_to = "";

	$my365_error_flg = false;
	$my365_error_reason = "";

	if((isset($argv[1]))&&(isset($argv[2]))&&(isset($argv[3]))&&(isset($argv[4]))){
		$my365_user_id = $argv[1];
		$my365_api_key = $argv[2];
		$my365_date_from = $argv[3];
		$my365_date_to = $argv[4];
	}else{
		exit();
	}
	$con = getConnection();
	selectDb($con, "_cover");

	if ($my365_user_id){
		$my365_user_id = $my365_user_id;

		$dirpath = $LOG_PICTURE."my365/".$my365_user_id;

		if (file_exists($dirpath) && is_dir($dirpath)) {
		}else{
			mkdir($dirpath, 0777);
		}

		$my365_url = $MY365_API_URL."?facebook_id=".$my365_user_id."&from=".$my365_date_from."&to=".$my365_date_to."&key=".$my365_api_key;
		$my365_result = json_decode(file_get_contents($my365_url,true),true);

		if(isset($my365_result["error"])){
			$my365_error_flg = true;
			$my365_error_reason = "no_id";

		}elseif(!isset($my365_result["response"])){
			$my365_error_flg = true;
			$my365_error_reason = "no_data";

		}elseif(isset($my365_result["response"])){
			$my365_error_flg = false;

			$cnt = 0;
			foreach($my365_result["response"] as $data){

				if(file_exists($dirpath."/photo_".$my365_user_id."_".$data["diary_id"].".jpeg")){
				}else{
					$cmd_tmp = "php ./batch_my365_photos_detail.php ".$my365_user_id." ".$data['diary_id']." ".$data['url_150'] ." > /dev/null & ";
					exec($cmd_tmp) ;
				}

			$cnt ++;
			}
		}
	}

$endTime = microtime(true);

	//アクセスログ
	$fb_user = $my365_user_id;
	$insta_user_id = "";
	$fb_user_name = "";
	$fb_user_gender = "";
	$birthday = "";
	$device_code = "";
	$ua = "";

	$access_page = "batch_my365_photos";
	$function_time = number_format($endTime - $startTime, C_DECIMALS);
	$sql  = " insert into crt_cover_creator_access (fb_uid,my365_user_id,insta_user_id,fb_user_name,sex_type,birthday,device_code,useragent,access_page,function_time,insert_datetime,update_datetime) ";
	$sql .= " values ('".$fb_user."','".$my365_user_id."','".$insta_user_id."','".$fb_user_name."','".$fb_user_gender."','".$birthday."','".$device_code."','".$ua."','".$access_page."','".$function_time."',now(),now())";
	$ret  = execute($con, $sql) ;
	closeConnection($con);

exit();
?>
