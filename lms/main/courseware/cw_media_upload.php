<?php
/*
 ==============================================================================
 上传FLV多媒体课程文档
 ==============================================================================
 */
$language_file = 'document';
include_once ("../inc/global.inc.php");
$this_section = SECTION_COURSES;
api_block_anonymous_users ();
api_protect_course_script ();

$is_allowed_to_edit = api_is_allowed_to_edit ();
if (! $is_allowed_to_edit) api_not_allowed ();

function range_check($inputValue) {
	return (intval ( $inputValue ) > 0);
}

$courseDir = api_get_course_path () . "/document/flv";
$sys_course_path = api_get_path ( SYS_COURSE_PATH );
$base_work_dir = $sys_course_path . $courseDir;
//echo $base_work_dir;
$table_courseware = Database::get_course_table ( TABLE_COURSEWARE );

$ftp_path = api_get_path ( SYS_FTP_ROOT_PATH ) . 'media/';

$max_upload_file_size = get_upload_max_filesize ( api_get_setting ( "upload_max_filesize" ) );

include_once (api_get_path ( LIB_PATH ) . 'pclzip/pclzip.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');
require_once (api_get_path ( SYS_CODE_PATH ) . "courseware/cw.lib.inc.php");

$nameTools = get_lang ( 'UploadHTMLPackage' );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = '<script type="text/javascript">
	$(document).ready( function() {
	$("#upload_file_local").parent().append("<div class=\'onShow\'>' . get_lang ( 'UploadFileSizeLessThan' ) . $max_upload_file_size . 'M</div>");
		
	$("tr.containerBody:eq(6)").hide();
	
	$("#location_local").click(function(){
		$("tr.containerBody:eq(1)").show();
		$("tr.containerBody:eq(2)").hide();
		$("#upload_file_local").parent().find(".onError").remove();
		
		$("#TO_NAME").attr("disabled",true);
		$("#TO_ID").attr("disabled",true);
		$("#link_select").attr("disabled",true);
		$("#link_clear").attr("disabled",true);
		$("#upload_file_local").removeAttr("disabled");
	});
	
	$("#location_remote").click(function(){
		$("tr.containerBody:eq(1)").hide();
		$("tr.containerBody:eq(2)").show();
		
		$("#TO_NAME").removeAttr("disabled");
		$("#TO_ID").attr("disabled",false);
		$("#link_select").removeAttr("disabled");
		$("#link_clear").attr("disabled",false);
		$("#upload_file_local").attr("disabled",true);
	});
	
	$("button.upload:eq(0)").click(function(){		
		if($("#upload_file_local").val()=="" && $("#location_local").attr("checked")==true){
			$("#upload_file_local").parent().find(".onError").remove();
			$("#upload_file_local").parent().append("<div class=\'onError\'>' . get_lang ( "PleaseSelectAFile" ) . '</div>");
			return false;
		}
	});
	
	});</script>';

$form = new FormValidator ( 'upload', 'POST', api_get_self (), '', 'enctype="multipart/form-data"' );

$form->addElement ( 'header', 'header', get_lang ( '上传视频课件' ) );

//$form->addElement ( 'text', 'title', get_lang ( 'Title' ), array ('size' => '45', 'style' => "width:350px", 'class' => 'inputText' ) );
//$form->addRule ( 'title', get_lang ( 'ThisFieldIsRequired' ), 'required' );

//文件位置
//$group = array ();
//$group [] = & HTML_QuickForm::createElement ( 'radio', 'location', null, get_lang ( 'Local' ), 1, array ('id' => 'location_local' ) );
//$group [] = & HTML_QuickForm::createElement ( 'radio', 'location', null, get_lang ( 'Remote' ), 2, array ('id' => 'location_remote' ) );
//$form->addGroup ( $group, 'file_location', get_lang ( 'FileLocation' ), '&nbsp;&nbsp;&nbsp;' );
//$defaults ['file_location'] ['location'] = 1;

