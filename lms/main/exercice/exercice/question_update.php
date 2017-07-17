<?php
include_once ('exercise.class.php');
include_once ('question.class.php');
include_once ('answer.class.php');

$language_file = 'exercice';
include_once ("../inc/global.inc.php");

include_once ('exercise.lib.php');
//api_protect_quiz_script ();

require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileDisplay.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . "attachment.lib.php");
include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');

$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( "action" ) : "");
$type = intval(getgpc ( 'answerType' ));
$qid = intval(getgpc ( 'qid' ));
$combo_questionId = intval(getgpc ( "pid") );

if ($action && $action == 'add') {
	$objQuestion = Question::getInstance ( $type );
	$form_action = $_SERVER ['PHP_SELF'];
  
}
if ($action && $action == 'edit') {
	$objQuestion = Question::read ( $qid );
	$form_action = $_SERVER ['PHP_SELF'];




}

$htmlHeadXtra [] = Display::display_kindeditor ( 'description', 'normal' );
Display::display_header ( null, FALSE );

if (is_object ( $objQuestion ) && $objQuestion) {
	$form = new FormValidator ( 'question_admin_form', 'post', $form_action );
	
	//题干(公共部分)
	$hide_question_name = empty ( $_REQUEST ['hideQN'] ) ? false : true;
	$form->addElement ( 'hidden', 'hideQN', $_REQUEST ['hideQN'] );
	$objQuestion->createForm ( $form );

	//答案、选项
	$objQuestion->createAnswersForm ( $form );

	$form->addElement ( 'hidden', 'pid', empty ( $combo_questionId ) ? '0' : $combo_questionId );
	$form->addElement ( 'hidden', 'action', $action );
	if ($action && $action == 'edit') $form->addElement ( 'hidden', 'qid', $qid );
	
	//提交按钮		
	$group = array ();
	$group [] = $form->createElement ( 'submit', 'submitQuestion', get_lang ( 'Ok' ), 'class="inputSubmit" id="submitQuestion"' );
	$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
	//$group[] =$form->createElement('style_button', 'submitQuestion',null,array('type'=>'button','class'=>"inputSubmit",	'value'=>get_lang('Ok'),'id'=>'submitQuestion'));
	//$goback_url=(empty($exerciseId)?"question_base.php":'admin.php?exerciseId='.$exerciseId);
	//$group[] =$form->createElement('style_button', 'back',null,array('type'=>'button','class'=>"back",'value'=>get_lang('Back'),'onclick'=>'javascript:location.href=\''.$goback_url.'\';'));
	$form->addGroup ( $group, 'submit', '&nbsp;', null, false );
	$default_template = '<tr class="containerBody"><td class="formLabel">{label}</td><td class="formTableTd" align="left">{element}</td></tr>';
	$renderer = $form->defaultRenderer ();
	$renderer->setElementTemplate ( $default_template, 'submit' );
	
	$form->addElement ( 'html', '</table>' );
	
	//Display::setTemplateBorder($form, '98%');
	$form_template = '<form {attributes}><table align="center" width="98%" cellpadding="4" cellspacing="0">{content}</table></form>';
	$renderer->setFormTemplate ( $form_template );
	
	if (isset ( $_POST ['submitQuestion'] ) && $form->validate ()) {
		//var_dump($form->exportValues());exit;
		// 问题题干的创建（题目及公共部分）
		$objQuestion->processCreation ( $form, $objExercise );
		
		// 选项及答案保存
		$objQuestion->processAnswersCreation ( $form );
		tb_close ();
	} else {
		echo '<style>div.row div.label{width: 10%;float:right;}div.row div.formw{width: 90%;float:right;}</style>	';
		$form->display ();
	}
}

