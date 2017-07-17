<?php
define ( "AUTH_SUCCESS", "auth_success" ); //认证成功
define ( "AUTH_FAILED", "auth_failed" ); //认证失败
define ( "AUTH_USER_NOT_EXSIST", "auth_user_not_exsist" ); //用户不存在
define ( "AUTH_PASSWORD_ERROR", "auth_password_error" ); //密码错误
define ( "AUTH_USER_LOCKED", "auth_user_locked" ); //用户被锁定
define ( "AUTH_USER_EXPIRATION", "auth_user_expiration" ); //帐号过期


/**
 * 认证主要方法， 必须实现， 且返回以上定义的变量之一：AUTH_SUCCESS，认识成功通过
 * @param unknown_type $username
 * @param unknown_type $password
 */
function authentication($username, $password) {
	$uData = auth_get_user_info ( $username );
	//if (Database::num_rows($result) > 0) //用户是否存在
	if ($uData) {
		$password = trim ( stripslashes ( $password ) );
		$password = Database::escape_string ( api_get_encrypted_password ( $password, SECURITY_SALT ) );
		
		//密码是否正确
		if ($password == $uData ['password'] and (trim ( strtolower ( $username ) ) == strtolower ( $uData ['username'] ))) {
			// 用户是否被锁定
			if ($uData ['active'] == '1') {
				// 是否到期
				if ($uData ['expiration_date'] > date ( 'Y-m-d H:i:s' ) or $uData ['expiration_date'] == '0000-00-00 00:00:00') {
					//执行到这里表示登录认证成功了												
					return AUTH_SUCCESS;
				} else { //已过期
					return AUTH_USER_EXPIRATION;
				}
			} else { //被锁定
				return AUTH_USER_LOCKED;
			}
		} else { // 登录失败，用户名或密码不正确
			return AUTH_PASSWORD_ERROR;
		}
	
	} else {
		return AUTH_USER_NOT_EXSIST;
	}
	return AUTH_FAILED;
}

function authentication1($username, $password) {
	if (empty ( $username ) or empty ( $password )) return AUTH_FAILED;
	$username = Database::escape ( trim ( stripslashes ( $username ) ) );
	$password = trim ( stripslashes ( $password ) );
	$password = Database::escape ( api_get_encrypted_password ( $password, SECURITY_SALT ) );
	$sql = "SELECT authentication(" . $username . "," . $password . ") AS auth_res";
	return Database::get_scalar_value ( $sql );
}
?>