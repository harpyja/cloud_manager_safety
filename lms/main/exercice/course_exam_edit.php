<?php
/**----------------------------------------------------------------
 新建/编辑课程测验
 liyu: 2011-10-20
 *----------------------------------------------------------------*/
include_once ('exercise.class.php');
include_once ('question.class.php');
include_once ('answer.class.php');

$language_file = 'exercice';
include_once ('../inc/global.inc.php');
include_once ('exercise.lib.php');

$is_allowed_to_edit = api_is_allowed_to_edit ();
if (! $is_allowed_to_edit) api_not_allowed ();
$tbl_quiz = Database::get_main_table ( TABLE_QUIZ_TEST );
$sql = "SELECT id FROM " . $tbl_quiz . " WHERE type=2 AND cc=" . Database::escape ( api_get_course_code () );
$exerciseId = Database::getval ( $sql, __FILE__, __LINE__ );

$objExercise = new Exercise ();
$objExercise->updateType ( 2 );
if ($exerciseId) { //编辑
	$objExercise->read ( intval ( $exerciseId ) );
	$form = new FormValidator ( 'exercise_admin', 'post', api_get_self () . '?exerciseId=' . $exerciseId );
	$form->addElement ( 'hidden', 'action', 'edit_save' );
	$form->addElement ( 'hidden', 'exerciseId', $exerciseId );
} else { //新增
	$objExercise->cc = api_get_course_code ();
	$form = new FormValidator ( 'exercise_admin' );
	$form->addElement ( 'hidden', 'action', 'add_save' );
}
$objExercise->createForm ( $form );

if ($form->validate ()) {
	$data = $form->getSubmitValues ();
	//var_dump($data);exit;
	$objExercise->processCreation ( $form );
	if ($objExercise->id) { //创建成功,跳转到设置题目界面
		$redirect_url = 'admin.php?message=ExerciseStored&exerciseId=' . $objExercise->id;
	} else { //编辑保存后
		$redirect_url = 'course_exam_edit.php?message=ExerciseStored&' . api_get_cidreq ();
	}
	api_redirect ( $redirect_url );
} else { //显示表单
	$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
	$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
	
	$htmlHeadXtra [] = Display::display_kindeditor ( 'description' );
	Display::display_header ( null, FALSE );
	
	$html = '<div id="demo" class="yui-navset">';
	$html .= '<ul class="yui-nav">';
	$html .= '<li  class="selected"><a href="course_exam_edit.php?' . api_get_cidreq () . '&exerciseId=' . $exerciseId . '"><em>1. ' . get_lang ( 'ModifyEx' ) . '</em></a></li>';
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