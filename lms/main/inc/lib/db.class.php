<?php

/*
 [UCenter] (C)2001-2009 Comsenz Inc.
 This is NOT a freeware, use is subject to license terms

 $Id: db.class.php 753 2008-11-14 06:48:25Z cnteacher $
 */

/**
 * <pre>
 * require_once UC_ROOT.'lib/db.class.php';
 $db = new db();
 $db->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET, UC_DBCONNECT, UC_DBTABLEPRE);
 *
 * </pre>
 *
 */
class db {
	var $querynum = 0;
	var $link = NULL;
	var $histories;
	
	var $dbhost;
	var $dbuser;
	var $dbpw;
	var $dbcharset;
	var $pconnect;
	var $tablepre;
	var $time;
	
	var $goneaway = 5;

	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $dbcharset = '', $pconnect = 0, $tablepre = '', $time = 0) {
		$this->dbhost = $dbhost;
		$this->dbuser = $dbuser;
		$this->dbpw = $dbpw;
		$this->dbname = $dbname;
		$this->dbcharset = $dbcharset;
		$this->pconnect = $pconnect;
		$this->tablepre = $tablepre;
		$this->time = $time;
		
		if ($pconnect) {
			if (! $this->link = mysql_pconnect ( $dbhost, $dbuser, $dbpw )) {
				$this->halt ( 'Can not connect to MySQL server' );
			}
		} else {
			if (! $this->link = mysql_connect ( $dbhost, $dbuser, $dbpw )) {
				$this->halt ( 'Can not connect to MySQL server' );
			}
		}
		
		if ($this->version () > '4.1') {
			if ($dbcharset) {
				//mysql_query("SET character_set_connection=".$dbcharset.", character_set_results=".$dbcharset.", character_set_client=binary", $this->link);
				if (! function_exists ( 'mysql_set_charset' )) {
					mysql_query ( "SET NAMES " . $dbcharset );
				} else {
					mysql_set_charset ( $dbcharset, $this->link );
				}
			}
			
			if ($this->version () > '5.0.1') {
				mysql_query ( "SET sql_mode=''", $this->link );
			}
		}
		
		if ($dbname) {
			mysql_select_db ( $dbname, $this->link );
		}
	
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array ( $query, $result_type );
	}

	function fetch_object($query) {
		return mysql_fetch_object ( $query );
	}

	/**
	 * 获取第一行第一列
	 * @param $sql
	 */
	function fetch_scalar($sql) {
		$query = $this->query ( $sql );
		$row = $this->fetch_row ( $query );
		return $row [0];
	}

	/**
	 * 获取第一行
	 * @param unknown_type $sql
	 */
	function result_first($sql) {
		$query = $this->query ( $sql );
		return $this->result ( $query, 0 );
	}

	/**
	 * 获取第一行,二元数组
	 * @param $sql
	 */
	function fetch_first($sql) {
		$query = $this->query ( $sql );
		return $this->fetch_array ( $query );
	}

	/**
	 * 获取所有数据,
	 * @param $sql
	 * @param $id 索引字段
	 */
	function fetch_all($sql, $id = '') {
		$arr = array ();
		$query = $this->query ( $sql );
		while ( $data = $this->fetch_array ( $query ) ) {
			$id ? $arr [$data [$id]] = $data : $arr [] = $data;
		}
		return $arr;
	}

	function query($sql, $type = '', $cachetime = FALSE) {
		$func = ($type == 'UNBUFFERED' && @function_exists ( 'mysql_unbuffered_query' ) ? 'mysql_unbuffered_query' : 'mysql_query');
		if (! ($query = $func ( $sql, $this->link )) && $type != 'SILENT') {
			$this->halt ( 'MySQL Query Error', $sql );
		}
		$this->querynum ++;
		$this->histories [] = $sql;
		return $query;
	}

	function affected_rows() {
		return mysql_affected_rows ( $this->link );
	}

	function error() {
		return (($this->link) ? mysql_error ( $this->link ) : mysql_error ());
	}

	function errno() {
		return intval ( ($this->link) ? mysql_errno ( $this->link ) : mysql_errno () );
	}

	function result($query, $row, $field) {
		$query = @mysql_result ( $query, $row, $field );
		return $query;
	}

	function num_rows($query) {
		$query = mysql_num_rows ( $query );
		return $query;
	}

	function num_fields($query) {
		return mysql_num_fields ( $query );
	}

	function free_result($query) {
		return mysql_free_result ( $query );
	}

	function insert_id() {
		return ($id = mysql_insert_id ( $this->link )) >= 0 ? $id : $this->result ( $this->query ( "SELECT last_insert_id()" ), 0 );
	}

	function fetch_row($query) {
		$query = mysql_fetch_row ( $query );
		return $query;
	}

	function fetch_fields($query) {
		return mysql_fetch_field ( $query );
	}

	function version() {
		return mysql_get_server_info ( $this->link );
	}

	function close() {
		return mysql_close ( $this->link );
	}

	function cache_gc() {
		$this->query ( "DELETE FROM {$this->tablepre}sqlcaches WHERE expiry<$this->time" );
	}

	function halt($message = '', $sql = '') {
		$error = mysql_error ();
		$errorno = mysql_errno ();
		if ($errorno == 2006 && $this->goneaway -- > 0) {
			$this->connect ( $this->dbhost, $this->dbuser, $this->dbpw, $this->dbname, $this->dbcharset, $this->pconnect, $this->tablepre, $this->time );
			$this->query ( $sql );
		} else {
			$s = '<b>Error:</b>' . $error . '<br />';
			$s .= '<b>Errno:</b>' . $errorno . '<br />';
			$s .= '<b>SQL:</b>:' . $sql;
			exit ( $s );
		}
	}

	function escape_str($str, $like = FALSE) {
		if (is_array ( $str )) {
			foreach ( $str as $key => $val ) {
				$str [$key] = $this->escape_str ( $val, $like );
			}
			return $str;
		}
		
		if (get_magic_quotes_gpc ()) {
			$str = stripslashes ( $str );
		}
		
		if (function_exists ( 'mysql_real_escape_string' )) {
			$str = mysql_real_escape_string ( $str );
		} elseif (function_exists ( 'mysql_escape_string' )) {
			$str = mysql_escape_string ( $str );
		} else {
			$str = addslashes ( $str );
		}
		
		// escape LIKE condition wildcards
		if ($like === TRUE) $str = str_replace ( array ('%', '_' ), array ('\\%', '\\_' ), $str );
		return $str;
	}

	function escape($str) {
		if (is_string ( $str )) {
			$str = "'" . $this->escape_str ( $str ) . "'";
		} elseif (is_bool ( $str )) {
			$str = ($str === FALSE) ? 0 : 1;
		} elseif (is_null ( $str )) {
			$str = 'NULL';
		}
		
		return $str;
	}
}
