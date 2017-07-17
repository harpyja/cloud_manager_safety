<?php

/**
 ==============================================================================

 ==============================================================================
 */

class Database {

	//-------------------------
	//数据库常用功能
	//-------------------------
	public function count_rows($table, $condition = '') {
		$sql = "SELECT COUNT(*) AS n FROM $table $condition";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		$obj = self::fetch_object ( $res );
		return $obj->n;
	}

	/**
	 * 返回第一行，第一列的值
	 *
	 */
	public function get_scalar_value($sql, $file = '', $line = 0) {
		$res = api_sql_query ( $sql, $file, $line );
		$row = mysql_fetch_row ( $res );
		return $row [0];
	}

	public static function getval($sql, $file = '', $line = 0) {
		$res = api_sql_query ( $sql, $file, $line );
		$row = mysql_fetch_row ( $res );
		return $row [0];
	}

	public function get_one_value($res) {
		$row = mysql_fetch_row ( $res );
		return $row [0];
	}

	public function if_row_exists($sql, $file = '', $line = 0) {
		$res = api_sql_query ( $sql, $file, $line );
		return mysql_num_rows ( $res ) > 0;
	}

	public function get_last_insert_id() {
		return mysql_insert_id ();
	}

	public function affected_rows() {
		return mysql_affected_rows ();
	}

	public function fetch_array($res, $option = 'ASSOC') {
		switch ($option) {
			case 'BOTH' :
				$rtn = mysql_fetch_array ( $res );
				break;
			case 'NUM' :
				$rtn = mysql_fetch_array ( $res, MYSQL_NUM );
				break;
			default :
				$rtn = mysql_fetch_array ( $res, MYSQL_ASSOC );
				break;
		}
		return $rtn;
	}

	public function fetch_object($res, $class = null, $params = null) {
		if (! empty ( $class )) {
			if (is_array ( $params )) {return mysql_fetch_object ( $res, $class, $params );}
			return mysql_fetch_object ( $res, $class );
		}
		return mysql_fetch_object ( $res );
	}

	public static function fetch_row($res) {
		return mysql_fetch_row ( $res );
	}

	public function num_rows($res) {
		return mysql_num_rows ( $res );
	}

	public function number_rows($sql) {
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		return mysql_num_rows ( $res );
	}

	public function free_result($res) {
		return mysql_free_result ( $res );
	}

	public function result($res, $row, $field) {
		return mysql_result ( $res, $row, $field );
	}

	public function get_uuid() {
		return self::get_scalar_value ( "SELECT MD5(UUID())" );
	}

	public function get_into_array($sql, $file = '', $line = 0) {
		$res = api_sql_query ( $sql, $file, $line );
		$tab = array ();
		while ( $row = self::fetch_array ( $res, "NUM" ) ) {
			$tab [] = $row [0];
		}
		return $tab;
	}

	public function get_into_array2($sql, $file = '', $line = 0) {
		$res = api_sql_query ( $sql, $file, $line );
		$tab = array ();
		while ( $row = self::fetch_array ( $res, "NUM" ) ) {
			$tab [$row [0]] = $row [1];
		}
		return $tab;
	}

	public function html_filter($data) {
		require_once (api_get_path ( LIB_PATH ) . 'html_purifier/HTMLPurifier.auto.php');
		$config = HTMLPurifier_Config::createDefault ();
		$config->set ( 'Core', 'Encoding', SYSTEM_CHARSET );
		$config->set ( 'HTML', 'Doctype', 'XHTML 1.0 Transitional' );
		$purifier = new HTMLPurifier ( $config );
		$ret = $purifier->purify ( $data );
		return $ret;
	}

	public function escape_string($data, $filter = true) {
		if (! isset ( $data ) or empty ( $data )) return '';
		if (is_array ( $data )) {
			return array_map ( array ('Database', 'escape_string' ), $data );
		} else {
			if (get_magic_quotes_gpc ()) {
				$data = stripslashes ( $data );
			}
			
			if (! is_numeric ( $data )) {
				$data = mysql_real_escape_string ( $data );
			}
			
			return $data;
		}
	}

