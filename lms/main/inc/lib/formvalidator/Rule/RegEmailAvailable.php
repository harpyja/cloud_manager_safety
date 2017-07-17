<?php

require_once ('HTML/QuickForm/Rule.php');
/**
 * QuickForm rule to check if a username is available
 */
class HTML_QuickForm_Rule_RegEmailAvailable extends HTML_QuickForm_Rule
{
	/**
	 * Function to check if a username is available
	 * @see HTML_QuickForm_Rule
	 * @param string $email Wanted email
	 * @param string $current_email 
	 * @return boolean True if email is available
	 */
	function validate($email,$current_email = null)
	{
		$user_table = Database::get_main_table(TABLE_MAIN_USER);
		$sql = "SELECT * FROM $user_table WHERE email = '$email'";
		if(!is_null($current_email))
		{
			$sql .= " AND email != '$current_email'";
		}
		$res = api_sql_query($sql,__FILE__,__LINE__);
		$number = mysql_num_rows($res);
		
		unset($res);
		$reg_user_table = Database::get_main_table(TABLE_MAIN_USER_REGISTER);
		$sql = "SELECT * FROM $reg_user_table WHERE reg_status=2 AND email = '$email'";
		//$sql = "SELECT * FROM $reg_user_table WHERE  email = '$email'";		
		$res = api_sql_query($sql,__FILE__,__LINE__);
		$number1 = mysql_num_rows($res);
		return ($number == 0 && $number1==0);
	}
}
?>