<?php

/**
 * V1.4 连接到主数据库
 */
function db_reconnect() {
	global $_configuration, $_database_connection;
	global $_db, $db;
	$_db = new db ();
	$_db->connect ( $_configuration ['db_host'], $_configuration ['db_user'], $_configuration ['db_password'], $_configuration ['main_database'], "utf8", 0, "", time () );
	$_database_connection = $_db->link;
	$db = $_db;
	return $_db;
}

function mysql_reconnect() {
	
	global $_configuration, $_database_connection;
	if (empty ( $_configuration ['db_host'] ) or empty ( $_configuration ['db_user'] )) {
		die ( "DB Connection Information is MISSED!" );
	}
	
	$_database_connection = @mysql_connect ( $_configuration ['db_host'], $_configuration ['db_user'], $_configuration ['db_password'] ) or die ( mysql_error () );
	
	//liyu
	if (! function_exists ( 'mysql_set_charset' )) {
		mysql_query ( "set names utf8;" );
	} else {
		mysql_set_charset ( 'utf8', $_database_connection );
	}
	
	mysql_select_db ( $_configuration ['main_database'], $_database_connection ) or die ( '<center><h2>ERROR ! Connect the Main Database Failed,Maybe it is not Existed!</h2></center>' );
}

function get_platform_settings() {
	//global $_setting;
	$sql = "SELECT SQL_CACHE variable,subkey,selected_value FROM settings_current";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	while ( $row = mysql_fetch_array ( $result ) ) {
		if ($row ['subkey'] == NULL || empty ( $row ['subkey'] )) {
			$_setting [$row ['variable']] = $row ['selected_value'];
		} else {
			$_setting [$row ['variable']] [$row ['subkey']] = $row ['selected_value'];
		}
	}
	return $_setting;
}

function get_platform_language() {
	global $_configuration;
	$user_language = $_GET ["language"];
	
	if ($_POST ["language_list"]) {
		if (substr ( $_configuration ['url_append'], strlen ( $_configuration ['url_append'] ) - 1 ) != "/") $url_applend_tmp = $_configuration ['url_append'] . "/";
		$user_language = str_replace ( $url_applend_tmp . "login.php?language=", "", $_POST ["language_list"] );
	}
	
	// Checking if we have a valid language. If not we set it to the platform language.
	$valid_languages = api_get_languages ();
	if (! in_array ( $user_language, $valid_languages ['folder'] )) {
		$user_language = get_setting ( 'platformLanguage' );
	}
	
	if (in_array ( $user_language, $valid_languages ['folder'] ) and (isset ( $_GET ['language'] ) or isset ( $_POST ['language_list'] ))) {
		$user_selected_language = $user_language; // $_GET["language"];
		$_SESSION ["user_language_choice"] = $user_selected_language;
		$platformLanguage = $user_selected_language;
	}
	
	if (isset ( $_SESSION ["user_language_choice"] )) {
		$language_interface = $_SESSION ["user_language_choice"];
	} else {
		$language_interface = get_setting ( 'platformLanguage' );
	}
	
	if (isset ( $_user ['language'] )) {
		//$language_interface = $_user['language'];
	}
	
	if ($_course ['language']) {
		//$language_interface = $_course['language'];
	}
	//语言选择的优先级为(小到大):下拉的选择->平台语言->Session中语言->用户语言->课程语言
	return $language_interface;
}

function get_personal_settings($user_id = 0) {
	$_my_setting = array ();
	if ($user_id) {
		$tbl_my_settings = Database::get_user_personal_table ( TABLE_PERSONAL_SETTINGS );
		$tbl_settings_main = Database::get_user_personal_table ( TABLE_PERSONAL_SETTINGS_MAIN );
		
		$my_setting_sql_default = "SELECT SQL_CACHE * FROM " . $tbl_settings_main . " WHERE enabled=1";
		$result1 = api_sql_query ( $my_setting_sql_default, __FILE__, __LINE__ );
		while ( $row1 = Database::fetch_array ( $result1, "ASSOC" ) ) {
			if ($row1 ['subkey'] == NULL || empty ( $row1 ['subkey'] )) {
				$_my_setting [$row1 ['variable']] = $row1 ['default_value'];
			} else {
				$_my_setting [$row1 ['variable']] [$row1 ['subkey']] = $row1 ['default_value'];
			}
		}
		
		$my_setting_sql = "SELECT * FROM " . $tbl_my_settings . " WHERE user_id='" . Database::escape_string ( $user_id ) . "'";
		$result2 = api_sql_query ( $my_setting_sql, __FILE__, __LINE__ );
		while ( $row2 = Database::fetch_array ( $result2, "ASSOC" ) ) {
			//echo $sql;
			if ($row2 ['setting_subkey'] == NULL || empty ( $row2 ['setting_subkey'] )) {
				$_my_setting [$row2 ['setting_key']] = $row2 ['setting_value'];
			} else {
				$_my_setting [$row2 ['setting_key']] [$row2 ['setting_subkey']] = $row2 ['setting_value'];
			}
		}
	}
	return $_my_setting;
}

