<?php
/*
 ==============================================================================
 INIT SECTION
 ==============================================================================
 */

// name of the language file that needs to be included
$language_file = array ('assignment', 'admin', 'class_of_course' );

require ('../inc/global.inc.php');
require_once ('assignment.lib.php');

api_protect_course_admin_script ();
$user_id = api_get_user_id ();
$course_code = api_get_course_code ();

$assignment_id = intval (getgpc("assignment_id") );
$id = intval (getgpc("id") );
$action = getgpc ( 'action' );
$display_type = getgpc ( 'display_type' );
$display_tool_options = getgpc ( 'display_tool_options' );
$display_assignment_form = getgpc ( 'display_assignment_form' );
$display_type = getgpc ( 'display_type' );
$web_work_dir = api_get_path ( WEB_COURSE_PATH ) . $course_code . '/assignment';
$sys_work_dir = api_get_path ( SYS_COURSE_PATH ) . $course_code . '/assignment';
$strAction = (isset ( $_GET ['action'] ) ? getgpc ( 'action', 'G' ) : 'display_type1');
$strType = (isset ( $_GET ['type'] ) ? getgpc ( 'type', 'G' ) : '');
$strActionType = (empty ( $strType ) ? 'action=' . $strAction . '&type=' . $strType : 'action=' . $strAction);
$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );

if (is_equal ( $_GET ['todo'], 'get_class_users' )) {
	header ( "Content-Type: text/xml;charset=UTF-8" );
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><student>";
	$class_id = intval(getgpc ( 'class_id', 'G' ));
	$class_users = CourseClassManager::get_user_with_class ( $class_id, STUDENT );
	if ($class_users && is_array ( $class_users )) {
		foreach ( $class_users as $tmp_user_id => $tmp_user ) {
			$xml .= "<name id='" . $tmp_user_id . "'>" . $tmp_user ['firstname'] . "</name>";
		}
	}
	echo $xml . '</student>';
	exit ();
}

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();

