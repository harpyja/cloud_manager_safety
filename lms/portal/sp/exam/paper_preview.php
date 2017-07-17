<?php
/************************
 * 查看考生答卷
 ************************/
include_once ('../exercice/exercise.class.php');
include_once ('../exercice/question.class.php');
include_once ('../exercice/answer.class.php');
$language_file = array ('exam', 'exercice', 'admin' );
include_once ('../inc/global.inc.php');
api_block_anonymous_users ();
include_once api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php';
require_once (api_get_path ( SYS_CODE_PATH ) . "exercice/exercise.lib.php");
$user_id = api_get_user_id ();

$exerciseId = (isset ( $_REQUEST ['exam_id'] ) ? getgpc ( 'exam_id' ) : "");
$objExercise = new Exercise ();
$objExercise->read ( $exerciseId );
api_protect_quiz_script ( $objExercise->exam_manager );

if (! is_object ( $objExercise )) exit ( " 非法数据！" );

$exerciseTitle = $objExercise->selectTitle (); //测验名称
$test_duration = $objExercise->selectDuration ();
$isAllowedToSeeAnswer = TRUE;
$isAllowedToSeePaper = TRUE;

$quiz_question_type = $objExercise->getQuizQuestionTypes ( $exerciseId );
$quiz_qt = array_keys ( $quiz_question_type );
$questionListArr = $objExercise->getAllQuestionsByType ();

foreach ( $quiz_question_type as $qtype => $qcount ) {
	$total_question_cnt += $qcount [0];
}

$test_duration = $objExercise->timeLimit;
$examAttempts = $objExercise->attempts;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$exerciseTitle?></title>
<link href="../../portal/sp/index.css" rel="stylesheet" type="text/css" />
<link href="../../portal/sp/ext.css" rel="stylesheet" type="text/css" />
<?php
echo import_assets ( "commons.js" );
echo import_assets ( "jquery-latest.js" );

echo import_assets ( "jquery-plugins/jquery.wtooltip.js" );
?>

</head>

<body>

<div class="register_body">
<div class="emax_content">

<?php
//大题题型
?>

<div class="register_title dc2" style="text-align: center;"><strong><?=$exerciseTitle?></strong></div>
<div class="emax_title de1"><span style="margin-right: 30px;">试题总数:<?=$total_question_cnt?></span><span
	style="margin-right: 30px;">答题时间：<?=($objExercise->duration > 0 ? ($objExercise->duration / 60) . "分钟" : "不限制");?></span></div>

<?php
//不显示答案
if (! $isAllowedToSeeAnswer) ob_start ();
?>

<div id="tab" class="yui-navset" style="margin-top: 1px">

<div class="yui-content" id="slider">
<?php
$totalScoreKgt = $totalScoreZgt = 0;
$questionIdx = 0;
foreach ( $quiz_question_type as $qtype => $qcount ) {
	$questionIdx ++;
	$questionListByType = $questionListArr [$qtype];
	?>
<div id="tab_<?=$qtype?>">
<div class="exam_block_screen">
<div class="register_hint dd2" style="width: auto; margin: 10px 0 15px;"><?=$_question_types [$qtype]?>
(本题型共<?=count ( $questionListByType )?>题，共<?=$quiz_question_type [$qtype] [1]?>分)</div>

<?php
	if ($questionListByType && count ( $questionListByType ) > 0) {
		//$questionList = array_keys ( $questionListByType );
		$counter = 0;
		foreach ( $questionListByType as $questionId => $questionItem ) {
			$counter ++;
			$questionName = $questionItem ['question'];
			$questionComment = $questionItem ['comment'];
			$answerType = $questionItem ['type'];
			$questionWeighting = $questionItem ['question_score'];
			$isQuestionCorrect = FALSE;
			
			//显示答案
			$objAnswerTmp = new Answer ( $questionId );
			$nbrAnswers = $objAnswerTmp->selectNbrAnswers ();
			?>
			<div class="exam_problem dd7" style="border-bottom:#c3c3c3 0px dashed">
<div
	style="height: auto; border-right: 0px dashed #c3c3c3; float: left; width: 100%; padding: 10px 0;">
<div><?=$counter . "、" . $questionName;?> (<?=$questionWeighting?> 分)</div>

<?php
			if (in_array ( $answerType, array (UNIQUE_ANSWER, MULTIPLE_ANSWER, TRUE_FALSE_ANSWER ) )) {
				echo '<ul style="margin-top: 10px;width: 100%;float:left" cellspacing="0">';
			}
			
			if (in_array ( $answerType, array (UNIQUE_ANSWER, MULTIPLE_ANSWER, TRUE_FALSE_ANSWER ) )) {
				for($answerId = 1; $answerId <= $nbrAnswers; $answerId ++) {
					$answer = $objAnswerTmp->selectAnswer ( $answerId );
					$answerCorrect = $objAnswerTmp->isCorrect ( $answerId );
					display_question_kgt ( $answerType, $answer, $answerCorrect, $questionIdx, $answerId );
				}
				echo '</ul>';
			} elseif ($answerType == FILL_IN_BLANKS) {
				$answer = $objAnswerTmp->selectAnswer ( 1 );
				$rtn_data = display_fill_blank_answer ( null, $answer, $questionWeighting, $isAllowedToSeeAnswer, $isAllowedToSeePaper );
				echo $rtn_data ['html'];
			} elseif ($answerType == FREE_ANSWER) {
				$html = '<div style="height: 9px; overflow: hidden;"></div>';
				$html .= '<div class="clearall"></div><div style="float:left"><span style="font-weight:bold;color:red;font-size:14px">' . get_lang ( "QuestionStdAnswer" ) . '</span>:&nbsp;<br/>' . nl2br ( FreeAnswer::get_correct_answer_str ( $questionId ) ) . '</div>';
				$html .= '<div class="clearall"></div></div>';
				echo $html;
			}
			unset ( $objAnswerTmp );
			?>
</div>


</div>
<div class="clearall"></div>
<!-- <div class="analyze"><span style="font-size: 14px; color: #A0001B"><?=get_lang ( "QuestionAnalysis" )?>：</span><br />
<span class="dd2"><?=$questionComment?></span>
<div class="clearall"></div>
</div> -->
	<?php
		} //END: foreach($questionList ...
	} //END: if(有题目）
	?>
	</div>
</div>
	<?php
} //END foreach($quiz_question_type ...
?>
</div>
</div>
<?php
//成绩显示


if (! $isAllowedToSeeAnswer) { //不显示答案
	ob_end_clean ();
}

?>
<div class="clearall"></div>
</div>

<div class="clearall"></div>
</div>


</body>
</html>
