<?php
/*
 ==============================================================================
 INIT SECTION
 ==============================================================================
 */

// name of the language file that needs to be included
$language_file = array ('assignment', 'admin' );

require_once ('../inc/global.inc.php');
require_once ('assignment.lib.php');

api_protect_course_script ();
$is_allowed_to_edit = api_is_allowed_to_edit ();
if (! $is_allowed_to_edit) api_not_allowed ();

$id = intval (getgpc("id") );
$action = getgpc ( 'action' );
$delete = getgpc ( 'delete' );
$display_tool_options =  getgpc("display_tool_options");
$display_assignment_form = getgpc("display_assignment_form");
if (! empty ( $_GET ['message'] )) $message = urldecode ( getgpc ( 'message', 'G' ) );

$user_id = api_get_user_id ();
$course_code = api_get_course_code ();
$base_work_dir = api_get_path ( SYS_COURSE_PATH ) . $course_code . '/assignment';
$http_www = api_get_path ( WEB_COURSE_PATH ) . $course_code . '/assignment';

$display_assignment_form = "true";
(! empty ( $display_tool_options ) && $display_tool_options = "true") ? $display_tool_options = true : $display_tool_options = false;

(! empty ( $display_assignment_form ) && $display_assignment_form = "true") ? $display_assignment_form = true : $display_assignment_form = false;

if ($display_assignment_form) {
	$tool_name = get_lang ( "UploadAssignment" );
	$interbreadcrumb [] = array ("url" => "index.php", "name" => get_lang ( 'StudentPublications' ) );
}

//Display::display_header($tool_name);


if ($is_allowed_to_edit) {
	if (is_equal ( $action, "assign_edit" )) {
		$sql = "select * from " . $assignment_table . " where id=" . Database::escape_string (getgpc("id","G") );
		$sql_result = api_sql_query ( $sql, __FILE__, __LINE__ );
		if ($data = mysql_fetch_array ( $sql_result )) {
			$title = $data ['title'];
			$author = $data ['author'];
			$content = $data ['content'];
			$deadline = $data ['deadline'];
			$type = $data ['assignment_type'];
			$is_allow_late_submission = $data ['is_allow_late_submission'];
			$priv_status = $data ['priv_status'];
			$is_published = $data ['is_published'];
			$attachment_name = $data ['attachment_name'];
			$attachment_uri = $data ['attachment_uri'];
			$attachment_size = $data ['attachment_size'];
			$assignment_type = $data ['assignment_type'];
		}
	}
}

