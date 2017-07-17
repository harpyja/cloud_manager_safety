<?php
include_once (dirname ( __FILE__ ) . '/constants.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'multibyte_string_functions.lib.php');
require_once (dirname ( __FILE__ ) . '/internationalization.lib.php');
include_once api_get_path ( LIBRARY_PATH ) . 'encrypt.class.php';

/*
 ==============================================================================
 PROTECTION FUNCTIONS
 use these to protect your scripts
 ==============================================================================
 */

/**
 * 保护课程访问权限
 * Function used to protect a course script.
 * The function blocks access when
 * - there is no $_SESSION["_course"] defined; or
 * - $is_allowed_in_course is set to false (this depends on the course
 * visibility and user status).
 *
 * This is only the first proposal, test and improve!
 *
 * @todo replace global variable
 * @author Zhong
 */
function api_protect_course_script() {
	if (api_is_platform_admin ()) return true;
	global $is_allowed_in_course;
	if (! isset ( $_SESSION ["_course"] ) || ! $is_allowed_in_course) {
		Display::display_header ( NULL, FALSE );
		api_not_allowed ();
		return false;
	} else {
		return true;
	}
}

/**
 * 保护平台管理员权限
 * Function used to protect an admin script.
 * The function blocks access when the user has no platform admin rights.
 * This is only the first proposal, test and improve!
 *
 * @author Zhong
 */
function api_protect_admin_script() {
	if (! api_is_platform_admin ()) {
		Display::display_header ( NULL, FALSE );
		api_not_allowed ();
		return false;
	} else {
		return true;
	}
}

/**
 * 防止未登录的匿名用户访问
 * Function used to prevent anonymous users from accessing a script.
 *
 * @author Zhong
 */
function api_block_anonymous_users() {
	global $_user;
	if ((isset ( $_user ['user_id'] ) && $_user ['user_id']) == false) {
		Display::display_header ( NULL, FALSE );
		api_not_allowed ();
		return false;
	} else {
		return true;
	}
}

/**
 * 保护课程管理员权限
 * Function used to protect an course admin script.
 * The function blocks access when the user has no course admin rights.
 * This is only the first proposal, test and improve!
 *
 * @author Zhong
 */
function api_protect_course_admin_script() {
	if (! api_is_allowed_to_edit ()) {
		Display::display_header ( NULL, FALSE );
		api_not_allowed ();
	}
}

/*
 ==============================================================================
 ACCESSOR FUNCTIONS
 don't access kernel variables directly,
 use these functions instead
 ==============================================================================
 */
/**
 * 访问的客户端工具
 * liyu: 增加Chrome,FireFox
 * @return an array with the navigator name and version
 *
 */
function api_get_navigator() {
	$navigator = 'Unknown';
	$version = 0;
	$user_agent = $_SERVER ['HTTP_USER_AGENT'];
	if (strstr ( $user_agent, 'MSIE' )) {
		$navigator = 'IE';
		list ( , $version ) = explode ( 'MSIE', $user_agent );
	} elseif (strstr ( $user_agent, 'Firefox' )) {
		$navigator = 'Firefox';
		list ( , $version ) = explode ( 'Firefox/', $user_agent );
	} elseif (strstr ( $user_agent, 'Chrome' )) {
		$navigator = 'Chrome';
		list ( , $version ) = explode ( 'Chrome/', $user_agent );
	} elseif (strstr ( $user_agent, 'Opera' )) {
		$navigator = 'Opera';
		list ( , $version ) = explode ( 'Version/', $user_agent );
	} elseif (strstr ( $user_agent, 'Safari' ) && strstr ( $user_agent, 'Version' )) {
		$navigator = 'Safari';
		list ( , $version ) = explode ( 'Version/', $user_agent );
	} elseif (stripos ( $user_agent, 'Maxthon' ) !== false) {
		$navigator = 'Maxthon';
		list ( , $version ) = explode ( 'Maxthon/', $user_agent );
	} elseif (stripos ( $user_agent, 'applewebkit' ) !== false) {
		$navigator = 'AppleWebKit';
		list ( , $version ) = explode ( 'Version/', $user_agent );
	} elseif (strstr ( $user_agent, 'Gecko' )) {
		$navigator = 'Gecko';
		list ( , $version ) = explode ( '; rv:', $user_agent );
	} elseif (strpos ( $user_agent, 'Konqueror' ) !== false) {
		$navigator = 'Konqueror';
		list ( , $version ) = explode ( 'Konqueror', $user_agent );
	}
	$version = doubleval ( $version );
	if (! strstr ( $version, '.' )) $version = number_format ( doubleval ( $version ), 1 );
	return array ('name' => $navigator, 'version' => $version );
}

/**
 * 是否允许注册
 * @return True if user selfregistration is allowed, false otherwise.
 */
function api_is_self_registration_allowed() {
	if (isset ( $GLOBALS ['allowSelfReg'] )) {
		return $GLOBALS ["allowSelfReg"];
	} else {
		return false;
	}
}

/**
 * Returns a full path to a certain ZLMS area, which you specify
 * through a parameter.
 *
 * See $_configuration['course_folder'] in the configuration.php
 * to alter the WEB_COURSE_PATH and SYS_COURSE_PATH parameters.
 *
 * @param one of the following constants:
 *
 * @example assume that your server root is /var/www/ ZLMS is installed in a subfolder ZLMS/ and the URL of your campus is http://www.myZLMS.com
 * The other configuration paramaters have not been changed.
 * The different api_get_paths will give
 * WEB_PATH			http://www.myZLMS.com
 * SYS_PATH			/var/www/
 * REL_PATH			ZLMS/
 * WEB_COURSE_PATH		http://www.zlms.org/storage/courses/
 * SYS_COURSE_PATH		/var/www/zllms/storage/courses/
 * REL_COURSE_PATH
 * WEB_CODE_PATH
 * SYS_CODE_PATH
 * SYS_LANG_PATH
 * WEB_IMG_PATH
 * GARBAGE_PATH
 * PLUGIN_PATH
 * SYS_ARCHIVE_PATH
 * INCLUDE_PATH
 * LIBRARY_PATH
 * CONFIGURATION_PATH
 */
function api_get_path($path_type) {
	global $_configuration;
	$root_web = $_configuration ['root_web'];
	switch ($path_type) {
		case SYS_SERVER_ROOT_PATH :
			$result = preg_replace ( '@' . api_get_path ( REL_PATH ) . '$@', '', api_get_path ( SYS_PATH ) );
			if (substr ( $result, - 1 ) == '/') {
				return $result;
			} else {
				return $result . '/';
			}
			break;
		
		case WEB_PATH : // example: http://www.zlms.org/zlms/
			if (substr ( $root_web, - 1 ) == '/') {
				return $root_web;
			} else {
				return $root_web . '/';
			}
			break;
		
		case SYS_PATH :
			// example: D:/ZLMS/htdocs/zlms
			if (substr ( $_configuration ['root_sys'], - 1 ) == '/') {
				return $_configuration ['root_sys'];
			} else {
				return $_configuration ['root_sys'] . '/';
			}
			break;
		
		case REL_PATH :
			// example: /zlms/
			if (substr ( $_configuration ['url_append'], - 1 ) === '/') {
				return $_configuration ['url_append'];
			} else {
				return $_configuration ['url_append'] . '/';
			}
			break;
		
		case WEB_COURSE_PATH :
			// example: http://www.zlms.org/storage/courses/
			return $root_web . $_configuration ['course_folder'];
			break;
		
		case SYS_COURSE_PATH :
			// example: D:/LAMP/xampp/htdocs/zlms/storage/courses/
			return $_configuration ['root_sys'] . $_configuration ['course_folder'];
			break;
		
		case REL_COURSE_PATH : // example: /zlms/storage/courses/
			return api_get_path ( REL_PATH ) . $_configuration ['course_folder'];
			break;
		
		case REL_CODE_PATH : // example: /zlms/main/
			return api_get_path ( REL_PATH ) . $_configuration ['code_append'];
			break;
		
		case WEB_CODE_PATH : // example: http://www.zlms.org/zlms/main/
			return $root_web . $_configuration ['code_append'];
			break;
		
		case WEB_ADMIN_PATH :
			return $root_web . $_configuration ['admin_append'];
			break;
		
		case SYS_CODE_PATH : // example: D:/LAMP/xampp/htdocs/zlms/main/
			return SYS_ROOT . $_configuration ['code_append'];
			break;
		
		case SYS_LANG_PATH : // example: D:/LAMP/xampp/htdocs/zlms/lang/
			return api_get_path ( SYS_PATH ) . 'lang/';
			break;
		
		case WEB_IMG_PATH : // example: /zlms/themes/default/images/
			return api_get_path ( REL_PATH ) . 'themes/img/';
			//return api_get_path(REL_PATH) . 'themes/'.api_get_setting('stylesheets').'/images/';
			break;
		
		case SYS_IMG_PATH : // example: /zlms/themes/default/images/
			return api_get_path ( SYS_PATH ) . 'themes/img/';
			//return api_get_path(REL_PATH) . 'themes/'.api_get_setting('stylesheets').'/images/';
			break;
		
		case WEB_IMAGE_PATH : // example: D:/LAMP/xampp/htdocs/zlms/themes/img/
			return api_get_path ( WEB_PATH ) . 'themes/img/';
			//return api_get_path(REL_PATH) . 'themes/'.api_get_setting('stylesheets').'/images/';
			break;
		
		case WEB_COMM_IMG_PATH : // example: /zlms/themes/images/
			return api_get_path ( REL_PATH ) . 'themes/images/';
			break;
		
		case GARBAGE_PATH : // example: D:/LAMP/xampp/htdocs/zlms/storage/garbage/
			return SYS_GARBAGE_DIR;
			break;
		
		case SYS_ARCHIVE_PATH : // example: D:/LAMP/xampp/htdocs/zlms/storage/archive/
			return api_get_path ( SYS_PATH ) . $_configuration ["archive_dir_name"];
			break;
		
		case INCLUDE_PATH : // example:
			//return str_replace('\\', '/', $GLOBALS['includePath']).'/';
			$incpath = realpath ( dirname ( __FILE__ ) . '/../' ); //liyu: 20091125
			return str_replace ( '\\', '/', $incpath ) . '/';
			break;
		
		case LIBRARY_PATH : // example:
			return api_get_path ( INCLUDE_PATH ) . 'lib/';
			break;
		
		case LIB_PATH : // example:
			return SYS_ROOT . 'lib/';
			break;
		
		case WEB_LIBRARY_PATH : // example:
			return api_get_path ( WEB_CODE_PATH ) . 'inc/lib/';
			break;
		
		case WEB_LIB_PATH :
			return api_get_path ( WEB_PATH ) . 'lib/';
			break;
		
		case CONFIGURATION_PATH : // example:
			return api_get_path ( INCLUDE_PATH ) . 'conf/';
			break;
		
		case WEB_CSS_PATH : // example: /zlms/themes/default/
			return api_get_path ( REL_PATH ) . 'themes/default/';
			break;
		
		case SYS_FTP_ROOT_PATH :
			return api_get_path ( SYS_PATH ) . $_configuration ['ftp_root_folder'];
			break;
		
		case SYS_ATTACHMENT_PATH :
			return api_get_path ( SYS_PATH ) . $_configuration ['attachment_folder'];
			break;
		
		case SYS_EXTENSIONS_PATH : // example: D:/LAMP/xampp/htdocs/zlms/extensions/
			return api_get_path ( SYS_PATH ) . 'extensions/';
			break;
		case WEB_EXTENSIONS_PATH : // example: D:/LAMP/xampp/htdocs/zlms/extensions/
			return api_get_path ( WEB_PATH ) . 'extensions/';
			break;
		
		case WEB_JS_PATH :
			return api_get_path ( WEB_PATH ) . 'themes/js/';
			break;
		
		case SYS_DATA_PATH :
			return api_get_path ( SYS_PATH ) . "storage/";
			break;
		
		case WEB_SCORM_PATH :
			return api_get_path ( WEB_CODE_PATH ) . 'scorm/';
			break;
		
		case SYS_SCORM_PATH :
			return api_get_path ( SYS_CODE_PATH ) . 'scorm/';
			break;
		
		case WEB_PORTAL_PATH :
			return api_get_path ( WEB_PATH ) . 'portal/sp/';
			break;
		
		default :
			return;
			break;
	}
}

/**
 * 登录用户的ID
 * This function returns the id of the user which is stored in the $_user array.
 *
 * @example The function can be used to check if a user is logged in
 * if (api_get_user_id())
 * @return integer the id of the current user
 */
function api_get_user_id() {
	if (empty ( $GLOBALS ['_user'] ['user_id'] )) return 0;
	
	return $GLOBALS ['_user'] ['user_id'];
}

function api_get_user_name() {
	return $GLOBALS ['_user'] ['username'];
}

function api_get_user_firstname() {
	return $GLOBALS ['_user'] ['firstName'];
}

/**
 * Get the list of courses a specific user is subscribed to
 * @param	int		User ID
 * @param	boolean	Whether to get session courses or not - NOT YET IMPLEMENTED
 * @return	array	Array of courses in the form [0]=>('code'=>xxx,'db'=>xxx,'dir'=>xxx,'status'=>d)
 * @since 1.8.6
 */
function api_get_user_courses($userid) {
	if ($userid != strval ( intval ( $userid ) )) return array ();
	
	$t_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	$t_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	$sql_select_courses = "SELECT cc.code code, cc.db_name db, cc.directory dir, cu.status status
					FROM    $t_course       cc, $t_course_user   cu WHERE cc.code = cu.course_code
					AND   cu.user_id = '" . $userid . "'";
	//echo $sql;
	$courses = array ();
	$result = api_sql_query ( $sql_select_courses, __FILE__, __LINE__ );
	if ($result === false) return array ();
	while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
		$courses [$row ['code']] = $row;
	}
	return $courses;
}

