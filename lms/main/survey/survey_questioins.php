<?php
$language_file = 'survey';
include_once ('../inc/global.inc.php');
api_protect_admin_script ();

require_once ('survey.inc.php');

$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( 'action' ) : "");
$survey_id = isset ( $_REQUEST ['survey_id'] ) ? getgpc ( "survey_id" ) : "";

$g_action=  getgpc('action');
if (isset ( $g_action )) {
	switch ($g_action) {
		case "del" :
			$item_id = getgpc ( "id", "G" );
			if ($objQuestionTmp = Question::get_info ( $item_id )) {
				$objQuestionTmp->delete ();
			}
			unset ( $objQuestionTmp );
			api_redirect ( "question_list.php?pool_id=" . getgpc ( "pool_id" ) . "&message=" . urlencode ( get_lang ( "OperationSuccess" ) ) );
			break;
	}
}

$htmlHeadXtra [] = Display::display_thickbox ();
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
Display::display_header ( NULL ,FALSE);

$html = '<div id="demo" class="yui-navset">';
$html .= '<ul class="yui-nav">';
$html .= '<li><a href="survey_users.php?survey_id=' . $survey_id . '"><em>' . get_lang ( 'SurveyUsers' ) . '</em></a></li>';
$html .= '<li class="selected"><a href="survey_questioins.php?survey_id=' . $survey_id . '"><em>' . get_lang ( 'SurveyQuestions' ) . '</em></a></li>';
$html .= '<li><a href="survey_edit.php?survey_id=' . $survey_id . '"><em>' . get_lang ( 'EditSurvey' ) . '</em></a></li>';
$html .= '</ul>';
$html .= '<div class="yui-content"><div id="tab1">';
echo $html;

$p_action=  getgpc('action');
if (isset ( $p_action )) {
	switch ($p_action) {
		case 'delete' :
			$number_of_selected_items = count ( $_POST ['id'] );
			$number_of_deleted_items = 0;
			foreach ( $_POST ['id'] as $index => $item_id ) {
				if ($objQuestionTmp = Question::get_info ( $item_id )) {
					$objQuestionTmp->delete ();
					$number_of_deleted_items ++;
				}
				unset ( $objQuestionTmp );
			}
			if ($number_of_selected_items == $number_of_deleted_items) {
				Display::display_normal_message ( get_lang ( 'SelectedQuestionDeleted' ) );
			} else {
				Display::display_error_message ( get_lang ( 'SomeQuestionNotDeleted' ) );
			}
			break;
	}
}

$pool_id = getgpc ( "pool_id", "G" );

$g_message=  getgpc('message');
if (! empty ( $g_message )) {
	$message = urldecode ( $g_message );
	Display::display_normal_message ( urldecode ( stripslashes ( $message ) ) );
}

$form = new FormValidator ( 'question_pool_form', 'get', $_SERVER ['PHP_SELF'] );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$form->addElement ( 'text', 'keyword', null, array ('class' => 'inputText', 'style' => 'width:20%' ) );
$form->addElement ( 'select', 'question_type', null, $_question_types );
$defaults ['question_type'] = getgpc ( 'question_type', 'G' );

/*$sql = "SELECT id,name FROM $tbl_survey_question_group WHERE survey_id=" . Database::escape ( $survey_id );
$categories = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
$categories=array_insert_first($categories,array(''=>get_lang('All')));
$form->addElement ( 'select', 'group_id', get_lang ( "InSurveyItemGroup" ), $categories );
$defaults['group_id']=getgpc ( 'group_id', 'G' );*/

$group = array ();
$group [] = $form->createElement ( 'submit', 'submit', get_lang ( 'Search' ), 'class="inputSubmit"' );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );
$form->addElement ( 'hidden', 'survey_id', $survey_id );
$form->setDefaults ( $defaults );

