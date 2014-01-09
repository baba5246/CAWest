<?php
	mb_language("Japanese");
	mb_internal_encoding("utf-8");
	mb_detect_order("ASCII,JIS,UTF-8,EUC-JP,SJIS");

	date_default_timezone_set('Asia/Tokyo');

//--------------------------------------------------------------
// サーバー設定情報
//--------------------------------------------------------------
	define("SEVER_DOMAIN", "ca-creative.jp");
	//define("SEVER_DOMAIN", "http://210.188.234.241");

//--------------------------------------------------------------
// ログ出力先
//--------------------------------------------------------------

	/** ログ出力先メインディレクトリ */
	define("LOG_DIR_NAME", "/var/www/lib/logs/");
	
	/** 実行ログ名 */
	define("ACTION_LOG_NAME", "action");
	
	/** DBログ名 */
	define("DB_LOG_NAME", "db");
	
	/** エラーログ名 */
	define("ERROR_LOG_NAME", "error");
	
//--------------------------------------------------------------
// システムDB用の設定情報
//--------------------------------------------------------------
	/** MASTER */
	define("DSN", "localhost");
	define("DSN_USER", "root");
	define("DSN_PASS", "passw0rd");
		
	/** DBの名前 */
	define("DB_NAME", "facebook");

//--------------------------------------------------------------
// アプリ用の設定情報
//--------------------------------------------------------------
	//Moteken
	define("APPID_MOTEKEN", "159891050783920");
	define("SECRET_MOTEKEN", "e766c08e0b63a8dc2d9f40893abe54ad");
	define("SCOPE_MOTEKEN", "offline_access,read_friendlists,photo_upload,status_update,publish_stream,publish_actions,user_photos,user_videos,friends_photos,friends_videos");
	define("PICTURE_MOTEKEN", "/var/www/lib/picture/");

	//Dogenzaka
	define("APPID_DOGENZAKA", "253223454727480");
	define("SECRET_DOGENZAKA", "01f2d332bb7337ca4744a8ea68bc3c1f");
	define("SCOPE_DOGENZAKA", "offline_access,read_friendlists,photo_upload,status_update,publish_stream,publish_actions,user_photos,user_videos,friends_photos,friends_videos");
	define("PICTURE_DOGENZAKA", "/var/www/lib/picture/");

	//Dogenzaka_mobile
	define("APPID_DOGENZAKA_MOBILE", "281944905182678");
	define("SECRET_DOGENZAKA_MOBILE", "5f3c26ad3a83eeeb245833e7bf5ab3fe");

?>
