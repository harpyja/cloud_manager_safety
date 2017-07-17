<?php
$language_file = 'survey';
require_once ('../inc/global.inc.php');

api_protect_admin_script ();

require_once ('survey.inc.php');

$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( 'action' ) : "");
$survey_id = isset ( $_REQUEST ['survey_id'] ) ? intval(getgpc ( "survey_id" )) : "";

$g_action=  getgpc('action');
if (is_equal ( $g_action, "edit" )) {
	$sql = "SELECT * FROM " . $tbl_survey_question_group . " WHERE id=" . Database::escape ( intval(getgpc ( "id" )) );
	$values = Database::fetch_one_row ( $sql, __FILE__, __LINE__ );
}

$form = new FormValidator ( 'survey_items_update' );

$form->addElement ( 'header', 'header', (is_equal ( $g_action, "add" ) ? get_lang ( 'Add' ) : get_lang ( "Edit" )) );
if (is_equal ( $g_action, "add" )) {
	$form->addElement ( 'hidden', 'action', 'add_save' );
	$form->addElement ( 'hidden', 'survey_id', $survey_id );
	$sql = "SELECT MAX(display_order) FROM " . $tbl_survey_question_group . " WHERE survey_id='" . escape ( $survey_id ) . "'";
	$values ["display_order"] = Database::get_scalar_value ( $sql ) + 1;
}

if (is_equal ( $g_action, "edit" )) {
	$form->addElement ( 'hidden', 'action', 'edit_save' );
	$form->addElement ( 'hidden', 'id', intval(getgpc ( "id", "G" )) );
}

$form->add_textfield ( 'name', get_lang ( 'Title' ), true, array ('style' => "width:300px", 'class' => 'inputText', 'maxlength' => 60 ) );
$form->add_textfield ( 'display_order', get_lang ( 'DisplayOrder' ), true, array ('style' => "width:100px", 'class' => 'inputText', 'maxlength' => 5 ) );
$form->addRule ( 'display_order', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'display_order', get_lang ( 'ThisFieldIsRequiredPositiveInteger' ), 'regex', '/^[1-9]\d*$/' );

//提交
$group = array ();
$group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit', get_lang ( 'Save' ), 'class="save"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, null, '&nbsp;', '&nbsp;&nbsp;' );

$form->setDefaults ( $values );
Display::setTemplateBorder ( $form, '98%' );

if ($form->validate ()) {
	//$data = $form->exportValues ();
	$data = $form->getSubmitValues ();
	//var_dump($data);exit;
	if (is_equal ( $data ['action'], "add_save" )) {
		$sql_data=array('name'=>$data['name'],'display_order'=>$data['display_order'],'survey_id'=>$data['survey_id']);
		$sql=Database::sql_insert($tbl_survey_question_group,$sql_data);
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	} elseif (is_equal ( $data ['action'], "edit_save" )) {
		$sql_data=array('name'=>$data['name'],'display_order'=>$data['display_order']);
		$sql=Database::sql_update($tbl_survey_question_group,$sql_data,"id=".Database::escape($data['id']));
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	}
	tb_close();
}

Display::display_header ( NULL ,FALSE);

$g_message=  getgpc('message');
if (! empty ( $g_message )) {
	Display::display_normal_message ( stripslashes ( urldecode ( $g_message ) ) );
}
$form->display ();

Display::display_footer ();