/**
 * 得到登录用户或指定用户的信息
 * @param $user_id (integer): the id of the user
 * @return $user_info (array): user_id, lastname, firstname, username, email, ...
 * @author Patrick Cool <patrick.cool@UGent.be>
 * @version 21 September 2004
 * @desc find all the information about a user. If no paramater is passed you find all the information about the current user.
 */
function api_get_user_info($user_id = '') {
	if ($user_id) {
		$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
		$sql = "SELECT * FROM " . $tbl_user . " WHERE user_id='" . escape ( $user_id ) . "'";
		//echo $sql;
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		if (Database::num_rows ( $result ) > 0) {
			$result_array = Database::fetch_array ( $result, 'ASSOC' );
			$user_info = $result_array;
			$user_info ['firstName'] = $result_array ['firstname'];
			$user_info ['lastName'] = $result_array ['lastname'];
			$user_info ['mail'] = $result_array ['email'];
			$user_info ['en_name'] = $result_array ['en_name'];
			
			switch ($result_array ['credential_type']) {
				case 1 :
					$user_info ['credential_type'] = get_lang ( 'IDCard' );
					break;
				case 2 :
					$user_info ['credential_type'] = get_lang ( 'WorkCard' );
					break;
				case 3 :
					$user_info ['credential_type'] = get_lang ( 'StudentCard' );
					break;
				default :
					$user_info ['credential_type'] = get_lang ( 'None' );
			}
			$user_info ['credentialType'] = $result_array ['credential_type'];
			switch ($result_array ['sex']) {
				case 1 :
					$user_info ['sex'] = get_lang ( 'Male' );
					break;
				case 2 :
					$user_info ['sex'] = get_lang ( 'Female' );
					break;
				default :
					$user_info ['sex'] = get_lang ( 'Secrect' );
			}
			$user_info ["sextype"] = $result_array ["sex"];
			
			return $user_info;
		}
		return false;
	
	} else {
		return $GLOBALS ["_user"];
	}
}

/**
 * Find all the information about a user from username instead of user id
 * @param $username (string): the username
 * @return $user_info (array): user_id, lastname, firstname, username, email, ...
 * @author Yannick Warnier <yannick.warnier@ZLMS.com>
 */
function api_get_user_info_from_username($username = '') {
	if (empty ( $username )) {
		return $GLOBALS ["_user"];
	}
	$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
	$sql = "SELECT * FROM " . $tbl_user . " WHERE username='" . escape ( $username ) . "'";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	if (Database::num_rows ( $result ) > 0) {
		$result_array = Database::fetch_array ( $result );
		$user_info ['firstName'] = $result_array ['firstname'];
		$user_info ['lastName'] = $result_array ['lastname'];
		$user_info ['mail'] = $result_array ['email'];
		$user_info ['picture_uri'] = $result_array ['picture_uri'];
		$user_info ['user_id'] = $result_array ['user_id'];
		$user_info ['official_code'] = $result_array ['official_code'];
		$user_info ['status'] = $result_array ['status'];
		$user_info ['auth_source'] = $result_array ['auth_source'];
		$user_info ['username'] = $result_array ['username'];
		//$user_info['theme'] = $result_array['theme'];
		return $user_info;
	}
	return false;
}

/**
 * 正在使用的课程ID
 * Returns the current course id (integer)
 */
function api_get_course_id() {
	return $GLOBALS ["_cid"];
}

function api_get_course_code() {
	global $_course;
	$cc = $_course ['sysCode'];
	if (empty ( $cc ) && isset ( $_SESSION ['_course'] )) {
	    $cc = $_SESSION ['_course'] ['sysCode'];
	}
	return $cc;
}

/**
 * 得到课程的路径
 * Returns the current course directory
 *
 * This function relies on api_get_course_info()
 * @return	string	The directory where the course is located inside the ZLMS "courses" directory
 * @author	Yannick Warnier <yannick.warnier@ZLMS.com>
 */
function api_get_course_path($course_code = null) {
	if (empty ( $course_code )) {
		$info = api_get_course_info ();
	} else {
		$info = api_get_course_info ( $course_code );
	}
	return $info ['path'];
}

/**
 * 得到课程表course_setting中的某个设置信息
 * Gets a course setting from the current course_setting table. Try always using integer values.
 * @param	string	The name of the setting we want from the table
 * @return	mixed	The value of that setting in that table. Return -1 if not found.
 */
function api_get_course_setting($setting_name, $course_code = '') {
	if (empty ( $course_code )) $course_code = api_get_course_code ();
	$table = Database::get_course_table ( TABLE_COURSE_SETTING );
	$res = Database::select_from_course_table ( $table, "variable =" . Database::escape ( $setting_name ), "value", "", 1, $course_code );
	if (Database::num_rows ( $res ) == 1) {
		$row = Database::fetch_array ( $res, 'ASSOC' );
		return $row ['value'];
	}
	return "";
}

function api_get_course_dbName($course_code = null) {
	if (! empty ( $course_code )) {
		$c = api_get_course_info ( $course_code );
		$table = Database::get_course_table ( TABLE_COURSE_SETTING, $c ['dbName'] );
	} else {
		$table = Database::get_course_table ( TABLE_COURSE_SETTING );
	}
	return $table;
}

/**
 * 获取课程的cidReq=id串
 * Returns the cidreq parameter name + current course id
 */
function api_get_cidreq() {
	if ($GLOBALS ["_cid"]) return 'cidReq=' . htmlspecialchars ( $GLOBALS ["_cid"] );
	return '';
}

/**
 * Returns the current course info array.
 * Note: this array is only defined if the user is inside a course.
 * Array elements:
 * ['name']
 * ['official_code']
 * ['sysCode']
 * ['path']
 * ['dbName']
 * ['dbNameGlu']
 * ['titular']
 * ['language']
 * ['extLink']['url' ]
 * ['extLink']['name']
 * ['categoryCode']
 * ['categoryName']
 *
 * @todo	same behaviour as api_get_user_info so that api_get_course_id becomes absolete too
 */
function api_get_course_info($course_code = null) {
	if (empty ( $course_code )) {
		global $_course;
		return $_course;
	} else {
		$course_code = escape ( $course_code );
		$course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
		$course_cat_table = Database::get_main_table ( TABLE_MAIN_CATEGORY );
		$sql = "SELECT `course`.*, `course_category`.`code` `faCode`, `course_category`.`name` `faName` FROM $course_table  LEFT JOIN $course_cat_table
                 ON `course`.`category_code` =  `course_category`.`id`  WHERE `course`.`code` = '$course_code'";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		$_course = array ();
		if (Database::num_rows ( $result ) > 0) {
			global $_configuration;
			$cData = Database::fetch_array ( $result );
			$_course ['id'] = $cData ['code']; //auto-assigned integer
			$_course ['code'] = $cData ['code'];
			$_course ['name'] = $cData ['title'];
			$_course ['official_code'] = $cData ['visual_code']; // use in echo
			$_course ['sysCode'] = $cData ['code']; // use as key in db
			$_course ['path'] = $cData ['directory']; // use as key in path
			$_course ['dbName'] = $cData ['db_name']; // use as key in db list
			//如 : webcs_DEMOCOURSE`.`
			$_course ['dbNameGlu'] = $_configuration ['table_prefix'] . $cData ['db_name'] . $_configuration ['db_glue'];
			$_course ['titular'] = $cData ['tutor_name'];
			$_course ['language'] = $cData ['course_language'];
			//$_course['extLink'     ]['url' ] = $cData['department_url'];
			//$_course['extLink'     ]['name'] = $cData['department_name'];
			$_course ['categoryCode'] = $cData ['faCode'];
			$_course ['categoryName'] = $cData ['faName'];
			
			$_course ['visibility'] = $cData ['visibility'];
			$_course ['subscribe_allowed'] = $cData ['subscribe'];
			$_course ['unubscribe_allowed'] = $cData ['unsubscribe'];
			$_course ['is_audit_enabled'] = $cData ['is_audit_enabled'];
			$_course ['is_subscribe_enabled'] = $cData ['is_subscribe_enabled'];
			$_course ['is_shown'] = $cData ['is_shown'];
			$_course ['credit_hours'] = $cData ['credit_hours'];
		}
		return $_course;
	}
}

