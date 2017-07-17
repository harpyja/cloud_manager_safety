<?php
/*
 ==============================================================================

 ==============================================================================
 */

$firstExpirationDelay = 31536000; // 课程默认过期时间 <- 86400*365    // 60*60*24 = 1 jour = 86400


include_once (api_get_path ( LIBRARY_PATH ) . 'add_course.lib.inc.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');

define ( "NOT_VISIBLE_NO_SUBSCRIPTION_ALLOWED", 0 );
define ( "NOT_VISIBLE_SUBSCRIPTION_ALLOWED", 1 );
define ( "VISIBLE_SUBSCRIPTION_ALLOWED", 2 );
define ( "VISIBLE_NO_SUBSCRIPTION_ALLOWED", 3 );

class CourseManager {
	
	var $all_category_tree = array ();
	var $category_path;
	var $sub_category_ids = array ();

	function __construct() {
		$this->all_category_tree = array ();
		$category_path = '';
	}

	/**
	 * 从$course_code得到课程的相关信息
	 * @param string $course_code, the course code
	 * @return an array with all the fields of the course table
	 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
	 */
	function get_course_information($course_code) {
		if (empty ( $course_code )) $course_code = api_get_course_code ();
		if (empty ( $course_code )) return false;
		$course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
		$sql = "SELECT * FROM " . $course_table . " WHERE code='" . escape ( $course_code ) . "'";
		$sql_result = api_sql_query ( $sql, __FILE__, __LINE__ );
		if ($result = Database::fetch_array ( $sql_result, 'ASSOC' )) {return $result;}
		return false;
	}

	/**
	 * 从$course_code得到课程的设置信息:
	 * which visibility;
	 * wether subscribing is allowed;
	 * wether unsubscribing is allowed.
	 *
	 * @param string $course_code, the course code
	 * @todo for more consistency: use course_info call from database API
	 * @return an array with int fields "visibility", "subscribe", "unsubscribe"
	 */
	function get_access_settings($course_code) {
		//$system_code = $course_info ["sysCode"];
		$course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
		$sql = "SELECT `visibility`, `subscribe`, `unsubscribe` from " . $course_table . " where `code` = '" . $course_code . "'";
		$sql_result = api_sql_query ( $sql, __FILE__, __LINE__ );
		$result = Database::fetch_array ( $sql_result, 'ASSOC' );
		return $result;
	}

	/**
	 * Returns the status of a user in a course, which is COURSEMANAGER or STUDENT.
	 *
	 * @return int the status of the user in that course
	 */
	function get_user_in_course_status($user_id, $course_code) {
		$course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$sql_query = "SELECT status FROM $course_user_table WHERE `course_code` = '$course_code' AND `user_id` = '$user_id'";
		return Database::get_scalar_value ( $sql_query );
	}

