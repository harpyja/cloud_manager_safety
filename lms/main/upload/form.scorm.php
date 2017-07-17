<?php

require_once (api_get_path ( SYS_CODE_PATH ) . "courseware/cw.lib.inc.php");
$ftp_path = LOCAL_FTP_DIR . 'scorm/';
$max_upload_file_size = get_upload_max_filesize ( api_get_setting ( "upload_max_filesize" ) );

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
	
	$("button.add:eq(0)").click(function(){
		if($("#upload_file_local").val()=="" && $("#location_local").attr("checked")==true){
			$("#upload_file_local").parent().find(".onError").remove();
			$("#upload_file_local").parent().append("<div class=\'onError\'>' . get_lang ( "PleaseSelectAFile" ) . '</div>");
			return false;
		}
		
	});
		});
</script>';

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$nameTools = get_lang ( "UploadScorm" );
Display::display_header ( $nameTools, FALSE );

include_once ('../scorm/content_makers.inc.php');

$form = new FormValidator ( '', 'POST', 'upload.php', '', 'id="upload_form" enctype="multipart/form-data"' );

$form->addElement ( 'header', 'header', get_lang ( "FileUpload" ) . ' - ' . $nameTools );

$form->addElement ( 'text', 'title', get_lang ( 'Name' ), array ('class' => 'inputText', 'style' => 'width:80%' ) );

//文件位置
$group = array ();
$group [] = & HTML_QuickForm::createElement ( 'radio', 'location', null, get_lang ( 'Local' ), 1, array ('id' => 'location_local' ) );
$group [] = & HTML_QuickForm::createElement ( 'radio', 'location', null, get_lang ( 'Remote' ), 2, array ('id' => 'location_remote' ) );
$form->addGroup ( $group, 'file_location', get_lang ( 'FileLocation' ), '&nbsp;&nbsp;&nbsp;' );
$defaults ['file_location'] ['location'] = 1;

//从本地文件中选取
$form->addElement ( 'file', 'user_file', get_lang ( 'FileToUpload' ), array ('class' => 'inputText', 'style' => 'width:50%', 'id' => 'upload_file_local' ) );
$form->addRule ( 'user_file', get_lang ( 'UploadFileSizeLessThan' ) . ($max_upload_file_size) . ' MB', 'maxfilesize', $max_upload_file_size * 1048576 );
$form->addRule ( 'user_file', get_lang ( 'UploadFileNameAre' ) . ' *.zip', 'filename', '/\\.(zip)$/' );

//:从storage\ftp\scorm_aicc中选择文件
$form->addElement ( 'select', 'file_name', get_lang ( 'UploadLocalFileFromGarbageDir' ), get_files_in_ftp ( $ftp_path, array ('zip' ) ) );

//V2.1: 课件制造商,来源
/*$select_content_marker = &$form->addElement ( 'select', 'content_maker', get_lang ( 'ContentMaker' ) );
foreach ( $content_origins as $index => $origin ) {
	$select_content_marker->addOption ( $origin, $index );
	if ($index == 1) {
		$select_content_marker->setSelected ( $origin );
	}
}*/
$group = array ();
foreach ( $content_origins as $index => $origin ) {
	$group [] = & HTML_QuickForm::createElement ( 'radio', 'content_maker', null, $origin, $index, array ('id' => 'maker_' . $index ) );
}
$form->addGroup ( $group, 'cm', get_lang ( 'ContentMaker' ), '&nbsp;&nbsp;&nbsp;', false );
$defaults ['content_maker'] = 'articulate';

//最小学习时间
$form->add_textfield ( 'learning_time', get_lang ( 'LPLearningTime' ), true, array ('id' => 'learning_time', 'style' => "width:60px;text-align:right;", 'class' => 'inputText', 'maxlength' => 60 ) );
$form->addRule ( 'learning_time', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'learning_time', get_lang ( 'MustLargerThan0' ), 'callback', 'range_check' );
$defaults ['learning_time'] = 30;

//学习顺序
$form->add_textfield ( 'learning_order', get_lang ( 'DisplayOrder' ), true, array ('id' => 'learning_order', 'style' => "width:60px;text-align:right;", 'class' => 'inputText', 'maxlength' => 60 ) );
$form->addRule ( 'learning_order', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'learning_order', get_lang ( 'MustLargerThan0' ), 'callback', 'range_check' );
$defaults ["learning_order"] = get_next_disp_order();

$form->addElement ( 'hidden', 'content_proximity', 'local' );

$group = array ();
//$group[] = $form->createElement('submit', 'submit', get_lang('Upload'), 'class="inputSubmit"');
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Import' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->add_real_progress_bar ( 'uploadScorm', 'user_file' );
$form->setDefaults ( $defaults );
Display::setTemplateBorder ( $form, '98%' );

echo '<div id="demo" class="yui-navset" style="margin:10px">';
echo display_cw_type_tab ( 'lp' );
echo '<div class="yui-content"><div id="tab1">';

Display::display_confirmation_message ( get_lang ( 'UploadScormTip' ), false );
$form->display ();
echo '</div></div></div>';
Display::display_footer ();