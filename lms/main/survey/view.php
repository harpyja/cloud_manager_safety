<?php
/************************
 * 查看考生答卷
 ************************/

$language_file = 'survey';
$cidReset = true;
require_once ('../inc/global.inc.php');

api_protect_admin_script ();

include_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once ('survey.inc.php');

$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( 'action' ) : "");
$survey_id = isset ( $_REQUEST ['survey_id'] ) ? intval(getgpc ( "survey_id" )) : "";
$user_id =isset ( $_REQUEST ['user_id'] ) ?intval( getgpc ( "user_id" )) : 0;
if(empty($survey_id) OR empty($user_id)){
	exit;
}

$survey_info = SurveyManager::get_info ( $survey_id );
$user_info=UserManager::get_user_info_by_id($user_id,TRUE);

$nbrQuestions=SurveyManager::get_question_count($survey_id);

//$paperTotalScore=SurveyManager::get_paper_total_score($survey_id);

$title = $survey_info ["title"];
$examType = $survey_info ['display_type']; //试卷显示类型
$option_display_type = $survey_info ['option_display_type'];
$exerciseStartTime = substr ( $survey_info ["start_date"], 0, 16 );
$exerciseEndTime = substr ( $survey_info ["end_date"], 0, 16 );

$sql = "SELECT data_tracking,last_attempt_time FROM $tbl_survey_user WHERE survey_id=" . Database::escape ( $survey_id ) . " AND user_id=" . Database::escape ( $user_id );
//echo $sql;
list($data_tracking,$last_attempt_time)=api_sql_query_one_row($sql,__FILE__,__LINE__);
$examResult = mb_unserialize ( $data_tracking );

//var_dump($examResult);


function mb_unserialize($serial_str) {
	//$serial_str = preg_replace ( '!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $serial_str );
	//$serial_str = str_replace ( "\r", "", $serial_str );
	return unserialize ( $serial_str );
}


function asc_unserialize($serial_str) {
	$serial_str = preg_replace ( '!s:(\d+):"(.*?)";!se', '"s:".strlen("$2").":\"$2\";"', $serial_str );
	$serial_str = str_replace ( "\r", "", $serial_str );
	return unserialize ( $serial_str );
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php
echo get_lang ( "SystemName" );
?></title>
<link href="../../portal/sp/index.css" rel="stylesheet"
	type="text/css" />
<link href="../../portal/sp/ext.css" rel="stylesheet"
	type="text/css" />
<?php
echo import_assets ( "commons.js" );
echo import_assets ( "jquery-latest.js" );
echo import_assets ( "jquery-plugins/jquery.wtooltip.js" );
?>
	<style>input[type=submit], input[type=button]{border:1px solid #666}</style>
</head>

<body>


<div class="register_body">
<div class="emax_content">

<?php

$i = $totalScore = $totalWeighting = 0;

echo form_open ( "exam_fb.php", array ('method' => 'get' ), array ('survey_id' => $survey_id ) );
?>
<div class="register_title dc2" style="text-align: center;"><strong><?php
echo $title;
?></strong></div>

<div style="margin-top:6px;float:right"><input type="button" id="btnSub"  value="<?php echo get_lang('Close')?>"  onclick="javascript:window.close();" /></div>
<div class="emax_title de1"><span style="margin-right: 30px;"></span><span
	style="margin-right: 30px;">学员：<?= $user_info['firstname'].' ('.$user_info['username'].',  '.$user_info['dept_path'].')'?></span> 
	<span style="margin-right: 30px;">题目总数: <?=$nbrQuestions?></span>
	<span style="margin-right: 30px;">答卷时间: <?=$last_attempt_time?></span>
</div>




<?php
$g_i = 1;
$major_question = 1;

	$questionListByType = SurveyManager::get_question_list ( $survey_id, '' );

	
	?>

<div id="qt<?=$qtype ["id"]?>_problem" class="exam_block_screen"
	<?php
	if ($major_question == 1) {
		echo ' style="display: block;"';
		$major_question = 0;
	} else
		echo ' style="display: none;"';
	?>>

<div class="register_hint dd2" style="width: auto; margin: 10px 0 15px;"><?=$qtype ["name"]	?> 
本调查问卷共<?=count ( $questionListByType )?>题</div>

<?php
	if ($questionListByType && count ( $questionListByType ) > 0) {
		
		//列表显示试题
		foreach ( $questionListByType as $question ) {
			$questionId = $question ["id"];
			
			//$choice保存的为当前题目学生提交的答案（多选为数组）,key为question.id;value为答案值,多选,填空则为数组,其它为单个值
			$choice = $examResult [$questionId];
			
			$objQuestionTmp = Question::get_info ( $questionId );
			$answerType = $objQuestionTmp->type;
			$questionName = $objQuestionTmp->question;
			unset ( $objQuestionTmp );
			
			//显示答案
			$objAnswerTmp = new Answer ( $questionId );
			$nbrAnswers = $objAnswerTmp->nbrAnswers;
			$questionScore = 0;
			
			switch ($answerType) {
				case UNIQUE_ANSWER :
					$rtn = UniqueAnswer::display_result ( $questionId, $questionName, $g_i, $examResult );
					echo $rtn ["html"];
					$questionScore = $rtn ["score"];
					$totalScore += $rtn ["score"];
					break;
				case MULTIPLE_ANSWER : //多选题
					$rtn = MultipleAnswer::display_result ( $questionId, $questionName, $g_i, $examResult );
					echo $rtn ["html"];
					$totalScore += $rtn ["score"];
					$questionScore = $rtn ["score"];
					break;
				case FREE_ANSWER :
					$rtn = FreeAnswer::display_result ( $questionId, $questionName, $g_i, $examResult );
					echo $rtn ["html"];
					break;
			}
			
			unset ( $objAnswerTmp, $questionScore );

			$g_i ++;
		
		} //END: foreach($questionList ...
	} //END: if(有题目）
	?>
	</div>
	<?php



//成绩显示
//$exercise_result =   "卷面总分值: " . $paperTotalScore . ", &nbsp;&nbsp;" 
$exercise_result =  '您的调查分值: <span style="font-size: 15px;font-weight:bold; color: #A0001B">' . round ( $totalScore ) . '</span>' ;
//. ",&nbsp;&nbsp;" . get_lang ( "CorrectRate" ) . ":" . (round (	$totalScore / $paperTotalScore * 100, 1 )) . "% ";

?>
<div class="register_hint dd2" style="width: auto; margin: 10px 0 15px;"><?= $exercise_result?></div>

<?php
echo form_close ();
?>
<div class="clearall"></div>
</div>

<div style="margin-top:6px;float:right"><input type="button" id="btnSub"  value="<?php echo get_lang('Close')?>"  onclick="javascript:window.close();" /></div>
<div class="clearall"></div>
</div>


<script type="text/javascript">
$(".exam_block_screen").css('display','block');
</script>

<?php
include_once 'inc/page_footer.php';
?>