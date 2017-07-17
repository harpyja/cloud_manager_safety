<?php
$language_file = array ('admin' );
$cidReset = true;
require_once ('../../inc/global.inc.php');
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();

require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( SYS_CODE_PATH ) . 'admin/admin.lib.inc.php');

$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
$table_user_role = Database::get_main_table ( TABLE_MAIN_USER_ROLE );
$view_sys_user = Database::get_main_table ( VIEW_USER );

$pid = isset ( $_GET ['pid'] ) ? intval(etgpc('pid')) : '0';
$action = isset ( $_REQUEST ['pid'] ) ? getgpc ( 'action' ) : 'add';

$deptObj = new DeptManager ();

if (isset ( $_REQUEST ['pid'] )) {
	$parent_dept_info = $deptObj->get_dept_info (intval(getgpc ( 'pid' )) );
}

if (isset ( $_REQUEST ['id'] )) {
	$dept_info = $deptObj->get_dept_info (intval(getgpc ( "id" )) );
}

function check_dept_no($inputValue) {
	global $table_dept;
	//var_dump($inputValue);exit;
	if ($inputValue ['action'] == 'add_save') {
		$sql = "SELECT * FROM " . $table_dept . " WHERE dept_no='" . escape ( $inputValue ['dept_no'] ) . "' AND pid=" . DEPT_TOP_ID;
	}
	
	if ($inputValue ['action'] == 'edit_save' && $inputValue ['id']) {
		$sql = "SELECT * FROM " . $table_dept . " WHERE dept_no='" . escape ( $inputValue ['dept_no'] ) . "' AND pid=" . DEPT_TOP_ID . " AND id<>'" . escape ( $inputValue ['id'] ) . "'";
	}
	if (Database::if_row_exists ( $sql, __FILE__, __LINE__ )) {
		$errors ['dept_no'] = get_lang ( 'DeptNoMustBeUnique' );
	}
	return empty ( $errors ) ? TRUE : $errors;
}

$htmlHeadXtra [] = '<script type="text/javascript">
	$(document).ready( function() {

		' . ((is_equal ( getgpc('action'), 'add' )) ? '$("tr.containerBody:eq(30)").hide();' : '') . '
		
		$("#create_new_admin1").click(function(){
			//$("tr.containerBody:eq(3)").hide();
		});
		
		$("#create_new_admin0").click(function(){
			$("tr.containerBody:eq(3)").show();
		});
	
		$("#org_form").submit(function(){
			if($("#dept_name").val()==""){
				$("#dept_name").parent().find(".onError").remove();
				$("#dept_name").parent().append("<div class=\'onError\'>' . get_lang ( "ThisFieldIsRequired" ) . '</div>");
				return false;
			}
			' . (is_equal ( getgpc('action'), 'add' ) ? '
			var isCreateNew=($("input[@name=\'create_new_admin\']:checked").val());
			if(isCreateNew=="0" &&	$("#TO_NAME_ADMIN").val()==""){
				$("#TO_NAME_ADMIN").parent().find(".onError").remove();
				$("#TO_NAME_ADMIN").parent().append("<div class=\'onError\'>' . get_lang ( "PleaseSelectAOrgAdmin" ) . '</div>");
				return false;
			}' : "") . '
			return true;
			});
		
	});
	</script>';

//$tool_name = get_lang ( 'DeptList' );
//Display::display_header ( $tool_name );


$form = new FormValidator ( 'org_form' );

$form->addElement ( 'hidden', 'pid', intval(getgpc('pid') ));
$form->addElement ( 'hidden', 'id', intval(getgpc('id')) );
//$form->addElement ( 'header', 'header', is_equal ( $_GET ['action'], 'edit' ) ? get_lang ( "OrgEdit" ) : get_lang ( 'OrgAdd' ) );
$form->addElement ( 'hidden', 'action', is_equal ( getgpc('action'), 'edit' ) ? 'edit_save' : 'add_save' );

