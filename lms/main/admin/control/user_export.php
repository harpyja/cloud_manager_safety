<?php
/**
 ==============================================================================
 * @package zllms.admin
 ==============================================================================
 */

$cidReset = true;
$language_file = 'admin';
include ('../../inc/global.inc.php');
$this_section = SECTION_PLATFORM_ADMIN;
api_protect_admin_script ();//User rights  @chang_z_f 2013-07-27

//$required_roles = array (ROLE_TRAINING_ADMIN );
//if (validate_role_base_permision ( $required_roles ) === FALSE) {
//	api_deny_access ( TRUE );
//}
//$restrict_org_id = $_SESSION ['_user'] ['role_restrict'] [ROLE_TRAINING_ADMIN];

include_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'export.lib.inc.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$user_table = Database::get_main_table ( TABLE_MAIN_USER );
$tbl_dept=Database::get_main_table ( TABLE_MAIN_DEPT );
$objDept = new DeptManager ();

$dept_id = isset ( $_REQUEST ['keyword_deptid'] ) ? getgpc ( 'keyword_deptid' ) : '0';

$tool_name = get_lang ( 'ExportUserListXMLCSV' );
$htmlHeadXtra[]=Display::display_thickbox();
$form = new FormValidator ( 'export_users', "POST", null, null );

//$form->addElement ( 'header', 'header', get_lang ( 'ExportUserListXMLCSV' ) );

/*$modaldialog_select_options = array ('is_multiple_line' => false, 'MODULE_ID' => 'SINGLE_COURSE', 'open_url' => api_get_path ( WEB_CODE_PATH ) . "commons/modal_frame.php?", 'form_name' => 'export_users', 'js_select_func_name' => 'select_data' );
$form->addElement ( 'modaldialog_select', 'course_code', get_lang ( 'OnlyUsersFromCourse' ), NULL, $modaldialog_select_options );*/

//$form->addElement ( 'static', null, null, '<em>' . get_lang ( 'UserExportTip' ) . '</em>' );

//$depts = $objDept->get_sub_dept_ddl2 ( 0, 'array' );
$all_orgs=$objDept->get_all_org();
/*$sql = "select * from sys_dept WHERE pid=" . DEPT_TOP_ID . " ORDER BY dept_pos";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
while($re = mysql_fetch_assoc($res)){
    $dept[]=$re;
}
$orgs['']='---'.api_get_setting('Institution').'(所有用户)---';
foreach($dept as $val){
    $orgs[$val['id']]=$val['dept_name'];
}*/
$orgs['']='---'.api_get_setting('Institution').'(所有用户)---';
foreach($all_orgs as $org_info){
	$orgs[$org_info['id']]=$org_info['dept_name'];
}

$form->addElement ( 'select', 'keyword_deptid', '导出部门范围', $orgs, array ('id' => "dept_id", 'style' => 'height:22px;' ) );

$group = array ();
$group [] = $form->createElement ( 'submit', 'submit', get_lang ( 'Export' ), array ("id" => "sub", 'class' => "inputSubmit" ) );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$defaults ['export_encoding'] = get_default_encoding ();
$defaults ['file_type'] = 'xls';
$form->setDefaults ( $defaults );

Display::setTemplateBorder ( $form, '98%' );
$form->add_progress_bar ();
if ($form->validate ()) {
	$export = $form->exportValues ();
	$export_encoding = $export ['export_encoding'];
	set_time_limit ( 0 );
	$sql = "SELECT  u.user_id 	AS UserId,
					u.active 	AS Active,
					u.firstname AS FirstName,
					u.username	AS UserName,
					u.lastname AS JobTitle,
					u.password	AS Password,
					u.email 	AS Email,
					t.dept_no AS DeptNo,
					u.official_code	AS OfficialCode,
					u.phone		AS PhoneNumber,
					u.mobile AS Mobile,
					u.sex AS Sex,
					u.credential_no AS CredentialNo";
	$sql .= " FROM $user_table AS u LEFT JOIN $tbl_dept AS t ON u.dept_id=t.id";
	$filename = 'ExportUsers_' . date ( 'YmdHi' ); //导出文件名

	if(is_not_blank($dept_id)){
		$sub_dept_ids=$objDept->get_sub_dept_ids2($dept_id);
		if($sub_dept_ids){
			$sql.=" WHERE ".Database::create_in($sub_dept_ids,'u.dept_id');
		}
	}
	$sql .= " ORDER BY u.username";

	$data = array ();
	$data [] = array ('UserId', 'Active', 'FirstName', 'UserName','JobTitle', 'Password', 'Email','DeptNo', 'OfficialCode', 'PhoneNumber', 'Mobile', 'Sex', 'CredentialNo');
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	//$user ['FirstName'] = mb_convert_encoding ( $user ['FirstName'], $export_encoding, SYSTEM_CHARSET );  CSV用
	while ( $user = Database::fetch_array ( $res, 'ASSOC' ) ) {
		$data [] = $user;
	}

	Export::export_table_data( $data, $filename, 'xls' );
//	Export::export_table_data_user ( $data, $filename, 'xls' );//changzf 12/12/15 location
}

Display::display_header($tool_name,FALSE);
Display::display_normal_message('注意: 当导出的用户数比较大时会非常缓慢甚至会失败,出现异常! <br/>建议分批导出,最好一次性导出记录不要超过200条为宜.',false);
$form->display ();



Display::display_footer ();
