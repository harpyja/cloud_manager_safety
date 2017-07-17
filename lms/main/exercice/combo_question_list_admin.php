<?php
/*
 组合（关联）单选题列表
 */

include_once('exercise.class.php');
include_once('question.class.php');
include_once('answer.class.php');


$language_file='exercice';
include("../inc/global.inc.php");

$exerciseId = Database::escape_string($_GET['exerciseId']);

// moves a question up in the list
if(isset($_GET['moveUp']))
{
	Question::changePosition(intval($_GET['moveUp']),'UP');
        $get_id=  intval ( getgpc('id'));
	header("Location: combo_question_list_admin.php?id=".$get_id."&exerciseId=".$exerciseId);
	exit;
}

// moves a question down in the list
if(isset($_GET['moveDown']))
{        $get_id= intval (  getgpc('id'));
	Question::changePosition(intval($_GET['moveDown']),'DOWN');
	header("Location: combo_question_list_admin.php?id=".$get_id."&exerciseId=".$exerciseId);
	exit;
}


$htmlHeadXtra[]=Display::display_thickbox();

$interbreadcrumb[]=array("url" => "exercice.php","name" => get_lang('Exercices'));
$nameTools=get_lang('QuestionManagement');
Display::display_header($nameTools,"Exercise");

$objComboMainQuestion = Question::read ($_GET['id']);
//var_dump($objComboMainQuestion);
if(is_object($objComboMainQuestion)){
	$question_title=$objComboMainQuestion->selectTitle();
	$description = $objComboMainQuestion->selectDescription();
}


$objExercise = $_SESSION['objExercise'];
if(!is_object($objExercise))
{
	// construction of the Exercise object
	$objExercise=new Exercise();

	// creation of a new exercise if wrong or not specified exercise ID
	if($exerciseId)
	{
		$objExercise->read($exerciseId);
	}

	// saves the object into the session
	api_session_register('objExercise');
}


echo "<style>#description_box{width:80%;margin:0px auto;}</style>";
if(!empty($description))
{
	echo '<div id="description_box">'.stripslashes($question_title).'<br/>'.stripslashes($description).'</div>';
}
echo '<br />';


// deletes a question from the exercise (not from the data base)
if($deleteQuestion)
{

	// if the question exists
	if($objQuestionTmp = Question::read($deleteQuestion))
	{
		$objQuestionTmp->delete($exerciseId);

		// if the question has been removed from the exercise
		if($objExercise->removeFromList($deleteQuestion))
		{
			$nbrQuestions--;
		}
	}

	// destruction of the Question object
	unset($objQuestionTmp);
}

//显示新增题菜单

?>
<div id="answer_type_<?=COMBO_UNIQUE_ANSWER ?>"
	style="float: left; width: 120px; text-align: center"><a
	class="thickbox"
	href="<?=api_get_path(WEB_CODE_PATH) ?>exercice/admin.php?newQuestion=yes&answerType=1&pid=<?php  
            $get_id=  intval ( getgpc('id'));
        echo $get_id;?>&exerciseId=<?= is_object($objExercise)?$objExercise->id:"" ?>&KeepThis=true&TB_iframe=true&height=390&width=900&modal=true">
<div><?=Display::return_icon("mcua.gif") ?></div>
<div><?=get_lang("Add") ?></div>
</a></div>
<script language="JavaScript" type="text/JavaScript">


function show_hide(ID)
{
	if(G('tr'+ID).style.display=='none')
	{
	   G('tr'+ID).style.display='block'
	   G('img'+ID).src='<?=api_get_path(WEB_IMG_PATH)?>1.gif';
	   return;
	}
	G('tr'+ID).style.display='none';
	G('img'+ID).src='<?=api_get_path(WEB_IMG_PATH)?>0.gif';	
}

</script>

