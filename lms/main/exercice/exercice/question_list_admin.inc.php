<?php
/*
 试题列表(被 admin.php 包含)
 */

if (! defined ( 'ALLOWED_TO_INCLUDE' )) exit ( "Access Denied !" );

//更改题目显示顺序
if (isset ( $_POST ['action'] ) && is_equal ( $_POST ["action"], "changeOrders" )) {
	$exerciseId = intval(getgpc ( "exerciseId", "P") );
	$questionOrders = getgpc ( "qo", "P" );
	$questionScores = getgpc ( "qs", "P" );
	if ($questionOrders && is_array ( $questionOrders )) {
		foreach ( $questionOrders as $question_id => $question_order ) {
			$sql_data = array ("question_order" => $question_order, 'question_score' => $questionScores [$question_id] );
			$sql_where = " exercice_id='" . escape ( $exerciseId ) . "' AND question_id='" . escape ( $question_id ) . "'";
			$sql = Database::sql_update ( $TBL_EXERCICE_QUESTION, $sql_data, $sql_where );
			api_sql_query ( $sql, __FILE__, __LINE__ );
		}
		
		if ($exerciseId) {
			api_session_unregister ( 'objExercise' );
			$objExercise = new Exercise ();
			$objExercise->read ( $exerciseId );
			api_session_register ( 'objExercise' );
		}
	}
	api_redirect ( 'admin.php?exerciseId=' . $exerciseId . '&qtype=' . getgpc ( 'qtype' ) );
}

$deleteQuestion = getgpc ( 'deleteQuestion' );
$exerciseId =intval(getgpc ( 'exerciseId') );
if ($deleteQuestion && $exerciseId) {
	$objExercise->removeQuestion ( $deleteQuestion );
	$qstnListByType = $objExercise->getQuestionList ( getgpc ( 'qtype' ) );
	if (empty ( $qstnListByType )) {
		api_redirect ( 'admin.php?exerciseId=' . $exerciseId );
	} else {
		api_redirect ( 'admin.php?exerciseId=' . $exerciseId . '&qtype=' . getgpc ( 'qtype' ) );
	}
}

if ($exerciseId) {
	$objExercise = new Exercise ();
	$objExercise->read ( $exerciseId );
} else {
	api_redirect ( 'exercise_admin.php' );
}
echo Display::display_thickbox ();
echo import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
echo '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");
$("#save_score").click(function(){
		$(".qs").each(function() { 
			$(this).val($("#same_score").val());
		}); 
	});
});</script>';

$html = '<div id="demo" class="yui-navset">';
$html .= '<ul class="yui-nav">';
$html .= '<li><a href="exercise_admin.php?exerciseId=' . $exerciseId . '"><em>1. ' . get_lang ( 'ModifyEx' ) . '</em></a></li>';
$html .= '<li  class="selected"><a href="admin.php?exerciseId=' . $exerciseId . '"><em>2. ' . get_lang ( 'QuestionList' ) . '</em></a></li>';
$html .= '<li><a href="../exam/manage/have_arranged.php?exam_id=' . $exerciseId . '"><em>3. ' . get_lang ( 'ArrageExaminees' ) . '</em></a></li>';
$html .= '</ul>';
$html .= '<div class="yui-content"><div id="tab1">';
echo $html;

if ($objExercise->active == 0) { //没有发布时
	echo '<div class="add_questions" style="float: left; text-align:left;margin-bottom:10px">';
	echo '<div style="float:right">';
	echo link_button ( 'database.gif', 'ChoseFromQuestionPool', api_get_path ( WEB_CODE_PATH ) . 'exercice/question_pool.php?fromExercise=' . $exerciseId, '90%', '92%' );
	echo '</div></div><div style="clear:both"></div>';
}

//顶部题型Tab
unset ( $_question_types [0] );
unset ( $myTools );
$quiz_question_type = $objExercise->getQuizQuestionTypes ( $exerciseId );
$quiz_qt = array_keys ( $quiz_question_type ); //var_dump($quiz_question_type);


$strActionType = (isset ( $_REQUEST ['qtype'] ) && $_REQUEST ["qtype"] ? trim ( $_REQUEST ['qtype'] ) : $quiz_qt [0]);
$html = '<div id="demo" class="yui-navset" style="padding-top:10px">';
$html .= '<ul class="yui-nav">';
foreach ( $quiz_question_type as $qtype => $qtcount ) {
	$strClass = ($strActionType == $qtype ? 'class="selected"' : '');
	$html .= '<li  ' . $strClass . '><a href="admin.php?exerciseId=' . $exerciseId . '&qtype=' . $qtype . '"><em>' . $_question_types [$qtype] . "(" . $qtcount [0] . ')</em></a></li>';
}
$html .= '</ul>';
$html .= '<div class="yui-content"><div id="tab1">';
if ($quiz_question_type) echo $html;