	/**
	 * Unsubscribe one or more users from a course
	 * @param int|array $user_id
	 * @param string $course_code
	 */
	function unsubscribe_user($user_id, $course_code) {
		if (! is_array ( $user_id )) {
			$user_id = array ($user_id );
		}
		if (count ( $user_id ) > 0) {
			$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
			$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
			
			/*	$sql = "SELECT * FROM $table_course WHERE code = '" . Database::escape_string ( $course_code ) . "'";
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			$course = Database::fetch_object ( $res );*/
			
			$sqlwhere = " cc=" . Database::escape ( $course_code ) . " AND " . Database::create_in ( $user_id, 'user_id' );
			$tbl_lp_view = Database::get_course_table ( TABLE_LP_VIEW );
			$tbl_lp_item_view = Database::get_course_table ( TABLE_LP_ITEM_VIEW );
			$tbl_sms_receivers = Database::get_course_table ( TABLE_TOOL_SMS_RECEIVED );
			$tbl_track_cw = Database::get_main_table ( TABLE_STATISTIC_TRACK_E_CW );
			$tbl_track_exercise = Database::get_main_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
			$tbl_track_attempt = Database::get_main_table ( TABLE_STATISTIC_TRACK_E_ATTEMPT );
			$table_reg_courses_user = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );
			
			$sql = "SELECT id FROM $tbl_lp_view WHERE " . $sqlwhere;
			$lp_view_ids = Database::get_into_array ( $sql, __FILE__, __LINE__ );
			
			$sql = "DELETE FROM $tbl_lp_view WHERE " . $sqlwhere;
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$sql = "DELETE FROM $tbl_lp_item_view WHERE cc=" . Database::escape ( $course_code ) . " AND " . Database::create_in ( $lp_view_ids, 'lp_view_id' );
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$sql = "DELETE FROM $tbl_sms_receivers WHERE " . $sqlwhere;
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$sql = "SELECT exe_id FROM $tbl_track_exercise WHERE exe_cours_id=" . Database::escape ( $course_code ) . " AND " . Database::create_in ( $user_id, 'exe_user_id' );
			$quiz_track_ids = Database::get_into_array ( $sql, __FILE__, __LINE__ );
			
			$sql = "DELETE FROM $tbl_track_exercise WHERE exe_cours_id=" . Database::escape ( $course_code ) . " AND " . Database::create_in ( $user_id, 'exe_user_id' );
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$sql = "DELETE FROM $tbl_track_attempt WHERE " . Database::create_in ( $lp_view_ids, 'exe_id' );
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$sql = "DELETE FROM $tbl_track_cw WHERE " . $sqlwhere;
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$sql = "DELETE FROM $table_course_user WHERE course_code = '" . $course_code . "' AND " . Database::create_in ( $user_id, 'user_id' );
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			self::unsubscribe_openuser ( $user_id, $course_code );
			
			//liyu: 删除课程申请注册的列表信息
			$sql = "DELETE FROM " . $table_reg_courses_user . " WHERE course_code=" . Database::escape ( $course_code ) . " AND " . Database::create_in ( $user_id, 'user_id' );
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			return true;
		}
		return false;
	}

	function subscribe_user2course($user_id, $course_code, $is_required_crs = 0, $begin_date = "0000-00-00", $finish_date = "0000-00-00", $type = 'add') {
		if (strtolower ( $type ) == 'add') {
			return self::subscribe_user ( $user_id, $course_code, STUDENT, $begin_date, $finish_date, $is_required_crs );
		} elseif (strtolower ( $type ) == 'replace') {
			$course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
			$table_reg_courses_user = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );
			$sqlwhere = " `user_id` = " . Database::escape ( $user_id ) . " AND `course_code` =" . Database::escape ( $course_code );
			$sql = "SELECT * FROM " . $course_user_table . "	WHERE " . $sqlwhere;
			$handle = api_sql_query ( $sql, __FILE__, __LINE__ );
			if (Database::num_rows ( $handle ) > 0) { //已注册入课程中
				$sql_data = array ("status" => STUDENT, 'begin_date' => $begin_date, 'finish_date' => $finish_date, 'is_required_course' => $is_required_crs );
				$add_course_user_entry_sql = Database::sql_update ( $course_user_table, $sql_data, $sqlwhere );
				$result = api_sql_query ( $add_course_user_entry_sql, __FILE__, __LINE__ );
				if ($result) {
					//liyu: 更新申请表中的审核信息
					$sql = "update " . $table_reg_courses_user . " set audit_result=1,audit_date=now() where course_code='" . $course_code . "' and user_id='" . $user_id . "' AND audit_result<>1";
					api_sql_query ( $sql, __FILE__, __LINE__ );
					return true;
				}
			} else {
				return self::subscribe_user ( $user_id, $course_code, STUDENT, $begin_date, $finish_date, $is_required_crs );
			}
		}
	}

	/**
	 * 注册用户到课程当中
	 * @param int $user_id
	 * @param string $course_code
	 * @param int $status
	 * @param string $begin_date
	 * @param string $finish_date
	 * @param int $is_required_crs
	 */
	function subscribe_user($user_id, $course_code, $status = STUDENT, $begin_date = "0000-00-00", $finish_date = "0000-00-00", $is_required_crs = 0) {
		if (empty ( $user_id ) || empty ( $course_code )) return false;
		
		$user_table = Database::get_main_table ( TABLE_MAIN_USER );
		$course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
		$course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$table_reg_courses_user = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );
		
		$status = ($status == STUDENT || $status == COURSEMANAGER) ? $status : STUDENT;
		$role_id = ($status == COURSEMANAGER) ? COURSE_ADMIN : NORMAL_COURSE_MEMBER;
		
		// 检查是否注册到本课程
		$sql = "SELECT * FROM " . $course_user_table . "	WHERE `user_id` = " . Database::escape ( $user_id ) . " AND `course_code` =" . Database::escape ( $course_code );
		$handle = api_sql_query ( $sql, __FILE__, __LINE__ );
		if (Database::num_rows ( $handle ) > 0) { //已注册入课程中
			return false;
		} else { //没有注册
			$sql = "SELECT * FROM " . $user_table . "	WHERE `user_id` = " . Database::escape ( $user_id );
			$handle = api_sql_query ( $sql, __FILE__, __LINE__ );
			if (Database::num_rows ( $handle ) > 0) {
				if (empty ( $begin_date ) or $begin_date == "0000-00-00") $begin_date = date ( 'Y-m-d' );
				if (empty ( $finish_date ) or $finish_date == "0000-00-00") $finish_date = date ( 'Y-m-d', strtotime ( "+ " . (DEFAULT_LEARNING_DAYS) . " days" ) );
				$sql_data = array ("course_code" => $course_code, "user_id" => $user_id, "status" => $status, 'begin_date' => $begin_date, 'finish_date' => $finish_date, 'is_required_course' => $is_required_crs );
				$add_course_user_entry_sql = Database::sql_insert ( $course_user_table, $sql_data );
				$result = api_sql_query ( $add_course_user_entry_sql, __FILE__, __LINE__ );
				if ($result) {
					//liyu: 更新申请表中的审核信息
					$sql = "update " . $table_reg_courses_user . " set audit_result=1,audit_date=now() where course_code='" . $course_code . "' and user_id='" . $user_id . "' AND audit_result<>1";
					api_sql_query ( $sql, __FILE__, __LINE__ );
					return true;
				}
			}
		}
		
		return false;
	}

	/**
	 * since ZLMS V1.1 增加参数$class_id
	 * Subscribe a user $user_id to a course $course_code.
	 * @author Hugues Peeters
	 * @author Roan Embrechts
	 *
	 * @param  int $user_id the id of the user
	 * @param  string $course_code the course code
	 * @param string $status (optional) The user's status in the course
	 *
	 * @return boolean true if subscription succeeds, boolean false otherwise.
	 * @todo script has ugly ifelseifelseifelseif structure, improve
	 */
	function add_user_to_course($user_id, $course_code, $status = STUDENT, $class_id = '0') {
		$user_table = Database::get_main_table ( TABLE_MAIN_USER );
		$course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
		$course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		
		$status = ($status == STUDENT || $status == COURSEMANAGER) ? $status : STUDENT;
		if (empty ( $user_id ) || empty ( $course_code )) {
			return false;
		} else {
			// 检查是否注册到本系统平台
			$sql = "SELECT status FROM " . $user_table . "	WHERE `user_id` = " . Database::escape ( $user_id );
			$handle = api_sql_query ( $sql, __FILE__, __LINE__ );
			if (Database::num_rows ( $handle ) == 0) {
				return false; // the user isn't registered to the platform
			} else {
				// 检查是否注册到本课程
				$sql = "SELECT * FROM " . $course_user_table . "	WHERE `user_id` = " . Database::escape ( $user_id ) . " AND `course_code` =" . Database::escape ( $course_code );
				$handle = api_sql_query ( $sql, __FILE__, __LINE__ );
				if (mysql_num_rows ( $handle ) > 0) {
					return false; // the user is already subscribed to the course
				} else {
					// 是否允许注册
					$sql = "SELECT * FROM " . $course_table . " WHERE  `code` = '" . escape ( $course_code ) . "'
					AND  `subscribe` = '" . SUBSCRIBE_NOT_ALLOWED . "'";
					$handle = api_sql_query ( $sql, __FILE__, __LINE__ );
					if (Database::num_rows ( $handle ) > 0) { //不允许时
						return false; // subscription not allowed for this course
					} else {
						$sql = "SELECT default_learing_days FROM $course_table WHERE code='" . escape ( $course_code ) . "'";
						$default_learing_days = Database::get_scalar_value ( $sql );
						if (empty ( $default_learing_days )) $default_learing_days = DEFAULT_LEARNING_DAYS;
						$time = strtotime ( "+ $default_learing_days days" );
						$finish_date = date ( 'Y-m-d H:i:s', $time );
						$sql_data = array ("course_code" => $course_code, "user_id" => $user_id, "status" => $status, "class_id" => $class_id, "begin_date" => date ( 'Y-m-d H:i:s' ), "finish_date" => $finish_date );
						$add_course_user_entry_sql = Database::sql_insert ( $course_user_table, $sql_data );
						$result = api_sql_query ( $add_course_user_entry_sql, __FILE__, __LINE__ );
						return $result ? TRUE : FALSE;
					}
				}
			}
		}
		return false;
	}

	function unsubscribe_openuser($user_id, $course_code) {
		$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		if (! is_array ( $user_id )) $user_id = array ($user_id );
		$sql = "SELECT * FROM $tbl_course_user WHERE course_code = '" . escape ( $course_code ) . "' AND user_id " . Database::create_in ( $user_id );
		if (Database::if_row_exists ( $sql, __FILE__, __LINE__ ) == false) { //已注册到课程的用户为开放授权用户
			$tbl_course_openuser = Database::get_main_table ( TABLE_MAIN_COURSE_OPENSCOPE );
			$sql = "DELETE FROM " . $tbl_course_openuser . " WHERE user_id " . Database::create_in ( $user_id ) . " AND course_code=" . Database::escape ( $course_code );
			$result = api_sql_query ( $sql, __FILE__, __LINE__ );
			return $result ? TRUE : FALSE;
		}
		return false;
	}

	/**
	 * Checks wether a parameter exists.
	 * If it doesn't, the function displays an error message.
	 *
	 * @return true if parameter is set and not empty, false otherwise
	 * @todo move function to better place, main_api ?
	 */
	function check_parameter($parameter, $error_message) {
		if (! isset ( $parameter ) || empty ( $parameter )) {
			Display::display_normal_message ( $error_message );
			return false;
		}
		return true;
	}

	/**
	 * Lets the script die when a parameter check fails.
	 * @todo move function to better place, main_api ?
	 */
	function check_parameter_or_fail($parameter, $error_message) {
		if (! CourseManager::check_parameter ( $parameter, $error_message )) die ();
	}

	/**
	 * @return true if there already are one or more courses
	 * with the same code OR visual_code (visualcode), false otherwise
	 */
	function is_existing_course_code($wanted_course_code) {
		$course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
		$sql_query = "SELECT * FROM " . $course_table . "WHERE `code` = '$wanted_course_code'";
		return Database::is_row_exits ( $sql_query, __FILE__, __LINE__ );
	}

	/**
	 * 返回课程的管理员列表（status=1)
	 * @return an array with the course info of all the courses (real and virtual) of which
	 * the current user is course admin
	 */
	function get_course_list_of_user_as_course_admin($user_id) {
		$course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
		$course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		
		$sql_query = "	SELECT * FROM $course_table course
		LEFT JOIN $course_user_table course_user ON course.`code` = course_user.`course_code`
		WHERE course_user.`user_id` = '$user_id' AND course_user.`is_course_admin` =1 ";
		
		$sql_result = api_sql_query ( $sql_query, __FILE__, __LINE__ );
		while ( $result = Database::fetch_array ( $sql_result ) ) {
			$result_array [] = $result;
		}
		
		return $result_array;
	}

	/**
	 * 获取课程的主讲教师
	 * @param $user_id
	 * @return unknown_type
	 */
	function get_course_tutor_list($course_code) {
		$course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
		$course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		
		$sql_query = "	SELECT * FROM $course_user_table course_user LEFT JOIN $course_table course
		ON course.`code` = course_user.`course_code` WHERE course_user.`tutor_id` = '1'
		AND course_user.`status` = " . COURSEMANAGER . " AND course_user.`course_code`='" . Database::escape_string ( $course_code ) . "'";
		
		$sql_result = api_sql_query ( $sql_query, __FILE__, __LINE__ );
		
		$result_array = api_store_result_array ( $result );
		
		return $result_array;
	}

	/**
	 * 用户是否注册到课程中
	 *
	 * @param $user_id, the id (int) of the user
	 * @param $course_info, array with info about the course (comes from course table)
	 *
	 * @return true if the user is registered in the course, false otherwise
	 */
	function is_user_subscribed_in_course($user_id, $course_code) {
		return self::is_user_subscribe ( $course_code, $user_id );
	}

	function is_allowed_to_unsubscribe($course_code, $user_id) {
		$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$sql = "SELECT * FROM $tbl_course_user AS t1 WHERE t1.user_id='" . escape ( $user_id ) . "' AND t1.`course_code` = " . Database::escape ( $course_code ) . " AND  is_course_admin=1 AND tutor_id=1";
		return (Database::if_row_exists ( $sql ) == false);
	}

	function is_user_subscribe($course_code, $user_id) {
		if (empty ( $course_code ) or empty ( $user_id )) return FALSE;
		if (is_not_blank ( $course_code ) && is_not_blank ( $user_id )) {
			$course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
			$sql = "SELECT * FROM " . $course_user_table . " WHERE course_code='" . escape ( $course_code ) . "' AND user_id='" . escape ( $user_id ) . "'";
			return Database::if_row_exists ( $sql );
		}
		return false;
	}

	function is_user_subscribe_requisition($course_code, $user_id) {
		if (is_not_blank ( $course_code ) && is_not_blank ( $user_id )) {
			$table_course_subscribe_requisition = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );
			$sql = "SELECT audit_result FROM " . $table_course_subscribe_requisition . " WHERE course_code='" . Database::escape_string ( $course_code ) . "' AND user_id='" . Database::escape_string ( $user_id ) . "'";
			$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
			if (Database::num_rows ( $rs ) > 0) {
				return Database::result ( $rs, 0, 0 );
			} else {
				return - 1;
			} //不存在
		}
		return - 2;
	}

	/**
	 * 是否存在课程班级
	 * @param $course_code
	 * @return unknown_type
	 */
	function has_course_class($course_code) {
		if (is_not_blank ( $course_code )) {
			$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
			$sql = "SELECT db_name FROM " . $table_course . " WHERE code='" . Database::escape_string ( $course_code ) . "'";
			$db_name = Database::get_scalar_value ( $sql );
			
			$table_class = Database::get_course_table ( TABLE_TOOL_COURSE_CLASS, $db_name );
			$sql = "SELECT count(*) FROM $table_class ";
			$sql .= " WHERE cc='" . $course_code . "' ";
			
			return Database::get_scalar_value ( $sql );
		}
		return 0;
	}

	function is_course_teacher($user_id, $course_code) {
		$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$sql_query = 'SELECT status FROM ' . $tbl_course_user . ' WHERE course_code="' . $course_code . '" and user_id="' . $user_id . '"';
		$sql_result = api_sql_query ( $sql_query, __FILE__, __LINE__ );
		$status = mysql_result ( $sql_result, 0, 'status' );
		return ($status == COURSEMANAGER);
	}

	function is_course_admin($user_id, $course_code) {
		$course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$sql = "SELECT * FROM $course_user_table AS t1 WHERE t1.user_id='" . escape ( $user_id ) . "' AND t1.`course_code` = " . Database::escape ( $course_code ) . " AND  is_course_admin=1";
		return Database::if_row_exists ( $sql );
	}

	/**
	 * 获取注册到某课程的用户列表
	 * Return user info array of all users registered in the specified real or virtual course
	 * This only returns the users that are registered in this actual course, not linked courses.
	 *
	 * @param string $course_code
	 * @return array with user info
	 */
	function get_user_list_from_course_code($course_code, $sql_where = "") {
		$a_users = array ();
		$table_user = Database::get_main_table ( VIEW_USER_DEPT );
		$view_couse_user = Database::get_main_table ( VIEW_COURSE_USER );
		$sql_query = "SELECT t.*,DATE_FORMAT(creation_time,'%Y-%m-%d') AS reg_date,u.firstname,u.org_name,u.dept_name,u.official_code
		FROM $view_couse_user t LEFT JOIN $table_user u ON t.user_id=u.user_id  WHERE t.course_code = " . Database::escape ( $course_code );
		if ($sql_where) $sql_query .= $sql_where;
		//echo $sql_query;
		$rs = api_sql_query ( $sql_query, __FILE__, __LINE__ );
		while ( $user = Database::fetch_array ( $rs, "ASSOC" ) ) {
			$a_users [$user ['user_id']] = $user;
		}
		return $a_users;
	}

	/**
	 * 获取用户-课程班级
	 *
	 * @param unknown_type $course_code
	 * @since ZLMS V1.1
	 */
	function get_courseclass_rel_user($course_code) {
		$table_class = Database::get_course_table ( TABLE_TOOL_COURSE_CLASS );
		$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		$sql = "SELECT cu.course_code,cu.user_id,c.id,c.name
			FROM $table_course_user AS cu LEFT JOIN $table_class AS c
			ON cu.class_id=c.id WHERE cu.course_code='" . Database::escape_string ( $course_code ) . "'";
		$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		while ( $user = Database::fetch_array ( $rs ) ) {
			$all_users [$user ['user_id']] = $user;
		}
		return $all_users;
	}

	/**
	 * liyu: 获取某课程的用户列表
	 *
	 * @param unknown_type $course_code
	 * @param unknown_type $condition 其它查询条件
	 * @return unknown
	 */
	function get_course_user_list($course_code, $condition = '') {
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		$table_course_user = Database::get_main_table ( VIEW_COURSE_USER );
		$sql = "SELECT  * FROM $table_course_user AS t1  WHERE t1.course_code=" . Database::escape ( $course_code ) . " " . $condition;
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		$user_list = array ();
		while ( $user = Database::fetch_array ( $res ) ) {
			$user_list [$user ['user_id']] = $user;
		}
		return $user_list;
	}

	function get_course_user_ids($course_code, $condition = '') {
		$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$sql = "SELECT  t1.user_id FROM " . $table_course_user . " AS t1 WHERE t1.course_code=" . Database::escape ( $course_code ) . " " . $condition;
		$user_ids = Database::get_into_array ( $sql, __FILE__, __LINE__ );
		return $user_ids;
	}

	function get_course_user_count($course_code, $condition = '') {
		$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$sql = "SELECT  COUNT(t1.user_id) FROM " . $table_course_user . " AS t1 WHERE t1.course_code=" . Database::escape ( $course_code ) . " " . $condition;
		$user_ids = Database::getval ( $sql, __FILE__, __LINE__ );
		return $user_ids;
	}

	/**
	 * Return user info array of all users registered in the specified real or virtual course
	 * This only returns the users that are registered in this actual course, not linked courses.
	 *
	 * @param string $course_code
	 * @return array with user id
	 */
	function get_student_list_from_course_code($course_code, $sql_where = "") {
		if (empty ( $course_code )) $course_code = api_get_course_code ();
		if (empty ( $course_code )) return false;
		$a_students = array ();
		
		$table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$sql_query = "SELECT * FROM $table WHERE `course_code` = '$course_code' AND `status` = " . STUDENT;
		if ($sql_where) $sql_query .= $sql_where;
		$rs = api_sql_query ( $sql_query, __FILE__, __LINE__ );
		while ( $student = Database::fetch_array ( $rs, "ASSOC" ) ) {
			$a_students [$student ['user_id']] = $student;
		}
		
		return $a_students;
	}

	/**
	 * 删除课程
	 * @param unknown_type $code
	 * @param unknown_type $do_course_dir_copy
	 */
	function delete_course($code, $do_course_dir_copy = true) {
		global $_configuration;
		$course = CourseManager::get_course_information ( $code );
		if (! can_do_my_bo ( $course ['created_user'] )) return false;
		$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
		$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		//$table_course_class = Database::get_main_table ( TABLE_MAIN_COURSE_CLASS );
		$tbl_course_openscore = Database::get_main_table ( TABLE_MAIN_COURSE_OPENSCOPE );
		
		$table_stats_attempt = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_ATTEMPT );
		$table_stats_exercises = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
		$table_stats_online = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_ONLINE );
		$table_stats_downloads = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_DOWNLOADS );
		$table_stats_links = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LINKS );
		
		if (empty ( $course )) return FALSE;
		else {
			//CourseManager::create_database_dump ( $code );
			$course_tables = array (
					TABLE_ANNOUNCEMENT, 
						TABLE_TOOL_ATTACHMENT, 
						TABLE_TOOL_COURSE_CLASS, 
						TABLE_COURSE_SETTING, 
						TABLE_COURSEWARE, 
						TABLE_DOCUMENT, 
						TABLE_ITEM_PROPERTY, 
						TABLE_LP_MAIN, 
						TABLE_LP_ITEM, 
						TABLE_LP_ITEM_VIEW, 
						TABLE_LP_VIEW, 
						TABLE_TOOL_SMS, 
						TABLE_TOOL_SMS_RECEIVED, 
						TABLE_ZLMEET_UPLOAD_FILE, 
						TABLE_ZLMEET_UPLOAD_PPT_FILE );
			foreach ( $course_tables as $k ) {
				$table_name = Database::get_course_table ( $k );
				Database::delete_from_course_table ( $table_name, "", $code );
			}
			
			$exam_tables = array (TABLE_QUIZ_TEST, TABLE_QUIZ_TEST_QUESTION, TABLE_MAIN_EXAM_QUESTION, TABLE_MAIN_EXAM_ANSWER );
			foreach ( $exam_tables as $k ) {
				$table_name = Database::get_main_table ( $k );
				Database::delete_from_course_table ( $table_name, "", $code );
			}
		}
		
		//删除目录
		$course_dir = api_get_path ( SYS_COURSE_PATH ) . $code;
		$garbage_dir = api_get_path ( GARBAGE_PATH ) . $code . '_DELETE_' . date ( 'YmdHis' );
		if ($do_course_dir_copy) {
			rename ( $course_dir, $garbage_dir );
		} else {
			remove_dir ( $course_dir );
		}
		
		$sql = "DELETE FROM $table_course_user WHERE course_code='" . $code . "'";
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$sql = "DELETE FROM $tbl_course_openscore WHERE course_code='" . $code . "'";
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		//删除申请课程注册用户的信息
		$table_course_subscription_requisition = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );
		$sql = "DELETE FROM " . $table_course_subscription_requisition . " WHERE course_code='" . escape ( $code ) . "'";
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		//删除教师的开课申请信息
		$table_course_requisition = Database::get_main_table ( TABLE_MAIN_COURSE_REQUISITION );
		$sql = "SELECT apply_id FROM " . $table_course . " WHERE  code='" . escape ( $code ) . "'";
		$apply_id = Database::get_scalar_value ( $sql );
		if ($apply_id) {
			$sql = "DELETE FROM " . $table_course_requisition . " WHERE id='" . $apply_id . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
		}
		
		//删除课程跟踪信息
		$sql_batch [] = "DELETE FROM $table_stats_attempt WHERE course_code = '" . $code . "'";
		$sql_batch [] = "DELETE FROM $table_stats_exercises WHERE exe_cours_id = '" . $code . "'";
		$sql_batch [] = "DELETE FROM $table_stats_online WHERE course = '" . $code . "'";
		$sql_batch [] = "DELETE FROM $table_stats_downloads WHERE down_cours_id = '" . $code . "'";
		$sql_batch [] = "DELETE FROM $table_stats_links WHERE links_cours_id = '" . $code . "'";
		
		foreach ( $sql_batch as $sql ) {
			api_sql_query ( $sql, __FILE__, __LINE__ );
                }
                //课程截屏录屏记录和文件
                $sql_filename="select filename from snapshot where `lesson_id` = '$code'";
                $result= api_sql_query ( $sql_filename, __FILE__, __LINE__ );
                while($vm = Database::fetch_row ( $result)){
                      $filename=$vm[0];
                      exec("sudo rm  ".URL_ROOT."/www".URL_APPEDND."/storage/snapshot/".$filename."*");  
                      }
                $sql_course_snapshot="DELETE FROM `snapshot` WHERE `lesson_id` = ".$code;
                api_sql_query ( $sql_course_snapshot, __FILE__, __LINE__ );
                
                //实验报告
                $sql_course_report="select `user`,`screenshot_file` from `report` where `code`='$code'";
                $report_arr= api_sql_query ( $sql_course_report,__FILE__, __LINE__ );
                while($r= Database::fetch_row ( $report_arr)){
                      $user=$r[0];
                      $filename=$r[1];
                      exec("rm  ".URL_ROOT.'/www/lms/storage/report/'.$user.'/'.$filename);
                }
                $sql = "DELETE FROM `report` WHERE `report`.`code`='" . $code . "'";
                api_sql_query ( $sql, __FILE__, __LINE__ );
                
		$sql = "DELETE FROM $table_course WHERE code='" . escape ( $code ) . "'";
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$log_msg = get_lang ( 'DelCourseInfo' ) . "code=" . $code;
		api_logging ( $log_msg, 'COURSE', 'DelCourseInfo' );
		
		return true;
	}

	/**
	 * 递归得到课程分类列表
	 *
	 * @param unknown_type $parent_id
	 * @param unknown_type $level
	 */
	function get_categories($parent_id = NULL, $level = 0) {
		global $course_categories, $crs_categories;
		$table_course_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );
		
		if (isset ( $parent_id )) {
			$sql = "SELECT * FROM " . $table_course_category . " WHERE parent_id='" . $parent_id . "' ORDER BY tree_pos";
		} else {
			$sql = "SELECT * FROM " . $table_course_category . " WHERE parent_id=0 ORDER BY tree_pos";
		}
		$level ++;
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		$has_children = Database::num_rows ( $res ) > 0;
		while ( $cat = Database::fetch_array ( $res, 'ASSOC' ) ) {
			$crs_categories [$cat ['id']] = $cat;
			$course_categories [$cat ['id']] = str_repeat ( '&nbsp;', 8 * $level ) . $cat ['name'] . ' - ' . $cat ['code'];
			if ($has_children) {
				self::get_categories ( $cat ['id'], $level );
			}
		}
	}

	/**
	 * 备份数据(多数据库时)
	 * Creates a file called mysql_dump.sql in the course folder
	 * @param $course_code The code of the course
	 * @todo Implementation for single database
	 */
	function create_database_dump($course_code) {
		global $_configuration;
		$base_work_dir = api_get_path ( GARBAGE_PATH ) . $course_code . "_DATA";
		if (! file_exists ( $base_work_dir )) mkdir ( $base_work_dir );
		//mysqldump --user=root --password=root --port=8889 zlms2 crs_item_property  --where="cc='DEMOCOURSE'" --complete_insert  --insert-ignore  --compact --add-locks=false --comments=false   --force  --extended-insert --no-create-info >  /tmp/zlms2_tbl.sql
		$sql = "SHOW TABLES LIKE '" . $_configuration ['table_prefix'] . "%'";
		$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		while ( $row = Database::fetch_array ( $rs, "NUM" ) ) {
			$table_name = $row [0];
			if (! in_array ( $table_name, array ("crs_metadata", "crs_view_sms_receivers", "crs_view_sms_receivers_list" ) )) {
				$output_file = $base_work_dir . "/" . $table_name . ".sql";
				$cmd = "mysqldump --user=" . DB_USER . " --password=" . DB_PWD . " --host=" . DB_HOST . " --port=" . DB_PORT . " " . DB_NAME . " " . $table_name . "  --where=\"cc='" . $course_code .
						 "'\" --complete_insert  --insert-ignore  --compact --add-locks=false --comments=false  --force  --extended-insert --no-create-info > " . $output_file;
						exec ( $cmd, $output, $rtn_var );
					}
				}
				
				$other_tables = array (
						"course" => "code", 
							"course_rel_class" => "course_code", 
							"course_rel_package" => "course_code", 
							"course_rel_user" => "course_code", 
							"course_requisition" => "code", 
							"course_subscribe_requisition" => "course_code", 
							"track_e_attempt" => "course_code", 
							"track_e_exercices" => "exe_cours_id", 
							TABLE_MAIN_EXAM_QUESTION => "cc", 
							TABLE_MAIN_EXAM_ANSWER => "cc" );
				foreach ( $other_tables as $table_name => $cc_field_name ) {
					$output_file = $base_work_dir . "/" . $table_name . ".sql";
					$cmd = "mysqldump --user=" . DB_USER . " --password=" . DB_PWD . " --host=" . DB_HOST . " --port=" . DB_PORT . " " . DB_NAME . " " . $table_name . "  --where=\"" . $cc_field_name . "='" . $course_code .
							 "'\" --complete_insert  --insert-ignore  --compact --add-locks=false --comments=false  --force  --extended-insert --no-create-info > " . $output_file;
							exec ( $cmd, $output, $rtn_var );
						}
						
						//TODO: 删除sql文件为空的
						return TRUE;
					
					}

					//------------------------------------- 课程分类管理
					

					function get_all_categories_tree($is_contained_top_category = false) {
						$data = cache ( CACHE_KEY_COURSE_CATEGORIES, '' );
						if ($data == NULL) {
							$table_course_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );
							$sql = "SELECT * FROM " . $table_course_category . " WHERE parent_id=0 ORDER BY tree_pos";
							$res = api_sql_query ( $sql, __FILE__, __LINE__ );
							$has_children = Database::num_rows ( $res ) > 0;
							while ( $cate = Database::fetch_array ( $res, 'ASSOC' ) ) {
								if ($is_contained_top_category) {
									$cate ['level'] = 0;
									$this->all_category_tree [] = $cate;
								}
								if ($has_children) {
									$this->get_category_trees ( $cate ['id'], 0 );
								}
							}
							$data = $this->all_category_tree;
							cache ( CACHE_KEY_COURSE_CATEGORIES, $data );
						}
						
						return $data;
					}
                                        //信安选课中心
                                       function get_all_categories_xa_tree($is_contained_top_category = false) {
						$data = cache ( CACHE_KEY_COURSE_CATEGORIES, '' );
						if ($data == NULL) {
							$table_course_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );
							$sql = "SELECT * FROM " . $table_course_category . " WHERE parent_id=0   and status=0 ORDER BY tree_pos";
							$res = api_sql_query ( $sql, __FILE__, __LINE__ );
							$has_children = Database::num_rows ( $res ) > 0;
							while ( $cate = Database::fetch_array ( $res, 'ASSOC' ) ) {
								if ($is_contained_top_category) {
									$cate ['level'] = 0;
									$this->all_category_tree [] = $cate;
								}
								if ($has_children) {
									$this->get_category_trees ( $cate ['id'], 0 );
								}
							}
							$data = $this->all_category_tree;
							cache ( CACHE_KEY_COURSE_CATEGORIES, $data );
						}
						
						return $data;
		}
                //基础选课中心
                      function get_all_categories_trees($is_contained_top_category = false,$jc_subclass) {  
                                                                        if($jc_subclass){   //不同条件的添加---zd
                                                                               $num=count($jc_subclass);
                                                                               if($num==1){    
                                                                                         $tiaojian="parent_id=".$jc_subclass[0]."  ||  id=".$jc_subclass[0];
                                                                                    }else{       
                                                                                        $tiaojian="parent_id=".$jc_subclass[0]."  ||  id=".$jc_subclass[0];
                                                                                        for($i=1;$i<$num;$i++){ 
                                                                                              $tiaojian.="  ||  parent_id=".$jc_subclass[$i]."  ||  "."id=".$jc_subclass[$i];    
                                                                                              } 
                                                                                    }
                                                                              }
						 $data = cache ( CACHE_KEY_COURSE_CATEGORIES, '' );
						if ($data == NULL) {
							$table_course_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );
							$sql = "SELECT * FROM " . $table_course_category . " WHERE  ".$tiaojian." ORDER BY tree_pos";
//                                                        echo $sql;
							$res = api_sql_query ( $sql, __FILE__, __LINE__ );
							$has_children = Database::num_rows ( $res ) > 0;
							while ( $cate = Database::fetch_array ( $res, 'ASSOC' ) ) {
								if ($is_contained_top_category) {   
									$cate ['level'] = 0;
									$this->all_category_tree [] = $cate;
								}
//								if ($has_children) {     // 问题--->子类重复查出两次
//									$this->get_category_trees ( $cate ['id'], 0 );
//								}
							}
							$data = $this->all_category_tree;
							cache ( CACHE_KEY_COURSE_CATEGORIES, $data );
						}
