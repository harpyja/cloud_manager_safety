<?php
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

$action = (isset ( $_REQUEST ['action'] ) ? getgpc ( 'action' ) : "");
$exam_id = (isset ( $_REQUEST ['exam_id'] ) ? intval ( getgpc ( 'exam_id' ) ): "");
$dept_id = isset ( $_GET ['keyword_deptid'] ) ? intval ( getgpc ( 'keyword_deptid', 'G') ) : '0';

api_session_unregister ( 'objExercise' );
api_session_unregister ( 'objQuestion' );
api_session_unregister ( 'objAnswer' );
api_session_unregister ( 'questionList' );
api_session_unregister ( 'exerciseResult' );

$choice = getgpc ( 'choice' );
$exerciseId = escape ( intval ( getgpc ( 'exerciseId' )) );
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

$redirect = 'main/exercice/course_exam_list.php';
if (! empty ( $choice )) { //管理操作
	$objExerciseTmp = new Exercise ();
	
	if ($objExerciseTmp->read ( $exerciseId )) {
		switch ($choice) {
			case 'delete' : // 删除测验
				$objExerciseTmp->delete ( FALSE );
				Display::display_msgbox ( get_lang ( 'ExerciseDeleted' ), $redirect );
				break;
			case 'enable' : // 显示
				if ($objExerciseTmp->selectNbrQuestions ()) {
					$objExerciseTmp->enable ();
					$objExerciseTmp->save ();
					Display::display_msgbox ( get_lang ( 'VisibilityChanged' ), $redirect );
				} else {
					Display::display_msgbox ('没有设置题目的考试不允许发布!', $redirect,'warning' );
				}
				break;
			case 'disable' : // 隐藏
				$objExerciseTmp->disable ();
				$objExerciseTmp->save ();
				//api_item_property_update ( $_course, TOOL_QUIZ, $exerciseId, "invisible" );
				Display::display_msgbox ( get_lang ( 'VisibilityChanged' ), $redirect );
				break;
			case 'del_result' :
				$exe_id = intval ( getgpc ( 'exe_id' ));
				$objExerciseTmp->del_exercise_tracking ( $exe_id );
				api_redirect ( "course_exam_list.php?show=result&exerciseId=" . $exerciseId );
				break;
		}
	}
	unset ( $objExerciseTmp );
}

$htmlHeadXtra [] = Display::display_thickbox ();
$nameTools = get_lang ( 'Exercices' );
Display::display_header ( $nameTools,$show == 'test' );

if ($show == 'test') { //测验试卷列表
	display_quiz_list ();
} elseif ($show == 'result') { //显示学生提交测验的结果列表
	display_result_list ( $exerciseId );
}

