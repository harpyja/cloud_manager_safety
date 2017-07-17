<?php
/************************
 * 查看考生答卷
 ************************/

$language_file = 'survey';
$cidReset = true;
require_once ('../inc/global.inc.php');

api_protect_admin_script ();

require_once ('survey.inc.php');

$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( 'action' ) : "");
$survey_id = isset ( $_REQUEST ['survey_id'] ) ? intval(getgpc ( "survey_id" )) : "";
if (empty ( $survey_id )) {
	exit ();
}

$survey_info = SurveyManager::get_survey ( $survey_id );
//var_dump($survey_info);

$nbrQuestions = SurveyManager::get_question_count ( $survey_id );

//$paperTotalScore=SurveyManager::get_paper_total_score($survey_id);


$title = $survey_info ["title"];
$examType = $survey_info ['display_type']; //试卷显示类型
$option_display_type = $survey_info ['option_display_type'];

$exerciseStartTime = substr ( $survey_info ["start_date"], 0, 16 );
$exerciseEndTime = substr ( $survey_info ["end_date"], 0, 16 );

//调查项
$mqstnList = SurveyManager::get_survey_group_list ( $survey_id );
//var_dump($mqstnList);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php
echo get_lang ( "SystemName" );
?></title>
<link href="../../portal/sp/index.css" rel="stylesheet" type="text/css" />
<link href="../../portal/sp/ext.css" rel="stylesheet" type="text/css" />
<?php
echo import_assets ( "commons.js" );
echo import_assets ( "jquery-latest.js" );
echo import_assets ( "jquery-plugins/jquery.wtooltip.js" );
echo import_assets ( "jquery.anythingslider.js", api_get_path ( WEB_PATH ) . PORTAL_LAYOUT . 'js/' );
echo import_assets ( "jquery.easing.1.2.js", api_get_path ( WEB_PATH ) . PORTAL_LAYOUT . 'js/' );
echo import_assets ( "jquery-plugins/scrolltopcontrol.js" );
?>

</head>

<body>


<div class="register_body">
<div class="emax_content">


<div class="register_title dc2" style="text-align: center;"><strong><?php
echo $title;
?></strong></div>
<div style="margin-top: 6px; float: right"
	onclick="javascript:window.close();">
<button type="button" class="simple" style="border:1px solid #999; height:25px"><?php
echo get_lang ( 'Close' )?></button>
</div>
<div class="emax_title de1"><span style="margin-right: 30px;"></span> <span
	style="margin-right: 30px;">题目总数: <?=$nbrQuestions?></span></div>


<?php
if ($examType == ONE_TYPE_PER_PAGE) {
	?>
<!-- Tab显示大题题型 -->
<div>
	<?php
	$major_question = 1;
	foreach ( $mqstnList as $qtype ) {
		?>
<div
	class="emax_btn_type <?php
		if ($major_question == 1) {
			echo 'on';
			$major_question = 0;
		}
		?>"
	id="qt<?=$qtype ["id"]?>">
<?php
		echo $qtype ["name"];
		?></div>
<?php
	}
	?>
<div class="clearall"></div>
</div>
<!-- Tab显示大题题型 -->

<?php
}
?>

<?php
$g_i = 1;
$major_question = 1;
if (is_array ( $mqstnList ) && count ( $mqstnList ) > 0) {
foreach ( $mqstnList as $qtype ) {
	//大题下的试题列表
	$sqlwhere = " AND group_id=" . Database::escape ( $qtype ["id"] );
	$questionListByType = SurveyManager::get_question_list ( $survey_id, $sqlwhere );
	$mqstn ['mqstn_name'] = $qtype ["name"];
	
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
(本调查项共<?=count ( $questionListByType )?>题)</div>

<?php
	$question_display_html = "";
	if ($questionListByType && is_array ( $questionListByType )) {
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
			
			$i ++;
			$g_i ++;
		}
		echo $question_display_html;
	} //END: if(有题目）
	?>
	</div>
	<?php
} //END foreach($quiz_question_type ...
}else{
		?>
		<div id="qt_problem" class="exam_block_screen" style="display: block;">
		<?php
		$questionListByType = SurveyManager::get_question_list ( $survey_id, '' );
		$question_display_html = "";
	if ($questionListByType && is_array ( $questionListByType )) {
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
			
			$i ++;
			$g_i ++;
		}
		echo $question_display_html;
	} //END: if(有题目）
?>
		</div>
	<?php
}

?>


<div class="clearall"></div>

<?php
if ($examType == ONE_TYPE_PER_PAGE) {
	?><div style="text-align: center; margin-top: 15px;"><a href="#top"
	class="cursor"><img src="../../portal/default/images/next_btn1.jpg"
	style="margin-right: 15px;" id="next_btn" /></a><a href="#top"
	class="cursor"><img src="../../portal/default/images/last_btn1.jpg"
	id="last_btn" /></a></div>
<?php
}
?>

</div>

<div style="margin-top: 6px; float: right;"
	onclick="javascript:window.close();">
<!--<button type="button" class="simple" style="border:1px solid #999; height:25px"><?php
echo get_lang ( 'Close' )?></button>-->
</div>
<div class="clearall"></div>
</div>



<?php
if ($examType == ONE_TYPE_PER_PAGE) {
	?>
<script type="text/javascript">
var next = $('#next_btn');
var last = $('#last_btn');
var ctl = $('.emax_btn_type');
var block = $('.exam_block_screen');
var examType_num = ctl.length;
$(document).ready(function(){
	ctl.each(function(index,domEle){
		domEle.setAttribute("id",'emax_'+index);
	});//console.info(ctl);
	block.each(function(index,domEle){
		domEle.setAttribute("id",'emax_'+index+'_problem');
	});//console.info(block);
	ctl.get(0).setAttribute('class','emax_btn_type emax_btn_type_on');
});
ctl.bind('click',function(event){
	ctl.removeClass('emax_btn_type_on');
	$(this).addClass('emax_btn_type_on');
	var block_id = $(this).attr('id')+'_problem';//console.info(block_id);
	block.css('display','none');
	$('#'+block_id).css('display','block');
});
function getNowNum(){
	var now = ctl.filter('.emax_btn_type_on');
	var now_id = now.attr('id');
	var id_num = parseInt(now_id.split('_')[1]);
	return id_num;
};
next.bind('click',function(event){
	var now_num = getNowNum();//console.info(now_num);console.info(examType_num);
	if(now_num == (examType_num-1)){
		var goNum = 0;
	}
	else{
		var goNum = now_num+1;
	}
	ctl.removeClass('emax_btn_type_on');
	$('#emax_'+goNum).addClass('emax_btn_type_on');
	block.css('display','none');
	$('#emax_'+goNum+'_problem').css('display','block');
});
last.bind('click',function(event){
	var now_num = getNowNum();
	if(now_num == 0){
		var goNum = (examType_num-1);
	}
	else{
		var goNum = now_num-1;
	}
	ctl.removeClass('emax_btn_type_on');
	$('#emax_'+goNum).addClass('emax_btn_type_on');
	block.css('display','none');
	$('#emax_'+goNum+'_problem').css('display','block');
});
</script>
<?php
}
if ($examType == ALL_ON_ONE_PAGE) {
	?><script type="text/javascript">
$(".exam_block_screen").css('display','block');
</script>
<?php
}

include_once 'inc/page_footer.php';
?>