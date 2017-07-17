<?php
/*
 ==============================================================================
 ==============================================================================
 */

$language_file = array ('assignment', 'admin' );
require ('../inc/global.inc.php');
require_once ('assignment.lib.php');
api_block_anonymous_users ();

api_protect_course_script ();

$id =intval( getgpc ( 'id' ));
$assignment_id = getgpc ( 'assignment_id' ); //提交作业后,作业的ID
$action = getgpc ( 'action' );
$delete = getgpc ( 'delete' );
$display_assignment_form = getgpc ( 'display_assignment_form' );
$user_id = api_get_user_id ();
$course_code = api_get_course_code ();
$is_allowed_to_edit = api_is_allowed_to_edit ();
$course_dir = api_get_path ( SYS_COURSE_PATH ) . $course_code;
$base_work_dir = $course_dir . '/assignment';
$http_www = api_get_path ( WEB_COURSE_PATH ) . $course_code . '/assignment';

$res1 = get_assignment_info ( $assignment_id );
$assignments = api_store_result_array ( $res1 );
$assignment_info = $assignments [0];

if ($action == "assign_sub_edit") {
	$sql = "select * from " . $assignment_submission_table . " where id='" . Database::escape_string ( $id ) . "'";
	$sql_result = api_sql_query ( $sql, __FILE__, __LINE__ );
	if ($data = Database::fetch_array ( $sql_result, 'ASSOC' )) {
		$title = $data ['title'];
		$author = $data ['author'];
		$content = $data ['content'];
		$attachment_name = $data ['attachment_name'];
		$attachment_uri = $data ['attachment_uri'];
		$attachment_size = $data ['attachment_size'];
		$sub_status = $data ['status'];
		/*$deadline=$data['deadline'];
			 $type=$data['assignment_type'];
			 $is_allow_late_submission=$data['is_allow_late_submission'];
			 $priv_status=$data['priv_status'];
			 $is_published=$data['is_published'];
			 $assignment_type=$data['assignment_type'];*/
	
	}
}

$form = new FormValidator ( 'form', 'POST', $_SERVER ['PHP_SELF'], '', 'enctype="multipart/form-data"' );
$form->addElement ( 'hidden', 'assignment_id', $assignment_id );
if ($action == "assign_sub_edit") {
	$form->addElement ( 'hidden', 'action', 'assign_sub_edit_save' );
	$form->addElement ( 'hidden', 'id',intval( getgpc("id","G") ));
} else {
	$form->addElement ( 'hidden', 'action', 'assign_sub' );
}
$form->addElement ( 'hidden', 'sub_status', $sub_status );

//$form->addElement ( 'header', 'header', get_lang ( 'SubmitAssignment' ) );
/* $titleWork = $form->addElement ( 'text', 'title', get_lang ( "TitleWork" ), array ('style' => "width:80%", 'class' => 'inputText' ) );
$form->addRule ( 'title', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$defaults ["title"] = ($action == "assign_sub_edit" ? ($title) : $_user ['firstName'] . get_lang ( 'SubmitAssignment' ) . ":" . $assignment_info [0] ['title']); */

$form->addElement ( 'textarea', 'content', get_lang ( 'Content' ), array ('id' => 'description', 'wrap' => 'virtual', 'class' => 'inputText', 'style' => 'width:100%;height:280px' ) );

$defaults ["content"] = ($action == "assign_sub_edit" ? stripslashes ( $content ) : stripslashes ( $workDescription ));

//附件
$max_upload_file_size = get_upload_max_filesize ( 0 );
$form->addElement ( 'file', 'file', get_lang ( 'DownloadFile' ), array ('style' => "width:350px", 'class' => 'inputText' ) );
if ($action == "assign_sub_edit") {
	$form->addElement ( 'static', 'fileUpload', "", get_lang ( "fileUploadedName" ) . "<a href=\"" . $http_www . "/" . $assignment_id . "/" . $attachment_uri . "\">" . $attachment_name . "</a>(" . (( int ) ($attachment_size / 1024)) . "KB)," . get_lang ( 'fileUploadedTip' ) );
}
//$form->addRule('file', 'Upload is required', 'uploadedfile');
//$form->addRule('file', get_lang('UploadFileTypeAre'). ' text/xml', 'mimetype', 'text/xml');
$form->addRule ( 'file', get_lang ( 'UploadFileSizeLessThan' ) . ($max_upload_file_size) . ' MB', 'maxfilesize', $max_upload_file_size * 1048576 );
$form->addRule ( 'file', get_lang ( 'UploadFileNameAre' ) . ' *.zip,*.rar,*.doc,*.xls,*.ppt,*.pdf', 'filename', '/\\.(zip|rar|doc|xls|ppt|pdf)$/' );

