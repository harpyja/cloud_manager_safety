<?php

/**
 ==============================================================================
 * This library provides functions for user management.
 * Include/require it in your code to use its functionality.
 *
 * @package zllms.library
 ==============================================================================
 */
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

class UserManager {

	/**
	 * Creates a new user for the platform
	 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>,
	 * Roan Embrechts <roan_embrechts@yahoo.com>
	 *
	 * @param string $firstName
	 * string $lastName
	 * int    $status
	 * string $email
	 * string $loginName
	 * string $password
	 * string $official_code	(optional)
	 * string $phone		(optional)
	 * string $picture_uri	(optional)
	 * string $auth_source	(optional)
	 *
	 * @return int     new user id - if the new user creation succeeds
	 * boolean false otherwise
	 *
	 * @desc The function tries to retrieve $_user['user_id'] from the global space.
	 * if it exists, $_user['user_id'] is the creator id       If       a problem arises,
	 * it stores the error message in global $api_failureList
	 *
	 * @todo Add the user language to the parameters
	 */
	function create_user($firstName, $lastName, $status, $email, $loginName, $password, $official_code = '', $language = "english", $phone = '', $picture_uri = '', $auth_source = PLATFORM_AUTH_SOURCE, $expiration_date = '0000-00-00 00:00:00', $active = 1, $dept_id = '0', $description = '', $sex = '1', $credential_type = '0', $credential_no = '', $zip_code = '', $address = '', $mobile = '', $qq = '', $msn = '', $introduction = '', $org_id = "1", $reg_ip = 'unknown',$seatnumber='') {
		global $_user;
		if (empty ( $loginName )) return FALSE;
		if (api_get_setting ( 'registration', 'email' ) == 'true' and empty ( $email )) return false;
		
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		$table_user_register = Database::get_main_table ( TABLE_MAIN_USER_REGISTER );
		
		if (LICENSE_USER_COUNT > 0) {
			$sql = "SELECT COUNT(*) FROM " . $table_user . " WHERE active=1";
			$user_count = Database::get_scalar_value ( $sql );
			if ($user_count - 1 > LICENSE_USER_COUNT) return false;
		}
		
		$creator_id = api_get_user_id ();
		
		if (! UserManager::is_username_available ( $loginName )) return api_set_failure ( 'loginName already used' );
		
		if (api_get_setting ( 'registration', 'email' ) == 'true') {
			if (! UserManager::is_email_available ( $email )) return api_set_failure ( 'email already used' );
		}
		
		if (empty ( $org_id )) {
			$deptObj = new DeptManager ();
			$dept_in_org = $deptObj->get_dept_in_org ( $dept_id );
			$dept_org = array_pop ( $dept_in_org );
			$org_id = $dept_org ['id'];
		}
		
		$sql_data = array ('firstname' => $firstName, 
				'lastname' => $lastName, 
				'status' => $status, 
				'username' => $loginName, 
				'password' => $password, 
				'email' => $email, 
				'official_code' => $official_code, 
				'picture_uri' => $picture_uri, 
				'creator_id' => $creator_id, 
				'auth_source' => $auth_source, 
				'phone' => $phone, 
				'language' => $language, 
				'registration_date' => date ( "Y-m-d H:i:s" ), 
				'expiration_date' => $expiration_date, 
				'active' => $active, 
				'dept_id' => $dept_id, 
				'description' => $description, 
				'sex' => $sex, 
				'credential_type' => $credential_type, 
				'credential_no' => $credential_no, 
				'zip_code' => $zip_code, 
				'address' => $address, 
				'mobile' => $mobile, 
				'qq' => $qq, 
				'msn' => $msn, 
				'introduction' => $introduction, 
				'org_id' => $org_id, 
				'reg_ip' => $reg_ip,
                'seatnumber'=>$seatnumber);
		$sql = Database::sql_insert ( $table_user, $sql_data );
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		$new_user_id = Database::get_last_insert_id ();
		if ($result) {
			if (empty ( $new_user_id )) {
				$sql = "SELECT user_id FROM " . $table_user . " WHERE username='" . Database::escape_string ( $loginName ) . "'";
				$new_user_id = Database::get_scalar_value ( $sql );
			}
			//liyu: 更新申请注册用户
			$sql = "update " . $table_user_register . " set reg_status=2 where username 	= '" . Database::escape_string ( $loginName ) . "' AND reg_status<>2";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			return $new_user_id;
		} else {
			return false;
		}
	}

