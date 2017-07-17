<?php
$language_file = array ('admin', 'registration' );
$cidReset = true;
include ('../../inc/global.inc.php');
api_protect_admin_script ();

include_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$tbl_user_register = Database::get_main_table ( TABLE_MAIN_USER_REGISTER );
$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
$objDept = new DeptManager ();

$user_id = intval(getgpc ( "user_id" ));
$user = UserManager::get_reg_user_info_by_id ( $user_id );

$form = new FormValidator ( 'user_audit', 'post', '', '' );
//$form->addElement ( 'header', 'header', get_lang ( 'AuditUserRegister' ) );
$form->addElement ( 'hidden', 'user_id', $user_id );
//注册日期
$form->addElement ( 'static', 'registration_date', get_lang ( 'RegistrationDate' ), $user ['registration_date'] );

$form->addElement ( 'text', 'username', get_lang ( 'LoginName' ), array ('maxlength' => 20, 'style' => "width:250px", 'class' => 'inputText', 'readonly' => 'true' ) );
$form->freeze ( 'username' );

$form->addElement ( 'text', 'firstname', get_lang ( 'FirstName' ), array ('style' => "width:250px", 'class' => 'inputText' ) );
$form->freeze ( 'firstname' );

// Email
$form->addElement ( 'text', 'email', get_lang ( 'Email' ), array ('style' => "width:250px", 'class' => 'inputText' ) );
$form->freeze ( 'email' );

//手机号码
$form->addElement ( 'text', 'mobile', get_lang ( 'MobilePhone' ), array ('style' => 'width:250px', 'class' => 'inputText', 'title' => get_lang ( 'MobilePhoneTip' ) ) );
$form->freeze ( 'mobile' );

//工号学号
/*$form->addElement ( 'text', 'official_code', get_lang ( 'OfficialCode' ), array ('style' => "width:250px", 'class' => 'inputText' ) );
$form->freeze ( 'official_code' );

// Phone
$form->addElement ( 'text', 'phone', get_lang ( 'PhoneWithAreaCode' ), array ('style' => "width:250px", 'class' => 'inputText' ) );
$form->freeze ( 'phone' );



$form->addElement ( "text", "qq", "QQ", array ('style' => 'width:250px', 'class' => 'inputText' ) );
$form->freeze ( 'qq' );

$form->addElement ( "text", "msn", "MSN", array ('style' => 'width:250px', 'class' => 'inputText' ) );
$form->freeze ( 'msn' );

$group = array ();
$group [] = $form->createElement ( 'radio', 'status', null, get_lang ( 'Teacher' ), COURSEMANAGER );
$group [] = $form->createElement ( 'radio', 'status', null, get_lang ( 'Student' ), STUDENT );
$form->addGroup ( $group, "status", get_lang ( 'UserType' ), '&nbsp;', false );
$form->freeze ( 'status' );

//邮编
$form->addElement ( "text", "zip_code", get_lang ( "ZipCode" ), array ('style' => 'width:250px', 'class' => 'inputText' ) );
$form->freeze ( 'zip_code' );

//地址
$form->addElement ( "text", "address", get_lang ( "Address" ), array ('style' => 'width:400px', 'class' => 'inputText' ) );
$form->freeze ( 'address' );*/

//性别
$group = array ();
$group [] = $form->createElement ( 'radio', 'sex', null, get_lang ( 'Male' ), 1 );
$group [] = $form->createElement ( 'radio', 'sex', null, get_lang ( 'Female' ), 2 );
$group [] = $form->createElement ( 'radio', 'sex', null, get_lang ( 'Secrect' ), 0 );
$form->addGroup ( $group, 'sex', get_lang ( 'Sex' ), null, false );
$form->freeze ( 'sex' );

//$form->addElement ( 'textarea', 'description', get_lang ( 'Remark' ), array ('cols' => 50, 'rows' => 5, 'class' => 'inputText' ) );
//$form->freeze ( 'description' );


//所属机构
$depts = $objDept->get_sub_dept_ddl2 ( 0, 'array' );
unset ( $depts [DEPT_TOP_ID] );
$form->addElement ( 'select', 'dept_id', get_lang ( 'InDept' ), $depts, array ('id' => "dept_id", 'style' => 'height:22px;' ) );
$form->addRule ( 'dept_id', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$group = array ();
$group [] = $form->createElement ( 'submit', 'submit', get_lang ( 'AuditPassed' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'submit', 'submit', get_lang ( 'AuditNotPassed' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ( $user );
Display::setTemplateBorder ( $form, '98%' );

if ($form->validate ()) {
	$user = $form->getSubmitValues ();
	//	var_dump ( $user );	exit ();
	if ($user ["submit"] == get_lang ( "AuditPassed" )) {
		$message = UserManager::lock_unlock_user ( 'unlock', $user ['user_id'] );
		$sql_data = array ("dept_id" => $user ["dept_id"] );
		$dept_in_org = $objDept->get_dept_in_org (  $user ["dept_id"] );
		$dept_org = array_pop ( $dept_in_org );
		$sql_data ['org_id'] = $dept_org ['id'];
		if (api_get_setting ( 'enabled_learning_card' ) == 'true') {
			$user_info = UserManager::get_reg_user_info_by_id ( $user ['user_id'] );
			$tbl_card = Database::get_main_table ( 'bos_card' );
			$sql = "UPDATE $tbl_card SET username='" . escape ( $user_info ['username'] ) . "' WHERE card_no='" . escape ( $user_info ['credential_no'] ) . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			$sql_data ['official_code'] = $user_info ['credential_no'];
		}
		$sql = Database::sql_update ( $tbl_user, $sql_data, " username='" . escape ( $user ['username'] ) . "'" );
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$log_msg = get_lang ( 'RegUserAuditPass' ) . "reg_user_id=" . $user ['user_id'];
		api_logging ( $log_msg, 'REGUSER', 'RegUserAuditPass' );
		$redirect_url = api_get_path ( WEB_CODE_PATH ) . "admin/user/user_list_audit.php?message=" . urlencode ( $message );
	}
	
	if ($user ["submit"] == get_lang ( "AuditNotPassed" )) {
		$message = UserManager::lock_unlock_user ( 'lock', $user ['user_id'] );
		$log_msg = get_lang ( 'RegUserAuditNotPass' ) . "reg_user_id=" . $user ['user_id'];
		api_logging ( $log_msg, 'REGUSER', 'RegUserAuditNotPass' );
		$redirect_url = api_get_path ( WEB_CODE_PATH ) . "admin/user/user_list_audit.php?message=" . urlencode ( $message );
	}
	tb_close ( $redirect_url );
}

Display::display_header ( null, FALSE );

$form->display ();
Display::display_footer ();
