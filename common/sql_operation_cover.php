<?php

	function select_cover_fb_uid_albums($fb_user,$equal=null,$albums_id=null) {

		$limit_date = date("Y-m-d H:i:s",strtotime("-1 hour"));

		$sql =  " select * from crt_cover_fb_uid_albums " ;
		$sql .= " where fb_uid = '".$fb_user."'";

		if(($albums_id)&&($equal)){
			$sql .= " and albums_id ".$equal." '".$albums_id."'";
		}

		$sql .= " and insert_datetime > '".$limit_date."'";
		$sql .= " order by `insert_datetime` DESC  ";
		return $sql;
	}


	function insert_cover_fb_uid_albums($fb_user,$albums_id,$albums_name,$albums_cover_photo_id,$albums_cover_photo_url,$albums_type,$albums_cover_photo_width,$albums_cover_photo_height,$albums_photos_number) {
		$sql =  " INSERT INTO `crt_cover_fb_uid_albums` ";
		$sql .= " (fb_uid,albums_id,albums_name,albums_cover_photo_id,albums_cover_photo_url,albums_type,albums_cover_photo_width,albums_cover_photo_height,albums_photos_number,insert_datetime,update_datetime) VALUES ('".$fb_user."','".$albums_id."','".$albums_name."','".$albums_cover_photo_id."','".$albums_cover_photo_url."','".$albums_type."',".$albums_cover_photo_width.",".$albums_cover_photo_height.",".$albums_photos_number.",now(),now())";

		return $sql;
	}


	function select_moteken_answer_master($qa_score_sum,$fb_user_gender,$answer_level) {

		$sql =  " select * from crt_moteken_answer_master " ;
		$sql .= " where answer_result = '".$qa_score_sum."'";
		$sql .= " and sex_type = '".$fb_user_gender."'";
		$sql .= " and answer_level = ".$answer_level;
		$sql .= " order by `insert_datetime` DESC  ";
		return $sql;
	}

	function select_moteken_answer_tran($qa_score_sum,$fb_user,$fb_user_friends_list,$limit=null) {

		$sql =  " select DISTINCT fb_uid from crt_moteken_answer_tran " ;
		$sql .= " where answer_result = '".$qa_score_sum."'";
		$sql .= " and fb_uid != '".$fb_user."'";
		$sql .= " and fb_uid in ('".$fb_user_friends_list."')";
		$sql .= " order by insert_datetime desc ";
		if($limit){
		$sql .= " limit ".$limit ." ";
		}

		return $sql;
	}


	function insert_moteken_access($fb_uid=null,$fb_user_name=null,$sex_type=null,$device_code=null,$access_page=null,$answer_result=null,$useragent=null) {

		$sql =  " INSERT INTO `crt_moteken_access` ";
		$sql .= " (fb_uid,fb_user_name,sex_type,device_code,access_page,answer_result,useragent,insert_datetime,update_datetime) VALUES ('".$fb_uid."','".$fb_user_name."','".$sex_type."','".$device_code."','".$access_page."','".$answer_result."','".$useragent."',now(),now())";

		return $sql;
	}


?>