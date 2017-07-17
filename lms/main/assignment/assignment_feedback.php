<?php

/*
 ==============================================================================
 ==============================================================================
 */

function score_range_check($inputValue) {
	return (intval ( $inputValue ) > 0 && intval ( $inputValue ) <= 100);
}

$language_file = array ('assignment', 'admin' );
include_once ('../inc/global.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'formvalidator/FormValidator.class.php');
require_once ('assignment.lib.php');
api_block_anonymous_users ();
api_protect_course_admin_script ();
$is_allowed_to_edit = api_is_allowed_to_edit ();
if (! $is_allowed_to_edit) api_not_allowed ();
$user_id = api_get_user_id ();
$course_code = api_get_course_code ();

$id = intval (getgpc("id") );
$action = getgpc ( 'action' );
$assignment_id = intval (getgpc("assignment_id") );
$submission_id = intval (getgpc("submission_id") );

$display_assignment_form = getgpc ( 'display_assignment_form' );
$base_work_dir = api_get_path ( SYS_COURSE_PATH ) . $course_code . '/assignment';
$http_www = api_get_path ( WEB_COURSE_PATH ) . $course_code . '/assignment';
$display_assignment_feedback_form = true;
if (is_equal ( $_REQUEST ['action'], "assign_fb_edit" )) {
	//$sql = "select * from " . $assignment_feedback_table . " where id='" . escape ( $id ) . "'";

        $sql = "SELECT t1.*,t3.title as t3title FROM " .$assignment_feedback_table. " as t1,crs_assignment_submission as t2,crs_assignment_main as t3 WHERE t1.id='" . escape ( $id ) . "' and t1.submission_id=t2.id and t2.assignment_id=t3.id";

        $sql_result = api_sql_query ( $sql, __FILE__, __LINE__ );
        $data = Database::fetch_array ( $sql_result, 'ASSOC' );

	if ($data['id']) {
                $title=$data['t3title'];
		$author = isset ( $data ['author'] ) && ! empty ( $data ['author'] ) ? $data ['author'] : api_get_user_name ();
		$content = $data ['content'];
		$correction_time = $data ['correction_time'];
		$score = $data ['score'];
		$attachment_name = $data ['attachment_name'];
		$attachment_uri = $data ['attachment_uri'];
		$attachment_size = $data ['attachment_size'];
	}
} else if (is_equal ( $_REQUEST ['action'], "assign_fb_show" )) {
	$display_assignment_feedback_form = false;
} else if (is_equal ( $_REQUEST ['action'], 'stud_sub_reject' )) { //作业回退
	$sql = "UPDATE " . $assignment_feedback_table . " SET score='0',`is_draft`='1' where id='" . escape ( $id ) . "'";
	api_sql_query ( $sql, __FILE__, __LINE__ );
	
	$sql = "UPDATE " . $assignment_submission_table . " SET status='2',`is_draft`='1' where id='" . escape ( $submission_id ) . "'";
	api_sql_query ( $sql, __FILE__, __LINE__ );
	
	$redirect_url = 'assignment_info.php?id=' . $assignment_id . '&action=assign_sub_list&message=' . urlencode ( get_lang ( 'SubmissionRejectSuccess' ) );
	api_redirect ( $redirect_url );
}

if ($display_assignment_feedback_form) {
	
	$form = new FormValidator ( 'form', 'POST', $_SERVER ['PHP_SELF'], '', 'enctype="multipart/form-data"' );
	$form->addElement ( 'hidden', 'submission_id', $submission_id );
	$form->addElement ( 'hidden', 'assignment_id', $assignment_id );
	
	if ($action == "assign_fb_edit") {
		$form->addElement ( 'hidden', 'action', 'assign_fb_edit_save' );
		$form->addElement ( 'hidden', 'id', intval(getgpc("id","G")) );
	} else {
		$form->addElement ( 'hidden', 'action', 'assign_fb_add_save' );
	}
	
	//$form->addElement ( 'header', 'header', get_lang ( 'AssignmentFeedBack' ) );
	

	//$titleWork=$form->addElement('text', 'title', get_lang("TitleWork"), array('size'=>'45'));
	//$defaults["title"] = ($action=="assign_fb_edit"?($title):'');
	//$form->addRule('title', get_lang('ThisFieldIsRequired'), 'required');
	

	$sql = "SELECT t1.*,t2.title as t2title FROM " . $assignment_submission_table . " as t1,crs_assignment_main as t2 WHERE t1.id='" . escape ( $submission_id ) . "' and t1.assignment_id=t2.id";
        $result3 = api_sql_query ( $sql, __FILE__, __LINE__ );
	if ($row3 = Database::fetch_array ( $result3, 'ASSOC' )) {
		$sub_title = $row3 ['t2title'];
		$user_info = UserManager::get_user_info_by_id ( $row3 ['student_id'], true );
		$sub_author = $user_info ['firstname'] . ' (' . $user_info ['username'] . ",  " . $user_info ['dept_path'] . ' )';
		$sub_time = substr ( $row3 ['creation_time'], 0, 16 );
		$sub_content = $row3 ['content'];
		$sub_attachment_name = $row3 ['attachment_name'];
		$sub_attachment_size = $row3 ['attachment_size'];
		$sub_attachment_uri = $http_www . "/" . $assignment_id . "/" . $row3 ['attachment_uri'];
		
		if (isset ( $row3 ['attachment_uri'] ) && ! empty ( $row3 ['attachment_uri'] )) $html_attachment = '<a href="' . $sub_attachment_uri . '">' . $sub_attachment_name . '</a>&nbsp;&nbsp;&nbsp;(' . ($sub_attachment_size / 1024) . "KB)";
		$form->addElement ( 'static', 'sub_title', get_lang ( 'Title' ), $sub_title );
		$form->addElement ( 'static', 'sub_author', get_lang ( 'Submitter' ), $sub_author );
		$form->addElement ( 'static', 'sub_time', get_lang ( 'SubmitTime' ), $sub_time );
		$form->addElement ( 'static', 'sub_attachement', get_lang ( 'DownloadFile' ), $html_attachment );
		$form->addElement ( 'static', 'sub_content', get_lang ( 'Content' ), $sub_content );
	}
	
	//	$titleAuthors = $form->addElement ( 'text', 'author', get_lang ( "FeedbackedTeacher" ), array ('style' => "width:250px", 'class' => 'inputText', 'readonly' => 'true' ) );
	//	$defaults ["author"] = ($action == "assign_fb_edit" ? stripslashes ( $author ) : $_user ['firstName']);
	

	//分数
	$form->addElement ( 'text', 'score', get_lang ( "Score" ), array ('maxlength' => '3', 'style' => "width:50px;text-align:right", 'class' => 'inputText', 'title' => get_lang ( 'ScoreTip' ) ) );
	$form->addRule ( 'score', get_lang ( 'ThisFieldIsRequired' ), 'required' );
	$form->addRule ( 'score', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
	$form->addRule ( 'score', get_lang ( '' ), 'rangelength', array (1, 3 ) );
	$form->addRule ( 'score', get_lang ( 'ScoreTip' ), 'callback', 'score_range_check' );
	$defaults ["score"] = ($action == "assign_fb_edit" ? ($score) : '0');
	
	$form->addElement ( 'textarea', 'content', get_lang ( 'FeedbackComment' ), array ('id' => 'description', 'wrap' => 'virtual', 'class' => 'inputText', 'style' => 'width:100%;height:200px' ) );
	$defaults ["content"] = ($action == "assign_fb_edit" ? stripslashes ( $content ) : stripslashes ( $workDescription ));
	
	//附件
	$form->addElement ( 'file', 'file', get_lang ( 'DownloadFile' ), array ('style' => "width:350px", 'class' => 'inputText' ) );
	if ($action == "assign_fb_edit" && $attachment_size > 0) {
		$form->addElement ( 'static', 'fileUpload', "", get_lang ( "fileUploadedName" ) . "<a href=\"" . $http_www . "/" . $assignment_id . "/" . $attachment_uri . "\">" . $attachment_name . "</a>(" . (( int ) ($attachment_size / 1024)) . "KB)," . get_lang ( 'fileUploadedTip' ) );
	}
	//$form->addRule('file', 'Upload is required', 'uploadedfile');
	//$form->addRule('file', get_lang('UploadFileTypeAre'). ' text/xml', 'mimetype', 'text/xml');
	$form->addRule ( 'file', get_lang ( 'UploadFileSizeLessThan' ) . get_upload_max_filesize () . ' MB', 'maxfilesize', get_upload_max_filesize () * 1048576 );
	$form->addRule ( 'file', get_lang ( 'UploadFileNameAre' ) . ' *.zip,*.rar,*.doc,*.xls,*.ppt,*.pdf', 'filename', '/\\.(zip|rar|doc|xls|ppt|pdf)$/' );
	
	//提交		
	$group = array ();
	$group [] = & HTML_QuickForm::createElement ( 'submit', 'submitWork', get_lang ( 'Submit' ), 'class="inputSubmit"' );
	$group [] = & HTML_QuickForm::createElement ( 'submit', 'submitAsDraft', get_lang ( 'SaveAsDraft' ), 'class="inputSubmit"' );
	$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
	$form->addGroup ( $group, null, '&nbsp;', '&nbsp;&nbsp;' );
	
	$form->setDefaults ( $defaults );
	
	Display::setTemplateBorder ( $form, '98%' );
	
	if ($is_allowed_to_edit && $form->validate ()) {
		$form->freeze ( array ('submitWork', 'submitAsDraft' ) );
		$data = $form->exportValues ();

		//附件处理
		$file_element = & $form->getElement ( 'file' );
		$author = $data ['author'] ? $data ['author'] : $_user ['firstName'];
		$content = $data ['content'];
		$creation_time = date ( "Y-m-d H:i:s" );
		$score = $data ['score'];
		$isDraft = (isset ( $data ['submitWork'] ) ? 0 : 1);
		$sub_status = (isset ( $data ['submitWork'] ) ? 1 : 0);
		if ($action == "assign_fb_edit_save") { //编辑草稿
			//上传附件
			$save_dir = $base_work_dir . '/' . $assignment_id;
			$file_url_prefix = 'assignment/' . $assignment_id . "/";
			$file = AttachmentManager::hanle_upload ( $file_element, 'ASSIGNMENT_FB', $save_dir, $file_url_prefix );
			
                        if (isset ( $file ) && $file['name'] && is_array ( $file )) {
				$filename = $file ['name'];
				$fsize = $file ['size'];
				$file_uri = $file ['new_name'];
				$uniqid = $file ['attachment_uniqid'];
			} 
                        //else {
			//	$redirect_url = 'index.php?cidReq=' . $course_code . '&action=show_message&error_message=' . urlencode ( get_lang ( 'UploadFileFailed' ) );
			//	api_redirect ( $redirect_url );
			//}
			
			//删除原先附件						
			if (strlen ( $file ['name'] ) > 0) {
				$file_path_prefix = api_get_path ( SYS_COURSE_PATH ) . $course_code . "/";
				AttachmentManager::del_all_attachment ( 'ASSIGNMENT_FB', $id, $file_path_prefix );
			}
			//更新附件表
			if (strlen ( $file ['name'] ) > 0) {
                        $sql = "UPDATE " . $table_attachment . " SET ref_id='" . escape ( $id ) . "' WHERE name='" . $uniqid . "'";
                        api_sql_query ( $sql, __FILE__, __LINE__ );
                        }

			$sql_data = array ('content' => $content, 'score' => $score );
			if (isset ( $file_uri ) && ! empty ( $file_uri )) {
				$sql_data ['attachment'] = $uniqid;
				$sql_data ['attachment_uri'] = $file_uri;
				$sql_data ['attachment_name'] = $filename;
				$sql_data ['attachment_size'] = $fsize;
			}
			$sql_data ['correction_time'] = date ( 'Y-m-d H:i:s' );
			$sql_data ['author'] = $author;
			$sql_data ['is_draft'] = $isDraft;
			$sql = Database::sql_update ( $assignment_feedback_table, $sql_data, "id='" . escape ( $id ) . "'" );

                        api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$sql = Database::sql_update ( $assignment_submission_table, array ('status' => $sub_status ), "id='" . escape ( $submission_id ) . "'" );
                        api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$redirect_url = 'assignment_info.php?id=' . $assignment_id . '&action=assign_sub_list&message=' . urlencode ( get_lang ( 'AssignmentFeedbackSuccess' ) );
		}
		
		if ($action == "assign_fb_add_save") { //新增
			$sql_data = array ('`submission_id`' => $submission_id, '`author`' => $author, '`score`' => $score, '`content`' => $content, '`correction_time`' => $creation_time, '`is_draft`' => $isDraft );
			$sql_data ['cc'] = api_get_course_code ();
			$sql = Database::sql_insert ( $assignment_feedback_table, $sql_data );
			api_sql_query ( $sql, __FILE__, __LINE__ );
			$fb_id = Database::get_last_insert_id ();
			
			$sql = "UPDATE " . $assignment_submission_table . " SET status=" . $sub_status . " where id='" . escape ( $submission_id ) . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			//处理附件				
			$save_dir = $base_work_dir . "/" . $assignment_id;
			$file_url_prefix = 'assignment/' . $assignment_id . "/";
			$file = AttachmentManager::hanle_upload ( $file_element, 'ASSIGNMENT_FB', $save_dir, $file_url_prefix );
			if (isset ( $file ) && $file && is_array ( $file )) {
				$filename = $file ['name'];
				$fsize = $file ['size'];
				$file_uri = $file ['new_name'];
				$uniqid = $file ['attachment_uniqid'];
			} else {
				$redirect_url = 'index.php?cidReq=' . $course_code . '&action=show_message&error_message=' . urlencode ( get_lang ( 'UploadFileFailed' ) );
				api_redirect ( $redirect_url );
			}
			
			//更新附件表
			$sql = "UPDATE " . $table_attachment . " SET ref_id='" . $fb_id . "',url='" . escape ( $file_url_prefix . $file_uri ) . "' WHERE name='" . $uniqid . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$sql_data = array ();
			if (isset ( $file_uri ) && ! empty ( $file_uri )) {
				$sql_data ['attachment'] = $uniqid;
				$sql_data ['attachment_uri'] = $file_uri;
				$sql_data ['attachment_name'] = $filename;
				$sql_data ['attachment_size'] = $fsize;
				$sql = Database::sql_update ( $assignment_feedback_table, $sql_data, "id='" . escape ( $fb_id ) . "'" );
				api_sql_query ( $sql, __FILE__, __LINE__ );
			}
			
			$redirect_url = 'assignment_info.php?id=' . $assignment_id . '&action=assign_sub_list&message=' . urlencode ( get_lang ( 'AssignmentFeedbackSuccess' ) );
		}
		
		tb_close ();
	}
	//}
	

	$htmlHeadXtra [] = Display::display_thickbox ();
	$htmlHeadXtra [] = Display::display_kindeditor ( 'description' );
	Display::display_header ( null, false );
	$form->display ();
}

if ($action == "assign_fb_show") { //显示批改详细信息
	Display::display_header ( null, false );
	$sql = "select * from " . $assignment_feedback_table . " where id='" . escape ( $id ) . "'";
	$sql_result = api_sql_query ( $sql, __FILE__, __LINE__ );
	if ($data = Database::fetch_array ( $sql_result, 'ASSOC' )) {
		//$title=$data['title'];
		$author = $data ['author'];
		$content = $data ['content'];
		$correction_time = $data ['correction_time'];
		$score = $data ['score'];
		$attachment_name = $data ['attachment_name'];
		$attachment_uri = $data ['attachment_uri'];
		$attachment_size = $data ['attachment_size'];
		
		$sql = "SELECT * FROM " . $assignment_submission_table . " WHERE id='" . escape ( $submission_id ) . "'";
		//echo $sql;
		$result3 = api_sql_query ( $sql, __FILE__, __LINE__ );
		if ($row3 = mysql_fetch_array ( $result3 )) {
			$sub_title = $row3 ['title'];
			$user_info = UserManager::get_user_info_by_id ( $row3 ['student_id'] );
			$sub_author = $user_info ['firstname'] . " " . $user_info ['lastname'];
			$sub_time = $row3 ['creation_time'];
			$sub_content = $row3 ['content'];
			$sub_attachment_name = $row3 ['attachment_name'];
			$sub_attachment_size = $row3 ['attachment_size'];
			if (isset ( $row3 ['attachment_uri'] ) && ! empty ( $row3 ['attachment_uri'] )) $sub_attachment_uri = $http_www . "/" . $assignment_id . "/" . $row3 ['attachment_uri'];
			$html_attachment = '<a href="' . $sub_attachment_uri . '">' . $sub_attachment_name . '</a>&nbsp;&nbsp;&nbsp;(' . ( int ) ($sub_attachment_size / 1024) . "KB)";
		}
		
		?>
<blockquote>
<table class="data_table">
	<!-- 	<tr class="row_odd">
		<th width="15%"><?=get_lang ( 'ItemName' )?></th>
		<th><?=get_lang ( 'ItemValue' )?></th>
	</tr> -->
	<tr class="row_odd">
		<td><b><?=get_lang ( 'Title' )?></b></td>
		<td><span><?=$sub_title?></span></td>
	</tr>
	<tr class="row_even">
		<td><b><?=get_lang ( 'Submitter' )?></b></td>
		<td><span><?=$sub_author?></span></td>
	</tr>
	<tr class="row_odd">
		<td><b><?=get_lang ( 'SubmitTime' )?></b></td>
		<td><span><?=$sub_time?></span></td>
	</tr>
	<tr class="row_even">
		<td><b><?=get_lang ( 'DownloadFile' )?></b></td>
		<td><span><?=$html_attachment?></span></td>
	</tr>
	<tr class="row_odd">
		<td colspan=2><b><?=get_lang ( 'Content' )?></b><br />
		<span><?=$sub_content?></span></td>
	</tr>
	<tr class="row_even">
		<td colspan=2 height="35" style="border: 0px"></td>
	</tr>

	<tr class="row_odd">
		<td style="border-top: 1px solid #cccccc;"><b><?=get_lang ( 'CorrectionTime' )?></b></td>
		<td style="border-top: 1px solid #cccccc;"><span><?=$correction_time?></span></td>
	</tr>
	<tr class="row_even">
		<td><b><?=get_lang ( 'Score' )?></b></td>
		<td><span><?=$score?></span></td>
	</tr>
	<tr class="row_odd">
		<td><b><?=get_lang ( 'DownloadFile' )?></b></td>
		<td><a href="<?=$http_www . "/" . $id?>/<?=$attachment_uri?>"><span><?=$attachment_name?></span></a>&nbsp;&nbsp;(<?=( int ) ($attachment_size / 1024)?>KB)</td>
	</tr>
	<tr class=""row_even"">
		<td colspan=2><b><?=get_lang ( 'FeedbackContent' )?></b>
		<p><span><?=$content?></span>
		
		</td>
	</tr>
</table>
</blockquote>
<?php
	}
} //END 显示批改详细信息


Display::display_footer ();
