<?php
/************************
 * 查看考生答卷
 ************************/
include_once ('../../exercice/exercise.class.php');
include_once ('../../exercice/question.class.php');
include_once ('../../exercice/answer.class.php');
$language_file = array ('exam', 'exercice' );
include_once ('../../inc/global.inc.php');
include_once api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php';
require_once (api_get_path ( SYS_CODE_PATH ) . "exercice/exercise.lib.php");

api_block_anonymous_users ();
$id = $exerciseId = $exam_id = (isset ( $_REQUEST ['exam_id'] ) ?  intval(getgpc ( 'exam_id' )) : "");
$result_id = (isset ( $_REQUEST ['result_id'] ) ?  intval(getgpc ( 'result_id' )) : "");
$user_id = api_get_user_id ();

$objExercise = new Exercise ();
$objExercise->read ( $exerciseId );
//$is_allowed = ($objExercise->is_exam_manager ( $exam_id ) or api_is_platform_admin ());
//if (! $is_allowed) api_deny_access ( FALSE );

$paper_total_score = $objExercise->get_quiz_total_score ( $exerciseId );
  
//提交答卷信息
$sql = "SELECT *,exe_duration AS exam_duration FROM $tbl_exam_result WHERE exe_id=" . Database::escape ( $result_id );
$exam_result = Database::fetch_one_row ( $sql, FALSE, __FILE__, __LINE__ );
$exerciseResult = unserialize ( $exam_result ['data_tracking'] );
$abc=  array_keys($exerciseResult);
$keys=$abc[count($abc)-1];
  
$sql = "SELECT data_tracking AS data_tracking FROM $tbl_exam_result WHERE exe_id=" . Database::escape ( $result_id );
$examResult = Database::fetch_one_row ( $sql, FALSE, __FILE__, __LINE__ );
$examResult = unserialize ( $examResult ['data_tracking'] );
 
if (! is_array ( $exerciseResult ) || ! is_object ( $objExercise )) exit ();

$exerciseTitle = $objExercise->selectTitle (); //测验名称
$test_duration = $objExercise->selectDuration ();
if ($objExercise->feedbacktype == 0) $isAllowedToSeeAnswer = TRUE;
if ($objExercise->feedbacktype == 2) $isAllowedToSeeAnswer = FALSE;
if ($objExercise->results_disabled == 0) $isAllowedToSeePaper = TRUE;
if ($objExercise->results_disabled == 1) $isAllowedToSeePaper = FALSE;

$quiz_question_type = $objExercise->getQuizQuestionTypes ( $exerciseId );
$quiz_qt = array_keys ( $quiz_question_type );
$questionListByType = $objExercise->getQuestionList ( FREE_ANSWER );

$questionListByType2 = $objExercise->getQuestionList ( COMBAT_QUESTION );
foreach ( $quiz_question_type as $qtype => $qcount ) {
	$total_question_cnt += $qcount [0];
}
 
$exam_user = UserManager::get_user_info_by_id ( $exam_result ['exe_user_id'] );
 
$test_duration = $objExercise->timeLimit;
$examAttempts = $objExercise->attempts;
 
//处理教师批改
if (isset ( $_POST ['action'] ) && is_equal ( $_POST ['action'], 'judgeSave' )) {
	if (Exercise::save_subjective_question_judge_result ( $id, $exam_result ['exe_user_id'], $result_id, $_POST['questionGotScore'], $_POST['questionGotComment'] )) {
		tb_close ('tobe_corrected.php?exam_id='.intval(getgpc ( 'exam_id' )));
	}
}

