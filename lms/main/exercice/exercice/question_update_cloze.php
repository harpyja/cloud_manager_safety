<?php
include_once('exercise.class.php');
include_once('question.class.php');
include_once('answer.class.php');
include_once('cloze_question.class.php');

$language_file='exercice';
include_once("../inc/global.inc.php");

include_once('exercise.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include_once(api_get_path(LIBRARY_PATH).'document.lib.php');

$this_section=SECTION_COURSES;

$is_allowedToEdit=api_is_allowed_to_edit();
if(!$is_allowedToEdit){
	api_not_allowed();
}


$action=(isset($_REQUEST['action'])?getgpc("action"):"");
$type = getgpc('answerType');
$combo_questionId=intval(getgpc("pid"));
$exerciseId=intval(getgpc('exerciseId'));
$qid=intval(getgpc('qid'));

$objClozeQuestion=new ClozeQuestion();

if(isset($_GET['action'])){
	$id=isset($_GET['id'])?intval(getgpc("id","G")):"";
	switch($action){
		case "moveUp":
			$position=getgpc('position',"G");
			$objClozeQuestion->move_up($combo_questionId,$id,$position);
				
			api_redirect("question_update_cloze.php?pid=".$combo_questionId."&exerciseId=".$exerciseId);
				
			break;
			
		case "moveDown":
			$position=getgpc('position',"G");
			$objClozeQuestion->move_down($combo_questionId,$id,$position);				
			api_redirect("question_update_cloze.php?pid=".$combo_questionId."&exerciseId=".$exerciseId);								
			break;
			
		case "delete":
			if($objQuestionTmp = Question::read($id))
			{
				$objQuestionTmp->delete();
			}
			unset($objQuestionTmp);
			
			break;
	}
}

$objClozeMainQuestion = $objClozeQuestion->read ($combo_questionId);
if(is_object($objClozeMainQuestion)){
	$question_title=$objClozeMainQuestion->selectTitle();
	$description = $objClozeMainQuestion->selectDescription();
}else{
	api_redirect("question_base.php");
}

$htmlHeadXtra[]=Display::display_thickbox();

$interbreadcrumb[]=array("url" => "exercice.php","name" => get_lang('Exercices'));
$interbreadcrumb[]=array("url" => "question_base.php","name" => get_lang('QuestionPoolManagement'));

Display::display_header(get_lang("NewSubQuestion"));
//echo "<body><div>";


if(is_object($objClozeMainQuestion)){
	echo '<div class="actions">';
	if($exerciseId){
		echo Display :: return_icon('button_back.jpg', get_lang('Back')) . '<a href="admin.php?exerciseId='.$exerciseId.'">' . get_lang('Back') . '</a>';
	}else{
		echo Display :: return_icon('button_back.jpg', get_lang('Back')) . '<a href="question_base.php">' . get_lang('Back') . '</a>';
	}
	echo str_repeat("&nbsp;",2).Display::return_icon('save.png', get_lang('NewSubQuestion'), array('align'=>'absbottom')) .'<b>' . get_lang("NewSubQuestion") . '</b>:';
	echo str_repeat("&nbsp;",4).'<a class="thickbox" href="question_update.php?action=add&answerType='.UNIQUE_ANSWER.'&pid='.$combo_questionId.'&hideQN=1&KeepThis=true&TB_iframe=true&height=390&width=900&modal=true">'.get_lang("ClozeSubQuestion") . '</a>';
	echo '</div>';
}

//echo "<style>#description_box{width:80%;margin:0px auto;}</style>";
if(!empty($question_title)){
	echo '<div id="description_box" style="width:98%;margin:0px auto;padding-bottom:15px">'.stripslashes($question_title).'<br/>'.stripslashes($description).'</div>';
}




//$properties ["width"] = '90%';
$table_header [] = get_lang ( 'Question' );
//$table_header [] = get_lang ( 'Type' );
$table_header [] = get_lang ( 'DifficultyLevel' );
$table_header [] =get_lang('Weighting');
$table_header [] = get_lang ( 'Actions' );
Display::display_complex_table_header ( $properties, $table_header );

if($combo_questionId)
{
	$questionList=$objClozeMainQuestion->selectSubQuestionList();
	$nbrQuestions=count($questionList);
	$i=1;
	foreach($questionList as $pos=>$id)
	{
		$objQuestionTmp = Question :: read($id);

		if(is_object($objQuestionTmp)){
			$row = array ();
			$qid=$objQuestionTmp->selectId();
			$type=$objQuestionTmp->selectType();
				
			//$row [] ="$pos. ".api_trunc_str2(strip_tags($objQuestionTmp->selectTitle()),120);
			$objAnswer=new Answer($id);
			$answerList=$objAnswer->getAnswersList();
			foreach($answerList as $ii=>$ans){
				$title .= Question::$alpha[$ii+1].".&nbsp;".$ans['answer'].str_repeat("&nbsp;",6);
			}
			$row[]=$title;
				
			//$row[]=($_question_types[$type]);
			$row[]= $_question_level[$objQuestionTmp->selectLevel()];
			$row[]=round($objQuestionTmp->selectWeighting());

			$actionHtml="";
			$actionHtml .= '<a class="thickbox" href="question_update.php?action=edit&qid='.$qid.'&answerType='.$type.'&pid='.$combo_questionId.'&hideQN=1&KeepThis=true&TB_iframe=true&height=390&width=900&modal=true">'.Display::return_icon('edit.gif', get_lang('Modify')).'</a>&nbsp; ';

			$actionHtml .= '<a href="'.$_SERVER['PHP_SELF'].'?action=delete&id='.$qid.'"
						onclick="javascript:if(!confirm(\''.addslashes(htmlentities(get_lang('ConfirmYourChoice'), ENT_NOQUOTES, SYSTEM_CHARSET)).'\')) return false;">
						'.Display::return_icon('delete.gif', get_lang('Delete')).'</a>';
				
			if($i != 1){
				$actionHtml .= '<a href="'. $_SERVER['PHP_SELF'].'?action=moveUp&id='. $qid.'&position='.$pos.'&pid='.$combo_questionId.'">'.Display::return_icon('up.gif', get_lang('MoveUp'), array('align'=>'absmiddle')).'</a>';
			}
			if($i != $nbrQuestions){
				$actionHtml .='<a href="'. $_SERVER['PHP_SELF'].'?action=moveDown&id='.$qid.'&position='.$pos.'&pid='.$combo_questionId.'">'.Display::return_icon('down.gif', get_lang('MoveDown'), array('align'=>'absmiddle')).'</a> ';
			}
			$row[]=$actionHtml;

			Display::display_alternating_table_row ( $row, ($i-1) % 2 );

			$i++;
			unset($objQuestionTmp,$title);
		}
	}
}
Display::display_table_footer ();



Display::display_footer();