	function add_user($userInfo, $required_fieds = array('username')) {
		if (empty ( $userInfo ['username'] ) && in_array ( 'username', $required_fieds )) return FALSE;
		if (empty ( $userInfo ['email'] ) && in_array ( 'email', $required_fieds )) return FALSE;
		
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		
		if (LICENSE_USER_COUNT > 0) {
			$sql = "SELECT COUNT(*) FROM " . $table_user;
			$user_count = Database::get_scalar_value ( $sql );
			if ($user_count > LICENSE_USER_COUNT) return false;
		}
		
		//用户已存在,返回其user_id
		if (! UserManager::is_username_available ( $userInfo ['username'] )) {
			$sql = "SELECT user_id FROM $table_user WHERE username = " . Database::escape ( $userInfo ['username'] );
			return Database::get_scalar_value ( $sql );
		
		//return false;
		}
		
		if (in_array ( 'email', $required_fieds ) && ! UserManager::is_email_available ( $email )) {
			$sql = "SELECT user_id FROM $table_user WHERE username = " . Database::escape ( $userInfo ['username'] );
			return Database::get_scalar_value ( $sql );
		}
		
		$sql = Database::sql_insert ( $table_user, $userInfo );
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		$new_user_id = Database::get_last_insert_id ();
		if ($result) {
			if (empty ( $new_user_id )) {
				$sql = "SELECT user_id FROM " . $table_user . " WHERE username='" . Database::escape_string ( $loginName ) . "'";
				$new_user_id = Database::get_scalar_value ( $sql );
			}
			
			//liyu: 更新申请注册用户
			$table_user_register = Database::get_main_table ( TABLE_MAIN_USER_REGISTER );
			$sql = "UPDATE " . $table_user_register . " SET reg_status=2 WHERE username = " . Database::escape ( $loginName ) . " AND reg_status<>2";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			api_logging ( get_lang ( 'AddUser' ) . "admin_" . $data ['dept_no'], 'USER', 'AddUser' );
			
			return $new_user_id;
		} else {
			return false;
		}
	}

	/**
	 * Update user information
	 * @param int $user_id
	 * @param string $firstname
	 * @param string $lastname
	 * @param string $username
	 * @param string $password
	 * @param string $auth_source
	 * @param string $email
	 * @param int $status
	 * @param string $official_code
	 * @param string $phone
	 * @param string $picture_uri
	 * @param int $creator_id
	 * @return boolean true if the user information was updated
	 */
	function update_user($user_id, $firstname, $lastname, $username, $password = null, $auth_source = null, $email, $status, $official_code, $phone, $picture_uri, $expiration_date, $active, $creator_id = null, $dept_id = '0', $description = '', $sex = '1', $credential_type = '0', $credential_no = '', $zip_code = '', $address = '', $mobile = '', $qq = '', $msn = '', $introducton = '', $org_id = "1",$seatnumber) {

        global $_configuration;
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		
		$sql_data = array ('firstname' => $firstname, 'lastname' => $lastname, 'username' => $username );
		if (! empty ( $password )) {
			$password = api_get_encrypted_password ( $password, SECURITY_SALT );
			$sql_data ["password"] = $password;
		}
		if (isset ( $auth_source ) && ! empty ( $auth_source )) {
			$sql_data ["auth_source"] = $auth_source;
		}
		if (! empty ( $creator_id )) {
			$sql_user_data ["creator_id"] = $creator_id;
		
		}
		
		$sql_user_data = array ('status' => $status, 
				'email' => $email, 
				'official_code' => $official_code, 
				'phone' => $phone, 
				'picture_uri' => $picture_uri, 
				'active' => $active, 
				'registration_date' => date ( "Y-m-d H:i:s" ), 
				'expiration_date' => $expiration_date, 
				'active' => $active, 
				'dept_id' => $dept_id, 
				'description' => $description, 
				'sex' => $sex, 
				'credential_type' => $credential_type, 
				'credential_no' => $credential_no, 
				'zip_code' => $zip_code, 
				'address' => $address, 
				'mobile' => $mobile, 
				'qq' => $qq, 
				'msn' => $msn, 
				'introduction' => $introducton, 
				'org_id' => $org_id,
                'seatnumber'=>$seatnumber );
		
		$sql_data = array_merge ( $sql_data, $sql_user_data );
		$sql = Database::sql_update ( $table_user, $sql_data, " user_id='$user_id'" );
		
		// echo $sql;exit;
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}