$form->add_textfield ( 'dept_no', get_lang ( 'OrgNo' ), false, array ('style' => "width:250px", 'class' => 'inputText', 'maxlength' => 30 ) );
$form->addRule ( 'dept_no', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$form->add_textfield ( 'dept_name', get_lang ( 'OrgName' ), true, array ('id' => 'dept_name', 'style' => "width:250px", 'class' => 'inputText' ) );
$form->addRule ( 'dept_name', get_lang ( 'Max' ), 'maxlength', 50 );

//备注说明
$form->addElement ( 'textarea', 'description', get_lang ( 'Description' ), array ('style' => 'width:80%;height:60px', 'class' => 'inputText' ) );
//管理员  
$sql="select user_id,firstname from user where status=1 or status=10";
$re = api_sql_query ( $sql, __FILE__, __LINE__ );
 while($res=Database::fetch_array ($re)) { 
   $teachers[$res['user_id']]=$res['firstname'];
 } 
$form->addElement ( 'select', 'dept_admin', get_lang ( "管理员" ), $teachers, array ('id' => "category_code", 'style' => 'height:22px;' ) );

if (is_equal ( $_GET ['action'], 'edit' )) {
	$form->freeze ( array ("dept_no" ) );
	$defaults ['dept_no'] = $dept_info ['dept_no'];
	$defaults ['dept_no'] = $dept_info ['dept_no'];
	$defaults ['dept_name'] = $dept_info ['dept_name'];
	$defaults ['description'] = $dept_info ['dept_desc'];
} elseif (is_equal ( $_GET ['action'], 'add' )) {
	$form->applyFilter ( 'dept_no', 'strtoupper' );
	$form->addRule ( 'dept_no', get_lang ( 'Max' ), 'maxlength', 30 );
	$form->addRule ( 'dept_no', get_lang ( 'DeptNoMustBeAlphanumeric' ), 'username' );

	//$form->addRule ( 'dept_no', get_lang ( 'DeptNoMustBeUnique' ), 'callback', 'check_dept_no' );
}

//提交
$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Save' ), 'id="btnSubmit" class="save"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, null, null, '&nbsp;&nbsp;' );

$form->setDefaults ( $defaults );
$form->addFormRule ( 'check_dept_no' );
Display::setTemplateBorder ( $form, '98%' );

if ($form->validate ()) {
	$data = $form->getSubmitValues ();
	//var_dump($data);exit;
	$dept_name = $data ['dept_name'];
	$dept_desc = $data ['description'];
$dept_admin=$data ['dept_admin']; 
	if(is_equal ( $data ['action'], 'add_save' )) { //新增
		$deptObj->init ();
		$parent_id = DEPT_TOP_ID;
		$dept_no = trim ( $data ['dept_no'] );
//	$dept_admin=$data ['dept_admin'];
	
		//创建机构信息
		$org_id = $deptObj->dept_add ( $parent_id, $dept_no, $dept_name, $dept_desc, 1, 1 );
		
		//更新机构
		$sql_data = array ('org_id' => $org_id );
		$sql = Database::sql_update ( $table_dept, $sql_data, "id='" . intval($org_id) . "'" );
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$log_msg = get_lang ( 'AddDeptInfo' ) . $data ['dept_name'] . "(" . $data ['dept_no'] . ",id=" . intval($org_id) . ")";
		api_logging ( $log_msg, 'DEPT', 'AddDeptInfo' );
		
		if (isset ( $data ['submit_plus'] )) {
			$redirect_url = 'org_update.php?pid=' . $data ['pid'] . '&refresh=1&message=' . urlencode ( get_lang ( 'AddDeptSuccess' ) );
			api_redirect ( $redirect_url );
		} else {
			$redirect_url = "dept_iframe.php?pid=" . $data ['pid'] . "&refresh=1&message=" . urlencode ( get_lang ( 'AddDeptSuccess' ) );
			tb_close ( $redirect_url );
		}
	} elseif (is_equal ( $data ['action'], 'edit_save' )) { //编辑
		$dept_admin_id = $data ['org_admin'] ['TO_ID_ADMIN'];
		//删除,然后重建新的

	//	$sql_data = array ('dept_name' => $data ['dept_name'], 'dept_desc' => $dept_desc ); //, "course_quota" => $data ['course_quota'], "user_quota" => $data ['user_quota'] );
$sql_data = array ('dept_name' => $data ['dept_name'], 'dept_desc' => $dept_desc,'dept_admin'=>$data ['dept_admin'] ); //, "course_quota" => $data ['course_quota'], "user_quota" => $data ['user_quota'] );
			
	$sql = Database::sql_update ( $table_dept, $sql_data, "id=" . intval(Database::escape ( $data ['id'] ) ));
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		cache(CACHE_KEY_ADMIN_DEPT,NULL);
		cache(CACHE_KEY_ADMIN_DEPT,$deptObj->get_all_dept_tree ());
		
		$redirect_url = "dept_iframe.php?pid=" . $data ['pid'] . "&refresh=1&message=" . urlencode ( get_lang ( 'AddDeptSuccess' ) );
		tb_close ( $redirect_url );
	}
}

Display::display_header ( NULL, FALSE );

$form->display ();

Display::display_footer ();
