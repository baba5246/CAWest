<?php

//--------------------------------------------------------------
// 
//--------------------------------------------------------------
	function get_request_value($key, $default_value=null) {
		$value = null;
		if (@$_POST[$key] !== null) {
			$value = @$_POST[$key];
		} elseif (@$_GET[$key] !== null) {
			$value = @$_GET[$key];
		}
		
		if (!$value && !is_null($default_value)) {
			$value = $default_value;
		}
		
		return $value;
	}
	
//--------------------------------------------------------------
// 
//--------------------------------------------------------------
	function outLog($domain=null,$fb_user,$value, $log_name) {
		//配列の場合は文字列に変換
		if (is_array($value)) {
			 $value = print_r($value, true); 
		}
		//リクエストURL
		global $_SERVER;
		$url = $_SERVER['PHP_SELF'];

		$dir_name = LOG_DIR_NAME . $domain . $log_name . "/" . date("Ym");
		//$dir_name = LOG_DIR_NAME . $log_name . "/" . date("Ym");
		if (!is_dir($dir_name)) {
			if (!mkdir($dir_name)) {
				//print ("ログディレクトリ作成エラーです。");
				return false;
			}
			chmod($dir_name, 0755);
		}

		$logFleName = $dir_name . "/" . $log_name . "_" . date("Ymd") . ".log";

		
		$logFile = fopen($logFleName, "a");
		if (!$logFile) {
			//print ("ログファイル作成エラーです。");
			return false;
		}
		flock($logFile, LOCK_EX);

		$ua = "";
//		$ua = $_SERVER['HTTP_USER_AGENT'];
		
		//ログを出力する
		$log_str = date("Y-m-d H:i:s"). "\t"  . $ua. "\t"  . $url . "\t" . $fb_user . "\t" . $value . "\r\n";
		fputs($logFile, mb_convert_encoding($log_str,"SJIS","utf-8"));
		
		//ログ出力を終了する
		flock($logFile, LOCK_UN);
		fclose($logFile);
	}


?>
