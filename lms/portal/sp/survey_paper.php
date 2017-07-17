<?php
$cidReset = true;
$language_file [] = 'survey';
include_once ("inc/app.inc.php");

if (!(api_get_setting ( 'enable_modules', 'survey_center' ) == 'true' AND $_configuration ['enable_module_survey'])) api_redirect ( 'index.php' );

include_once (api_get_path ( SYS_CODE_PATH ) . "survey/survey.inc.php");
$user_id = api_get_user_id ();

$surveyID = $id = $survey_id = (isset ( $_REQUEST ['id'] ) ? getgpc ( 'id' ) : "");
$result_access_check = SurveyManager::is_survey_available ( $id, $user_id );
//echo $result_access_check;
if ($result_access_check != 1) {
	Display::display_reduced_header ( null );
	switch ($result_access_check) {
		case 101 : //调查问卷不存在
			Display::display_warning_message ( get_lang ( 'ExamNotFound' ), false );
			break;
		case 102 : //调查问卷不可用
			Display::display_warning_message ( get_lang ( 'ErrorSurveyNotAvailable' ), false );
			break;
		case 103 : //不是调查问卷考生
			Display::display_warning_message ( get_lang ( 'ErrorSurveyUserNotExists' ), false );
			break;
		case 104 : //调查问卷时间不允许,可参加考试时间段限制
			Display::display_warning_message ( get_lang ( 'ErrorSurveyTimeNotAllowed' ), false );
			break;
		case 105 : //超过最大允许调查次数,调查次数限制: 0:不限制
			Display::display_warning_message ( get_lang ( 'ErrorReachedMaxAttempts' ), false );
			break;
		default :
			Display::display_error_message ( get_lang ( 'NotAllowedHere' ), false );
	}
	
	Display::display_footer ();
	exit ();
}

$survey_info = SurveyManager::get_info ( $survey_id );

$nbrQuestions = SurveyManager::get_question_count ( $survey_id );

$title = $survey_info ["title"];
$option_display_type = $survey_info ['option_display_type'];
$exerciseStartTime = substr ( $survey_info ["start_date"], 0, 16 );
$exerciseEndTime = substr ( $survey_info ["end_date"], 0, 16 );

$htmlHeadXtra [] = '<script type="text/javascript">
	var langConfirmSubmitQuiz="' . get_lang ( "ConfirmSubmitSurvey" ) . '";
	var langSubmittingPlsDoNothing="' . get_lang ( 'SubmittingPlsDoNothing' ) . '";
	var exam_id="' . $id . '";
	</script>';

//得到试题列表
$questionList = array ();

//$htmlHeadXtra [] = import_assets ( "exam/disable.js" );
$htmlHeadXtra [] = import_assets ( "jquery.anythingslider.js", "js/" );
$htmlHeadXtra [] = import_assets ( "jquery.easing.1.2.js", "js/" );
$htmlHeadXtra [] = import_assets ( "jquery-plugins/scrolltopcontrol.js" );

$interbreadcrumb [] = array ("url" => 'index.php', "name" => get_lang ( 'HomePage' ) );
$interbreadcrumb [] = array ("url" => 'survey.php', "name" => get_lang ( 'Survey' ) );
$nameTools = $title;

//调查项
//$mqstnList = SurveyManager::get_survey_group_list ( $survey_id );


$data_list = array ();
$major_question = 1;
$g_i = 1;

$questionListByType = SurveyManager::get_question_list ( $surveyID, '' );
//	var_dump($questionListByType);
if ($questionListByType && is_array ( $questionListByType )) {
	$question_display_html = "";
	$i = 1;
	//列表显示试题
	foreach ( $questionListByType as $question ) {
		$questionId = $question ["id"];
		$objQuestionTmp = Question::get_info ( $questionId );
		$answerType = $objQuestionTmp->type;
		$questionName = $objQuestionTmp->question;
		
		switch ($answerType) {
			case UNIQUE_ANSWER :
				$question_display_html .= UniqueAnswer::display_question ( $questionId, $questionName, $g_i, $option_display_type );
				break;
			case MULTIPLE_ANSWER :
				$question_display_html .= MultipleAnswer::display_question ( $questionId, $questionName, $g_i, $option_display_type );
				break;
			case FREE_ANSWER :
				$question_display_html .= FreeAnswer::display_question ( $questionId, $questionName, $g_i, $option_display_type );
				break;
		}
		$mqstn ['question_display_html'] = $question_display_html;
		$i ++;
		$g_i ++;
		$data_list [] = $mqstn;
		unset ( $question_display_html );
	}
}

//var_dump($data_list);exit;


include_once ("inc/page_header.php");
//display_tab ( TAB_SURVEY_CENTER );
//echo "<pre>";var_dump($mqstn ['question_display_html']); echo "<pre>";
?>

<script type="text/javascript">
var total=<?=$nbrQuestions?>;
$(document).ready( function() {
	$("#sub").click( function() {
		submitPaper();
	});
	$("#check").click( function() {
		is_exam_finish(total);
	});
});
</script>
<script type="text/javascript" src="js/exam.js"></script>
<aside id="sidebar" class="column open cloudindex" style="height: 2232px;">
    <div id="flexButton" class="closeButton close"></div>
	<div class="navs"></div>
</aside>
<section id="main" class="column">
<h4 class="page-mark"><?=display_interbreadcrumb ( $interbreadcrumb, $nameTools, false )?></h4>
<article class="publicModule width_full">
<form method="post" id="frm_exam" name="frm_exam" action="survey_do.php">
	<input type="hidden" name="formSent" value="1" /> 
	<input type="hidden" name="formSub" value="1" /> 
    <input type="hidden" name="examType" value="<?=$survey_info ['display_type']?>" /> 
    <input type="hidden" name="id" value="<?=$survey_info ['id']?>" />
    <div class="survey_ctitle">
        <h1><?=$title?><p>
         学员：<?=$_SESSION ['_user'] ['firstName']?> &nbsp;&nbsp;题目总数:<?=$nbrQuestions?>
        </p></h1>
        
    </div>
<div class="wenjuanCont">
<?php

foreach ( $data_list as $qt ) {
	?>
	<div class="exam_block_screen">
	<?=$qt ['question_display_html']?>
    </div>
	<?php
}
?>
</form>
<!--
<a href="javascript:;" id="check" class="cursor"><img src="images/check_btn2.jpg" /></a> 
-->
<a href="javascript:;" id="sub" class="cursor orangebutton">全部答完，提交</a>
</div>
</article>
</section>
<script type="text/javascript">
$(".exam_block_screen").css('display','block');
</script>

