<?php
/*
==============================================================================
	ZLMS - elearning and course management software
	
	Copyright (c) 2004-2005 ZLMS S.A.
	Copyright (c) Bart Mollet, Hogeschool Gent
	
	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".
	
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	See the GNU General Public License for more details.
	
	Contact address: Zhong
	Mail: poopsoft@163.com
==============================================================================
*/
require_once ('HTML/QuickForm/Rule.php');
/**
 * QuickForm rule to check if a username is of the correct format
 */
class HTML_QuickForm_Rule_Username extends HTML_QuickForm_Rule
{
	/**
	 * Function to check if a username is of the correct format
	 * @see HTML_QuickForm_Rule
	 * @param string $username Wanted username
	 * @return boolean True if username is of the correct format
	 */
	function validate($username)
	{
		//liyu:20090902  不是a-z0-9_.-@字符
		//$filtered_username = eregi_replace('[^a-z0-9_.-@]', '_', strtr($username, '�����������������������������������������������������', 'AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn'));
		//return $filtered_username == $username;
		
		//$result=ereg("^[a-zA-Z0-9\-_]+$",$username);
		$result=preg_match("/^[a-zA-Z0-9\-_]+$/i",$username,$matches);
		return $result>0;
	}
}
?>