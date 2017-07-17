<?php
/**
 ==============================================================================
 *
 ==============================================================================
 */

// name of the language file that needs to be included
$language_file = array ('course_info', 'admin', 'create_course' );
$cidReset = true;
include_once ("../../inc/global.inc.php");
$this_section = SECTION_PLATFORM_ADMIN;

api_protect_admin_script ();

require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');


function upload_max_filesize_check($inputValue) {
	return (intval ( $inputValue ) > 0 && intval ( $inputValue ) <= get_upload_max_filesize ( 0 ));
}

$course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
$table_course_user = $course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );

$course_code = isset ( $_GET ['course_code'] ) ? getgpc('course_code') : getgpc('code');

//获取课程信息
$course = CourseManager::get_course_information ( $course_code );
if(empty($course)){
	tb_close('course_list.php');
}


$tool_name = get_lang ( 'ModifyCourseInfo' );
$interbreadcrumb [] = array ("url" => api_get_path ( WEB_ADMIN_PATH ) . 'index.php', "name" => get_lang ( 'PlatformAdmin' ) );
$interbreadcrumb [] = array ("url" => "course_list.php", "name" => get_lang ( 'CourseList' ) );

$htmlHeadXtra [] = '<script type="text/javascript">
	$(document).ready( function() {
		$("#upload_max_filesize").parent().append("<div class=\'onShow\'>' . get_lang ( 'UploadFileSizeLessThan' ) . get_upload_max_filesize ( 0 ) . 'M</div>");		
		$("#enable_quiz_pass_control0").parent().append("<div class=\'onShow\'>' . get_lang ( 'EnabledQuizPassControlTip' ) . '</div>");		
		
	});
	</script>';

$old_upload_max_filesize = intval ( api_get_course_setting ( 'upload_max_filesize', $course_code ) ); //上传文件大小旧值


$table_course_setting = Database::get_course_table ( TABLE_COURSE_SETTING, $course ['db_name'] );

//修改课程信息表单
$form = new FormValidator ( 'update_course' );

$form->addElement ( 'header', 'header', get_lang ( 'ModifyCourseSetting' ) );

$form->addElement ( 'hidden', 'code', $course_code );

//标题
$form->add_textfield ( 'title', get_lang ( 'CourseTitle' ), true, array ('style' => "width:250px", 'class' => 'inputText' ) );
$form->freeze ( "title" );

//注册参与本课程需要审核
/*$group = array();
$group[] = $form->createElement('radio', 'is_subscription_needed_approval', null, get_lang('Yes'), 1);
$group[] = $form->createElement('radio', 'is_subscription_needed_approval', null, get_lang('No'), 0);
$form->addGroup($group,'is_subscription_needed_approval',get_lang('SubscribeApproval'),'&nbsp;&nbsp;',false);
$course["is_subscription_needed_approval"]=api_get_course_setting('is_subscription_needed_approval',$course_code);*/

//申请注册本课程时同时选择课程班级
/*$group = array ();
$group [] = $form->createElement ( 'radio', 'is_chose_courseclass_when_subscribe', null, get_lang ( 'Yes' ), 1 );
$group [] = $form->createElement ( 'radio', 'is_chose_courseclass_when_subscribe', null, get_lang ( 'No' ), 0 );
$form->addGroup ( $group, 'is_chose_courseclass_when_subscribe', get_lang ( 'ChoseCourseClassWhenSubscribe' ), '&nbsp;&nbsp;', false );
$course ['is_chose_courseclass_when_subscribe'] = api_get_course_setting ( 'is_chose_courseclass_when_subscribe', $course_code );*/
$form->createElement('hidden','is_chose_courseclass_when_subscribe',0);

