<?php
/**
 ==============================================================================
 * user_edit.php
 ==============================================================================
 */
$language_file = array ('admin', 'registration' );
$cidReset = true;
include ('../../inc/global.inc.php');
$this_section = SECTION_PLATFORM_ADMIN;
api_protect_admin_script ();

require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$table_user = Database::get_main_table ( TABLE_MAIN_USER );

$htmlHeadXtra [] = '<script language="JavaScript" type="text/JavaScript">
function enable_expiration_date() { //v2.0
	document.user_add.radio_expiration_date[0].checked=false;
	document.user_add.radio_expiration_date[1].checked=true;
}

function password_switch_radio_button(form, input){
	var NodeList = document.getElementsByTagName("input");
	for(var i=0; i< NodeList.length; i++){
		if(NodeList.item(i).name=="reset_password" && NodeList.item(i).value=="2"){
			NodeList.item(i).checked=true;
		}
	}
}

function showadv() {
		if(document.user_add.advshow.checked == true) {
			document.getElementById("adv").style.display = "";
		} else {
			document.getElementById("adv").style.display = "none";
		}
}


function change_credeential_state(v){
		if(v!="0") {
			G("credential_no").disabled=false;
			G("credential_no").className="inputText";
			G("credential_no").style.display = "";
			/*document.getElementById("credential_no").style.width="250px";*/
		}
		else {
			G("credential_no").value="";
			G("credential_no").style.display = "none";
			G("credential_no").disabled=true;
		}
}
</script>';

$get_user_id= intval( getgpc('user_id'));
$user_id = isset ( $get_user_id ) ? intval ( $get_user_id ) : intval ( $_POST ['user_id'] );
$tool_name = get_lang ( 'ModifyUserInfo' );

//$interbreadcrumb [] = array ("url" => api_get_path ( WEB_ADMIN_PATH ) . 'index.php', "name" => get_lang ( 'PlatformAdmin' ) );
//$interbreadcrumb [] = array ('url' => "user_list.php", "name" => get_lang ( 'UserList' ) );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

$sql = "SELECT u.* FROM $table_user u  WHERE u.user_id = '" . $user_id . "'";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
if (Database::num_rows ( $res ) != 1) {
	$redirect_url = "user_list.php";
	tb_close ( $redirect_url );
}
$user_data = Database::fetch_array ( $res, 'ASSOC' );
$user_data ['platform_admin'] = $user_data ['is_admin'];
$user_data ['seatnumber'] = $user_data ['seatnumber'];
$user_data ['send_mail'] = 0;
$user_data ['old_password'] = $user_data ['password'];
unset ( $user_data ['password'] );

//部门数据
$deptObj = new DeptManager ();

$form = new FormValidator ( 'user_edit', 'post', '', '' );

//$form->addElement ( 'header', 'header', get_lang ( 'ModifyUserInfo' ) );


$form->addElement ( 'hidden', 'user_id', $user_id );

// 登录名
if (isRoot ( $user_data ['username'] )) { //'root'用户不允许更新登录名
	$form->addElement ( 'text', 'username', get_lang ( 'LoginName' ), array ('maxlength' => 20, 'style' => "width:250px", 'class' => 'inputText', 'readonly' => 'true' ) );
	$form->freeze ( 'username' );
} else {
	$form->addElement ( 'text', 'username', get_lang ( 'LoginName' ), array ('maxlength' => 20, 'style' => "width:250px", 'class' => 'inputText' ) );
}
$form->addRule ( 'username', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'username', get_lang ( 'OnlyLettersAndNumbersAllowed' ), 'username' );
$form->addRule ( 'username', '', 'maxlength', 20 );
$form->addRule ( 'username', get_lang ( 'UserTaken' ), 'username_available', $user_data ['username'] );

