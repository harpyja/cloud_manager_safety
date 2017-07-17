<?php
/*
 * 待安排的考生
 */
include_once ('../../exercice/exercise.class.php');
$language_file = array ('exercice', 'admin' );
include_once ('../../inc/global.inc.php');

require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
include_once ('../../exercice/exercise.lib.php');

//api_protect_quiz_script ();

$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( 'action' ) : "");
$exam_id = (isset ( $_REQUEST ['exam_id'] ) ?  intval(getgpc ( 'exam_id' )) : "");
$dept_id = isset ( $_GET ['keyword_deptid'] ) ?  intval(getgpc ( 'keyword_deptid', 'G' )) : '0';

if (isset ( $_POST ['action'] )) {
	switch ($_POST ['action']) {
		case 'batch_delete' : //批量删除
			$number_of_selected_items = count ( $_POST ['id'] );
			$number_of_deleted_items = 0;
			foreach ( $_POST ['id'] as $index => $item_id ) {
				if (Exercise::del_user_from_exam ( $exam_id, $item_id )) {
					$number_of_deleted_items ++;
				}
			}
			if ($number_of_selected_items == $number_of_deleted_items) {
				api_redirect ( "have_arranged.php?exam_id=" . $exam_id . "&message=" . urlencode ( get_lang ( 'OperationSuccess' ) ) );
			} else {
				api_redirect ( "have_arranged.php?exam_id=" . $exam_id . "&message=" . urlencode ( get_lang ( '操作失败！不允许删除有成绩的考生！' ) ) );
			}
			break;
	
	}
}

$objDept = new DeptManager ();
/*$all_org = $objDept->get_all_org ();
$orgs [''] = get_lang ( 'All' );
foreach ( $all_org as $org ) {
	$orgs [$org ['id']] = $org ['dept_name'];
}*/

$sql = "SELECT t1.* FROM " . $TBL_EXERCICES . " AS t1 WHERE id='" . escape ( $exam_id ) . "'";
$exam_info = Database::fetch_one_row ( $sql, TRUE, __FILE__, __LINE__ );

$htmlHeadXtra [] = Display::display_thickbox ();
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
Display::display_header ( null, FALSE );
if (isset ( $_GET ["message"] )) {
	Display::display_normal_message ( urldecode ( trim ( getgpc("message") ) ) );
}
$html = '<div id="demo" class="yui-navset">';
$html .= '<ul class="yui-nav">';
$html .= '<li><a href="../../exercice/exercise_admin.php?exerciseId=' . $exam_id . '"><em>1. ' . get_lang ( 'ModifyEx' ) . '</em></a></li>';
$html .= '<li><a href="../../exercice/admin.php?exerciseId=' . $exam_id . '"><em>2. ' . get_lang('QuestionList') . '</em></a></li>';
$html .= '<li  class="selected"><a href="have_arranged.php?exam_id=' . $exam_id . '"><em>3. ' . get_lang('ArrageExaminees') . '</em></a></li>';
$html .= '</ul>';
$html .= '<div class="yui-content"><div id="tab1">';
echo $html;

$form = new FormValidator ( 'search_simple', 'get', 'have_arranged.php?exam_id=' . $exam_id, '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{label}&nbsp;{element}</span> ' );

$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" );
$form->addElement ( 'text', 'keyword', $keyword_tip, array ('style' => "width:20%", 'class' => 'inputText', 'title' => $keyword_tip ) );

$depts = $objDept->get_sub_dept_ddl2 ( DEPT_TOP_ID, 'array' );
$form->addElement ( 'select', 'keyword_deptid', get_lang ( 'InDept' ), $depts, array ('id' => "dept_id", 'style' => 'height:22px;' ) );
$form->addElement ( 'hidden', 'exam_id', $exam_id );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );

echo '<div class="actions">';
echo '<span style="float:right; padding-top:5px;">';
echo link_button ( 'ods.gif', 'ArrageExaminees', 'tobe_arranged.php?exam_id=' . $exam_id, '90%', '92%' );
echo '</span>';
$form->display ();
echo '</div>';

$query_vars = array ();
$query_vars ['exam_id'] =  intval(getgpc ( "exam_id", "G") );
$sql_where = "";
if (isset ( $_GET ['keyword'] )) {
	$query_vars ['keyword'] = getgpc('keyword');
	$keyword = trim ( Database::escape_str ( $_GET ['keyword'], TRUE ) );
	if (! empty ( $keyword )) {
		$sql_where .= " AND  (t2.firstname LIKE '%" . $keyword . "%'  OR t2.username LIKE '%" . $keyword . "%')";
	}
}
if ($dept_id and ! is_equal ( $dept_id, 'null' )) {
	$query_vars ['keyword_deptid'] = $dept_id;
	$deptObj = new DeptManager ();
	$dept_sn = $deptObj->get_sub_dept_sn ( $dept_id );
	if ($dept_sn) $sql_where .= " AND t2.dept_sn LIKE '" . $dept_sn . "%'";
}

$table_header [] = array ("" );
$table_header [] = array (get_lang ( 'LoginName' ), true );
$table_header [] = array (get_lang ( 'FirstName' ), true );
$table_header [] = array (get_lang ( 'InOrg' ), true );
$table_header [] = array (get_lang ( 'InDept' ), true );
$table_header [] = array (get_lang ( 'JobTitle' ), true );
$table_header [] = array (get_lang ( 'ExamStartDate' ), true );
$table_header [] = array (get_lang ( 'ExamEndDate' ), true );

$datalist = get_exam_user_list ( $exam_id, $sql_where );
foreach ( $datalist as $data ) {
	$row = array ();
	$row [] = $data ['user_id'];
	$row [] = $data ['username'];
	$row [] = $data ['firstname'];
	$row [] = $data ['org_name'];
	$row [] = $data ['dept_name'];
	$row [] = $data ['lastname'];
	$row [] = $data ['available_start_date'];
	$row [] = $data ['available_end_date'];
	
	$table_data [] = $row;
}
unset ( $data, $row );

$sorting_options = array ('column' => 1, 'default_order_direction' => 'ASC' );
$actions = array ("batch_delete" => get_lang ( "BatchDelete" ) );
Display::display_sortable_table ( $table_header, $table_data, $sorting_options, array (), $query_vars, $actions );
echo '</div></div></div>';

echo '<div style="padding-top:15px;">';
echo '<div style="float:right;"><button name="cancle" class="cancel" onclick="javascript:self.parent.location.reload();self.parent.tb_remove();" type="button">' . get_lang ( 'Finish' ) . '</button></div>';
echo '<div style="float:right;"><button name="cancle" class="inputSubmit" onclick="javascript:location.href=\'../../exercice/admin.php?exerciseId=' . $exam_id . '\';" type="button">' . get_lang ( 'Previous' ) . '</button></div>';
echo '</div>';

Display::display_footer ();