function free_answer_display_result($upload,$questionId, $questionName, $questionPonderation, $seq, $examResult, $questionComment) {
	$choice = $examResult [$questionId];
    $html='';
	$html .= '<div><b>' . $seq . "</b>、" . $questionName . ' (' . $questionPonderation . '分)<br>';
	$html .=  get_lang ( "QuestionSubAnswer" ) . $choice . '<br/>';
	$html .= get_lang ( "QuestionStdAnswer" ) . nl2br ( FreeAnswer::get_correct_answer_str ( $questionId ) ). '<br/>' ;
    if($upload[$questionId]!=""){
        $g_username=  getgpc('username');
        $g_exam_id=   intval(getgpc('exam_id'));
        $html.='<a href='.URL_APPEDND.'/storage/exam/'.$g_username.'/'.$g_exam_id.'/'.$upload[$questionId].'>'.'答案下载'.'</a><br>';
    }
	
	if ($questionPonderation > 0) {
		$questionScoreRange = range ( 0, $questionPonderation );

		$html .= form_label ( '评分分值: ', 'questionGotScore_' . $questionId );
		$html .= form_dropdown ( "questionGotScore[" . $questionId . "]", $questionScoreRange, 0, 'id="questionGotScore_' . $questionId . '" style="border:1px solid #999;height:22px;min-width:60px"' );
		$html .= '<br/>评语:<br/>&nbsp;&nbsp;' . form_textarea ( "questionGotComment[" . $questionId . "]", '', 'style="width:300px;height:100px"' );
	}

	
	$html .= '<br/>'.get_lang ( "QuestionAnalysis" ) . '：'.$questionComment .'<br/>';
	return $html;
}






