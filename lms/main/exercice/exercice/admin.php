<?php
/**
 * 试卷题目管理: 试题列表及新增(编辑)
 */
include_once ('exercise.class.php');
include_once ('question.class.php');
include_once ('answer.class.php');
$language_file = 'exercice';
include_once ("../inc/global.inc.php");
require_once ('exercise.lib.php');
api_protect_quiz_script ();
include_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');

define ( ALLOWED_TO_INCLUDE, 1 );
$exerciseId = intval(escape ( $_GET ['exerciseId'] ));
$newQuestion = getgpc ( 'newQuestion', 'G' );
$editQuestion = getgpc ( 'editQuestion', 'G' );
$modifyAnswers = getgpc ( 'modifyAnswers', 'G' );
$modifyQuestion = getgpc ( 'modifyQuestion', 'G' );
$modifyExercise = getgpc ( 'modifyExercise', 'G' );
$deleteQuestion = getgpc ( 'deleteQuestion' );
$combo_questionId = intval(getgpc ( 'pid', 'G' ));

// 从 session 中得到当前的一些相关值
$questionId = $_SESSION ['questionId'];
$objExercise = $_SESSION ['objExercise'];
$objQuestion = $_SESSION ['objQuestion'];

//初始化测验Exercise
if (is_null ( $objExercise ) or is_object ( $objExercise ) == false) {
	$objExercise = new Exercise ();
	if ($exerciseId) $objExercise->read ( $exerciseId );
	api_session_register ( 'objExercise' );
}

if (is_null ( $objExercise ) or is_object ( $objExercise ) == false) api_redirect ( 'exercice.php' );

//总题目数
$nbrQuestions = $objExercise->selectNbrQuestions ();

// 初始化Question
if ($editQuestion || $newQuestion || $modifyQuestion || $modifyAnswers) { //有效的操作
	if ($editQuestion || $newQuestion) { //新增或编辑题目
		if ($editQuestion) { //编辑
			$objQuestion = Question::read ( $editQuestion ) or die ( get_lang ( 'QuestionNotFound' ) );// 没有找到
			api_session_register ( 'objQuestion' );
		}
	}
	if (is_object ( $objQuestion )) $questionId = $objQuestion->selectId ();
}

//---------------------------------------------------------------
Display::display_header ( null, false );
//echo dispaly_intro_title ( $objExercise->exercise . '<span style="padding-left:100px"><a href="exercice.php">' . get_lang ( 'Back' ) . '</a></span>' );

if (isset ( $_GET ['message'] )) {
	if (in_array ( $_GET ['message'], array ('ExerciseStored' ) )) {
		Display::display_confirmation_message ( get_lang ( $_GET ['message'] ) );
	}
}

//描述
/*$description = $objExercise->selectDescription ();
if (! empty ( $description )) {
	echo '<div id="description_box" style="margin-left:5px;float:left;width:80%">' . stripslashes ( $description ) . '</div>';
}
*/

//新增或编辑题目
if ($newQuestion or $editQuestion) {
	$type = getgpc ( 'answerType' );
	echo form_hidden ( "Type", $type );
	require_once ('question_update_admin.inc.php');
}

// 本测验的题目列表
if (! $newQuestion && ! $modifyQuestion && ! $editQuestion) {
	require_once ('question_list_admin.inc.php');
}

api_session_register ( 'objExercise' );
api_session_register ( 'objQuestion' );

echo '<div style="padding-top:15px;">';
echo '<div style="float:right;"><button name="cancle" class="inputSubmit" onclick="javascript:location.href=\'../exam/manage/have_arranged.php?exam_id=' . $exerciseId . '\';" type="button">' . get_lang ( 'Next' ) . '</button></div>';
echo '<div style="float:right;"><button name="cancle" class="cancel" onclick="javascript:location.href=\'exercise_admin.php?modifyExercise=yes&exerciseId=' . $exerciseId . '\';" type="button">' . get_lang ( 'Previous' ) . '</button></div>';
echo '</div>';

Display::display_footer ();
