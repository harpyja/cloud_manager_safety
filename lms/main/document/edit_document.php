<?php
/*
 ==============================================================================
 编辑文档信息
 ==============================================================================
 */

$language_file = 'document';
include ('../inc/global.inc.php');

$is_allowedToEdit = api_is_allowed_to_edit ();
if (! $is_allowedToEdit) api_not_allowed ();

include (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');

$document_id = intval(getgpc ( 'id', 'G' ));
$dbTable = Database::get_course_table ( TABLE_DOCUMENT );
$baseWorkDir = api_get_path ( SYS_COURSE_PATH ) . api_get_course_code () . "/document";

$sql = "SELECT comment,title,path,display_order FROM $dbTable WHERE id=" . Database::escape ( $document_id );
$defaults = Database::fetch_one_row ( $sql, FALSE, __FILE__, __LINE__ );

$filepath = $baseWorkDir . $defaults ['path'];

$nameTools = get_lang ( 'EditDocument' );

$form = new FormValidator ( 'formEdit', 'post' );
//$form->addElement ( 'header', 'header', get_lang ( 'EditDocument' ) );
$form->addElement ( 'hidden', 'document_id', $document_id );

$form->addElement ( 'text', 'title', get_lang ( 'Title' ), array ('style' => "width:350px", 'class' => 'inputText' ) );
$form->addRule ( 'title', get_lang ( 'ThisFieldIsRequired' ), 'required' );

//显示顺序
$form->addElement ( 'text', 'display_order', get_lang ( 'DisplayOrder' ), array ('style' => "width:50px", 'class' => 'inputText' ) );
$form->addRule ( 'display_order', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );

//备注说明
$form->addElement ( 'textarea', 'comment', get_lang ( 'Description' ), array ('cols' => 50, 'rows' => 4, 'class' => 'inputText' ) );

$group = array ();
$group [] = $form->createElement ( 'submit', 'submit', get_lang ( 'Ok' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ( $defaults );
Display::setTemplateBorder ( $form, '98%' );
if ($form->validate ()) {
	$data = $form->getSubmitValues ();
	$sql_data = array ('title' => $data ['title'], 'comment' => $data ['comment'], 'display_order' => $data ['display_order'] );
	$sql = Database::sql_update ( $dbTable, $sql_data, "id=" . Database::escape ( $data ['document_id'] ) );
	if (api_sql_query ( $sql, __FILE__, __LINE__ )) tb_close ();
}

Display::display_header ( null, FALSE );
$form->display ();

Display::display_footer ();