//密码
$group = array ();
$group [] = & HTML_QuickForm::createElement ( 'radio', 'reset_password', null, get_lang ( 'DontResetPassword' ) . '<br>', 0, array ('onclick' => 'document.getElementById(\'password\').value=\'\';' ) );
if (count ( $extAuthSource ) > 0) {
	$group [] = & HTML_QuickForm::createElement ( 'radio', 'reset_password', null, get_lang ( 'ExternalAuthentication' ) . ' ', 3 );
	$auth_sources = array ();
	foreach ( $extAuthSource as $key => $info ) {
		$auth_sources [$key] = $key;
	}
	$group [] = & HTML_QuickForm::createElement ( 'select', 'auth_source', null, $auth_sources );
	$group [] = & HTML_QuickForm::createElement ( 'static', '', '', '<br />' );

	//$form->addGroup($group, 'password', null, '',false);
}
$group [] = & HTML_QuickForm::createElement ( 'radio', 'reset_password', null, get_lang ( 'ResetToDefaultPassword' ) . '(' . get_lang ( 'DefaultPasswordIs' ) . api_get_setting ( 'default_password' ) . ')<br>', 3, array ('onclick' => 'document.getElementById(\'password\').value=\'\';' ) );
//$group [] = & HTML_QuickForm::createElement ( 'radio', 'reset_password', null, get_lang ( 'AutoGeneratePassword' ) . '<br>', 1, array ('onclick' => 'document.getElementById(\'password\').value=\'\';' ) );
$group [] = & HTML_QuickForm::createElement ( 'radio', 'reset_password', null, null, 2 );
$group [] = & HTML_QuickForm::createElement ( 'password', 'password', null, array ('id' => 'password', 'style' => "width:230px", 'class' => 'inputText', 'onkeydown' => 'password_switch_radio_button(document.user_add,"reset_password")' ) );
$form->addGroup ( $group, 'password', get_lang ( 'Password' ), '', false );

if (api_is_platform_admin () && ! isRoot () && isRoot ( $user_data ['username'] )) {
	$form->freeze ( 'password' );
}

// 姓名
/* hgz 20070514 do not use lastname , only use firstname
 $form->addElement('text','lastname',get_lang('LastName'));
 $form->applyFilter('lastname','html_filter');
 $form->applyFilter('lastname','trim');
 $form->addRule('lastname', get_lang('ThisFieldIsRequired'), 'required');
 */
$form->addElement ( 'text', 'firstname', get_lang ( 'FirstName' ), array ('style' => "width:250px", 'class' => 'inputText' ) );
$form->applyFilter ( 'firstname', 'html_filter' );
$form->applyFilter ( 'firstname', 'trim' );
$form->addRule ( 'firstname', get_lang ( 'ThisFieldIsRequired' ), 'required' );

// Email
$form->addElement ( 'text', 'email', get_lang ( 'Email' ), array ('style' => "width:250px", 'class' => 'inputText' ) );
$form->addRule ( 'email', get_lang ( 'EmailWrong' ), 'email' );
$form->addRule ( 'email', get_lang ( 'EmailWrong' ), 'required' );

//工号学号
$form->addElement ( 'text', 'official_code', get_lang ( 'OfficialCode' ), array ('style' => "width:250px", 'class' => 'inputText' ) );
$form->applyFilter ( 'official_code', 'html_filter' );
$form->addElement ( 'text', 'seatnumber', get_lang ( '座位号' ), array ('style' => "width:250px", 'class' => 'inputText' ) );
$form->applyFilter ( 'seatnumber', 'html_filter' );

$form->addElement ( 'text', 'lastname', get_lang ( 'LastName' ), array ('style' => "width:230px", 'class' => 'inputText', 'id' => 'lastname' ) );
//$form->addRule ( 'lastname', get_lang ( 'ThisFieldIsRequired' ), 'required' );


$group = array ();
$group [] = & HTML_QuickForm::createElement ( 'radio', 'status', null, get_lang ( 'Student' ), STUDENT );
$group [] = & HTML_QuickForm::createElement ( 'radio', 'status', null, get_lang ( 'Teacher' ), COURSEMANAGER );
if (isRoot () or (! isRoot ( $user_data ['username'] ) && api_is_platform_admin () && api_get_user_name () == $user_data ['username'])) {
	$group [] = & HTML_QuickForm::createElement ( 'radio', 'status', null, get_lang ( 'PlatformAdministrator' ), PLATFORM_ADMIN );
}
$form->addGroup ( $group, null, get_lang ( 'UserRole' ), '&nbsp;' );

//所属机构
/*$orgs = get_restrict_org_dd ();
$orgs=array_insert_first($orgs,array("0"=>""));
$form->addElement ( 'select', 'org_id', get_lang ( 'InOrg' ), $orgs, array ('id' => "org_id", 'style' => 'height:22px;' ) );
$form->addRule ( 'org_id', get_lang ( 'ThisFieldIsRequired' ), 'required' );*/

