<?php
if(defined("ROOT_CODE_PATH")==false){
	define('ROOT_CODE_PATH', str_replace('extensions/auth/external/auth.inc.php', '',
	str_replace('\\', '/', __FILE__)));
}

/**
 * 开发步骤
 * 1. 根据具体业务逻辑去实现实现如下几个方法.
 */

//这些只是第三方库, 可以不使用
require_once(ROOT_CODE_PATH."main/inc/conf/configuration.php");
include_once(ROOT_CODE_PATH."main/inc/lib/commons/application.inc.php");

/**
 * 外部认证源的认证, 如果成功返回TRUE
 * @param string $login 登录名
 * @param string $password 密码
 */
function external_login($login, $password){
	global $_db_mysql,$_db_pgsql;//,$_db_mssql;
	$password=api_get_encrypted_password(trim($password),SECURITY_SALT);
	$sql="SELECT COUNT(*) FROM user WHERE username='"
	.$_db_mysql->escape(trim($login))."' AND password='".$_db_mysql->escape(trim($password))."'";
	$cnt=$_db_mysql->get_var($sql);
	//mysql_close($_db_mysql->dbh);
	return $cnt>0;
}

/**
 * 取外部认证源的用户信息
 * @param  string $login 登录名
 */
function external_get_user_info ($login){
	global $_db_mysql,$_db_pgsql;//,$_db_mssql;

	$user_info=array();
	$sql="SELECT * FROM user WHERE username='"
	.$_db_mysql->escape(trim($login))."'";

	$user_info_arr=$_db_mysql->get_row($sql);
	if($user_info_arr){
		$user_info['password']=$user_info_arr->password;
		$user_info['email']=$user_info_arr->email;		
		$user_info['firstname']=$user_info_arr->firstname;
		$user_info['phone']=$user_info_arr->phone;
		$user_info['official_code']=$user_info_arr->official_code;
	}
	return $user_info;
}

/**
 * 将外部认证源的用户信息放到本地数据库表(user)中
 * @param string $login 登录名
 * @param array $info_array 用户信息
 */
function external_put_user_info_locally($login, $info_array){	
	global $_db_mysql,$_db_pgsql,$_db_mssql;
	global $_configuration;
	//global $loginFailed, $uidReset, $_user,$_uid;
	global $platformLanguage;
	
	define ("STUDENT",5);
	define ("COURSEMANAGER",1);

	$login_name 	= $login;
	//$password		= api_get_encrypted_password($info_array['password'],SECURITY_SALT);
	$password		= $info_array["password"];	
	$email      	= $info_array["email"];	
	$firstName  	= $info_array["firstname"];
	$official_code 	= $info_array["official_code"];;
	$phone			= $info_array['phone'];
	$status			= STUDENT;	

	$_database_connection = @mysql_pconnect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'])
	or die (mysql_error());
	$selectResult = mysql_select_db($_configuration['main_database'],$_database_connection)
	or  die ('<center><h2>ERROR ! Connect the Main Database Failed!</h2></center>');
	require_once(ROOT_CODE_PATH.'inc/lib/usermanager.lib.php');
	$_uid = UserManager::create_user($firstName, "", $status,
	$email, $login_name, $password, $official_code,$platformLanguage,$phone, '', 'external');

	return $_uid;
	
}
?>