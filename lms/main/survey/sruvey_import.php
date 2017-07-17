<?php
$language_file = 'survey';
require_once ('../inc/global.inc.php');
api_protect_admin_script ();

require_once ('survey.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'export.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'import.lib.php');

$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( 'action' ) : "");
$survey_id = isset ( $_REQUEST ['survey_id'] ) ? intval(getgpc ( "survey_id" )) : "";

define ( "QUESTION_OPTION_SPLIT_CHAR", "|" );
set_time_limit ( 0 );

$tool_name = get_lang ( 'Import' );
$form = new FormValidator ( 'import_questions','post',api_get_self() );
//$form->addElement ( 'header', 'header', get_lang ( 'ImportSurveyQuestions' ) );
$form->addElement ( 'hidden', 'survey_id', $survey_id );
$form->addElement ( 'hidden', 'action', $action );

//选择文件
$form->addElement ( 'file', 'import_file', get_lang ( 'ImportFileLocation' ), array ('style' => "width:400px", 'class' => 'inputText' ) );
$form->addRule ( 'import_file', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$allowed_file_types = array ('xls' );
$form->addRule ( 'import_file', get_lang ( 'InvalidExtension' ) . ' (' . implode ( ',', $allowed_file_types ) . ')', 'filetype', $allowed_file_types );

$group = array ();
$group [] = $form->createElement ( 'submit', 'submit', get_lang ( 'Ok' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

Display::setTemplateBorder ( $form, '98%' );
$form->add_progress_bar ();

if ($form->validate ()) {
	$post_data = $form->getSubmitValues ();
	$survey_id = trim ( $post_data ['survey_id'] );
	if ($_FILES ['import_file'] ['size'] !== 0) {
		$save_path = $_FILES ['import_file'] ['tmp_name'];
		set_time_limit ( 0 );
		$file_type = getFileExt ( $_FILES ['import_file'] ['name'] );
		$file_type = strtolower ( $file_type );
		if ($file_type == 'xls') {
			$data = Import::parse_to_array ( $save_path, $file_type );
			$data_rows = $data ['data'];
			
			$question_data = parse_upload_data ( $data_rows );
			//var_dump($question_data);exit;
			save_data ( $question_data, $survey_id );
		
		} 
		my_delete ( $_FILES ['import_file'] ['tmp_name'] );
		//tb_close ( "survey_questioins.php?survey_id=" . $survey_id );
		tb_close();
	}
}


function save_data($data, $survey_id) {
	if (! empty ( $data )) {
		global $tbl_survey_question, $tbl_survey_question_option,$tbl_survey_question_group, $_question_types;
		foreach ( $data as $key => $item ) {
			$questionOption = $item ['questionOption'];
			
			$questionGroup=$item['group'];
			$sql="SELECT id FROM $tbl_survey_question_group WHERE survey_id=" 
			. Database::escape ( $survey_id )." AND name=".Database::escape($questionGroup);
			$group_id= Database::get_scalar_value ( $sql );
			
			$tbl_category = Database::get_main_table ( TABLE_CATEGORY );
			$sql="SELECT id FROM $tbl_category WHERE module='survey_question' AND name=".Database::escape($item['category']);
			$category_id= Database::get_scalar_value ( $sql );
			
			unset ( $item ['questionOption'],$item['group'] );
			$sql = "SELECT MAX(sort) FROM $tbl_survey_question WHERE survey_id=" . Database::escape ( $survey_id );
			$sort = Database::get_scalar_value ( $sql );
			$sort = (empty ( $sort ) ? 1 : $sort ++);
			
			$sql_data = array ("survey_id" => $survey_id, 'survey_question' => $item ['question'], 'type' => $item ['type'], 'sort' => $sort ,'group_id'=>$group_id,'category'=>$category_id);
			$sql = Database::sql_insert ( $tbl_survey_question, $sql_data );
			api_sql_query ( $sql, __FILE__, __LINE__ ); //插入表crs_question
			$question_id = Database::get_last_insert_id ();
			
			if (in_array ( $item ["type"], array (UNIQUE_ANSWER, MULTIPLE_ANSWER ) )) {
				if ($questionOption && is_array ( $questionOption )) {
					foreach ( $questionOption as $options ) {
						$options ['question_id'] = $question_id;
						$options ['survey_id'] = $survey_id;
						if (! empty ( $options ) && is_array ( $options )) {
							$sql1 = Database::sql_insert ( $tbl_survey_question_option, $options );
							api_sql_query ( $sql1, __FILE__, __LINE__ );
						}
					}
				}
			}
		}
	}
	return FALSE;
}


/**
 * 导入并解析非综合试题
 * @param unknown_type $data_rows
 */
function parse_upload_data($data_rows) {
	global $_question_types;
	//var_dump($data_rows);exit;
	$answer_alpha_idx = array_flip ( Question::$alpha );
	$allQuestions = array ();
	if (is_array ( $data_rows ) && count ( $data_rows ) > 0) {
		foreach ( $data_rows as $key => $item ) {
			$questionItem = array ();
			$question_type = trim ( $item ['QuestionType'] );
			//题型
			if ($question_type == $_question_types [UNIQUE_ANSWER]) {
				$questionItem ['type'] = UNIQUE_ANSWER;
			} elseif ($question_type == $_question_types [MULTIPLE_ANSWER]) {
				$questionItem ['type'] = MULTIPLE_ANSWER;
			} elseif ($question_type == $_question_types [FREE_ANSWER]) {
				$questionItem ['type'] = FREE_ANSWER;
			}
			
			$questionItem ['question'] = trim ( $item ['QuestionTitle'] ); //题目
			$questionItem ['category'] = trim ( $item ['Category'] ); //题目
			$questionItem ['group'] = trim ( $item ['Group'] ); //题目
			//$question_score = floatval ( $item ['Score'] );
			//$questionItem ['ponderation'] = floatval ( $question_score ); //分数
			//			$questionItem ['created_user'] = $questionItem ['last_updated_user'] = api_get_user_id ();
			//			$questionItem ['created_date'] = $questionItem ['last_updated_date'] = date ( 'Y-m-d H:i:s' );
			

			$available_question_type = array (UNIQUE_ANSWER, MULTIPLE_ANSWER, FREE_ANSWER );
			
			if (in_array ( $questionItem ['type'], $available_question_type )) {
				//选项Options/答案的处理 (插入exam_answer表的数据)
				if ($questionItem ['type'] == UNIQUE_ANSWER or $questionItem ['type'] == MULTIPLE_ANSWER) { //选择题
					

					$questionOptions = array ();
					$options = explode ( QUESTION_OPTION_SPLIT_CHAR, $item ['Options'] );
					
					$questionOptionScore = array ();
					$optionScore = explode ( QUESTION_OPTION_SPLIT_CHAR, $item ['OptionScore'] );
					foreach ( $options as $key => $option ) {
						$optionItem = array ();
						$optionItem ['sort'] = $key + 1;
						$optionItem ['option_text'] = $option;
						$optionItem ['value'] = $optionScore [$key];
						$questionOptions [] = $optionItem;
					}
					$questionItem ["questionOption"] = $questionOptions;
				}
				$allQuestions [] = $questionItem;
			}
		}
		return $allQuestions;
	}
	return array ();
}

Display::display_header ( $tool_name ,FALSE);

if (file_exists ( api_get_path ( SYS_PATH ) . "storage/examples/import_files/tpl_import_survey.xls" )) {
	echo '<div style="float:left;padding-left:10px"><a href="' . api_get_path ( WEB_PATH ) . 'storage/examples/import_files/tpl_import_survey.xls">' . get_lang ( "ImportTemplate" ) . "</a></div>";
	echo '<div style="clear:both"></div>';
}

$form->display ();

Display::display_footer ();

