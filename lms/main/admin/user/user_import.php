<?php

/*
 ==============================================================================
 用户导入
 ==============================================================================
 */

function doAction() {
	if ($_FILES ['import_file'] ['size'] !== 0) {
		$save_path = $_FILES ['import_file'] ['tmp_name'];
		set_time_limit ( 0 );
		$file_type = getFileExt ( $_FILES ['import_file'] ['name'] );
		$file_type = strtolower ( $file_type );
		if ($file_type == 'xls' or $file_type == 'xlsx') {
			$users = parse_upload_data ( $save_path, $file_type );
		} else {
			api_redirect ( 'user_import.php?message=' . urlencode ( get_lang ( 'FileImported' ) ) );
		}
		
		my_delete ( $_FILES ['import_file'] ['tmp_name'] );
		
		$errors = validate_data ( $users );
		if (count ( $errors ) == 0) {
			$users = complete_missing_data ( $users, getgpc ( 'import_dept' ) );
			save_data ( $users );
			$redirect_url = 'user_list.php?action=show_message&message=' . urlencode ( get_lang ( 'FileImported' ) );
			tb_close ( $redirect_url );
		}
		
		return $errors;
	}
}

function parse_upload_data($file) {
	//$users = Import :: csv_to_array($file);
	$data = Import::parse_to_array ( $file, 'xls' );
	$users = $data ['data'];
	if (is_array ( $users ) && count ( $users ) > 0) {
		foreach ( $users as $index => $user ) {
			if (strtolower ( $user ['Status'] == 'student' )) {
				$user ['Status'] = STUDENT;
			}
			if (strtolower ( $user ['Status'] == 'teacher' )) {
				$user ['Status'] = COURSEMANAGER;
			}
			
			$user ['FirstName'] = mb_convert_encoding ( $user ['FirstName'], SYSTEM_CHARSET, getgpc('import_encoding') );
			//$user ['ClassName'] = mb_convert_encoding ( $user ['ClassName'], SYSTEM_CHARSET, $_POST ['import_encoding'] );
			$user ['OfficialCode'] = mb_convert_encoding ( $user ['OfficialCode'], SYSTEM_CHARSET, getgpc('import_encoding') );
			$users [$index] = $user;
		}
		return $users;
	}
	return array ();
}

/**
 * 验证导入的用户数据
 * @param unknown_type $users
 */
function validate_data($users) {
	global $defined_auth_sources;
	global $_configuration;
	$errors = array ();
	$usernames = array ();
	$emails = array ();
	foreach ( $users as $index => $user ) {
		$tmp_cur_user = $user ['UserName'];
		if (! empty ( $tmp_cur_user ) and ! is_root ( $tmp_cur_user )) {
			$user ['Status'] = STUDENT;
			
			//1. 检查必填字段
			$mandatory_fields = array ('UserName' ); //某些不能为空的字段
			if (api_get_setting ( 'registration', 'email' ) == 'true') {
				$mandatory_fields [] = 'Email';
			}
			foreach ( $mandatory_fields as $key => $field ) {
				if (empty ( $user [$field] ) || strlen ( $user [$field] ) == 0) { //UserName值
					$user ['error'] = get_lang ( $field . 'Mandatory' );
					$errors [$index] = $user;
				}
			}
			
			//2. 检查登录名
			if (! empty ( $user ['UserName'] ) && strlen ( $user ['UserName'] ) != 0) {
				//2.1. check if no username was used twice in import file
				if (isset ( $usernames [$user ['UserName']] )) {
					$user ['error'] = get_lang ( 'UserNameUsedTwice' );
					$errors [$index] = $user;
				}
				
				$usernames [$user ['UserName']] = 1;
				//2.2. check if username isn't allready in use in database
				if (! UserManager::is_username_available ( $user ['UserName'] )) {
					$user ['error'] = get_lang ( 'UserNameNotAvailable' );
					$errors [$index] = $user;
				}
				
				//2.3. check if username isn't longer than the 20 allowed characters
				if (strlen ( $user ['UserName'] ) > 30) {
					$user ['error'] = get_lang ( 'UserNameTooLong' );
					$errors [$index] = $user;
				}
			}
			
			//5. Check authentication source
			if ($user ['AuthSource'] && strlen ( $user ['AuthSource'] ) != 0) {
				if (! in_array ( $user ['AuthSource'], $defined_auth_sources )) {
					$user ['error'] = get_lang ( 'AuthSourceNotAvailable' );
					$errors [$index] = $user;
				}
			}
			
			//6. check email
			if (! empty ( $user ['Email'] ) && strlen ( $user ['Email'] ) != 0) {
				//6.1. check if no username was used twice in import file
				if (isset ( $emails [$user ['Email']] )) {
					//$user ['error'] = get_lang ( 'EmailUsedTwice' );
				//$errors [$index] = $user;
				}
				$emails [$user ['Email']] = 1;
				
				if (! is_email ( trim ( $user ['Email'] ) )) {
					$user ['error'] = '邮箱地址格式错误';
					$errors [$index] = $user;
				}
			
		//2.2. check if username isn't allready in use in database
			/*if (! UserManager::is_email_available ( $user ['Email'] )) {
					$user ['error'] = get_lang ( 'EmailNotAvailable' );
					$errors [$index] = $user;
				}*/
			}
		}
	}
	return $errors;
}