//						var_dump($data);
						return $data;
					}
       //攻防实训
                                   function get_gf_categories_trees($is_contained_top_category = false,$gf_subclass) {  
                                                                        if($gf_subclass){   //不同条件的添加---zd
                                                                               $num=count($gf_subclass);
                                                                               if($num==1){    
                                                                                         $tiaojian="parent_id=".$gf_subclass[0]."  ||  id=".$gf_subclass[0];
                                                                                    }else{       
                                                                                        $tiaojian="parent_id=".$gf_subclass[0]."  ||  id=".$gf_subclass[0];
                                                                                        for($i=1;$i<$num;$i++){ 
                                                                                              $tiaojian.="  ||  parent_id=".$gf_subclass[$i]."  ||  "."id=".$gf_subclass[$i];    
                                                                                              } 
                                                                                    }
                                                                              }
						 $data = cache ( CACHE_KEY_COURSE_CATEGORIES, '' );
						if ($data == NULL) {
							$table_course_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );
							$sql = "SELECT * FROM " . $table_course_category . " WHERE  ".$tiaojian." ORDER BY tree_pos";
//                                                        echo $sql;
							$res = api_sql_query ( $sql, __FILE__, __LINE__ );
							$has_children = Database::num_rows ( $res ) > 0;
							while ( $cate = Database::fetch_array ( $res, 'ASSOC' ) ) {
								if ($is_contained_top_category) {   
									$cate ['level'] = 0;
									$this->all_category_tree [] = $cate;
								}
//								if ($has_children) {     // 问题--->子类重复查出两次
//									$this->get_category_trees ( $cate ['id'], 0 );
//								}
							}
							$data = $this->all_category_tree;
							cache ( CACHE_KEY_COURSE_CATEGORIES, $data );
						}
