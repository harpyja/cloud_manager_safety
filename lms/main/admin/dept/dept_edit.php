<?php
$language_file = array ('admin' );
$cidReset = true;
require_once ('../../inc/global.inc.php');
api_protect_admin_script ();

require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( SYS_CODE_PATH ) . 'admin/admin.lib.inc.php');

define ( UNIQUE_DEPT_NO, TRUE );
$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
$table_dept_admins = Database::get_main_table ( TABLE_MAIN_DEPT_ADMINS );

$pid = isset ( $_GET ['pid'] ) ? intval(getgpc('pid')) : '0';
$action = getgpc ( 'action' );

$deptObj = new DeptManager ();
$dept_info = $deptObj->get_dept_info (intval(getgpc ( 'id' )) );
//var_dump($dept_info);


if (isset ( $_REQUEST ['pid'] )) {
	$parent_dept_info = $deptObj->get_dept_info ( getgpc ( 'pid' ) );
}

function check_dept_no_editSave($inputValue) {
	global $table_dept, $dept_info;
	if ($inputValue && $dept_info ['dept_no'] == $inputValue) {return true;}
	if ($inputValue && $dept_info ['dept_no'] != $inputValue) {
		$sql = "SELECT * FROM " . $table_dept . " WHERE dept_no='" . Database::escape_string ( $inputValue ) . "'";
		return Database::number_rows ( $sql ) <= 1;
	}
	return false;
}

$htmlHeadXtra [] = '<script type="text/javascript">
	function refresh_tree(){ parent.Tree.location.reload();parent.Tree.d.openAll(); }
</script>';

$dept_tree = array ();

$top_dept = $deptObj->get_top_dept ();
if ($top_dept && ! $pid) {
	$pid = intval($top_dept ['id']);
	$pname = $top_dept ['dept_name'];
}

$form = new FormValidator ( 'dept_add' );

$form->addElement ( 'hidden', 'pid', intval(getgpc('pid')) );

//编辑
//$form->addElement ( 'header', 'header', get_lang ( 'EditDept' ) );
$form->addElement ( 'hidden', 'action', 'dept_edit_save' );
$form->addElement ( 'hidden', 'id', intval(getgpc ( 'id', 'G' )) );

//部门编号
$form->add_textfield ( 'dept_no', get_lang ( 'DeptNo' ), false, array ('style' => "width:250px", 'class' => 'inputText', 'maxlength' => 30 ) );
$form->applyFilter ( 'dept_no', 'strtoupper' );
$form->addRule ( 'dept_no', get_lang ( 'Max' ), 'maxlength', 30 );
$form->addRule ( 'dept_no', get_lang ( 'DeptNoMustBeAlphanumeric' ), 'username' );
if (UNIQUE_DEPT_NO) {
	$form->addRule ( 'dept_no', get_lang ( 'ThisFieldIsRequired' ), 'required' );
	$form->addRule ( 'dept_no', get_lang ( 'DeptNoMustBeUnique' ), 'callback', 'check_dept_no_editSave' );
}
$defaults ['dept_no'] = $dept_info ['dept_no'];

//部门名称
$form->add_textfield ( 'dept_name', get_lang ( 'DeptName' ), true, array ('style' => "width:250px", 'class' => 'inputText' ) );
$form->addRule ( 'dept_name', get_lang ( 'Max' ), 'maxlength', 50 );
$defaults ['dept_name'] = $dept_info ['dept_name'];

$form->addElement ( 'hidden', 'enabled', '1' );

//备注说明
$form->addElement ( 'textarea', 'description', get_lang ( 'Remark' ), array ('style' => 'width:80%;height:60px', 'class' => 'inputText' ) );
$defaults ['description'] = $dept_info ['dept_desc'];

//提交
$group = array ();
$group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit', get_lang ( 'Save' ), 'class="save"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, null, '&nbsp;', '&nbsp;&nbsp;' );

$form->setDefaults ( $defaults );
Display::setTemplateBorder ( $form, '98%' );

if ($form->validate ()) {
	//$data = $form->exportValues ();
	$data = $form->getSubmitValues ();
	
	if (is_equal ( $data ['action'], 'dept_edit_save' )) { //编辑
		$dept_id = intval(getgpc ( 'id', 'P' ));
		//$dept_admin = $data ['dept_admin'] ['TO_ID_ADMIN'];
		//$parent_id = $data ['parent_dept_id'] ['TO_ID'];
		$sql_data = array ("dept_no" => $data ['dept_no'], "dept_name" => $data ["dept_name"], "dept_desc" => $data ['description'], "enabled" => $data ['enabled'],/*"dept_admin"=>$dept_admin*/	);
		/*if ($dept_info ['pid'] != 0 && $parent_id) {
			$sql_data ["pid"] = $parent_id;
		}*/
		
		$dept_in_org = $deptObj->get_dept_in_org (intval(getgpc ( 'id' )) );
		$dept_org = array_pop ( $dept_in_org );
		$sql_data ['org_id'] = intval($dept_org ['id']);
		
		$sql = Database::sql_update ( $table_dept, $sql_data, "id=" . Database::escape ( $dept_id ) );
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		//增加部门管理员
		cache(CACHE_KEY_ADMIN_DEPT,null);
		cache(CACHE_KEY_ADMIN_DEPT, $deptObj->get_all_dept_tree ());

		$log_msg = get_lang ( 'EditDeptInfo' ) . $data ['dept_name'] . "(" . $data ['dept_no'] . ",id=" . intval(getgpc ( 'id', 'P' )) . ")";
		api_logging ( $log_msg, 'DEPT', 'EditDeptInfo' );
		
		$redirect_url = "dept_list.php?pid=" . $data ['pid'] . "&refresh=1&message=" . urlencode ( get_lang ( 'EditDeptSuccess' ) );
		tb_close ();
	}
	exit ();
}

Display::display_header ( NULL, FALSE );
if (! empty ( $_GET ['message'] )) Display::display_normal_message ( stripslashes ( urldecode (getgpc("message","G") ) ) );
if (isset ( $_GET ['refresh'] )) echo '<script>self.parent.refresh_tree();</script>';
$form->display ();
Display::display_footer ();
