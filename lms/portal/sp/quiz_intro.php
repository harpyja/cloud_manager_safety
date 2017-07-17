<?php
include_once ('../../main/exercice/exercise.class.php');
include_once ('../../main/exercice/question.class.php');
include_once ('../../main/exercice/answer.class.php');
$get_cidreq=  $_REQUEST['cidReq'];//getgpc('cidReq');
if (empty ( $get_cidreq )) {
	$cidReq = null;
	$cidReset = TRUE;
}
$language_file [] = 'exercice';
include_once ("inc/app.inc.php");


$typeId=getgpc('type');
if (api_get_setting ( 'enable_modules', 'exam_center' ) != 'true') {
	api_redirect ( 'learning_center.php' );
}

include_once ('../../main/exercice/exercise.lib.php');

api_block_anonymous_users ();
$user_id = api_get_user_id ();
$exerciseId = getgpc ( 'exerciseId' );

$objExercise = new Exercise ();

//测验不存在或不可用时
$result_access_check = Exercise::do_exam_available ( $exerciseId, $user_id ); //echo $result_access_check;
$redirect_url = api_get_path(WEB_PORTAL_PATH) . 'exam_center.php?type='.$typeId;

//if ($result_access_check != SUCCESS) {
//	unset ( $objExercise );
//	switch ($result_access_check) {
//		case 101 : //测验不存在
//			Display::display_msgbox ( get_lang ( 'ErrorExamNotFound' ), $redirect_url, 'warning' );
//			break;
//		case 102 : //测验不可用
//			Display::display_msgbox ( get_lang ( 'ErrorExamNotAvailable' ), $redirect_url, 'warning' );
//			break;
//		case 103 : //不是考试考生
//			Display::display_msgbox ( get_lang ( 'ErrorExamUserNotExists' ), $redirect_url, 'warning' );
//			break;
//		case 104 : //考试时间不允许,可参加考试时间段限制
//			Display::display_msgbox ( get_lang ( 'ErrorExamTimeNotAllowed' ), $redirect_url, 'warning' );
//			break;
//		case 105 : //超过最大允许考试次数,考试次数限制: 0:不限制
//			Display::display_msgbox ( get_lang ( 'ErrorReachedMaxAttempts' ), $redirect_url, 'warning' );
//			break;
//		case 106 : //已通过
//			Display::display_msgbox ( get_lang ( 'ErrorPassTheExamNotAllowed' ), $redirect_url, 'success' );
//			break;
//		default :
//			Display::display_msgbox ( get_lang ( 'NotAllowedHere' ), $redirect_url, 'error' );
//	}
//}

$objExercise->read ( $exerciseId );

if (is_null ( $objExercise ) or ! is_object ( $objExercise )) {
	api_redirect ( "course_home.php?" . api_get_cidreq () );
}

$exerciseTitle = $objExercise->selectTitle ();
$exerciseDescription = $objExercise->selectDescription ();
$exerciseDescription = stripslashes ( $exerciseDescription );
//$attempt_times = $objExercise->get_user_attempts ( $user_id, $course_code );
$attempt_times = $objExercise->get_exam_user_attempts ( $exerciseId, $user_id );
$test_duration = $objExercise->selectDuration ();

$available_time = $objExercise->get_exam_time ( $user_id, $exerciseId, $objExercise );
$exerciseStartTime = $available_time ['start_date'];
$exerciseEndTime = $available_time ['end_date'];

//试题列表
if (! isset ( $_SESSION ['questionList'] ) or empty ( $_SESSION ['questionList'] )) {
	$questionList = $objExercise->selectQuestionList ();
	api_session_register ( 'questionList' );
}

if ((! isset ( $objExercise ) && isset ( $_SESSION ['objExercise'] )) or isset ( $_SESSION ['questionList'] )) {
	$questionList = $_SESSION ['questionList'];
}

//var_dump($questionList);
$nbrQuestions = sizeof ( $questionList );