//						var_dump($data);
						return $data;
					}
  //协议分析与开发
                                   function get_xy_categories_trees($is_contained_top_category = false,$xy_subclass) {  
                                                                        if($xy_subclass){   //不同条件的添加---zd
                                                                               $num=count($xy_subclass);
                                                                               if($num==1){    
                                                                                         $tiaojian="parent_id=".$xy_subclass[0]."  ||  id=".$xy_subclass[0];
                                                                                    }else{       
                                                                                        $tiaojian="parent_id=".$xy_subclass[0]."  ||  id=".$xy_subclass[0];
                                                                                        for($i=1;$i<$num;$i++){ 
                                                                                              $tiaojian.="  ||  parent_id=".$xy_subclass[$i]."  ||  "."id=".$xy_subclass[$i];    
                                                                                              } 
                                                                                    }
                                                                              }
						 $data = cache ( CACHE_KEY_COURSE_CATEGORIES, '' );
						if ($data == NULL) {
							$table_course_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );
							$sql = "SELECT * FROM " . $table_course_category . " WHERE  ".$tiaojian." ORDER BY tree_pos";
//                                                        echo $sql;
							$res = api_sql_query ( $sql, __FILE__, __LINE__ );
							$has_children = Database::num_rows ( $res ) > 0;
							while ( $cate = Database::fetch_array ( $res, 'ASSOC' ) ) {
								if ($is_contained_top_category) {   
									$cate ['level'] = 0;
									$this->all_category_tree [] = $cate;
								}
//								if ($has_children) {     // 问题--->子类重复查出两次
//									$this->get_category_trees ( $cate ['id'], 0 );
//								}
							}
							$data = $this->all_category_tree;
							cache ( CACHE_KEY_COURSE_CATEGORIES, $data );
						}
