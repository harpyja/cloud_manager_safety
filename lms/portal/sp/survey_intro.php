<?php
$cidReset = true;
$language_file [] = 'survey';
include_once ("inc/app.inc.php");

if (!(api_get_setting ( 'enable_modules', 'survey_center' ) == 'true' AND $_configuration ['enable_module_survey'])) api_redirect ( 'index.php' );

include_once (api_get_path ( SYS_CODE_PATH ) . "survey/survey.inc.php");

$id = (isset ( $_REQUEST ['id'] ) ? getgpc ( 'id' ) : "");

$user_id = api_get_user_id ();

$result_access_check = SurveyManager::is_survey_available ( $id, $user_id );
if ($result_access_check != SUCCESS) {
	switch ($result_access_check) {
		case 101 : //调查问卷不存在
			$result_msg = get_lang ( 'ExamNotFound' );
			break;
		case 102 : //调查问卷不可用
			$result_msg = get_lang ( 'ErrorSurveyNotAvailable' );
			break;
		case 103 : //不是调查问卷考生
			$result_msg = get_lang ( 'ErrorSurveyUserNotExists' );
			break;
		case 104 : //调查问卷时间不允许,可参加考试时间段限制
			$result_msg = get_lang ( 'ErrorSurveyTimeNotAllowed' );
			break;
		case 105 : //超过最大允许调查次数,调查次数限制: 0:不限制
			$result_msg = get_lang ( 'ErrorReachedMaxAttempts' );
			break;
		default :
			$result_msg = get_lang ( 'NotAllowedHere' );
	}

}

$survey_info = SurveyManager::get_info ( $id );
//var_dump($survey_info);


$sql = "SELECT COUNT(id) FROM $tbl_survey_question WHERE survey_id=" . Database::escape ( $id );
$nbrQuestions = Database::get_scalar_value ( $sql );

$title = $survey_info ["title"];
$exerciseStartTime = substr ( $survey_info ["start_date"], 0, 16 );
$exerciseEndTime = substr ( $survey_info ["end_date"], 0, 16 );

$js = '<script type="text/javascript">
	var lang_plsUseIE="' . get_lang ( "PlsUseIE" ) . '";
	var confirmYourChoice="' . get_lang ( "CanYouConfirmThis" ) . '";
	var survey_id="' . $id . '";
	</script>';

$interbreadcrumb [] = array ("url" => 'index.php', "name" => get_lang ( 'HomePage' ) );
$interbreadcrumb [] = array ("url" => 'survey.php', "name" => get_lang ( 'MySurvey' ) );
$nameTools = $title;
include_once ("inc/page_header.php");
display_tab ( TAB_SURVEY_CENTER );
?>
<?=$js;?>
<script type="text/javascript">
	$(document).ready( function() {
		if(!is_ie){
			//$.prompt(lang_plsUseIE);
		}
		
		$("#confirm_button").click( function() {
					$.prompt(confirmYourChoice,	{buttons:{'确定':true, '取消':false},
						callback: function(v,m,f){
							if(v){
								$("#theForm").submit();
							}
						else{}
				}
			});
		});
	});
	</script>

<style type="text/css">
table .testInfo {
	background-color: #F8F8F8;
	border: 1px dotted #808080;
	margin-left: auto;
	margin-right: auto;
	text-align: left;
	width: 80%;
}

table.testInfo td#testInfoImage {
	text-align: center;
	width: 15%;
}

table.testInfo td {
	white-space: nowrap;
	padding-bottom: 10px;
}

button.next {
	background-image: url("../../themes/default/images/button_next.gif");
	background-color: #B31000;
	border-color: #D4E2F6;
	background-position: 10px 50%;
	background-repeat: no-repeat;
	padding-left: 30px;
}

button {
	-moz-border-radius: 5px 5px 5px 5px;
	background-color: #A8A7A7;
	border-width: 1px;
	color: white;
	cursor: pointer;
	font-size: 100%;
	margin: 0 5px 3px 3px !important;
	padding: 5px 15px;
	text-decoration: none;
	vertical-align: middle;
}

.warning-message {
	margin-left: auto;
	margin-right: auto;
	width: 60%;
	background-color: #FFCD82;
	border: 1px solid #FF6600;
	color: #666666;
	margin-bottom: 10px;
	margin-top: 10px;
	min-height: 30px;
	padding: 5px;
	position: relative;
}
</style>
<aside id="sidebar" class="column open cloudindex" style="height: 989px;">
    <div id="flexButton" class="closeButton close"></div>
	<div class="navs"></div>
</aside>
<section id="main" class="column">
	<h4 class="page-mark"><?=display_interbreadcrumb ( $interbreadcrumb, $nameTools, false )?></h4>
	<?php
    if ($result_access_check != SUCCESS) {
        ?>
    	<div class="alert_info"><?=$result_msg?></div>
    <?php
    }else{
    ?>	
    <article class="publicModule width_full survey">
    <form method="get" action="survey_paper.php" id="theForm" name="theForm"><input type="hidden" name="id" value="<?=$survey_info ['id']?>" />
        <h2 class="surveyTitle"><?=$title?> <span class="xcv"><?=get_lang ( 'NumberOfQuestions' )?>:<?=$nbrQuestions?>  <span><?=get_lang ( 'AttemptDuration' )?>:&nbsp;<?=substr ( $survey_info ['start_date'], 0, 16 )?> 至 <?=substr ( $survey_info ['end_date'], 0, 16 )?></span></span></h2>

 <div class="surveyCc"><?=$survey_info ['intro']?></div>
    
    <?php
    
    if ($result_access_check == SUCCESS) {
        ?>
<!--    <button id="confirm_button" class="next" type="button">--><?//=get_lang ( 'EnterSurvey' )?><!--</button>-->
        <input type="submit" class="next confirm_button orangebutton" value="<?=get_lang ( 'EnterSurvey' )?>"/>
    <?php
    }
    ?>
    
    </form>
    <?php	
    }
    ?>
</article>
</section>
 

