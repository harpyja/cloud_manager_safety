<?php
$language_file = array ('admin' );
$cidReset = true;
require_once ('../../inc/global.inc.php');
api_protect_admin_script ();
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( SYS_CODE_PATH ) . 'admin/admin.lib.inc.php');

$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
define ( UNIQUE_DEPT_NO, TRUE );
$pid = isset ( $_GET ['pid'] ) ? intval(getgpc('pid')) : '0';
$org_id = intval(getgpc ( "org_id" ));
$action = getgpc ( 'action' );
$deptObj = new DeptManager ();
$dept_tree = array ();

if (isset ( $_REQUEST ['pid'] )) {
	$parent_dept_info = $deptObj->get_dept_info ( intval(getgpc('pid')) );
}

function check_dept_no($inputValue) {
	global $table_dept;
	$sql = "SELECT * FROM " . $table_dept . " WHERE dept_no='" . Database::escape_string ( $inputValue ) . "'";
	return ! Database::if_row_exists ( $sql );
}

$top_dept = $deptObj->get_top_dept ();
if ($top_dept && ! $pid) {
	$pid =  intval( $top_dept ['id']);
	$pname = $top_dept ['dept_name'];
}

$form = new FormValidator ( 'dept_add' );

$form->addElement ( 'hidden', 'pid', intval(getgpc('pid')) );

//$form->addElement ( 'header', 'header', get_lang ( 'AddDept' ) );
$form->addElement ( 'hidden', 'action', 'dept_add_save' );
$form->addElement ( 'hidden', 'parent_dept_id', $pid );
$form->addElement ( 'hidden', 'org_id', $org_id );

$form->addElement ( "text", "parent_dept_name", get_lang ( "DeptParent" ), array ('style' => "width:250px", 'class' => 'inputText', 'maxlength' => 30 ) );
$defaults ['parent_dept_name'] = $deptObj->get_dept_path ( $pid, TRUE ); //$parent_dept_info['dept_name'];
$form->freeze ( array ("parent_dept_name" ) );

$form->add_textfield ( 'dept_no', get_lang ( 'DeptNo' ), false, array ('style' => "width:250px", 'class' => 'inputText', 'maxlength' => 30 ) );
$form->applyFilter ( 'dept_no', 'strtoupper' );
$form->addRule ( 'dept_no', get_lang ( 'Max' ), 'maxlength', 50 );
$form->addRule ( 'dept_no', get_lang ( 'DeptNoMustBeAlphanumeric' ), 'username' );
if (UNIQUE_DEPT_NO) {
	$form->addRule ( 'dept_no', get_lang ( 'ThisFieldIsRequired' ), 'required' );
	$form->addRule ( 'dept_no', get_lang ( 'DeptNoMustBeUnique' ), 'callback', 'check_dept_no' );
}

$form->add_textfield ( 'dept_name', get_lang ( 'DeptName' ), true, array ('style' => "width:250px", 'class' => 'inputText' ) );
$form->addRule ( 'dept_name', get_lang ( 'Max' ), 'maxlength', 50 );
$form->addElement ( 'hidden', 'enabled', '1' );

//备注说明
$form->addElement ( 'textarea', 'description', get_lang ( 'Remark' ), array ('style' => 'width:80%;height:60px', 'class' => 'inputText' ) );

//提交
$group = array ();
$group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit', get_lang ( 'Save' ), 'class="save"' );
$group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit_plus', get_lang ( 'SaveAndAdd' ), 'class="plus"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, null, '&nbsp;', '&nbsp;&nbsp;' );

$form->setDefaults ( $defaults );
Display::setTemplateBorder ( $form, '98%' );

if ($form->validate ()) {
	$data = $form->getSubmitValues ();
	//var_dump($data);exit;
	if (is_equal ( $data ['action'], 'dept_add_save' )) { //新增
		$parent_id = $data ['parent_dept_id']; //$data['parent_dept_id']['TO_ID']; 
		$dept_no = $data ['dept_no'];
		$dept_name = $data ['dept_name'];
		$dept_desc = $data ['description'];
		if (empty ( $dept_desc )) $dept_desc = $dept_name;
		$dept_admin = $data ['dept_admin'] ['TO_ID_ADMIN'];
		$org_id = $data ['org_id'];
		$deptObj->init ();
		$deptObj->dept_add ( $parent_id, $dept_no, $dept_name, $dept_desc, 1, $dept_admin, $org_id );
		
		if (isset ( $data ['submit_plus'] )) {
			$redirect_url = 'dept_add.php?org_id=' . $data ['org_id'] . '&pid=' . $data ['pid'] . '&refresh=1&message=' . urlencode ( get_lang ( 'AddDeptSuccess' ) );
			api_redirect ( $redirect_url );
		
		} else {
			$redirect_url = "dept_list.php?org_id='.$data ['org_id'].'&pid=" . $data ['pid'] . "&refresh=1&message=" . urlencode ( get_lang ( 'AddDeptSuccess' ) );
			tb_close ( $redirect_url );
		}
	}
}

Display::display_header ( NULL, FALSE );
if (! empty ( $_GET ['message'] )) Display::display_normal_message ( stripslashes ( urldecode (getgpc("message","G") ) ) );
if (isset ( $_GET ['refresh'] )) echo '<script>self.parent.refresh_tree();</script>';
$form->display ();
Display::display_footer ();