/*
 ==============================================================================
 DATABASE QUERY MANAGEMENT
 ==============================================================================
 */
/**
 * 执行SQL语句,
 * Executes an SQL query
 * You have to use addslashes() on each value that you want to record into the database
 *
 * @author Olivier Brouckaert
 * @param  string $query - SQL query
 * @param  string $file - optional, the file path and name of the error (__FILE__)
 * @param  string $line - optional, the line of the error (__LINE__)
 * @return resource - the return value of the query
 */
function api_sql_query($sql, $file = '', $line = 0) {
	global $log, $_database_connection;
	$log->debug ( $sql );
	
	if (isset ( $_database_connection ) && $_database_connection != NULL) {
		$result = mysql_query ( $sql, $_database_connection );
	} else {
		$result = mysql_query ( $sql );
	}
	
	if ($line && ! $result && DEBUG_MODE) {
		$log->err ( "MySQL Query Error: " . mysql_errno () . ": " . mysql_error () );
		$log->err ( mysql_errno () . ": " . mysql_error () );
		$info = '<pre>';
		$info .= '<b>MYSQL ERROR :</b><br/> ';
		$info .= mysql_errno () . ": " . mysql_error ();
		$info .= '<br/>';
		$info .= '<b>QUERY       :</b><br/> ';
		$info .= $sql;
		$info .= '<br/>';
		$info .= '<b>FILE        :</b><br/> ';
		$info .= ($file == '' ? ' unknown ' : $file);
		$info .= '<br/>';
		$info .= '<b>LINE        :</b><br/> ';
		$info .= ($line == 0 ? ' unknown ' : $line);
		$info .= '</pre>';
		@ mysql_close ();
		die ( $info );
	}
	return $result;
}

/**
 * 将查询结果存入数组
 * Store the result of a query into an array
 *
 * @author Olivier Brouckaert
 * @param  resource $result - the return value of the query
 * @return array - the value returned by the query
 */
function api_store_result($result) {
	$tab = array ();
	while ( $row = mysql_fetch_array ( $result ) ) {
		$tab [] = $row;
	}
	return $tab;
}

/**
 * sql 查询结果存入数组
 *
 * @param unknown_type $query
 * @param unknown_type $file
 * @param unknown_type $line
 * @return unknown
 * @author liyu
 */
function api_sql_query_array($sql, $file = '', $line = 0) {
	$result = api_sql_query ( $sql, $file, $line );
	return api_store_result ( $result );
}

function api_store_result_array($result) {
	$tab = array ();
	while ( $row = mysql_fetch_array ( $result, MYSQL_ASSOC ) ) {
		$tab [] = $row;
	}
	return $tab;
}

function api_sql_query_array_assoc($sql, $file = '', $line = 0) {
	$result = api_sql_query ( $sql, $file, $line );
	return api_store_result_array ( $result );
}

function api_sql_query_one_row($sql, $file = '', $line = 0) {
	$result = api_sql_query ( $sql, $file, $line );
	if (mysql_num_rows ( $result ) == 1) {
		$row = mysql_fetch_array ( $result, MYSQL_NUM );
		return $row;
	}
	return false;
}

/*
 ==============================================================================
 SESSION MANAGEMENT
 ==============================================================================
 */
/**
 *
 *
 * @author Olivier Brouckaert
 * @param  string variable - the variable name to save into the session
 */
function api_session_start() {
	global $_configuration;
	$session_lifetime = 0;
	if (isset ( $_configuration ["session_lifetime"] ) and $_configuration ["session_lifetime"] > 0) {
		$session_lifetime = $_configuration ["session_lifetime"];
	}
	session_set_cookie_params ( $session_lifetime, URL_APPEND );
	if (isset ( $_configuration ['store_session_in_db'] ) && $_configuration ['store_session_in_db'] == TRUE && function_exists ( 'session_set_save_handler' )) {
		require_once (api_get_path ( LIBRARY_PATH ) . 'session_handler.class.php');
		$session_handler = new session_handler ();
		@ session_set_save_handler ( array (& $session_handler, 'open' ), array (& $session_handler, 'close' ), array (& $session_handler, 'read' ), array (& $session_handler, 'write' ), array (& $session_handler, 'destroy' ), array (& $session_handler, 'garbage' ) );
	}
	if (isset ( $_configuration ["session_name"] ) && ! empty ( $_configuration ["session_name"] )) session_name ( $_configuration ["session_name"] );
	session_start ();
	header ( "Cache-Control: no-cache, must-revalidate" ); // HTTP/1.1
	header ( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); // Date in the past
}

/**
 * save a variable into the session
 *
 * BUG: function works only with global variables
 *
 * @author Olivier Brouckaert
 * @param  string variable - the variable name to save into the session
 */
function api_session_register($variable) {
	global $$variable;
	//session_register ( $variable );
        $_SESSION[$variable]='';
	$_SESSION [$variable] = $$variable;
}

/**
 * Remove a variable from the session.
 *
 * @author Olivier Brouckaert
 * @param  string variable - the variable name to remove from the session
 */
function api_session_unregister($variable) {
	$variable = strval ( $variable );
	if (isset ( $GLOBALS [$variable] )) {
		unset ( $GLOBALS [$variable] );
	}
	
	if (isset ( $_SESSION [$variable] )) {
		$_SESSION [$variable] = null;
		session_unregister ( $variable );
	}
}

/**
 * Clear the session
 *
 * @author Olivier Brouckaert
 */
function api_session_clear() {
	session_regenerate_id ();
	session_unset ();
	$_SESSION = array ();
}

/**
 * Destroy the session
 *
 * @author Olivier Brouckaert
 */
function api_session_destroy() {
	session_unset ();
	$_SESSION = array ();
	session_destroy ();
}

/*
 ==============================================================================
 STRING MANAGEMENT
 ==============================================================================
 */
/**
 * 往已有URL中增加参数，如果已有参数则用新值替换
 * Add a parameter to the existing URL. If this parameter already exists,
 * just replace it with the new value
 * @param   string  The URL
 * @param   string  param=value string
 * @param   boolean Whether to filter XSS or not
 * @return  string  The URL with the added parameter
 */
function api_add_url_param($url, $param, $filter_xss = true) {
	if (empty ( $param )) {
		return $url;
	}
	if (strstr ( $url, '?' )) {
		if ($param [0] != '&') {
			$param = '&' . $param;
		}
		list ( , $query_string ) = explode ( '?', $url );
		$param_list1 = explode ( '&', $param );
		$param_list2 = explode ( '&', $query_string );
		$param_list1_keys = $param_list1_vals = array ();
		foreach ( $param_list1 as $key => $enreg ) {
			list ( $param_list1_keys [$key], $param_list1_vals [$key] ) = explode ( '=', $enreg );
		}
		$param_list1 = array ('keys' => $param_list1_keys, 'vals' => $param_list1_vals );
		foreach ( $param_list2 as $enreg ) {
			$enreg = explode ( '=', $enreg );
			$key = array_search ( $enreg [0], $param_list1 ['keys'] );
			if (! is_null ( $key ) && ! is_bool ( $key )) {
				$url = str_replace ( $enreg [0] . '=' . $enreg [1], $enreg [0] . '=' . $param_list1 ['vals'] [$key], $url );
				$param = str_replace ( '&' . $enreg [0] . '=' . $param_list1 ['vals'] [$key], '', $param );
			}
		}
		$url .= $param;
	} else {
		$url = $url . '?' . $param;
	}
	if ($filter_xss === true) {
		$url = Security::remove_XSS ( urldecode ( $url ) );
	}
	return $url;
}

/**
 * 生成密码
 * Returns a difficult to guess password.
 * @param int $length, the length of the password
 * @return string the generated password
 */
function api_generate_password($length = 8) {
	$characters = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
	if ($length < 2) $length = 2;
	
	$password = '';
	for($i = 0; $i < $length; $i ++) {
		$password .= $characters [rand () % strlen ( $characters )];
	}
	return $password;
}

/**
 * Checks a password to see wether it is OK to use.
 * @param string $password
 * @return true if the password is acceptable, false otherwise
 */
function api_check_password($password) {
	$lengthPass = strlen ( $password );
	if ($lengthPass < 5) {
		return false;
	}
	$passLower = strtolower ( $password );
	$cptLettres = $cptChiffres = 0;
	for($i = 0; $i < $lengthPass; $i ++) {
		$codeCharCur = ord ( $passLower [$i] );
		if ($i && abs ( $codeCharCur - $codeCharPrev ) <= 1) {
			$consecutif ++;
			if ($consecutif == 3) {
				return false;
			}
		} else {
			$consecutif = 1;
		}
		if ($codeCharCur >= 97 && $codeCharCur <= 122) {
			$cptLettres ++;
		} elseif ($codeCharCur >= 48 && $codeCharCur <= 57) {
			$cptChiffres ++;
		} else {
			return false;
		}
		$codeCharPrev = $codeCharCur;
	}
	return ($cptLettres >= 3 && $cptChiffres >= 2) ? true : false;
}

/**
 * truncates a string
 * TODO: 更新到最新版本
 * @author Brouckaert Olivier
 * @param  string text - text to truncate
 * @param  integer length - length of the truncated text
 * @param  string endStr - suffix
 * @param  boolean middle - if true, truncates on string middle
 */
function api_trunc_str($text, $length = 30, $endStr = '...', $middle = false) {
	if (api_strlen ( $text ) <= $length) {
		return $text;
	}
	if ($middle) {
		$text = rtrim ( api_substr ( $text, 0, round ( $length / 2 ) ) ) . $endStr . ltrim ( api_substr ( $text, - round ( $length / 2 ) ) );
	} else {
		$text = rtrim ( api_substr ( $text, 0, $length ) ) . $endStr;
	}
	return $text;
}

/**
 * @deprecated use api_trunc_str() instead
 * @param $input
 * @param $length
 * @return unknown_type
 */
function shorten($input, $length = 45) {
	$length = intval ( $length );
	if (! $length) {
		$length = 45;
	}
	return api_trunc_str ( $input, $length );
}

function api_trunc_str2($text, $length = 30, $endStr = '...') {
	//echo mb_strlen($text,SYSTEM_CHARSET);
	if (mb_strlen ( $text, SYSTEM_CHARSET ) <= $length) {
		return $text;
	}
	$text = mb_substr ( $text, 0, $length, SYSTEM_CHARSET ) . $endStr;
	return $text;

}