function get_last_login_time($user_id) {
	$tbl_track_login = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LOGIN );
	$sql = "select login_date from " . $tbl_track_login . " WHERE login_user_id='" . Database::escape_string ( $user_id ) . "' ORDER BY login_id DESC LIMIT 2";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	
	if (Database::num_rows ( $result ) != 2)
		return false; //从来没有登录
	else {
		return mysql_result ( $result, 1 ); //上次登录时间
	}
}

function get_user_role($user_id = 0) {
	$view_user_role = Database::get_main_table ( VIEW_USER_ROLE );
	$table_user = Database::get_main_table ( TABLE_MAIN_USER );
	if ($user_id) {
		//$sql="SELECT * FROM $table_admin WHERE user_id='".Database::escape_string($user_id)."'";
		$sql = "SELECT is_admin FROM " . $table_user . " WHERE user_id=" . Database::escape ( $user_id );
		if (Database::get_scalar_value ( $sql ) == 1) return ROLE_SUPER_ADMIN;
		
		$sql = "SELECT role_nums FROM " . $view_user_role . " WHERE user_id='" . Database::escape_string ( $user_id ) . "'";
		$user_role = Database::get_scalar_value ( $sql );
		return ($user_role ? $user_role : ROLE_USER);
	}
	return ROLE_USER;
}

function get_user_role_resstrict($user_id = 0) {
	if ($user_id) {
		$tbl_user_role = Database::get_main_table ( TABLE_MAIN_USER_ROLE );
		$sql = "SELECT role_no,department_id FROM $tbl_user_role WHERE user_id=" . Database::escape ( $user_id );
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		while ( $row = Database::fetch_array ( $result, "ASSOC" ) ) {
			$role_org [$row ['role_no']] = $row ['org_id'];
		}
		return empty ( $role_org ) ? array (ROLE_USER => "" ) : $role_org;
	}
	return FALSE;
}

function validate_role_base_permision($required_roles) {
	//var_dump($_SESSION['_user']['role_restrict']);
	if (empty ( $required_roles ) or empty ( $_SESSION ['_user'] ['role_restrict'] )) return FALSE;
	
	if (api_is_platform_admin ()) {
		return TRUE;
	} else {
		$user_auth_roles = array_keys ( $_SESSION ['_user'] ['role_restrict'] );
		//$needed_roles=explode(",",$required_roles); var_dump($needed_roles);
		//$user_auth_roles=explode(",",$_user['roles']);
		foreach ( $user_auth_roles as $role ) {
			if (in_array ( $role, $required_roles )) return TRUE;
		}
		return FALSE;
	
	}
}

function is_display_menu_item($menu_required_roles, $menu_required_status) {
	if (api_is_platform_admin ()) return TRUE;
	
	if (empty ( $menu_required_roles ) or empty ( $_SESSION ['_user'] ['role_restrict'] )) return FALSE;
	$has_priv = false;
	$menu_required_roles_arr = explode ( ",", $menu_required_roles );
	$user_roles = array_keys ( $_SESSION ['_user'] ['role_restrict'] ); //用户拥有的角色
	$user_roles [] = ROLE_USER;
	if ($menu_required_roles_arr && $user_roles) {
		foreach ( $user_roles as $role ) {
			if (in_array ( $role, $menu_required_roles_arr ) and ($menu_required_status == $_SESSION ['_user'] ['status'] or $menu_required_status == 0)) {
				$has_priv = true;
				break;
			}
		}
		return $has_priv;
	}
	return false;
}

