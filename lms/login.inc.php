<?php
$cidReset = true;
$language_file = array ('index', 'courses' );
require_once ('main/inc/global.inc.php');
$loginFailed = isset ( $_GET ['loginFailed'] ) ? true : isset ( $loginFailed );

if ($_GET ['logout']) {
	logout ();
	if (is_equal ( $_GET ["logout"], "clear" )) {
		setcookie ( TEST_COOKIE, NULL, time () - 3600 );
		setcookie ( 'lms_login_name', NULL, time () - 3600 );
	}
}
//用户自身选择的语言,优先级最
if (! empty ( $_SESSION ['user_language_choice'] )) {
	$user_selected_language = $_SESSION ['user_language_choice'];
} elseif (! empty ( $_SESSION ['_user'] ['language'] )) {
	$user_selected_language = $_SESSION ['_user'] ['language'];
} else { //平台语言
	$user_selected_language = get_setting ( 'platformLanguage' );
}

$_browser = api_get_navigator ();
header ( "Content-Type: text/html;charset=" . SYSTEM_CHARSET );
if (! (isset ( $_POST ['login'] ) && isset ( $_POST ['password'] ))) {
	setcookie ( TEST_COOKIE, 'lms_cookie_checker', 0, api_get_path ( REL_PATH ), false );
}

function logout() {
	global $_configuration, $uid;
	$query_string = '';
	$tbl_track_login = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LOGIN );
	
	//$uid = intval($_GET['uid']);
	if (empty ( $uid )) $uid = api_get_user_id ();
	$sql = "SELECT login_id FROM " . $tbl_track_login . " WHERE login_user_id='" . $uid . "' ORDER BY login_date DESC LIMIT 1";
	$i_id_last_connection = Database::get_scalar_value ( $sql );
	if ($i_id_last_connection) {
		$s_sql_update_logout_date = "UPDATE " . $tbl_track_login . " SET logout_date=NOW() WHERE login_id='" . $i_id_last_connection . "'";
		api_sql_query ( $s_sql_update_logout_date );
	}
	
	LoginDelete ( $uid, $_configuration ['statistics_database'] );
	
	api_session_destroy ();
	api_redirect ( "login.php" . $query_string );
}

function handle_login_failed() {
	switch ($_GET ['error']) {
		case 'seccode_error' :
			$message = get_lang ( 'VerifyCodeError' );
			break;
		case 'account_expired' :
			$message = get_lang ( 'AccountExpired' );
			break;
		case 'account_inactive' :
			$message = get_lang ( 'AccountInactive' );
			break;
		case 'user_password_incorrect' :
			$message = get_lang ( 'InvalidId' );
			break;
		case 'single_login_denied' :
			$message = get_lang ( "AccountHasLogonInAnotherPlace" );
			break;
		case 'sso_auth_failed' :
			$message = get_lang ( "SSOAuthFiled" );
		default :
			$message = get_lang ( "InvalidId" );
			if (api_is_self_registration_allowed ()) {
				$message = get_lang ( "InvalidForSelfRegistration" );
			}
	}
	echo $message;
}
