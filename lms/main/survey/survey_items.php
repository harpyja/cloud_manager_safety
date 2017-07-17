<?php
$language_file = 'survey';
include_once ('../inc/global.inc.php');
api_protect_admin_script ();

require_once ('survey.inc.php');

$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( 'action' ) : "");
$survey_id = isset ( $_REQUEST ['survey_id'] ) ? intval(getgpc ( "survey_id" )) : "";

$g_todo=  getgpc('todo');
if (isset ( $g_todo )) {
	switch ($g_todo) {
		case 'delete' :
			$sql = "DELETE FROM " . $tbl_survey_question_group . " WHERE id=" . Database::escape ( intval(getgpc ( "id", 'G' )) );
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			break;
	}
}

//JQuery,Thickbox
$htmlHeadXtra [] = Display::display_thickbox ();
$htmlHeadXtra [] = import_assets ( "jquery-plugins/jquery.wtooltip.js" );
$htmlHeadXtra [] = '<script type="text/javascript">
	$(document).ready( function() {
		//$("a,img").wTooltip();
	});
</script>';

Display::display_header ( NULL ,FALSE);
//顶部链接

echo '<div class="actions">';
echo str_repeat ( '&nbsp;', 4 ) . link_button('documents.gif','AddSurveyItem','survey_items_update.php?action=add&survey_id='.$survey_id,200,500);
echo '</div>';

$table_header [] = get_lang ( 'Name' );
$table_header [] = get_lang ( 'NumberOfQuestions' );
$table_header [] = get_lang ( 'DisplayOrder' );
$table_header [] = get_lang ( 'Actions' );

$sql = "SELECT t1.* as qstn_cnt FROM " . $tbl_survey_question_group . " AS t1  WHERE t1.survey_id='" . escape($survey_id) . "' ORDER BY t1.display_order";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
while ( $data = Database::fetch_array ( $res ,'ASSOC') ) {
	$row = array ();
	$row [] = $data ['name'];
	$row [] = SurveyManager::get_question_count($survey_id,$data ['id']);
	$row [] = $data ["display_order"];
	
	$action = "";
	$action .= '&nbsp;<a class="thickbox" href="survey_items_update.php?action=edit&id=' . $data ['id'] . '&KeepThis=true&TB_iframe=true&height=200&width=500&modal=true">' . Display::return_icon ( 'edit.gif', get_lang ( 'Edit' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>&nbsp;';
	$action .= '&nbsp;<a href="survey_items.php?action=survey_items&todo=delete&amp;id=' . $data ['id'] . '&survey_id='.$survey_id.'" onclick="javascript:if(!confirm(' . "'" . addslashes ( htmlentities ( get_lang ( "ConfirmYourChoice" ), ENT_NOQUOTES, SYSTEM_CHARSET ) ) . "'" . ')) return false;">' . Display::return_icon ( 
			'delete.gif', get_lang ( 'Delete' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>';
	$row [] = $action;
	$table_data[]=$row;
}
echo Display::display_table($table_header,$table_data);

Display::display_footer ();
?>

