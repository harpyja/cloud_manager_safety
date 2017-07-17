<?php
/************************
 * 查看考生答卷
 ************************/

$language_file = array ('survey', 'admin' );
$cidReset = true;
require_once ('../inc/global.inc.php');

api_protect_admin_script ();

require_once ('survey.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( 'action' ) : "");
$survey_id = isset ( $_REQUEST ['survey_id'] ) ? intval(getgpc ( "survey_id" )) : "";
if (empty ( $survey_id )) {
	exit ( "非法访问" );
}
$org_id = intval(getgpc ( 'org_id', 'G' ));
$dept_id = isset ( $_GET ['keyword_deptid'] ) ? intval(getgpc ( 'keyword_deptid', 'G' )) : '0';
$objDept = new DeptManager ();
if (is_not_blank ( $dept_id )) {
	$all_sub_depts = $objDept->get_sub_dept_ddl ( $org_id );
	foreach ( $all_sub_depts as $item ) {
		$depts [$item ['id']] = str_repeat ( "&nbsp;&nbsp;", intval ( $item ['level'] / 2 ) ) . $item ['dept_name'];
	}
}

$survey_info = SurveyManager::get_info ( $survey_id );

$nbrQuestions = SurveyManager::get_question_count ( $survey_id );

$user_count = SurveyManager::get_survey_user_count ( $survey_id );

//$paperTotalScore=SurveyManager::get_paper_total_score($survey_id);


$title = $survey_info ["title"];
$examType = $survey_info ['display_type']; //试卷显示类型
$option_display_type = $survey_info ['option_display_type'];
$exerciseStartTime = substr ( $survey_info ["start_date"], 0, 16 );
$exerciseEndTime = substr ( $survey_info ["end_date"], 0, 16 );

$tbl_user_dept = Database::get_main_table ( VIEW_USER_DEPT );
if ($dept_id and ! is_equal ( $dept_id, 'null' )) {
	$dept_sn = $objDept->get_sub_dept_sn ( $dept_id );
	if ($dept_sn) $sql_where .= " AND t3.dept_sn LIKE '" . $dept_sn . "%'";
}
$stat_sql = "SELECT t1.id,count(option_id) AS cnt_opt_id,ROUND(count(option_id)/" . $user_count . "*100,1) AS rate
FROM $tbl_survey_question_option as t1 LEFT JOIN $tbl_survey_answer as t2 ON t1.id=t2.option_id, $tbl_user_dept AS t3
WHERE t1.survey_id=" . Database::escape ( $survey_id ) . " AND t2.user_id=t3.user_id ";
if ($sql_where) $stat_sql .= $sql_where;
$stat_sql .= " GROUP BY t1.id";
//echo $stat_sql;
$res = api_sql_query ( $stat_sql, __FILE__, __LINE__ );
while ( $row = Database::fetch_array ( $res, 'ASSOC' ) ) {
	$statResult [$row ['id']] = array ('cnt_opt_id' => $row ['cnt_opt_id'], 'rate' => $row ['rate'] );
}

$depts = $objDept->get_sub_dept_ddl2 ( DEPT_TOP_ID, 'array' );
$i = $totalScore = $totalWeighting = 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php
echo api_get_setting ( 'siteName' );
?></title>
<link href="../../portal/sp/index.css" rel="stylesheet" type="text/css" />
<link href="../../portal/sp/ext.css" rel="stylesheet" type="text/css" />
<style>
input[type=submit],input[type=button] {
	border: 1px solid #666
}
</style>
<?php
echo import_assets ( "commons.js" );
echo import_assets ( "jquery-latest.js" );
?>
</head>

<body>

<div class="register_body">
<div class="emax_content">

<?=form_open ( "reporting.php", array ('method' => 'get' ), array ('survey_id' => $survey_id ) )?>
<div class="register_title dc2" style="text-align: center;"><strong><?=$title?></strong>
<div style="margin-top: 6px; float: right; font-size: 14px"><input
	type="button" id="btnSub" value="<?=get_lang ( 'Close' )?>"
	onclick="javascript:window.close();" /></div>
</div>

<div class="emax_title de1">
<span style="margin-right: 10px;"><?=get_lang ( 'InDept' )?>: <?php
echo form_dropdown ( "keyword_deptid", $depts, $dept_id, 'id="dept_id" style="min-width:200px"' )?></span>
<span style="margin-right: 60px;"><input type="submit"
	value="<?=get_lang ( 'Statistics' )?>" /></span> <span
	style="margin-right: 30px;"></span></div>



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

<div class="register_hint dd2" style="width: auto; margin: 10px 0 15px;"><?=$qtype ["name"]?>
本调查问卷共<?=count ( $questionListByType )?>题</div>
<?php
if ($questionListByType && count ( $questionListByType ) > 0) {
	
	//列表显示试题
	foreach ( $questionListByType as $question ) {
		$questionId = $question ["id"];
		
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
				$rtn = UniqueAnswer::display_stat_result ( $questionId, $questionName, $g_i, $statResult );
				echo $rtn ["html"];
				$questionScore = $rtn ["score"];
				$totalScore += $rtn ["score"];
				break;
			case MULTIPLE_ANSWER : //多选题
				$rtn = MultipleAnswer::display_stat_result ( $questionId, $questionName, $g_i, $statResult );
				echo $rtn ["html"];
				$totalScore += $rtn ["score"];
				$questionScore = $rtn ["score"];
				break;
			case FREE_ANSWER :
				$rtn = FreeAnswer::display_stat_result ( $questionId, $questionName, $g_i );
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
	
	echo form_close ();
	?>
<div class="clearall"></div>
</div>

<div style="margin-top: 6px; float: right; font-size: 14px"><input
	type="button" id="btnSub" value="<?php
	echo get_lang ( 'Close' )?>"
	onclick="javascript:window.close();" /></div>
<div class="clearall"></div>
</div>



<script type="text/javascript">
$(".exam_block_screen").css('display','block');
</script>

<?php

include_once 'inc/page_footer.php';
?>