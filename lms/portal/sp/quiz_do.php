<?php
include_once ('../../main/exercice/exercise.class.php');
include_once ('../../main/exercice/question.class.php');
include_once ('../../main/exercice/answer.class.php');

$language_file [] = 'exercice';
include_once ("inc/app.inc.php");
include_once (api_get_path ( SYS_CODE_PATH ) . 'exercice/exercise.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'text.lib.php');
include (api_get_path ( LIBRARY_PATH ) . 'mail.lib.inc.php');

if (api_get_setting ( 'enable_modules', 'exam_center' ) != 'true') {
	api_redirect ( 'learning_center.php' );
}

$user_id = api_get_user_id ();
$username=  Database::getval("select `firstname` from user where user_id=".$user_id,__FILE__,__LINE__);
$name=  Database::getval("select `username` from user where user_id=".$user_id,__FILE__,__LINE__);
$is_allowedToEdit = api_is_allowed_to_edit (); //liyu


$main_user_table = Database::get_main_table ( TABLE_MAIN_USER );
$main_course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );

$formSent = $_POST['formSent'];
$exerciseId =$_POST['exerciseId']; 
$choice = $_POST['choice']; //提交的答案
//  echo "<pre>";              
//print_r($_FILES[choice]) ; 
//     echo "</pre>";       
$filey=$_FILES[choice];
$fname=$filey["name"];//考题号=》上传文件的名字
   //   var_dump($choice)."</br>"; 

$tpname=$filey["tmp_name"];
$a=  array_keys($fname);
$numba=count($a);
//echo "总共有多少个".$numba;
//echo  "第一个名字为".$a[0];
$ffgg= $tpname[8] ;
//$username=api_get_user_name();
            
//var_dump($filey);
 
        
$url1=URL_ROOT.'/www'.URL_APPEDND."/storage/exam/".$name ;

if(!file_exists($url1)){
exec("mkdir $url1");
//echo $url1;
}
$url2= URL_ROOT.'/www'.URL_APPEDND."/storage/exam/".$name."/".$exerciseId;
if(!file_exists($url2)){
exec("mkdir $url2");
}

//echo   $username."/".$exerciseId ;

//    echo "<br>".$url1;
//    echo "<br>".$url2;
//    echo "<br>".$url;            
//var_dump($exerciseId);//考题的Id
//echo  $_FILES["choice"]["name"];
//测验不存在或不可用时
$result_access_check = Exercise::do_exam_available ( $exerciseId, $user_id ); //echo $result_access_check;
$redirect_url = api_get_path ( WEB_PORTAL_PATH ) . 'exam_center.php';
if ($result_access_check != SUCCESS) {
//	switch ($result_access_check) {
//                case 101 : //测验不存在
//        //             echo '<script>alert("测验不存在")</script>';
//                    header("location:".$_SERVER['HTTP_REFERER']."&noction=101");
//                    //Display::display_msgbox ( get_lang ( 'ErrorExamNotFound' ), $redirect_url, 'warning' );
//                    break;
//                case 102 : //测验不可用
//                     header("location:".$_SERVER['HTTP_REFERER']."&noction=102");
//                    //Display::display_msgbox ( get_lang ( 'ErrorExamNotAvailable' ), $redirect_url, 'warning' );
//                    break;
//                case 103 : //不是考试考生
//                    header("location:".$_SERVER['HTTP_REFERER']."&noction=103");
//                    //Display::display_msgbox ( get_lang ( 'ErrorExamUserNotExists' ), $redirect_url, 'warning' );
//                    break;
//                case 104 : //考试时间不允许,可参加考试时间段限制
//                    header("location:".$_SERVER['HTTP_REFERER']."&noction=104");
//                    //Display::display_msgbox ( get_lang ( 'ErrorExamTimeNotAllowed' ), $redirect_url, 'warning' );
//                    break;
//                case 105 : //超过最大允许考试次数,考试次数限制: 0:不限制
//        //            echo '<script>alert("超过最大允许考试次数")</script>';
//        //            header("location:http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
//                    header("location:".$_SERVER['HTTP_REFERER']."&noction=105");
//                    //Display::display_msgbox ( get_lang ( 'ErrorReachedMaxAttempts' ), $redirect_url, 'warning' );
//                    break;
//                case 106 : //已通过
//                   header("location:".$_SERVER['HTTP_REFERER']."&noction=106");
//                    //Display::display_msgbox ( get_lang ( 'ErrorPassTheExamNotAllowed' ), 'exam_center.php', 'warning' );
//                    break;
//                default :
//                    header("location:".$_SERVER['HTTP_REFERER']."&noction=default");
//                    //Display::display_msgbox ( get_lang ( 'NotAllowedHere' ), $redirect_url, 'error' );
//	}
}