//所属部门
/*$all_sub_depts = $deptObj->get_sub_dept_ddl ( $user_data ['org_id'] );
foreach ( $all_sub_depts as $item ) {
	$depts [$item ['id']] = str_repeat ( "&nbsp;", intval ( $item ['level'] / 2 ) ) . $item ['dept_name'];
}
$form->addElement ( 'select', 'dept_id', get_lang ( 'UserInDept' ), $depts, array ('id' => "dept_id", 'style' => 'height:22px;' ) );*/
$depts = $deptObj->get_sub_dept_ddl2 ( 0, 'array' );
$form->addElement ( 'select', 'dept_id', get_lang ( 'UserInDept' ), $depts, array ('id' => "dept_id", 'style' => 'height:22px;' ) );

$form->addElement ( "hidden", "old_org_id", $user_data ['org_id'] );
$form->addElement ( "hidden", "old_dept_id", $user_data ['dept_id'] );

//--------------------------------------------------------其它信息


if (! $user_data ['platform_admin']) {
	// 过期日期
	$group = array ();
	$group [] = $form->createElement ( 'radio', 'radio_expiration_date', null, get_lang ( 'NeverExpires' ), 0 );
	$group [] = $form->createElement ( 'radio', 'radio_expiration_date', null, get_lang ( 'ExpirationDate' ), 1 );
	//$group[] = $form->createElement('datepicker', 'expiration_date',null, array ('form_name' => $form->getAttribute('name'), 'onChange'=>'enable_expiration_date()'));
	$group [] = $form->createElement ( 'calendar_datetime', 'expiration_date', null, array (), array ('show_time' => TRUE ) );
	$form->addGroup ( $group, 'max_member_group', get_lang ( 'ExpirationDate' ), '&nbsp;&nbsp;', false );
	
	// 激活
	$group = array ();
	$group [] = $form->createElement ( 'radio', 'active', null, get_lang ( 'Yes' ), 1 );
	$group [] = $form->createElement ( 'radio', 'active', null, get_lang ( 'No' ), 0 );
	$form->addGroup ( $group, 'active', get_lang ( 'ActiveAccount' ), '', false );
}

//注册日期
$form->addElement ( 'static', 'registration_date', get_lang ( 'RegistrationDate' ), $user_data ['registration_date'] );

//生日
//$form->addElement('date','birthday', get_lang('Birthday'),array('language'=> 'zh','format'=> 'YMd','minYear' => 1950,'maxYear'=> 2009));


//性别
$group = array ();
$group [] = $form->createElement ( 'radio', 'sex', null, get_lang ( 'Male' ), 1 );
$group [] = $form->createElement ( 'radio', 'sex', null, get_lang ( 'Female' ), 2 );
$group [] = $form->createElement ( 'radio', 'sex', null, get_lang ( 'Secrect' ), 0 );
$form->addGroup ( $group, 'sex', get_lang ( 'Sex' ), null, false );

//证件及号码
/*$group = array ();
$credentials_options  = array ('0' => get_lang ( 'None' ), '1' => get_lang ( 'IDCard' ), '2' => get_lang ( 'WorkCard' ), '3' => get_lang ( 'StudentCard' ) );
$group [] = & HTML_QuickForm::createElement ( 'select', 'credential_type', null, $credentials_options, array ('style' => "width:120px", "onchange" => "change_credeential_state(this.value)" ) );
$tmp_options = array ('style' => "width:230px", 'class' => 'inputText', 'id' => 'credential_no' );
if (! $user_data ['credential_no']) {
	$tmp_options ['disabled'] = 'true';
}
$group [] = & HTML_QuickForm::createElement ( 'text', 'credential_no', null, $tmp_options );
$form->addGroup ( $group, 'credential', get_lang ( 'CredentialTypeAndNo' ), '' );*/
$form->addElement ( 'hidden', 'credential_type', '3' );
$form->addElement ( 'text', 'credential_no', get_lang ( 'IDCard' ), array ('style' => "width:230px", 'class' => 'inputText', 'id' => 'credential_no' ) );

