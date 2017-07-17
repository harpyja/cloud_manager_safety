<?php
/*
 ==============================================================================
	外部系统认证模块,用户数据已在ZLMS数据库中时使用,第三方认证源的登录认证处理逻辑
 ==============================================================================
 */

require_once('auth.inc.php');

//外部认证源的认证, 这个方法需要根据具体业务逻辑去实现了.
$loginSucces = external_login($login, $password);

//下面的代码不需要修改
if ($loginSucces)
{
	$loginFailed = false;
	$uidReset = true;
	$_user['user_id'] = $uData['user_id'];
	$_uid=$uData['user_id']; //liyu
	api_session_register('_uid');
}
else
{
	$loginFailed = true;
	unset($_user['user_id']);
	$uidReset = false;
}
?>