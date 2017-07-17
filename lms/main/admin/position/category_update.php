<?php
$language_file = array ('exam', 'admin' );
$cidReset = true;

include_once ('../../inc/global.inc.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
api_protect_admin_script ();

$this_module = 'position';
$tbl_category = Database::get_main_table ( TABLE_CATEGORY );
$table_position = Database::get_main_table ( TABLE_MAIN_SYS_POSITION );

$htmlHeadXtra [] = '<script type="text/javascript" src="' . api_get_path ( WEB_JS_PATH ) . 'commons.js"></script>';
$htmlHeadXtra [] = '
<script language="JavaScript" type="text/JavaScript">
<!--
//-->
</script>';
if (! empty ( $_GET ['message'] )) {
	$message = urldecode ( getgpc('message') );
}
//$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('Exam'));
$tool_name = get_lang ( 'AddPosition' );

$form = new FormValidator ( 'exam_ae' );

$form->addElement ( 'header', 'header', $tool_name );

$form->addElement ( 'text', 'name', get_lang ( 'CategoryName' ), array ('maxlength' => 40, 'style' => "width:70%", 'class' => 'inputText' ) );
$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$form->addElement ( 'hidden', 'moudle', 'position' );

//其它说明
if (api_get_setting ( 'wcag_anysurfer_public_pages' ) == 'true') {
	$form->addElement ( 'textarea', 'description', get_lang ( 'Remark' ), array ('cols' => 50, 'rows' => 5, 'class' => 'inputText' ) );
} else {
	$fck_attribute ['Width'] = '100%';
	$fck_attribute ['Height'] = '150';
	$fck_attribute ['ToolbarSet'] = 'Comment';
	$fck_attribute ["ToolbarStartExpanded"] = "false";
	$form->add_html_editor ( 'description', get_lang ( 'Remark' ), false );
}

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
	$parent_id = (is_equal ( $_REQUEST ['action'], 'add_pool_save' )) ? Database::escape_string (intval(getgpc("pid","P")) ) : 0;
	$pool_name = Database::escape_string ( $data ['pool_name'] );
	//$pool_pos=Database::escape_string($data['pool_pos']);
	$pool_desc = Database::escape_string ( $data ['description'] );
	if (is_equal ( $_REQUEST ['action'], 'add_pool_save' )) {
		$sql = "SELECT MAX(display_order) FROM " . $tbl_exam_question_pool . " WHERE pid='" . $parent_id . "'";
		$pool_pos = Database::get_scalar_value ( $sql ) + 1;
		
		$sql = "insert into " . $tbl_exam_question_pool . "(pid, pool_name, pool_desc,display_order) values
			('" . $parent_id . "','" . $pool_name . "',	'" . $pool_desc . "','" . $pool_pos . "')";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		$redirect_url = 'pool_list.php';
		echo '<script>self.parent.location.href="' . $redirect_url . '";self.parent.tb_remove();self.parent.refresh_tree();</script>';
		exit ();
	
	//api_redirect($redirect_url);
	}
	
	if (is_equal ( $_REQUEST ['action'], 'edit_pool_save' )) {
	
	}
}

Display::display_header ( $tool_name, FALSE );
//api_display_tool_title($tool_name);


if (! empty ( $message )) {
	Display::display_normal_message ( urldecode ( stripslashes ( $message ) ) );
}

$form->display ();
Display::display_footer ();