//从本地文件中选取
$form->addElement ( 'file', 'user_upload', get_lang ( '文件(mp4)' ), array ('class' => 'inputText', 'style' => 'width:50%', 'id' => 'upload_file_local' ) );
$form->addRule ( 'user_upload', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$form->addRule ( 'user_upload', get_lang ( 'UploadFileSizeLessThan' ) . ($max_upload_file_size) . ' MB', 'maxfilesize', $max_upload_file_size * 1048576 );  

$form->addRule ( 'user_upload', get_lang ( 'UploadFileNameAre' ) . ' *.mp4', 'filename', '/\\.mp4$/' );

//$ftp_files = get_files_in_ftp ( $ftp_path, array ('flv', 'mp4', 'mp3' ) );
//$form->addElement ( 'select', 'file_name', get_lang ( 'UploadLocalFileFromFTPDir' ), $ftp_files, array ('style' => 'width:40%' ) );

//最小学习时间
/*$form->add_textfield ( 'learning_time', get_lang ( 'MinLearningTime' ), true, array ('id' => 'learning_time', 'style' => "width:60px;text-align:right;", 'class' => 'inputText', 'maxlength' => 60 ) );
$form->addRule ( 'learning_time', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'learning_time', get_lang ( 'MustLargerThan0' ), 'callback', 'range_check' );
$defaults ['learning_time'] = 30;*/

//显示顺序
$form->add_textfield ( 'display_order', get_lang ( 'DisplayOrder' ), true, array ('id' => 'learning_order', 'style' => "width:60px;text-align:right;", 'class' => 'inputText', 'maxlength' => 60 ) );
$form->addRule ( 'display_order', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'display_order', get_lang ( 'MustLargerThan0' ), 'callback', 'range_check' );
//$sql = "SELECT COUNT(*)+1 FROM " . $table_courseware . " WHERE cc='" . api_get_course_code () . "' AND cw_type='media'";
$defaults ["display_order"] = get_next_disp_order ();

//标题摘要
$form->addElement ( 'textarea', 'comment', get_lang ( 'Comment' ), array ('cols' => 40, 'rows' => 3, 'wrap' => 'virtual', 'class' => 'inputText' ) );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submitDocument', get_lang ( 'Save' ), 'class="upload" id="upload"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ( $defaults );
Display::setTemplateBorder ( $form, '98%' );
$form->add_real_progress_bar ( 'DocumentUpload', 'user_upload' );

if ($form->validate ()) {
	//设置内存及执行时间
	if (function_exists ( 'ini_set' )) {
		ini_set ( 'memory_limit', '512M' );
		ini_set ( 'max_execution_time', 1800 ); //设置执行时间
	}
	$data = $form->exportValues (); //var_dump($data);exit;
	//$data = $form->getSubmitValues ();
	$file_element = & $form->getElement ( 'user_upload' );
	$open_target = $data ['open_target'];
//	$title = trim ( $data ['title'] );
        $title = $_FILES ['user_upload'] ['name'];
       //$UploadSize=round($_FILES['user_upload']['size']/1024/1024,2);
	$comment = $data ['comment'];
//	$file_location = $data ['file_location'] ['location'];
        $file_location = 1;
	$display_order = trim ( $data ["display_order"] );
	if ($file_location == "1") {
		$upload_ok = process_uploaded_file ( $_FILES ['user_upload'] );
		if ($upload_ok) {
			$new_filename = get_unique_name () . '.' . strtolower ( getFileExt ( $_FILES ['user_upload'] ['name'] ) );
			if (! file_exists ( $base_work_dir )) mkdir ( $base_work_dir, CHMOD_NORMAL );
			$file_element->moveUploadedFile ( $base_work_dir, $new_filename );
			$result = handle_uploaded_media_courseware ( $_course, $_FILES ['user_upload'], $new_filename, api_get_user_id (), NULL, $title, $comment, $open_target, $display_order );
			if ($result) { //成功	
				$redirect_url = "cw_media_list.php?message=" . urlencode ( get_lang ( 'UplUploadSucceeded' ) );
			} else {
				$redirect_url = "cw_media_list.php?message=" . urlencode ( get_lang ( 'UplUploadFailed' ) );
			}
		}
	} 

	else if ($file_location == "2") {
		$operation_ok = false;
		if (substr ( $ftp_path, - 1 ) != '/') $ftp_path = $ftp_path . '/';
		$s = $ftp_path . $data ['file_name'];
		$info = pathinfo ( $s );
		$filename = $info ['basename'];
		$extension = $info ['extension'];
		$file_base_name = str_replace ( '.' . $extension, '', $filename );
		$new_dir = replace_dangerous_char ( trim ( $file_base_name ), 'strict' );
		
		$sys_filename = api_to_system_encoding ( $data ['file_name'], SYSTEM_CHARSET );
		$dest_filename = get_unique_name () . '.' . strtolower ( getFileExt ( $sys_filename ) );
		if (! file_exists ( $base_work_dir )) mkdir ( $base_work_dir, CHMOD_NORMAL );
		$result = copy ( $ftp_path . $sys_filename, $base_work_dir . "/" . $dest_filename );
		
		$audio_total_play_time = getFLVDuration ( $base_work_dir . "/" . $dest_filename );
		$audio_total_play_time = round ( floatval ( $audio_total_play_time ) );
		
		if ($result) {
			$file_size = filesize ( $base_work_dir . "/" . $dest_filename );
			$cw_id = add_media_courseware ( $_course, "/flv/" . $dest_filename, $file_size, $title, $comment, $open_target, 'media', $audio_total_play_time );
			if ($cw_id) {
				api_item_property_update ( $_course, TOOL_COURSEWARE_MEDIA, $cw_id, 'MediaCoursewareAdded', api_get_user_id (), 0, NULL );
				$operation_ok = true;
			}
		}
		
		if ($operation_ok) {
			$redirect_url = "cw_media_list.php?message=" . urlencode ( get_lang ( 'CWUploadSucceeded' ) );
		} else {
			$redirect_url = "cw_media_list.php?message=" . urlencode ( get_lang ( 'CWUploadFailed' ) );
		}
	}
	
	tb_close ();
}
?>
<?php
//echo display_cw_action_menus('mediacw');
Display::display_header ( null, FALSE );
echo '<div id="demo" class="yui-navset" style="margin:10px">';
echo display_cw_type_tab ( 'mediacw' );
echo '<div class="yui-content"><div id="tab1">';
Display::display_confirmation_message ( get_lang ( '请注意文件名必须为英文,数字或下划线' ), false );
$form->display ();
echo '<br><br></div></div></div>';
Display::display_footer ();
