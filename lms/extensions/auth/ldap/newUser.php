<?php 
/*
==============================================================================
	
==============================================================================
*/
/**
==============================================================================
*	Users trying to login, who do not yet exist in the local System database, 
*	can be added by this script which tries to retrieve ldap information about
*   them.
*
*	@author Roan Embrechts
*	@package ZLMS.auth.ldap
==============================================================================
*/
	
/*
==================================================
	when a user does not exist yet in ZLMS, 
	but he or she does exist in the LDAP,
	we add him to the ZLMS database
==================================================
*/

require_once(api_get_apth(SYS_CODE_PATH).'auth/ldap/authldap.php');

//error_log('Trying to register new user '.$login.' with pass '.$password,0);

$ldap_login_success = ldap_login($login, $password);	

if ($ldap_login_success)
{	
	$info_array = ldap_find_user_info($login);
	$_uid=ldap_put_user_info_locally($login, $info_array);
		
	$loginFailed = false;
	$uidReset = true;
	$_user['user_id'] = $_uid;
	api_session_register('_uid');
}
else
{
	//error_log('Could not find '.$login.' on LDAP server',0);
	$loginFailed = true;
	unset($_user['user_id']);
	$uidReset = false;
	api_session_unregister('_uid');
}
?>