echo '<div class="actions">';
echo '<span style="float:right; padding-top:5px;">';
echo link_button ( 'excel.gif', 'ImportSurveyQuestions', 'sruvey_import.php?survey_id=' . $survey_id, '40%', '60%' );
Display::display_icon ( 'i.gif' );
echo str_repeat ( "&nbsp;", 0 ) . Display::return_icon ( 'survey.gif', get_lang ( 'AddQuestion' ), array ('align' => 'absbottom' ) ) . '<b>' . get_lang ( "AddQuestion" ) . '</b>:';
echo str_repeat ( "&nbsp;", 2 ) . '<a class="thickbox" href="question_update.php?action=add&answerType=' . UNIQUE_ANSWER . '&survey_id=' . $survey_id . '&KeepThis=true&TB_iframe=true&height=80%&width=90%&modal=true" title="' . $_question_types [UNIQUE_ANSWER] . '">' . $_question_types [UNIQUE_ANSWER] .
		 '</a>';
echo str_repeat ( "&nbsp;", 1 ) . '<a class="thickbox" href="question_update.php?action=add&answerType=' . MULTIPLE_ANSWER . '&survey_id=' . $survey_id . '&KeepThis=true&TB_iframe=true&height=80%&width=90%&modal=true" title="' . $_question_types [MULTIPLE_ANSWER] . '">' .
		 $_question_types [MULTIPLE_ANSWER] . '</a>';
echo str_repeat ( "&nbsp;", 1 ) . '<a class="thickbox" href="question_update.php?action=add&answerType=' . FREE_ANSWER . '&survey_id=' . $survey_id . '&KeepThis=true&TB_iframe=true&height=80%&width=90%&modal=true" title="' . $_question_types [FREE_ANSWER] . '">' . $_question_types [FREE_ANSWER] .
		 '</a>';
echo '</span>';
$form->display ();
echo '</div>';

//试题列表
$question_list = Question::get_list ( $survey_id, getgpc ( 'question_type', 'G' ), getgpc ( 'keyword', 'G' ), getgpc ( 'group_id', 'G' ) );

$nbrQuestions = count ( $question_list );

$table_header [] = array ();
$table_header [] = array (get_lang ( 'QuestionType' ), true );
$table_header [] = array (get_lang ( 'Question' ) );
//$table_header [] = array (get_lang ( "InCategories" ),true );
//$table_header [] = array (get_lang ( "InSurveyItemGroup" ),true );
$table_header [] = array (get_lang ( 'Actions' ) );

$table_data = array ();

foreach ( $question_list as $row ) {
	$row_render = array ();
	$row_render [] = $row ["id"];
	
	$row_render [] = $_question_types [$row ['type']];
	
	$row_render [] = api_trunc_str2 ( $row ['survey_question'], 50 );
	
	//$row_render [] = $row ['category_name'];
	//$row_render [] = $row ['group_name'];
	

	$action_html = "";
	$action_html .= link_button ( 'edit.gif', 'Modify', 'question_update.php?action=edit&qid=' . $row ['id'] . '&answerType=' . $row ['type'] . '&survey_id=' . $survey_id, 330, 750, FALSE );
	
	$href='survey_questions.php?action=del&id=' . $row ["id"] . '&pool_id=' . $pool_id;
	$action_html .= '&nbsp;'.confirm_href('delete.gif', 'ConfirmYourChoice', 'Delete', $href);
	
	$row_render [] = $action_html;
	
	$table_data [] = $row_render;

}
$sorting_options = array ();
$sorting_options ['column'] = 1;
$sorting_options ['default_order_direction'] = 'DESC';

$query_vars = array ('question_type' => getgpc ( 'question_type', 'G' ), 'group_id' => getgpc ( 'group_id', 'G' ), 'keyword' => getgpc ( 'keyword', 'G' ), "survey_id" => $survey_id );
$actions = array ('delete' => get_lang ( 'BatchDelete' ) );
Display::display_sortable_table ( $table_header, $table_data, $sorting_options, array (), $query_vars, $actions );
echo '</div></div></div>';
Display::display_footer ();
