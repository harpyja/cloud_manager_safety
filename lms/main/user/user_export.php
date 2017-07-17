<?php
/**
==============================================================================
   导出课程内的用户
 * @package zllms.user
==============================================================================
 */
$language_file = 'admin';
include ('../inc/global.inc.php');

include_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");
include_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'export.lib.inc.php');

if (! isset ( $_cid )) {
	header ( "location: " . $_configuration ['root_web'] );
}

$currentCourseID = $_course ['sysCode'];

$tool_name = get_lang ( 'ExportUserListXMLCSV' );
$interbreadcrumb [] = array ("url" => "user.php", "name" => get_lang ( "CourseUsers" ) );

set_time_limit ( 0 );

$form = new FormValidator ( 'export_users' );

$form->addElement ( 'header', 'header', get_lang ( 'ExportUserListXMLCSV' ) );

$group = array ();
$group [] = $form->createElement ( 'radio', 'file_type', null, 'CSV', 'csv' );
$group [] = $form->createElement ( 'radio', 'file_type', null, 'Excel', 'xls' );
$group [] = $form->createElement ( 'radio', 'file_type', null, 'XML', 'xml' );
$form->addGroup ( $group, 'file_type', get_lang ( 'OutputFileType' ), null, false );

//liyu: 屏蔽是否加上CSV头
//$form->addElement('checkbox', 'addcsvheader', get_lang('AddCSVHeader'), get_lang('YesAddCSVHeader'),'1');
$form->addElement ( 'hidden', 'addcsvheader', '1' );

$encodings = get_encodings ();
$form->addElement ( 'select', 'export_encoding', get_lang ( 'ExportEncoding' ), $encodings );

$group = array ();
$group [] = $form->createElement ( 'submit', 'submit', get_lang ( 'Ok' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$defaults ['export_encoding'] = get_default_encoding ();
$defaults ['file_type'] = 'csv';
//$defaults['addcsvheader']='1';
$form->setDefaults ( $defaults );

Display::setTemplateBorder ( $form, '100%' );

if ($form->validate ()) {
	$export = $form->exportValues ();
	$file_type = $export ['file_type'];
	$export_encoding = $export ['export_encoding'];
	
	$table_user = Database::get_main_table ( TABLE_MAIN_USER );
	$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	$course = api_get_course_info ();
	$sql = "SELECT u.user_id as UserId, u.official_code as OfficialCode,
					   u.firstname as FirstName,u.email as Email
				FROM $table_user u, $table_course_user cu WHERE cu.user_id = u.user_id AND cu.course_code = '" . $currentCourseID . "' ORDER BY lastname ASC";
	
	$filename = 'export_users_' . $currentCourseID . '_' . date ( 'YmdHis' );
	
	$data = array ();
	if ($file_type == 'csv') $export ['addcsvheader'] == '1'; //liyu:导出的CSV文件同时能导入到平台内
	if ($export ['addcsvheader'] == '1' && ($export ['file_type'] == 'csv' || $export ['file_type'] == 'xls')) {
		if (api_get_setting ( 'use_session_mode' ) != "true") {
			$data [] = array ('UserId', 'OfficialCode', 'FirstName', 'Email' );
		} else {
			$data [] = array ('UserId', 'OfficialCode', 'FirstName', 'Email', 'Session' );
		}
	}
	
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	
	while ( $user = Database::fetch_array ( $res, 'ASSOC' ) ) {
		if (strtolower ( $export_encoding ) != strtolower ( SYSTEM_CHARSET )) {
			//$user['LastName'] = mb_convert_encoding($user['LastName'], $export_encoding, SYSTEM_CHARSET);
			$user ['FirstName'] = mb_convert_encoding ( $user ['FirstName'], $export_encoding, SYSTEM_CHARSET );
		}
		$data [] = $user;
	}
	switch ($file_type) {
		case 'xml' :
			Export::export_table_xml ( $data, $filename, 'Contact', 'Contacts' );
			break;
		case 'csv' :
			Export::export_table_csv ( $data, $filename );
			break;
		case 'xls' :
			Export::export_table_xls ( $data, $filename );
			break;
	}
}

Display::display_header($tool_name,FALSE);
Display::display_warning_message ( get_lang ( 'UserExportNotes' ), false );
$form->display ();

Display::display_footer ();