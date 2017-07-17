<?php
/**----------------------------------------------------------------
exercise_result.php
 liyu: 2011-10-27
 *----------------------------------------------------------------*/

/*
 测验列表
 */
include_once ('exercise.class.php');
include_once ('question.class.php');
include_once ('answer.class.php');
$cidReset = TRUE;
$language_file = array ('exercice', 'admin' );
include_once ('../inc/global.inc.php');
include_once ('exercise.lib.php');

api_protect_quiz_script ();

require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$TBL_USER = Database::get_main_table ( TABLE_MAIN_USER );
$TBL_ITEM_PROPERTY = Database::get_course_table ( TABLE_ITEM_PROPERTY );

$show = (isset ( $_GET ['show'] ) && $_GET ['show'] == 'result') ? 'result' : 'test';
$type = (isset ( $_GET ['type'] ) ? getgpc ( 'type', 'G' ) : 1);
$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( 'action' ) : "");
$exam_id = (isset ( $_REQUEST ['exam_id'] ) ? getgpc ( 'exam_id' ) : "");
$exerciseId = escape ( getgpc ( 'exerciseId' ) );
$dept_id = isset ( $_GET ['keyword_deptid'] ) ? getgpc ( 'keyword_deptid', 'G' ) : '0';
$choice = getgpc ( 'choice' );
$objDept = new DeptManager ();
api_session_unregister ( 'objExercise' );
api_session_unregister ( 'objQuestion' );
api_session_unregister ( 'objAnswer' );
api_session_unregister ( 'questionList' );
api_session_unregister ( 'exerciseResult' );

if ($exerciseId) {
	$sql = "SELECT title FROM $TBL_EXERCICES WHERE id='" . $exerciseId . "'";
	$exerciseTitle = Database::get_scalar_value ( $sql );
}

if (is_equal ( $_GET ['choice'], 'export' )) {
	require_once (api_get_path ( LIBRARY_PATH ) . 'export.lib.inc.php');
	$data_header = array (get_lang ( 'FirstName' ), get_lang ( 'LoginName' ), get_lang ( 'OfficialCode' ), get_lang ( 'UserInDept' ), get_lang ( 'ExamStartDate' ), get_lang ( 'AnswerTime' ), get_lang ( 'Score' ), get_lang ( 'StudentScore' ) );
	$export_data = get_result_date ( $exerciseId, TRUE );
	//var_dump($export_data);exit;
	$filename = '在线考试_' . $exerciseTitle . '_' . date ( 'Ymd' ); //导出文件名
	array_unshift ( $export_data, $data_header );
	Export::export_data ( $export_data, $filename, 'xls' );
}

$redirect = 'main/exercice/exercise_result.php?type=' . $type;
if (! empty ( $choice )) { //管理操作
	$objExerciseTmp = new Exercise ();
	if ($objExerciseTmp->read ( $exerciseId )) {
		switch ($choice) {
			case 'del_result' :
				$exe_id = getgpc ( 'exe_id' );
				$objExerciseTmp->del_exercise_tracking ( $exe_id );
				api_redirect ( "exercice.php?show=result&exerciseId=" . $exerciseId . '&type=' . $type );
				break;
		}
	}
	unset ( $objExerciseTmp );
}

//$htmlHeadXtra [] = Display::display_thickbox ();
$nameTools = get_lang ( 'Exercices' );
Display::display_header ( $nameTools, FALSE );

//显示学生提交测验的结果列表
$query_vars = array ();
$query_vars ['show'] = 'result';
$query_vars ['exerciseId'] = $exerciseId;
$query_vars ['keyword_deptid'] = $dept_id;
if (is_not_blank ( $_REQUEST ['keyword'] )) $query_vars ['keyword'] = getgpc ( 'keyword' );
if (is_not_blank ( $_REQUEST ['type'] )) $query_vars ['type'] = getgpc ( 'type' );

$form = new FormValidator ( 'search_simple', 'get' );
$form->addElement ( 'hidden', 'show', 'result' );
$form->addElement ( 'hidden', 'exerciseId', $exerciseId );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{label}&nbsp;{element}</span> ' );
$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" );
$form->addElement ( 'text', 'keyword', $keyword_tip, array ('style' => "width:20%", 'class' => 'inputText', 'title' => $keyword_tip ) );

$depts = $objDept->get_sub_dept_ddl2 ( DEPT_TOP_ID, 'array' );
$form->addElement ( 'select', 'keyword_deptid', get_lang ( 'InDept' ), $depts, array ('id' => "dept_id", 'style' => 'height:22px;min-width:120px' ) );

