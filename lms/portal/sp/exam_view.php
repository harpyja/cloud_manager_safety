<?php
/************************
 * 查看考生答卷
 ************************/
include_once ('../../main/exercice/exercise.class.php');
include_once ('../../main/exercice/question.class.php');
include_once ('../../main/exercice/answer.class.php');
$language_file = array ('exam', 'exercice', 'admin' );
include_once ('../../main/inc/global.inc.php');
api_block_anonymous_users ();
include_once api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php';
require_once (api_get_path ( SYS_CODE_PATH ) . "exercice/exercise.lib.php");

$exerciseId = (isset ( $_REQUEST ['exam_id'] ) ? getgpc ( 'exam_id' ) : "");
$result_id = (isset ( $_REQUEST ['result_id'] ) ? getgpc ( 'result_id' ) : "");
$user_id = api_get_user_id ();

$objExercise = new Exercise ();
$objExercise->read ( $exerciseId );

$sql = "SELECT *  FROM $tbl_exam_result WHERE exe_id=" . Database::escape ( $result_id );
$exam_result = Database::fetch_one_row ( $sql, FALSE, __FILE__, __LINE__ );

$is_allowed = FALSE;
if ($objExercise->feedbacktype == 0 and $exam_result ['exe_user_id'] == $user_id && $exam_result ['exe_exo_id'] == $exerciseId) $is_allowed = TRUE;
if ($user_id == $objExercise->exam_manager) $is_allowed = TRUE;
if (api_is_platform_admin ()) $is_allowed = TRUE;
if (! $is_allowed) api_not_allowed ();

$exerciseResult = unserialize ( $exam_result ['data_tracking'] );

//var_dump($exerciseResult);
if (! is_array ( $exerciseResult ) || ! is_object ( $objExercise )) {
	Display::display_msgbox("没有答案提交数据或是非法访问！",api_get_path(WEB_PATH),3,'warning');
}

$exerciseTitle = $objExercise->selectTitle (); //测验名称
$test_duration = $objExercise->selectDuration ();
if ($objExercise->feedbacktype == 0) $isAllowedToSeeAnswer = TRUE;
if ($objExercise->feedbacktype == 2) $isAllowedToSeeAnswer = FALSE;
if (api_is_platform_admin () or $user_id == $objExercise->exam_manager) $isAllowedToSeeAnswer = TRUE;
if ($objExercise->results_disabled == 0) $isAllowedToSeePaper = TRUE;
if ($objExercise->results_disabled == 1) $isAllowedToSeePaper = FALSE;
if (api_is_platform_admin () or $user_id == $objExercise->exam_manager) $isAllowedToSeePaper = TRUE;

$exam_user = UserManager::get_user_info_by_id ( $exam_result ['exe_user_id'], TRUE );

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
echo import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
echo import_assets ( "jquery-plugins/jquery.wtooltip.js" );
?>
<script type="text/javascript">$(document).ready( function() {
		$("body").addClass("yui-skin-sam");
		$(".yui-content > div").fadeOut("fast").eq(0).fadeIn("normal");
		$("#tab li").eq(0).addClass("selected");
		
		$("#tab li").click(function(){ 
			$(this).addClass("selected").siblings().removeClass("selected");
			var cur_idx=$("#tab li").index(this);
			$(".yui-content > div").eq(cur_idx).addClass("selected").siblings().removeClass("selected");
			$(".yui-content > div").fadeOut("normal").eq(cur_idx).fadeIn("normal");
		});
});</script>
</head>

<body>

<div class="register_body">
<div class="emax_content">

<?php
//大题题型
echo form_open ( "exam_fb.php", array ('method' => 'get' ), array ('id' => $exerciseId ) );

//获取用户成绩排名$rank-----zd
    $sql="select score from exam_rel_user where exam_id=".getgpc('exam_id')." order by score desc";
    $rs=api_sql_query($sql,__FILE__,__LINE__);
    $tab = array ();
    while ( $row = Database::fetch_array ( $rs ) ) {
        $tab [] = $row;
    }
//var_dump($tab);  // [0] => array(1) { 'score' => string(2) "99" } [1] => array(1) { 'score' => string(1) "0" } [2] => array(1) { 'score' => string(1) "0" }
//$tab需要处理成一维数组$da
for($i=0;$i<count($tab);$i++){
    $da[]=$tab[$i]['score'];
}
//var_dump($da);
$uid=$exam_result ['exe_user_id'];
$sql1="select score from exam_rel_user where user_id=".$uid." and exam_id=".getgpc('exam_id');
$vl=DATABASE::getval($sql1);
$rank=array_keys($da,$vl);
?>