/**
 * handling simple and double apostrofe in order that strings be stored properly in database
 *
 * @author Denes Nagy
 * @param  string variable - the variable to be revised
 */
function domesticate($input) {
	$input = stripslashes ( $input );
	$input = str_replace ( "'", "''", $input );
	$input = str_replace ( '"', "''", $input );
	return ($input);
}

/**
 * Sets the current user as anonymous if it hasn't been identified yet. This
 * function should be used inside a tool only. The function api_clear_anonymous()
 * acts in the opposite direction by clearing the anonymous user's data every
 * time we get on a course homepage or on a neutral page (index, admin, my space)
 * @return	bool	true if set user as anonymous, false if user was already logged in or anonymous id could not be found
 * @since 1.8.5
 */
function api_set_anonymous() {
	global $_user;
	if (! empty ( $_user ['user_id'] )) {
		return false;
	} else {
		$user_id = api_get_anonymous_id ();
		if ($user_id == 0) {
			return false;
		} else {
			api_session_unregister ( '_user' );
			$_user ['user_id'] = $user_id;
			$_user ['is_anonymous'] = true;
			api_session_register ( '_user' );
			$GLOBALS ['_user'] = $_user;
			return true;
		}
	}
}

/**
 * Gets an anonymous user ID
 *
 * For some tools that need tracking, like the learnpath tool, it is necessary
 * to have a usable user-id to enable some kind of tracking, even if not
 * perfect. An anonymous ID is taken from the users table by looking for a
 * status of "6" (anonymous).
 * @return	int	User ID of the anonymous user, or O if no anonymous user found
 * @since 1.8.5
 */
function api_get_anonymous_id() {
	$table = Database::get_main_table ( TABLE_MAIN_USER );
	$sql = "SELECT user_id FROM $table WHERE status = " . ANONYMOUS;
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	if (Database::num_rows ( $res ) > 0) {
		$row = Database::fetch_array ( $res );
		//error_log('api_get_anonymous_id() returns '.$row['user_id'],0);
		return $row ['user_id'];
	} else //no anonymous user was found
{
		return 0;
	}
}

/**
 * Tells whether this user is an anonymous user
 * @param	int		User ID (optional, will take session ID if not provided)
 * @param	bool	Whether to check in the database (true) or simply in the session (false) to see if the current user is the anonymous user
 * @return	bool	true if this user is anonymous, false otherwise
 * @since 1.8.5
 */
function api_is_anonymous($user_id = null, $db_check = false) {
	if (! isset ( $user_id )) {
		$user_id = api_get_user_id ();
	}
	if ($db_check) {
		$info = api_get_user_info ( $user_id );
		if ($info ['status'] == ANONYMOUS) {
			return true;
		}
	} else {
		global $_user;
		if (! isset ( $_user )) {
			//in some cases, api_set_anonymous doesn't seem to be
			//triggered in local.inc.php. Make sure it is.
			//Occurs in agenda for admin links - YW
			global $use_anonymous;
			if (isset ( $use_anonymous ) && $use_anonymous == true) {
				api_set_anonymous ();
			}
			return true;
		}
		if (isset ( $_user ['is_anonymous'] ) and $_user ['is_anonymous'] === true) {
			return true;
		}
	}
	return false;
}

/**
 * Check if the current user is a course coach
 * @return	bool	True if current user is a course coach
 * @since 1.8.5
 */
function api_is_course_coach() {
	return $_SESSION ['is_courseCoach'];
}

/**
 * Check if the current user is a course tutor
 * @return 	bool	True if current user is a course tutor
 * @since 1.8.5
 */
function api_is_course_tutor() {
	return $_SESSION ['is_courseTutor'];
}

/*
 ==============================================================================
 CONFIGURATION SETTINGS
 ==============================================================================
 */
/**
 * DEPRECATED, use api_get_setting instead
 */
function get_setting($variable, $key = NULL) {
	global $_setting;
	return is_null ( $key ) ? $_setting [$variable] : $_setting [$variable] [$key];
}

/**
 * Returns the value of a setting from the web-adjustable admin config settings.
 *
 * WARNING true/false are stored as string, so when comparing you need to check e.g.
 * if(api_get_setting("show_navigation_menu") == "true") //CORRECT
 * instead of
 * if(api_get_setting("show_navigation_menu") == true) //INCORRECT
 *
 * @author Rene Haentjens
 * @author Bart Mollet
 */
function api_get_setting($variable, $key = NULL) {
	global $_setting;
	
	$ret = is_null ( $key ) ? $_setting [$variable] : $_setting [$variable] [$key];
	
	if (($variable == 'stylesheets') && empty ( $ret )) {
		$ret = 'default';
	}
	
	return $ret;
}

/**
 * 获取登录用户的个人设置信息
 *
 * @param unknown_type $variable
 * @param unknown_type $key
 * @return unknown
 * @author liyu
 */
function api_get_my_setting($variable, $key = NULL) {
	global $_my_setting;
	if (empty ( $_my_setting )) {
		$_my_setting = get_personal_settings ( api_get_user_id () );
	}
	$ret = is_null ( $key ) ? $_my_setting [$variable] : $_my_setting [$variable] [$key];
	
	return $ret;
}

/**
 * Returns an escaped version of $_SERVER['PHP_SELF'] to avoid XSS injection
 * @return	string	Escaped version of $_SERVER['PHP_SELF']
 */
function api_get_self() {
	return htmlentities ( $_SERVER ['PHP_SELF'], ENT_NOQUOTES, SYSTEM_CHARSET );
}

function get_lang($line, $id = '') {
	global $lang;
	$line = ($line == '' or ! isset ( $lang [$line] )) ? $line : $lang [$line];
	
	if ($id != '') {
		$line = '<label for="' . $id . '">' . $line . "</label>";
	}
	
	return $line;
}

/**
 * Gets the current interface language
 * @return string The current language of the interface
 */
function api_get_interface_language() {
	global $language_interface;
	return $language_interface;
}

/*
 ==============================================================================
 USER PERMISSIONS
 ==============================================================================
 */
/**
 * Check if current user is a platform administrator
 * @return boolean True if the user has platform admin rights,
 * false otherwise.
 */
function api_is_platform_admin() {
	return $_SESSION ["is_platformAdmin"];
}

function api_is_admin() {
    global $_user;               
    if (api_is_platform_admin () or $_user ['status'] == COURSEMANAGER or $_user ['status'] == PLATFORM_ADMIN){
      return TRUE;  
    }else{
       return FALSE;
    }
}

/**
 * Check if current user is allowed to create courses
 * @return boolean True if the user has course creation rights,
 * false otherwise.
 */
function api_is_allowed_to_create_course() {
	return $_SESSION ["is_allowedCreateCourse"]; //教师有创建课程权限
}

/**
 * Check if the current user is a course administrator
 * @return boolean True if current user is a course administrator
 */
function api_is_course_admin() {
	return $_SESSION ["is_courseAdmin"];
}

/**
 * Checks whether the curent user is in a course or not.
 *
 * @param	string	The course code - optional (takes it from session if not given)
 * @return	boolean
 * @author	Yannick Warnier <yannick.warnier@ZLMS.com>
 * @since 1.8.6
 */
function api_is_in_course($course_code = null) {
	if (isset ( $_SESSION ['_course'] ['sysCode'] )) {
		if (! empty ( $course_code )) {
			if ($course_code == $_SESSION ['_course'] ['sysCode'])
				return true;
			else return false;
		} else {
			return true;
		}
	}
	return false;
}

/**
 * Checks whether the curent user is in a group or not.
 *
 * @param	string	The group id - optional (takes it from session if not given)
 * @param	string	The course code - optional (no additional check by course if course code is not given)
 * @return	boolean
 * @author	Ivan Tcholakov
 * @since 1.8.6
 */
function api_is_in_group($group_id = null, $course_code = null) {
	if (! empty ( $course_code )) {
		if (isset ( $_SESSION ['_course'] ['sysCode'] )) {
			if ($course_code != $_SESSION ['_course'] ['sysCode']) return false;
		} else {
			return false;
		}
	}
	
	if (isset ( $_SESSION ['_gid'] ) && $_SESSION ['_gid'] != '') {
		if (! empty ( $group_id )) {
			if ($group_id == $_SESSION ['_gid'])
				return true;
			else return false;
		} else {
			return true;
		}
	}
	return false;
}

// sys_get_temp_dir() is on php since 5.2.1 (1.8.6引进)
if (! function_exists ( 'sys_get_temp_dir' )) {

	// Based on http://www.phpit.net/
	// article/creating-zip-tar-archives-dynamically-php/2/
	function sys_get_temp_dir() {
		// Try to get from environment variable
		if (! empty ( $_ENV ['TMP'] )) {
			return realpath ( $_ENV ['TMP'] );
		} else if (! empty ( $_ENV ['TMPDIR'] )) {
			return realpath ( $_ENV ['TMPDIR'] );
		} else if (! empty ( $_ENV ['TEMP'] )) {
			return realpath ( $_ENV ['TEMP'] );
		} else {
			// Try to use system's temporary directory
			// as random name shouldn't exist
			$temp_file = tempnam ( md5 ( uniqid ( rand (), TRUE ) ), '' );
			if ($temp_file) {
				$temp_dir = realpath ( dirname ( $temp_file ) );
				unlink ( $temp_file );
				return $temp_dir;
			} else {
				return FALSE;
			}
		}
	}
}

/**
 * This function gets the hash in md5 or sha1 (it depends in the platform config) of a given password
 * @param  string password
 * @return string password with the applied hash
 * @since 1.8.6
 * */
function api_get_encrypted_password($password, $salt = '') {
	global $_configuration;
	switch ($_configuration ['crypted_method']) {
		case 'rijndael_256' :
			if (! empty ( $salt )) {
				$passwordcrypted = api_encrypt ( $password . $salt, $_configuration ['security_key'], MCRYPT_RIJNDAEL_256 );
			} else {
				$passwordcrypted = api_encrypt ( $password, $_configuration ['security_key'], MCRYPT_RIJNDAEL_256 );
			}
			return $passwordcrypted;
			break;
		case 'md5' :
			if (! empty ( $salt )) {
				$passwordcrypted = md5 ( $password . $salt );
			} else {
				$passwordcrypted = md5 ( $password );
			}
			return $passwordcrypted;
			break;
		case 'sha1' :
			if (! empty ( $salt )) {
				$passwordcrypted = sha1 ( $password . $salt );
			} else {
				$passwordcrypted = sha1 ( $password );
			}
			return $passwordcrypted;
			break;
		case 'none' :
			return $password;
			break;
		default :
			if (! empty ( $salt )) {
				$passwordcrypted = md5 ( $password . $salt );
			} else {
				$passwordcrypted = md5 ( $password );
			}
			return $passwordcrypted;
			break;
	}
}

