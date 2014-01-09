<?php
	// -------------------------------------------------------------------
	// DBコネクションを生成する(MORA/MASTER使用)
	// -------------------------------------------------------------------
	function getConnection($db_name=DB_NAME, $dsn=DSN, $dsn_user=DSN_USER, $dsn_pass=DSN_PASS) {
		$con = null;
		if (!($con = mysql_connect($dsn, $dsn_user, $dsn_pass, TRUE))) {
			//ログ出力
			
			exit("DB接続エラー");
		}
		selectDb($con, $db_name);

		return $con;
	}
	
	// -------------------------------------------------------------------
	// DBコネクションを閉じる
	// -------------------------------------------------------------------
	function closeConnection($con) {
		mysql_close($con);
	}
	
	// -------------------------------------------------------------------
	// DBの切り替えを行う
	// -------------------------------------------------------------------
	function selectDb($con, $db_name) {
		if (!(mysql_select_db($db_name, $con))) {
			exit("DB選択エラー");
		}
		db_send_query("SET NAMES utf8", $con);
	}

	// -------------------------------------------------------------------
	// SELECTのSQL文を実行する
	// -------------------------------------------------------------------
	function select($con, $sql) {
		//開始時刻
		if (trim($sql) == "") {
			outErrorLog("Query was empty");
			return FALSE;
		}
		
		$res = null;
		if (!($res = db_send_query($sql, $con))) {
			return FALSE;
		}
		return $res;
	}
	
	// -------------------------------------------------------------------
	// SELECTのSQL文を実行してリストで返す
	// -------------------------------------------------------------------
	function selectList($con, $sql, $list_key=null) {
		if (trim($sql) == "") {
			return FALSE;
		}
		$res = select($con, $sql);
		if (!$res) {
			return FALSE;
		}

		$list = array();
		while($row = mysql_fetch_assoc($res)) {
			if ($list_key) {
				$key = $row[$list_key];
				$list[$key] = $row;
			} else {
				$list[] = $row;
			}
		}
		
		//メモリの解放
		mysql_free_result($res);
		
		return $list;
	}
	
	// -------------------------------------------------------------------
	// トランザクションを開始する
	// -------------------------------------------------------------------
	function begin($con) {
	if (!($res = db_send_query("BEGIN", $con))) {
			return FALSE;
		}
		
		return $res;
	}

	// -------------------------------------------------------------------
	// コミットを実行する
	// -------------------------------------------------------------------
	function commit($con) {
		if (!($res = db_send_query("COMMIT", $con))) {
			return FALSE;
		}
		
		return $res;
	}

	// -------------------------------------------------------------------
	// ロールバックを実行する
	// -------------------------------------------------------------------
	function rollback($con) {
		if (!($res = db_send_query("ROLLBACK", $con))) {
			return FALSE;
		}
		
		return $res;
	}

	// -------------------------------------------------------------------
	// トランザクション内のSQLを実行する
	// -------------------------------------------------------------------
	function executeTransaction($con, $sql) {
		if (!($res = db_send_query($sql, $con))) {
			db_send_query("ROLLBACK", $con);
			return FALSE;
		}
		
		return $res;
	}

	// -------------------------------------------------------------------
	// トランザクション外のSQLを実行する
	// -------------------------------------------------------------------
	function execute($con, $sql) {
		if (trim($sql) == "") {
			return FALSE;
		}
		
		if (!($res = db_send_query($sql, $con))) {
			return FALSE;
		}
		
		return TRUE;
	}

	// -------------------------------------------------------------------
	// SQLをDBに投げる
	// -------------------------------------------------------------------
	function db_send_query($sql, $con) {

		$res = mysql_query($sql, $con);
		if ($res === FALSE) {
			return FALSE;
		}
		return $res;
	}
?>