//						var_dump($data);
						return $data;
					}
                function get_all_categories_treei($is_contained_top_category = false) {
                    $data = cache ( CACHE_KEY_COURSE_CATEGORIES, '' );
                    if ($data == NULL) {
                        $table_course_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );
                        $sql = "SELECT * FROM " . $table_course_category . " WHERE parent_id!=0 ORDER BY tree_pos";
                        $res = api_sql_query ( $sql, __FILE__, __LINE__ );
                        $has_children = Database::num_rows ( $res ) > 0;
                        while ( $cate = Database::fetch_array ( $res, 'ASSOC' ) ) {
                            if ($is_contained_top_category) {
                                $cate ['level'] = 0;
                                $this->all_category_tree [] = $cate;
                            }
                            if ($has_children) {
                                $this->get_category_trees ( $cate ['id'], 0 );
                            }
                        }
                        $data = $this->all_category_tree;
                        cache ( CACHE_KEY_COURSE_CATEGORIES, $data );
                    }
                    return $data;
                }
					/**
					 * 递归得到课程分类列表
					 *
					 */
					function get_category_trees($parent_id = 0, $level = 0) {
						$table_course_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );
						$sql = "SELECT * FROM " . $table_course_category . " WHERE parent_id='" . $parent_id . "' ORDER BY tree_pos";
						$level ++;
						$res = api_sql_query ( $sql, __FILE__, __LINE__ );
						$has_children = Database::num_rows ( $res ) > 0;
						while ( $cate = Database::fetch_array ( $res, 'ASSOC' ) ) {
							$cate ['level'] = $level;
							$this->all_category_tree [] = $cate;
							if ($has_children) {
								self::get_category_trees ( $cate ['id'], $level );
							}
						}
					}

					function get_sub_category_tree_ids($parent_id = 0, $inc_self = FALSE) {
						$table_course_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );
						$sql = "SELECT * FROM " . $table_course_category . " WHERE parent_id='" . $parent_id . "' ORDER BY tree_pos";
						$res = api_sql_query ( $sql, __FILE__, __LINE__ );
						$has_children = Database::num_rows ( $res ) > 0;
						if ($inc_self) $this->sub_category_ids [] = $parent_id;
						while ( $category = Database::fetch_array ( $res, 'ASSOC' ) ) {
							$this->sub_category_ids [] = ($category ['id']);
							if ($has_children) {
								self::get_sub_category_tree_ids ( $category ['id'] );
							}
						}
						return $this->sub_category_ids;
					}

					function get_category_path($cate_id, $is_contain_top = false) {
						if ($cate_id) {
							$table_course_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );
							$sql = "SELECT name,parent_id FROM " . $table_course_category . " WHERE id='" . Database::escape_string ( $cate_id ) . "'";
							//echo $sql;
							//$rs= api_sql_query ( $sql, __FILE__, __LINE__ );
							$cate_info_arr = api_sql_query_array_assoc ( $sql );
							if ($cate_info_arr && is_array ( $cate_info_arr ) && count ( $cate_info_arr ) > 0) {
								$cate_info = $cate_info_arr [0];
								if ($is_contain_top) {
									$this->category_path .= $cate_info ['name'] . "/";
								}
								if ($cate_info ['parent_id']) {
									if (! $is_contain_top) {
										$this->category_path .= $cate_info ['name'] . "/";
									}
									$this->get_category_path ( $cate_info ['parent_id'], $is_contain_top );
								}
								return $this->category_path;
							}
						}
					}

					//----------------------------------------- 课程分类管理
					

					/**
					 * 课程用户审核通过的处理
					 * @param $course_code
					 * @param $user_id
					 * @return unknown_type
					 */
					function apply_audit_pass($course_code, $user_id) {
						if ($course_code && $user_id) {
							$table_course_subscribe_requisition = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );
							//all_course_information = CourseManager::get_course_information($course_code);
							$sql = "update " . $table_course_subscribe_requisition . " set audit_result=1,audit_date=now() where course_code='" . escape ( $course_code ) . "' and user_id='" . escape ( $user_id ) . "'";
							api_sql_query ( $sql, __FILE__, __LINE__ );
							
							$sql = "SELECT status,class_id FROM " . $table_course_subscribe_requisition . " WHERE course_code='" . escape ( $course_code ) . "' and user_id='" . escape ( $user_id ) . "'";
							$res = api_sql_query ( $sql, __FILE__, __LINE__ );
							list ( $user_status, $class_id ) = Database::fetch_row ( $res );
							
							if (! self::is_user_subscribe ( $course_code, $user_id )) {
								if (self::add_user_to_course ( $user_id, $course_code, $user_status, $class_id )) {
									return 1;
								} else {
									return 0;
								}
							} else {
								return 2;
							}
						}
						return 0;
					}

					function apply_audit_not_pass($course_code, $user_id) {
						$table_course_subscribe_requisition = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );
						$sql = "update " . $table_course_subscribe_requisition . " set audit_result=2,audit_date=now() where course_code='" . escape ( $course_code ) . "' and user_id='" . escape ( $user_id ) . "'";
						return api_sql_query ( $sql, __FILE__, __LINE__ );
					}

					function apply_audit_del($course_code, $user_id) {
						$table_course_subscribe_requisition = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );
						$sql = "DELETE FROM " . $table_course_subscribe_requisition . " WHERE course_code='" . escape ( $course_code ) . "' and user_id='" . escape ( $user_id ) . "'";
						return api_sql_query ( $sql, __FILE__, __LINE__ );
					}

					/**
					 * liyu: 注册用户到某门课程,即插入到表:course_subscribe_requisitation
					 * V1.1 增加参数 $class_id
					 * @param unknown_type $course_code
					 * @param unknown_type $class_id
					 * @return unknown
					 */
					function subscribe_applied_user_to_course($course_code, $user_id, $class_id = '0') {
//                                           // echo  $course_code."qqqq".$user_id;
						global $_user;
						$rtn_message = "";
						$table_course_subscribe_requisition = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );
						$table_user = Database::get_main_table ( TABLE_MAIN_USER );
						$tbl_course_openscope = Database::get_main_table ( TABLE_MAIN_COURSE_OPENSCOPE );
						$course_info = self::get_course_information ( $course_code );
						if (api_get_setting ( 'course_center_open_scope' ) == 2) {
							$sql = "SELECT * FROM $tbl_course_openscope WHERE course_code= " . Database::escape ( $course_code ) . " AND user_id=" . Database::escape ( $user_id );
							if (! Database::if_row_exists ( $sql, __FILE__, __LINE__ )) return 'YouArtNotAllowedToSubTheCourse';
						}
						
						if ($course_info ['subscribe'] == 1) { //允许注册
							//需要审核
							if (strtolower ( $class_id ) == "undefined") $class_id = "0";
							$sql_data = array ("course_code" => $course_code, "user_id" => $user_id, "class_id" => $class_id, "status" => STUDENT );
							$sql_data ["audit_user"] = 0;
							$sql_data ["audit_result"] = 1;
							$sql_data ['audit_date'] = date ( "Y-m-d H:i:s" );
							$sql_data ["creation_date"] = date ( "Y-m-d H:i:s" );
							$sql = Database::sql_insert ( $table_course_subscribe_requisition, $sql_data );
							$result = api_sql_query ( $sql, __FILE__, __LINE__ );
							
							$result = (CourseManager::add_user_to_course ( $user_id, $course_code, $_user ['status'], $class_id ));
							$rtn_message = ($result ? "您已成功注册选修了该课程!" : "操作失败，如有任何疑问，请联系系统管理员！");
						} else {
							$rtn_message = ('对不起,你没有权限选修该课程!');
						}
						
						return $rtn_message;
					}

					public function get_course_tutor($course_code) {
						$user_table = Database::get_main_table ( TABLE_MAIN_USER );
						$course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
						$sql_query = "SELECT t2.user_id,t2.username,t2.firstname FROM $course_user_table AS t1, $user_table AS t2 WHERE t1.user_id=t2.user_id
				AND t1.`course_code` = " . Database::escape ( $course_code ) . " AND t1.status=" . COURSEMANAGER . " AND tutor_id=1";
						return Database::fetch_one_row ( $sql_query, TRUE, __FILE__, __LINE );
					}

					public function get_course_admin($course_code) {
						$user_table = Database::get_main_table ( TABLE_MAIN_USER );
						$course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
						$sql_query = "SELECT t2.user_id,t2.username,t2.firstname,t2.email FROM $course_user_table AS t1, $user_table AS t2
				WHERE t1.user_id=t2.user_id	AND t1.`course_code` = " . Database::escape ( $course_code ) . " AND t1.status=" . COURSEMANAGER . " AND is_course_admin=1";
						return Database::fetch_one_row ( $sql_query, TRUE, __FILE__, __LINE );
					}

					public function get_user_subscribe_courses_code($user_id) {
						$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
						$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
						$sql = "SELECT course_code FROM " . $table_course_user . " AS t1 WHERE t1.user_id='" . escape ( $user_id ) . "'";
						$sql .= " ORDER BY last_access_time DESC";
						return Database::get_into_array ( $sql, __FILE__, __LINE__ );
					}
				
				} //end class CourseManager