$objExercise = new Exercise ();
$objExercise->read ( $exerciseId );




//////////////////////////////////////////////////
//$upfiles="im no1";
//////////////////////////////////////////////////


//处理提交的测验表单
if ($formSent) {
	$exerciseResult = array ();
	if (is_array ( $choice )) $exerciseResult = $choice;
}
//
//echo "<br>-----------------孤单的分割线--------------<br>";

 array_push($exerciseResult, $fname);
//                
//echo '<br>-----------华丽的分割线------------<br>';
 //  print_r   ($exerciseResult);//保存本次考试的数据，除了上传文件

                
//if (! is_array ( $exerciseResult ) || ! is_object ( $objExercise )) Display::display_msgbox ( '非法提交！', api_get_path ( WEB_QH_PATH ) . 'exam_center.php', 'warning' );
$data_tracking = serialize ( $exerciseResult );//序列化用户提交的答案

//
//echo '<br>-----------悲催的分割线------------<br>';
// print_r($data_tracking);//打印实例化结果
$exeId = Exercise::get_quiz_track_id ( $exerciseId );//exam_track的id
                
//if (empty ( $exeId )) Display::display_msgbox ( '非法提交！', api_get_path ( WEB_QH_PATH ) . 'exam_center.php', 'warning' );
if ($exerciseResult) { //先保存用户提交的答案,防止丢失
      Exercise::update_event_exercice ( $exeId, $objExercise->id, $user_id, 0, 0, $data_tracking );
}

$questionList = $questionList = $objExercise->selectQuestionList ();

$exerciseTitle = $objExercise->selectTitle (); //测验名称
$test_duration = $objExercise->selectDuration ();
if ($objExercise->feedbacktype == 0) $isAllowedToSeeAnswer = TRUE;
if ($objExercise->feedbacktype == 2) $isAllowedToSeeAnswer = FALSE;
if ($objExercise->results_disabled == 0) $isAllowedToSeePaper = TRUE;
if ($objExercise->results_disabled == 1) $isAllowedToSeePaper = FALSE;
$courseName = $_SESSION ['_course'] ['name'];
//$courae_admin = CourseManager::get_course_admin ( api_get_course_code () );


$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {
		$("body").addClass("yui-skin-sam");
		$(".yui-content > div").fadeOut("fast").eq(0).fadeIn("normal");
		$("#tab li").eq(0).addClass("selected");
		
		$("#tab li").click(function(){ 
			$(this).addClass("selected").siblings().removeClass("selected");
			var cur_idx=$("#tab li").index(this);
			$(".yui-content > div").eq(cur_idx).addClass("selected").siblings().removeClass("selected");
			$(".yui-content > div").fadeOut("normal").eq(cur_idx).fadeIn("normal");
		});
});</script>';

include_once ("inc/page_header.php");

display_tab ( TAB_EXAM_CENTER );
?>


<div class="body_banner_down">
<!--
<div style="float: left; width: 55px;">&nbsp;</div>
<a href="exam_center.php" class="label dd2">我的考试</a>
<div style="float: left" class="dd2">|</div>
<a href="exam_result.php" class="label dd2">考试成绩查询</a></div>
-->
<div class="register_body">
<div class="emax_content">

<?php
$quiz_question_type = $objExercise->getQuizQuestionTypes ( $exerciseId );

$quiz_qt = array_keys ( $quiz_question_type );
                
$questionListArr = $objExercise->getAllQuestionsByType ();
                
foreach ( $quiz_question_type as $qtype => $qcount ) {
	$total_question_cnt += $qcount [0];
}

$i = $totalScore = $totalWeighting = 0;

echo form_open ( "exercice.php", array ('method' => 'get' ), array ('exerciseId' => $exerciseId ) );
?>
<div class="ehead">
<h3>
<?php

?>
</h3>
<div class="emax_title de1">
    <h1><?=$exerciseTitle?></h1>
    <?php
        if($objExercise->feedbacktype!=1){
            echo '	<span style="margin-right: 30px;">考生：'.$username.'</span>';
        }
    ?>
	<span style="margin-right: 30px;">试题总数:<?=$total_question_cnt?></span>
    <span style="margin-right: 30px;">答题时间：<?php
	echo ($test_duration > 0 ? ($test_duration / 60) . "分钟" : "不限制");
	?></span>