/**
 * Check if the current user is a course or session coach
 * @deprecated
 * @return boolean True if current user is a course or session coach
 */
function api_is_coach() {
	global $_user;
	global $sessionIsCoach;
	
	return api_is_allowed_to_edit ( TRUE );
}

/*
 ==============================================================================
 DISPLAY OPTIONS
 student view, title, message boxes,...
 ==============================================================================
 */
/**
 * Displays the title of a tool.
 * Normal use: parameter is a string:
 * api_display_tool_title("My Tool")
 *
 * Optionally, there can be a subtitle below
 * the normal title, and / or a supra title above the normal title.
 *
 * e.g. supra title:
 * group
 * GROUP PROPERTIES
 *
 * e.g. subtitle:
 * AGENDA
 * calender & events tool
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param  mixed $titleElement - it could either be a string or an array
 * containing 'supraTitle', 'mainTitle',
 * 'subTitle'
 * @return void
 */
function api_display_tool_title($titleElement) {
	if (is_string ( $titleElement )) {
		$tit = $titleElement;
		unset ( $titleElement );
		$titleElement ['mainTitle'] = $tit;
	}
	echo '<h3>';
	if ($titleElement ['supraTitle']) {
		echo '<small>' . $titleElement ['supraTitle'] . '</small><br>';
	}
	if ($titleElement ['mainTitle']) {
		echo $titleElement ['mainTitle'];
	}
	if ($titleElement ['subTitle']) {
		echo '<br><small>' . $titleElement ['subTitle'] . '</small>';
	}
	echo '</h3>';
}

/**
 * Displays the contents of an array in a messagebox.
 * @param array $info_array An array with the messages to show
 */
function api_display_array($info_array) {
	foreach ( $info_array as $element ) {
		$message .= $element . "<br>";
	}
	Display::display_normal_message ( $message );
}

/**
 * Displays debug info
 * @param string $debug_info The message to display
 * @author Roan Embrechts
 * @version 1.1, March 2004
 */
function api_display_debug_info($debug_info) {
	$message = "<i>Debug Information:</i><br>";
	$message .= $debug_info;
	Display::display_normal_message ( $message );
}

/**
 * @deprecated, use api_is_allowed_to_edit() instead
 */
function is_allowed_to_edit() {
	return api_is_allowed_to_edit ();
}

/**
 * 判断是否可编辑课程信息(包括课程管理员及平台管理员)
 * Function that removes the need to directly use is_courseAdmin global in
 * tool scripts. It returns true or false depending on the user's rights in
 * this particular course.
 * Optionally checking for tutor and coach roles here allows us to use the
 * student_view feature altogether with these roles as well.
 * @param	bool	Whether to check if the user has the tutor role
 * @param	bool	Whether to check if the user has the coach role
 * @version 1.8.5, February 2004
 * @return boolean, true: the user has the rights to edit, false: he does not
 */
function api_is_allowed_to_edit($tutor = true) {
	$is_courseAdmin = api_is_course_admin () || api_is_platform_admin ();
	if (! $is_courseAdmin && $tutor == true) { //if we also want to check if the user is a tutor...
		$is_courseAdmin = $is_courseAdmin || api_is_course_tutor ();
	}
	return $is_courseAdmin;
}

/**
 *
 * @param $tool the tool we are checking ifthe user has a certain permission
 * @param $action the action we are checking (add, edit, delete, move, visibility)
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @version 1.0
 */
function api_is_allowed($tool, $action, $task_id = 0) {
	global $_course, $_user;
	if (api_is_course_admin ()) return true;
	return false;
}

/**
 * Displays message "You are not allowed here..." and exits the entire script.
 * @version 1.0, February 2004
 * @version 1.8.0, August 2006
 */
function api_not_allowed() {
	global $_user;
	$home_url = api_get_path ( WEB_PATH );
	if (! headers_sent ()) Display::display_reduced_header ( NULL );
	if (! empty ( $_SERVER ['REQUEST_URI'] ) && ! empty ( $_GET ['cidReq'] )) { //cidReq有值且访问请求一个URL
		echo '<div align="left">';
		if (! (isset ( $_user ['user_id'] ) && $_user ['user_id'])) { //Session超时
			include_once (api_get_path ( LIBRARY_PATH ) . 'formvalidator/FormValidator.class.php');
			echo '<form  action="' . $home_url . '" method="get" id="loginForm" target="_top"></form><script>if(document.getElementById("loginForm")) document.getElementById("loginForm").submit();else location.href="' . $home_url . '";</script>';
		} else {
			Display::display_error_message ( '<p>' . get_lang ( 'AccessDeny' ) . '&nbsp;' . get_lang ( 'AccessDenyReson1' ) . '<a href="javascript:history.back();">' . get_lang ( "ClickHere" ) . get_lang ( "ReturnTo" ) . '</a><br/>', false );
		}
		$_SESSION ['request_uri'] = $_SERVER ['REQUEST_URI'];
		Display::display_footer ();
		die ();
	} else {
		if (! isset ( $_user ['user_id'] ) && empty ( $_SESSION ['_user'] ['user_id'] )) { //Session超时
			echo '<form  action="' . $home_url . '" method="get" id="loginForm" target="_top"></form><script>if(document.getElementById("loginForm")) document.getElementById("loginForm").submit();else location.href="' . $home_url . '";</script>';
		} else { //没有权限
			$html = get_lang ( 'AccessDeny' ) . '&nbsp;' . get_lang ( 'AccessDenyReson1' );
			//$html .= '<a href="' . ($_SERVER ['HTTP_REFERER'] ? $_SERVER ['HTTP_REFERER'] : 'javascript:history.back();') . '">' . get_lang ( "ClickHere" ) . get_lang ( "ReturnTo" ) . '</a><br/>';
			Display::display_error_message ( $html, false );
		}
		Display::display_footer ();
		die ();
	}
}

/**
 * Displays message "You are not allowed here..." and exits the entire script.
 * 显示拒绝访问信息并退出执行脚本, 为1.8.5版本复制过来,有待测试
 *
 * @param	bool	Whether or not to print headers (default = false -> does not print them)
 * @version 1.0, February 2004
 * @version 1.8.5, August 2006
 */
function api_not_allowed_($print_headers = false) {
	$home_url = api_get_path ( WEB_PATH );
	$user = api_get_user_id ();
	$course = api_get_course_id ();
	if ((isset ( $user ) && ! api_is_anonymous ()) && (! isset ( $course ) || $course == - 1) && empty ( $_GET ['cidReq'] )) { //if the access is not authorized and there is some login information
		// but the cidReq is not found, assume we are missing course data and send the user
		// to the user_portal
		if (! headers_sent () or $print_headers) Display::display_reduced_header ( NULL );
		echo '<div align="left">';
		Display::display_error_message ( get_lang ( 'NotAllowedClickBack' ) . '&nbsp;<a href="' . $_SERVER ['HTTP_REFERRER'] . '">' . get_lang ( 'BackToPreviousPage' ) . '</a><br/>', false );
		echo '</div>';
		if ($print_headers) Display::display_footer ();
		die ();
	} elseif (! empty ( $_SERVER ['REQUEST_URI'] ) && ! empty ( $_GET ['cidReq'] )) {
		if (! empty ( $user ) && ! api_is_anonymous ()) {
			if (! headers_sent () or $print_headers) Display::display_reduced_header ( NULL );
			echo '<div align="left">';
			Display::display_error_message ( get_lang ( 'NotAllowedClickBack' ) . '&nbsp;&nbsp;<a href="' . $_SERVER ['HTTP_REFERRER'] . '">' . get_lang ( 'BackToPreviousPage' ) . '</a><br/>', false );
			echo '</div>';
			if ($print_headers) Display::display_footer ();
			die ();
		} else {
			include_once (api_get_path ( LIBRARY_PATH ) . 'formvalidator/FormValidator.class.php');
			$form = new FormValidator ( 'formLogin', 'post', api_get_self () . '?' . $_SERVER ['QUERY_STRING'], "_top" );
			$form->addElement ( 'static', null, null, 'Username' );
			$form->addElement ( 'text', 'login', '', array ('size' => 15 ) );
			$form->addElement ( 'static', null, null, 'Password' );
			$form->addElement ( 'password', 'password', '', array ('size' => 15 ) );
			$form->addElement ( 'submit', 'submitAuth', get_lang ( 'Ok' ) );
			$test = $form->return_form ();
			if (! headers_sent () or $print_headers) Display::display_reduced_header ( NULL );
			echo '<div align="center">';
			Display::display_error_message ( get_lang ( 'NotAllowed' ) . '<br/><br/>' . get_lang ( 'PleaseLoginAgainFromFormBelow' ) . '<br/>' . $test, false );
			echo '</div>';
			$_SESSION ['request_uri'] = $_SERVER ['REQUEST_URI'];
			if ($print_headers) Display::display_footer ();
			die ();
		}
	} else {
		if (! empty ( $user ) && ! api_is_anonymous ()) {
			if (! headers_sent () or $print_headers) Display::display_reduced_header ( NULL );
			echo '<div align="center">';
			Display::display_error_message ( get_lang ( 'NotAllowedClickBack' ) . '<br/><br/><a href="' . $_SERVER ['HTTP_REFERRER'] . '">' . get_lang ( 'BackToPreviousPage' ) . '</a><br/>', false );
			echo '</div>';
			if ($print_headers) Display::display_footer ();
			die ();
		} else {
			//if no course ID was included in the requested URL, redirect to homepage
			if ($print_headers) Display::display_reduced_header ( NULL );
			echo '<div align="center">';
			Display::display_error_message ( get_lang ( 'NotAllowed' ) . '<br/><br/><a href="' . $home_url . '">' . get_lang ( 'PleaseLoginAgainFromHomepage' ) . '</a><br/>', false );
			echo '</div>';
			if ($print_headers) Display::display_footer ();
			die ();
		}
	}
}

/*
 ==============================================================================
 WHAT'S NEW
 functions for the what's new icons
 in the user course list
 ==============================================================================
 */
/**
 * 转换MySQL 时间串为Unix Timestamp
 * @param $last_post_datetime standard output date in a sql query
 * @return unix timestamp
 * @author Toon Van Hoecke <Toon.VanHoecke@UGent.be>
 * @version October 2003
 * @desc convert sql date to unix timestamp
 */
function convert_mysql_date($last_post_datetime) {
	list ( $last_post_date, $last_post_time ) = split ( " ", $last_post_datetime );
	list ( $year, $month, $day ) = explode ( "-", $last_post_date );
	list ( $hour, $min, $sec ) = explode ( ":", $last_post_time );
	$announceDate = mktime ( $hour, $min, $sec, $month, $day, $year );
	return $announceDate;
}