//在设置启用课件的学习顺序后, 本章测验必须通过才以进行下一章节的学习
/*$group = array ();
$group [] = $form->createElement ( 'radio', 'enable_quiz_pass_control', null, get_lang ( 'Yes' ), 1, array ('id' => 'enable_quiz_pass_control1' ) );
$group [] = $form->createElement ( 'radio', 'enable_quiz_pass_control', null, get_lang ( 'No' ), 0, array ('id' => 'enable_quiz_pass_control0' ) );
$form->addGroup ( $group, '', get_lang ( 'EnabledQuizPassControl' ), '&nbsp;&nbsp;', false );
$quiz_pass_control = api_get_course_setting ( 'enable_quiz_pass_control', $course_code );
$course ['enable_quiz_pass_control'] = (empty ( $quiz_pass_control ) or $quiz_pass_control == - 1 ? 0 : $quiz_pass_control);*/
$form->createElement('hidden','enable_quiz_pass_control',0);

//磁盘限额
$form->addElement ( 'text', 'disk_quota', get_lang ( 'CourseQuota' ), array ('style' => "width:80px;text-align:right", 'class' => 'inputText' ) );
$form->addRule ( 'disk_quota', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'disk_quota', get_lang ( 'ThisFieldShouldBeNumeric' ), 'numeric' );
$course ['disk_quota'] = $course ['disk_quota'] / 1048576;

//文件上传大小
//$form->addElement('static', null, null, '<em>'.get_lang('UploadFileSizeLessThan').get_upload_max_filesize(0).'M</em>');
$form->addElement ( 'text', 'upload_max_filesize', get_lang ( "UploadMaxFileSize" ), array ('id' => 'upload_max_filesize', 'style' => "width:80px;text-align:right", 'class' => 'inputText' ) );
$course ['upload_max_filesize'] = get_upload_max_filesize ( $old_upload_max_filesize );
$form->addRule ( 'upload_max_filesize', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'upload_max_filesize', get_lang ( 'MaxUploadFileSizeTip' ) . get_upload_max_filesize ( 0 ) . "M", 'callback', 'upload_max_filesize_check' );

//文件上传类型限制
$blacklist = api_get_setting ( 'upload_extensions_blacklist' );
$whitelist = api_get_setting ( 'upload_extensions_whitelist' );
if (api_get_setting ( 'upload_extensions_list_type' ) == 'whitelist') {
	$whilelist_array = ($whitelist ? explode ( ';', $whitelist ) : array ());
	if (is_array ( $whilelist_array ) && $whilelist_array) {
		foreach ( $whilelist_array as $value ) {
			if ($value) $list_arr [$value] = $value;
		}
	}
	$label_upload_filetype = get_lang ( "CourseSettingUploadWhite" );
	$elementName = 'upload_file_type';
	//$selected_file_type_str=api_get_course_setting('upload_file_type',$currentCourseDbName);
	$selected_file_type_str = api_get_course_setting ( 'upload_file_type', $course_code );
}
if (api_get_setting ( 'upload_extensions_list_type' ) == 'blacklist') {
	$blacklist_array = ($blacklist ? explode ( ';', $blacklist ) : array ());
	if (is_array ( $blacklist_array ) && $blacklist_array) {
		foreach ( $blacklist_array as $value ) {
			if ($value) $list_arr [$value] = $value;
		}
	}
	$label_upload_filetype = get_lang ( "CourseSettingUploadBlack" );
	$elementName = 'upload_file_type';
	//$selected_file_type_str=api_get_course_setting('upload_file_type',$currentCourseDbName);
	$selected_file_type_str = api_get_course_setting ( 'upload_file_type', $course_code );
}
$ams = & $form->addElement ( 'advmultiselect', $elementName, null, $list_arr, array ('size' => 8, 'class' => 'pool', 'style' => 'width:150px;' ) );
$ams->setLabel ( array ($label_upload_filetype, get_lang ( 'AvailableFileType' ), '', get_lang ( 'SelectedFileType' ) ) );
$ams->setButtonAttributes ( 'add', array ('value' => '>>', 'class' => 'inputSubmitShort' ) );
$ams->setButtonAttributes ( 'remove', array ('value' => '<<', 'class' => 'inputSubmitShort' ) );
include (api_get_path ( INCLUDE_PATH ) . "conf/templates.php");
$template = $template ["html"] ["advmultiselect"];
$ams->setElementTemplate ( $template );
$course [$elementName] = explode ( ";", $selected_file_type_str );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Save' ), 'onclick="valide()" class="save"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, null, '&nbsp;' );