$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );
//$form->addElement('style_button', 'cancle',null,array('type'=>'button','class'=>"cancel",'value'=>get_lang('Cancel'),'onclick'=>'javascript:self.parent.tb_remove();'));
$url = api_add_url_querystring ( 'exercice.php?choice=export', $query_vars );

echo '<div class="actions">';
//echo '<span style="float:right; padding-top:5px;">', link_button ( 'excel.gif', 'Export', $url ), '</span>';
$form->display ();
echo '</div>';

$table_header [] = array (get_lang ( 'FirstName' ), true );
$table_header [] = array (get_lang ( 'LoginName' ), true );
$table_header [] = array (get_lang ( 'OfficialCode' ), true );
$table_header [] = array (get_lang ( 'InOrg' ) . '/' . get_lang ( 'InDept' ), true );
$table_header [] = array (get_lang ( 'ExamStartDate' ), true );
$table_header [] = array (get_lang ( 'AnswerTime' ), true );
$table_header [] = array (get_lang ( 'Score' ), true );
$table_header [] = array (get_lang ( 'StudentScore' ), false );
$table_header [] = array (get_lang ( 'Actions' ), false );

$table_data = array ();
$data_list = get_result_date ( $exerciseId );
foreach ( $data_list as $data ) {
	$table_data [] = $data;
}
$sorting_options = array ('column' => 0, 'default_order_direction' => 'ASC' );
Display::display_sortable_table ( $table_header, $table_data, $sorting_options, array (), $query_vars, array (), NAV_BAR_BOTTOM );

function get_result_date($exerciseId, $is_export = FALSE) {
	global $TBL_TRACK_EXERCICES;
	$tbl_user_dept = Database::get_main_table ( VIEW_USER_DEPT );
	$tbl_exam_rel_user = Database::get_main_table ( TABLE_MAIN_EXAM_REL_USER ); //考试用户关联表
	$sql = "SELECT t1.*,t3.username,t3.firstname,t3.lastname,t3.official_code,CONCAT(t3.org_name,'/',t3.dept_name) AS deptname FROM $TBL_TRACK_EXERCICES AS t1," . $tbl_user_dept . " AS t3 WHERE exe_exo_id=" . Database::escape ( $exerciseId ) . " AND t1.exe_user_id=t3.user_id";
	$sql .= get_result_sqlwhere ();
	$sql .= " ORDER BY t1.start_date,t3.username DESC";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	$table_data = array ();
	while ( $data = Database::fetch_array ( $result, 'ASSOC' ) ) {
		$row = array ();
		$row [] = $data ['firstname'];
		$row [] = $data ['username'];
		$row [] = $data ['official_code'];
		$row [] = $data ['deptname'];
		$row [] = substr ( $data ['start_date'], 0, 16 );
		$row [] = api_time_to_hms ( $data ['exe_duration'] );
		$row [] = $data ['exe_result'];
		$row [] = $data ['exe_weighting'] > 0 ? round ( $data ['exe_result'] * 100 / $data ['exe_weighting'] ) : 0;
		if ($is_export == false) $row [] = icon_href ( 'scorm_fullscreen.gif', 'ExamViewSub', '../exam/exam_view.php?result_id=' . $data ['exe_id'] . "&exam_id=" . $exerciseId, '_blank' );
		$table_data [] = $row;
	}
	return $table_data;
}

function get_result_sqlwhere() {
	$objDept = new DeptManager ();
	$dept_id = isset ( $_GET ['keyword_deptid'] ) ? getgpc ( 'keyword_deptid', 'G' ) : '0';
	$sql_where = "";
	if (is_not_blank ( $_GET ['keyword'] )) {
		$query_vars ['keyword'] = getgpc ( 'keyword', 'G' );
		$keyword = trim ( Database::escape_str ( getgpc ( 'keyword', 'G' ), TRUE ) );
		if (! empty ( $keyword )) {
			$sql_where .= " AND  (t3.firstname LIKE '%" . $keyword . "%'  OR t3.username LIKE '%" . $keyword . "%')";
		}
	}
	if ($dept_id and ! is_equal ( $dept_id, 'null' )) {
		$query_vars ['keyword_deptid'] = $dept_id;
		$dept_sn = $objDept->get_sub_dept_sn ( $dept_id );
		if ($dept_sn) $sql_where .= " AND t3.dept_sn LIKE '" . $dept_sn . "%'";
	}
	return $sql_where;
}

Display::display_footer ();
