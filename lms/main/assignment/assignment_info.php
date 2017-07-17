<?php

$language_file = array ('assignment', 'admin', 'class_of_course' );
require ('../inc/global.inc.php');
require_once ('assignment.lib.php');
api_block_anonymous_users ();
api_protect_course_admin_script ();
$is_allowed_to_edit = api_is_allowed_to_edit ();
$user_id = api_get_user_id ();
$course_code = api_get_course_code ();

$id = intval (getgpc("id") );
$assignment_id = intval (getgpc("assignment_id") );
$action = getgpc ( 'action' );
$delete = getgpc ( 'delete' );
$strType = (isset ( $_GET ['type'] ) ? getgpc ( 'type', 'G' ) : '');
$strAction = (isset ( $_GET ['action'] ) ? getgpc ( 'action', 'G' ) : 'assign_info');
$strActionType = (empty ( $strType ) ? 'action=' . $strAction . '&type=' . $strType : 'action=' . $strAction);
$display_assignment_form = getgpc("display_assignment_form");

$base_work_dir = api_get_path ( SYS_COURSE_PATH ) . $course_code . '/assignment';
$http_www = api_get_path ( WEB_COURSE_PATH ) . $course_code . '/assignment';

//下载附件
if (isset ( $_GET ['action'] )) {
	switch (getgpc ( 'action', 'G' )) {
		case 'download' :
			$res = get_assignment_info ( $id );
			if ($file_row = Database::fetch_array ( $res, 'ASSOC' )) {
				$download_name = $file_row ['attachment_name'];
				$full_file_name = $base_work_dir . "/" . $id . "/" . $file_row ['attachment_uri'];
				DocumentManager::file_send_for_download ( $full_file_name, true, $download_name );
				exit ();
			} else {
				header ( "HTTP/1.0 404 Not Found" );
				$error404 = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">';
				$error404 .= '<html><head>';
				$error404 .= '<title>404 Not Found</title>';
				$error404 .= '</head><body>';
				$error404 .= '<h1>Not Found</h1>';
				$error404 .= '<p>The requested URL was not found on this server.</p>';
				$error404 .= '<hr>';
				$error404 .= '</body></html>';
				echo ($error404);
				exit ();
			}
			break;
	}
}

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();

Display::display_header ( null, FALSE );

//echo display_action_links ();


