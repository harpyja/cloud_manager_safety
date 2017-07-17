<?php
$language_file = array ('survey', 'admin' );
include_once ('../inc/global.inc.php');
api_protect_admin_script ();

require_once ('survey.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( 'action' ) : "");
$survey_id = isset ( $_REQUEST ['survey_id'] ) ? intval(getgpc ( "survey_id" )) : "";
$g_keyword_diptid=  getgpc('keyword_deptid');
$dept_id = isset ( $g_keyword_diptid ) ? intval(getgpc ( 'keyword_deptid', 'G' )) : '0';

$htmlHeadXtra [] = Display::display_thickbox ();

$tool_name = get_lang ( "SurveyQuery" );
Display::display_header ( $tool_name, FALSE );

$objDept = new DeptManager ();
$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{label}&nbsp;{element}</span> ' );

$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" );
$form->addElement ( 'text', 'keyword', $keyword_tip, array ('style' => "width:20%", 'class' => 'inputText', 'title' => $keyword_tip ) );
$depts = $objDept->get_sub_dept_ddl2 ( DEPT_TOP_ID, 'array' );
$form->addElement ( 'select', 'keyword_deptid', get_lang ( 'InDept' ), $depts, array ('id' => "dept_id", 'style' => 'height:22px;min-width:120px' ) );
$form->addElement ( 'hidden', 'survey_id', $survey_id );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );

echo '<div class="actions">';
echo '<span style="float:right; padding-top:5px;">';
//echo '&nbsp;' . link_button ( 'enroll.gif', 'AddUsersToSurvey', 'add_user2survey.php?survey_id=' . $survey_id, 300, 750 );
echo '</span>';
$form->display ();
echo '</div>';
 
$query_vars = array ();
$query_vars ['survey_id'] = $survey_id;
$sql_where = "";

$g_keyword=  getgpc('keyword');
if (isset ( $g_keyword )) {
	$query_vars ['keyword'] = $g_keyword;
	$keyword = trim ( Database::escape_str ( $_GET ['keyword'], TRUE ) );
	if (! empty ( $keyword )) {
		$sql_where .= " AND  (t2.firstname LIKE '%" . $keyword . "%'  OR t2.username LIKE '%" . $keyword . "%')";
	}
}
if ($dept_id and ! is_equal ( $dept_id, 'null' )) {
	$query_vars ['keyword_deptid'] = $dept_id;
	$dept_sn = $objDept->get_sub_dept_sn ( $dept_id );
	if ($dept_sn) $sql_where .= " AND t2.dept_sn LIKE '" . $dept_sn . "%'";
}

//$table_header [] = array ("" );
$table_header [] = array (get_lang ( 'FirstName' ), true );
$table_header [] = array (get_lang ( 'LoginName' ), true );
$table_header [] = array (get_lang ( 'InOrg' ).'/'.get_lang ( 'InDept' ), true );
$table_header [] = array (get_lang ( 'JobTitle' ), true );
$table_header [] = array (get_lang ( 'AttemptDate' ), true );
$table_header [] = array (get_lang ( 'Actions' ) );

$tbl_user_dept = Database::get_main_table ( VIEW_USER_DEPT );
$sql = "SELECT t1.*,t1.user_id,t2.username,t2.firstname,t2.lastname,t2.dept_name,t2.org_name FROM " . $tbl_survey_user . " AS t1 LEFT JOIN " . $tbl_user_dept . " AS t2 ON t1.user_id=t2.user_id  WHERE t1.survey_id=" . Database::escape ( $survey_id );
if ($sql_where) $sql .= $sql_where;
//echo $sql;
$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
$datalist = api_store_result_array ( $rs );
foreach ( $datalist as $data ) {
	$row = array ();
	//$row [] = $data ['user_id'];
	$row [] = $data ['firstname'];
	$row [] = $data ['username'];
	$row [] = $data ['org_name'].'/'.$data ['dept_name'];
	$row [] = $data ['lastname'];
	$row [] = $data ['last_attempt_time'];
	
	//查看答卷
	$action = '<a href="view.php?survey_id=' . $survey_id . '&user_id=' . $data ['user_id'] . '" title="' . get_lang ( "SurveyViewUserSubmit" ) . '" target="_blank">' .
			 Display::return_icon ( 'edit_group.gif', get_lang ( "SurveyViewUserSubmit" ), array ('style' => 'vertical-align: middle;' ) ) . '</a>';
	
	$row [] = $action;
	
	$table_data [] = $row;
}
unset ( $data, $row );

$sorting_options = array ('column' => 0, 'default_order_direction' => 'ASC' );
//$actions = array ("batch_delete" => get_lang ( "BatchDelete" ) );
Display::display_sortable_table ( $table_header, $table_data, $sorting_options, array (), $query_vars, $actions );

Display::display_footer ();