function get_menu_item($parent_menu_no, $level = 2) {
	if (empty ( $_SESSION ['_user'] ['role_restrict'] )) return FALSE;
	$tbl_main_menu = Database::get_main_table ( TABLE_MAIN_MENU );
	if (api_is_platform_admin ()) {
		$sql = "SELECT * FROM " . $tbl_main_menu . " WHERE is_enabled=1 AND menu_no LIKE '" . $parent_menu_no . "__' ORDER BY menu_no ";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		return api_store_result_array ( $result );
	} else {
		$user_roles = array_keys ( $_SESSION ['_user'] ['role_restrict'] ); //用户拥有的角色
		$user_roles [] = ROLE_USER;
		$sql = "SELECT * FROM " . $tbl_main_menu . " WHERE is_enabled=1 AND menu_no LIKE '" . $parent_menu_no . "__'";
		$sql .= " AND (priv_status=0 OR priv_status='" . $_SESSION ['_user'] ['status'] . "') ";
		$sql .= " ORDER BY menu_no ";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		$rtn = array ();
		while ( $row = Database::fetch_array ( $result, "ASSOC" ) ) {
			//$menu_required_roles_arr = explode ( ",", $row ["priv_roles"] );
			//foreach ( $user_roles as $role ) {
			//if (in_array ( $role, $menu_required_roles_arr )) {
			$rtn [] = $row;
		
		//}
		//}
		}
		return $rtn;
	}
}

function get_restrict_org_dd($restrict_org_id) {
	require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
	$deptObj = new DeptManager ();
	if (api_is_platform_admin ()) {
		$all_org = $deptObj->get_all_org ();
		foreach ( $all_org as $org ) {
			$orgs [$org ['id']] = $org ['dept_name'];
		}
	} else {
		$org_info = $deptObj->get_dept_info ( $restrict_org_id );
		$orgs [$restrict_org_id] = $org_info ['dept_name'];
	}
	return $orgs;
}

function auth_get_user_info($username) {
	if ($username) {
		$user_table = Database::get_main_table ( TABLE_MAIN_USER );
		$username = trim ( $username );
		$sql = "SELECT user_id, username, password, auth_source, active, expiration_date,last_login_date
		FROM " . $user_table . " WHERE LOWER(username) = '" . escape ( strtolower ( $username ) ) . "'";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		return Database::fetch_array ( $result, "ASSOC" );
	}
	return false;
}

function get_learning_status($status) {
	switch ($status) {
		case LEARNING_STATE_COMPLETED :
			return get_lang ( "LearningState3" );
			break;
		case LEARNING_STATE_IMCOMPLETED :
			return get_lang ( "LearningState2" );
			break;
		case LEARNING_STATE_NOTATTEMPT :
			return get_lang ( "LearningState0" );
			break;
		case LEARNING_STATE_PASSED :
			return get_lang ( "LearningState1" );
			break;
		case LEARNING_STATE_FAILED :
			return get_lang ( "LearningState4" );
			break;
	}
	return "";
}

/**
 * 获取本人参与的所有课程
 * Database function that gets the list of courses for a particular user.
 * @param $user_id, the id of the user
 * @return an array with courses
 */
function get_personal_course_list($user_id, $sql_where = "") {
	$personal_course_list = array ();
	
	//$main_user_table 		= Database :: get_main_table(TABLE_MAIN_USER);
	$main_course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
	$main_course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	$personal_course_list = array ();
	
	$personal_course_list_sql = "SELECT course.code, course.directory, course.visual_code, course.db_name, course.title,
	course.tutor_name, course_rel_user.status,
	IF(UNIX_TIMESTAMP(course.expiration_date)-UNIX_TIMESTAMP(NOW())<0,1,0) AS is_course_expired,
	IF(UNIX_TIMESTAMP(course.start_date)-UNIX_TIMESTAMP(NOW())<0,1,0) AS is_course_started,
	CONCAT('" . get_lang ( "From" ) . "',DATE_FORMAT(start_date,'%y-%m-%d'),'" . get_lang ( "To" ) . "',DATE_FORMAT(expiration_date,'%y-%m-%d')) AS duration,
	course_rel_user.tutor_id,course_rel_user.is_course_admin,course_rel_user.creation_time,course.credit,
	course_rel_user.is_pass,course_rel_user.begin_date,course_rel_user.finish_date,course.credit_hours
	FROM    " . $main_course_table . " AS course," . $main_course_user_table . " AS course_rel_user
	WHERE course.code = course_rel_user.course_code	AND  course_rel_user.user_id = '" . $user_id . "' ";
	
	if ($sql_where) $personal_course_list_sql .= $sql_where;
	$personal_course_list_sql .= " ORDER BY course.code,course.title";
	
	//echo $personal_course_list_sql;
	$personal_course_list = api_sql_query_array_assoc ( $personal_course_list_sql, __FILE__, __LINE__ );
	
	return $personal_course_list;
}