if ($is_allowed_to_edit) { //课程管理员,教师视图
	echo display_action_links ();
	
	$myTools ['assign_info'] = get_lang ( 'AssignmentInfo' );
	$myTools ['assign_sub_list'] = get_lang ( 'AssignmentStudentSubList' );
	$myTools ['assign_unsub_userlist'] = get_lang ( 'AssignmentStudentNotSubList' );
	
	$tab_html = '<div id="demo" class="yui-navset" style="margin:10px">';
	$tab_html .= '<ul class="yui-nav">';
	foreach ( $myTools as $key => $value ) {
		$strClass = ($strAction == $key ? 'class="selected"' : '');
		$tab_html .= '<li  ' . $strClass . '><a href="assignment_info.php?action=' . $key . '&id=' . $id . '"><em>' . $value . '</em></a></li>';
	}
	$tab_html .= '</ul>';
	$tab_html .= '<div class="yui-content"><div id="tab1">';
	echo $tab_html;
	
	switch ($strAction) {
		case 'assign_info' :
			$result = get_assignment_info ( $id );
			if ($row = Database::fetch_array ( $result )) {
				$table_data = array ();
				$table_data [] = array ('<b>' . get_lang ( 'TitleWork' ) . '</b>', $row ['title'] );
				$table_data [] = array ('<b>' . get_lang ( 'Authors' ) . '</b>', $row ['author'] );
				$table_data [] = array ('<b>' . get_lang ( 'Deadline' ) . '</b>', $row ['deadline'] );
				$table_data [] = array ('<b>' . get_lang ( 'IsAllowedSubLate' ) . '</b>', $row ['is_allow_late_submission'] == 1 ? get_lang ( 'AllowLateSub' ) : get_lang ( 'NotAllowLateSub' ) );
				$table_data [] = array ('<b>' . get_lang ( 'AssignmentType' ) . '</b>', $row ['assignment_type'] == 'INDIVIDUAL' ? get_lang ( 'IndividualWork' ) : get_lang ( 'GroupWork' ) );
				
				if ($row ['priv_status'] == 0) $priv_status_txt = get_lang ( 'AssignmentPriv0' );
				if ($row ['priv_status'] == 1) $priv_status_txt = get_lang ( 'AssignmentPriv1' );
				if ($row ['priv_status'] == 2) $priv_status_txt = get_lang ( 'AssignmentPriv2' );
				$table_data [] = array ('<b>' . get_lang ( 'AssignmentPriv' ) . '</b>', $priv_status_txt );
				$table_data [] = array ('<b>' . get_lang ( 'PublishStatus' ) . '</b>', $row ['is_published'] == 1 ? get_lang ( 'Published' ) : get_lang ( 'UnPublished' ) );
				if ($row ['is_published'] == 1) {
					$table_data [] = array ('<b>' . get_lang ( 'PublishedTime' ) . '</b>', $row ['published_time'] );
				}
                         if($row ['attachment_name']){
				$download_html = '<span>' . $row ['attachment_name'] . '</span>&nbsp;&nbsp;(' . round ( $row ['attachment_size'] / 1024,2 ) . 'KB)
		                &nbsp;&nbsp; <a	href="' . $_SERVER ['PHP_SELF'] . '?action=download&id=' . $id . '">' . get_lang ( 'Download' ) . '</a>';
                             }else{
                                 $download_html="无";
                             }
                                $table_data [] = array ('<b>' . get_lang ( 'DownloadFile' ) . '</b>', $download_html );
				echo Display::display_table ( null, $table_data );
				echo '<div><b>' . get_lang ( 'Content' ) . '</b><br/>';
				echo $row ['content'] . '</div>';
			
			} else {
				echo '<br/>' . Display::display_normal_message ( get_lang ( 'ThisContentIsEmpty' ) );
			}
			break;
		case "assign_sub_list" :
			
			$all_classes = CourseClassManager::get_all_classes ();
			$myClasses ['all'] = get_lang ( 'AllClasses' );
			$myClasses ['0'] = get_lang ( 'NoCategoryClass' );
			if (is_array ( $all_classes )) {
				foreach ( $all_classes as $class_info ) {
					$myClasses [$class_info ['id']] = $class_info ['name'];
				}
			}
			$query_vars ['course_class'] = (isset ( $_GET ['course_class'] ) ? getgpc ( 'course_class', 'G' ) : "all");
			
			$users_in_class = CourseClassManager::get_user_with_class ( $query_vars ['course_class'], NULL );
			$userid_in_class = array_keys ( $users_in_class );
			$str_userid_in_class = implode ( ",", $userid_in_class );
			//echo $str_userid_in_class;
			

			$form = new FormValidator ( 'search_simple', 'get', '', '', null, false );
			$renderer = $form->defaultRenderer ();
			$renderer->setElementTemplate ( '<span>{element}</span> ' );
			$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText' ) );
			$form->addElement ( 'select', 'course_class', get_lang ( 'Status1' ), $myClasses );
			$form->addElement ( 'hidden', 'action', 'assign_sub_list' );
			$form->addElement ( 'hidden', 'id', getgpc ( 'id', 'G' ) );
			$form->addElement ( 'submit', 'submit', get_lang ( 'SearchFilter' ), 'class="inputSubmit"' );
			//$form->display();
			if (isset ( $_GET ['keyword'] )) $query_vars ['keyword'] = getgpc ( 'keyword', 'G' );
			
			$sql1 = "select t1.id,t1.assignment_id,t1.title,t1.creation_time,t1.author,t1.student_id,t1.attachment_size,
	CASE status WHEN 1 THEN '" . get_lang ( 'Feedbacked' ) . "' 
				WHEN 0 THEN '" . get_lang ( 'Feedbacking' ) . "' 
				WHEN -1 THEN '" . get_lang ( 'ToFeedback' ) . "' 
				ELSE '" . get_lang ( 'Unknown' ) . "' END as status_desc,	
	if(status=1,'" . get_lang ( 'Feedbacked' ) . "','" . get_lang ( 'ToFeedback' ) . "') as status_description ,
	status,if(t2.score is NULL,-1,t2.score) as score ,
	IF(t2.is_draft is NULL,-1,t2.is_draft) as is_draft,t2.id as id2
	from " . $assignment_submission_table . " t1
	left join " . $assignment_feedback_table . " t2 on t1.id=t2.submission_id 
	where t1.assignment_id=" . Database::escape_string ( $id ) . " and t1.is_draft=0 ";
			if (is_not_blank ( $str_userid_in_class )) {
				$sql1 .= " AND t1.student_id IN (" . $str_userid_in_class . ") ";
			}
			if (is_not_blank ( $query_vars ['keyword'] )) {
				$sql1 .= " AND (t1.title LIKE '%" . $query_vars ['keyword'] . "%' OR t1.author LIKE '%" . $query_vars ['keyword'] . "%')";
			}
			$sql1 .= "ORDER BY author,creation_time";
			//echo $sql1;
			$result1 = api_sql_query ( $sql1, __FILE__, __LINE__ );
			
			if (Database::num_rows ( $result1 ) > 0) {
				$table_header [] = array (get_lang ( 'Submitter' ), true );
				$table_header [] = array (get_lang ( 'Class_of_course' ) );
				//$table_header [] = array (get_lang ( 'TitleWork' ), true );
				$table_header [] = array (get_lang ( 'SubmitTime' ), true );
				$table_header [] = array (get_lang ( 'Attachment' ), FALSE );
				$table_header [] = array (get_lang ( 'AssignmentStatus' ), true );
				$table_header [] = array (get_lang ( 'Score' ), true );
				//$table_header[] = array(get_lang('Attachment'),false);
				$table_header [] = array (get_lang ( 'Actions' ), false );
				
				$sorting_options = array ();
				$sorting_options ['column'] = 1;
				$sorting_options ['default_order_direction'] = 'ASC';
				$sorting_options ['tablename'] = 'tablename_sublist';
				
				$table_data = array ();
				//$str_student_submitted_id='';//已提交作业学生ID串
				$total_score = $total_class_user_count = 0;
				$sum_feedback_count = $sum_not_feedback_count = 0;
				while ( $data = Database::fetch_object ( $result1 ) ) {
					$row_data = array ();
					$row_data [] = $data->author;
					$row_data [] = $users_in_class [$data->student_id]['name'];
					$attachment_tip = get_lang ( 'HasAttachment' ) . "," . get_lang ( 'AttachmentSize' ) . ':' . round ( $data->attachment_size / 1024, 2 ) . 'KB';
					//$row_data[]=($data->attachment_size>0?Display::return_icon('attachment.gif',$attachment_tip):'');
					//$row_data [] = $data->title;
					$row_data [] = substr ( $data->creation_time, 0, 16 );
					$row_data [] = ($data->attachment_size > 0 ? Display::return_icon ( 'attachment.gif', $attachment_tip ) : '');
					$row_data [] = $data->status_desc;
					$row_data [] = $data->score >= 0 ? $data->score : '';
					if ($data->status == - 1 && $data->is_draft = - 1) { //待批改,显示批改按钮
						$href = 'assignment_feedback.php?assignment_id=' . $data->assignment_id . '&submission_id=' . $data->id;
						$actionHtml = link_button ( 'works_small.gif', 'AssignmentFeedBack', $href, '90%', '80%', false );
						$sum_not_feedback_count ++;
					} else if ($data->status == 0 && $data->score >= 0 && $data->is_draft == 1) { //批改中,显示修改草稿按钮
						$href = 'assignment_feedback.php?assignment_id=' . $data->assignment_id . '&submission_id=' . $data->id . '&id=' . $data->id2 . '&action=assign_fb_edit';
						$actionHtml = link_button ( 'edit.gif', 'Edit', $href, '90%', '80%', false );
						$sum_not_feedback_count ++;
					} else if ($data->status == 1 && $data->score >= 0 && $data->is_draft == 0) { //批改完,无操作
						$href = 'assignment_feedback.php?id=' . $data->id2 . '&submission_id=' . $data->id . '&assignment_id=' . $data->assignment_id . '&action=assign_fb_show';
						$actionHtml = link_button ( 'synthese_view.gif', 'Info', $href, '90%', '80%', false );
						$total_score += $data->score;
						$total_class_user_count ++;
						$sum_feedback_count ++;
					}
					
					//else if($data->status!=2){
					$href = 'assignment_feedback.php?id=' . $data->id2 . '&submission_id=' . $data->id . '&assignment_id=' . $data->assignment_id . '&action=stud_sub_reject';
					$actionHtml .= '&nbsp;&nbsp;' . confirm_href ( 'undelete.gif', 'ConfirmYourChoice', 'SubmissionReject', $href );
					//}
					$row_data [] = $actionHtml;
					$table_data [] = $row_data;
					unset ( $actionHtml, $row_data );
				
				}
				
				$avg_score = round ( $total_score / ($total_class_user_count != 0 ? $total_class_user_count : 1), 2 );
				$query_vars ['id'] = getgpc ( 'id', 'G' );
				$query_vars ['action'] = 'assign_sub_list';
				
				echo '<div class="actions">';
				echo '<span style="float:right; padding-top:2px;">';
				echo '&nbsp;&nbsp;' . link_button ( 'folder_new.gif', 'BatchFeedbackAssignment', 'batch_feedback.php?assignment_id=' . $id, '90%', '90%' );
				echo '</span>';
				$form->display ();
				echo '</div>';
				
				Display::display_sortable_table ( $table_header, $table_data, $sorting_options, array (), $query_vars );
				echo '<div style="float:right">' . get_lang ( "SumOfFeedback" ) . ": " . $sum_feedback_count . str_repeat ( '&nbsp;', 10 ), get_lang ( "SumOfNotFeedback" ) . ": " . $sum_not_feedback_count . str_repeat ( '&nbsp;', 10 ), get_lang ( 'AverageScore' ) . ': ' . $avg_score . "</div>";
			
			} else { //没有记录时
				echo '<br/>' . Display::display_normal_message ( get_lang ( 'ThisContentIsEmpty' ) );
			}
			break;
		case "assign_unsub_userlist" :
			
			$all_classes = CourseClassManager::get_all_classes ();
			$myClasses ['all'] = get_lang ( 'AllClasses' );
			$myClasses ['0'] = get_lang ( 'NoCategoryClass' );
			if (is_array ( $all_classes )) {
				foreach ( $all_classes as $class_info ) {
					$myClasses [$class_info ['id']] = $class_info ['name'];
				}
			}
			$query_vars ['course_class'] = (isset ( $_GET ['course_class'] ) ? getgpc ( 'course_class', 'G' ) : "all");
			
			$users_in_class = CourseClassManager::get_user_with_class ( $query_vars ['course_class'], STUDENT );
			$userid_in_class = array_keys ( $users_in_class );
			$str_userid_in_class = implode ( ",", $userid_in_class );
			
			$form = new FormValidator ( 'search_simple', 'get', '', '', null, false );
			$renderer = $form->defaultRenderer ();
			$renderer->setElementTemplate ( '<span>{element}</span> ' );
			$form->addElement ( 'select', 'course_class', get_lang ( 'Status1' ), $myClasses );
			$form->addElement ( 'hidden', 'action', 'assign_unsub_userlist' );
			$form->addElement ( 'hidden', 'id', getgpc ( 'id', 'G' ) );
			$form->addElement ( 'submit', 'submit', get_lang ( 'Filter' ), 'class="inputSubmit"' );
			$form->display ();
			
			$sql2 = "SELECT * FROM " . Database::get_main_table ( VIEW_USER_DEPT ) . " t1 WHERE is_admin!=1 and t1.user_id NOT IN (SELECT t3.student_id FROM " . $assignment_submission_table . " t3 LEFT JOIN " . $assignment_feedback_table . " t4 on t3.id=t4.submission_id WHERE t3.assignment_id='" .
					 Database::escape_string ( $id ) . "' and t3.is_draft=0) AND t1.user_id IN (SELECT user_id FROM " . Database::get_main_table ( TABLE_MAIN_COURSE_USER ) . " AS t10 WHERE t10.course_code = '" . api_get_course_code () . "' AND t10.status = " . STUDENT . ")";
					
					if ($query_vars ['course_class'] == 'all') {
						$sql2 .= " and t1.user_id in(select user_id from " . Database::get_main_table ( TABLE_MAIN_COURSE_USER ) . " where course_code='" . api_get_course_code () . "' and status=" . STUDENT . " )";
					}
					if ($userid_in_class) {
						$sql2 .= " AND t1.user_id IN (" . $str_userid_in_class . ") ";
					}
					
					//echo $sql2;
					$result2 = api_sql_query ( $sql2, __FILE__, __LINE__ );
					
					if (Database::num_rows ( $result2 )) {
						$table_header2 [] = array (get_lang ( 'LoginName' ), true );
						$table_header2 [] = array (get_lang ( 'FirstName' ), true );
						$table_header2 [] = array (get_lang ( 'OfficialCode' ), true );
						$table_header2 [] = array (get_lang ( 'InDept' ) );
						$table_header2 [] = array (get_lang ( 'Class_of_course' ) );
						$table_header2 [] = array (get_lang ( 'Email' ), true );
						$table_header2 [] = array (get_lang ( 'Actions' ) );
						$rowIndex = 0;
						while ( $data2 = Database::fetch_object ( $result2 ) ) {
							$row_data2 = array ();
							$row_data2 [] = $data2->username;
							$row_data2 [] = $data2->firstname;
							$row_data2 [] = $data2->official_code;
							$row_data2 [] = $data2->dept_name;
							$row_data2 [] = $users_in_class [$data2->user_id] ['name'];
							$row_data2 [] = Display::encrypted_mailto_link ( $data2->email, $data2->email );
							$row_data2 [] = link_button ( 'synthese_view.gif', 'Info', '../user/userInfo.php?uInfo=' . $data2->user_id, '80%', '90%', false );
							$table_data2 [] = $row_data2;
						}
                                                $query_vars = array ('id' => intval(getgpc("id","G")), 'action' => 'assign_unsub_userlist' );
						Display::display_sortable_table ( $table_header2, $table_data2, array (), array (), $query_vars );
					} else { //没有记录时
						echo '<br/>' . Display::display_normal_message ( get_lang ( 'ThisContentIsEmpty' ) );
					}
					break;
			}
		} //课程管理员,教师视图结束
		

		echo '</div></div></div>';
		Display::display_footer ();
