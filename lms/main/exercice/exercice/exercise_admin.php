<?php
/*
 新建/编辑测验
 */
include_once ('exercise.class.php');
include_once ('question.class.php');
include_once ('answer.class.php');
$language_file = 'exercice';
include_once ('../inc/global.inc.php');
include_once ('exercise.lib.php');


$type=(isset($_GET['type'])?getgpc('type','G'):1);
$exerciseId = intval(getgpc ( 'exerciseId' ));
$objExercise = new Exercise ();
$objExercise->read($exerciseId);
api_protect_quiz_script($objExercise->exam_manager) ;

if (isset ( $_GET ['exerciseId'] )) { //编辑
	$nameTools = get_lang ( 'ModifyEx' );
        $g_exerciseid=  intval(getgpc('exerciseId'));
	$form = new FormValidator ( 'exercise_admin', 'post', api_get_self () . '?exerciseId=' . $g_exerciseid );
	//$form->addElement ( 'header', 'header', $nameTools );
	$objExercise->read ( intval ( $exerciseId ) );
	$form->addElement ( 'hidden', 'action', 'edit_save' );
} else { //新增
	$nameTools = get_lang ( 'NewEx' );
	$form = new FormValidator ( 'exercise_admin' );
	//$form->addElement ( 'header', 'header', $nameTools );
	$form->addElement ( 'hidden', 'action', 'add_save' );
	$objExercise->type = $type;
	$objExercise->cc = '';
}
$objExercise->createForm ( $form );

if ($form->validate ()) {
	$data = $form->getSubmitValues ();
	//var_dump($data);exit;
	$objExercise->processCreation ( $form );
	
	/*	if (is_equal ( $data ["action"], "edit_save" )) {
		$redirect_url = 'exercice.php?message=ExerciseEdited' . api_get_cidreq ();
	} else {*/
	$redirect_url = 'admin.php?message=ExerciseStored&exerciseId=' . $objExercise->id;
	//if (empty ( $redirect_url )) $redirect_url = "exercice.php";
	api_redirect ( $redirect_url );
} else { //显示表单
	$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
	$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
	$htmlHeadXtra [] = Display::display_kindeditor ( 'description' );
	Display::display_header ( $nameTools, FALSE );
	
	$html = '<div id="demo" class="yui-navset">';
	$html .= '<ul class="yui-nav">';
	$html .= '<li  class="selected"><a href="exercise_admin.php?exerciseId=' . $exerciseId . '"><em>1. ' . $nameTools . '</em></a></li>';
	if ($exerciseId) {
		$html .= '<li><a href="admin.php?exerciseId=' . $exerciseId . '"><em>2. ' . get_lang ( 'QuestionList' ) . '</em></a></li>';
		$html .= '<li><a href="../exam/manage/have_arranged.php?exam_id=' . $exerciseId . '"><em>3. ' . get_lang ( 'ArrageExaminees' ) . '</em></a></li>';
	}
	$html .= '</ul>';
	$html .= '<div class="yui-content"><div id="tab1">';
	echo $html;
	$form->display ();
	
	echo '</div></div></div>';
}

Display::display_footer ();

