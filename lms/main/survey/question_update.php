<?php
$language_file = array ('survey', 'admin' );
$cidReset = true;

include_once ('../inc/global.inc.php');
require_once ('survey.inc.php');

api_protect_admin_script ();

$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( "action" ) : "");
$type = getgpc ( 'answerType' );
$survey_id = isset ( $_REQUEST ['survey_id'] ) ? intval(getgpc ( "survey_id" )) : "";
$qid = intval(getgpc ( 'qid' ));

//$interbreadcrumb[]=array("url" => "pool_iframe.php","name" => get_lang('Exam'));
$htmlHeadXtra [] = Display::display_kindeditor ( 'description','normal' );

if ($action && $action == 'add') {
	$objQuestion = Question::getInstance ( $type );
	$form_action = $_SERVER ['PHP_SELF'];
}
if ($action && $action == 'edit') {
	$objQuestion = Question::get_info ( $qid );
	$form_action = $_SERVER ['PHP_SELF'];
}

Display::display_header ( NULL ,FALSE);

if (is_object ( $objQuestion ) && $objQuestion) {
	$objQuestion->survey_id = $survey_id;
	
	$form = new FormValidator ( 'question_admin_form', 'post', $form_action );
	
	//题干(公共部分)
	$hide_question_name = empty ( $_REQUEST ['hideQN'] ) ? false : true;
	$form->addElement ( 'hidden', 'hideQN', $_REQUEST ['hideQN'] );
	$objQuestion->createForm ( $form );
	
	//答案、选项
	$objQuestion->createAnswersForm ( $form );
	
	$form->addElement ( 'hidden', 'action', $action );
	$form->addElement ( 'hidden', 'survey_id', $survey_id );
	if ($action && $action == 'edit') {
		$form->addElement ( 'hidden', 'qid', $qid );
	}
	
	//提交按钮		
	$group = array ();
	$group [] = $form->createElement ( 'submit', 'submitQuestion', get_lang ( 'SaveQuestion' ), 'class="inputSubmit" id="submitQuestion"' );
	$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
	
	$form->addGroup ( $group, 'submit', '&nbsp;', null, false );
	$default_template = '<tr class="containerBody"><td class="formLabel">{label}</td><td class="formTableTd" align="left">{element}</td></tr>';
	$renderer = $form->defaultRenderer ();
	$renderer->setElementTemplate ( $default_template, 'submit' );
	
	$form->addElement ( 'html', '</table>' );
	
	//Display::setTemplateBorder($form, '98%');
	$form_template = '<form {attributes}><table align="center" width="98%" cellpadding="4" cellspacing="0">{content}</table></form>';
	$renderer->setFormTemplate ( $form_template );
	
	/**********************
	 * 处理提交信息
	 **********************/
        $p_submitquestion=  getgpc('submitQuestion');
	if (isset ( $p_submitquestion ) && $form->validate ()) {
		//var_dump($form->exportValues());exit;
		

		// question : 问题题干的创建（题目及公共部分）
		$objQuestion->processCreation ( $form );
		
		// answers :　选项及答案保存
		$objQuestion->processAnswersCreation ( $form );
		
		tb_close ();
	} else {
		echo import_assets ( "commons.js" );
		$form->display ();
	}
}

Display::display_footer ();