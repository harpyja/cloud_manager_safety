<?php
include_once ('exercise.class.php');
include_once ('question.class.php');
include_once ('answer.class.php');

$language_file = 'exercice';
include_once ('../inc/global.inc.php');
include_once ('exercise.lib.php');
api_protect_quiz_script ();

define ( "QUESTION_OPTION_SPLIT_CHAR", "|" );
set_time_limit ( 0 );
require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'export.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'import.lib.php');

$form = new FormValidator ( 'export_questions' );
$form->addElement ( 'hidden', 'type', getgpc ( 'type', 'G' ) );

$sql = "SELECT id,pool_name FROM " . $tbl_exam_question_pool . "  ORDER BY display_order ASC";
$all_pools = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
$form->addElement ( 'select', 'pool_id', get_lang ( "QuestionPool" ), $all_pools, array ('style' => "min-width:50%" ) );
$defaults ['pool_id'] = intval(getgpc ( 'pool_id' ));

$sql = "SELECT code,title FROM " . $tbl_course . "";
$all_courses = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
//$all_courses = array_insert_first ( $all_courses, array ('' => '---使用导入文件中的课程编号设置---' ) );
$form->addElement ( 'select', 'cc', get_lang ( "Courses" ), $all_courses, array ('style' => "min-width:50%", 'id' => 'course_code' ) );

