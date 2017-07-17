<?php
/**
 * liyu: 验证注册的用户名是否可用
 */
require_once ('HTML/QuickForm/Rule.php');
/**
 * QuickForm rule to check if a username is available
 */
class HTML_QuickForm_Rule_RegUsernameAvailable extends HTML_QuickForm_Rule
{
	/**
	 * Function to check if a username is available
	 * @see HTML_QuickForm_Rule
	 * @param string $username Wanted username
	 * @param string $current_username 
	 * @return boolean True if username is available
	 */
	function validate($username,$current_username = null)
	{
		$user_table = Database::get_main_table(TABLE_MAIN_USER);
		$sql = "SELECT * FROM $user_table WHERE username = '$username'";
		if(!is_null($current_username))
		{
			$sql .= " AND username != '$current_username'";
		}
		$res = api_sql_query($sql,__FILE__,__LINE__);
		$number0 = mysql_num_rows($res);
		
		unset($res);
		$reg_user_table = Database::get_main_table(TABLE_MAIN_USER_REGISTER);		
		//$sql = "SELECT * FROM $reg_user_table WHERE reg_status=2 and username = '$username'";
		$sql = "SELECT * FROM $reg_user_table WHERE username = '$username'";		
		$res = api_sql_query($sql,__FILE__,__LINE__);
		$number = mysql_num_rows($res);
				
		return ($number0==0 && $number == 0);
	}
}
?>