	public function escape_str($str, $like = FALSE) {
		if (is_array ( $str )) {
			foreach ( $str as $key => $val ) {
				$str [$key] = self::escape_str ( $val, $like );
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
		if ($like === TRUE) {
			$str = str_replace ( array ('%', '_' ), array ('\\%', '\\_' ), $str );
		}
		
		return $str;
	}

	public function escape($str) {
		if (is_string ( $str )) {
			$str = "'" . self::escape_str ( $str ) . "'";
		} elseif (is_bool ( $str )) {
			$str = ($str === FALSE) ? 0 : 1;
		} elseif (is_null ( $str )) {
			$str = 'NULL';
		}
		return $str;
	}

	public static function query($sql, $file = '', $line = 0) {
		return api_sql_query ( $sql, $file, $line );
	}

	public function sql_select($tbname, $where = "", $fields = "*", $orderby = "", $limit = 0) {
		$sql = "SELECT " . $fields . " FROM " . $tbname . " " . ($where ? " WHERE " . $where : "") . ($orderby ? " ORDER BY " . $orderby : "") . " " . ($limit ? " limit " . $limit : "");
		return $sql;
	}

	public function sql_insert($tbname, $row, $is_contained_ignore = false) {
		foreach ( $row as $key => $value ) {
			$sqlfield .= $key . ",";
			//if(is_null($value) OR (is_string($value) && $value=="")){
			if (strtoupper ( $value ) == 'NULL') {
				$sqlvalue .= "NULL,";
			} else {
				$sqlvalue .= self::escape ( $value ) . ",";
			}
		}
		return "INSERT " . ($is_contained_ignore ? "IGNORE" : "") . " INTO " . $tbname . " (" . substr ( $sqlfield, 0, - 1 ) . ") VALUES (" . substr ( $sqlvalue, 0, - 1 ) . ")";
	}

	public function sql_update($tbname, $row, $where) {
		foreach ( $row as $key => $value ) {
			if (strtoupper ( $value ) == 'NULL') {
				$sqlud .= $key . "= NULL,";
			} else {
				$sqlud .= $key . "= " . self::escape ( $value ) . ",";
			}
		}
		return "UPDATE " . $tbname . " SET " . substr ( $sqlud, 0, - 1 ) . " WHERE " . $where;
	}

	public function sql_delete($tbname, $where) {
		return "DELETE FROM " . $tbname . " WHERE " . $where;
	}

	/**
	 * 创建像这样的查询: "IN('a','b')";
	 *
	 * @access   public
	 * @param    mix      $item_list      列表数组或字符串,如果为字符串时,字符串只接受数字串
	 * @param    string   $field_name     字段名称
	 * @author   wj
	 *
	 * @return   void
	 */
	public function create_in($item_list, $field_name = '') {
		if (empty ( $item_list )) {
			return $field_name . " IN ('') ";
		} else {
			if (! is_array ( $item_list )) {
				$item_list = explode ( ',', $item_list );
				foreach ( $item_list as $k => $v ) {
					$item_list [$k] = intval ( $v );
				}
			}
			
			$item_list = array_unique ( $item_list );
			$item_list_tmp = '';
			foreach ( $item_list as $item ) {
				if ($item !== '') {
					$item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
				}
			}
			if (empty ( $item_list_tmp )) {
				return $field_name . " IN ('') ";
			} else {
				return $field_name . ' IN (' . $item_list_tmp . ') ';
			}
		}
	}

	/**
	 * 获取一行记录
	 * @param $sql
	 * @param $limited
	 * @return unknown_type
	 */
	public function fetch_one_row($sql, $limited = false, $file = "", $line = 0) {
		if ($limited == true) $sql = trim ( $sql . ' LIMIT 1' );
		$res = api_sql_query ( $sql, $file, $line );
		if ($res !== false) {
			$row = Database::fetch_array ( $res, 'ASSOC' );
			if ($row !== false) {
				return $row;
			} else {
				return FALSE;
			}
		} else {
			return false;
		}
	}

	/*
	 -----------------------------------------------------------------------------
		Accessor Functions
		Usually, you won't need these directly but instead
		rely on of the get_xxx_table functions.
		-----------------------------------------------------------------------------
		*/
	public function get_main_database() {
		global $_configuration;
		return $_configuration ['main_database'];
	}

	public function get_statistic_database() {
		global $_configuration;
		return $_configuration ['statistics_database'];
	}

	public function get_database_glue() {
		global $_configuration;
		return $_configuration ['db_glue'];
	}

	public function get_database_name_prefix() {
		global $_configuration;
		return $_configuration ['db_prefix'];
	}

	public function get_course_table_prefix() {
		global $_configuration;
		return $_configuration ['table_prefix'];
	}

	public function get_main_table($short_table_name) {
		$database = Database::get_main_database ();
		return Database::format_table_name ( $database, $short_table_name );
	}

	public function get_course_table($short_table_name, $database_name = '') {
		$short_table_name = self::get_course_table_prefix () . $short_table_name;
		$database_name_with_glue = (self::get_main_database ()) . self::get_database_glue ();
		return Database::format_glued_course_table_name ( $database_name_with_glue, $short_table_name );
	}

	function get_statistic_table($short_table_name) {
		$database = Database::get_statistic_database ();
		return Database::format_table_name ( $database, $short_table_name );
	}

	public function get_language_isocode($lang_folder) {
		$table = Database::get_main_table ( TABLE_MAIN_LANGUAGE );
		$sql_query = "SELECT isocode FROM $table WHERE dokeos_folder = '$lang_folder'";
		$sql_result = api_sql_query ( $sql_query, __FILE__, __LINE__ );
		$result = mysql_fetch_array ( $sql_result );
		return $result ['isocode'];
	}

	public function glue_course_database_name($database_name) {
		$prefix = Database::get_course_table_prefix ();
		$glue = Database::get_database_glue ();
		$database_name_with_glue = $prefix . $database_name . $glue;
		return $database_name_with_glue;
	}

	public function fix_database_parameter($database_name) {
		if ($database_name == '') {
			$course_info = api_get_course_info ();
			$database_name_with_glue = $course_info ["dbNameGlu"]; //如 : webcs_DEMOCOURSE`.`
		} else {
			$database_name_with_glue = Database::glue_course_database_name ( $database_name );
		}
		return $database_name_with_glue;
	}

	public function format_glued_course_table_name($database_name_with_glue, $table) {
		$course_info = api_get_course_info ();
		return "`" . $database_name_with_glue . $table . "`";
	}

	public function format_table_name($database, $table) {
		return "`" . $database . "`.`" . $table . "`";
	}

	/*	 -----------------------------------------------------------------------------
		Query Functions		these execute a query and return the result(s).
		-----------------------------------------------------------------------------*/
	public function get_user_course_sql($field_name, $user_id = NULL) {
		if (! $user_id) $user_id = api_get_user_id ();
		if ($field_name && $user_id) {
			$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
			$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
			$sql = "SELECT t2.code,t2.db_name,t2.title,t2.tutor_name FROM " . $table_course_user . " AS t1 LEFT JOIN " . $table_course . " AS t2 ON t1.course_code=t2.code WHERE t1.user_id='" . Database::escape_string ( $user_id ) . "' ORDER BY t1.creation_time DESC";
			//echo $sql;
			$my_courses = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
			if (is_array ( $my_courses ) && count ( $my_courses ) > 0) {
				foreach ( $my_courses as $key => $value ) {
					$all_cc [] = $value ['code'];
				}
				$sql_cc_in = Database::create_in ( $all_cc, $field_name );
			}
			return $sql_cc_in;
		}
		return false;
	}

	public function insert_into_course_table($table, $sql_row, $courseCode, $field = 'cc') {
		if ($table && $sql_row) {
			if (empty ( $courseCode )) $courseCode = api_get_course_code ();
			$sql_row [$field] = $courseCode;
			$sql = Database::sql_insert ( $table, $sql_row );
			return api_sql_query ( $sql, __FILE__, __LINE__ );
		}
		return false;
	}

	public function delete_from_course_table($table, $where = '', $courseCode, $field = 'cc') {
		if ($table) {
			if (empty ( $courseCode )) $courseCode = api_get_course_code ();
			if (empty ( $where )) {
				$sql_where = $field . "=" . Database::escape ( $courseCode );
			} else {
				$sql_where = $where . " AND " . $field . "=" . Database::escape ( $courseCode );
			}
			$sql = Database::sql_delete ( $table, $sql_where );
			return api_sql_query ( $sql, __FILE__, __LINE__ );
		}
	}

	public function update_course_table($table, $sql_row, $where = "", $courseCode, $field = 'cc') {
		if ($table && $sql_row) {
			if (empty ( $courseCode )) $courseCode = api_get_course_code ();
			$sql_where = " " . $field . "=" . Database::escape ( $courseCode );
			$sql = Database::sql_update ( $table, $sql_row, $sql_where . " " . ($where ? " AND " . $where : "") );
			return api_sql_query ( $sql, __FILE__, __LINE__ );
		}
		return false;
	}

	public function select_from_course_table($tbname, $where = '', $fields = '*', $orderby = "", $limit = 0, $courseCode, $courseField = 'cc', $rtn = TRUE) {
		if (empty ( $courseCode )) $courseCode = api_get_course_code ();
		$sql_where = " WHERE " . $courseField . "=" . Database::escape ( $courseCode );
		$sql = "SELECT " . $fields . " FROM " . $tbname . " " . $sql_where . " " . ($where ? " AND " . $where : "") . ($orderby ? " ORDER BY " . $orderby : "") . " " . ($limit ? " limit " . $limit : "");
		//echo $sql."<Br/>";
		if ($rtn) return api_sql_query ( $sql, __FILE__, __LINE__ );
		else return $sql;
	}

	public function get_course_info($course_code) {
		$table = Database::get_main_table ( TABLE_MAIN_COURSE );
		$sql_query = "SELECT * FROM $table WHERE `code` = '$course_code'";
		$sql_result = api_sql_query ( $sql_query, __FILE__, __LINE__ );
		if ($result = self::fetch_array ( $sql_result, 'ASSOC' )) {
			$result_array ["code"] = $result ["code"];
			$result_array ["visual_code"] = $result ["visual_code"];
			$result_array ["title"] = $result ['title'];
			$result_array ["category_code"] = $result ["category_code"];
			return $result_array;
		}
		return false;
	}

	/**
	 * @param $user_id (integer): the id of the user
	 * @return $user_info (array): user_id, lastname, firstname, username, email, ...
	 * @author Patrick Cool <patrick.cool@UGent.be>, expanded to get info for any user
	 * @author Roan Embrechts, first version + converted to Database API
	 * @version 30 September 2004
	 * @desc find all the information about a specified user. Without parameter this is the current user.
	 * @todo shouldn't this be in the user.lib.php script?
	 */
	public function get_user_info_from_id($user_id = '') {
		$table = Database::get_main_table ( TABLE_MAIN_USER );
		if ($user_id == '') {
			return $GLOBALS ["_user"];
		} else {
			$sql_query = "SELECT * FROM $table WHERE `user_id` = '$user_id'";
			$result = api_sql_query ( $sql_query, __FILE__, __LINE__ );
			$result_array = mysql_fetch_array ( $result );
			
			$result_array ['firstName'] = $result_array ['firstname'];
			$result_array ['lastName'] = $result_array ['lastname'];
			$result_array ['mail'] = $result_array ['email'];
			#$result_array['picture_uri'] 	= $result_array['picture_uri'];
			#$result_array ['user_id'  ] 	= $result_array['user_id'   ];
			return $result_array;
			return $result_array;
		}
	}

	public function get_user_with_dept($user_id = NULL) {
		if (empty ( $user_id )) $user_id = api_get_user_id ();
		if ($user_id) {
			//$view_user_dept=Database::get_main_table(VIEW_USER_DEPT);
			$table_user = Database::get_main_table ( TABLE_MAIN_USER );
			//include_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');
			require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
			$sql = "SELECT username,firstname,dept_id FROM $table_user WHERE user_id=" . Database::escape ( $user_id );
			$res = self::query ( $sql, __FILE__, __LINE__ );
			list ( $username, $firstname, $dept_id ) = self::result ( $res, 0 );
			
			$deptObj = new DeptManager ();
			$dept_path = $deptObj->get_dept_path ( $dept_id, FALSE );
			
			$dept_arr = explode ( "/", $dept_path );
			$org_name = (is_array ( $dept_arr ) ? end ( $dept_arr ) : $dept_arr);
			
			return list ( $username, $firstname, $dept_path, $org_name ) = array ($username, $firstname, $dept_path, $org_name );
		}
		return "";
	}
}