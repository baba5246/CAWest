<?php
//header('Content-Type: text/html; charset=UTF-8');
header('p3p: CP="ALL DSP COR PSAa PSDa OUR NOR ONL UNI COM NAV"');
$startTime = microtime(true);

	require './require.php';

	$fb_user = get_request_value("fb_user");
	$album_id = get_request_value("album_id");
	$access_token = get_request_value("access_token");

	$con = getConnection();
	selectDb($con, "_cover");

	if(($fb_user)&&($album_id)){
		$dirpath = $LOG_PICTURE."photo/".$fb_user;

		if (file_exists($dirpath) && is_dir($dirpath)) {
		}else{
			mkdir($dirpath, 0777);
		}

		$query_url = 'https://graph.facebook.com/fql?q=SELECT+object_id,pid,aid,caption,src,src_width,src_height,src_small,src_small_width,src_small_height,src_big,src_big_width,src_big_height,created,object_id,album_object_id+FROM+photo+WHERE+album_object_id+=+'.$album_id.'+ORDER+BY+created+DESC&access_token='. $access_token;

		//$query_url = 'https://graph.facebook.com/fql?q=SELECT+pid,aid,caption,src,src_width,src_height,src_small,src_small_width,src_small_height,src_big,src_big_width,src_big_height,created,object_id,album_object_id+FROM+photo+WHERE+album_object_id+=+'.$album_id.'+ORDER+BY+created+DESC&access_token='. $access_token;

		$query_result = json_decode(file_get_contents($query_url),true);

outLog("cover_creator/","uid_".$fb_user,$query_url, "debug");


		if($query_result["data"]){
			$photos_list = array();
			$cnt = 0;
			foreach($query_result["data"] as $data){

				$tmp_url = "https://graph.facebook.com/".$data["object_id"]."?access_token=".$access_token;
				$tmp_result = json_decode(file_get_contents($tmp_url),true);

outLog("cover_creator/","uid_".$fb_user,$tmp_result["images"][0]["source"], "debug");

				$photos_list[$cnt]["id"] = $data["pid"];
				$photos_list[$cnt]["albums_id"] = $data["album_object_id"];
				$photos_list[$cnt]["caption"] = $data["caption"];

				$photos_list[$cnt]["src_big"] = $tmp_result["images"][0]["source"];
				//$photos_list[$cnt]["src_big"] = $data["src_big"];
				$photos_list[$cnt]["photos_url"] = $fb_user."/thumb_".$fb_user."_".$data["pid"];
				$photos_list[$cnt]["created"] = date("Y-m-d",$data["created"]);
				if($photos_list[$cnt]["photos_url"]){
				}else{
				}

				$sql = "SELECT * FROM `crt_cover_fb_uid_photos` ";
				$sql .= " WHERE delete_flg = '0' " ;
				$sql .= " AND  `photos_id` = '".$data["pid"]."'" ;

				$photos_data = selectList($con, $sql);

				if(isset($photos_data[0]["id"])){
					$cmd_tmp = "php ./batch_album_photos.php ".$fb_user." ".$data["pid"]." ".$tmp_result["images"][0]["source"]." > /dev/null & ";
					//$cmd_tmp = "php ./batch_album_photos.php ".$fb_user." ".$data["pid"]." ".$data["src_big"]." > /dev/null & ";
					exec($cmd_tmp);
				}else{
/*
					$value_fb_uid = $fb_user;
					$value_fb_user_name = "";
					$value_albums_id = $data["album_object_id"];
					$value_photos_id = $data["pid"];
					$value_photos_name = mysql_real_escape_string($data["caption"]);
					$value_photos_name = str_replace("\r", "", $value_photos_name);
					$value_photos_name = str_replace("\n", "", $value_photos_name);
					$value_photos_name = str_replace("\t", "", $value_photos_name);
					$value_photos_picture = mysql_real_escape_string($data["src"]);

					$value_photos_source = mysql_real_escape_string($tmp_result["images"][0]["source"]);
					//$value_photos_source = mysql_real_escape_string($data["src_big"]);

					$sql  = " insert into `crt_cover_fb_uid_photos` (`fb_uid`,`fb_user_name`,`albums_id`,`photos_id`,`photos_name`,`photos_picture`,`photos_source`,`insert_datetime`,`update_datetime`) ";
					$sql .= " values ('".$value_fb_uid."','".$value_fb_user_name."','".$value_albums_id."','".$value_photos_id."','".$value_photos_name."','".$value_photos_picture."','".$value_photos_source."',now(),now())";

					$ret  = execute($con, $sql) ;

					$cmd_tmp = "php ./batch_album_photos.php ".$fb_user." ".$data["pid"]." ".$tmp_result["images"][0]["source"]." > /dev/null & ";
					//$cmd_tmp = "php ./batch_album_photos.php ".$fb_user." ".$data["pid"]." ".$data["src_big"]." > /dev/null & ";
					exec($cmd_tmp);

					//$cmd_tmp = "php ./batch_album_photos_thumb.php ".$fb_user." ".$data["pid"]." ".$data["src"]." > /dev/null & ";
					//$cmd_tmp = "php ./batch_album_photos.php ".$fb_user." ".$data["pid"]." ".$data["src"]." > /dev/null & ";
					//exec($cmd_tmp);
*/
				}
				$cnt ++;
			}
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
	$access_page = "batch_album_photos_ajax";
	$function_time = number_format($endTime - $startTime, C_DECIMALS);
	$sql  = " insert into crt_cover_creator_access (fb_uid,my365_user_id,insta_user_id,fb_user_name,sex_type,birthday,device_code,useragent,access_page,function_time,insert_datetime,update_datetime) ";
	$sql .= " values ('".$fb_user."','".$my365_user_id."','".$insta_user_id."','".$fb_user_name."','".$fb_user_gender."','".$birthday."','".$device_code."','".$ua."','".$access_page."','".$function_time."',now(),now())";
	$ret  = execute($con, $sql) ;
	closeConnection($con);

exit();
?>
