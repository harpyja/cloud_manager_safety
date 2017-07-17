<?php
/**
 * 试题及答案/选项的新增及编辑
 */

if (! defined ( 'ALLOWED_TO_INCLUDE' )) {
	exit ();
}

if (isset ( $_GET ['editQuestion'] )) //编辑
{
	$objQuestion = Question::read ( $_GET ['editQuestion'] );
	$action = $_SERVER ['PHP_SELF'] . "?modifyQuestion=" . $modifyQuestion . "&editQuestion=" . $objQuestion->id;
} else //新增
{
	$objQuestion = Question::getInstance ( getgpc ( 'answerType' ) );
	$action = $_SERVER ['PHP_SELF'] . "?modifyQuestion=" . $modifyQuestion . "&newQuestion=" . $newQuestion;
}

if (is_object ( $objQuestion ) && $objQuestion) {
	$objExercise = $_SESSION ['objExercise'];
	$exerciseId = is_object ( $objExercise ) ? $objExercise->selectId () : "";
	
	echo '<style>	div.row div.label{width: 10%;	float:right;} div.row div.formw{		width: 90%;	float:right;}	</style>	';
	$form = new FormValidator ( 'question_admin_form', 'post', $action );
	
	//题干(公共部分)
	$objQuestion->createForm ( $form );
	
	//答案、选项
	$objQuestion->createAnswersForm ( $form );
	
	$combo_questionId = intval(getgpc ( "pid") );
	$form->addElement ( 'hidden', 'pid', empty ( $combo_questionId ) ? '0' : intval(getgpc ( "pid" )) );
	
	//提交按钮		
	$group = array ();
	$group [] = $form->createElement ( 'submit', 'submitQuestion', get_lang ( 'Ok' ), 'class="inputSubmit" id="submitQuestion"' );
	//$group[] =$form->createElement('style_button', 'submitQuestion',null,array('type'=>'button','class'=>"inputSubmit",	'value'=>get_lang('Ok'),'id'=>'submitQuestion'));
	$goback_url = (empty ( $exerciseId ) ? "question_base.php" : 'admin.php?exerciseId=' . $exerciseId);
	$group [] = $form->createElement ( 'style_button', 'back', null, array ('type' => 'button', 'class' => "back", 'value' => get_lang ( 'Back' ), 'onclick' => 'javascript:location.href=\'' . $goback_url . '\';' ) );
	$form->addGroup ( $group, 'submit', '&nbsp;', null, false );
	$default_template = '<tr class="containerBody"><td class="formLabel">{label}</td><td class="formTableTd" align="left">{element}</td></tr>';
	$renderer = $form->defaultRenderer ();
	$renderer->setElementTemplate ( $default_template, 'submit' );
	
	$form->addElement ( 'html', '</table>' );
	
	/**********************
	 * 处理提交信息
	 **********************/
	if (isset ( $_POST ['submitQuestion'] ) && $form->validate ()) {
		//var_dump($form->exportValues());exit;
		

		// question : 问题题干的创建（题目及公共部分）
		$objQuestion->processCreation ( $form, $objExercise );
		
		// answers :　先项及答案
		$objQuestion->processAnswersCreation ( $form, $nb_answers );
		
		api_item_property_update ( $_course, TOOL_QUIZ, $objExercise->id, "QuizUpdated", api_get_user_id () );
		
		// redirect
		if ($combo_questionId) {
			$url = 'combo_question_list_admin.php?id=' . $combo_questionId . '&exerciseId=' . $objExercise->id;
			echo '<script type="text/javascript">window.location.href="' . $url . '";</script>';
		} else {
			echo '<script type="text/javascript">window.location.href="admin.php"</script>';
		}
	} else {//显示
		echo '<h3>' . $questionName . '</h3>';
		
		$form->display ();
	}
}
