<?php
$language_file = array ('document' );
include_once ("../inc/global.inc.php");
api_protect_course_script ();
$is_allowed_to_edit = api_is_allowed_to_edit ();
if (! $is_allowed_to_edit) api_not_allowed ();

$course_dir = api_get_course_path () . "/document";
$sys_course_path = api_get_path ( SYS_COURSE_PATH );
$base_work_dir = $sys_course_path . $course_dir; //课程的系统目录


$http_www = api_get_path ( WEB_COURSE_PATH ) . api_get_course_code () . '/document';
$table_courseware = Database::get_course_table ( TABLE_COURSEWARE );

include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'tablesort.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . "export.lib.inc.php");
require_once (api_get_path ( SYS_CODE_PATH ) . "courseware/cw.lib.inc.php");
include_once (api_get_path ( SYS_CODE_PATH ) . 'document/document.inc.php');

api_session_unregister ( 'oLP' );
api_session_unregister ( 'lpobject' );

if (isset ( $_GET ['action'] )) {
	switch (getgpc ( 'action', 'G' )) {
	
	}
}

$htmlHeadXtra [] = Display::display_thickbox ();
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

$tool_name = get_lang ( "MultiMediaCourseware" );
$interbreadcrumb [] = array ("url" => api_get_path ( WEB_CODE_PATH ) . 'scorm/lp_controller.php?tabAction=mediacw', "name" => get_lang ( 'Courseware' ) );

$interbreadcrumb [] = array ("url" => "cw_media_list.php", "name" => get_lang ( "MultiMediaCourseware" ) );
Display::display_header ( $tool_name, FALSE );

echo '<div id="demo" class="yui-navset">';
echo display_cw_action_menus ( 'mediacw' );
echo '<div class="yui-content"><div id="tab1">';

if (isset ( $_GET ['message'] )) {
	Display::display_normal_message ( urldecode ( getgpc ( 'message' ) ) );
}

if ($is_allowed_to_edit) {
	if (isset ( $_GET ['delete'] )) {
		if (delete_courseware ( $_course, getgpc ( 'delete', 'G' ), $base_work_dir, TOOL_COURSEWARE_MEDIA )) {
			Display::display_confirmation_message ( get_lang ( 'DocDeleted' ) );
		} else {
			Display::display_error_message ( get_lang ( 'DocDeleteError' ) );
		}
	}
	
	if (isset ( $_POST ['action'] )) {
		switch (getgpc ( 'action', 'P' )) {
			case 'delete' :
				foreach ( $_POST ['path'] as $index => $path ) {
					delete_courseware ( $_course, $path, $base_work_dir, TOOL_COURSEWARE_MEDIA );
				}
				Display::display_confirmation_message ( get_lang ( 'DocDeleted' ) );
				break;
		}
	}
	
	if ((isset ( $_GET ['set_invisible'] ) && ! empty ( $_GET ['set_invisible'] )) || (isset ( $_GET ['set_visible'] ) && ! empty ( $_GET ['set_visible'] )) and $_GET ['set_visible'] != '*' and $_GET ['set_invisible'] != '*') {
		if (isset ( $_GET ['set_visible'] )) {
			$update_id = getgpc("set_visible","G");
			$visibility_command = 'visible';
			$visibility = 1;
		} else {
			$update_id = getgpc("set_invisible","G");
			$visibility_command = 'invisible';
			$visibility = 0;
		}
		$sql = "UPDATE $table_courseware SET visibility='" . $visibility . "' WHERE cc='" . api_get_course_code () . "' AND  cw_type='media' AND id=" . Database::escape ( $update_id );
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		if (api_sql_query ( $sql, __FILE__, __LINE__ )) {
			Display::display_confirmation_message ( get_lang ( "OperationSuccess" ) );
		} else {
			Display::display_error_message ( get_lang ( "OperationFailed" ) );
		}
	
	}
}

$pacakge_list = get_all_courseware_data ( api_get_course_code (), 'media', $is_allowed_to_edit );

$html = "<div class='actions'>";
$html .= link_button ( 'submit_file.gif', 'UploadMediaCourseware', 'cw_media_upload.php', '90%', '80%' );
$html .= '</div>';
echo $html;

//准备列表数据
if (is_array ( $pacakge_list ) && count ( $pacakge_list ) > 0) {
	$use_document_title = get_setting ( 'use_document_title' );
	$sortable_data = array ();
	foreach($pacakge_list as $key=>$data){
		$row = array ();
		
		$size = $data ["size"];
		$document_name = $data ['title'];
		$path = $data ['path'];
		
		$open_target = $data ['open_target'];
		
		$row [] = create_media_link ( $http_www, $data ); //.'<br />'.$invisibility_span_open.nl2br(htmlspecialchars($id['comment'])).$invisibility_span_close;
		$row [] = invisible_wrap ( $data ["display_order"], $data ['visibility'] == 0 );
		
		//$row [] = Display::return_icon ( 'file_flash.gif', get_lang ( 'FLV' ), array ('align' => 'middle', 'hspace' => '5' ) );
		$row [] = invisible_wrap ( $data ['learning_time'], $data ['visibility'] == 0 );
		$row [] = invisible_wrap ( format_file_size ( $size ), $data ['visibility'] == 0 );
		
		$row [] = invisible_wrap ( $data ['attribute'] . " s", $data ['visibility'] == 0 );
		
		$display_date = substr ( $data ['created_date'], 0, 10 );
		$display_date = invisible_wrap ( $display_date, $data ['visibility'] == 0 );
		$row [] = $display_date;
		
		$visibility_icon = ($data ['visibility'] == 0) ? 'invisible' : 'visible';
		$visibility_command = ($data ['visibility'] == 0) ? 'set_visible' : 'set_invisible';
		$row [] = '&nbsp;&nbsp;<a href="cw_media_list.php?' . $visibility_command . '=' . $data ['id'] . '">' . Display::return_icon ( $visibility_icon . '.gif', get_lang ( 'Visible' ) ) . '</a>';
		
		$row [] = build_media_courseware_action_icons ( $path, $data ['visibility'], $key, $data ['title'] );
		
		$sortable_data [] = $row;
	}

}

$table_header [] = array (get_lang ( 'Name' ) );
$table_header [] = array (get_lang ( 'DisplayOrder' ), null, null, array ("width" => "60" ) );
$table_header [] = array (get_lang ( 'MinLearningTime' ), true, null, array ('width' => '120' ) );
//$table_header [] = array (get_lang ( 'Type' ), null, array ("width" => "30" ) ); //,null,array('width'=>'5%'));
$table_header [] = array (get_lang ( 'Size' ), null, null, array ("width" => "80" ) ); //,null,array('width'=>'10%'));
$table_header [] = array (get_lang ( 'PlayTime' ), null, null, array ("width" => "100" ) );
$table_header [] = array (get_lang ( 'CreationDate' ), null, null, array ("width" => "120" ) ); //,null,array('width'=>'10%'));
$table_header [] = array (get_lang ( 'Visible' ), false, null, array ('style' => 'width:70px' ) );
$table_header [] = array (get_lang ( 'Actions' ), false, null, array ('style' => 'width:50px' ) );
echo Display::display_table ( $table_header, $sortable_data );

echo '</div></div></div>';
Display::display_footer ();
