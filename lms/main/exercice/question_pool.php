<?php
/*
 题库
 */
include_once ('exercise.class.php');
include_once ('question.class.php');
include_once ('answer.class.php');

$language_file = 'exercice';
include_once ('../inc/global.inc.php');
include_once ('exercise.lib.php');

$exerciseId = intval ( getgpc ( 'exerciseId' ));
$objExercise = new Exercise ();
$objExercise->read ( $exerciseId );
//api_protect_quiz_script ( $objExercise->exam_manager );

$delete = getgpc ( 'delete' );
$recup = getgpc ( 'recup' );
$fromExercise = getgpc ( 'fromExercise' );

$is_course_exam = (is_object ( $objExercise ) && $objExercise ? ($objExercise->type == 2 && $objExercise->cc ? TRUE : FALSE) : false);

$documentPath = api_get_path ( SYS_COURSE_PATH ) . api_get_course_code () . '/document';
$picturePath = $documentPath . '/images';

if ($delete) { //删除
	if ($objQuestionTmp = Question::read ( $delete )) {
		$objQuestionTmp->delete ();
	}
	unset ( $objQuestionTmp );
} elseif ($recup && $fromExercise) { //增加到测验当中, gets an existing question and copies it into a new exercise
	if ($objQuestionTmp = Question::read ( $recup )) {
		$objQuestionTmp->addToList ( $fromExercise );
	}
	
	unset ( $objQuestionTmp );
	$objExercise->addToList ( $recup );
	
	api_session_register ( 'objExercise' );
	
	api_redirect ( "admin.php" );
}

if (isset ( $_POST ['action'] )) {
	switch ($_POST ['action']) {
		case 'batch_reuse' : //批量使用题库中试题
			$number_of_selected_items = count ( $_POST ['id'] );
			$number_of_deleted_items = 0;
			foreach ( $_POST ['id'] as $index => $item_id ) {
				if ($objQuestionTmp = Question::read ( $item_id )) {
					$objQuestionTmp->addToList ( $fromExercise );
					$number_of_deleted_items ++;
				}
				unset ( $objQuestionTmp );
				
				if (is_object ( $objExercise )) $objExercise->addToList ( $item_id );
			
			}
			api_session_register ( 'objExercise' );
			if (is_object ( $objExercise )) $exerciseId = $objExercise->selectId ();
			tb_close ();
			break;
		case 'batch_delete' : //批量删除
			$number_of_selected_items = count ( $_POST ['id'] );
			$number_of_deleted_items = 0;
			foreach ( $_POST ['id'] as $index => $item_id ) {
				if ($objQuestionTmp = Question::read ( $item_id )) {
					$objQuestionTmp->delete ();
					$number_of_deleted_items ++;
				}
				unset ( $objQuestionTmp );
			}
			tb_close ();
			break;
	}
}

function get_sqlwhere() {
	global $fromExercise, $TBL_EXERCICE_QUESTION;
	$sql_where = " AND t1.id NOT IN (SELECT question_id FROM $TBL_EXERCICE_QUESTION AS t2 WHERE exercice_id=" . Database::escape ( $fromExercise ) . ") ";
	
	if (isset ( $_GET ['type'] ) && ! empty ( $_GET ['type'] )) {
		$sql_where .= " AND t1.type=" . Database::escape ( getgpc ( 'type', 'G' ) );
	}
	
	if (isset ( $_GET ["pool_id"] ) && ! empty ( $_GET ["pool_id"] )) {
		$sql_where .= " AND t1.pool_id=" . Database::escape ( intval ( getgpc ( 'pool_id', 'G' )) );
	}
	
	if (is_not_blank ( $_GET ["level"] )) {
		$sql_where .= " AND level=" . Database::escape ( getgpc ( 'level' ) );
	}
	
	if (is_not_blank ( $_GET ["keyword"] )) {
		$keyword = trim ( Database::escape_str ( getgpc ( 'keyword' ), TRUE ) );
		//$sql .= " AND (question_code LIKE '%" . $keyword . "%' OR question LIKE '%" . $keyword . "%')";
		$sql_where .= " AND  question_code LIKE '%" . $keyword . "%'";
	}
	
	if (is_not_blank ( $_GET ['cc'] )) {
		$sql .= " AND cc=" . Database::escape ( getgpc ( 'cc', 'G' ) );
	}
	return trim ( $sql_where );
}