function complete_missing_data($users, $import_dept = 0) {
	foreach ( $users as $index => $user ) {
		//1. Create a username if necessary
		if (! isset ( $user ['UserName'] ) || strlen ( $user ['UserName'] ) == 0) {
			$username = strtolower ( ereg_replace ( '[^a-zA-Z]', '', substr ( $user ['FirstName'], 0, 3 ) . ' ' . substr ( $user ['LastName'], 0, 4 ) ) );
			if (! UserManager::is_username_available ( $username )) {
				$i = 0;
				$temp_username = $username . $i;
				while ( ! UserManager::is_username_available ( $temp_username ) ) {
					$temp_username = $username . ++ $i;
				}
				$username = $temp_username;
			}
			$users [$index] ['UserName'] = $username;
		}
		//2. generate a password if necessary
		if (empty ( $user ['Password'] ) || strlen ( $user ['Password'] ) == 0) {
			$users [$index] ['Password'] = api_generate_password ();
		}
		
		$users [$index] ['Status'] = STUDENT;
		
		//4. set authsource if not allready set
		if (! isset ( $user ['AuthSource'] ) || strlen ( $user ['AuthSource'] ) == 0) {
			$users [$index] ['AuthSource'] = PLATFORM_AUTH_SOURCE;
		}
		
		if (empty ( $import_dept )) { //使用文件中部门
			if (is_not_blank ( $user ['DeptNo'] )) {
				$tbl_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
				$sql = "SELECT id FROM " . $tbl_dept . " WHERE dept_no=" . Database::escape ( trim ( $user ['DeptNo'] ) );
				$users [$index] ['DeptID'] = Database::get_scalar_value ( $sql );
			} else {
				$users [$index] ['DeptID'] = 0;
			}
		} else { // 导入到某部门中去
			$users [$index] ['DeptID'] = $import_dept;
		}
	}
	return $users;
}

function save_data($users) {
	global $_configuration;
	global $restrict_org_id;
	$user_table = Database::get_main_table ( TABLE_MAIN_USER );
	$platformLanguage = api_get_setting ( 'platformLanguage' );
	$sendMail = getgpc ( 'sendMail', 'P' ) ? 1 : 0;
	$ip = get_onlineip ();
	foreach ( $users as $index => $user ) {
		if (in_array ( $user ['UserName'], $_configuration ['default_administrator_name'] )) continue;
		$user_id = UserManager::create_user ( $user ['FirstName'], $user ['JobTitle'], $user ['Status'], $user ['Email'], $user ['UserName'], $user ['Password'], $user ['OfficialCode'], $platformLanguage, $user ['PhoneNumber'], $user ['Picture'], $user ['AuthSource'], '0000-00-00 00:00:00', 
				$user ['Active'], $user ['DeptID'], '', $user ['Sex'], $user ['CredentialType'], $user ['CredentialNo'], $user ['ZipCode'], $user ['Address'], $user ['Mobile'], $user ['QQ'], $user ['MSN'], '', $restrict_org_id, $ip );
		
		$log_msg = get_lang ( 'ImportUser' ) . "id=" . $user_id;
		api_logging ( $log_msg, 'User' );
		
		if ($sendMail) {
			//$emailTo = '"' . $user['FirstName'] . '" <' . $user['Email'] . '>';
			$emailTo = trim ( $user ['Email'] );
			$emailSubject = get_lang ( 'YourReg' );
			$emailBody = get_lang ( 'Dear' ) . ' ' . $user ['FirstName'] . ": <br/>" . get_lang ( 'YouAreReg' ) . ' "' . get_setting ( 'siteName' ) . '". <br/>' . get_lang ( 'Settings' ) . "<br/>";
			$emailBody .= get_lang ( 'TheU' ) . ' : ' . $user ['UserName'] . "<br/>" . get_lang ( 'siteAddr' ) . ' : ' . api_get_path ( 'WEB_PATH' ) . " <br/><br/>";
			
			email_body_txt_add ( $emailBody );
			api_email_wrapper ( $emailTo, $emailSubject, $emailBody );
		}
	
	}
}