<table width="100%" >
	<tr>
		<td align="center">
		<table width="95%" >
			<tr>
				<td>
				<table class="data_table">
					<tr class="row_odd" bgcolor='#e6e6e6'>
						<th width="70%"><b><?php echo get_lang('Question'); ?></b></th>
						<th width="10%"><b><?php echo get_lang('Type');?></b></th>
						<th width="5%"><b><?php echo get_lang('Weighting');?></b></th>
						<th width="15%"><b><?php echo get_lang('Actions');?></b></th>
					</tr>

					<?php
					if($_GET['id'])
					{
						$questionList=$objComboMainQuestion->selectSubQuestionList();

						$i=1;
						foreach($questionList as $position=>$id)
						{
							$objQuestionTmp = Question :: read($id);
							?>

					<tr
					<?php if($i%2==0) echo 'class="row_odd"'; else echo 'class="row_even"'; ?>>
						<td align="left"><?php echo Display::return_icon('0.gif','点击展开/关闭题目描述',
						array('onclick'=>"show_hide(".$id.");",'style'=>'cursor:hand;','id'=>'img'.$id)); ?>
						<?php echo "$i. ".(is_object($objQuestionTmp)?$objQuestionTmp->selectTitle():""); ?></td>
						<td align="left"><?php eval('echo get_lang('.get_class($objQuestionTmp).'::$explanationLangVar);'); ?></td>
						<td><?=$objQuestionTmp->selectWeighting() ?></td>
						<td align="left"><a class="thickbox"
							href="<?=api_get_path(WEB_CODE_PATH) ?>exercice/admin.php?editQuestion=<?php echo $id; ?>&pid=<?=is_object($objQuestionTmp)?$objQuestionTmp->pid:'0' ?>&KeepThis=true&TB_iframe=true&height=390&width=900&modal=true">
							<?php echo Display::return_icon('edit.gif', get_lang('Modify'), array('align'=>'absmiddle')); ?>
						</a> <a
							href="<?php echo $_SERVER['PHP_SELF']; ?>?deleteQuestion=<?php echo $id; ?>"
							onclick="javascript:if(!confirm('<?php echo addslashes(htmlentities(get_lang('ConfirmYourChoice'), ENT_NOQUOTES, SYSTEM_CHARSET)); ?>')) return false;">
							<?php echo Display::return_icon('delete.gif', get_lang('Delete'), array('align'=>'absmiddle')); ?>
						</a> <?php
						if($i != 1)
						{
							?> <a
                                                            href="<?php echo $_SERVER['PHP_SELF']; ?>?moveUp=<?=$id?>&id=<?=  intval ( getgpc('id')) ?>&exerciseId=<?=$exerciseId ?>">
							<?php echo Display::return_icon('up.gif', get_lang('MoveUp'), array('align'=>'absmiddle')); ?>
						</a> <?php
						}

						if($i != sizeof($questionList))
						{
							?> <a
                                                            href="<?php echo $_SERVER['PHP_SELF']; ?>?moveDown=<?php echo $id; ?>&id=<?=  intval ( getgpc('id')) ?>&exerciseId=<?=$exerciseId ?>">
							<?php echo Display::return_icon('down.gif', get_lang('MoveDown'), array('align'=>'absmiddle')); ?>
						</a> <?php			}		?></td>

						<?php
						$i++;


						?>
					</tr>
					<?php echo "<tr id=\"tr".$id."\" style=\"display:none;\"><td colspan=\"6\" align=left>".(is_object($objQuestionTmp)?$objQuestionTmp->selectDescription():"")."</td></tr>"; ?>
					<?php //unset($objQuestionTmp);
					$objQuestionTmp=NULL;
						}
					}

					if(!$i)
					{

						?>
					<tr>
						<td colspan="3" class="row_odd"><i><?php echo get_lang('NoQuestion'); ?></i></td>
					</tr>
					<?php } ?>

				</table>

				</td>
			</tr>
		</table>
		</td>
	</tr>
</table>

					<?php Display::display_footer();?>