$course_db_name = $course ['db_name'];
$form->setDefaults ( $course );

Display::setTemplateBorder ( $form, '98%' );

// Validate form
if ($form->validate ()) {
	
	$course = $form->getSubmitValues ();
	//var_dump($course);exit;
	$dbName = getgpc('dbName');
	$course_code = $course ['code'];
	
	$sql_data = array ('disk_quota' => ($course ['disk_quota']) * 1048576 );
	$sql = Database::sql_update ( $course_table, $sql_data, "code=" . Database::escape ( $course_code ) );
	api_sql_query ( $sql, __FILE__, __LINE__ );
	
	//$sql_row=array('value'=>$course['is_subscription_needed_approval']);
	//Database::update_course_table($table_course_setting,$sql_row,"variable = 'is_subscription_needed_approval'",$course_code);
	

	$sql_row = array ('value' => $course ['is_chose_courseclass_when_subscribe'] );
	Database::update_course_table ( $table_course_setting, $sql_row, "variable = 'is_chose_courseclass_when_subscribe'", $course_code );
	
/*	$sql = "SELECT * FROM $table_course_setting WHERE variable = 'enable_quiz_pass_control' AND cc=" . Database::escape ( $course_code );
	if (Database::if_row_exists ( $sql )) {
		$sql_row = array ('value' => $course ['enable_quiz_pass_control'] );
		Database::update_course_table ( $table_course_setting, $sql_row, "variable = 'enable_quiz_pass_control'", $course_code );
	} else {
		$sql_row = array ('value' => $course ['enable_quiz_pass_control'],'variable'=>'enable_quiz_pass_control','category'=>'quiz','cc'=>$course_code );
		$sql=Database::sql_insert($table_course_setting,$sql_row);
		api_sql_query($sql,__FILE__,__LINE__);
	}*/
	
	$new_upload_max_filesize = intval ( $course ['upload_max_filesize'] );
	if ($new_upload_max_filesize != $old_upload_max_filesize) {
		if ($new_upload_max_filesize > get_upload_max_filesize ( 0 )) $new_upload_max_filesize = get_upload_max_filesize ( 0 );
		$sql = Database::update_course_table ( $table_course_setting, array ('value' => $new_upload_max_filesize ), "variable = 'upload_max_filesize'", $course_code );
		//api_sql_query($sql,__FILE__,__LINE__);
	}
	
	if (count ( $course ['upload_file_type'] ) == 0) $course ['upload_file_type'] = array ('zip', 'flv', 'mp3', 'ppt' );
	if (is_array ( $course ['upload_file_type'] ) && $course ['upload_file_type']) {
		$sql = Database::update_course_table ( $table_course_setting, array ('value' => implode ( ';', $course ['upload_file_type'] ) ), "variable = 'upload_file_type'", $course_code );
		//api_sql_query($sql,__FILE__,__LINE__);
	}
	
	$log_msg = get_lang ( 'EditCourseInfo' ) . "code=" . $course_code;
	api_logging ( $log_msg, 'COURSE', 'EditCourseInfo' );
	
	$redirect_url = 'course_list.php';
	echo '<script>self.parent.location.href="' . $redirect_url . '";self.parent.tb_remove();</script>';
	exit ();
}

$htmlHeadXtra [] = $ams->getElementJs ( false );
Display::display_header ( $tool_name ,FALSE);

$form->display ();

Display::display_footer ();
