<?php
/*
 ==============================================================================
 上传HTML打包课程文档
 ==============================================================================
 */

$language_file = 'document';
include_once ("../inc/global.inc.php");
$this_section = SECTION_COURSES;
api_block_anonymous_users ();
//api_protect_course_script ();

$is_allowed_to_edit = api_is_allowed_to_edit ();

$courseDir = api_get_course_code () . "/document";
$sys_course_path = api_get_path ( SYS_COURSE_PATH );
$base_work_dir = $sys_course_path . $courseDir;
//if (! $is_allowed_to_edit) api_not_allowed ();

include_once (api_get_path ( LIB_PATH ) . 'pclzip/pclzip.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
require_once (api_get_path ( SYS_CODE_PATH ) . "courseware/cw.lib.inc.php");
include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');

$table_courseware = Database::get_course_table ( TABLE_COURSEWARE );
$pkg_id =intval( getgpc ( 'id', 'G' ));
$type = isset ( $_REQUEST ['type'] ) ? getgpc ( 'type' ) : 'htmlcw';

if ($pkg_id) {
	$sql = "SELECT * FROM " . $table_courseware . " WHERE id='" . Database::escape_string ( $pkg_id ) . "'";
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$row = api_store_result_array ( $res );
	$courseware_info = (is_array ( $row ) ? $row [0] : NULL);
}

$nameTools = get_lang ( 'UploadHTMLPackage' );
$interbreadcrumb [] = array ("url" => "cw_package_list.php", "name" => get_lang ( "HTMLPackageCourseware" ) );

$form = new FormValidator ( 'upload', 'POST', $_SERVER ['PHP_SELF'], '_self' );

//$form->addElement ( 'header', 'header', get_lang ( 'EditUploadHTMLPackage' ) );


$form->addElement ( 'hidden', 'action', 'edit_save' );
$form->addElement ( 'hidden', 'package_id', $pkg_id );
$form->addElement ( 'hidden', 'type', $type );

//$form->addElement('file','user_upload',get_lang('File'),array('style'=>"width:350px",'class'=>'inputText'));
//$form->addRule('user_upload', get_lang('ThisFieldIsRequired'), 'required');


$form->addElement ( 'text', 'title', get_lang ( 'Title' ), array ('size' => '45', 'style' => "width:350px", 'class' => 'inputText' ) );
$form->addRule ( 'title', get_lang ( 'ThisFieldIsRequired' ), 'required' );

if (is_equal ( $type, 'htmlcw' )) {
	$form->addElement ( 'text', 'attribute', get_lang ( 'DefaultPage' ), array ('size' => '45', 'style' => "width:350px", 'class' => 'inputText' ) );
	$form->addRule ( 'attribute', get_lang ( 'ThisFieldIsRequired' ), 'required' );
	$form->addRule ( 'attribute', get_lang ( 'OnlyLettersAndNumbersAllowed' ), 'regex', '/^[a-zA-Z0-9\-_\.\/]+$/i' );
} else {

}

//最小学习时间
$form->add_textfield ( 'learning_time', get_lang ( 'MinLearningTime' ), true, array ('id' => 'learning_time', 'style' => "width:60px;text-align:right;", 'class' => 'inputText', 'maxlength' => 60 ) );
$form->addRule ( 'learning_time', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );

//显示顺序
$form->add_textfield ( 'display_order', get_lang ( 'DisplayOrder' ), true, array ('id' => 'learning_order', 'style' => "width:60px;text-align:right;", 'class' => 'inputText', 'maxlength' => 60 ) );
$form->addRule ( 'display_order', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'display_order', get_lang ( 'MustLargerThan0' ), 'callback', 'range_check' );

$form->addElement ( 'textarea', 'comment', get_lang ( 'Comment' ), array ('cols' => 50, 'rows' => 5, 'wrap' => 'virtual', 'class' => 'inputText' ) );

$form->addElement ( 'hidden', 'unzip', "1" );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submitDocument', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ( $courseware_info );

Display::setTemplateBorder ( $form, '98%' );

if ($form->validate ()) {
	//$data=$form->exportValues();
	$data = $form->getSubmitValues ();
	$default_page = (empty ( $data ['attribute'] ) ? "" : $data ['attribute']);
	//$open_target = $data ['open_target'];
	$title = trim ( $data ['title'] );
	$comment = $data ['comment'];
	$display_order = trim ( $data ["display_order"] );
	if (isset ( $_POST ['action'] ) && getgpc ( 'action', 'P' ) == 'edit_save') {
		$sql_data = array ("title" => trim ( $title ), "comment" => $comment, "display_order" => $display_order, 'learning_time' => intval ( $data ["learning_time"] ) );
		if ($default_page) $sql_data ['attribute'] = $default_page;
		$sql = Database::sql_update ( $table_courseware, $sql_data, "id='" . escape ( $data ["package_id"] ) . "'" );
		//echo $sql;exit;
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		if ($res) {
			api_item_property_update ( $_course, TOOL_COURSEWARE_PACKAGE, $pkg_id, 'HTMLCoursewareUpdated', api_get_user_id () );
			if (is_equal ( $type, 'htmlcw' )) {
				$redirect_url = 'cw_package_list.php';
			}
			if (is_equal ( $type, 'mediacw' )) {
				$redirect_url = 'cw_media_list.php';
			}
			
			tb_close (  );
		}
	}
}

Display::display_header ( $nameTools, FALSE );

$form->display ();

Display::display_footer ();