function icon_href($icon, $lang_alt, $href, $target = '_self') {
	$html = '<a href="' . $href . '" title="' . get_lang ( $lang_alt ) . '" target="' . $target . '">' . Display::return_icon ( $icon, get_lang ( $lang_alt ), array ('style' => 'vertical-align: middle;' ) ) . '</a>';
	return $html;
}

function confirm_href($icon, $confirm_msg_key, $lang, $href) {
	$ext_attr = ' onclick="javascript:if(!confirm(' . "'" . addslashes ( htmlentities ( get_lang ( $confirm_msg_key ), ENT_NOQUOTES, SYSTEM_CHARSET ) ) . "'" . ')) return false;"';
	$html = '<a href="' . $href . '" title="' . get_lang ( $lang ) . '" target="_self" ' . $ext_attr . '>' . Display::return_icon ( $icon, get_lang ( $lang ), array ('style' => 'vertical-align: middle;' ) ) . '</a>';
	return $html;
}

function link_button($icon, $lang_alt, $href, $height, $width, $disp_link_txt = TRUE, $modal = TRUE) {
	if (isset ( $width ) && isset ( $height )) {
		$param = 'KeepThis=true&TB_iframe=true&modal=' . ($modal ? 'true' : 'false') . '&width=' . $width . '&height=' . $height;
		$url = api_add_url_param ( $href, $param );
		$html = '<a class="thickbox" href="' . $url . '" title="' . get_lang ( $lang_alt ) . '">' . ($icon ? Display::return_icon ( $icon, get_lang ( $lang_alt ), array ('style' => 'vertical-align: middle;', /*'width' => 22, 'height' => 22 */) ) : '') . ($disp_link_txt ? get_lang ( $lang_alt ) : '') . '</a>';
	} else {
		$html = '<a href="' . $href . '" title="' . get_lang ( $lang_alt ) . '">' . ($icon ? Display::return_icon ( $icon, get_lang ( $lang_alt ), array ('style' => 'vertical-align: middle;', /*'width' => 22, 'height' => 22 )*/) ) : '') . ($disp_link_txt ? get_lang ( $lang_alt ) : '') . '</a>';
	}
	return $html;
}

function tb_close($redirect_url = null) {
	if (empty ( $redirect_url )) {
		echo '<script>self.parent.location.reload();self.parent.tb_remove();</script>';
		exit ();
	} else {
		echo '<script>self.parent.location.href="' . $redirect_url . '";self.parent.tb_remove();</script>';
		exit ();
	}
}

function get_dept_path($dept_id, $is_contain_top = FALSE, $reverse = true) {
	$dept_path = '';
	if ($dept_id) {
		require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
		$objDept = new DeptManager ();
		$objDept->dept_path = "";
		$dept_path = $objDept->get_dept_path ( $dept_id, $is_contain_top );
		if ($dept_path) {
			$dept_path_arr = explode ( "/", $dept_path );
			if ($reverse) {
				$dept_path_arr = array_reverse ( $dept_path_arr );
				$dept_path = implode ( "/", $dept_path_arr );
			}
		}
	}
	return $dept_path;
}

function dispaly_intro_title($content) {
	$html = '<div class="actions actions_ext">';
	$html .= '<div class="actions_txt">';
	$html .= $content;
	$html .= '</div>';
	$html .= '</div>';
	return $html;
}

function can_do_my_bo($created_user) {
	$can_do = false;
	if (isRoot ())
		$can_do = TRUE;
	else {
		//只能操作自己创建的对象
		if (api_is_platform_admin () && $created_user == api_get_user_id ()) $can_do = TRUE;
	}
	return $can_do;
}

function email_body_txt_add(&$emailBody) {
	$emailBody .= "<br/><br/>" . get_setting ( 'siteName' ) . ' 访问网址: ' . api_get_path ( WEB_PATH ) . "<br/><br/>" . get_lang ( 'Problem' ) . "<br/>";
	$emailBody .= get_lang ( 'Manager' ) . ' : ' . api_get_setting ( 'administratorSurname' ) . ' ' . api_get_setting ( 'administratorName' ) . "<br/>";
	$emailBody .= get_lang ( 'Phone' ) . ' : ' . api_get_setting ( 'administratorTelephone' ) . "<br/>" . get_lang ( 'Email' ) . ' : ' . api_get_setting ( 'emailAdministrator' );
	$emailBody .= '<br/><br/><br/>谢谢你对我们工作的支持!';
}