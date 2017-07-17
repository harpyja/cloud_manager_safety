<?php 
/*
==============================================================================
	
==============================================================================
*/
/**
==============================================================================
*	LDAP settings
*	In the older code, there was a distinction between
*	the teacher and student LDAP server. Later I decided not
*	to make this distinction. However, it could be built in
*	in the future but then perhaps in a more general way.
*
*	Originally, Thomas and I agreed to store all settings in one file
*	(configuration.php) to make it easier for claroline admins to make changes.
*	Since October 2003, this changed: the include directory has been
*	changed to be called "inc", and all tools should have their own file(s).
*
*	This file "ldap_var.inc.php" was already used by the
*	older french authentification functions. I have moved the new
*	variables from the configuration.php to here as well.
*
*	@author Roan Embrechts
*	@package ZLMS.auth.ldap
==============================================================================
*/

//LDAP 服务器信息
$ldap_host = api_get_setting('ldap_main_server_address');
$ldap_port = api_get_setting('ldap_main_server_port');
$ldap_rdn = api_get_setting('ldap_authentication_login'); //登录信息
$ldap_pass = api_get_setting('ldap_authentication_password'); //密码
$ldap_basedn = api_get_setting('ldap_domain');
$ldap_search_dn = api_get_setting('ldap_search_string');

//additional server params for use of replica in case of problems
$ldap_host2 = api_get_setting('ldap_replicate_server_address');
$ldap_port2 = api_get_setting('ldap_replicate_server_port');

//LDAP服务器版本 - set to 3 for LDAP 3
$ldap_version = api_get_setting('ldap_version');

$ldap_pass_placeholder = "PLACEHOLDER";
?>