function display_quiz_list() {
	global $TBL_EXERCICES, $TBL_EXERCICE_QUESTION;
	
	$form = new FormValidator ( 'search_simple' );
	$renderer = $form->defaultRenderer ();
	$renderer->setElementTemplate ( '<span>{label}&nbsp;{element}</span> ' );
	
	$keyword_tip = get_lang ( 'ExerciseName' );
	$form->addElement ( 'text', 'keyword', $keyword_tip, array ('style' => "width:20%", 'class' => 'inputText', 'title' => $keyword_tip ) );
	$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );
	//顶部链接
	echo '<div class="actions">';
	$form->display ();
	echo '</div>';
	
	$sql = "SELECT id,title,type,active,description,max_attempt,max_duration,cc FROM $TBL_EXERCICES AS ce
			WHERE active<>'-1' AND type=2 ORDER BY display_order";
	//echo $sql;
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	
	//$table_header [] = array ("", 'width="30"' );
	$table_header [] = array (get_lang ( 'ExerciseName' ) );
	//$table_header [] = array (get_lang ( 'ExamProperty' ) );
	$table_header [] = array (get_lang ( 'QuizAllowedDuration' ), 'width="80"' );
	$table_header [] = array (get_lang ( 'ExerciseAttempts' ), 'width="120"' );
	$table_header [] = array (get_lang ( 'QuestionCount' ), null, array ('width' => "80" ) );
	$table_header [] = array (get_lang ( 'QuizTotalScore' ), 'width="80"' );
	//$table_header[] = array(get_lang('AverageScore'),true);
	$table_header [] = array (get_lang ( 'isPublishedNow' ), 'width="80"' );
	$table_header [] = array (get_lang ( 'Preview' ), 'width="30"' );
	$table_header [] = array (get_lang ( 'Actions' ), false, null, array ('width' => "90" ) );
	
	//$total_rows=Database::num_rows($result);
	while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
		$tbl_row = array ();
		//$tbl_row [] = $row ['id'];
		

		//原来exercice_submit.php
		//$tbl_row [] = '<a href="exercice_intro.php?' . api_get_cidreq () . "&exerciseId=" . $row ['id'] . '" ' . (! $row ['active'] ? ' class="invisible"' : "") . ">" . $row ['title'] . '</a>';
		$tbl_row [] = '<span ' . (! $row ['active'] ? ' class="invisible"' : "") . ">" . $row ['title'] . '</span>';
		/*		if ($row ['type'] == 1) {
			$tbl_row [] = get_lang ( 'ExamProperty1' );
		} else {
			$sql = "SELECT title FROm " . Database::get_main_table ( TABLE_MAIN_COURSE ) . " WHERE code=" . Database::escape ( $row ['cc'] );
			$course_title = Database::get_scalar_value ( $sql );
			$tbl_row [] = get_lang ( 'ExamProperty2' ) . ' (' . $course_title . ')';
		}*/
		
		$tbl_row [] = $row ['max_duration'] == 0 ? get_lang ( "Infinite" ) : ($row ['max_duration'] / 60) . "&nbsp;" . get_lang ( "Minites" );
		
		$tbl_row [] = ($row ['max_attempt'] == 0 ? get_lang ( "Infinite" ) : $row ['max_attempt']);
		
		$sqlquery = "SELECT count(*) FROM $TBL_EXERCICE_QUESTION WHERE `exercice_id` = '" . $row ['id'] . "'";
		$questionCount = Database::get_scalar_value ( $sqlquery );
		$tbl_row [] = $questionCount;
		
		$tbl_row [] = Exercise::get_quiz_total_score ( $row ['id'] );
		//$tbl_row[]=get_average_score($row['id']);
		

		if ($row ['active']) {
			//$visible_html = '<a href="exercice.php?choice=disable&exerciseId='.$row['id'].'">'. Display::return_icon('right.gif', get_lang('Deactivate')).'</a> ';
			$visible_html = Display::return_icon ( 'right.gif', get_lang ( 'QuizPublished' ) ) . "&nbsp;" . get_lang ( 'QuizPublished' );
		} else {
			$visible_html = '<a href="course_exam_list.php?choice=enable&exerciseId=' . $row ['id'] . '" onclick="return confirm(\'' . get_lang ( "QuizPublishConfirm" ) . '\');">' . Display::return_icon ( 'wrong.gif', get_lang ( 'ClickToPublishQuiz' ) ) . '</a>';
		}
		$tbl_row [] = $visible_html;
		
		//预览
		$tbl_row [] = $questionCount > 0 ? '&nbsp;&nbsp;' . link_button ( 'preview.gif', 'Preview', 'exercise_preview.php?type=exercise&id=' . $row ["id"], '94%', '86%', FALSE ) : '';
		//$tbl_row [] = $questionCount > 0 ? '&nbsp;&nbsp;' . icon_href( 'preview.gif', 'Preview', 'exercise_preview.php?type=exercise&id=' . $row ["id"],'_blank') : '';
		

		$action_html = "";
		//V2.4
		/*if ($row ['active'] != 1) {
			$action_html .= '&nbsp;&nbsp;' . icon_href ( 'wizard.gif', 'BuildQuiz', 'admin.php?exerciseId=' . $row ["id"] );
		} else {
			$action_html .= '&nbsp;&nbsp;' . Display::return_icon ( 'wizard_gray.gif', get_lang ( 'BuildQuiz' ), array ('style' => 'vertical-align: middle;' ) );
		}*/
		
		//安排考生
		//$action_html .= '&nbsp;&nbsp;' . link_button ( 'edit_group.gif', 'ArrageExaminees', '../exam/manage/have_arranged.php?exam_id=' . $row ['id'], '94%', '90%', FALSE );
		

		//编辑
		$action_html .= '&nbsp;&nbsp;' . link_button ( 'exercise22.png', 'ExamInfoSetting', 'exercise_admin.php?modifyExercise=yes&exerciseId=' . $row ["id"], '90%', '80%', FALSE );
		
		//删除
		$action_html .= '&nbsp;&nbsp;<a href="course_exam_list.php?choice=delete&exerciseId=' . $row [id] . '"
		onclick="javascript:if(!confirm(\'' . get_lang ( 'AreYouSureToDelete' ) . " " . $row ['title'] . '?\')) return false;">' . Display::return_icon ( 'delete.gif', get_lang ( 'Delete' ), 
				array ('style' => 'vertical-align: middle;' ) ) . '</a>';
		
		$action_html .= '&nbsp;&nbsp;' . link_button ( 'statistics.gif', 'ExamResultQuery', 'course_exam_list.php?show=result&exerciseId=' . $row ["id"], '90%', '90%', FALSE );
		
		$tobecorrect_user_count = Exercise::stat_exam_tobecorrect_user_count ( $row ["id"] );
		if ($tobecorrect_user_count > 0) {
			$action_html .= '&nbsp;&nbsp;' . link_button ( 'plugin.gif', 'ExamSubPapers', '../exam/manage/tobe_corrected.php?exam_id=' . $row ["id"], '96%', '96%', FALSE ) . '(' . $tobecorrect_user_count . ')';
		}
		
		$tbl_row [] = $action_html;
		$table_data [] = $tbl_row;
	}
	$sorting_options = array ('column' => 0, 'default_order_direction' => 'DESC' );
	$query_vars = array ('keyword' => getgpc ( 'keyword' ) );
	//$form_actions = array ('batch_delete' => get_lang ( "BatchDelete" ) );
	Display::display_sortable_table ( $table_header, $table_data, $sorting_options, array (), $query_vars );
}