$group = array ();
$group [] = & HTML_QuickForm::createElement ( 'submit', 'submitWork', get_lang ( 'Submit' ), 'class="inputSubmit"' );
$group [] = & HTML_QuickForm::createElement ( 'submit', 'submitAsDraft', get_lang ( 'SaveAsDraft' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, null, '&nbsp;', '&nbsp;&nbsp;' );

$form->setDefaults ( $defaults );
Display::setTemplateBorder ( $form, '98%' );

if ($form->validate ()) {
	$form->freeze ();
	$data = $form->exportValues ();
	
	//附件处理
	$file_element = & $form->getElement ( 'file' );
	$title = $data ['title'];
	$author = $_user ['firstName'];
	$content = $data ['content'];
	$creation_time = date ( "Y-m-d H:i:s" );
	$sub_status = $data ['sub_status'];
	$assignment_id = $data ['assignment_id'];
	
	$is_allowed_sub = true;
	$isDraft = (isset ( $data ['submitWork'] ) ? 0 : 1);
	$redirect_url = api_get_path ( WEB_PORTAL_PATH ) . 'course_home.php?action=assignment&cidReq=' . $course_code;
	
	if ($action == "assign_sub") { //新增		
		//判断是否允许延期提交
		$sql = "select UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP() as df,is_allow_late_submission from " . $assignment_table . " where id='" . escape ( $assignment_id ) . "'";
		list ( $df, $is_allow_late_submission ) = api_sql_query_one_row ( $sql, __FILE__, __LINE__ );
		if ($is_allow_late_submission == 0 && $df < 0) $is_allowed_sub = false;
		
		if ($is_allowed_sub) {
			$sql_data = array ('assignment_id' => $assignment_id, 
					'title' => $title, 
					'content' => $content, 
					'attachment_uri' => $file_uri, 
					'attachment_name' => $filename, 
					'attachment_size' => 0, 
					'status' => - 1, 
					'creation_time' => date ( 'Y-m-d H:i:s' ), 
					'last_edit_time' => date ( 'Y-m-d H:i:s' ), 
					'student_id' => api_get_user_id (), 
					'author' => $author, 
					'is_draft' => $isDraft );
			$sql_data ['cc'] = api_get_course_code ();
			$sql = Database::sql_insert ( $assignment_submission_table, $sql_data );
			//echo $sql;
			api_sql_query ( $sql, __FILE__, __LINE__ );
			$submission_id = Database::get_last_insert_id ();
			
			//处理附件
			$save_dir = $base_work_dir . "/" . $assignment_id;
			$file_url_prefix = 'assignment/' . $assignment_id . "/";
			$file = AttachmentManager::hanle_upload ( $file_element, 'ASSIGNMENT_SUB', $save_dir, $file_url_prefix );
			if (isset ( $file ) && $file && is_array ( $file )) {
				$filename = $file ['name'];
				$fsize = $file ['size'];
				$file_uri = $file ['new_name'];
				$uniqid = $file ['attachment_uniqid'];
			} else {
				Display::display_msgbox ( get_lang ( 'UploadFileFailed' ), $redirect_url, 'warning' );
			}
			
			//更新附件表
			$sql_row = array ('ref_id' => $submission_id, 'url' => ($file_url_prefix . $file_uri) );
			$sql = Database::sql_update ( $table_attachment, $sql_row, "name=" . Database::escape ( $uniqid ) );
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			$sql_row = array ('attachment' => $uniqid, 'attachment_uri' => $file_uri, 'attachment_name' => $filename, 'attachment_size' => $fsize );
			$sql = Database::sql_update ( $assignment_submission_table, $sql_row, "id=" . Database::escape ( $submission_id ) );
			api_sql_query ( $sql, __FILE__, __LINE__ );
			
			if ($isDraft == 0) {
				api_item_property_update ( $_course, TOOL_ASSIGNMENT, $assignment_id, 'TeacherAssignmentAdded', $user_id, 0, NULL, (date ( "Y-m-d H:i:s", time () )), $deadline );
				alertTeacher ( $assignment_id, $submission_id );
			}
                        $url= api_get_path ( WEB_CODE_PATH ) . "assignment/assignment_info_stud.php?action=go";
			tb_close();
//			Display::display_msgbox ( get_lang ( 'AssignmentSubmited' ), $redirect_url );
		} else {
			Display::display_msgbox ( get_lang ( 'NotAllowedSubmitAssignment' ), $redirect_url, 'warning' );
		}
	}
	
	if ($action == "assign_sub_edit_save") {
		//上传附件
		$save_dir = $base_work_dir . '/' . $assignment_id;
		$file_url_prefix = 'assignment/' . $assignment_id . "/";
		$file = AttachmentManager::hanle_upload ( $file_element, 'ASSIGNMENT_SUB', $save_dir, $file_url_prefix );
		//var_dump($file);
		//exit;
		if (isset ( $file ) && $file && is_array ( $file )) {
			$filename = $file ['name'];
			$fsize = $file ['size'];
			$file_uri = $file ['new_name'];
			$uniqid = $file ['attachment_uniqid'];
		} else {
			Display::display_msgbox ( get_lang ( 'UploadFileFailed' ), $redirect_url, 'warning' );
		}
		
		//删除原先附件				
		if (strlen ( $file ['name'] ) > 0) {
			$file_path_prefix = $course_dir . "/";
			AttachmentManager::del_all_attachment ( 'ASSIGNMENT_SUB', $id, $file_path_prefix );
		}
		//更新附件表
		$sql = "UPDATE " . $table_attachment . " SET ref_id='" . Database::escape_string ( $id ) . "' WHERE name='" . $uniqid . "'";
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		//如果为回退作业的提交,更新为待批改状态
		if ($sub_status == '2') {
			$new_sub_status = '-1';
			$sql = "SELECT id FROM " . $assignment_submission_table . " WHERE student_id='" . api_get_user_id () . "' AND assignment_id='" . escape ( $assignment_id ) . "' AND is_draft=1";
			$sub_id = Database::getval ( $sql, __FILE__, __LINE__ );
			if (isset ( $sub_id )) {
				$sql = "SELECT * FROM " . $assignment_feedback_table . " WHERE submission_id='" . $sub_id . "'";
				if (Database::if_row_exists ( $sql )) $new_sub_status = '0';
			}
			
			$sql = "UPDATE " . $assignment_submission_table . " SET status='" . $new_sub_status . "' where id='" . escape ( $id ) . "'";
			api_sql_query ( $sql, __FILE__, __LINE__ );
		}
		
		//保存提交的作业
		$sql_row = array ('assignment_id' => $assignment_id, 'title' => $title, 'content' => $content, 'last_edit_time' => date ( 'Y-m-d H:i:s' ), 'student_id' => api_get_user_id (), 'author' => $author, 'is_draft' => $isDraft );
		if (isset ( $file_uri ) && ! empty ( $file_uri )) {
			$sql_row ['attachment'] = $uniqid;
			$sql_row ['attachment_name'] = $filename;
			$sql_row ['attachment_uri'] = $file_uri;
			$sql_row ['attachment_size'] = $fsize;
		}
		$sql = Database::sql_update ( $assignment_submission_table, $sql_row, "id=" . Database::escape ( $id ) );
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		if ($isDraft == 0) {
			api_item_property_update ( $_course, TOOL_ASSIGNMENT, $assignment_id, 'StudentAssignmentUpdated', $user_id );
			alertTeacher ( $assignment_id, $id );
		}
                tb_close();
//		Display::display_msgbox ( get_lang ( 'AssignmentSubmited' ), $redirect_url );
	}

}

$htmlHeadXtra [] = Display::display_kindeditor ( 'description' );
Display::display_reduced_header ();

$form->display ();

Display::display_footer ();