echo form_open ( $_SERVER ['PHP_SELF'], 'method="post"', array ("exerciseId" => $exerciseId, "action" => "changeOrders", "qtype" => $strActionType ) );

if ($nbrQuestions) {
	//$questionList = $objExercise->selectQuestionList ();
	$questionList = $objExercise->getQuestionList ( $strActionType );
	//var_dump($questionList);
	$table_header [] = array (get_lang ( 'DisplayOrder' ) );
	$table_header [] = array (get_lang ( 'Weighting' ) );
	$table_header [] = array (get_lang ( 'Question' ) );
	$table_header [] = array (get_lang ( 'Type' ) );
	$table_header [] = array (get_lang ( 'DifficultyLevel' ) );
	$table_header [] = array (get_lang ( 'Weighting' ) );
	//$table_header [] = array (get_lang ( 'Course' ) );
	if ($objExercise->active == 0) $table_header [] = array (get_lang ( 'Actions' ) );
	
	$i = 1;
	$totalWeighting = 0;
	foreach ( $questionList as $pos => $id ) {
		//var_dump($id);
		$objQuestionTmp = Question::read ( $pos );
		if (is_object ( $objQuestionTmp )) {
			$question_order = $id ["question_order"];
			$type = $objQuestionTmp->selectType ();
			if ($type == $strActionType) {
				$row = array ();
				//显示顺序
				if ($objExercise->active == 0) {
					$row [] = '<div class="edit" id="' . $pos . '" name=""><input type="text" value="' . $question_order . '"
					class="inputText" name="qo[' . $pos . ']" style="width:30px;text-align:right;" onFocus="this.select();"/></div>';
				} else {
					$row [] = $question_order;
				}
				
				//分值
				$tmp_weight = ($id ['question_score'] ? $id ['question_score'] : $objQuestionTmp->weighting);
				if ($objExercise->active == 0) {
					$row [] = '<div class="edit" id="qs' . $pos . '" ><input type="text" value="' . ($tmp_weight) . '"
					class="inputText qs" name="qs[' . $pos . ']" style="width:30px;text-align:right;" onFocus="this.select();" rel="qs"/></div>';
				} else {
					$row [] = $tmp_weight;
				}
				
				$row [] = $i . '. ' . api_trunc_str2 ( strip_tags ( $objQuestionTmp->selectTitle () ), 50 );
				//$row[]=eval(' get_lang('.get_class($objQuestionTmp).'::$explanationLangVar);');
				

				$question_type = $_question_types [$type];
				if ($type == COMBO_QUESTION) $question_type .= "&nbsp;(" . Question::get_combo_sub_question_count ( $pos ) . ")";
				$row [] = $question_type;
				$row [] = $_question_level [$objQuestionTmp->selectLevel ()];
				$totalWeighting += $objQuestionTmp->selectWeighting ();
				$row [] = round ( $objQuestionTmp->selectWeighting (), 1 );
				$actionHtml = "";
				if (is_object ( $objQuestionTmp ) && $objQuestionTmp->type == COMBO_QUESTION) { //combo_question_list_admin.php?id=
					$actionHtml .= '<a href="question_update_combo.php?pid=' . $pos . '&exerciseId=' . $exerciseId . '">' . Display::return_icon ( 'wizard_small.gif', get_lang ( 'Build' ), array ('align' => 'absmiddle' ) );
				}
				
				if ($objExercise->active == 0) {
					$href = 'admin.php?deleteQuestion=' . $pos . '&exerciseId=' . $exerciseId . '&qtype=' . $type;
					$actionHtml .= '&nbsp;&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', $href );
				}
				
				if ($actionHtml) $row [] = $actionHtml;
				
				$table_content [] = $row;
				$i ++;
			}
		}
		unset ( $objQuestionTmp );
	}
	//var_dump($table_content);
	echo Display::display_table ( $table_header, $table_content );
	$totalWeighting = Exercise::get_quiz_total_score ( $exerciseId );
	if ($objExercise->active == 0 && $nbrQuestions) {
		echo '<div style="margin:4px 0; float:left;"><button class="save" type="submit" name="save_question"
		 id="save_question">' . get_lang ( 'SaveQuestoinOrder' ) . '</button></div>';
		echo '<div style="margin:4px 10px; float:left;">', '统一设置本题型分值为', '<input type="text" id="same_score" value="1" onfocus="this.select();" class="inputText" style="width:40px">', '<input class="cancel" type="button" name="save_score"
		 id="save_score" value="' . get_lang ( 'Setting' ) . '" /></div>';
	}
	echo '<div  style="margin-right:20px; float:right;">' . get_lang ( "QuizTotalScore" ) . ": " . $totalWeighting . '</div>';
}
echo form_close ();
echo '</div></div></div>';
