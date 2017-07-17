<?php 
/*
==============================================================================
	外部系统认证模块,用户数据不在ZLMS数据库中时使用, 
==============================================================================
*/

require_once('auth.inc.php');

//外部认证源的认证, 这个方法需要根据具体业务逻辑去实现了.
$external_login_success = external_login($login, $password);	


if ($external_login_success)//用户外部认证成功
{	
	//用户数据不在ZLMS数据库中时,需要获取外面部用户数据,然后插入到ZLMS用户表user中
	$info_array = external_get_user_info($login);
	$_uid=external_put_user_info_locally($login, $info_array);
	
	//从这行开始直接复制
	$loginFailed = false;
	$uidReset = true;
	$_user['user_id'] = $_uid;
	api_session_register('_uid');
}
else
{	
	$loginFailed = true;
	$uidReset = false;
	unset($_user['user_id']);	
	api_session_unregister('_uid');
}
?>