<div class="register_title dc2" style="text-align: center;"><strong><?=$exerciseTitle?></strong></div>
<div class="emax_title de1"><span style="margin-right: 30px;">考生：<?=$exam_user ["firstname"]?>(<?=$exam_user ["username"] . ', ' . $exam_user ['dept_path']?>)</span><span
	style="margin-right: 30px;">试题总数:<?=$total_question_cnt?></span><span
	style="margin-right: 30px;">答题时间：<?=($test_duration > 0 ? ($test_duration / 60) . "分钟" : "不限制");?></span>
    <span>考生名次：<?=$rank[0]+1?></span>
</div>

<?php
//不显示答案
if (! $isAllowedToSeeAnswer) ob_start ();
?>

<div id="tab" class="yui-navset" style="margin-top: 1px"><!-- Tab显示大题题型 -->
<ul class="yui-nav">
	<?php
	if ($quiz_qt && is_array ( $quiz_qt ) && count ( $quiz_qt ) > 1) {
		$idx = 0;
		foreach ( $quiz_question_type as $qtype => $qcount ) {
			echo '<li id="qt_' . ($idx ++) . '"><a href="#"><em>' . $_question_types [$qtype] . "(" . $qcount [0] . ')</em></a></li>';
		}
	}
	
	/*for($i=1;$i<=$total_question_cnt;$i++){
		echo  '<li ><a href="#"><em>' . $i . '</em></a></li>';
	}*/
	?>
</ul>
<!-- Tab显示大题题型 -->