$form->addElement ( 'text', 'phone', get_lang ( 'PhoneWithAreaCode' ), array ('style' => "width:250px", 'class' => 'inputText' ) );
//$form->addRule ( 'phone', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
//$form->addRule ( 'phone', get_lang ( 'ThisFieldMinLengthIs' ) . ':11', 'minlength', 10 );


//手机号码
$form->addElement ( 'text', 'mobile', get_lang ( 'MobilePhone' ), array ('style' => 'width:250px', 'class' => 'inputText', 'title' => get_lang ( 'MobilePhoneTip' ) ) );
$form->addRule ( 'mobile', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'mobile', get_lang ( 'ThisFieldMinLengthIs' ) . ':11', 'minlength', 11 );

// Picture
/*$form->addElement ( 'file', 'picture', get_lang ( 'Photo' ), array ('style' => "width:350px", 'class' => 'inputText' ) );
$allowed_picture_types = array ('jpg', 'jpeg', 'png', 'gif' );
$form->addRule ( 'picture', get_lang ( 'OnlyImagesAllowed' ) . ' (' . implode ( ',', $allowed_picture_types ) . ')', 'filetype', $allowed_picture_types );
$form->addRule ( 'picture', get_lang ( 'UploadFileSizeLessThan' ) . '200 KB', 'maxfilesize', 200 * 1024 );
if (strlen ( $user_data ['picture_uri'] ) > 0) {
	$form->addElement ( 'checkbox', 'delete_picture', '&nbsp;', get_lang ( 'DelImage' ) );
}*/

// Phone
/*
$form->addElement ( "text", "qq", "QQ", array ('style' => 'width:250px', 'class' => 'inputText' ) );
$form->addRule ( 'qq', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );

$form->addElement ( "text", "msn", "MSN", array ('style' => 'width:250px', 'class' => 'inputText' ) );
$form->addRule ( 'msn', get_lang ( 'MSNWrong' ), 'email' );

//邮编
$form->addElement ( "text", "zip_code", get_lang ( "ZipCode" ), array ('style' => 'width:250px', 'class' => 'inputText' ) );
$form->addRule ( 'zip_code', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );

//地址
$form->addElement ( "text", "address", get_lang ( "Address" ), array ('style' => 'width:400px', 'class' => 'inputText' ) );


//备注说明
$form->addElement ( 'textarea', 'description', get_lang ( 'Remark' ), array ('cols' => 50, 'rows' => 5, 'class' => 'inputText' ) );*/

// Send email
$group = array ();
$group [] = & HTML_QuickForm::createElement ( 'radio', 'send_mail', null, get_lang ( 'Yes' ), 1 );
$group [] = & HTML_QuickForm::createElement ( 'radio', 'send_mail', null, get_lang ( 'No' ), 0 );
$form->addGroup ( $group, 'mail', get_lang ( 'SendMailToUsers' ), '&nbsp;', false );