if ($action == "display_type3") {
	$htmlHeadXtra [] = "<script type=\"text/javascript\">
$(document).ready(function (){ 
$(\"#search_filter\").attr('disabled','true');
$('#course_class').change(function (){ 
	$.ajax({ 
			url:'" . $_SERVER ['PHP_SELF'] . "', 
			type:'get', 
			dataType:'xml', 
			data:'action=display_type3&todo=get_class_users&class_id='+$(\"select[@id='course_class'] option[@selected]\").val()+'&date='+(new Date()).getTime(),   
			error:function(json){alert(\"Get Data Error!\");},
  			success: function(xml){
      			$(\"#user_in_class\").html('');
      			if($(\"select[@id='course_class'] option[@selected]\").val()=='-1'){
      			 	$(\"#user_in_class\").attr('disabled','true');
      			 	$(\"#search_filter\").attr('disabled','true');
      			}
 				$(xml).find(\"name\").each(function(){ 
  					var id=$(this).attr(\"id\");
  					var name=$(this).text(); 
    				$('<option value='+id+'>'+name+'</option>').appendTo('#user_in_class'); 
    				$(\"#user_in_class\").attr('disabled','');  
    				$(\"#search_filter\").attr('disabled','');  				
        		});
  			}
	}); 
});
}); 
</script>";
}

Display::display_header ( null, false );

//课程管理员工具链接
echo display_action_links ();

$myTools ['display_type1'] = get_lang ( 'AssignmentReporingSummary' );
$myTools ['display_type2'] = get_lang ( 'AssignmentStudentSubReporting' );
$myTools ['display_type3'] = get_lang ( 'AssignmentSingleStudentSubReporting' );

$tab_html = '<div id="demo" class="yui-navset" style="margin:10px">';
$tab_html .= '<ul class="yui-nav">';
foreach ( $myTools as $key => $value ) {
	$strClass = ($strAction == $key ? 'class="selected"' : '');
	$tab_html .= '<li  ' . $strClass . '><a href="reporting.php?action=' . $key . '&id=' . $id . '"><em>' . $value . '</em></a></li>';
}
$tab_html .= '</ul>';
$tab_html .= '<div class="yui-content"><div id="tab1">';
echo $tab_html;

$sql = "select count(user_id) as cnt from " . $tbl_course_user . " where course_code='" . $course_code . "' and status=" . STUDENT . "  and user_id not in (select user_id from " . Database::get_main_table ( TABLE_MAIN_USER ) . " WHERE is_admin=1)";
$total_studnet_count = Database::get_scalar_value ( $sql );

if ($action == "display_type1") {
	$sql = "select count(user_id) as cnt from " . $tbl_course_user . " where course_code='" . $course_code . "'";
	$total_course_user = Database::get_scalar_value ( $sql );
	
	$sql = "select count(*) as cnt from " . $assignment_table . " WHERE cc='" . $course_code . "'";
	$sumOfCreation = Database::get_scalar_value ( $sql );
	
	$sql = "select count(*) as cnt from " . $assignment_table . " where is_published=1 AND cc='" . $course_code . "'";
	$sumOfPublished = Database::get_scalar_value ( $sql );
	
	$sql = "select count(*) as cnt from " . $assignment_submission_table . " WHERE cc='" . $course_code . "'";
	$sumOfSumbmision = Database::get_scalar_value ( $sql );
	//if ($sumOfCreation > 0) $percentageOfPublished = round ( $sumOfPublished * 100 / $sumOfCreation, 2 );
	$table_header = null;
	$table_data [] = array (get_lang ( 'SumOfCourseUser' ), $total_course_user );
	$table_data [] = array (get_lang ( 'SumOfStudentCount' ), $total_studnet_count );
	$table_data [] = array (get_lang ( 'SumOfCreation' ), $sumOfCreation );
	$table_data [] = array (get_lang ( 'SumOfPublished' ), $sumOfPublished );
	$table_data [] = array ('学生提交作业总次数', $sumOfSumbmision );
	$table_data [] = array ('平均每次作业学生提交数', ($sumOfPublished > 0 ? round ( $sumOfSumbmision / $sumOfPublished, 1 ) : 0) );
	echo Display::display_table ( $table_header, $table_data );
} else if ($action == "display_type2") {
	$table_header = array ();
	$table_header [] = array (get_lang ( 'TitleWork' ) );
	$table_header [] = array (get_lang ( 'CreationTime' ) );
	$table_header [] = array (get_lang ( 'PublishedTime' ) );
	$table_header [] = array (get_lang ( 'Deadline' ) );
	$table_header [] = array (get_lang ( 'SubmitedUserCount' ) );
	$table_header [] = array (get_lang ( 'UnSubmitedUserCount' ) );
	$table_header [] = array (get_lang ( 'SumOfAttachement' ) );
	$table_header [] = array (get_lang ( 'FileSizeOfAttachment' ) . '(KB)' );
	$table_header [] = array (get_lang ( 'SumOfFeedback' ) );
	$table_header [] = array (get_lang ( 'AverageScore' ) );
	
	$sql1 = "select t1.id,t1.title,t1.creation_time,t1.deadline,t1.published_time,t1.attachment_size,t1.attachment_uri
	from " . $assignment_table . " t1 where is_published=1 AND cc='" . $course_code . "' ";
	$sql1 .= "order by published_time";
	$result1 = api_sql_query ( $sql1, __FILE__, __LINE__ );
	$rowIndex = 0;
	while ( $row1 = Database::fetch_array ( $result1, 'ASSOC' ) ) {
		$table_row = array ();
		$submitted_user_count = get_submited_user_count ( $row1 ['id'] );
		$table_row [] = $row1 ['title'];
		$table_row [] = substr ( $row1 ['creation_time'], 0, 16 );
		$table_row [] = substr ( $row1 ['published_time'], 0, 16 );
		$table_row [] = substr ( $row1 ['deadline'], 0, 16 );
		$table_row [] = $total_studnet_count - $submitted_user_count;
		
		$table_row [] = $submitted_user_count;
		
		//附件总大小
		$sql = "select sum(attachment_size) as sm from " . $assignment_submission_table . " where assignment_id='" . $row1 ['id'] . "' AND cc='" . $course_code . "' ";
		$total_attachment_size = Database::getval ( $sql ) + $row1 ['attachment_size'];
		
		$sql = "select  sum(attachment_size) as sm from " . $assignment_feedback_table . " where submission_id in (select id from " . $assignment_submission_table . " where assignment_id='" . $row1 ['id'] . "' AND cc='" . $course_code . "')";
		$total_attachment_size += Database::getval ( $sql );
		$total_attachment_size = round ( $total_attachment_size / 1024 );
		
		//附件总数
		$sql = "select count(*) as cnt from " . $assignment_submission_table . " where assignment_id='" . $row1 ['id'] . "' and attachment_uri is not NULL and attachment_name is not NULL AND cc='" . $course_code . "'";
		$table_row [] = $total_attachment_count = Database::getval ( $sql );
		$table_row [] = $total_attachment_size;
		
		$sql = "select  count(*) as cnt from " . $assignment_feedback_table . " where submission_id in (select id from " . $assignment_submission_table . " where assignment_id='" . $row1 ['id'] . "') " . "and attachment_uri is not NULL and attachment_name is not NULL";
		$sql .= " AND cc='" . $course_code . "'";
		$total_attachment_count += Database::get_scalar_value ( $sql );
		if (isset ( $row1 ['attachment_uri'] ) && ! empty ( $row1 ['attachment_uri'] )) $total_attachment_count ++;
		$table_row [] = $total_attachment_count;
		
		//已批改作业数
		$sql = "select  count(*) as cnt from " . $assignment_feedback_table . " where submission_id in (select id from " . $assignment_submission_table . " where assignment_id='" . $row1 ['id'] . "' and status=1) " . "and score>0";
		$sql .= "  AND cc='" . $course_code . "'";
		$table_row [] = $total_feedback_count = Database::getval ( $sql );
		
		//已批改完的学生作业平均分
		$sql = "select  avg(score) as avg_score from " . $assignment_feedback_table . " where submission_id in(select id from " . $assignment_submission_table . " where assignment_id='" . $row1 ['id'] . "' and status=1) " . "and score>0";
		$sql .= " AND cc='" . $course_code . "'";
		$averageScore = Database::getval ( $sql );
		$table_data [] = $table_row;
	}
	echo Display::display_table ( $table_header, $table_data );
} //END type=2


else if ($action == "display_type3") {
	
	$form = new FormValidator ( 'search_simple', 'get', '', '', null, false );
	$form->addElement ( 'hidden', 'action', 'display_type3' );
	$renderer = $form->defaultRenderer ();
	$renderer->setElementTemplate ( '<span>{element}</span> ' );
	$modaldialog_select_options = array ('is_multiple_line' => false, 'MODULE_ID' => 'COURSE_SINGLE_USER', 'open_url' => api_get_path ( WEB_CODE_PATH ) . "commons/modal_frame.php?", 'form_name' => 'search_simple', 'TO_NAME' => 'TO_NAME', 'TO_ID' => 'TO_ID' );
	$form->addElement ( 'modaldialog_select', 'student', get_lang ( 'CourseTeachers' ), NULL, $modaldialog_select_options );
	$form->addElement ( 'submit', 'submit', get_lang ( 'Search' ), 'class="inputSubmit"' );
	echo '<div class="actions">';
	$form->display ();
	echo '</div>';
	
	
	$table_header = array ();
	$table_header [] = array (get_lang ( 'TitleWork' ) );
	$table_header [] = array (get_lang ( 'Deadline' ) );
	$table_header [] = array (get_lang ( 'SubmitTime' ) );
	$table_header [] = array (get_lang ( 'AssignmentStatus' ) );
	$table_header [] = array (get_lang ( 'Score' ) );
	
	$student_id = $_REQUEST ['student'] ['TO_ID'];
	if (is_not_blank ( $student_id )) {
		$sql = "SELECT t.id,t.title,t.deadline,t.last_edit_time,t3.score,t.status,
					CASE status WHEN 1 THEN '" . get_lang ( 'Feedbacked' ) . "' 
									WHEN 0 THEN '" . get_lang ( 'Feedbacking' ) . "' 
									WHEN -1 THEN '" . get_lang ( 'ToFeedback' ) . "' 
									ELSE '" . get_lang ( 'Unknown' ) . "' END as status_desc
					FROM	(SELECT t1.id,t1.title,t1.deadline,t2.last_edit_time,t2.id as id2,t2.status FROM " . $assignment_table . " t1 ,
					" . $assignment_submission_table . " t2
					WHERE  t1.id=t2.assignment_id
					and t2.student_id='" . escape ( $student_id ) . "' 
					and t1.is_published=1 and t1.assignment_type='INDIVIDUAL' AND t1.cc='" . $course_code . "' ) as t
					left join " . $assignment_feedback_table . " t3 on t.id2=t3.submission_id";
		//echo $sql;
		$result3 = api_sql_query ( $sql, __FILE__, __LINE__ );
		$tmp_user_count = 0;
		$tmp_total_score = 0;
		while ( $row3 = Database::fetch_array ( $result3, 'ASSOC' ) ) {
			$table_row = array ();
			if ($row3 ['status'] == 1) {
				$tmp_user_count ++;
				$tmp_total_score += $row3 ['score'];
			}
			$table_row [] = $row3 ['title'];
			$table_row [] = substr($row3 ['deadline'],0,16);
			$table_row [] = substr($row3 ['last_edit_time'],0,16);
			$table_row [] = $row3 ['status_desc'];
			$table_row [] = $row3 ['score'];
			$table_data [] = $table_row;
		} //end while
		echo Display::display_table ( $table_header, $table_data );
		if (Database::num_rows ( $result3 ) > 0) echo '<div style="float:right"><b> ' . get_lang ( 'AverageScore' ) . ':</b> ' . ($tmp_user_count != 0 ? $tmp_total_score / $tmp_user_count : '无法计算') . '</div>';
	} else{
		echo '<br/>';
		Display::display_confirmation_message('请选择一个具体的学员再查看其相关信息');
	}
} 

//按课程班级统计学生的作业情况
else if ($action == "display_type4") {

} //end display_type3


echo '</div></div></div>';
Display::display_footer ();