include_once ("inc/page_header.php");
$htmlHeadXtra [] = '<script type="text/javascript">
	$(document).ready( function() {
		if(!is_ie){
			//$.prompt("' . get_lang ( "PlsUseIE" ) . '");
		}

		$("#confirm_button").click( function() {
					var gotourl = "quiz_paper.php?exerciseId=' . $exerciseId . '";
					$.prompt("' . get_lang ( "CanYouConfirmThis" ) . '",
						{
				buttons:{\'确定\':true, \'取消\':false},
				callback: function(v,m,f){
					if(v){
						$("#theForm").submit();
				/**		if(is_ie){
 								$(this).attr("disabled","true");
 								window.open(gotourl,"","toolbar=no, menubar=no,fullscreen=yes,resizable=no,scrollbars=yes");
 							}else{
 								$.prompt("' . get_lang ( "PlsUseIE" ) . '");
 							}**/
					}
					else{}
				}
			});
		});
	});
	</script>';
?>
<!--<script type="text/javascript">-->
<!--    $(document).ready( function() {-->
<!--        if(!is_ie){-->
<!--            //$.prompt("' . get_lang ( "PlsUseIE" ) . '");-->
<!--        }-->
<!---->
<!--        $("#confirm_button").click( function() {-->
<!--            var gotourl = "quiz_paper.php?exerciseId='--><?//=$exerciseId?><!--'";-->
<!--            $.prompt("'--><?//=get_lang ( "CanYouConfirmThis" )?><!--'",-->
<!--            {-->
<!--                buttons:{'确定':true, '取消':false},-->
<!--            callback: function(v,m,f){-->
<!--                if(v){-->
<!--                    $("#theForm").submit();-->
<!--                    window.open(gotourl,"","toolbar=no, menubar=no,fullscreen=yes,resizable=no,scrollbars=yes");-->
<!--                    /**		if(is_ie){-->
<!-- 								$(this).attr("disabled","true");-->
<!-- 								window.open(gotourl,"","toolbar=no, menubar=no,fullscreen=yes,resizable=no,scrollbars=yes");-->
<!-- 							}else{-->
<!-- 								$.prompt("' . get_lang ( "PlsUseIE" ) . '");-->
<!-- 							}**/-->
<!--                }-->
<!--            }-->
<!--        });-->
<!--    });-->
<!--    });-->
<!--</script>-->



<?php

$htmlHeadXtra [] = '<style>
table .testInfo {
	background-color:#F8F8F8;
	border:1px dotted #808080;
	margin-left:auto;
	margin-right:auto;
	text-align:left;
	width:80%;
}

table.testInfo td#testInfoImage {
	text-align:center;
	width:15%;
}

table.testInfo td {
	white-space:nowrap;
	padding-bottom:6px;
}


button.next {
	background-image:url("../../themes/default/images/button_next.gif");
}
button.save, button.add, button.search, button.refresh, button.upload, button.login, button.plus, button.minus, button.next, button.back, button.simple {
	background-color:rgb(0,49,92);
	border-color:#D4E2F6;
}
button.add, button.save, button.cancel, button.refresh, button.upload, button.search, button.login, button.plus, button.minus, button.next, button.back {
	background-position:10px 50%;
	background-repeat:no-repeat;
	padding-left:30px;
}
button {
	-moz-border-radius:5px 5px 5px 5px;
	background-color:#A8A7A7;
	border-width:1px;
	color:white;
	cursor:pointer;
	font-size:100%;
	margin:0 5px 3px 3px !important;
	padding:5px 15px;
	text-decoration:none;
	vertical-align:middle;
}
</style>';

$exerciseTitle = api_parse_tex ( $exerciseTitle );

if ($htmlHeadXtra) {
	foreach ( $htmlHeadXtra as $head_html ) {
		echo $head_html;
	}
}
?>