$group = array ();
$group [] = $form->createElement ( 'submit', 'submit', get_lang ( 'Ok' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->applyFilter ( '__ALL__', 'trim' );

// Set default values
if (! is_null ( $user_data ['expiration_date'] ) && $user_data ['expiration_date'] != '0000-00-00 00:00:00') {
	$expiration_date = $user_data ['expiration_date'];
	$user_data ['radio_expiration_date'] = 1;
} else {
	$expiration_date = '';
	$user_data ['radio_expiration_date'] = 0;
}

//liyu:更新成新日历控件后
$user_data ['expiration_date'] = $expiration_date;

$user_data ['reset_password'] = '0';
$user_data ['credential'] ['credential_type'] = $user_data ['credential_type'];
$user_data ['credential'] ['credential_no'] = $user_data ['credential_no'];

$form->setDefaults ( $user_data );
Display::setTemplateBorder ( $form, '98%' );

if ($form->validate ()) {
	//$user = $form->exportValues();
	$user = $form->getSubmitValues ();
	//var_dump($user);exit;
	if (! can_do_my_bo ( $user_data ['creator_id'] )) {
		Display::display_msgbox ( '对不起,你没有操作的权限!', 'main/admin/user/user_list.php', 'warning' );
	}
	
	/*	$picture_element = & $form->getElement ( 'picture' );
	$picture = $picture_element->getValue ();
	$picture_uri = $user_data ['picture_uri'];
	if (strlen ( $picture ['name'] ) > 0) {
		$picture_uri = uniqid ( '' ) . '_' . replace_dangerous_char ( $picture ['name'] );
		$picture_location = api_get_path ( SYS_PATH ) . 'storage/users_picture/' . $picture_uri;
		move_uploaded_file ( $picture ['tmp_name'], $picture_location );
	} elseif (isset ( $user ['delete_picture'] )) {
		@unlink ( api_get_path ( SYS_PATH ) . 'storage/users_picture/' . $picture_uri );
		$picture_uri = '';
	}*/
	$user_id = $user ['user_id'];
	$lastname = $user ['lastname'];
	$firstname = $user ['firstname'];
	$official_code = $user ['official_code'];
	$seatnumber = $user ['seatnumber'];
	$email = $user ['email'];
	$phone = $user ['phone'];
	$username = $user ['username'];
	$status = intval ( $user ['status'] );
	$picture = $_FILES ['picture'];
	$platform_admin = ($status == PLATFORM_ADMIN ? 1 : 0);
	$send_mail = intval ( $user ['send_mail'] );
	$reset_password = intval ( $user ['reset_password'] );
	$description = $user ['description'];
	$dept_id = $user ['dept_id'];
	//$org_info = $deptObj->get_dept_info ( $dept_id );
	//$org_id = $org_info ['org_id'];
	$dept_in_org = $deptObj->get_dept_in_org ( intval(getgpc ( "dept_id" )), TRUE );
	$dept_org = array_pop ( $dept_in_org );
	$org_id = $dept_org ['id'];
	$credential_type = 3;
	//$credential_no = ($credential_type == '0' ? '' : $user ['credential'] ['credential_no']);
	$credential_no = $user ['credential_no'];
	$zip_code = $user ['zip_code'];
	$address = $user ['address'];
	$mobile = $user ['mobile'];
	$qq = $user ['qq'];
	$msn = $user ['msn'];
	$sex = $user ['sex'];
	if ($user ['radio_expiration_date'] == '1' && ! $user_data ['platform_admin']) {
		$expiration_date = $user ['expiration_date'];
	} else {
		$expiration_date = '0000-00-00 00:00:00';
	}
	$active = $user_data ['platform_admin'] ? 1 : intval ( $user ['active'] );
	
	if ($reset_password == 0) { //勿重新设定密码
		$password = null;
		$auth_source = $user_data ['auth_source'];
	} elseif ($reset_password == 3) { //重置为默认
		$password = api_get_setting ( 'default_password' );
		$auth_source = PLATFORM_AUTH_SOURCE;
	} else { //新密码
		$password = $user ['password'];
		$auth_source = PLATFORM_AUTH_SOURCE;
	}
	UserManager::update_user ( $user_id, $firstname, $lastname, $username, $password, $auth_source, $email, $status, $official_code, $phone, $picture_uri, $expiration_date, $active, api_get_user_id (), $dept_id, $description, $sex, $credential_type, $credential_no, $zip_code, $address, $mobile,
			$qq, $msn, "", $org_id ,$seatnumber);

	if (isRoot ()) { //只有root可以修改这个属性
		if (isRoot ( $username )) $platform_admin = 1;
		if ($platform_admin == 1) {
			$sql = "UPDATE " . $table_user . " SET is_admin=1 WHERE user_id=" . Database::escape ( $user_id );
			api_sql_query ( $sql, __FILE__, __LINE__ );
		} else {
			$sql = "UPDATE " . $table_user . " SET is_admin=0,status='" . $status . "' WHERE user_id=" . Database::escape ( $user_id );
			api_sql_query ( $sql, __FILE__, __LINE__ );
		}
	}
	
	//部门变更之后选修课程的审核
	if ($user ["old_dept_id"] != $dept_id) {
		$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
		$table_course_subscribe_requisition = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );
		
		//该用户的所有没有通过的课程选修申请,需要变更审批的部门经理
		$sql = "SELECT t1.course_code FROM " . $table_course_subscribe_requisition . " AS t1 LEFT JOIN " . $table_course . " AS t2 ON t1.course_code=t2.code WHERE t1.user_id='" . escape ( $user_id ) . "' AND t2.is_audit_enabled=2";
		$course_code = Database::get_into_array ( $sql );
		if ($course_code && is_array ( $course_code )) {
			$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
			$sql = "SELECT dept_admin FROM " . $table_dept . " WHERE dept_id='" . escape ( $dept_id ) . "'";
			$dept_admin = Database::get_scalar_value ( $sql );
			$sql = "UPDATE " . $table_course_subscribe_requisition . " SET audit_user='" . $dept_admin . "' WHERE user_id='" . escape ( $user_id ) . "' AND " . Database::create_in ( $course_code, "course_code" );
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		}
	}
	
	if ($user ["old_org_id"] != $org_id) {
		$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
		$table_course_subscribe_requisition = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );
		
		//该用户的所有没有通过的课程选修申请,需要变更审批的培训管理员
		$sql = "SELECT t1.course_code FROM " . $table_course_subscribe_requisition . " AS t1 LEFT JOIN " . $table_course . " AS t2 ON t1.course_code=t2.code WHERE t1.user_id='" . escape ( $user_id ) . "' AND t2.is_audit_enabled=3";
		$course_code = Database::get_into_array ( $sql );
		if ($course_code && is_array ( $course_code )) {
			$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
			$sql = "SELECT dept_admin FROM " . $table_dept . " WHERE dept_id='" . escape ( $dept_id ) . "'";
			$dept_admin = Database::get_scalar_value ( $sql );
			$sql = "UPDATE " . $table_course_subscribe_requisition . " SET audit_user='" . $dept_admin . "' WHERE user_id='" . escape ( $user_id ) . "' AND " . Database::create_in ( $course_code, "course_code" );
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		}
	}
	
	api_logging ( get_lang ( 'EditUser' ) . $username, 'USER', 'EditUser' );
	if (! empty ( $email ) && $send_mail) {
		$emailTo = trim ( $email );
		$emailSubject = get_lang ( 'ModifProfile' );
		$emailBody = get_lang ( 'Dear' ) . ' ' . stripslashes ( "$firstname" ) . ":<br/>" . get_lang ( 'YouAreReg' ) . ' ' . get_setting ( 'siteName' ) . ' ' . get_lang ( 'Settings' ) . "<br/>" . get_lang ( 'TheU' ) . ' : ' . $username . "<br/>";
		if ($reset_password != 0 or $_configuration ['crypted_method'] == "none" && $password) $emailBody .= get_lang ( 'Pass' ) . ' : ' . stripslashes ( $password ) . "<br/>";
		email_body_txt_add ( $emailBody );
		api_email_wrapper ( $emailTo, $emailSubject, $emailBody );
	}
	
	$redirect_url = 'user_list.php';
        tb_close($redirect_url);
	Display::display_msgbox ( get_lang ( 'UserUpdated' ), $redirect_url );
}