/**
 * Gets a MySQL datetime format string from a UNIX timestamp
 * @param   int     UNIX timestamp, as generated by the time() function. Will be generated if parameter not provided
 * @return  string  MySQL datetime format, like '2009-01-30 12:23:34'
 * @since 1.8.6
 */
function api_get_datetime($time = null, $format = 'Y-m-d H:i:s') {
	if (! isset ( $time )) $time = time ();
	return date ( $format, $time );
}

/**
 * Gets item visibility from the item_property table
 * @param	array	Course properties array (result of api_get_course_info())
 * @param	string	Tool (learnpath, document, etc)
 * @param	int		The item ID in the given tool
 * @return	int		-1 on error, 0 if invisible, 1 if visible
 */
function api_get_item_visibility($_course, $tool, $id) {
	if (! is_array ( $_course ) or count ( $_course ) == 0 or empty ( $tool ) or empty ( $id )) return - 1;
	$tool = escape ( $tool );
	$TABLE_ITEMPROPERTY = Database::get_course_table ( TABLE_ITEM_PROPERTY, $_course ['dbName'] );
	$sql = "SELECT * FROM $TABLE_ITEMPROPERTY WHERE tool = '$tool' AND ref = '" . escape ( $id ) . "' AND cc='" . $_course ["code"] . "' LIMIT 1";
	$res = api_sql_query ( $sql );
	if ($res === false or Database::num_rows ( $res ) == 0) return - 1;
	$row = Database::fetch_array ( $res );
	return $row ['visibility'];
}

/**
 * Updates or adds item properties to the Item_propetry table
 * Tool and lastedit_type are language independant strings (langvars->get_lang!)
 *
 * @param $_course : 课程信息数组
 * @param $tool : 课程工具名称
 * @param $item_id : 课程工具ID, 与各课程工具的主键关联 ('id', ...), "*" = all items of the tool
 * @param $lastedit_type : add or update action (1) message to be translated (in trad4all) : e.g. DocumentAdded, DocumentUpdated;
 * (2) "delete"; (3) "visible"; (4) "invisible";
 * @param $user_id : id of the editing/adding user
 * @param $to_group_id : id of the intended group ( 0 = for everybody), only relevant for $type (1)
 * @param $to_user_id : id of the intended user (always has priority over $to_group_id !), only relevant for $type (1)
 * @param string $start_visible 0000-00-00 00:00:00 format
 * @param unknown_type $end_visible 0000-00-00 00:00:00 format
 * @param $learn_path_document : the document of learn path is invisibility for student
 * @return boolean False if update fails.
 * @author Zhong
 * @version January 2007
 * @desc update the item_properties table (if entry not exists, insert) of the course
 */
function api_item_property_update($_course, $tool, $item_id, $lastedit_type, $user_id, $to_group_id = 0, $to_user_id = NULL, $start_visible = 0, $end_visible = 0, $learn_path_document = false) {
	$tool = escape ( $tool );
	$item_id = escape ( $item_id );
	$lastedit_type = escape ( $lastedit_type );
	$user_id = escape ( $user_id );
	$to_group_id = escape ( $to_group_id );
	$to_user_id = escape ( $to_user_id );
	$start_visible = escape ( $start_visible );
	$end_visible = escape ( $end_visible );
	$course_code = $_course ['code'];
	
	//$time = time();
	$time = date ( "Y-m-d H:i:s", time () );
	$TABLE_ITEMPROPERTY = Database::get_course_table ( TABLE_ITEM_PROPERTY );
	if ($to_user_id <= 0) $to_user_id = NULL; //no to_user_id set
	$start_visible = ($start_visible == 0) ? "0000-00-00 00:00:00" : $start_visible;
	$end_visible = ($end_visible == 0) ? "0000-00-00 00:00:00" : $end_visible;
	
	// set filters for $to_user_id and $to_group_id, with priority for $to_user_id
	$filter = "tool='$tool' AND ref='$item_id'";
	if ($item_id == "*") $filter = "tool='$tool' AND visibility<>'2'"; ////删除 for all (not deleted) items of the tool
	

	// check if $to_user_id and $to_group_id are passed in the function call
	// if both are not passed (both are null) then it is a message for everybody and $to_group_id should be 0 !
	if (is_null ( $to_user_id ) && is_null ( $to_group_id )) $to_group_id = 0;
	if (! is_null ( $to_user_id )) $to_filter = " AND to_user_id='$to_user_id'"; // set filter to intended user
	// update if possible
	$set_type = "";
	switch ($lastedit_type) {
		case "delete" : // 删除 delete = make item only visible for the platform admin
			$visibility = '2';
			$sql = "UPDATE $TABLE_ITEMPROPERTY	SET lastedit_date='$time', lastedit_user_id='$user_id', visibility='$visibility' $set_type	WHERE $filter";
			break;
		case "visible" : // change item to visible
			$visibility = '1';
			$sql = "UPDATE $TABLE_ITEMPROPERTY	SET lastedit_date='$time', lastedit_user_id='$user_id', visibility='$visibility' $set_type	WHERE $filter";
			break;
		case "invisible" : // change item to invisible
			$visibility = '0';
			$sql = "UPDATE $TABLE_ITEMPROPERTY	SET lastedit_date='$time', lastedit_user_id='$user_id', visibility='$visibility' $set_type	WHERE $filter";
			break;
		default : // item will be added or updated
			$set_type = ", lastedit_type='$lastedit_type' ";
			$visibility = '1';
			$filter .= $to_filter;
			$sql = "UPDATE $TABLE_ITEMPROPERTY	SET lastedit_date='$time', lastedit_user_id='$user_id' $set_type WHERE $filter";
	}
	//echo $sql;
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	
	if (Database::affected_rows () == 0) {
		if (! is_null ( $to_user_id )) $to_value = $to_user_id;
		$sql_data = array ('tool' => $tool, 
				'ref' => $item_id, 
				'insert_date' => $time, 
				'insert_user_id' => $user_id, 
				'lastedit_date' => $time, 
				'lastedit_type' => $lastedit_type, 
				'lastedit_user_id' => $user_id, 
				'to_user_id' => $to_value, 
				'visibility' => $visibility, 
				'start_visible' => $start_visible, 
				'end_visible' => $end_visible );
		$sql_data ['cc'] = $_course ['code'];
		$sql = Database::sql_insert ( $TABLE_ITEMPROPERTY, $sql_data );
		return api_sql_query ( $sql, __FILE__, __LINE__ );
	}
	return TRUE;
}

/*
 ==============================================================================
 Language Dropdown
 ==============================================================================
 */

function api_get_languages_combo($name = "language") {
	$ret = "";
	$platformLanguage = api_get_setting ( 'platformLanguage' );
	$language_list = api_get_languages ();
	if (count ( $language_list ['name'] ) < 2) return $ret;
	if (isset ( $_SESSION ['user_language_choice'] ))
		$default = $_SESSION ['user_language_choice'];
	else $default = $platformLanguage;
	$languages = $language_list ['name'];
	$folder = $language_list ['folder'];
	$ret .= '<select name="' . $name . '">';
	foreach ( $languages as $key => $value ) {
		if ($folder [$key] == $default)
			$selected = ' selected="selected"';
		else $selected = '';
		$ret .= sprintf ( '<option value=%s" %s>%s</option>' . "\n", $folder [$key], $selected, $value );
	}
	$ret .= '</select>';
	return $ret;
}

/**
 * 语言下拉框列表
 * Displays a form (drop down menu) so the user can select his/her preferred language.
 * The form works with or without javascript
 */
function api_display_language_form() {
	$platformLanguage = api_get_setting ( 'platformLanguage' );
	$dirname = api_get_path ( SYS_PATH ) . "main/lang/"; // this line is probably no longer needed
	// retrieve a complete list of all the languages.
	$language_list = api_get_languages ();
	// the the current language of the user so that his/her language occurs as selected in the dropdown menu
	$user_selected_language = $_SESSION ["user_language_choice"];
	if (! isset ( $user_selected_language )) $user_selected_language = $platformLanguage;
	$original_languages = $language_list ['name'];
	$folder = $language_list ['folder']; // this line is probably no longer needed
	?>
<script language="JavaScript" type="text/JavaScript">
	<!--
	function jumpMenu(targ,selObj,restore){ //v3.0
	  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
	  if (restore) selObj.selectedIndex=0;
	}
	//-->
	</script>
<?php
	echo "<form id=\"lang_form\" name=\"lang_form\" method=\"post\" action=\"" . $_SERVER ['PHP_SELF'] . "\">", "<select name=\"language_list\"  onchange=\"jumpMenu('parent',this,0)\">";
	foreach ( $original_languages as $key => $value ) {
		if ($folder [$key] == $user_selected_language)
			$option_end = " selected=\"selected\" >";
		else $option_end = ">";
		echo "<option value=\"" . $_SERVER ['PHP_SELF'] . "?language=" . $folder [$key] . "\"$option_end";
		#echo substr($value,0,16); #cut string to keep 800x600 aspect
		echo $value;
		//echo htmlentities($value, ENT_NOQUOTES, 'utf-8');
		echo "</option>\n";
	}
	echo "</select>";
	echo "<noscript><input type=\"submit\" name=\"user_select_language\" value=\"" . get_lang ( "Ok" ) . "\" /></noscript>";
	echo "</form>";
}

/**
 * Return a list of all the languages that are made available by the admin.
 * @return array An array with all languages. Structure of the array is
 * array['name'] = An array with the name of every language
 * array['folder'] = An array with the corresponding ZLMS-folder
 */
function api_get_languages() {
	$tbl_language = Database::get_main_table ( TABLE_MAIN_LANGUAGE );
	$sql = "SELECT * FROM $tbl_language WHERE available='1' AND enabled='1' ORDER BY original_name ASC";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	while ( $row = mysql_fetch_array ( $result ) ) {
		$language_list ['name'] [] = $row ['original_name'];
		$language_list ['folder'] [] = $row ['dokeos_folder'];
	}
	return $language_list;
}

/**
 * Return the id of a language
 * @param string language name (ZLMS_folder)
 * @return int id of the language
 */
function api_get_language_id($language) {
	$tbl_language = Database::get_main_table ( TABLE_MAIN_LANGUAGE );
	$language = escape ( $language );
	$sql = "SELECT id FROM $tbl_language WHERE available='1' AND ZLMS_folder = '$language' ORDER BY ZLMS_folder ASC";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	$row = Database::fetch_array ( $result );
	return $row ['id'];
}

