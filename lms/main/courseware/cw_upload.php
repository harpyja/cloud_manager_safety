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
api_protect_course_script ();

$is_allowed_to_edit = api_is_allowed_to_edit ();
if (! $is_allowed_to_edit) api_not_allowed ();
$user_id = api_get_user_id ();
$table_courseware = Database::get_course_table ( TABLE_COURSEWARE );
$courseDir = api_get_course_code () . '/html/';
$base_work_dir = api_get_path ( SYS_COURSE_PATH ) . $courseDir;
$max_upload_file_size = get_upload_max_filesize ( api_get_setting ( "upload_max_filesize" ) );
$ftp_path = api_get_path ( SYS_FTP_ROOT_PATH ) . 'zip/';

require_once (api_get_path ( SYS_CODE_PATH ) . "courseware/cw.lib.inc.php");
include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');

$htmlHeadXtra [] = Display::display_thickbox ();
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = '<script type="text/javascript">
	$(document).ready( function() {
	$("#upload_file_local").parent().append("<div class=\'onShow\'>' . get_lang ( 'UploadFileSizeLessThan' ) . $max_upload_file_size . 'M</div>");
		
	$("tr.containerBody:eq(3)").hide();
	
	$("#location_local").click(function(){
		$("tr.containerBody:eq(2)").show();
		$("tr.containerBody:eq(3)").hide();
		$("#upload_file_local").parent().find(".onError").remove();
		
		$("#TO_NAME").attr("disabled",true);
		$("#TO_ID").attr("disabled",true);
		$("#link_select").attr("disabled",true);
		$("#link_clear").attr("disabled",true);
		$("#upload_file_local").removeAttr("disabled");
	});
	
	$("#location_remote").click(function(){
		$("tr.containerBody:eq(2)").hide();
		$("tr.containerBody:eq(3)").show();
		
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

$nameTools = get_lang ( 'UploadHTMLPackage' );
$interbreadcrumb [] = array ("url" => "cw_package_list.php", "name" => get_lang ( "HTMLPackageCourseware" ) );

$form = new FormValidator ( 'upload', 'POST', $_SERVER ['PHP_SELF'], '', 'enctype="multipart/form-data"' );

$form->addElement ( 'header', 'header', get_lang ( 'UploadHTMLPackage' ) );

$form->addElement ( 'text', 'title', get_lang ( 'Title' ), array ('size' => '45', 'style' => "width:350px", 'class' => 'inputText' ) );
$form->addRule ( 'title', get_lang ( 'ThisFieldIsRequired' ), 'required' );

//文件位置
$group = array ();
$group [] = & HTML_QuickForm::createElement ( 'radio', 'location', null, get_lang ( 'Local' ), 1, array ('id' => 'location_local' ) );
$group [] = & HTML_QuickForm::createElement ( 'radio', 'location', null, get_lang ( 'Remote' ), 2, array ('id' => 'location_remote' ) );
$form->addGroup ( $group, 'file_location', get_lang ( 'FileLocation' ), '&nbsp;&nbsp;&nbsp;' );
$defaults ['file_location'] ['location'] = 1;

//从本地文件中选取
$form->addElement ( 'file', 'user_upload', get_lang ( 'File' ), array ('class' => 'inputText', 'style' => 'width:50%', 'id' => 'upload_file_local' ) );
$form->addRule ( 'user_upload', get_lang ( 'UploadFileSizeLessThan' ) . ($max_upload_file_size) . ' MB', 'maxfilesize', $max_upload_file_size * 1024 * 1024 );
$form->addRule ( 'user_upload', get_lang ( 'UploadFileNameAre' ) . ' *.zip', 'filename', '/\\.(zip)$/' );

$ftp_files = get_files_in_ftp ( $ftp_path, array ('flv', 'mp4', 'mp3' ) );
$form->addElement ( 'select', 'file_name', get_lang ( 'UploadLocalFileFromFTPDir' ), $ftp_files, array ('style' => 'width:40%' ) );

$form->addElement ( 'text', 'attribute', get_lang ( 'DefaultPage' ), array ('size' => '45', 'style' => "width:40%", 'class' => 'inputText' ) );
$form->addRule ( 'attribute', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'attribute', get_lang ( 'OnlyLettersAndNumbersAllowed' ), 'regex', '/^[a-zA-Z0-9\-_\.\/]+$/i' );
$defaults ['attribute'] = 'index.html';

//最小学习时间
$form->add_textfield ( 'learning_time', get_lang ( 'MinLearningTime' ), true, array ('id' => 'learning_time', 'style' => "width:60px;text-align:right;", 'class' => 'inputText', 'maxlength' => 60 ) );
$form->addRule ( 'learning_time', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'learning_time', get_lang ( 'MustLargerThan0' ), 'callback', 'range_check' );
$defaults ['learning_time'] = 30;

//显示顺序
$form->add_textfield ( 'display_order', get_lang ( 'DisplayOrder' ), true, array ('id' => 'learning_order', 'style' => "width:60px;text-align:right;", 'class' => 'inputText', 'maxlength' => 60 ) );
$form->addRule ( 'display_order', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'display_order', get_lang ( 'MustLargerThan0' ), 'callback', 'range_check' );
//$sql = "SELECT max(display_order)+1 FROM " . $table_courseware . " WHERE cc='" . api_get_course_code () . "' AND cw_type='html'";
//$display_order = Database::get_scalar_value ( $sql );
//$defaults ["display_order"] = ($display_order ? $display_order ++ : 1);
$defaults ["display_order"] = get_next_disp_order ();

$form->addElement ( 'textarea', 'comment', get_lang ( 'Comment' ), array ('cols' => 40, 'rows' => 3, 'wrap' => 'virtual', 'class' => 'inputText' ) );

$form->addElement ( 'hidden', 'unzip', "1" );

$group = array ();
$group [] = $form->createElement ( 'submit', 'submitDocument', get_lang ( 'Ok' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ( $defaults );
Display::setTemplateBorder ( $form, '98%' );
$form->add_real_progress_bar ( 'DocumentUpload', 'user_upload' );

if ($form->validate ()) {
	//设置内存及执行时间
	ini_set ( 'memory_limit', '256M' );
	ini_set ( 'max_execution_time', 1800 ); //设置执行时间
	$data = $form->getSubmitValues ();
	//var_dump($data);exit;
	$title = trim ( $data ['title'] );
	$comment = $data ['comment'];
	$file_location = $data ['file_location'] ['location'];
	$display_order = trim ( $data ["display_order"] );
	if ($file_location == "1") {
		$upload_ok = process_uploaded_file ( $_FILES ['user_upload'] );
		if ($upload_ok) {
			$new_path = handle_uploaded_package ( $_course, $_FILES ['user_upload'], $base_work_dir, $user_id, 0, 1, $title, trim ( $comment ), $data ['attribute'], '_self', intval ( $data ['learning_time'] ), $display_order );
			if ($new_path) { //上传及解压成功
				$redirect_url = "cw_list.php?message=" . urlencode ( get_lang ( 'UplUploadSucceeded' ) );
			}
		}
	} elseif ($file_location == "2") {
		$operation_ok = false;
		if (substr ( $ftp_path, - 1 ) != '/') $ftp_path = $ftp_path . '/';
		$s = $ftp_path . $data ['file_name'];
		
		$info = pathinfo ( $s );
		$filename = $info ['basename'];
		$extension = $info ['extension'];
		$file_base_name = str_replace ( '.' . $extension, '', $filename );
		$new_dir = replace_dangerous_char ( trim ( $file_base_name ), 'strict' );
		
		$new_dir_name = get_unique_name ();
		$upload_path = "/htmlpkg_" . $new_dir_name;
		if (! file_exists ( $base_work_dir )) mkdir ( $base_work_dir, CHMOD_NORMAL );
		$result = unzip_file ( $ftp_path . $data ['file_name'], $base_work_dir . $upload_path );
		
		if ($result) {
			$real_filesize = dirsize ( $base_work_dir . $upload_path );
			$package_id = add_package ( $_course, $upload_path, $real_filesize, $data ['attribute'], $title, $comment, '_self', intval ( $data ['learning_time'] ), $display_order );
			if ($package_id) {
				api_item_property_update ( $_course, TOOL_COURSEWARE_PACKAGE, $package_id, 'HTMLCoursewarePackageAdded', $user_id, 0, null );
				//Display::display_confirmation_message ( get_lang ( 'UplUploadSucceeded' ), false );
				$operation_ok = true;
			} else {
				$operation_ok = false;
			}
		}
	
	//$redirect_url = "cw_package_list.php?message=" . urlencode ( get_lang ( $operation_ok ? 'UplUploadSucceeded' : 'OperationFailed' ) );
	}
	tb_close ();
}

Display::display_header ( $nameTools, FALSE );

//echo '<span style="float:right"><input alt="#TB_inline?height=150&amp;width=400&amp;inlineId=myOnPageContent" title="' . get_lang ( 'UserGuideAndNotice' ) . '" class="thickbox" type="button" value="' . get_lang ( "UserGuideAndNotice" ) . '" /></span>';
//echo '<div id="myOnPageContent" style="display:none"><p>' . get_lang ( 'DocumentStoreTip' ) . '</p></div>';


echo '<div id="demo" class="yui-navset" style="margin:10px">';
echo display_cw_type_tab ( 'htmlcw' );
echo '<div class="yui-content"><div id="tab1">';
Display::display_confirmation_message ( get_lang ( 'UploadDocTip' ), false );
$form->display ();
echo '</div></div></div>';
Display::display_footer ();