<!-- <div class="register_banner"></div> -->
<?php
display_tab ( TAB_EXAM_CENTER );
if(isset($_GET['noction']) && $_GET['noction']!==''){
//    $noction=getgpc('noction','G');
//    if($noction='101'){
//        $noctionVar='对不起，测验不存在！';
//    }elseif($noction='102'){
//        $noctionVar='对不起，测验不可用！';
//    }elseif($noction='103'){
//        $noctionVar='对不起，您不是考试考生！';
//    }elseif($noction='104'){
//        $noctionVar='对不起，考试时间不允许！';
//    }elseif($noction='105'){
//        $noctionVar='对不起，您已经超过最大允许考试次数！';
//    }elseif($noction='106'){
//        $noctionVar='对不起，您已通过该测试！';
//    }elseif($noction='default'){
//        $noctionVar='对不起，您的操作错误！';
//    }else{
//        $noctionVar='';
//    }
    echo '<br>
<div   style="width:80%;border:1px dashed #999999;backgroup:#EFEFEF;margin:0px auto;line-height:30px;height:30px;color:red">
 <b>&nbsp;&nbsp;&nbsp;对不起，您的测试已经超过最大允许考试次数或者测试时间已经过期，请联系！</b>
</div>';
}
?>

<!--<div class="body_banner_down">-->
<!--<div style="float: left; width: 55px;">&nbsp;</div>-->
<!--<a href="learning_center.php" class="label dd2">我的课程</a>-->
<!--<div style="float: left" class="dd2">|</div>-->
<!--<a href="learning_progress.php" class="label dd2">学习进度</a></div>-->


<form method="get" action="quiz_paper.php" id="theForm" name="theForm"
	target="_top"><input type="hidden" name="exerciseId"
	value="<?=$exerciseId?>" />
<div class="register_body">
<div class="emax_content">
	<h3 class="examTitle"><?php echo $exerciseTitle;?></h3>
<div class="mvbox exampinfo">
<table class="testInfo">
	<tbody>
		<tr>
			<td rowspan="6" id="testInfoImage"><?php
			echo Display::return_icon ( "desktop.png" );
			?></td>
			<td id="testInfoLabels"></td>
			<td></td>
		</tr>
		<tr>
			<td width="10%"><?php
			echo get_lang ( "QuizAllowedDuration" );
			?>:&nbsp;</td>
			<td align="left"><?php
			echo ($test_duration == 0 ? get_lang ( "NoLimitedDuration" ) : ($test_duration / 60) . '&nbsp;' . get_lang ( "Minites" ));
			?>&nbsp;</td>
			<td width="10%"><?php
			echo get_lang ( "QuestionCount" );
			?>:&nbsp;</td>
			<td width="30%"><?php
			echo $nbrQuestions;
			?></td>
		</tr>
		<tr>
			<td>最大允许考试次数:</td>
			<td><?=$objExercise->selectAttempts () == 0 ? '不限制' : $objExercise->selectAttempts ()?></td>
			<td><?php
			echo get_lang ( "AttemptedCount" );
			?>:&nbsp;</td>
			<td><?php
			echo $attempt_times . '&nbsp;' . get_lang ( "Times" );
			?></td>
		</tr>
		<tr>
			<td><?php
			echo get_lang ( "ExerciseDuration" );
			?>:&nbsp;</td>
			<td><?php
			echo (substr($exerciseStartTime,0,16) . '&nbsp;' . get_lang ( "To" ) . '&nbsp;' . substr($exerciseEndTime,0,16));
			?></td>
			<td><?php
			//echo get_lang ( "DisplayMode" );
			?>&nbsp;</td>
			<td><?php
			//echo get_lang ( "DisplayModeFullScreen" );
			?></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
		</tr>
	</tbody>
</table>
</div>
<div class="mvbox oninfo">
<?php
echo $exerciseDescription;
?>
</div>

<center>
<div class="sd">

<!--<input class="next"  id="confirm_button" type="submit" value='开始考试'>-->
    <input id="confirm_button" class="next" type="button" value="<?php
        echo get_lang ( "EnterExercise" );
        ?>">

</center>


</div>

</div>

<?php
//include_once 'inc/page_footer.php';
?>