	/**
	 * liyu: 注册用户插入表 user_register
	 *
	 * @param unknown_type $firstName
	 * @param unknown_type $status
	 * @param unknown_type $email
	 * @param unknown_type $loginName
	 * @param unknown_type $password
	 * @param unknown_type $official_code
	 * @param unknown_type $language
	 * @param unknown_type $phone
	 * @param unknown_type $question
	 * @param unknown_type $answer
	 * @param unknown_type $active
	 * @param unknown_type $reg_status
	 * @return unknown
	 */
	function register_user($firstName, $status, $email, $loginName, $password, $official_code = '', $language = "english", $phone = '', $mobile = '', $question, $answer, $description, $reg_status = 0, $qq = '', $msn = '', $dept_id ='0', $sex = '1', $credential_type = '0', $credential_no = '', $zip_code = '', $address = '', $reg_ip = "0.0.0.0") {
		$table_user_register = Database::get_main_table ( TABLE_MAIN_USER_REGISTER );
		
		$password = api_get_encrypted_password ( $password, SECURITY_SALT );
		 
		$sql_data = array ('firstname' => $firstName, 
				'status' => $status, 
				'username' => $loginName, 
				'password' => $password, 
				'email' => $email, 
				'official_code' => $official_code, 
				'phone' => $phone, 
				'mobile' => $mobile, 
				'language' => $language, 
				'registration_date' => date ( "Y-m-d H:i:s" ), 
				'reg_status' => $reg_status, 
				'question' => $question, 
				'answer' => $answer, 
				'description' => $description, 
				'qq' => $qq, 
				'msn' => $msn, 
				'sex' => $sex, 
				'dept_id' => $dept_id, 
				'credential_type' => $credential_type, 
				'credential_no' => $credential_no, 
				'zip_code' => $zip_code, 
				'address' => $address, 
				"reg_ip" => $reg_ip );
		$sql = Database::sql_insert ( $table_user_register, $sql_data );
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		//echo $sql;
		if ($result) {
			return Database::get_last_insert_id ();
		} else {
			return 0;
		}
	}

