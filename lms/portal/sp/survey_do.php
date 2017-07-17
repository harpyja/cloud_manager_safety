<?php
$cidReset = true;
$language_file [] = 'survey';
include_once ("inc/app.inc.php");
include_once (api_get_path ( SYS_CODE_PATH ) . "survey/survey.inc.php");
if (!(api_get_setting ( 'enable_modules', 'survey_center' ) == 'true' AND $_configuration ['enable_module_survey'])) api_redirect ( 'index.php' );

$id = $survey_id = (isset ( $_REQUEST ['id'] ) ? getgpc ( 'id' ) : "");
$user_id = api_get_user_id ();

$result_access_check = SurveyManager::is_survey_available ( $id, $user_id );
//echo $result_access_check;
if ($result_access_check != SUCCESS && $result_access_check!=105) {
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

$formSent = getgpc("formSent");
$choice=  getgpc('choice'); 
$examType=  getgpc('examType');
$examResult=  getgpc('examResult');

//处理提交的测验表单
if (isset ( $formSent )) {
	
	if (empty ( $examResult ) or ! is_array ( $examResult )) {
		$examResult = array ();
	}
	
	if (is_array ( $choice )) {
		if (DEBUG_MODE) {
			api_error_log ( 'Receive Submit ExamResult:', __FILE__, __LINE__, "exam.log" );
			api_error_log ( $choice, __FILE__, __LINE__, "exam.log" );
		}
		
		if ($examType == ALL_ON_ONE_PAGE or $examType == ONE_TYPE_PER_PAGE) { //整屏显示时，接收表单传来数据,
			$examResult = $choice;
		}
		
		if (DEBUG_MODE) api_error_log ( '$choice is an array - end', __FILE__, __LINE__, "exam.log" );
	}

}
if (! is_array ( $examResult )) {
	api_error_log ( " 非法的调查问卷数据提交！" . $id . ",user_id=" . $user_id, __FILE__, __LINE__, "exam.log" );
	exit ( " 非法的调查问卷数据提交！" );
}

//var_dump ( $examResult );exit;

$survey_info = SurveyManager::get_info ( $survey_id );

$sql = "SELECT COUNT(id) FROM $tbl_survey_question WHERE survey_id=" . Database::escape ( $survey_id );
$nbrQuestions = Database::get_scalar_value ( $sql );

$sql = "DELETE FROM $tbl_survey_answer WHERE survey_id=" . Database::escape ( $survey_id )." AND user_id=".Database::escape($user_id);
api_sql_query ( $sql, __FILE__, __LINE__ );

$title = $survey_info ["title"];
$exerciseStartTime = substr ( $survey_info ["start_date"], 0, 16 );
$exerciseEndTime = substr ( $survey_info ["end_date"], 0, 16 );


$htmlHeadXtra[]='<script>survey_id='.$survey_id.';</script>';

$interbreadcrumb [] = array ("url" => 'index.php', "name" => get_lang ( 'HomePage' ) );
$interbreadcrumb [] = array ("url" => 'survey.php', "name" => get_lang ( 'MySurvey' ) );
$nameTools = $title;

$i = $totalScore = $totalWeighting = 0;

$data_list = array ();
$g_i = 1;
$major_question = 1;

$questionListByType = SurveyManager::get_question_list ( $survey_id );

$mqstn ['qstn_count'] = count ( $questionListByType );
if ($major_question == 1) {
	$mqstn ['is_init_display'] = 1;
	$major_question = 0;
} else {
	$mqstn ['is_init_display'] = 0;
}

if ($questionListByType && count ( $questionListByType ) > 0) {
	//$isAllowedToSeePaper = ($survey_info ["is_allowed_see_paper"] == 1);
	$question_display_html = "";
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
				$questionScore = UniqueAnswer::judge_question ( $questionId, $examResult );
				$totalScore += $questionScore;
				//将答题结果及成绩保存
				UniqueAnswer::save_result ( $survey_id, $user_id, $questionId, $examResult, $questionScore );
				break;
			case MULTIPLE_ANSWER : //多选题
				$questionScore = MultipleAnswer::judge_question ( $questionId, $examResult );
				$totalScore += $questionScore;
				//将答题结果及成绩保存
				MultipleAnswer::save_result ( $survey_id, $user_id, $questionId, $examResult );
				break;
			case FREE_ANSWER :
				FreeAnswer::save_result ( $survey_id, $user_id, $questionId, $examResult );
				break;
		
		}
		
		unset ( $objAnswerTmp, $questionScore );
		
		$mqstn ['question_display_html'] = $question_display_html;
		$g_i ++;
	
	} //END: foreach($questionList ...
} //END: if(有题目）
$data_list [] = $mqstn;
//END foreach($quiz_question_type ...

/*
 ==============================================================================
 主要功能:  记录提交答案及结果
 ==============================================================================
 */
$data_tracking = serialize ( $examResult );
$sub_result = SurveyManager::update_event_survey ( $survey_id, $user_id, $totalScore, $data_tracking );

include_once ("inc/page_header.php");
?>
<?php display_tab(TAB_SURVEY_CENTER);?>

<aside id="sidebar" class="column open cloudindex" style="height: 2232px;">
    <div id="flexButton" class="closeButton close"></div>
	<div class="navs"></div>
</aside>
<section id="main" class="column">
	<h4 class="page-mark"><?php echo display_interbreadcrumb ( $interbreadcrumb, $nameTools, false ) ;?></h4>
	<article class="publicModule width_full">
	<script type="text/javascript">
        $(document).ready(function(){
            $("#btnSub").click(function(){
                if($("#suggestion").val()==""){ $.prompt("建议不能为空!"); return false; }
                $.post("ajax_actions.php",{action:"save_survey_suggestion",survey_id:survey_id,suggestion:$("#suggestion").val()},
                        function(data){
                            //alert(data);
                            if(data==1){
                                $.prompt("您已成功提交建议!",{
                                    buttons:{'确定':true},
                                    callback: function(v,m,f){
                                        if(v){
                                            location.href="survey.php";
                                        }
                                    }
                                });
                            }else{
                                $.prompt("提交失败!");
                            }
                        }
                    );
                });
        });
    
    </script>
<form method="post" id="frm_exam" name="frm_exam" action="exam_fb.php">
	<input type="hidden" name="id" value="{$exam_info.id}" />
    <!--
    <h1 class="h1t"><?=$title?></h1>
    <div class="emax_title de1">
        <span>学员：<?=$_SESSION['_user']['firstName']?></span>
        <span>题目总数:<?=$nbrQuestions?></span>
    </div>
	-->
    <div class="suok">
	<p class="la">您的调查问卷已成功提交! 非常感谢你在百忙中的积极参与!</p>
	<p class="lb">如果您还有关于本调查的其它建议, 欢迎您在下面写下来!</p>
    <div class="inputwidth">
	<textarea id="suggestion" name="suggestion"></textarea>
	<input type="button" id="btnSub" class="orangebutton"  value="提交" />
    </div>
    </div>
</form>
</article>
</section>


