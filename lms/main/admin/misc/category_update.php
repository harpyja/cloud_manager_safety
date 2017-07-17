<?php
$language_file = array ('admin' );
$cidReset = true;

include_once ('../../inc/global.inc.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
api_protect_admin_script ();

$this_module = isset ( $_REQUEST ['module'] ) ? getgpc ( 'module' ) : '';
$tbl_category = Database::get_main_table ( TABLE_CATEGORY );
$id = isset ( $_GET ['id'] ) ? intval(getgpc ( 'id', 'G' )) : '0';

$sql = "SELECT * FROM " . $tbl_category . " WHERE id=" . Database::escape ( $id );
if ($id) $cateogry_info = Database::fetch_one_row ( $sql, fales, __FILE__, __LINE__ );

$htmlHeadXtra [] = '<script type="text/javascript" src="' . api_get_path ( WEB_JS_PATH ) . 'commons.js"></script>';

if (! empty ( $_GET ['message'] )) {
	$message = urldecode ( getgpc('message') );
}
//$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('Exam'));
$tool_name = get_lang ( 'AddCategory' );

$form = new FormValidator ( 'category_ae' );

//$form->addElement ( 'header', 'header', $tool_name );
$form->addElement ( 'hidden', 'module', $this_module );
$form->addElement ( 'hidden', 'action', is_equal (getgpc("action","G"), 'edit' ) ? 'edit_save' : 'add_save' );
$form->addElement ( 'hidden', 'id', intval(getgpc('id')) );

$form->addElement ( 'text', 'name', get_lang ( 'CategoryName' ), array ('maxlength' => 40, 'style' => "width:70%", 'class' => 'inputText' ) );
$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$defaults ['name'] = $cateogry_info ['name'];

//编号
$form->addElement ( 'text', 'code', get_lang ( 'CategoryCode' ), array ('style' => "width:300px", 'class' => 'inputText' ) );
//$form->addRule('categoryCode', get_lang('ThisFieldIsRequired'), 'required');
$form->addRule ( 'code', get_lang ( 'OnlyLettersAndNumbersAllowed' ), 'username' );
$form->applyFilter ( 'visual_code', 'strtoupper' );

//提交按钮
$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="save"' );
//$group[] = $form->createElement('style_button', 'button',get_lang('Back'), array('type'=>'button','value'=>get_lang("Back"),'class'=>"save",'onclick'=>'location.href=\'pool_list.php\';'));
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ( $defaults );

Display::setTemplateBorder ( $form, '98%' );

// Validate form
if ($form->validate ()) {
	$data = $form->exportValues ();
	$this_module = $data ['module'];
	$cate_name = Database::escape_string ( $data ['name'] );
	$pos = Database::escape_string ( $data ['pos'] );
	$desc = Database::escape_string ( $data ['description'] );
	$cate_code = trim ( $data ['code'] );
	if (is_equal (getgpc("action"), 'add_save' )) {
		$sql = "SELECT MAX(sort_order) FROM " . $tbl_category . " WHERE module='" . $this_module . "'";
		$pos = Database::get_scalar_value ( $sql ) + 1;
		
		$sql_row = array ('parent_id' => 0, 'module' => $this_module, 'name' => $cate_name, 'description' => $desc, 'code' => $cate_code, 'sort_order' => $pos, 'created_date' => date ( "Y-m-d H:i:s" ), 'last_updated_date' => date ( "Y-m-d H:i:s" ) );
		$sql = Database::sql_insert ( $tbl_category, $sql_row );
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$redirect_url = 'category_list.php?module=' . $this_module;
		tb_close ( $redirect_url );
	}
	
	if (is_equal ( getgpc("action"), 'edit_save' )) {
		$sql_row = array ('name' => $cate_name, 'description' => $desc, 'sort_order' => $pos, 'last_updated_date' => date ( "Y-m-d H:i:s" ), 'code' => $cate_code );
		$sql = Database::sql_update ( $tbl_category, $sql_row, " id=" . Database::escape ( intval($data ['id']) ) );
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$redirect_url = 'category_list.php?module=' . $this_module;
		tb_close ( $redirect_url );
	}

}

Display::display_header ( $tool_name, FALSE );

if (! empty ( $message )) {
	Display::display_normal_message ( urldecode ( $message ) );
}

$form->display ();