/**
 * Gets language isocode column from the language table, taking the current language as a query parameter.
 * @param string $language	This is the name of the folder containing translations for the corresponding language (e.g arabic, english).
 * If $language is omitted, interface language is assumed then.
 * @return string			The found isocode or null on error.
 * Returned codes are according to the following standards (in order of preference):
 * -  ISO 639-1 : Alpha-2 code (two-letters code - en, fr, es, ...)
 * -  RFC 4646  : five-letter code based on the ISO 639 two-letter language codes
 * and the ISO 3166 two-letter territory codes (pt-BR, ...)
 * -  ISO 639-2 : Alpha-3 code (three-letters code - ast, fur, ...)
 */
function api_get_language_isocode($language = null) {
	return Database::get_language_isocode ( $language );
}

/**
 * Returns a list of CSS themes currently available in the CSS folder
 * @return	array	List of themes directories from the css folder
 */
function api_get_themes() {
	$cssdir = api_get_path ( SYS_PATH ) . 'themes/css/';
	$list_dir = array ();
	$list_name = array ();
	if (@is_dir ( $cssdir )) {
		$themes = @scandir ( $cssdir );
		if (is_array ( $themes )) {
			if ($themes !== false) {
				sort ( $themes );
				
				foreach ( $themes as $theme ) {
					if (substr ( $theme, 0, 1 ) == '.') {
						continue;
					} else {
						if (@is_dir ( $cssdir . $theme )) {
							$list_dir [] = $theme;
							$list_name [] = ucwords ( str_replace ( '_', ' ', $theme ) );
						}
					}
				}
			}
		}
	}
	$return = array ();
	$return [] = $list_dir;
	$return [] = $list_name;
	return $return;
}

/*
 ==============================================================================
 WYSIWYG HTML AREA
 functions for the WYSIWYG html editor, TeX parsing...
 ==============================================================================
 */
/**
 * Displays the FckEditor WYSIWYG editor for online editing of html
 * @param string $name The name of the form-element
 * @param string $content The default content of the html-editor
 * @param int $height The height of the form element
 * @param int $width The width of the form element
 * @param string $optAttrib optional attributes for the form element
 */
function api_disp_html_area($name, $content = '', $height = '', $width = '100%', $optAttrib = '') {
	echo api_return_html_area ( $name, $content, $height, $width, $optAttrib );
}

function api_return_html_area($name, $content = '', $height = '', $width = '100%', $optAttrib = '') {
	global $_configuration, $_course, $fck_attribute;
	require_once (dirname ( __FILE__ ) . '/formvalidator/Element/html_editor.php');
	$editor = new HTML_QuickForm_html_editor ( $name );
	$editor->setValue ( $content );
	if ($height) $fck_attribute ['Height'] = $height;
	if ($width) $fck_attribute ['Width'] = $width;
	return $editor->toHtml ();
}

/**
 * Send an email.
 *
 * Wrapper function for the standard php mail() function. Change this function
 * to your needs. The parameters must follow the same rules as the standard php
 * mail() function. Please look at the documentation on http: //www. php.
 * net/manual/en/function. mail.php
 * @param string $to
 * @param string $subject
 * @param string $message
 * @param string $additional_headers
 * @param string $additional_parameters
 */
function api_send_mail($to, $subject, $message, $additional_headers = null, $additional_parameters = null) {
	$ret = mail ( $to, $subject, $message, $additional_headers, $additional_parameters );
	return $ret;
}

/**
 * This function converts the string "true" or "false" to a boolean true or false.
 * This function is in the first place written for the ZLMS Config Settings (also named AWACS)
 * @param string "true" or "false"
 * @return boolean true or false
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 */
function string_2_boolean($string) {
	if ($string == "true" or $string == "T" or $string == "yes" or $string == "1") return true;
	if ($string == "false" or $string == "F" or $string == "no" or $string == "0") return false;
}

/**
 * Determines the number of plugins installed for a given location
 */
function api_number_of_plugins($location) {
	global $_plugins;
	if (is_array ( $_plugins [$location] )) return count ( $_plugins [$location] );
	return 0;
}

/**
 * including the necessary plugins
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 */
function api_plugin($location) {
	global $_plugins;
	if (is_array ( $_plugins [$location] )) {
		foreach ( $_plugins [$location] as $this_plugin ) {
			//include (api_get_path ( SYS_PLUGIN_PATH ) . "$this_plugin/index.php");
		}
	}
}

/**
 * Checks to see wether a certain plugin is installed.
 * @return boolean true if the plugin is installed, false otherwise.
 */
function api_is_plugin_installed($plugin_list, $plugin_name) {
	foreach ( $plugin_list as $plugin_location ) {
		if (array_search ( $plugin_name, $plugin_location ) !== false) return true;
	}
	return false;
}

/**
 * Apply parsing to content to parse tex commandos that are seperated by [tex]
 * [/tex] to make it readable for techexplorer plugin.
 * @param string $text The text to parse
 * @return string The text after parsing.
 * @author Patrick Cool <patrick.cool@UGent.be>
 * @version June 2004
 */
function api_parse_tex($textext) {
	if (strstr ( $_SERVER ['HTTP_USER_AGENT'], 'MSIE' )) {
		$textext = str_replace ( array ("[tex]", "[/tex]" ), array ("<object classid=\"clsid:5AFAB315-AD87-11D3-98BB-002035EFB1A4\"><param name=\"autosize\" value=\"true\" /><param name=\"DataType\" value=\"0\" /><param name=\"Data\" value=\"", "\" /></object>" ), $textext );
	} else {
		$textext = str_replace ( array ("[tex]", "[/tex]" ), array ("<embed type=\"application/x-techexplorer\" texdata=\"", "\" autosize=\"true\" pluginspage=\"http://www.integretechpub.com/techexplorer/\">" ), $textext );
	}
	return $textext;
}

/**
 * Transform a number of seconds in hh:mm:ss format
 * @author Julian Prud'homme
 * @param integer the number of seconds
 * @return string the formated time
 */
function api_time_to_hms($seconds, $showDays = false, $simple_style = TRUE) {
	$hours = floor ( $seconds / 3600 );
	$min = floor ( ($seconds - ($hours * 3600)) / 60 );
	$sec = floor ( $seconds - ($hours * 3600) - ($min * 60) );
	if ($sec < 10) $sec = "0" . $sec;
	if ($min < 10) $min = "0" . $min;
	if ($showDays && $hours > 24) {
		$days = floor ( $hours / 24 );
		$hours = $hours - $days * 24;
		if ($simple_style) {
			return $days . ":" . $hours . ":" . $min . ":" . $sec;
		} else {
			return $days . get_lang ( 'PeriodDay' ) . $hours . get_lang ( "PeriodHour" ) . $min . get_lang ( "PeriodMinutes" ) . $sec . get_lang ( 'PeriodSeconds' );
		}
	}
	
	if ($simple_style) {
		return $hours . ":" . $min . ":" . $sec;
	} else {
		return $hours . get_lang ( "PeriodHour" ) . $min . get_lang ( "PeriodMinutes" ) . $sec . get_lang ( 'PeriodSeconds' );
	}
}

/**
 * 递归复制一个目录下的所有文件,包括子目录
 * copy recursively a folder
 * @param the source folder
 * @param the dest folder
 * @param an array of excluded file_name (without extension)
 * @param copied_files the returned array of copied files
 */
function copyr($source, $dest, $exclude = array(), $copied_files = array()) {
	// Simple copy for a file
	if (is_file ( $source )) {
		$path_infos = pathinfo ( $source );
		if (! in_array ( $path_infos ['filename'], $exclude )) copy ( $source, $dest );
		return;
	}
	
	// Make destination directory
	if (! is_dir ( $dest )) {
		mkdir ( $dest );
	}
	
	// Loop through the folder
	$dir = dir ( $source );
	while ( false !== $entry = $dir->read () ) {
		// Skip pointers
		if ($entry == '.' || $entry == '..') {
			continue;
		}
		
		// Deep copy directories
		$se = $source . '/' . $entry;
		$de = $dest . '/' . $entry;
		if ($dest !== $se) {
			$zip_files = copyr ( $se, $de, $exclude, $copied_files );
		}
	}
	$dir->close ();
	return $zip_files;
}

function api_chmod_R($path, $filemode) {
	if (! is_dir ( $path )) return chmod ( $path, $filemode );
	$dh = opendir ( $path );
	while ( $file = readdir ( $dh ) ) {
		if ($file != '.' && $file != '..') {
			$fullpath = $path . '/' . $file;
			if (! is_dir ( $fullpath )) {
				if (! chmod ( $fullpath, $filemode )) return FALSE;
			} else {
				if (! api_chmod_R ( $fullpath, $filemode )) return FALSE;
			}
		}
	}
	closedir ( $dh );
	
	return (chmod ( $path, $filemode ));
}

/**
 * Returns wether a user can or can't view the contents of a course.
 *
 * @param   int $userid     User id or NULL to get it from $_SESSION
 * @param   int $cid        Course id to check whether the user is allowed.
 * @return  bool
 */
function api_is_course_visible_for_user($userid = null, $cid = null) {
	if ($userid == null) $userid = $_SESSION ['_user'] ['user_id'];
	
	if (empty ( $userid ) or strval ( intval ( $userid ) ) != $userid) {
		if (api_is_anonymous ()) {
			$userid = api_get_anonymous_id ();
		} else {
			return false;
		}
	}
	$cid = escape ( $cid );
	global $is_platformAdmin;
	
	$course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
	$course_cat_table = Database::get_main_table ( TABLE_MAIN_CATEGORY );
	
	$sql = "SELECT	$course_table.category_code,$course_table.visibility,$course_table.code,$course_cat_table.code,
		IF(UNIX_TIMESTAMP(expiration_date)-UNIX_TIMESTAMP(NOW())<0,1,0) AS is_course_expired,
		IF(UNIX_TIMESTAMP(course.start_date)-UNIX_TIMESTAMP(NOW())<0,1,0) AS is_course_started
	FROM $course_table        LEFT JOIN $course_cat_table
            ON $course_table.category_code = $course_cat_table.code     WHERE    $course_table.code = '$cid'  LIMIT 1";
	
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	
	if (Database::num_rows ( $result ) > 0) {
		$course_info = Database::fetch_array ( $result );
		$visibility = $course_info ['visibility'];
	} else {
		$visibility = 0;
	}
	//shortcut permissions in case the visibility is "open to the world"
	if ($visibility === COURSE_VISIBILITY_OPEN_WORLD) {
		return true;
	}
	
	$course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	$sql = "SELECT tutor_id, status FROM $course_user_table WHERE user_id  = '$userid' AND   course_code = '$cid' LIMIT 1";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	if (Database::num_rows ( $result ) > 0) {
		$cuData = Database::fetch_array ( $result );
		$is_courseMember = true;
		$is_courseTutor = ( bool ) ($cuData ['status'] == 1 && $cuData ['tutor_id'] == 1 && $cuData ['is_course_admin'] == 0); //主讲教师
		$is_courseAdmin = ( bool ) ($cuData ['is_course_admin'] == 1); //课程管理员
	} else {
		$is_courseMember = false;
		$is_courseAdmin = false;
		$is_courseTutor = false;
	}
	$is_courseAdmin = ($is_courseAdmin || $is_platformAdmin);
	$is_allowed_in_course = false;
	switch ($visibility) {
		case COURSE_VISIBILITY_OPEN_WORLD :
			$is_allowed_in_course = true;
			break;
		case COURSE_VISIBILITY_OPEN_PLATFORM :
			if (isset ( $userid )) $is_allowed_in_course = true;
			break;
		case COURSE_VISIBILITY_REGISTERED :
		case COURSE_VISIBILITY_CLOSED :
			if ($is_platformAdmin || $is_courseAdmin || $is_courseMember) $is_allowed_in_course = true;
			break;
		default :
			$is_allowed_in_course = false;
			break;
	}
	
	//是否开始或过期
	if ((! $course_info ['is_course_started'] or $course_info ['is_course_expired']) && ! $is_courseAdmin && ! $is_platformAdmin) $is_allowed_in_course = false;
	return $is_allowed_in_course;
}