	/**
	 * 审核注册用户
	 * @param int $status, do we want to lock the user ($status=lock) or unlock it ($status=unlock)
	 * @param int $user_id The user id
	 * @return language variable
	 */
	function lock_unlock_user($status, $user_id) {
		$reg_user_table = Database::get_main_table ( TABLE_MAIN_USER_REGISTER );
		$user_table = Database::get_main_table ( TABLE_MAIN_USER );
		
		if (is_numeric ( $user_id )) {
			$reg_user_info = UserManager::get_reg_user_info_by_id ( $user_id );
			$emailTo = $reg_user_info ['email'];
			if ($status == 'lock') {
				$return_message = get_lang ( 'RegUserApprovalNotPass' );
				$sql = "UPDATE $reg_user_table SET reg_status=" . AUDIT_REGISTER_REFUSE . " WHERE user_id='" . Database::escape_string ( $user_id ) . "'";
				$result = api_sql_query ( $sql, __FILE__, __LINE__ );
				if ($result) {
					$emailBody = get_lang ( 'LetterHeader' ) . $reg_user_info ['firstname'] . ":<br/><br/>" . get_lang ( 'YourRegisterInfo' ) . "<br/><table><tr><td>" . get_lang ( 'UserName' ) . ' :</td><td> ' . $reg_user_info ['username'] . "</td></tr><tr><td>" . get_lang ( 'FirstName' ) .
							 ' : </td><td>' . $reg_user_info ['firstname'] . "</td></tr><tr><td>" . get_lang ( 'Email' ) . ' : </td><td>' . $reg_user_info ['email'] . "</td></tr><tr><td>" . get_lang ( 'UserType' ) . ' : </td><td>' .
							 ($reg_user_info ['status'] == COURSEMANAGER ? get_lang ( "Tutor" ) : get_lang ( "Student" )) . "</td></tr></table>" . get_lang ( "ApprovalNotPassed" ) . "<br/><br/>" . get_lang ( 'LetterThanksWord' );
					
					$emailSubject = get_lang ( 'RegUserApprovalNotPass' );
					api_email_wrapper ( $emailTo, $emailSubject, $emailBody );
				}
			}
			//审核通过
			if ($status == 'unlock') {
				$return_message = get_lang ( 'RegUserApprovalPass' );
				$result = UserManager::audit_reg_user_passed ( $user_id );
				if ($result) {
					$emailBody = get_lang ( 'LetterHeader' ) . $reg_user_info ['firstname'] . ":<br/><br/>" . get_lang ( 'YourRegisterInfo' ) . "<br/><table><tr><td>" . get_lang ( 'UserName' ) . ' :</td><td> ' . $reg_user_info ['username'] . "</td></tr><tr><td>" . get_lang ( 'FirstName' ) .
							 ' : </td><td>' . $reg_user_info ['firstname'] . "</td></tr><tr><td>" . get_lang ( 'Email' ) . ' : </td><td>' . $reg_user_info ['email'] . "</td></tr><tr><td>" . get_lang ( 'UserType' ) . ' : </td><td>' .
							 ($reg_user_info ['status'] == COURSEMANAGER ? get_lang ( "Tutor" ) : get_lang ( "Student" )) . "</td></tr></table>";
					$emailBody .= get_lang ( "ApprovalPassed" ) . "," . get_lang ( 'PlsLoginThrough' ) . get_lang ( 'SystemName' ) . ":<br/><a href='" . api_get_path ( WEB_PATH ) . "' target='_blank'>" . api_get_path ( WEB_PATH ) . "</a><br/><br/>" . get_lang ( 'LetterThanksWord' );
					
					$emailSubject = get_lang ( 'RegUserApprovalPass' );
					
					api_email_wrapper ( $emailTo, $emailSubject, $emailBody );
				}
			}
			
			if ($reg_user_info && is_array ( $reg_user_info )) {
				$emailToName = "";
			}
		}
		
		return $return_message;
	}