$language_file = array ('admin', 'registration' );
include ('../../inc/global.inc.php');
api_protect_admin_script ();
require_once (api_get_path ( LIBRARY_PATH ) . 'import.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$defined_auth_sources [] = PLATFORM_AUTH_SOURCE;
if (is_array ( $extAuthSource )) {
	$defined_auth_sources = array_merge ( $defined_auth_sources, array_keys ( $extAuthSource ) );
}

$form = new FormValidator ( 'user_import' );
//$form->addElement ( 'header', 'header', get_lang ( 'ImportUserListXMLCSV' ) );


//选择文件
$form->addElement ( 'file', 'import_file', get_lang ( 'ImportFileLocation' ), array ('style' => "width:60%", 'class' => 'inputText' ) );
$form->addRule ( 'import_file', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$allowed_file_types = array ('csv', 'xls' );
$form->addRule ( 'import_file', get_lang ( 'InvalidExtension' ) . ' (' . implode ( ',', $allowed_file_types ) . ')', 'filetype', $allowed_file_types );

$objDept = new DeptManager ();
$depts = $objDept->get_all_dept_tree ( FALSE );
foreach ( $depts as $dept ) {
	$dept_options [$dept ['id']] = str_repeat ( '&nbsp;&nbsp;', $dept ['level'] ) . $dept ['dept_name'];
}
$dept_options = array_insert_first ( $dept_options, array ('0' => '---' . get_lang ( 'UserDeptCodeInXls' ) . '---' ) );
$form->addElement ( 'select', 'import_dept', get_lang ( 'ImportToDept' ), $dept_options );
$defaults ['import_dept'] = (isset ( $_GET ['dept_id'] ) ? getgpc ( "dept_id", 'G' ) : 0);

//是否发送邮件
$group = array ();
$group [] = $form->createElement ( 'radio', 'sendMail', null, get_lang ( 'Yes' ), 1 );
$group [] = $form->createElement ( 'radio', 'sendMail', null, get_lang ( 'No' ), 0 );
$form->addGroup ( $group, 'sendMail', get_lang ( 'SendMailToUsers' ), null, false );

$group = array ();
$group [] = $form->createElement ( 'submit', 'submit', get_lang ( 'Ok' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$defaults ['sendMail'] = '0';
//$defaults ['import_encoding'] = get_default_encoding ();
$form->setDefaults ( $defaults );
Display::setTemplateBorder ( $form, '98%' );
if ($form->validate ()) {
	$errors = doAction ();
}

$tool_name = get_lang ( 'ImportUserListXMLCSV' );
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( $tool_name, FALSE );

$user = array ();
$users = array ();

if (count ( $errors ) > 0) {
	$error_message = '<ul>';
	$error_message .= '<li>' . get_lang ( 'ErrorsWhenImportingFile' ) . ' :</li>';
	foreach ( $errors as $index => $error_user ) {
		$error_message .= '<li>' . sprintf ( get_lang ( "LineN" ), $index ) . ":&nbsp;" . $error_user ['UserName'] . ' -- ' . $error_user ['FirstName'] . ' ' . $error_user ['LastName'] . ': ';
		$error_message .= $error_user ['error'];
		$error_message .= '</li>';
	}
	$error_message .= '</ul>';
	Display::display_error_message ( $error_message, false );
}

//提示帮助


echo '<div id="myOnPageContent" style="display:none"><p>' . get_lang ( 'UserImportNotes' ) . '</p></div>';
echo '<div><div style="float:left"><input alt="#TB_inline?height=220&amp;width=450&amp;inlineId=myOnPageContent" title="' . get_lang ( 'UserGuideAndNotice' ) . '" class="thickbox thickbox_btn" type="button" value="' . get_lang ( "UserGuideAndNotice" ) . '" /></div>';
echo '<div style="float:left"><a href="' . api_get_path ( WEB_PATH ) . 'storage/examples/import_files/example_import_users.xls">Excel导入模板</a></div>';
echo '</div><div style="clear:both"></div>';
//Display::display_warning_message(get_lang('UserImportNotes'),false);
$form->display ();

Display::display_footer ();