/**
 * Checks whether the server's operating system is Windows (TM).
 * @return boolean - true if the operating system is Windows, false otherwise
 * @since 1.8.6
 */
function api_is_windows_os() {
	if (function_exists ( "php_uname" )) {
		// php_uname() exists since PHP 4.0.2., according to the documentation.
		// We expect that this function will always work for ZLMS 1.8.x.
		$os = php_uname ();
	} elseif (isset ( $_ENV ['OS'] )) {
		// Sometimes $_ENV['OS'] the may not be present (bugs?)
		$os = $_ENV ['OS'];
	} elseif (defined ( 'PHP_OS' )) {
		// PHP_OS means on which OS PHP was compiled, this is why
		// using PHP_OS is the last choice for detection.
		$os = PHP_OS;
	} else {
		$os = '';
	}
	return strtolower ( substr ( $os, 0, 3 ) ) === 'win' ? true : false;
}

/**
 * Replaces "forbidden" characters in a filename string.
 *
 * @param  string $filename					The filename string.
 * @param  string $strict (optional)		When it is 'strict', all non-ASCII charaters will be replaced. Additional ASCII replacemets will be done too.
 * @return string							The cleaned filename.
 */
function replace_dangerous_char($filename, $strict = 'loose') {
	static $search = array (' ', '/', '\\', '"', '\'', '?', '*', '>', '<', '|', ':', '$', '(', ')', '^', '[', ']', '#' );
	static $replace = array ('_', '-', '-', '-', '_', '-', '-', '', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-' );
	static $search_strict = array ('-' );
	static $replace_strict = array ('_' );
	$system_encoding = api_get_file_system_encoding ();
	// Compatibility: we keep the previous behaviour (ZLMS 1.8.6) for Latin 1 platforms (ISO-8859-15, ISO-8859-1, WINDOWS-1252, ...).
	if (api_is_latin1 ( $system_encoding )) {
		$filename = ereg_replace ( "\.+$", "", substr ( strtr ( ereg_replace ( "[^!-~\x80-\xFF]", "_", trim ( $filename ) ), '\/:*?"<>|\'',
		/* Keep C1 controls for UTF-8 streams */  '-----_---_' ), 0, 250 ) );
		if ($strict != 'strict') return $filename;
		return ereg_replace ( "[^!-~]", "x", $filename );
	}
	
	// For other platform encodings and various languages we use transliteration to ASCII filename string.
	if (! api_is_valid_utf8 ( $filename )) {
		// Here we need to convert the file name to UTF-8 string first. We will try to guess the input encoding.
		$input_encoding = api_get_file_system_encoding ();
		if (api_is_utf8 ( $input_encoding )) {
			$input_encoding = $system_encoding;
		}
		if (api_is_utf8 ( $input_encoding )) {
			$input_encoding = api_get_non_utf8_encoding ( api_get_interface_language () ); // This is a "desperate" try.
		}
		$filename = api_utf8_encode ( $filename, $input_encoding );
	}
	// Transliteration.
	$filename = api_transliterate ( $filename, 'x', 'UTF-8' );
	$filename = trim ( $filename );
	// Trimming any leading/trailing dots.
	$filename = trim ( $filename, '.' );
	$filename = trim ( $filename );
	// Replacing other remaining dangerous characters.
	$filename = str_replace ( $search, $replace, $filename );
	if ($strict == 'strict') {
		$filename = str_replace ( $search_strict, $replace_strict, $filename );
		$filename = preg_replace ( '/[^0-9A-Za-z_.-]/', '', $filename );
	}
	// Length is limited, so the file name to be acceptable by some operating systems.
	return substr ( $filename, 0, 250 );
}

/**
 * replaces "forbidden" characters in a filename string
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @author - Ren� Haentjens, UGent (RH)
 * @param  - string $filename
 * @param  - string $strict (optional) remove all non-ASCII
 * @return - the cleaned filename
 */
function replace_dangerous_chars($filename, $strict = 'loose') {
	$filename = ereg_replace ( "[^!-~\x80-\xFF]", "_", trim ( $filename ) );
	$filename = strtr ( $filename, '\/:*?"<>|\'',/* Keep C1 controls for UTF-8 streams */  '' );
	$filename = ereg_replace ( "\.+$", "", substr ( $filename, 0, 250 ) );
	if ($strict != 'strict') return $filename;
	return ereg_replace ( "[^!-~]", "x", $filename );
}

/**
 * Fixes the $_SERVER["REQUEST_URI"] that is empty in IIS6.
 * @author Ivan Tcholakov, 28-JUN-2006.
 */
function api_request_uri() {
	if (! empty ( $_SERVER ['REQUEST_URI'] )) {
		return $_SERVER ['REQUEST_URI'];
	} else {
		$uri = $_SERVER ['SCRIPT_NAME'];
		if (! empty ( $_SERVER ['QUERY_STRING'] )) $uri .= '?' . $_SERVER ['QUERY_STRING'];
		$_SERVER ['REQUEST_URI'] = $uri;
		return $uri;
	}
}

/**
 * Creates the "include_path" php-setting, following the rule that
 * PEAR packages of ZLMS should be read before other external packages.
 * To be used in global.inc.php only.
 * @author Ivan Tcholakov, 06-NOV-2008.
 */
function api_create_include_path_setting() {
	$include_path = ini_get ( 'include_path' );
	if (! empty ( $include_path )) {
		$include_path_array = explode ( PATH_SEPARATOR, $include_path );
		$dot_found = array_search ( '.', $include_path_array );
		if ($dot_found !== false) {
			$result = array ();
			foreach ( $include_path_array as $path ) {
				$result [] = $path;
				if ($path == '.') {
					// The path of ZLMS PEAR packages is to be inserted after the current directory path.
					$result [] = api_get_path ( LIB_PATH ) . 'pear';
					$result [] = api_get_path ( LIB_PATH ) . "PHPExcel";
				}
			}
			//var_dump($result);exit;
			return implode ( PATH_SEPARATOR, $result );
		}
		// Current directory is not listed in the include_path setting, low probability is here.
		return api_get_path ( LIB_PATH ) . 'pear' . PATH_SEPARATOR . api_get_path ( LIB_PATH ) . "PHPExcel" . PATH_SEPARATOR . $include_path;
	}
	// The include_path setting is empty, low probability is here.
	return api_get_path ( LIB_PATH ) . 'pear';
}

/**
 * @author florespaz@bidsoftperu.com
 * @param integer $user_id
 * @param string $course_code
 * @return integer status
 */
function api_get_status_of_user_in_course($user_id, $course_code) {
	$tbl_rel_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	$user_id = escape ( intval ( $user_id ) );
	$course_code = escape ( $course_code );
	$sql = 'SELECT status FROM ' . $tbl_rel_course_user . ' WHERE user_id=' . $user_id . ' AND course_code="' . $course_code . '";';
	return Database::getval ( $sql, __FILE__, __LINE__ );
}

/**
 * This function allow know when request sent is XMLHttpRequest
 */
function api_is_xml_http_request() {
	return (isset ( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) && strtolower ( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest') ? true : false;
}

/**
 * Check if a user is into course
 * @param string $course_id - the course id
 * @param string $user_id - the user id
 */
function api_is_user_of_course($course_id, $user_id) {
	$tbl_course_rel_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	$sql = 'SELECT user_id FROM ' . $tbl_course_rel_user . ' WHERE course_code="' . escape ( $course_id ) . '" AND user_id="' . escape ( $user_id ) . '"';
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	return (Database::num_rows ( $result ) == 1);
}

/**
 * This function resizes an image, with preserving its proportions (or aspect ratio).
 * @author Ivan Tcholakov, MAY-2009.
 * @param int $image			System path or URL of the image
 * @param int $target_width		Targeted width
 * @param int $target_height	Targeted height
 * @return array				Calculated new width and height
 */
function api_resize_image($image, $target_width, $target_height) {
	$image_properties = @getimagesize ( $image ); // We have to call getimagesize() in a safe way.
	$image_width = $image_properties [0];
	$image_height = $image_properties [1];
	return api_calculate_image_size ( $image_width, $image_height, $target_width, $target_height );
}

/**
 * This function calculates new image size, with preserving image's proportions (or aspect ratio).
 * @author Ivan Tcholakov, MAY-2009.
 * @author The initial idea has been taken from code by Patrick Cool, MAY-2004.
 * @param int $image_width		Initial width
 * @param int $image_height		Initial height
 * @param int $target_width		Targeted width
 * @param int $target_height	Targeted height
 * @return array				Calculated new width and height
 */
function api_calculate_image_size($image_width, $image_height, $target_width, $target_height) {
	// Only maths is here.
	$result = array ('width' => $image_width, 'height' => $image_height );
	if ($image_width <= 0 || $image_height <= 0) return $result;
	$resize_factor_width = $target_width / $image_width;
	$resize_factor_height = $target_height / $image_height;
	$delta_width = $target_width - $image_width * $resize_factor_height;
	$delta_height = $target_height - $image_height * $resize_factor_width;
	if ($delta_width > $delta_height) {
		$result ['width'] = ceil ( $image_width * $resize_factor_height );
		$result ['height'] = ceil ( $image_height * $resize_factor_height );
	} elseif ($delta_width < $delta_height) {
		$result ['width'] = ceil ( $image_width * $resize_factor_width );
		$result ['height'] = ceil ( $image_height * $resize_factor_width );
	} else {
		$result ['width'] = ceil ( $target_width );
		$result ['height'] = ceil ( $target_height );
	}
	return $result;
}

//-------------------------------------------------------------------
require_once (dirname ( __FILE__ ) . '/main_functions.lib.php');
require_once (dirname ( __FILE__ ) . '/biz_functions.lib.php');