	/**
	 * Can user be deleted?
	 * This functions checks if there's a course in which the given user is the
	 * only course administrator. If that is the case, the user can't be
	 * deleted because the course would remain without a course admin.
	 * @param int $user_id The user id
	 * @return boolean true if user can be deleted
	 */
	function can_delete_user($user_id) {
		//系统默认的管理员不能删除
		global $_configuration;
		$user_info = UserManager::get_user_information ( $user_id );
		if (! can_do_my_bo ( $user_info ['creator_id'] )) return false;
		if ($user_info && in_array ( $user_info ['username'], $_configuration ['default_administrator_name'] )) return false;
		
		//机构管理员不能删除		
		/*$sql = "SELECT * FROM sys_dept AS t1 WHERE  org_id>0 AND org_id IS NOT NULL AND pid=1 AND t1.dept_admin=" . Database::escape ( $user_id );
		if (Database::if_row_exists ( $sql )) {
			return false;
		}*/
		
		//保证课程中至少有一位课程管理员
		$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		
		//某用户注册为课程管理员角色的所有课程
		$sql = "SELECT * FROM $table_course_user WHERE is_course_admin =1 AND user_id = " . Database::escape ( $user_id );
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		while ( $course = Database::fetch_array ( $res, "ASSOC" ) ) {
			$sql = "SELECT user_id FROM $table_course_user WHERE is_course_admin=1 AND course_code ='" . $course ["course_code"] . "'";
			$res2 = api_sql_query ( $sql, __FILE__, __LINE__ );
			if (Database::num_rows ( $res2 ) == 1) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Delete a user from the platform
	 * @param int $user_id The user id
	 * @return boolean true if user is succesfully deleted, false otherwise
	 */
	function delete_user($user_id) {
		global $_configuration;
		$user_id = Database::escape_string ( $user_id );
		if (! UserManager::can_delete_user ( $user_id )) {
			return false;
		}
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
		$table_exam_user = Database::get_main_table ( TABLE_MAIN_EXAM_REL_USER );
		
		// 删除用户注册的所有课程 course->user
		$sql = "DELETE FROM $table_course_user WHERE user_id = '" . $user_id . "'";
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		//删除用户图片文件
		$user_info = api_get_user_info ( $user_id );
		if (strlen ( $user_info ['picture_uri'] ) > 0) {
			$img_path = api_get_path ( SYS_PATH ) . 'storage/users_picture/' . $user_info ['picture_uri'];
			unlink ( $img_path );
		}
		
		//删除user表中记录
		$sql = "DELETE FROM $table_user WHERE user_id = '" . $user_id . "'";
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		//liyu:删除注册用户表记录
		$reg_user_table = Database::get_main_table ( TABLE_MAIN_USER_REGISTER );
		$sql = "DELETE FROM " . $reg_user_table . " WHERE ref_user_id='" . $user_id . "'";
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		//liyu: 删除注册到相关课程的用户
		$table_reg_courses_user = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );
		$sql = "DELETE FROM " . $table_reg_courses_user . " WHERE user_id='" . $user_id . "'";
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$sql = "DELETE FROM " . $table_exam_user . " WHERE user_id='" . $user_id . "'";
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		//liyu: 删除用户授权信息
		$tbl_user_role = Database::get_main_table ( TABLE_MAIN_USER_ROLE );
		$sql = "DELETE FROM " . $tbl_user_role . " WHERE user_id='" . $user_id . "'";
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$tbl_course_openscope = Database::get_main_table ( TABLE_MAIN_COURSE_OPENSCOPE );
		$sql = "DELETE FROM " . $tbl_course_openscope . " WHERE user_id='" . $user_id . "'";
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		//TODO:liyu: 删除用户相关上传文件
		

		//TODO:liyu: 删除跟踪信息
		if ($_configuration ['remove_user_also_delete_track']) {
			$tbl_track_login = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LOGIN );
			$sql = "DELETE FROM " . $tbl_track_login . " WHERE login_user_id='" . $user_id . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$tbl_track_downloads = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_DOWNLOADS );
			$sql = "DELETE FROM " . $tbl_track_downloads . " WHERE down_user_id='" . $user_id . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$tbl_track_exercices = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
			$sql = "DELETE FROM " . $tbl_track_exercices . " WHERE exe_user_id='" . $user_id . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$tbl_track_attempt = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_ATTEMPT );
			$sql = "DELETE FROM " . $tbl_track_attempt . " WHERE user_id='" . $user_id . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$tbl_track_links = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LINKS );
			$sql = "DELETE FROM " . $tbl_track_links . " WHERE links_user_id='" . $user_id . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$tbl_track_online = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_ONLINE );
			$sql = "DELETE FROM " . $tbl_track_online . " WHERE login_user_id='" . $user_id . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$tbl_track_cw = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_CW );
			$sql = "DELETE FROM " . $tbl_track_cw . " WHERE user_id='" . $user_id . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
		
		}
		
		return true;
	}

	/**
	 * 删除注册用户
	 *
	 * @param unknown_type $user_id
	 * @return unknown
	 */
	function delete_user_register($user_id) {
		$tbl_user_reg = Database::get_main_table ( TABLE_MAIN_USER_REGISTER );
		$user_id = Database::escape_string ( $user_id );
		$sql = "SELECT reg_status FROM " . $tbl_user_reg . " WHERE user_id='" . $user_id . "'";
		$data = Database::get_scalar_value ( $sql );
		//if ($data == 2) { //审核通过不允许删除
			//return false;
		//} else {
			$sql = "DELETE FROM $tbl_user_reg WHERE user_id = '" . $user_id . "'";
			return api_sql_query ( $sql, __FILE__, __LINE__ );
		//}
	}

	/**
	 * liyu审核注册用户通过
	 *
	 * @param unknown_type $user_id
	 */
	function audit_reg_user_passed($user_id) {
		$reg_user_table = Database::get_main_table ( TABLE_MAIN_USER_REGISTER );
		$user_table = Database::get_main_table ( TABLE_MAIN_USER );
		
		$user = UserManager::get_reg_user_info_by_id ( $user_id );
		if ($user && $user ['reg_status'] != AUDIT_REGISTER_PASS) {
			$insert_user_id = self::create_user ( $user ['firstname'], '', $user ['status'], $user ['email'], $user ['username'], $user ['password'], $user ['official_code'], $user ['language'], $user ['phone'], '', PLATFORM_AUTH_SOURCE, '0000-00-00 00:00:00', 1, $user ['dept_id'], 
					$user ['description'], $user ['sex'], $user ['credential_type'], $user ['credential_no'], $user ['zip_code'], $user ['address'], $user ['mobile'], $user ['qq'], $user ['msn'], '', '1', $user ['reg_ip'] );
			
			$sql_data = array ("reg_status" => AUDIT_REGISTER_PASS, "ref_user_id" => $insert_user_id );
			$sql = Database::sql_update ( $reg_user_table, $sql_data, " user_id='" . escape ( $user_id ) . "'" );
			$result = api_sql_query ( $sql, __FILE__, __LINE__ );
			
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if a username is available
	 * @param string the wanted username
	 * @return boolean true if the wanted username is available
	 */
	function is_username_available($username) {
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		$sql = "SELECT username FROM $table_user WHERE username = '" . addslashes ( $username ) . "'";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		return mysql_num_rows ( $res ) == 0;
	}

	function is_email_available($email) {
		if (empty ( $email )) return false;
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		$sql = "SELECT user_id FROM $table_user WHERE email = '" . escape ( $email ) . "'";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		return Database::num_rows ( $res ) == 0;
	}

	/**
	 * @return an array with all users of the platform.
	 * @todo optional course code parameter, optional sorting parameters...
	 * @deprecated This function isn't used anywhere in the code.
	 */
	function get_user_list($condition = '') {
		$user_table = Database::get_main_table ( TABLE_MAIN_USER );
		$sql_query = "SELECT * FROM $user_table " . $condition;
		$sql_result = api_sql_query ( $sql_query, __FILE__, __LINE__ );
		return api_store_result_array ( $sql_result );
	}

	/**
	 * Get user information
	 * @param string $username The username
	 * @return array All user information as an associative array
	 */
	function get_user_info($username) {
		if (empty ( $username )) return false;
		$user_table = Database::get_main_table ( TABLE_MAIN_USER );
		$sql = "SELECT * FROM $user_table WHERE username='" . escape ( $username ) . "'";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		$user = Database::fetch_array ( $res, 'ASSOC' );
		return $user;
	}

	function get_user_information($user_id) {
		if (empty ( $user_id )) $user_id = api_get_user_id ();
		if (empty ( $user_id )) return false;
		$user_table = Database::get_main_table ( TABLE_MAIN_USER ); //
		$sql = "SELECT * FROM $user_table WHERE user_id=" . Database::escape ( $user_id );
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		return Database::fetch_array ( $res, 'ASSOC' );
	}

	/**
	 * Get user information
	 * @param string $id The id
	 * @return array All user information as an associative array
	 */
	public static function get_user_info_by_id($user_id, $fetch_other_info = false) {
		if (empty ( $user_id )) $user_id = api_get_user_id ();
		if (empty ( $user_id )) return false;
		$user_table = Database::get_main_table ( VIEW_USER_DEPT ); //TABLE_MAIN_USER
		$sql = "SELECT * FROM $user_table WHERE user_id=" . Database::escape ( $user_id );
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		$user = Database::fetch_array ( $res, 'ASSOC' );
		if ($user) {
			if ($fetch_other_info) {
				require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
				$objDept = new DeptManager ();
				$objDept->dept_path = "";
				$dept_path = $objDept->get_dept_path ( $user ["dept_id"], TRUE );
				$dept_path = rtrim ( $dept_path, "/" );
				$user ["dept_path"] = api_substr ( $dept_path, 0, api_strrpos ( $dept_path, "/" ) );
			}
			return $user;
		}
		return false;
	}

	/**
	 * liyu: 获取注册用户的信息
	 * @param string $id The id
	 * @return array All user information as an associative array
	 */
	function get_reg_user_info_by_id($user_id) {
		$user_id = intval ( $user_id );
		$user_table = Database::get_main_table ( TABLE_MAIN_USER_REGISTER );
		$sql = "SELECT * FROM $user_table WHERE user_id=" . $user_id;
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		$user = Database::fetch_array ( $res, 'ASSOC' );
		return $user;
	}

	//for survey
	function get_teacher_list($course_id, $sel_teacher = '') {
		$user_course_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$user_table = Database::get_main_table ( TABLE_MAIN_USER );
		$sql_query = "SELECT * FROM $user_table a, $user_course_table b where a.user_id=b.user_id AND b.status=1 AND b.course_code='$course_id'";
		$sql_result = api_sql_query ( $sql_query, __FILE__, __LINE__ );
		echo "<select name=\"author\">";
		while ( $result = mysql_fetch_array ( $sql_result ) ) {
			if ($sel_teacher == $result [user_id]) $selected = "selected";
			echo "\n<option value=\"" . $result [user_id] . "\" $selected>" . $result [firstname] . "</option>";
		}
		echo "</select>";
	}

	/**
	 * liyu: 获取某个用户注册到的所有课程列表信息
	 *
	 * @param unknown_type $user_id
	 * @return unknown
	 */
	function get_all_courses_of_one_user_subscribed($user_id = NULL) {
		if (empty ( $user_id )) return FALSE;
		
		$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
		$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$sql = "SELECT t2.code,t2.db_name,t2.title,t2.tutor_name FROM " . $table_course_user . " AS t1 LEFT JOIN " . $table_course . " AS t2 ON t1.course_code=t2.code WHERE t1.user_id=" . Database::escape ( $user_id ) . " ORDER BY t1.creation_time DESC";
		//echo $sql;
		return api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
	}

	/**
	 * liyu:判断是否可编辑课程信息(包括课程管理员及平台管理员)
	 *
	 * @param unknown_type $user_id
	 * @param unknown_type $db_name
	 * @param unknown_type $tutor
	 */
	function is_course_admin($user_id, $cidReq = '', $tutor = true) {
		$is_platformAdmin = false;
		$is_courseAdmin = false;
		$user_table = Database::get_main_table ( TABLE_MAIN_USER );
		$sql = "SELECT * FROM $user_table WHERE user_id='" . escape ( $user_id ) . "' AND is_admin=1";
		if (Database::if_row_exists ( $sql )) {
			$is_platformAdmin = true;
		}
		
		$course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$sql = "SELECT * FROM $course_user_table WHERE `user_id`  = '" . escape ( $user_id ) . "'  AND `course_code` = '$cidReq'";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		//表course_rel_user中有记录时
		if (Database::num_rows ( $result ) > 0) {
			$cuData = Database::fetch_array ( $result, "ASSOC" );
			$is_courseMember = true;
			
			//主讲教师
			$is_courseTutor = ( bool ) ($cuData ['status'] == COURSEMANAGER && $cuData ['tutor_id'] == 1 && $cuData ['is_course_admin'] == 0);
			
			//课程管理员
			$is_courseAdmin = ( bool ) ($cuData ['status'] == COURSEMANAGER && $cuData ['is_course_admin'] == 1); //liyu
		} else // this user has no status related to this course
{
			$is_courseMember = false;
			$is_courseAdmin = false;
			$is_courseTutor = false;
		}
		
		$is_courseAdmin = ( bool ) ($is_courseAdmin || $is_platformAdmin);
		if (! $is_courseAdmin && $tutor == true) {
			$is_courseAdmin = $is_courseAdmin || $is_courseTutor;
		}
		return $is_courseAdmin;
	}

	function reset_password($user_id, $password = '123456') {
		global $_configuration;
		if (empty ( $user_id ) or empty ( $password )) return FALSE;
		
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		$sql = "SELECt username FROM $table_user WHERE user_id=" . Database::escape ( $user_id );
		$username = Database::get_scalar_value ( $sql );
		if ($username == $_configuration ['default_administrator_name']) {
			return false;
		}
		
		$password = api_get_encrypted_password ( $password, SECURITY_SALT );
		$sql = "UPDATE $table_user SET password='" . Database::escape_string ( $password ) . "'";
		$sql .= " WHERE user_id=" . Database::escape ( $user_id );
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}

	function get_user_dept_admin($user_id) {
		if (empty ( $user_id )) return "";
		$view_user_dept = Database::get_main_table ( VIEW_USER_DEPT );
		$sql = "SELECT dept_admin,org_admin FROM " . $view_user_dept . " WHERE user_id='" . escape ( $user_id ) . "'";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		list ( $dept_admin, $org_admin ) = Database::fetch_row ( $res );
		if (empty ( $dept_admin )) {
			return $org_admin;
		} else {
			return $dept_admin;
		}
	}
}

?>
