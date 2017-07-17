<?php

/*
 ==============================================================================

 ==============================================================================
 */

class CourseClassManager {

	function get_all_classes_info($course_code = '', $incTop = TRUE) {
		$all_classes = self::get_all_classes ( $course_code );
		$all_classes_arr = array ();
		if ($incTop) $all_classes_arr [0] = '---' . get_lang ( 'PleaseSelectAChoice' ) . '---';
		if ($all_classes and is_array ( $all_classes )) {
			foreach ( $all_classes as $tmp_class_info ) {
				$all_classes_arr [$tmp_class_info ['id']] = $tmp_class_info ['name'];
			}
		}
		return $all_classes_arr;
	}

	function get_class_info($class_id) {
		$table_class = Database::get_course_table ( TABLE_TOOL_COURSE_CLASS );
		$sql = "SELECT * FROM $table_class WHERE id='" . $class_id . "'";
		return Database::fetch_one_row ( $sql, false, __FILE__, __LINE__ );
	}

	function update_name($name, $class_id, $code) {
		$table_class = Database::get_course_table ( TABLE_TOOL_COURSE_CLASS );
		$sql = "UPDATE $table_class SET name='" . escape ( $name ) . "' ";
		if (isset ( $code ) && ! empty ( $code )) $sql .= ",code='" . escape ( $code ) . "'";
		$sql .= " WHERE id='" . $class_id . "'";
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}

	function create_class($name, $code = "") {
		$table_class = Database::get_course_table ( TABLE_TOOL_COURSE_CLASS );
		$sql = "INSERT INTO $table_class SET name='" . escape ( $name ) . "'";
		if (isset ( $code ) && ! empty ( $code )) $sql .= ",code='" . escape ( $code ) . "'";
		$sql .= " ,cc='" . api_get_course_code () . "' ";
		api_sql_query ( $sql, __FILE__, __LINE__ );
		return Database::affected_rows () == 1;
	}

	function class_name_exists($name) {
		$table_class = Database::get_course_table ( TABLE_TOOL_COURSE_CLASS );
		$sql = "SELECT * FROM $table_class WHERE name='" . escape ( $name ) . "'";
		$sql .= " AND cc='" . api_get_course_code () . "' ";
		return Database::if_row_exists ( $sql, __FILE__, __LINE__ );
	}

	function delete_class($class_id) {
		$table_class = Database::get_course_table ( TABLE_TOOL_COURSE_CLASS );
		$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		
		$sql = "SELECT COUNT(*) FROM " . $table_course_user . " WHERE course_code='" . api_get_course_code () . "' AND class_id='" . escape ( $class_id ) . "'";
		if (Database::get_scalar_value ( $sql ) == 0) { //没有与该班级关联的用户时方可删除
			//可不执行下面这句
			//$sql = "UPDATE " . $table_course_user . " SET class_id=0 WHERE course_code='" . api_get_course_code () . "' AND class_id='" . escape ( $class_id ) . "'";
			//api_sql_query ( $sql, __FILE__, __LINE__ );
			$sql = "DELETE FROM $table_class WHERE id = '" . $class_id . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			return SUCCESS;
		} else {
			return - 1;
		}
	}

	function get_all_classes($course_code = '') {
		if (empty ( $course_code )) $course_code = api_get_course_code ();
		if (empty ( $course_code )) return false;
		$table_class = Database::get_course_table ( TABLE_TOOL_COURSE_CLASS );
		$sql = "SELECT * FROM $table_class WHERE cc='" . escape ( $course_code ) . "' ";
		return api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
	}

	/**
	 * 获取本课程班级内所有用户
	 * @param int $class_id
	 * @return array
	 */
	function get_users($class_id) {
		$table_class = Database::get_course_table ( TABLE_TOOL_COURSE_CLASS );
		$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$table_user = Database::get_main_table ( VIEW_USER_DEPT );
		$sql = "SELECT * FROM $table_class c,$table_course_user cu, $table_user u WHERE cu.class_id = '" . $class_id . "' AND cu.user_id = u.user_id AND cu.class_id=c.id";
		$sql .= " ORDER BY u.username";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		$users = api_store_result ( $res );
		return $users;
	}

	function get_user_with_class($class_id = NULL, $status = NULL) {
		global $_course;
		$table_class = Database::get_course_table ( TABLE_TOOL_COURSE_CLASS );
		$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		$sql = "SELECT cu.course_code,cu.class_id,cu.status,u.user_id,u.username,u.firstname,IFNULL(c.id,0) AS id,IFNULL(c.name,'" . get_lang ( 'NoCategoryClass' ) . "') AS name FROM " . $table_course_user . " cu LEFT JOIN " . $table_class . " c
			ON cu.class_id=c.id LEFT JOIN " . $table_user . " u ON cu.user_id = u.user_id WHERE course_code='" . api_get_course_code () . "'";
		/*$sql="SELECT cu.course_code,cu.class_id,cu.status,u.user_id,u.username,u.firstname,IFNULL(c.id,0) AS id,IFNULL(c.name,'"
		.get_lang('NoCategoryClass')."') AS name FROM ".$table_course_user." cu , ".$table_class." c,".$table_user." u
			WHERE cu.class_id=c.id AND cu.user_id = u.user_id AND course_code='".$_course['sysCode']."'";*/
		if (isset ( $status )) {
			$sql .= " AND cu.status='" . $status . "'";
		}
		if (isset ( $class_id ) && $class_id != 'all') {
			$sql .= " AND cu.class_id = '" . $class_id . "'";
		}
		//echo $sql;
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		$users = array ();
		while ( $user = Database::fetch_array ( $res, 'ASSOC' ) ) {
			$users [$user ['user_id']] = $user;
		}
		return $users;
	}

	function add_user($user_id, $class_id) {
		$table_class = Database::get_course_table ( TABLE_TOOL_COURSE_CLASS );
		$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$sql = "UPDATE $table_course_user SET class_id='" . escape ( $class_id ) . "' WHERE course_code='" . api_get_course_code () . "' AND user_id='" . escape ( $user_id ) . "'";
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}

	function unsubscribe_user($user_id, $class_id) {
		$table_class = Database::get_course_table ( TABLE_TOOL_COURSE_CLASS );
		$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$sql = "UPDATE $table_course_user SET class_id=0 WHERE course_code='" . api_get_course_code () . "' AND user_id='" . escape ( $user_id ) . "'";
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}

}
