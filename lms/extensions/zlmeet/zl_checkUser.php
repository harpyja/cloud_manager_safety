<?php
header("Content-Type:text/xml;charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

include_once("../../../../main/inc/global.inc.php");

$userId=$_SESSION['_user']["user_id"];
$userName=$_SESSION['_user']['firstName'];
 
$userName=trim($_REQUEST['userName']);
$password=trim($_REQUEST['password']);
$roomID=trim($_REQUEST['roomID']);
$role=trim($_REQUEST['role']);  //以上参数是zlmeet传过来的
 
$is_login_failed=false; //验证是否通过,自己定义的一个变量
 
$user_table = Database::get_main_table(TABLE_MAIN_USER);
$sql = "SELECT user_id, username, password, auth_source, active, expiration_date
                FROM $user_table WHERE username = '".Database::escape_string($userName)."'";
$result = api_sql_query($sql,__FILE__,__LINE__);
if(DEBUG_MODE) api_error_log($sql,__FILE__,__LINE__,"zlmeet.log");
$password = trim(stripslashes($password));
//$password=Database::escape_string(api_get_encrypted_password($password,SECURITY_SALT));
if (mysql_num_rows($result) > 0)
{
	$uData = mysql_fetch_array($result);
	if ($password == $uData['password'] AND (trim($userName) == $uData['username'])
	&& $uData['active']=='1')  {
		$is_login_failed=false;
	}else {
		$is_login_failed=true;
	}
}else{
	$is_login_failed=true;
}
 
if(isset($userName) && !empty($userName)){
	$is_login_failed=false;
}

//验证完后输出结果给zlmeet
if(!$is_login_failed)
{
	$xml= "<Result isUser='true' />";
}
else
{
	$xml="<Result isUser='false' />";
}
echo $xml;
?>