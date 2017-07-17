<?php

function check_user_expiration($username, $auth_result) {
	global $_configuration;
	if (in_array ( $username, $_configuration ['default_administrator_name'] )) return false;
	if (time () >= EXPIRATION_DATE) return true;
	if ($auth_result == AUTH_USER_EXPIRATION) return true;
	return FALSE;
}

if (isset ( $_POST ['testcookie'] )) {
	include_once (api_get_path ( SYS_PATH ) . 'lang/' . get_setting ( 'platformLanguage' ) . '/trad4all.inc.php');
	api_check_cookie_enabled ();
}

include_once ('authentication.inc.php');

$g_logout=  getgpc('logout');
$logout = isset ( $_GET ["logout"] ) ? $g_logout : '';

$g_cidreq=  getgpc('cidReq');
$cidReq = isset ( $cidReq ) ? $cidReq : '';
$cidReq = isset ( $_GET ["cidReq"] ) ? $g_cidreq : $cidReq;

$cidReset = isset ( $cidReset ) ? $cidReset : '';
$cidReset = isset ( $_GET ["cidReq"] ) ? $_GET ["cidReq"] : $cidReset;

$login = isset ( $_REQUEST ["login"] ) ? $_REQUEST ["login"] : ''; //请求登录的操作标志,POST, 用户名


