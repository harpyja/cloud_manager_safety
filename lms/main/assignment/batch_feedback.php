<?php
$language_file = array ('assignment', 'admin', 'class_of_course' );
require ('../inc/global.inc.php');
require_once ('assignment.lib.php');
api_block_anonymous_users ();
api_protect_course_admin_script ();
$is_allowed_to_edit = api_is_allowed_to_edit ();
$user_id = api_get_user_id ();
$course_code = api_get_course_code ();

$assignment_id = intval (getgpc("assignment_id") );
$action = getgpc ( 'action' );

if (IS_POST && isset ( $_POST ['formSent'] ) && trim ( $_POST ['formSent'] ) == '1') {
	$isDraft = (isset ( $_POST ['submitAsDraft'] ) ? 1 : 0);
	$sub_status = (isset ( $_POST ['submitWork'] ) ? 1 : 0);
	if (isset ( $_POST ['score'] ) && isset ( $_POST ['comment'] )) {
		$post_score = getgpc ( 'score', 'P' );
		$post_comment = getgpc ( 'comment', 'P' );
		$author = $_user ['firstName'];
		foreach ( $post_score as $key => $val ) {
			list ( $submission_id, $feedback_id ) = explode ( '###', $key );
			if (empty ( $feedback_id )) {
				$sql_data = array ('`submission_id`' => $submission_id, '`author`' => $author, '`score`' => $val, '`content`' => $post_comment [$key], '`correction_time`' => date ( "Y-m-d H:i:s" ), '`is_draft`' => $isDraft );
				$sql_data ['cc'] = api_get_course_code ();
				$sql = Database::sql_insert ( $assignment_feedback_table, $sql_data );
				api_sql_query ( $sql, __FILE__, __LINE__ );
			} else {
				$sql_data = array ('`author`' => $author, '`score`' => $val, '`content`' => $post_comment [$key], '`is_draft`' => $isDraft );
				$sql = Database::sql_update ( $assignment_feedback_table, $sql_data, "id='" . escape ( $feedback_id ) . "'" );
				api_sql_query ( $sql, __FILE__, __LINE__ );
			}
			$sql = Database::sql_update ( $assignment_submission_table, array ('status' => $sub_status ), "id='" . escape ( $submission_id ) . "'" );
			api_sql_query ( $sql, __FILE__, __LINE__ );
		}
	}
}

$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( null, FALSE );

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
$form->addElement ( 'hidden', 'id', intval(getgpc ( 'id', 'G' )) );
$form->addElement ( 'submit', 'submit', get_lang ( 'SearchFilter' ), 'class="inputSubmit"' );
//$form->display();
if (isset ( $_GET ['keyword'] )) $query_vars ['keyword'] = getgpc ( 'keyword', 'G' );

$sql1 = "select t1.id,t1.assignment_id,t1.title,t1.creation_time,t1.author,t1.student_id,t1.attachment_size,
CASE status WHEN 0 THEN '" . get_lang ( 'Feedbacking' ) . "' WHEN -1 THEN '" . get_lang ( 'ToFeedback' ) . "' ELSE '" . get_lang ( 'Unknown' ) . "' END as status_desc,
status,if(t2.score is NULL,-1,t2.score) as score,t2.content,IF(t2.is_draft is NULL,-1,t2.is_draft) as is_draft,t2.id as id2
from " . $assignment_submission_table . " t1 LEFT JOIN " . $assignment_feedback_table . " t2 on t1.id=t2.submission_id
WHERE status<>1 AND t1.assignment_id=" . Database::escape ( $assignment_id ) . " and t1.is_draft=0 ";
if (is_not_blank ( $str_userid_in_class )) {
	$sql1 .= " AND t1.student_id IN (" . $str_userid_in_class . ") ";
}
if (is_not_blank ( $query_vars ['keyword'] )) {
	$sql1 .= " AND (t1.title LIKE '%" . $query_vars ['keyword'] . "%' OR t1.author LIKE '%" . $query_vars ['keyword'] . "%')";
}
$sql1 .= "ORDER BY author,creation_time";
//echo $sql1;
$result1 = api_sql_query ( $sql1, __FILE__, __LINE__ );

$table_header [] = array (get_lang ( 'Submitter' ), true );
// $table_header [] = array (get_lang ( 'TitleWork' ), true );
$table_header [] = array (get_lang ( 'SubmitTime' ), true );
$table_header [] = array (get_lang ( 'AssignmentStatus' ), true );
$table_header [] = array (get_lang ( 'Score' ), true );
$table_header [] = array (get_lang ( 'FeedbackComment' ), false );
$table_header [] = array (get_lang ( 'Actions' ), false );
$sorting_options = array ();
$sorting_options ['tablename'] = 'tablename_sublist';

$table_data = array ();
$total_score = $total_class_user_count = 0;
$sum_feedback_count = $sum_not_feedback_count = 0;
while ( $data = Database::fetch_object ( $result1 ) ) {
	$row_data = array ();
	$row_data [] = $data->author;
	// 	$row_data [] = $data->title;
	$row_data [] = substr ( $data->creation_time, 0, 16 );
	$row_data [] = $data->status_desc;
	$row_data [] = form_input ( 'score[' . $data->id . '###' . $data->id2 . ']', $data->score == - 1 ? '' : $data->score, 'style="width:40px;" class="inputText"' );
	$row_data [] = form_textarea ( 'comment[' . $data->id . '###' . $data->id2 . ']', $data->content, ' class="inputText" style="height:40px;width:400px"' );
	
	$actionHtml = '';
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
		$sum_feedback_count ++;
	}
	$href = 'assignment_feedback.php?id=' . $data->id2 . '&submission_id=' . $data->id . '&assignment_id=' . $data->assignment_id . '&action=stud_sub_reject';
	$actionHtml .= '&nbsp;&nbsp;' . confirm_href ( 'undelete.gif', 'ConfirmYourChoice', 'SubmissionReject', $href );
	$row_data [] = $actionHtml;
	$table_data [] = $row_data;
}

echo '<div class="actions">';
$form->display ();
echo '</div>';

echo form_open ( 'batch_feedback.php', 'method="post"', array ('assignment_id' => $assignment_id, 'formSent' => '1' ) );
echo Display::display_table ( $table_header, $table_data );
echo '<div style="padding:10px 50px">';
echo '<input class="inputSubmit" name="submitWork" value="批改完成并提交" type="submit" />';
echo '&nbsp;&nbsp;<input class="inputSubmit" name="submitAsDraft" value="存为草稿" type="submit" />';
echo '&nbsp;&nbsp;<button type="button" class="cancel" onclick="javascript:self.parent.tb_remove();" name="cancle" >取消</button>';
echo '</div>';
echo form_close ();
Display::display_footer ();