Display::display_header ( $tool_name, FALSE );

if ($_configuration ['enable_user_ext_info']) {
	$html = '<div id="demo" class="yui-navset">';
	$html .= '<ul class="yui-nav">';
	$html .= '<li  class="selected"><a href="user_edit.php?user_id=' . $user_id . '"><em>基本信息</em></a></li>';
	$html .= '<li><a href="user_edit_ext.php?user_id=' . $user_id . '"><em>扩展信息</em></a></li>';
	$html .= '</ul>';
	$html .= '<div class="yui-content"><div id="tab1">';
//	echo $html;
}
$image = $user_data ['picture_uri'];
if (strlen ( $image ) > 0 && file_exists ( api_get_path ( SYS_PATH ) . "storage/users_picture/{$image}" )) {
	$picture_url = api_get_path ( WEB_PATH ) . 'storage/users_picture/' . $user_data ['picture_uri'];
} else {
	$picture_url = api_get_path ( WEB_IMG_PATH ) . "unknown.jpg";
}
$img_attributes = 'src="' . $picture_url . '?rand=' . time () . '" ' . 'alt="' . $user_data ['lastname'] . ' ' . $user_data ['firstname'] . '" ' . 'style="float:right; padding:5px;" ';
//$image_size = getimagesize ( $picture_url );
//if ($image_size [0] > 150) $img_attributes .= 'width="150" ';


echo '<table width="100%" border=0><tr>';
echo '<td align=left valign=top><img ' . $img_attributes . '/></td>';
echo '<td width=90% valign=top>';
$form->display ();
echo '</td></tr></table>';
echo '<br>';
if ($_configuration ['enable_user_ext_info']) echo '</div></div></div>';
Display::display_footer ();
