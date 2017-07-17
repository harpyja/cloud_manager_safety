<?php
/*
 ==============================================================================

 ==============================================================================
 */
/**
 ==============================================================================
 *	Users trying to login, who already exist in the ZLMS database
 *	and have ldap as authentication type, get verified here.
 *
 *	@author Roan Embrechts
 *	@package ZLMS.auth.ldap
 ==============================================================================
 */

/*
 An external authentification module
 needs to set
 - $loginFailed
 - $uidReset
 - $_user['user_id']
 - register the $_user['user_id'] in the session
 As the LDAP code shows, this is not as difficult as you might think.
 */
/*
 ===============================================
 LDAP authentification module
 this calls the loginWithLdap function
 from the LDAP library, and sets a few
 variables based on the result.
 ===============================================
 */

require_once(api_get_apth(SYS_CODE_PATH).'auth/ldap/authldap.php');

$loginLdapSucces = ldap_login($login, $password);

if ($loginLdapSucces)
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