function combat_question_display_result($upload,$questionId, $questionName, $questionPonderation, $seq, $examResult, $questionComment) {
    
    $html ='';
    $choice = $examResult [$questionId];
       // print_r($choice);
	$html .= '<b>' . $seq . "</b>、<br>" . $questionName . ' (' . $questionPonderation . '分)';

	 $html .= '<br>'.get_lang ( "QuestionSubAnswer" ) . $choice ;
         if($upload[$questionId]!=""){
             $g_username=  getgpc('username');
             $g_exam_id=   intval(getgpc('exam_id'));
         $html.='<br><a href='.URL_APPEDND.'/storage/exam/'.$g_username.'/'.$g_exam_id.'/'.$upload[$questionId].'>'.'答案下载'.'</a>';
           }   echo '<br>';


    if ($questionPonderation > 0) {
		$questionScoreRange = range ( 0, $questionPonderation );
        $html.='<br>';
		$html .= form_label ( '评分分值: ', 'questionGotScore_' . $questionId );
		$html .= form_dropdown ( "questionGotScore[" . $questionId . "]", $questionScoreRange, 0, 'id="questionGotScore_' . $questionId . '" style="border:1px solid #999;height:22px;min-width:60px"' );
		$html .= '评语:<br/>' . form_textarea ( "questionGotComment[" . $questionId . "]", '', 'style="width:300px;height:100px"' ).'<br>';
	}


	
	$html .=  get_lang ( "QuestionAnalysis" ). $questionComment .'<br>';
	return $html;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$exerciseTitle?></title>
<link href="<?=api_get_path ( WEB_PATH )?>portal/sp/index.css"
	rel="stylesheet" type="text/css" />
<link href="<?=api_get_path ( WEB_PATH )?>portal/sp/ext.css"
	rel="stylesheet" type="text/css" />
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
$i = $totalScore = $totalWeighting = 0;
echo form_open ( "exam_judge.php", array ('method' => 'post', 'onsubmit' => "javascript:return confirm('你确定要提交本次批改结果吗? 这将做为该考试的最终成绩!');" ), array ('exam_id' => $id, 'result_id' => $result_id, 'action' => 'judgeSave' ) );
?>
<div class="register_title dc2" style="text-align: center;">
				<strong><?php
				echo $exerciseTitle;
				?></strong>
			</div>
			<div class="emax_title de1">
				<span style="margin-right: 30px;"></span><span
					style="margin-right: 30px;">学员：<?php
					echo $exam_user ["firstname"] . ' (' . $exam_user ['username'] . ',' . $exam_user ['dept_path'] . ')'?></span>
				<span style="margin-right: 30px;">试题总数:<?=$total_question_cnt?></span>
				<span style="margin-right: 30px;">答题时间：<?php
				echo ($test_duration > 0 ? ($test_duration) . "分钟" : "不限制");
				?></span> <span style="margin-right: 30px;">答卷时间：<?php
	echo api_time_to_hms ( $exam_result ['exam_duration'] );
                $examScore = $exam_result ['exe_result'];

                //成绩显示
                $exercise_result = get_lang ( "ExamPaperScore" ) . ": " . $paper_total_score . ", &nbsp;&nbsp;";
                $exercise_result .= '客观题得分: <span style="font-size: 15px;font-weight:bold; color: #A0001B" id="">' . round ( $examScore ) . '</span>' . ",&nbsp;&nbsp;" . get_lang ( "CorrectRate" ) . ":" . (round ( $examScore / $paper_total_score * 100, 1 )) . "% ";
                $result_msg = $exercise_result
	?></span>
			</div>
           <br> <?=$result_msg?>
<?php
if (! $isAllowedToSeeAnswer) { //不显示题目标准答案及考生考卷
//ob_start ();
}

$g_i = 1;
            $jd_count= count ( $questionListByType );//简答
            $sz_count=count($questionListByType2);//实战
?>

<div id="qt<?=$qtype ["id"]?>_problem" class="exam_block_screen" style="display: block;">
    <?php

    if($jd_count!==0){?>
				<div class="register_hint dd2" style="width: auto; margin: 10px 0 15px;">简答题.  本题型共<?php echo count ( $questionListByType );?>题</div>
<?php }
if ($questionListByType && count ( $questionListByType ) > 0) {
	$isAllowedToSeePaper = TRUE;
	
	//列表显示试题
	foreach ( $questionListByType as $questionId => $question ) {
		//$choice保存的为当前题目学生提交的答案（多选为数组）,key为question.id;value为答案值,多选,填空则为数组,其它为单个值
		$choice = $exerciseResult [$questionId];
        $upload=$exerciseResult[$keys];
		$answerType = $question ['type'];
		$questionName = $question ['question'];
		
		$questionWeighting = $question ['question_score'];
		$questionComment = $question ['comment'];
		
		$rtn = free_answer_display_result ( $upload,$questionId, $questionName, $questionWeighting, $g_i, $exerciseResult, $questionComment, $exam_info, $qtype );
		echo $rtn;
		$totalWeighting += $questionWeighting; //试卷卷面总分
		$g_i ++;
	
	} //END: foreach($questionList ...
} //END: if(有题目）
?>
    
    
    
<div id="qt<?=$qtype ["id"]?>_problem" class="exam_block_screen"
				style="display: block;">

				<div class="register_hint dd2" style="width: auto; margin: 10px 0 15px;">实战题.  本题型共<?php echo count ( $questionListByType2 ); ?>题</div>

<?php
if ($questionListByType2 && count ( $questionListByType2 ) > 0) {
	$isAllowedToSeePaper = TRUE;
	
	//列表显示试题
	foreach ( $questionListByType2 as $questionId2 => $question2 ) {
		//$choice保存的为当前题目学生提交的答案（多选为数组）,key为question.id;value为答案值,多选,填空则为数组,其它为单个值
             $arr_key=  array_keys($exerciseResult);
             
                $upload=$exerciseResult[$keys];
		$choice2 = $exerciseResult [$questionId2];
		    
		$answerType2 = $question2 ['type'];
		$questionName2 = $question2 ['question'];
		
		$questionWeighting2 = $question2 ['question_score'];
		$questionComment2 = $question2 ['comment'];
		
		$rtn = combat_question_display_result ( $upload,$questionId2, $questionName2, $questionWeighting2, $g_i, $exerciseResult, $questionComment2, $exam_info, $qtype );
		echo $rtn;
		$totalWeighting += $questionWeighting; //试卷卷面总分
		$g_i ++;
	
	} //END: foreach($questionList ...
} //END: if(有题目）
?>
    
    
    
	</div>
	<?php
	?>
 <br><br>
<input type="submit" name="judgeSub" value="<?=get_lang ( "Submit" )?>" class="simple" style="border: 1px #666 solid"/>

<?php
echo form_close ();
?>
<div class="clearall"></div>
		</div> 
		<div class="clearall"></div>
	</div>
</body>
</html>