//选择文件
$form->addElement ( 'file', 'import_file', get_lang ( 'ImportFileLocation' ), array ('style' => "width:70%", 'class' => 'inputText' ) );
$form->addRule ( 'import_file', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$allowed_file_types = array ('xls' );
$form->addRule ( 'import_file', get_lang ( 'InvalidExtension' ) . ' (' . implode ( ',', $allowed_file_types ) . ')', 'filetype', $allowed_file_types );

$group = array ();
$group [] = $form->createElement ( 'submit', 'submit', get_lang ( 'Ok' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ( $defaults );

Display::setTemplateBorder ( $form, '98%' );
$form->add_progress_bar ();

if ($form->validate ()) {
	$post_data = $form->getSubmitValues ();
	$cc = trim ( $post_data ['cc'] );
	$pool_id = trim ( $post_data ['pool_id'] );
	if ($_FILES ['import_file'] ['size'] !== 0) {
		$save_path = $_FILES ['import_file'] ['tmp_name'];
		set_time_limit ( 0 );
		$file_type = getFileExt ( $_FILES ['import_file'] ['name'] );
		$file_type = strtolower ( $file_type );
		if ($file_type == 'xls') {
			$data = Import::parse_to_array ( $save_path, 'xls' );
			$data_rows = $data ['data'];
			$question_data = parse_upload_data ( $data_rows );
			//var_dump($question_data);exit;
			save_data ( $question_data, $cc, $pool_id );
		} else {
		
		}
		
		my_delete ( $_FILES ['import_file'] ['tmp_name'] );
		
		tb_close ( 'question_base.php?pool_id=' . $pool_id );
	}
}

function save_data($data, $cc, $pool_id) {
	if (empty ( $data )) return false;
	else {
		global $TBL_QUESTIONS, $TBL_REPONSES, $_question_types;
		foreach ( $data as $key => $item ) {
			$questionAnswer = $item ['questionAnswer'];
			unset ( $item ['questionAnswer'] );
			$item ['pool_id'] = $pool_id;
			$item ['created_user'] = $item ['last_updated_user'] = api_get_user_id ();
			$item ['created_date'] = $item ['last_updated_date'] = date ( 'Y-m-d H:i:s' );
			if (empty ( $item ['cc'] )) $item ['cc'] = $cc;
			$sql = Database::sql_insert ( $TBL_QUESTIONS, $item );
			api_sql_query ( $sql, __FILE__, __LINE__ ); //插入表crs_question
			$question_id = Database::get_last_insert_id ();
			
			if (in_array ( $item ["type"], array (UNIQUE_ANSWER, MULTIPLE_ANSWER, TRUE_FALSE_ANSWER ) )) {
				if ($questionAnswer && is_array ( $questionAnswer )) {
					foreach ( $questionAnswer as $options ) {
						if (empty ( $options ['cc'] )) $options ['cc'] = $cc;
						$options ['question_id'] = $question_id;
						if (! empty ( $options ) && is_array ( $options )) {
							$sql1 = Database::sql_insert ( $TBL_REPONSES, $options );
							api_sql_query ( $sql1, __FILE__, __LINE__ );
						}
					}
				}
			} elseif (in_array ( $item ["type"], array (FILL_IN_BLANKS, FREE_ANSWER ) )) {
				if ($questionAnswer && is_array ( $questionAnswer )) {
					if (empty ( $questionAnswer ['cc'] )) $questionAnswer ['cc'] = $cc;
					$questionAnswer ['question_id'] = $question_id;
					$sql1 = Database::sql_insert ( $TBL_REPONSES, $questionAnswer );
					api_sql_query ( $sql1, __FILE__, __LINE__ );
				}
			}
		}
	}
}

function parse_upload_data($data_rows) {
	global $_question_types, $_configuration;
	//var_dump($data_rows);exit;
	$answer_alpha_idx = array_flip ( Question::$alpha );
	$allQuestions = array ();
	$allCourse = array ();
	$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	if (is_array ( $data_rows ) && count ( $data_rows ) > 0) {
		foreach ( $data_rows as $key => $item ) {
			$questionItem = array ();
			$question_type = trim ( $item ['QuestionType'] );
			//题型
			if ($question_type == $_question_types [UNIQUE_ANSWER]) {
				$questionItem ['type'] = UNIQUE_ANSWER;
			} elseif ($question_type == $_question_types [MULTIPLE_ANSWER]) {
				$questionItem ['type'] = MULTIPLE_ANSWER;
			} elseif ($question_type == $_question_types [FILL_IN_BLANKS]) {
				$questionItem ['type'] = FILL_IN_BLANKS;
			} elseif ($question_type == $_question_types [FREE_ANSWER]) {
				$questionItem ['type'] = FREE_ANSWER;
			} elseif ($question_type == $_question_types [TRUE_FALSE_ANSWER]) {
				$questionItem ['type'] = TRUE_FALSE_ANSWER;
			} elseif ($question_type == $_question_types [CLOZE_QUESTION]) {
				$questionItem ['type'] = CLOZE_QUESTION;
			} elseif ($question_type == $_question_types [COMBO_QUESTION]) {
				$questionItem ['type'] = COMBO_QUESTION;
			}
			
			$question_score = floatval ( $item ['Score'] );
			$questionItem ['ponderation'] = floatval ( $question_score ); //分数
			$questionItem ['level'] = $item ['Difficulty']; //难度
			$questionItem ['question'] = trim ( $item ['QuestionTitle'] ); //题目
			$questionItem ['comment'] = trim ( $item ['QuestionAnalysis'] );
			$questionItem ['question_code'] = (empty ( $item ['QuestionCode'] ) ? Question::_get_question_code () : $item ['QuestionCode']);
			if ($item ['CourseCode']) {
				$questionItem ['cc'] = trim ( $item ['CourseCode'] );
				if (empty ( $allCourse [$questionItem ['cc']] )) {
					$sql = "SELECT * FROM " . $tbl_course . " WHERE code=" . Database::escape ( $questionItem ['cc'] );
					if (Database::if_row_exists ( $sql, __FILE__, __LINE__ )) $allCourse [$questionItem ['cc']] = 1;
				}
				if (empty ( $allCourse [$questionItem ['cc']] )) $questionItem ['cc'] = '';
			}
			$questionItem ['created_user'] = $questionItem ['last_updated_user'] = api_get_user_id ();
			$questionItem ['created_date'] = $questionItem ['last_updated_date'] = date ( 'Y-m-d H:i:s' );
			
			$available_question_type = array (UNIQUE_ANSWER, MULTIPLE_ANSWER, TRUE_FALSE_ANSWER );
			if ($_configuration ['enable_question_freeanswer']) $available_question_type [] = FREE_ANSWER;
			if ($_configuration ['enable_question_fillblanks']) $available_question_type [] = FILL_IN_BLANKS;
			if (empty ( $item ["QuestionTrunkLineNumber"] ) && in_array ( $questionItem ['type'], $available_question_type )) {
				//选项Options/答案的处理 (插入exam_answer表的数据)
				if ($questionItem ['type'] == UNIQUE_ANSWER or $questionItem ['type'] == MULTIPLE_ANSWER) { //选择题
					$correct_answer = explode ( QUESTION_OPTION_SPLIT_CHAR, $item ['Answer'] ); //答案
					$correct_answer_index = array ();
					foreach ( $correct_answer as $answerTmp ) {
						$correct_answer_index [] = $answer_alpha_idx [$answerTmp];
					}
					
					$questionOptions = array ();
					$options = explode ( QUESTION_OPTION_SPLIT_CHAR, $item ['Options'] );
					foreach ( $options as $key => $option ) {
						$optionItem = array ();
						$optionItem ['id'] = $optionItem ['position'] = $key + 1;
						$optionItem ['answer'] = $option;
						
						if (in_array ( $optionItem ['id'], $correct_answer_index )) {
							$optionItem ['correct'] = 1;
							if ($optionItem ['type'] == UNIQUE_ANSWER) {
								$optionItem ['ponderation'] = floatval ( $question_score );
							} else {
								$optionItem ['ponderation'] = round ( floatval ( $question_score ) / count ( $correct_answer_index ), 1 );
							}
						} else {
							$optionItem ['correct'] = 0;
							$optionItem ['ponderation'] = 0;
						}
						$questionOptions [] = $optionItem;
					}
					$questionItem ["questionAnswer"] = $questionOptions;
				} elseif ($questionItem ['type'] == TRUE_FALSE_ANSWER) { //判断题
					$questionOptions = array ();
					$tf_correct_answer = trim ( $item ['Answer'] );
					$optionItem = array ();
					$optionItem ['id'] = $optionItem ['position'] = 1;
					$optionItem ['answer'] = get_lang ( "QuestionRight" );
					if (in_array ( $tf_correct_answer, array ("T", "1", get_lang ( "QuestionRight" ) ) )) {
						$optionItem ['correct'] = 1;
						$optionItem ['ponderation'] = floatval ( $question_score );
					} else {
						$optionItem ['correct'] = 0;
						$optionItem ['ponderation'] = 0;
					}
					$questionOptions [] = $optionItem;
					
					$optionItem = array ();
					$optionItem ['id'] = $optionItem ['position'] = 2;
					$optionItem ['answer'] = get_lang ( "QuestionWrong" );
					if (in_array ( $tf_correct_answer, array ("F", "0", get_lang ( "QuestionWrong" ) ) )) {
						$optionItem ['correct'] = 1;
						$optionItem ['ponderation'] = floatval ( $question_score );
					} else {
						$optionItem ['correct'] = 0;
						$optionItem ['ponderation'] = 0;
					}
					$questionOptions [] = $optionItem;
					
					$questionItem ["questionAnswer"] = $questionOptions;
				} elseif ($questionItem ['type'] == FILL_IN_BLANKS) { //填空题
					if ($_configuration ['enable_question_fillblanks']) {
						$questionAnswer = array ();
						$fb_answer = trim ( $item ['Answer'] );
						$questionAnswer ['id'] = $questionAnswer ['position'] = 1;
						$questionAnswer ['correct'] = 0;
						$questionAnswer ['ponderation'] = floatval ( $question_score );
						
						//preg_match_all('/\[[^\]]*\]/',$fb_answer,$matches);
						//$blank_count=count($matches[0]); //V1.4: 这里都算成等分值的了, 以后改进
						$fill_blank_answer_arr = get_fill_blank_answer_array ( $fb_answer );
						$blank_count = count ( $fill_blank_answer_arr );
						$blank_score = round ( floatval ( $question_score ) / $blank_count, 1 );
						for($i = 0; $i < $blank_count; $i ++) {
							$blank_score_arr [] = $blank_score;
						}
						$questionAnswer ['answer'] = $fb_answer . " ::" . implode ( ",", $blank_score_arr );
						
						$questionItem ["questionAnswer"] = $questionAnswer;
						unset ( $blank_count, $blank_score, $blank_score_arr );
					}
				} elseif ($questionItem ['type'] == FREE_ANSWER) { //简答题
					if ($_configuration ['enable_question_freeanswer']) {
						$questionAnswer = array ();
						$questionAnswer ['id'] = $questionAnswer ['position'] = 1;
						$questionAnswer ['correct'] = 0;
						$questionAnswer ['ponderation'] = floatval ( $question_score );
						$questionAnswer ['answer'] = $item ['Answer'];
						$questionItem ["questionAnswer"] = $questionAnswer;
					}
				}
				$allQuestions [] = $questionItem;
			} else {
			
			}
		}
		return $allQuestions;
	}
	return array ();
}

/**
 * 导入并解析非综合试题
 * @param unknown_type $data_rows
 */
function parse_upload_data2($data_rows) {
	global $_question_types, $_configuration;
	//var_dump($data_rows);exit;
	$answer_alpha_idx = array_flip ( Question::$alpha );
	$allQuestions = array ();
	$allCourse = array ();
	$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	if (is_array ( $data_rows ) && count ( $data_rows ) > 0) {
		foreach ( $data_rows as $key => $item ) {
			$questionItem = array ();
			$question_type = trim ( $item ['试题类型'] );
			//题型
			if ($question_type == $_question_types [UNIQUE_ANSWER]) {
				$questionItem ['type'] = UNIQUE_ANSWER;
			} elseif ($question_type == $_question_types [MULTIPLE_ANSWER]) {
				$questionItem ['type'] = MULTIPLE_ANSWER;
			} elseif (in_array ( $question_type, array ($_question_types [FREE_ANSWER], '计算题', '问答题' ) )) {
				$questionItem ['type'] = FREE_ANSWER;
			} elseif ($question_type == $_question_types [TRUE_FALSE_ANSWER]) {
				$questionItem ['type'] = TRUE_FALSE_ANSWER;
			}
			
			$question_score = floatval ( trim ( $item ['题目分数'] ) );
			$questionItem ['ponderation'] = $question_score ? $question_score : 1; //分数
			$questionItem ['level'] = $item ['题目难度'] ? $item ['题目难度'] : 3; //难度
			$questionItem ['question'] = trim ( $item ['试题内容'] ); //题目
			$questionItem ['answer'] = trim ( $item ['答案'] ); //答案
			$questionItem ['comment'] = trim ( $item ['题目解析'] );
			$questionItem ['question_code'] = (empty ( $item ['题目编号'] ) ? Question::_get_question_code () : $item ['题目编号']);
			if ($item ['课程编号']) {
				$questionItem ['cc'] = trim ( $item ['课程编号'] );
				if (empty ( $allCourse [$questionItem ['cc']] )) {
					$sql = "SELECT * FROM " . $tbl_course . " WHERE code=" . Database::escape ( $questionItem ['cc'] );
					if (Database::if_row_exists ( $sql, __FILE__, __LINE__ )) $allCourse [$questionItem ['cc']] = 1;
				}
				if (empty ( $allCourse [$questionItem ['cc']] )) $questionItem ['cc'] = '';
			}
			$questionItem ['created_user'] = $questionItem ['last_updated_user'] = api_get_user_id ();
			$questionItem ['created_date'] = $questionItem ['last_updated_date'] = date ( 'Y-m-d H:i:s' );
			
			$available_question_type = array (UNIQUE_ANSWER, MULTIPLE_ANSWER, TRUE_FALSE_ANSWER );
			if ($_configuration ['enable_question_freeanswer']) $available_question_type [] = FREE_ANSWER;
			if ($_configuration ['enable_question_fillblanks']) $available_question_type [] = FILL_IN_BLANKS;
			if (empty ( $item ["QuestionTrunkLineNumber"] ) && in_array ( $questionItem ['type'], $available_question_type )) {
				//选项Options/答案的处理 (插入exam_answer表的数据)
				if ($questionItem ['type'] == UNIQUE_ANSWER or $questionItem ['type'] == MULTIPLE_ANSWER) { //选择题
					$correct_answer = explode ( QUESTION_OPTION_SPLIT_CHAR, $item ['答案'] ); //答案
					$correct_answer_index = array ();
					foreach ( $correct_answer as $answerTmp ) {
						$correct_answer_index [] = $answer_alpha_idx [$answerTmp];
					}
					
					$questionOptions = $options = array ();
					if ($item ['选项A']) $options [] = trim ( $item ['选项A'] );
					if ($item ['选项B']) $options [] = trim ( $item ['选项B'] );
					if ($item ['选项C']) $options [] = trim ( $item ['选项C'] );
					if ($item ['选项D']) $options [] = trim ( $item ['选项D'] );
					if ($item ['选项E']) $options [] = trim ( $item ['选项E'] );
					if ($item ['选项F']) $options [] = trim ( $item ['选项F'] );
					if ($item ['选项G']) $options [] = trim ( $item ['选项G'] );
					foreach ( $options as $key => $option ) {
						$optionItem = array ();
						$optionItem ['id'] = $optionItem ['position'] = $key + 1;
						$optionItem ['answer'] = $option;
						
						if (in_array ( $optionItem ['position'], $correct_answer_index )) {
							$optionItem ['correct'] = 1;
							if ($optionItem ['type'] == UNIQUE_ANSWER) {
								$optionItem ['ponderation'] = floatval ( $question_score );
							} else {
								$optionItem ['ponderation'] = round ( floatval ( $question_score ) / count ( $correct_answer_index ), 1 );
							}
						} else {
							$optionItem ['correct'] = 0;
							$optionItem ['ponderation'] = 0;
						}
						$questionOptions [] = $optionItem;
					}
					$questionItem ["questionAnswer"] = $questionOptions;
				} elseif ($questionItem ['type'] == TRUE_FALSE_ANSWER) { //判断题
					$questionOptions = array ();
					$tf_correct_answer = trim ( $item ['答案'] );
					$optionItem = array ();
					$optionItem ['id'] = $optionItem ['position'] = 1;
					$optionItem ['answer'] = get_lang ( "QuestionRight" );
					if (in_array ( $tf_correct_answer, array ("T", "1", 'A', get_lang ( "QuestionRight" ) ) )) {
						$optionItem ['correct'] = 1;
						$optionItem ['ponderation'] = floatval ( $question_score );
					} else {
						$optionItem ['correct'] = 0;
						$optionItem ['ponderation'] = 0;
					}
					$questionOptions [] = $optionItem;
					
					$optionItem = array ();
					$optionItem ['id'] = $optionItem ['position'] = 2;
					$optionItem ['answer'] = get_lang ( "QuestionWrong" );
					if (in_array ( $tf_correct_answer, array ("F", "0", 'B', get_lang ( "QuestionWrong" ) ) )) {
						$optionItem ['correct'] = 1;
						$optionItem ['ponderation'] = floatval ( $question_score );
					} else {
						$optionItem ['correct'] = 0;
						$optionItem ['ponderation'] = 0;
					}
					$questionOptions [] = $optionItem;
					
					$questionItem ["questionAnswer"] = $questionOptions;
				} elseif ($questionItem ['type'] == FREE_ANSWER) { //简答题
					if ($_configuration ['enable_question_freeanswer']) {
						$questionAnswer = array ();
						$questionAnswer ['id'] = $questionAnswer ['position'] = 1;
						$questionAnswer ['correct'] = 0;
						$questionAnswer ['ponderation'] = floatval ( $question_score );
						$questionAnswer ['answer'] = $item ['答案'];
						$questionItem ["questionAnswer"] = $questionAnswer;
					}
				}
				$allQuestions [] = $questionItem;
			} else {
			
			}
		}
		return $allQuestions;
	}
	return array ();
}

$htmlHeadXtra [] = '<script type="text/javascript">
	$(document).ready( function() {
		$("#course_code").parent().append("<div class=\'onShow\'><br/>请注意,导入文件中的课程编号必须设置正确,否则会以当前选择的课程代替!</div>");
	});</script>';

Display::display_header ( NULL, FALSE );
$import_tpl = 'storage/examples/import_files/example_question.xls';
if (file_exists ( api_get_path ( SYS_PATH ) . $import_tpl )) {
	echo '<div style="float:left;padding-left:10px"><a href="' . api_get_path ( WEB_PATH ) . $import_tpl . '">' . get_lang ( "ImportTemplate" ) . "</a></div>";
	echo '<div style="clear:both"></div>';
}

$form->display ();

Display::display_footer ();