function get_number_of_items() {
	global $tbl_exam_question;
	$sql = "SELECT COUNT(id) FROM $tbl_exam_question AS t1  WHERE t1.pid=0 ";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= $sql_where;
	// 	echo $sql."<br/>";
	return Database::get_scalar_value ( $sql );
}

function get_data_list($from, $number_of_items, $column, $direction) {
	global $_question_types, $_question_level, $tbl_exam_question;
	$tbl_exam_question = Database::get_main_table ( TABLE_MAIN_EXAM_QUESTION );
	$sql = "SELECT id AS col0,question AS col1,type AS col2,level AS col3 FROM $tbl_exam_question AS t1  WHERE t1.pid=0 ";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= $sql_where;
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	//echo $sql;
	$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
	$datalist = array ();
	while ( $row = Database::fetch_row ( $rs ) ) {
		$row [1] = strip_tags($row [1]) ;
		$row [2] = $_question_types [$row [2]];
		$row [3] = $_question_level [$row [3]];
		$datalist [] = $row;
	}
	return $datalist;
}

$nameTools = get_lang ( 'ChoseFromQuestionPool' );
Display::display_header ( $nameTools, FALSE );

$form = new FormValidator ( 'question_pool_form', 'get', $_SERVER ['PHP_SELF'] );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$form->addElement ( 'hidden', 'fromExercise', $fromExercise );
$form->addElement ( 'static', '', '', get_lang ( '题目：' ) );
$form->addElement ( "text", "keyword", null, array ("class" => "inputText" ) );
$form->addElement ( 'static', '', '', get_lang ( '类型：' ) );
$form->addElement ( 'select', 'type', null, $_question_types );

//难度
$form->addElement ( 'static', '', '', get_lang ( '难度：' ) );
$level_options = array ('0' => get_lang ( "All" ), '1' => get_lang ( "DifficultyEasier" ), '2' => get_lang ( "DifficultyEasy" ), '3' => get_lang ( "DifficultyNormal" ), '4' => get_lang ( "DifficultyHard" ), '5' => get_lang ( "DifficultyHarder" ) );
$form->addElement ( "select", "level", get_lang ( "DifficultyLevel" ), $level_options, array ("style" => 'width:10%' ) );

$sql = "SELECT id,pool_name FROM " . $tbl_exam_question_pool . "  ORDER BY display_order ASC";
$all_pools = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
$all_pools = array_insert_first ( $all_pools, array ('0' => get_lang ( "All" ) ) );
$form->addElement ( 'static', '', '', get_lang ( '题库：' ) );
$form->addElement ( 'select', 'pool_id', get_lang ( "QuestionPool" ), $all_pools );
$defaults ['pool_id'] = intval ( getgpc ( 'pool_id' ));

$sql = "SELECT code,title FROM " . $tbl_course . "";
$all_courses = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
$all_courses = array_insert_first ( $all_courses, array ('' => get_lang ( "All" ) ) );
//$form->addElement ( 'select', 'cc', get_lang ( "Courses" ), $all_courses );

$group = array ();
$group [] = $form->createElement ( 'submit', 'submit', get_lang ( 'Filter' ), 'class="inputSubmit"' );
if ($fromExercise) {
	$group [] = & HTML_QuickForm::createElement ( 'style_button', 'rtn', null, array ('type' => 'button', 'value' => get_lang ( 'GoBackToEx' ), 'class' => "cancel", 'onclick' => 'javascript:self.parent.tb_remove();' ) );
}
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );
$form->setDefaults ( $defaults );
echo '<div class="actions">';
$form->display ();
echo '</div>';

$query_vars = array ('fromExercise' => $fromExercise, 'type' => getgpc ( 'type', 'G' ), 'keyword' => getgpc ( 'keyword', 'G' ), 'pool_id' => intval ( getgpc ( 'pool_id', 'G' )) );
$table = new SortableTable ( 'question_list', 'get_number_of_items', 'get_data_list', 2, NUMBER_PAGE, 'ASC' );
$table->set_additional_parameters ( $query_vars );
$idx = 0;
$table->set_header ( $idx ++, '', false );
$table->set_header ( $idx ++, get_lang ( '题目' ) );
$table->set_header ( $idx ++, get_lang ( 'Type' ) );
$table->set_header ( $idx ++, get_lang ( 'DifficultyLevel' ) );
$actions = array ('batch_reuse' => get_lang ( 'ReuseToQuiz' ) );
$table->set_form_actions ( $actions );
$table->display ();

Display::display_footer ();
