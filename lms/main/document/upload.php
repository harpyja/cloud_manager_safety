<?php
/*
 ==============================================================================
 上传课程文档
 ==============================================================================
 */

$language_file = 'document';
include_once ("../inc/global.inc.php");
include_once ('document.inc.php');
api_protect_course_script ();
$is_allowed_to_edit = api_is_allowed_to_edit ();
if (! $is_allowed_to_edit) api_not_allowed ();
set_time_limit ( 0 );
include_once (api_get_path ( LIB_PATH ) . 'pclzip/pclzip.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');

$courseDir = api_get_course_code () . "/document";
$base_work_dir = api_get_path ( SYS_COURSE_PATH ) . $courseDir;

if (isset ( $_GET ['path'] ) && $_GET ['path'] != '') {
	$path = urldecode ( getgpc ( 'path', 'G' ) );
} elseif (isset ( $_POST ['curdirpath'] )) {
	$path = urldecode ( getgpc ( 'curdirpath', 'P' ) );
} else {
	$path = '/';
}

if (! DocumentManager::get_document_id ( $_course, $path )) $path = '/';

Display::display_header ( NULL, FALSE );

// 处理上传文件
if (IS_POST && isset ( $_FILES ['user_upload'] )) {
	$upload_ok = process_uploaded_file ( $_FILES ['user_upload'] );
	if ($upload_ok) {
		$upload_path = trim ( urldecode ( getgpc ( 'curdirpath', 'P' ) ) );
		if ($upload_path != '/') $upload_path = $upload_path . '/';
		if (! is_dir ( $base_work_dir . $upload_path )) mkdir ( $base_work_dir . $upload_path, CHMOD_NORMAL );
		$new_path = handle_uploaded_document ( $_course, $_FILES ['user_upload'], $base_work_dir, $upload_path, api_get_user_id (), NULL, 0, $_POST ['title'] );
		if ($new_path) {
			$redirect_url = 'document.php?' . api_get_cidreq () . '&curdirpath=' . urlencode ( getgpc ( 'curdirpath', 'P' ) );
			tb_close ( $redirect_url );
		} else {
			Display::display_error_message ( '文件信息保存失败!' );
			exit ();
		}
	} else {
		Display::display_warning_message ( get_lang ( 'OperationFailed' ) );
		exit ();
	}
}

$form = new FormValidator ( 'upload', 'POST', 'upload.php', '', 'enctype="multipart/form-data"' );
//$form->addElement ( 'header', 'header', get_lang ( 'UplUploadDocument' ) );
$form->addElement ( 'hidden', 'curdirpath', urldecode ( $path ) );

$form->addElement ( 'text', 'title', get_lang ( 'Title' ), array ('style' => "width:350px", 'class' => 'inputText' ) );
$form->addRule ( 'title', get_lang ( 'ThisFieldIsRequired' ), 'required' );

//上传文件
$form->addElement ( 'file', 'user_upload', get_lang ( 'UplUploadDocument' ), array ('style' => "width:80%", 'class' => 'inputText' ) );
$form->addRule ( 'user_upload', get_lang ( 'ThisFieldIsRequired' ), 'required' );

//备注说明
//$form->addElement ( 'textarea', 'comment', get_lang ( 'Description' ), array ('cols' => 50, 'rows' => 4 ,'class'=>'inputText') );


$group = array ();
$group [] = $form->createElement ( 'submit', 'submitDocument', get_lang ( 'Ok' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.location.reload();self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

Display::setTemplateBorder ( $form, '100%' );
$form->add_real_progress_bar ( 'DocumentUpload', 'user_upload' );
//Display::display_warning_message(get_lang('DocumentStoreTip'));
echo '<em>' . get_lang ( 'UploadFileSizeLessThan' ) . get_upload_max_filesize ( api_get_setting ( "upload_max_filesize" ) ) . 'M</em><br/>';
$form->display ();
Display::display_footer ();