require_once (api_get_path ( LIBRARY_PATH ) . 'formvalidator/FormValidator.class.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'fileDisplay.lib.php');

if ($display_assignment_form) {
	$form = new FormValidator ( 'form1', 'POST', $_SERVER ['PHP_SELF'], '', 'enctype="multipart/form-data"' );
	$form->addElement ( 'hidden', 'assignment_type', 'INDIVIDUAL' );
	if (is_equal ( $action, "assign_edit" )) {
		$form->addElement ( 'hidden', 'action', 'assign_edit_save' );
		$form->addElement ( 'hidden', 'id', intval(getgpc("id","G")) );
	} else {
		$form->addElement ( 'hidden', 'action', 'assign' );
	}
	
	//		$form->addElement ( 'header', 'header', get_lang ( 'AssignWork' ) );
	

	//作者
	$titleAuthors = $form->addElement ( 'hidden', 'author', get_lang ( "Authors" ), array ('style' => "width:80%", 'class' => 'inputText', 'readonly' => 'true' ) );
	$defaults ["author"] = ($action == "assign_edit" ? stripslashes ( $author ) : $_user ['firstName']);
	
	//标题
	$titleWork = $form->addElement ( 'text', 'title', get_lang ( "TitleWork" ), array ('style' => "width:80%", 'class' => 'inputText' ) );
	$defaults ["title"] = ($action == "assign_edit" ? stripslashes ( $title ) : '');
	$form->addRule ( 'title', get_lang ( 'ThisFieldIsRequired' ), 'required' );
	
	$time = (is_equal ( $action, "assign_edit" ) ? strtotime ( $deadline ) : strtotime ( "+1 day" ));
	$form->addElement ( 'calendar_datetime', 'deadline', get_lang ( "Deadline" ), null, array ('show_time' => TRUE ) );
	$defaults ['deadline'] = date ( 'Y-m-d H:i', $time );
	$form->addRule ( 'deadline', get_lang ( 'ThisFieldIsRequired' ), 'required' );
	
	//内容
	$form->addElement ( 'textarea', 'content', get_lang ( 'Content' ), array ('id' => 'description', 'wrap' => 'virtual', 'class' => 'inputText', 'style' => 'width:100%;height:260px' ) );
	
	$defaults ["content"] = ($action == "assign_edit" ? stripslashes ( $content ) : "");
	
	//附件
	$form->addElement ( 'file', 'file', get_lang ( 'DownloadFile' ), array ('style' => "width:350px", 'class' => 'inputText' ) );
	if ($action == "assign_edit") {
		$form->addElement ( 'static', 'fileUpload', "", get_lang ( "fileUploadedName" ) . "<a href=\"" . $http_www . "/" . getgpc("id","G") . "/" . $attachment_uri . "\">" . $attachment_name . "</a>(" . (( int ) ($attachment_size / 1024)) . "KB)," . get_lang ( 'fileUploadedTip' ) );
	}
	//$form->addRule('file', 'Upload is required', 'uploadedfile');
	$upload_max_filesize = get_upload_max_filesize ( api_get_course_setting ( "upload_max_filesize" ) );
	$form->addRule ( 'file', get_lang ( 'UploadFileSizeLessThan' ) . ($upload_max_filesize) . ' MB', 'maxfilesize', $upload_max_filesize * 1048576 );
	//$form->addRule('file', get_lang('UploadFileTypeAre'). ' text/xml', 'mimetype', 'text/xml');
	$form->addRule ( 'file', get_lang ( 'UploadFileNameAre' ) . ' *.zip,*.rar,*.doc,*.xls,*.ppt,*.pdf,*.docx,*.xlsx,*.pptx', 'filename', '/\\.(zip|rar|doc|xls|ppt|pdf|docx|xlsx|pptx)$/' );
	
	//允许迟交?			
	$group = array ();
	$group [] = & HTML_QuickForm::createElement ( 'radio', 'is_allow_late_submission', null, get_lang ( 'AllowLateSub' ) . "<br>", 1 );
	$group [] = & HTML_QuickForm::createElement ( 'radio', 'is_allow_late_submission', null, get_lang ( 'NotAllowLateSub' ) . '<br>', 0 );
	$form->addGroup ( $group, 'is_allow_late_submission', get_lang ( 'IsAllowedSubLate' ), '', false );
	$defaults ['is_allow_late_submission'] = ($action == "assign_edit" ? $is_allow_late_submission : 1);
	
	//类型
	/*$group = array();
		 $group[] =& HTML_QuickForm::createElement('radio', 'assignment_type',null,get_lang('IndividualWork')."<br>","INDIVIDUAL");
		 $group[] =& HTML_QuickForm::createElement('radio', 'assignment_type',null,get_lang('GroupWork').'<br>','GROUP');
		 $form->addGroup($group, 'assignment_type', get_lang('AssignmentType'), '',false);
		 $defaults['assignment_type'] = ($action=="assign_edit"?$assignment_type:"INDIVIDUAL");*/
	
	//访问权限
	$group = array ();
	$group [] = & HTML_QuickForm::createElement ( 'radio', 'priv_status', null, get_lang ( 'AssignmentPriv0' ) . "<br>", 0 );
	$group [] = & HTML_QuickForm::createElement ( 'radio', 'priv_status', null, get_lang ( 'AssignmentPriv1' ) . '<br>', 1 );
//	$group [] = & HTML_QuickForm::createElement ( 'radio', 'priv_status', null, get_lang ( 'AssignmentPriv2' ) . '<br>', 2 );
	$form->addGroup ( $group, 'priv_status', get_lang ( 'AssignmentPriv' ), '', false );
	$defaults ['priv_status'] = ($action == "assign_edit" ? $priv_status : 0);
	
	//是否立即发布, 发布之后的作业不允许再设置为未发布
	/*$group = array();
		 $group[] =& HTML_QuickForm::createElement('radio', 'is_published',null,get_lang('Yes')."&nbsp;&nbsp;",1);
		 $group[] =& HTML_QuickForm::createElement('radio', 'is_published',null,get_lang('No').'<br>',0);
		 $form->addGroup($group, 'is_published', get_lang('isPublishedNow'), '',false);
		 $defaults['is_published'] = (is_equal($_REQUEST['action'],"assign_edit")?$is_published:0);*/
	$group = array ();
	if (is_equal ( $_REQUEST ['action'], "assign_edit" )) {
		if ($is_published == 1) {
			$form->addElement ( "hidden", "is_published", "1" );
		} elseif ($is_published == 0) {
			$group [] = & HTML_QuickForm::createElement ( 'radio', 'is_published', null, get_lang ( 'Yes' ) . "&nbsp;&nbsp;", 1 );
			$group [] = & HTML_QuickForm::createElement ( 'radio', 'is_published', null, get_lang ( 'No' ) . '<br>', 0 );
			$form->addGroup ( $group, 'is_published', get_lang ( 'isPublishedNow' ), '', false );
			$defaults ['is_published'] = 0;
		}
	} else {
		$group [] = & HTML_QuickForm::createElement ( 'radio', 'is_published', null, get_lang ( 'Yes' ) . "&nbsp;&nbsp;", 1 );
		$group [] = & HTML_QuickForm::createElement ( 'radio', 'is_published', null, get_lang ( 'No' ) . '<br>', 0 );
		$form->addGroup ( $group, 'is_published', get_lang ( 'isPublishedNow' ), '', false );
		$defaults ['is_published'] = 0;
	}
	
	//提交
	$group = array ();
	$group [] = $form->createElement ( 'submit', 'submitWork', get_lang ( 'Ok' ), 'class="inputSubmit"' );
	$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
	$form->addGroup ( $group, 'submitWork', '&nbsp;', null, false );
	
	$form->setDefaults ( $defaults );
	
	Display::setTemplateBorder ( $form, '98%' );
	
	if ($is_allowed_to_edit) {
		
		if ($form->validate ()) {
			$form->freeze ();
			//$data = $form->exportValues();
			$data = $form->getSubmitValues ();
			//var_dump($data);exit;
			

			//附件处理
			$title = $data ['title'];
			$author = $data ['author'];
			$content = $data ['content'];
			$deadline = $data ['deadline'] . ":00";
			$type = $data ['assignment_type'];
			$is_allow_late_submission = $data ['is_allow_late_submission'];
			$priv_status = $data ['priv_status'];
			$is_published = $data ['is_published'];
			$assignment_type = $data ['assignment_type'];
			//$assignment_type=(isset($assignment_type) && !empty($assignment_type) && strlen($assignment_type)>0)?$data['assignment_type']:'INDIVIDUAL';
			$published_time = ($is_published == "1" ? date ( "Y-m-d H:i:s" ) : "0000-00-00 00:00:00");
			
			$file_element = & $form->getElement ( 'file' );
			if (is_equal ( $_REQUEST ['action'], "assign_edit_save" )) {
				//上传附件
				$save_dir = $base_work_dir . '/' . $id;
				$file_url_prefix = 'assignment/' . $id . "/";
				$file = AttachmentManager::hanle_upload ( $file_element, 'ASSIGNMENT_MAIN', $save_dir, $file_url_prefix );
				if (isset ( $file ) && $file && is_array ( $file )) {
					$filename = $file ['name'];
					$fsize = $file ['size'];
					$file_uri = $file ['new_name'];
					$uniqid = $file ['attachment_uniqid'];
				} else {
					$redirect_url = 'index.php?cidReq=' . $course_code . '&action=show_message&error_message=' . urlencode ( get_lang ( 'UploadFileFailed' ) );
					api_redirect ( $redirect_url );
				}
				
				//删除原先附件						
				if (strlen ( $file ['name'] ) > 0) {
					$file_path_prefix = $course_code . '/assignment/';
					AttachmentManager::del_all_attachment ( 'ASSIGNMENT_MAIN', $id, $file_path_prefix );
				}
				//更新附件表
				$sql = "UPDATE " . $table_attachment . " SET ref_id='" . Database::escape_string ( $id ) . "' WHERE name='" . $uniqid . "'";
				 $sql .= " AND cc='" . api_get_course_code () . "' ";
				api_sql_query ( $sql, __FILE__, __LINE__ );
				
				//更新作业表
				$sql_row = array ('title' => $title, 
						'content' => $content, 
						'deadline' => $deadline, 
						'is_published' => $is_published, 
						'published_time' => $published_time, 
						'author' => $author, 
						'assignment_type' => $type, 
						'is_allow_late_submission' => $is_allow_late_submission, 
						'priv_status' => $priv_status );
				if (is_not_blank ( $file_uri ) && strlen ( $file ['name'] ) > 0) {
					$sql_row ['attachment'] = $uniqid;
					$sql_row ['attachment_uri'] = $file_uri;
					$sql_row ['attachment_name'] = $filename;
					$sql_row ['attachment_size'] = $fsize;
				}
				$sql = Database::sql_update ( $assignment_table, $sql_row, "id=" . Database::escape ( $id ) );
				api_sql_query ( $sql, __FILE__, __LINE__ );
				//echo $sql;
				

				api_item_property_update ( $_course, TOOL_ASSIGNMENT, $id, 'TeacherAssignmentUpdated', $user_id );
				if ($is_published) {
					//alertStudent($assign_id);
					api_item_property_update ( $_course, TOOL_ASSIGNMENT, $id, 'visible', $user_id );
				} else {
					api_item_property_update ( $_course, TOOL_ASSIGNMENT, $id, 'invisible', $user_id );
				}
				$redirect_url = 'index.php?cidReq=' . $course_code . '&action=show_message&message=' . urlencode ( get_lang ( 'AssignmentSaved' ) );
			} else if (is_equal ( $_REQUEST ['action'], "assign" )) { //新增一个作业项		
				$sql_data = array ('title' => $title, 
						'content' => $content, 
						'deadline' => $deadline, 
						'is_published' => $is_published, 
						'published_time' => $published_time, 
						'author' => $author, 
						'creation_time' => date ( "Y-m-d H:i:s" ), 
						'group_id' => 0, 
						'assignment_type' => $assignment_type, 
						'is_allow_late_submission' => $is_allow_late_submission, 
						'priv_status' => $priv_status );
				$sql_data ['cc'] = api_get_course_code ();
				$sql = Database::sql_insert ( $assignment_table, $sql_data );
				api_sql_query ( $sql, __FILE__, __LINE__ );
				$assign_id = mysql_insert_id ();
				//$sql="UPDATE $assignment_table SET content='".Database::escape_string($content)."' WHERE id=".$assign_id;
				//api_sql_query($sql,__FILE__,__LINE__);
				

				//处理附件
				$newDir = $base_work_dir . "/" . $assign_id;
				if (! file_exists ( $newDir )) {
					mkdir ( $newDir, CHMOD_NORMAL );
					$fd = fopen ( $newDir . "/index.php", "w" );
					fwrite ( $fd, "<html>ACCESS DENIED!</html>" );
					fclose ( $fd );
				}
				
				$save_dir = $newDir;
				$file_url_prefix = 'assignment/' . $assign_id . "/";
				$file = AttachmentManager::hanle_upload ( $file_element, 'ASSIGNMENT_MAIN', $save_dir, $file_url_prefix );
				if (isset ( $file ) && $file && is_array ( $file )) {
					$filename = $file ['name'];
					$fsize = $file ['size'];
					$file_uri = $file ['new_name'];
					$uniqid = $file ['attachment_uniqid'];
				} else {
					$redirect_url = 'index.php?cidReq=' . $course_code . '&action=show_message&error_message=' . urlencode ( get_lang ( 'UploadFileFailed' ) );
					tb_close ( $redirect_url );
				}
				
				//更新附件表
				$sql = "UPDATE " . $table_attachment . " SET ref_id='" . $assign_id . "',url='" . escape ( $file_url_prefix . $file_uri ) . "' WHERE name='" . $uniqid . "'";
				$sql .= " AND cc='" . api_get_course_code () . "' ";
				api_sql_query ( $sql, __FILE__, __LINE__ );
				
				/*$sql="UPDATE ".$table_attachment." SET url='".($_course['path']."/assignment/".$assign_id."/".$file_uri)."',ref_id='".$assign_id."' where name='".$uniqid."'";
					 api_sql_query($sql,__FILE__,__LINE__);*/
				
				$sql_row = array ('attachment' => $uniqid, 'attachment_uri' => $file_uri, 'attachment_name' => $filename, 'attachment_size' => $fsize );
				$sql = Database::sql_update ( $assignment_table, $sql_row, "id=" . Database::escape ( $assign_id ) );
				api_sql_query ( $sql, __FILE__, __LINE__ );
				
				api_item_property_update ( $_course, TOOL_ASSIGNMENT, $assign_id, 'TeacherAssignmentAdded', $user_id, 0, NULL, (date ( "Y-m-d H:i:s", time () )), $deadline );
				if ($is_published) {
					api_item_property_update ( $_course, TOOL_ASSIGNMENT, $assign_id, 'visible', $user_id );
					alertStudent ( $assign_id );
				} else {
					api_item_property_update ( $_course, TOOL_ASSIGNMENT, $assign_id, 'invisible', $user_id );
				}
				$redirect_url = 'index.php?cidReq=' . $course_code . '&action=show_message&message=' . urlencode ( get_lang ( 'AssignmentAdded' ) );
			}
			
			tb_close ( $redirect_url );
		}
	}
	
	$htmlHeadXtra [] = Display::display_kindeditor ( 'description', 'normal' );
	Display::display_header ( $tool_name, FALSE );
	
	$form->display ();

}
Display::display_footer ();
