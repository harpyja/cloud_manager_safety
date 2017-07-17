<?php
/**
 ==============================================================================
 * @package zllms.admin
 ==============================================================================
 */
include_once ('exercise.class.php');
include_once ('question.class.php');
include_once ('answer.class.php');

$language_file = 'exercice';
include_once ('../inc/global.inc.php');
include_once ('exercise.lib.php');

define ( "QUESTION_OPTION_SPLIT_CHAR", "|" );
set_time_limit ( 0 );
include (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include (api_get_path ( LIBRARY_PATH ) . 'export.lib.inc.php');

$alpha = array ('', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z' );

$tool_name = get_lang ( 'Export' );
$interbreadcrumb [] = array ("url" => 'exercice.php', "name" => get_lang ( 'Exercices' ) );
$interbreadcrumb [] = array ("url" => 'question_base.php', "name" => get_lang ( 'QuestionPool' ) );

$form = new FormValidator ( 'export_questions' );
$form->addElement ( 'hidden', 'type', getgpc ( 'type', 'G' ) );
// $form->addElement ( 'header', 'header', get_lang ( 'ExportQuestions' ) );
$form->addElement ( 'hidden', 'file_type', 'xls' );

$group = array ();
$group [] = & HTML_QuickForm::createElement ( 'checkbox', 'by_pool', null, get_lang ( 'ByPool' ), array ('id' => 'by_pool' ) );
$group [] = & HTML_QuickForm::createElement ( 'checkbox', 'by_course', null, get_lang ( 'ByCourse' ), array ('id' => 'by_course' ) );
$form->addGroup ( $group, null, get_lang ( 'FilterCondition' ), '&nbsp;' );
$defaults ['status'] = STUDENT;

$sql = "SELECT id,pool_name FROM " . $tbl_exam_question_pool . "  ORDER BY display_order ASC";
$all_pools = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
$form->addElement ( 'select', 'pool_id', get_lang ( "QuestionPool" ), $all_pools );
$defaults ['pool_id'] = intval(getgpc ( 'pool_id' ));

$sql = "SELECT code,title FROM " . $tbl_course . "";
$all_courses = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
$form->addElement ( 'select', 'cc', get_lang ( "Courses" ), $all_courses );

$list_arr = array (UNIQUE_ANSWER => get_lang ( "UniqueSelect" ), MULTIPLE_ANSWER => get_lang ( "MultipleSelect" ), TRUE_FALSE_ANSWER => get_lang ( "TrueFalseAnswer" ) );
if ($_configuration ['enable_question_freeanswer']) $list_arr [FREE_ANSWER] = get_lang ( "FreeAnswer" );
if ($_configuration ['enable_question_fillblanks']) $list_arr [FILL_IN_BLANKS] = get_lang ( "FillBlanks" );
$ams = & $form->addElement ( 'advmultiselect', "question_type", get_lang ( 'ExportQuestionType' ), $list_arr, array ('size' => 5, 'class' => 'pool', 'style' => 'width:150px;' ) );
//$ams->setLabel(array($label_upload_filetype, get_lang('AvailableFileType'),'', get_lang('SelectedFileType')));
$ams->setButtonAttributes ( 'add', array ('value' => '>>', 'class' => 'inputSubmitShort' ) );
$ams->setButtonAttributes ( 'remove', array ('value' => '<<', 'class' => 'inputSubmitShort' ) );
include (api_get_path ( INCLUDE_PATH ) . "conf/templates.php");
$template = $template ["html"] ["advmultiselect"];
$ams->setElementTemplate ( $template );
$form->addRule ( 'question_type', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$group = array ();
$group [] = $form->createElement ( 'submit', 'submit', get_lang ( 'Ok' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ( $defaults );

Display::setTemplateBorder ( $form, '98%' );
//$form->add_progress_bar();
if ($form->validate ()) {
	$export = $form->exportValues ();
	$export = $form->getSubmitValues ();
	$file_type = $export ['file_type'];
	$question_type = $export ['question_type'];
	//var_dump($question_type);exit;
	$cc = trim ( $export ['cc'] );
	$pool_id = trim ( $export ['pool_id'] );
	$data = array ();
	$data [] = array ('QuestionType', "Difficulty", "Score", 'QuestionTitle', "Options", "Answer", "QuestionAnalysis", "QuestionTrunkLineNumber" );
	$filename = 'ExportQuestions_' . api_get_course_code () . '_' . date ( 'YmdHis' ); //导出文件名
	//description AS QuestionDescription,
	//t1.type IN (".UNIQUE_ANSWER.",".MULTIPLE_ANSWER.",".FILL_IN_BLANKS.",".FREE_ANSWER.",".TRUE_FALSE_ANSWER.")";
	$sql = "SELECT t1.type AS QuestionType, level AS Difficulty, ponderation AS Score,question AS QuestionTitle,
				 t2.Options,'' AS Answer, t1.comment AS QuestionAnalysis,'' AS QuestionTrunkLineNumber,t1.id AS question_id,t1.pid FROM $TBL_QUESTIONS AS t1
  				LEFT JOIN (SELECT GROUP_CONCAT(answer ORDER BY id SEPARATOR '" . QUESTION_OPTION_SPLIT_CHAR . "') AS Options,question_id
             	FROM $TBL_REPONSES AS t GROUP BY question_id) AS t2	ON t1.id = t2.question_id WHERE t1.pid=0 ";
	$sql .= " AND " . Database::create_in ( $question_type, "type" );
	if ($export ['by_course'] && $cc) $sql .= " AND t1.cc='" . $cc . "' ";
	if ($export ['by_pool'] && $pool_id) $sql .= " AND t1.pool_id='" . $pool_id . "' ";
	$sql .= "  ORDER BY question_code";
	//echo $sql;exit;
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$questions = array ();
	$line = 2;
	while ( $row = Database::fetch_array ( $res, 'ASSOC' ) ) {
		if ($row ['QuestionType'] == UNIQUE_ANSWER) { //单选
			$row ['QuestionType'] = get_lang ( "UniqueSelect" );
			$row ["Difficulty"] = $row ["Difficulty"];
			$row ["Score"] = $row ["Score"];
			$row ["QuestionTitle"] = $row ["QuestionTitle"];
			$row ["Options"] = $row ["Options"];
			$sql = "SELECT id FROM " . $TBL_REPONSES . " WHERE question_id='" . $row ['question_id'] . "' AND correct=1";
			$anid = Database::get_scalar_value ( $sql );
			$row ['Answer'] = $alpha [$anid];
			$row ['QuestionAnalysis'] = $row ['QuestionAnalysis'];
			$row ["QuestionTrunkLineNumber"] = "";
			unset ( $row ["question_id"], $row ["pid"] );
			$questions [] = $row;
			$line ++;
		}
		if ($row ['QuestionType'] == TRUE_FALSE_ANSWER) { //判断题
			$row ['QuestionType'] = get_lang ( "TrueFalseAnswer" );
			$row ["Difficulty"] = $row ["Difficulty"];
			$row ["Score"] = $row ["Score"];
			$row ["QuestionTitle"] = $row ["QuestionTitle"];
			$row ['Options'] = "";
			$sql = "SELECT answer FROM " . $TBL_REPONSES . " WHERE question_id='" . $row ['question_id'] . "' AND correct=1";
			$anid = Database::get_scalar_value ( $sql );
			$row ['Answer'] = $anid;
			$row ['QuestionAnalysis'] = $row ['QuestionAnalysis'];
			$row ["QuestionTrunkLineNumber"] = "";
			unset ( $row ["question_id"], $row ["pid"] );
			$questions [] = $row;
			$line ++;
		} elseif ($row ['QuestionType'] == MULTIPLE_ANSWER) { //多选
			$row ['QuestionType'] = get_lang ( "MultipleSelect" );
			$row ["Difficulty"] = $row ["Difficulty"];
			$row ["Score"] = $row ["Score"];
			$row ["QuestionTitle"] = $row ["QuestionTitle"];
			$row ["Options"] = $row ["Options"];
			$sql = "SELECT id FROM " . $TBL_REPONSES . " WHERE question_id='" . $row ['question_id'] . "' AND correct=1";
			$res2 = api_sql_query ( $sql, __FILE__, __LINE__ );
			while ( $row2 = Database::fetch_array ( $res2, 'ASSOC' ) ) {
				$row ['Answer'] .= $alpha [$row2 ['id']] . ";";
			}
			Database::free_result ( $res2 );
			unset ( $row2 );
			$row ['QuestionAnalysis'] = $row ['QuestionAnalysis'];
			$row ["QuestionTrunkLineNumber"] = "";
			unset ( $row ["question_id"], $row ["pid"] );
			$questions [] = $row;
			$line ++;
		} elseif ($row ['QuestionType'] == FILL_IN_BLANKS) { //填空
			if ($_configuration ['enable_question_fillblanks']) {
				$fb_answer = $row ['Options'];
				$row ['QuestionType'] = get_lang ( "FillBlanks" );
				$row ["Difficulty"] = $row ["Difficulty"];
				$row ["Score"] = $row ["Score"];
				$row ["QuestionTitle"] = $row ["QuestionTitle"];
				$row ['Options'] = "";
				
				$fb_answers = explode ( "::", $fb_answer );
				$row ['Answer'] = $fb_answers [0]; //答案
				$row ['QuestionAnalysis'] = $row ['QuestionAnalysis'];
				$row ["QuestionTrunkLineNumber"] = "";
				unset ( $row ["question_id"], $row ["pid"] );
				$questions [] = $row;
				$line ++;
			}
		} elseif ($row ['QuestionType'] == FREE_ANSWER) { //简答
			if ($_configuration ['enable_question_freeanswer']) {
				$row ['QuestionType'] = get_lang ( "FreeAnswer" );
				$row ["Difficulty"] = $row ["Difficulty"];
				$row ["Score"] = $row ["Score"];
				$row ["QuestionTitle"] = $row ["QuestionTitle"];
				$row ['Options'] = "";
				$sql = "SELECT answer FROM " . $TBL_REPONSES . " WHERE question_id='" . $row ['question_id'] . "'";
				$row ['Answer'] = Database::get_scalar_value ( $sql );
				$row ['QuestionAnalysis'] = $row ['QuestionAnalysis'];
				$row ["QuestionTrunkLineNumber"] = "";
				unset ( $row ["question_id"], $row ["pid"] );
				$questions [] = $row;
				$line ++;
			}
		} elseif ($row ["QuestionType"] == CLOZE_QUESTION) {
			if ($_configuration ['enable_question_cloze']) {
				$row ['QuestionType'] = get_lang ( "ComboQuestion" );
				$row ["Difficulty"] = $row ["Difficulty"];
				$row ["Score"] = $row ["Score"];
				$row ["QuestionTitle"] = $row ["QuestionTitle"];
				$row ['Options'] = "";
				$row ['Answer'] = "";
				$row ['QuestionAnalysis'] = $row ['QuestionAnalysis'];
				$row ["QuestionTrunkLineNumber"] = "";
				$trunk_question_id = $row ['question_id'];
				unset ( $row ["question_id"], $row ["pid"] );
				$questions [] = $row;
				$line ++;
				
				$sql1 = "SELECT t1.type AS QuestionType, level AS Difficulty, ponderation AS Score,question AS QuestionTitle,
				 t2.Options,'' AS Answer, t1.comment AS QuestionAnalysis,'' AS QuestionTrunkLineNumber,t1.id AS question_id FROM $TBL_QUESTIONS AS t1
  				LEFT JOIN (SELECT GROUP_CONCAT(answer ORDER BY id SEPARATOR '" . QUESTION_OPTION_SPLIT_CHAR . "') AS Options,question_id
             	FROM $TBL_REPONSES  GROUP BY question_id) AS t2	ON t1.id = t2.question_id WHERE t1.pid='" . $trunk_question_id . "'";
				if ($export ['by_course'] && $cc) $sql .= " AND t1.cc='" . $cc . "' ";
				if ($export ['by_pool'] && $pool_id) $sql .= " AND t1.pool_id='" . $pool_id . "' ";
				$res1 = api_sql_query ( $sql1, __FILE__, __LINE__ );
				$line2 = 1;
				while ( $row1 = Database::fetch_array ( $res1, 'ASSOC' ) ) {
					if ($row1 ['QuestionType'] == UNIQUE_ANSWER) { //单选
						$row1 ['QuestionType'] = get_lang ( "UniqueSelect" );
						$row1 ["Difficulty"] = $row1 ["Difficulty"];
						$row1 ["Score"] = $row1 ["Score"];
						$row1 ["QuestionTitle"] = $row1 ["QuestionTitle"];
						$row1 ["Options"] = $row1 ["Options"];
						$sql2 = "SELECT id FROM " . $TBL_REPONSES . " WHERE question_id='" . $row1 ['question_id'] . "' AND correct=1";
						$anid = Database::get_scalar_value ( $sql2 );
						$row1 ['Answer'] = $alpha [$anid];
						$row1 ['QuestionAnalysis'] = $row1 ['QuestionAnalysis'];
						$row1 ["QuestionTrunkLineNumber"] = strval ( $line - $line2 );
						unset ( $row1 ["question_id"], $row1 ["pid"] );
						$questions [] = $row1;
						$line ++;
						$line2 ++;
					}
				}
			}
		} elseif ($row ["QuestionType"] == COMBO_QUESTION) {
			if ($_configuration ['enable_question_combo']) {
				$row ['QuestionType'] = get_lang ( "ComboQuestion" );
				$row ["Difficulty"] = $row ["Difficulty"];
				$row ["Score"] = $row ["Score"];
				$row ["QuestionTitle"] = $row ["QuestionTitle"];
				$row ['Options'] = "";
				$row ['Answer'] = "";
				$row ['QuestionAnalysis'] = $row ['QuestionAnalysis'];
				$row ["QuestionTrunkLineNumber"] = "";
				$trunk_question_id = $row ['question_id'];
				unset ( $row ["question_id"], $row ["pid"] );
				$questions [] = $row;
				$line ++;
				
				$sql1 = "SELECT t1.type AS QuestionType, level AS Difficulty, ponderation AS Score,question AS QuestionTitle,
				 t2.Options,'' AS Answer, t1.comment AS QuestionAnalysis,'' AS QuestionTrunkLineNumber,t1.id AS question_id FROM $TBL_QUESTIONS AS t1
  				LEFT JOIN (SELECT GROUP_CONCAT(answer ORDER BY id SEPARATOR '" . QUESTION_OPTION_SPLIT_CHAR . "') AS Options,question_id
             	FROM $TBL_REPONSES  GROUP BY question_id) AS t2	ON t1.id = t2.question_id WHERE t1.pid='" . $trunk_question_id . "'";
				if ($export ['by_course'] && $cc) $sql .= " AND t1.cc='" . $cc . "' ";
				if ($export ['by_pool'] && $pool_id) $sql .= " AND t1.pool_id='" . $pool_id . "' ";
				$res1 = api_sql_query ( $sql1, __FILE__, __LINE__ );
				$line2 = 1;
				while ( $row1 = Database::fetch_array ( $res1, 'ASSOC' ) ) {
					if ($row1 ['QuestionType'] == UNIQUE_ANSWER) { //单选
						$row1 ['QuestionType'] = get_lang ( "UniqueSelect" );
						$row1 ["Difficulty"] = $row1 ["Difficulty"];
						$row1 ["Score"] = $row1 ["Score"];
						$row1 ["QuestionTitle"] = $row1 ["QuestionTitle"];
						$row1 ["Options"] = $row1 ["Options"];
						$sql2 = "SELECT id FROM " . $TBL_REPONSES . " WHERE question_id='" . $row1 ['question_id'] . "' AND correct=1";
						$anid = Database::get_scalar_value ( $sql2 );
						$row1 ['Answer'] = $alpha [$anid];
						$row1 ['QuestionAnalysis'] = $row1 ['QuestionAnalysis'];
						$row1 ["QuestionTrunkLineNumber"] = strval ( $line - $line2 );
						unset ( $row1 ["question_id"], $row1 ["pid"] );
						$questions [] = $row1;
						$line ++;
						$line2 ++;
					}
					if ($row1 ['QuestionType'] == TRUE_FALSE_ANSWER) { //判断题
						$row1 ['QuestionType'] = get_lang ( "TrueFalseAnswer" );
						$row1 ["Difficulty"] = $row1 ["Difficulty"];
						$row1 ["Score"] = $row1 ["Score"];
						$row1 ["QuestionTitle"] = $row1 ["QuestionTitle"];
						$row1 ['Options'] = "";
						$sql = "SELECT answer FROM " . $TBL_REPONSES . " WHERE question_id='" . $row ['question_id'] . "' AND correct=1";
						$anid = Database::get_scalar_value ( $sql );
						$row1 ['Answer'] = $anid;
						$row1 ['QuestionAnalysis'] = $row1 ['QuestionAnalysis'];
						$row1 ["QuestionTrunkLineNumber"] = strval ( $line - $line2 );
						unset ( $row1 ["question_id"], $row1 ["pid"] );
						$questions [] = $row1;
						$line ++;
						$line2 ++;
					} elseif ($row ['QuestionType'] == MULTIPLE_ANSWER) { //多选
						$row1 ['QuestionType'] = get_lang ( "MultipleSelect" );
						$row1 ["Difficulty"] = $row1 ["Difficulty"];
						$row1 ["Score"] = $row1 ["Score"];
						$row1 ["QuestionTitle"] = $row1 ["QuestionTitle"];
						$row1 ["Options"] = $row1 ["Options"];
						$sql2 = "SELECT id FROM " . $TBL_REPONSES . " WHERE question_id='" . $row ['question_id'] . "' AND correct=1";
						$res2 = api_sql_query ( $sql2, __FILE__, __LINE__ );
						while ( $row2 = Database::fetch_array ( $res2, 'ASSOC' ) ) {
							$row ['Answer'] .= $alpha [$row2 ['id']] . ";";
						}
						Database::free_result ( $res2 );
						unset ( $row2 );
						$row1 ['QuestionAnalysis'] = $row1 ['QuestionAnalysis'];
						$row1 ["QuestionTrunkLineNumber"] = strval ( $line - $line2 );
						unset ( $row1 ["question_id"], $row1 ["pid"] );
						$questions [] = $row1;
						$line ++;
						$line2 ++;
					}
				}
			}
		}
	}
	
	//}
	

	foreach ( $questions as $question ) {
		if ($file_type == 'csv') {
			$export_encoding = 'GBK';
			$question ['QuestionTitle'] = mb_convert_encoding ( $question ['QuestionTitle'], $export_encoding, SYSTEM_CHARSET );
			$question ['QuestionAnalysis'] = mb_convert_encoding ( $question ['QuestionAnalysis'], $export_encoding, SYSTEM_CHARSET );
		}
		$data [] = $question;
	}
	
	//var_dump($data);exit;
	switch ($file_type) {
		case 'csv' :
			Export::export_table_data ( $data, $filename, 'csv' );
			break;
		case 'xls' :
			Export::export_table_data ( $data, $filename, 'xls', false );
			break;
	}
	tb_close ();
}

$htmlHeadXtra [] = $ams->getElementJs ( false );

$htmlHeadXtra [] = '<script type="text/javascript">
	$(document).ready( function() {
		$("tr.containerBody:eq(1)").hide();
		$("tr.containerBody:eq(2)").hide();
	
		$("#by_pool").click(function(){
			if($("#by_pool").attr("checked")){
				$("tr.containerBody:eq(1)").show();
			}else{
				$("tr.containerBody:eq(1)").hide();
			}
		});
		
		$("#by_course").click(function(){
				if($("#by_course").attr("checked")){
				$("tr.containerBody:eq(2)").show();
			}else{
				$("tr.containerBody:eq(2)").hide();
			}
		});
	});</script>';

Display::display_header ( $tool_name, FALSE );

$form->display ();

Display::display_footer ();