<div class="yui-content" id="slider">
<?php
$totalScoreKgt = $totalScoreZgt = 0;
foreach ( $quiz_question_type as $qtype => $qcount ) {
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
			$choice = $exerciseResult [$questionId]; //$choice保存的为当前题目学生提交的答案（多选为数组）,key为question.id;value为答案值,多选,填空则为数组,其它为单个值
			$questionName = $questionItem ['question'];
			$questionComment = $questionItem ['comment'];
			$answerType = $questionItem ['type'];
			$questionWeighting = $questionItem ['question_score'];
			$isQuestionCorrect = FALSE;
			
			//显示答案
			$objAnswerTmp = new Answer ( $questionId );
			$nbrAnswers = $objAnswerTmp->selectNbrAnswers ();
			$questionScore = 0;
			?>
			<div class="exam_problem dd7"
	style="border-bottom: #c3c3c3 0px dashed">
<div
	style="height: auto; border-right: 0px dashed #c3c3c3; float: left; width: 750px; padding: 10px 0;">
<div><?=$counter . "、" . $questionName;?> (<?=$questionWeighting?> 分)</div>

<?php
			if (in_array ( $answerType, array (UNIQUE_ANSWER, MULTIPLE_ANSWER, TRUE_FALSE_ANSWER ) )) {
				echo '<table style="text-align: center; margin-top: 10px;" cellspacing="0"><tr>
		<td width="70">考生答案</td>
		<td width="70" style="color: #A0001B">正确答案</td>
		<td width="500">选项</td></tr>';
				switch ($answerType) {
					case TRUE_FALSE_ANSWER :
						$isQuestionCorrect = TrueFalseAnswer::is_correct ( $questionId, $choice );
					case UNIQUE_ANSWER :
						$isQuestionCorrect = UniqueAnswer::is_correct ( $questionId, $choice );
						break;
					case MULTIPLE_ANSWER :
						$isQuestionCorrect = MultipleAnswer::is_correct ( $questionId, $choice );
						break;
				}
			}
			
			if (in_array ( $answerType, array (UNIQUE_ANSWER, MULTIPLE_ANSWER, TRUE_FALSE_ANSWER ) )) {
				if ($isQuestionCorrect) {
					$questionScore = $questionWeighting;
					$totalScore += $questionScore;
					$totalScoreKgt += $questionScore;
				} else {
					$questionScore = 0;
				}
				
				for($answerId = 1; $answerId <= $nbrAnswers; $answerId ++) {
					$answer = $objAnswerTmp->selectAnswer ( $answerId );
					$answerCorrect = $objAnswerTmp->isCorrect ( $answerId );
					$answerWeighting = $objAnswerTmp->selectWeighting ( $answerId );
					switch ($answerType) {
						case TRUE_FALSE_ANSWER :
						case UNIQUE_ANSWER :
							$studentChoice = ($choice == $answerId) ? 1 : 0;
							break;
						case MULTIPLE_ANSWER :
							$studentChoice = $choice [$answerId];
							break;
					}
					display_unique_or_multiple_answer ( $answerType, $studentChoice, $answer, $answerCorrect );
				}
				echo '</table>';
			} elseif ($answerType == FILL_IN_BLANKS) {
				$answer = $objAnswerTmp->selectAnswer ( 1 );
				$rtn_data = display_fill_blank_answer ( $choice, $answer, $questionWeighting, $isAllowedToSeeAnswer, $isAllowedToSeePaper );
				$questionScore = $rtn_data ['score'];
				$totalScore += $questionScore;
				echo $rtn_data ['html'];
			} elseif ($answerType == FREE_ANSWER) {
				$html = '<div style="height: 9px; overflow: hidden;"></div>';
				$html .= '<div>' . ($isAllowedToSeePaper ? $choice : "");
				$html .= '<div class="clearall"></div><div style="float:left"><span style="font-weight:bold;color:red;font-size:14px">' . get_lang ( "QuestionStdAnswer" ) . '</span>:&nbsp;<br/>' . nl2br ( FreeAnswer::get_correct_answer_str ( $questionId ) ) . '</div>';
				$html .= '<div class="clearall"></div></div>';
				$questionScore = FreeAnswer::get_score ( $result_id, $questionId, $user_id );
				$teacher_comment = FreeAnswer::get_teacher_comment ( $result_id, $questionId, $user_id );
				$totalScore += $questionScore;
				$totalScoreZgt += $questionScore;
				echo $html;
			}
			?>
</div>

<div
	style="float: left; width: 160px; text-align: center; margin-top: 10px; margin-left: 10px">
<?php
			if ($isAllowedToSeePaper) {
				if ($answerType == FREE_ANSWER) {
					?><div style="float: right"><br />
<strong>
	此题<?=($questionWeighting)?>分,得<?=$questionScore?>分</strong></div>
<?php
					if ($teacher_comment) {
						?><br />
阅卷评语:<br />
<div style="border: 1px solid #666; padding: 4px"><?=$teacher_comment?></div>
			<?php
					}
				} else {
					?>
				<div style="float: right"><img
	src="../../portal/sp/images/<?=($questionScore == $questionWeighting ? "correct.gif" : "cross.gif")?>" /><br />
<strong>
	此题<?=($questionWeighting)?>分,得<?=$questionScore?>分</strong></div>
<?php
				}
			}
			?></div>
</div>
<div class="clearall"></div>
<div class="analyze"><span style="font-size: 14px; color: #A0001B"><?=get_lang ( "QuestionAnalysis" )?>：</span><br />
<span class="dd2"><?=$questionComment?></span>
<div class="clearall"></div>
</div>
<?php
			unset ( $objAnswerTmp );
			$totalWeighting += $questionWeighting;
			?>
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
$score = $totalWeighting > 0 ? (round ( round ( $totalScore ) / $totalWeighting * 100 )) : 0; //百分比成绩
$exercise_result = round ( $totalScore ) . '(其中客观题部分:' . $totalScoreKgt . '分, 主观题部分:' . $totalScoreZgt . '分), 试卷卷面总分:' . $totalWeighting . " - 百分制成绩为: <span style='font-weight:bold;color:red;font-size:18px'>" . $score . '</span>';

if (! $isAllowedToSeeAnswer) { //不显示答案
	ob_end_clean ();
}

//$result_msg = '<span style="font-size: 14px; color: #A0001B">' . get_lang ( 'ExerciseFinished' ) . "</span>";
$result_msg = ($isAllowedToSeePaper ? get_lang ( 'YourTotalScore' ) . $exercise_result : "");
//if($objExercise->results_disabled) {
//	ob_end_clean();
?>
<div class="register_hint dd2" style="width: auto; margin: 10px 0 15px;"><?=$result_msg?></div>
<?=form_close ()?>
<div class="clearall"></div>
</div>


<div class="clearall"></div>
</div>


</body>
</html>
