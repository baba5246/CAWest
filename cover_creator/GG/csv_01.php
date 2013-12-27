<?php

	require './gg_require.php';


	$access_page = array(
		"index" => "トータルビュー",
		"index_user" => "ユーザービュー",
		"thank_" => "画像投稿"
	);

	$access_page_list_group_by_fb_uid = array(
		"picture_tag_" => "画像生成_タグ",
		"picture_tag_check_tag_01" => "画像生成_タグ_01",
		"picture_tag_check_tag_02" => "画像生成_タグ_02",
		"picture_tag_check_tag_03" => "画像生成_タグ_03",
		"picture_tag_check_tag_04" => "画像生成_タグ_04",
		"picture_tag_check_tag_05" => "画像生成_タグ_05",
		"picture_tag_check_tag_06" => "画像生成_タグ_06",
		"picture_tag_check_tag_07" => "画像生成_タグ_07",
		"picture_tag_check_tag_08" => "画像生成_タグ_08",
		"picture_tag_check_tag_09" => "画像生成_タグ_09",
		"picture_tag_check_tag_10" => "画像生成_タグ_10",
		"picture_album_photo_" => "画像生成_アルバム",
		"picture_album_photo_check_album_photo_01" => "画像生成_アルバム_01",
		"picture_album_photo_check_album_photo_02" => "画像生成_アルバム_02",
		"picture_album_photo_check_album_photo_03" => "画像生成_アルバム_03",
		"picture_album_photo_check_album_photo_04" => "画像生成_アルバム_04",
		"picture_album_photo_check_album_photo_05" => "画像生成_アルバム_05",
		"picture_album_photo_check_album_photo_06" => "画像生成_アルバム_06",
		"picture_album_photo_check_album_photo_07" => "画像生成_アルバム_07",
		"picture_album_photo_check_album_photo_08" => "画像生成_アルバム_08",
		"picture_album_photo_check_album_photo_09" => "画像生成_アルバム_09",
		"picture_album_photo_check_album_photo_10" => "画像生成_アルバム_10",
		"picture_my365_" => "画像生成_my365",
		"picture_my365_365_01" => "画像生成_my365_01",
		"picture_my365_365_02" => "画像生成_my365_02",
		"picture_instagram_" => "画像生成_instagram",
		"picture_instagram_inst_01" => "画像生成_instagram_01",
		"picture_instagram_inst_02" => "画像生成_instagram_02"
	);

	$access_user = array(
		"user" => "新規ユーザー数",
	);

	$target_date = "2012-08-07";
	//$target_date = date('Y-m-d',strtotime('yesterday', time()));
	$date_from = $target_date.' 00:00:00';
	$date_to   = $target_date.' 23:59:59';
	$sql_where_common = " where fb_uid not in ('100001266476736','100003231024714','100001667290389','100001601090005','100003706654531','1155734596') and insert_datetime >= '".$date_from."' and insert_datetime <= '".$date_to."' ";

	$con = getConnection();
	selectDb($con, "cover");

	$csv_list = array();
	$cnt = 0;

	foreach($access_page as $key => $value){
	
		if($key == "index"){
			$sql_where = $sql_where_common . " and access_page = '".$key."' ";
		}elseif($key == "index_user"){
			$sql_where = $sql_where_common . " and access_page = 'index' group by fb_uid ";
		}elseif($key == "thank_"){
			$sql_where = $sql_where_common . " and access_page like '%".$key."%'  group by fb_uid ";
		}
		
		$sql = "select * from crt_cover_creator_access " .$sql_where ;

		$result_list = selectList($con, $sql) ;

		$csv_list[$cnt]["id"] = $key;
		$csv_list[$cnt]["name"] = $value;
		$csv_list[$cnt]["count"] = count($result_list);

		$cnt ++;
	}

	foreach($access_page_list_group_by_fb_uid as $key => $value){
	
		$sql_where = $sql_where_common . " and access_page like '%".$key."%' group by fb_uid ";
		$sql = "select * from crt_cover_creator_access " .$sql_where ;
		$result_list = selectList($con, $sql) ;

		$csv_list[$cnt]["id"] = $key;
		$csv_list[$cnt]["name"] = $value;
		$csv_list[$cnt]["count"] = count($result_list);

		$cnt ++;
	}

	foreach($access_user as $key => $value){
	
		$sql_where = $sql_where_common . " group by fb_uid ";
		$sql = "select * from crt_cover_creator_user " .$sql_where ;
		$result_list = selectList($con, $sql) ;

		$csv_list[$cnt]["id"] = $key;
		$csv_list[$cnt]["name"] = $value;
		$csv_list[$cnt]["count"] = count($result_list);

		$cnt ++;
	}


	closeConnection($con);

	$csv_file = "test_01.csv";
	$csv_data = "";

	$csv_data .= $target_date;
	$csv_data .= "\n";

	// CSVデータの作成
	$cnt = 0;
	foreach($csv_list as $data ){
		$cnt ++ ;
		$csv_data .= $data["id"]. ",";
		$csv_data .= $data["name"]. ",";
		$csv_data .= $data["count"];
	
		if(count($csv_list) !== intval($key)+1){
		
			$csv_data .= "\n";
		
		}
	}

	// MIMEタイプの設定
	header("Content-Type: application/octet-stream");

	// ファイル名の表示
	header("Content-Disposition: attachment; filename=$csv_file");

	// データの出力
	echo(mb_convert_encoding($csv_data,"SJIS","UTF-8"));
?>