/**
 * 教师角度: 只显示本班学生的测验情况
 */
function display_result_list($exerciseId = 0) {
	global $_configuration, $_cid;
	$dept_id = isset ( $_GET ['keyword_deptid'] ) ? getgpc ( 'keyword_deptid', 'G' ) : '0';
	
	$objDept = new DeptManager ();
	/*	$all_org = $objDept->get_all_org ();
	$orgs [''] = get_lang ( 'All' );
	foreach ( $all_org as $org ) {
		$orgs [$org ['id']] = $org ['dept_name'];
	}*/
	$query_vars = array ();
	$query_vars ['show'] = 'result';
	$query_vars ['exerciseId'] = $exerciseId;
	if (is_not_blank ( getgpc ( 'keyword' ) )) $query_vars ['keyword'] = getgpc ( 'keyword' );
	$query_vars ['keyword_deptid'] = $dept_id;
	
	$form = new FormValidator ( 'search_simple', 'get', 'exercice.php', '', '_self', false );
	$form->addElement ( 'hidden', 'show', 'result' );
	$form->addElement ( 'hidden', 'exerciseId', $exerciseId );
	$renderer = $form->defaultRenderer ();
	$renderer->setElementTemplate ( '<span>{label}&nbsp;{element}</span> ' );
	$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" );
	$form->addElement ( 'text', 'keyword', $keyword_tip, array ('style' => "width:20%", 'class' => 'inputText', 'title' => $keyword_tip ) );
	
	//$form->addElement ( 'select', 'keyword_orgid', get_lang ( 'InOrg' ), $orgs, array ('id' => "org_id", 'style' => 'height:22px;', 'title' => get_lang ( 'InOrg' ) ) );
	$depts = $objDept->get_sub_dept_ddl2 ( DEPT_TOP_ID, 'array' );
	$form->addElement ( 'select', 'keyword_deptid', get_lang ( 'InDept' ), $depts, array ('id' => "dept_id", 'style' => 'height:22px;min-width:120px' ) );
	
	$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );
	//$form->addElement('style_button', 'cancle',null,array('type'=>'button','class'=>"cancel",'value'=>get_lang('Cancel'),'onclick'=>'javascript:self.parent.tb_remove();'));
	$url = api_add_url_querystring ( 'exercice.php?choice=export', $query_vars );
	
	echo '<div class="actions">';
	echo '<span style="float:right; padding-top:5px;">', link_button ( 'excel.gif', 'Export', $url ), '</span>';
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
}

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
	$dept_id = isset ( $_GET ['keyword_deptid'] ) ? intval ( getgpc ( 'keyword_deptid', 'G' )) : '0';
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

Display::display_footer ( $show == 'test' );