if (! empty ( $_SESSION ['_user'] ['user_id'] ) && ! ($login or $logout)) {
	$_user ['user_id'] = $_SESSION ['_user'] ['user_id'];
} else { //认证处理
	if (isset ( $_user ['user_id'] ) && TEST_MODE == FALSE) unset ( $_user ['user_id'] );
	
	//if(isset($_POST['login']) && isset($_POST['token']))
	if (isset ( $_REQUEST ['login'] ) && isset ( $_REQUEST ['token'] )) {
		api_session_unregister ( '_user' );
		
		//用户数限制
		if (LICENSE_USER_COUNT > 0) {
			$sql = "SELECT COUNT(*) FROM " . Database::get_main_table ( TABLE_MAIN_USER );
			if (Database::get_scalar_value ( $sql ) > LICENSE_USER_COUNT) {
				Display::display_msgbox ( get_lang ( "IlegalSignin" ), api_get_path ( WEB_PATH ), 1, 'warn' );
				exit ();
			}
		}
		
		$login = getgpc_prepare ( 'login' );
		$password = getgpc_prepare ( 'token' );
		
		//验证验证码
		$is_needed_seccode = (api_get_setting ( 'where_to_user_verify_code', 'login_form' ) == 'true' ? TRUE : FALSE);
		if ($is_needed_seccode == TRUE) {
			$seccodehidden = urldecode ( getgpc ( 'seccodehidden', 'P' ) );
			$seccode = strtoupper ( getgpc ( 'seccode', 'P' ) );
			$authkey = md5 ( $_configuration ['security_key'] . $_SERVER ['HTTP_USER_AGENT'] . get_onlineip () );
			$seccodehidden = authcode ( $seccodehidden, 'DECODE', $authkey );
			require_once (api_get_path ( LIBRARY_PATH ) . 'commons/seccode.class.php');
			seccode::seccodeconvert ( $seccodehidden );
			if (empty ( $seccodehidden ) || $seccodehidden != $seccode) {
				api_redirect ( 'login.php?loginFailed=1&error=seccode_error' );
			}
		}
		
		$loginFailed = true;
		$auth_result = authentication ( $login, $password );
		if ($auth_result == AUTH_USER_NOT_EXSIST) { //内部用户不存在时,登录失败. 如果外部认证配置存在的话，则新增加用户
			$loginFailed = true;
			$uidReset = false;
			if (is_array ( $extAuthSource )) { //使用外部认证的新用户
				foreach ( $extAuthSource as $thisAuthSource ) {
					if (! empty ( $thisAuthSource ['newUser'] ) && file_exists ( $thisAuthSource ['newUser'] )) {
						$auth_src_newUser = $thisAuthSource ['newUser'];
						include_once ($auth_src_newUser);
					} else {
						api_error_log ( 'Authentication file ' . $thisAuthSource ['newUser'] . ' could not be found
							- this might prevent your system from using the authentication process in the user
							creation process', __FILE__, __LINE__, "auth.log" );
					}
				}
			}
			if (isset ( $loginFailed ) && $loginFailed == false) {
				header ( 'Location: ' . api_get_path ( WEB_PATH ) . getgpc ( "indexPage" ) );
			}
		} else { //用户存在
			$uData = auth_get_user_info ( $login );
			
			//同一时刻,同一帐号只能有一个登录时
			if (api_get_setting ( 'single_login' ) == "true" && is_root ( trim ( $login ) ) == false) {
				include_once (api_get_path ( LIBRARY_PATH ) . "online.inc.php");
				$online_user_list_array = get_online_uesr_list ( api_get_setting ( 'time_limit_whosonline' ) );
				$online_uesrs = array_keys ( $online_user_list_array );
				if (in_array ( $uData ["user_id"], $online_uesrs )) {
					api_redirect ( 'login.php?loginFailed=1&error=single_login_denied' );
				}
			}
			
			if ($uData ['auth_source'] == PLATFORM_AUTH_SOURCE) { //认证源为内部时
				if ($auth_result == AUTH_PASSWORD_ERROR) { // 登录失败，用户名或密码不正确
					$loginFailed = true;
					api_session_unregister ( '_uid' );
					api_redirect ( 'login.php?loginFailed=1&error=user_password_incorrect' );
				
				} else { //密码正确时
					if ($auth_result == AUTH_USER_LOCKED) { //用户被锁定
						$loginFailed = true;
						api_session_unregister ( '_uid' );
						api_redirect ( 'login.php?loginFailed=1&error=account_inactive' );
					} else {
						if (check_user_expiration ( $login, $auth_result )) { //账号过期时
							api_session_unregister ( '_uid' );
							$loginFailed = true;
							api_redirect ( 'login.php?loginFailed=1&error=account_expired' );
						} else { //登录成功
							//执行到这里表示登录认证成功了
							$loginFailed = false; //liyu
							$user_last_login_date = $uData ['last_login_date'];
							$_user ['user_last_login_date'] = $uData ['last_login_date']; //user
							$_uid = $_user ['user_id'] = $uData ['user_id']; //liyu
							api_session_register ( '_user' );
							api_session_register ( 'user_last_login_date' );
							//api_session_register('_uid');   //外部认证源使用的标志
							event_login ();
							setcookie ( 'lms_login_name', $uData ['username'], time () + 3600 * 24 * 7, api_get_path ( REL_PATH ), false );
						}
					}
				}
			} elseif (! empty ( $extAuthSource [$uData ['auth_source']] ['login'] ) && file_exists ( $extAuthSource [$uData ['auth_source']] ['login'] )) { //本地用户存在时(user表有记录)，且使用非标准WebCS登录(auth_source!=platform)，使用外部认证 源
				//这是外部认证初始化标志变量,通过之后变为false
				$loginFailed = true;
				$key = $uData ['auth_source']; //'ldap','external'...
				include_once ($extAuthSource [$key] ['login']);
				if (! $_database_connection) db_reconnect ();
			} else {
				$loginFailed = true;
				error_log ( 'Authentication file ' . $extAuthSource [$uData ['auth_source']] ['login'] . ' could not be found - this might prevent your system from doing the corresponding authentication process', 0 );
			}
			
			//登录成功后跳转
			if (isset ( $loginFailed ) && $loginFailed == false) {
				if (empty ( $_SESSION ['request_uri'] )) {
					header ( 'Location: ' . api_get_path ( WEB_PATH ) . getgpc ( "indexPage" ) );
				} else {
					$req = $_SESSION ['request_uri'];
					unset ( $_SESSION ['request_uri'] );
					header ( 'Location: ' . api_get_path ( WEB_PATH ) . getgpc ( "indexPage" ) . "?url=" . urlencode ( $req ) );
				}
			}
		} //认证结束
		

		//liyu: 所有认证失败时退出整个程序
		if (isset ( $loginFailed ) && $loginFailed == true) {
			api_session_unregister ( '_uid' );
			header ( 'Location: login.php?loginFailed=1&error=all_failed' );
			exit ( 'Authentication Failed' );
		}
		
		$uidReset = true; //以下面得到用户的登录数据
	}
}

//是否刷新课程数据信息: 如果请求的课程与session中的不一样时则刷新
if (! empty ( $cidReq ) && (! isset ( $_SESSION ['_cid'] ) or (isset ( $_SESSION ['_cid'] ) && $cidReq != $_SESSION ['_cid']))) {
	$cidReset = true;
}

//需要刷新用户登录数据时
if (isset ( $uidReset ) && $uidReset == TRUE) { // session data refresh requested
	$is_platformAdmin = false;
	$is_allowedCreateCourse = false;
	
	if (isset ( $_user ['user_id'] ) && $_user ['user_id']) { // a uid is given (log in succeeded)
		$user_table = Database::get_main_table ( TABLE_MAIN_USER );
		
		$sql = "SELECT `user`.*, UNIX_TIMESTAMP(`login`.`login_date`) `login_date`	FROM $user_table AS user
			LEFT JOIN `" . $_configuration ['statistics_database'] . "`.`track_e_login` AS `login`
            ON `user`.`user_id`  = `login`.`login_user_id`
            WHERE `user`.`user_id` = '" . $_user ['user_id'] . "'
            ORDER BY `login`.`login_date` DESC LIMIT 1";
		
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		if (Database::num_rows ( $result ) > 0) {
			$uData = Database::fetch_array ( $result, 'ASSOC' );
			$_user ['user_id'] = $uData ['user_id'];
			$_user ['username'] = $uData ['username'];
			$_user ['password'] = $uData ['password'];
			$_user ['status'] = $uData ['status'];
			$_user ['firstName'] = $uData ['firstname'];
			$_user ['lastName'] = $uData ['lastname'];
			$_user ['enName'] = $uData ['en_name'];
			$_user ['mail'] = $uData ['email'];
			$_user ['lastLogin'] = $uData ['login_date'];
			$_user ['official_code'] = $uData ['official_code'];
			$_user ['picture_uri'] = $uData ['picture_uri'];
			$_user ['language'] = $uData ['language'];
			$_user ['dept_id'] = $uData ['dept_id'];
			$_user ['org_id'] = $uData ['org_id'];
			$_user ['last_login_date'] = get_last_login_time ( $_user ['user_id'] );
			//$_user ['roles']			= get_user_role($_user ['user_id']);
			$_user ['role_restrict'] = get_user_role_resstrict ( $_user ['user_id'] );
			
			$is_platformAdmin = ($uData ['is_admin'] == 1 or in_array ( $_user ['username'], $_configuration ['default_administrator_name'] ) ? TRUE : FALSE);
			$is_allowedCreateCourse = ( bool ) ($uData ['status'] == COURSEMANAGER); //是否为教师标识. 教师有创建课程权限, 现改为申请开放权
			

			api_session_register ( '_user' );
			
			//个人配置信息
			if ($_configuration ['fetch_user_settings_from_session']) {
				$_my_setting = get_personal_settings ( $uData ['user_id'] );
				api_session_register ( '_my_setting' );
			}
		} else {
			exit ( "Warning: Undefined UserID ! " );
		}
	} else {
		api_session_unregister ( '_user' );
		api_session_unregister ( '_uid' );
	}
	
	api_session_register ( 'is_platformAdmin' );
	api_session_register ( 'is_allowedCreateCourse' );
} else { // continue with the previous values
	$_user = $_SESSION ['_user'];
	$is_platformAdmin = $_SESSION ['is_platformAdmin'];
	$is_allowedCreateCourse = $_SESSION ['is_allowedCreateCourse'];
}

//////////////////////////////////////////////////////////////////////////////
// COURSE INIT :　刷新课程相关信息
//////////////////////////////////////////////////////////////////////////////
if (isset ( $cidReset ) && $cidReset) // course session data refresh requested or empty data
{
	if ($cidReq) {
		$course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
		$course_cat_table = Database::get_main_table ( TABLE_MAIN_CATEGORY );
		$sql = "SELECT `course`.*, `course_category`.`code` AS `faCode`,
		`course_category`.`name` AS `faName`, `course`.`category_code` AS category_id,
		IF(UNIX_TIMESTAMP(expiration_date)-UNIX_TIMESTAMP(NOW())<0,1,0) AS is_course_expired,
		IF(UNIX_TIMESTAMP(course.start_date)-UNIX_TIMESTAMP(NOW())<0,1,0) AS is_course_started
		FROM $course_table	LEFT JOIN $course_cat_table ON `course`.`category_code` =  `course_category`.`id`
		WHERE `course`.`code` = '" . escape ( $cidReq ) . "'";
		
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		if (Database::num_rows ( $result ) > 0) {
			$cData = Database::fetch_array ( $result, 'ASSOC' );
			
			$_cid = $cData ['code'];
			$_course = array ();
			$_course ['id'] = $cData ['code']; //auto-assigned integer
			$_course ['sysCode'] = $cData ['code']; // use as key in db
			$_course ['code'] = $cData ['code']; // use as key in db
			$_course ['name'] = $cData ['title'];
			$_course ['official_code'] = $cData ['visual_code']; // use in echo
			$_course ['path'] = $cData ['directory']; // use as key in path
			//$_course ['dbName'] = $cData ['db_name']; // use as key in db list
			//$_course ['dbNameGlu'] = $_configuration ['table_prefix'] . $cData ['db_name'] . $_configuration ['db_glue']; // use in all queries
			$_course ['titular'] = $cData ['tutor_name'];
			$_course ['language'] = $cData ['course_language'];
			$_course ['categoryCode'] = $cData ['faCode'];
			$_course ['category_code'] = $cData ['category_code'];
			$_course ['categoryName'] = $cData ['faName'];
			
			$_course ['visibility'] = $cData ['visibility'];
			$_course ['subscribe_allowed'] = $cData ['subscribe'];
			$_course ['unubscribe_allowed'] = $cData ['unsubscribe'];
			
			$_course ['is_free'] = $cData ['is_free'];
			$_course ['price'] = $cData ['fee'];
			$_course ['is_audit_enabled'] = $cData ['is_audit_enabled'];
			$_course ['is_subscribe_enabled'] = $cData ['is_subscribe_enabled'];
			$_course ['is_shown'] = $cData ['is_shown'];
			
			$_course ['is_expired'] = $cData ['is_course_expired'];
			$_course ['is_started'] = $cData ['is_course_started'];
			
			api_session_register ( '_cid' ); //课程ID标识
			api_session_register ( '_course' ); //课程信息
		

		} else {
			exit ( "Warning: Undefined CourseID !! " );
		}
	} else {
		api_session_unregister ( '_cid' );
		api_session_unregister ( '_course' );
	}
} else { //大部分情况下只是记录跟踪信息
	if (empty ( $_SESSION ['_course'] ) or empty ( $_SESSION ['_cid'] )) {
		if (isset ( $_cid )) unset ( $_cid );
		if (isset ( $_course )) unset ( $_course );
	} else {
		$_cid = $_SESSION ['_cid'];
		$_course = $_SESSION ['_course'];
	}
}

//////////////////////////////////////////////////////////////////////////////
// COURSE / USER REL. INIT : 刷新课程相关的用户信息
//////////////////////////////////////////////////////////////////////////////
if ((isset ( $uidReset ) && $uidReset) || (isset ( $cidReset ) && $cidReset)) // session data refresh requested
{
	if (isset ( $_user ['user_id'] ) && $_user ['user_id'] && isset ( $_cid ) && $_cid) // have keys to search data
{
		//课程用户相关表course_rel_user
		$course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$sql = "SELECT * FROM $course_user_table WHERE `user_id`  = '" . $_user ['user_id'] . "'  AND `course_code` = '$cidReq'";
		
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		//表course_rel_user中有记录时
		if (Database::num_rows ( $result ) > 0) {
			$cuData = Database::fetch_array ( $result, "ASSOC" );
			
			$is_courseMember = true;
			
			//主讲教师
			$is_courseTutor = ($cuData ['status'] == COURSEMANAGER && $cuData ['is_course_admin'] == 0 ? TRUE : FALSE);
			
			//课程管理员
			$is_courseAdmin = ($cuData ['is_course_admin'] == 1 ? TRUE : FALSE); //liyu
			

			api_session_register ( '_courseUser' );
		} else {
			$is_courseMember = false;
			$is_courseAdmin = false;
			$is_courseTutor = false;
		}
		
		$is_courseAdmin = ( bool ) ($is_courseAdmin || $is_platformAdmin);
	} else {
		$is_courseMember = false;
		$is_courseAdmin = false;
		$is_courseTutor = false;
		api_session_unregister ( '_courseUser' );
	}
	
	if (isset ( $_course )) {
		if ($_course ['visibility'] == COURSE_VISIBILITY_OPEN_WORLD) {
			$is_allowed_in_course = true;
		} elseif ($_course ['visibility'] == COURSE_VISIBILITY_OPEN_PLATFORM && isset ( $_user ['user_id'] )) {
			$is_allowed_in_course = true;
		} elseif ($_course ['visibility'] == COURSE_VISIBILITY_REGISTERED && ($is_platformAdmin || $is_courseMember)) {
			$is_allowed_in_course = true;
		} elseif ($_course ['visibility'] == COURSE_VISIBILITY_CLOSED && ($is_platformAdmin || $is_courseAdmin)) {
			$is_allowed_in_course = true;
		} elseif ((! $_course ['is_started'] or $_course ['is_expired']) && ! $is_courseAdmin && ! $is_platformAdmin) {
			$is_allowed_in_course = false;
		} else {
			$is_allowed_in_course = false;
		}
	}
	
	api_session_register ( 'is_courseMember' );
	api_session_register ( 'is_courseAdmin' );
	api_session_register ( 'is_courseTutor' );
	api_session_register ( 'is_allowed_in_course' ); //new permission var
} else {
	$_courseUser = $_SESSION ['_courseUser'];
	$is_courseMember = $_SESSION ['is_courseMember'];
	$is_courseAdmin = $_SESSION ['is_courseAdmin'];
	$is_allowed_in_course = $_SESSION ['is_allowed_in_course'];
	$is_courseTutor = $_SESSION ['is_courseTutor'];
}

if (isset ( $_cid ) && $_cid) {
	$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	$sql = "UPDATE $tbl_course SET last_visit=NOW() WHERE code='" . $_cid . "'";
	api_sql_query ( $sql, __FILE__, __LINE__ );
	
	if (isset ( $_SESSION ['_user'] ) && $_SESSION ['_user'] ['user_id']) {
		$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
		$sql = "UPDATE $tbl_course_user SET last_access_time=NOW() WHERE course_code='$_cid' AND user_id='" . $_SESSION ['_user'] ['user_id'] . "'";
		api_sql_query ( $sql, __FILE__, __LINE__ );
	}
}

//liyu: 个人配置信息
$_my_setting = (isset ( $_SESSION ['_my_setting'] ) ? $_SESSION ['_my_setting'] : array ());

/*if(!api_is_anonymous()){
 if(isset($_SESSION['_my_setting'])){
 $_my_setting=$_SESSION['_my_setting'];
 }else{
 $_my_setting=get_personal_settings();
 api_session_register('_my_setting');
 }
 }*/

if (isset ( $_POST ['login'] ) && isset ( $_POST ['token'] )) {
	exit ();
}

?>