</div>
</div>
<?php
if ($objExercise->results_disabled) {
	ob_start ();
}
?>

<div id="tab" class="yui-navset" style="margin-top: 1px"><!-- Tab显示大题题型 -->

<div class="yui-content" id="slider">
<?php
$totalScoreKgt = $totalScoreZgt = 0;
$containZgt = false;
foreach ( $quiz_question_type as $qtype => $qcount ) {
	$questionListByType = $questionListArr [$qtype];
	?>
<div id="tab_<?=$qtype?>">
<div class="exam_block_screen">
<!--<div class="register_hint dd2" style="width: auto; margin: 10px 0 15px;">--><?//=$_question_types [$qtype]?>
<!--(本题型共--><?//=count ( $questionListByType )?><!--题，共--><?//=$quiz_question_type [$qtype] [1]?><!--分)</div>-->

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
			<div class="exam_problem dd7">
<div style="height: auto; border-right: 1px dashed #c3c3c3; float: left; width: 720px; padding: 10px 0;">
<!--<div>--><?//=$counter . "、" . $questionName;?><!-- (--><?//=$questionWeighting?><!-- 分)</div>-->

<?php
			if (in_array ( $answerType, array (UNIQUE_ANSWER, MULTIPLE_ANSWER, TRUE_FALSE_ANSWER ) )) {
				echo '<table style="text-align: center; margin-top: 10px;display: none;" cellspacing="0"><tr>
 	<!--td width="70">我的选择</td>
 		<td width="70" style="color: #A0001B">正确答案</td>
		<td width="500">选项</td-->';

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
//					display_unique_or_multiple_answer ( $answerType, $studentChoice, $answer, $answerCorrect );
				}
				echo '</table>';
			} elseif ($answerType == FILL_IN_BLANKS) {
				$answer = $objAnswerTmp->selectAnswer ( 1 );
				$rtn_data = display_fill_blank_answer ( $choice, $answer, $questionWeighting, $isAllowedToSeeAnswer, $isAllowedToSeePaper );
				$questionScore = $rtn_data ['score'];
				$totalScore += $questionScore;
//				echo $rtn_data ['html'];
			} elseif ($answerType == FREE_ANSWER) {
//				echo '<div style="height: 9px; overflow: hidden;"></div>';
//				echo '<div>' . ($isAllowedToSeePaper ? $choice : "") . '</div>';
			
                        }
//                        elseif($answerType == COMBAT_QUESTION){
//                            
//                        }
			?>
</div>

<!--<div style="float: left; width: 105px; text-align: center; margin-top: 10px;">-->
<?php
			if ($isAllowedToSeePaper) {
				?>
<!--<img src="images/--><?//=($questionScore == $questionWeighting ? "correct.gif" : "cross.gif")?><!--" /><br />-->
<!--<strong> 此题--><?//=($questionWeighting)?><!--分,得--><?//=$questionScore?><!--分</strong></div>-->
<?php
			}
			if ($answerType == FREE_ANSWER) {
				$containZgt = true;
				//echo '此题最终得分视评卷决定';
			}
			?>
<div class="clearall"></div>
</div>

<!--<div class="analyze"><span style="font-size: 14px; color: #A0001B">--><?//=get_lang ( "QuestionAnalysis" )?><!--：</span><br />-->
<!--<span class="dd2">--><?//=$questionComment?><!--</span>-->
<!--<div class="clearall"></div>-->
<!--</div>-->


<?php
                                       
			unset ( $objAnswerTmp );
			$totalWeighting += $questionWeighting;
			
			//以下大段主要功能: 将答题结果及成绩保存
			//if ($_configuration ['tracking_enabled'] && ! api_is_course_admin ()) {
				if (is_string ( $choice )) {
					if ($answerType == UNIQUE_ANSWER)
						$studentSubAnswer = Question::$alpha [$choice];
					else {
						$studentSubAnswer = $choice;
					}
				}
				if (is_array ( $choice )) {
					if ($answerType == MULTIPLE_ANSWER) {
						foreach ( $choice as $c ) {
							$ans [] = Question::$alpha [$c];
						}
						$studentSubAnswer = implode ( "|", $ans );
					} else {
						$studentSubAnswer = implode ( "|", $choice );
					}
				}
                                if (is_string ( $choice )) {
					if ($answerType == COMBAT_QUESTION){
					
						$studentSubAnswer = $choice;
                                                $furl = $fname[$questionId];
                                            //   $file =  $file[$questionId] ;
                                                
                                    $src1=$tpname[$questionId];
                                    
                                        if($src1!=""){
                                            $file=URL_ROOT.'/www'.URL_APPEDND."/storage/exam/".$_SESSION['_user']['username']."/".$exerciseId."/".$furl;
                                              //$file=URL_ROOT.'/www'.URL_APPEDND."/storage/exam/".$name."/".$exerciseId."/".rand().".zip";
                                        }else{
                                            $file = "";
                                        }
                                   move_uploaded_file($src1,$file);   
                                $src1="";
                                        }	
				}
                                if (is_string ( $choice )) {
					if ($answerType == FREE_ANSWER){
						$studentSubAnswer = $choice;
                                             //  $file =  $file[$questionId] ;
                                            $src1=$tpname[$questionId];
                                            $furl = $fname[$questionId];
                            if($src1!=""){
                                
                                //$file=URL_ROOT.'/www'.URL_APPEDND."/storage/exam/".$name."/".$exerciseId."/".rand().".zip";
                                $file=URL_ROOT.'/www'.URL_APPEDND."/storage/exam/".$_SESSION['_user']['username']."/".$exerciseId."/".$furl;
                                }else{
                                $file = "";
                                }
                     move_uploaded_file($src1,$file);   
                                        $src1="";
                                        }	
				}
                                if(!isset($file)){
                                    $file = '';
                                }
                                if($exeId){
                                    Exercise::exercise_attempt ( $questionScore, $studentSubAnswer, $questionId, $exeId, $i ,$file);
                                }
				
			//} //END：评分结果
                                unset($file);
			?>
	<?php
		} //END: 题目 foreach($questionListByType ...
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
//$totalScore = ($totalScore > 100 ? 100 : $totalScore);
$totalScore = $totalScore;
$score = $totalWeighting > 0 ? (round ( round ( $totalScore ) / $totalWeighting * 100 )) : 0; //百分比成绩

 if ($containZgt) {
	$exercise_result = "<p>试卷卷面总分为: " . $totalWeighting . "</p><p>您的客观题部分成绩为:" . round ( $totalScore ) . "  (百分制成绩为: " . $score . ')!';
	$exercise_result .= '</p><p> 本考试含有主观题, 最终成绩的确定以教师评定为准!</p>';
} else {
	$exercise_result = "<p>试卷卷面总分为: " . $totalWeighting . "</p><p> 您的总成绩为:" . round ( $totalScore ) . ",  (百分制成绩为:" . $score . ').</p>';
}
/**
if ($containZgt) {
	$exercise_result = "<p>试卷卷面总分为: " . $totalWeighting . ",</p> <p>您的客观题部分成绩为:" . round ( $totalScore ) . " (百分制成绩为: " . $score . ')</p>.';
	$exercise_result = '<p> 本考试含有主观题, 最终成绩的确定以教师评定为准!</p>';
} else {
	$exercise_result = "<p>试卷卷面总分为: " . $totalWeighting . "</p>, <p>您的总成绩为:" . round ( $totalScore ) . ",  (百分制成绩为: :" . $score . ')></p>.';
}
**/
/*
 ==============================================================================
 主要功能:  记录提交答案及结果  保存到 exam_track表
 ==============================================================================
 */

if ($_configuration ['tracking_enabled']) {
	Exercise::update_event_exercice ( $exeId, $objExercise->id, api_get_user_id (), $totalScore, $totalWeighting, $data_tracking );
}

//不显示答案
if (! $isAllowedToSeeAnswer) ob_end_clean ();

$result_msg = '<span class="okexame">' . get_lang ( 'ExerciseFinished' ) . "</span>";
$result_msg .= ($isAllowedToSeePaper ? $exercise_result : "");
//if($objExercise->results_disabled) {
//	ob_end_clean();
//Display::display_warning_message ( $result_msg, false );


?>
    
<div class="register_hint dd2 sand"><?=$result_msg?></div>
<?php
echo form_close ();
?>
<div class="clearall"></div>
</div>

<div class="clearall"></div>
</div>

<?php
api_session_unregister ( 'exerciseResult' );
api_session_unregister ( 'exerciseResultCoordinates' );
api_session_unregister ( 'quizStartTime' );
api_session_unregister ( 'exercice_start_date' );
