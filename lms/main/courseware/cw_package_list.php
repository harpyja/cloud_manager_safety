<?php
$language_file = array ('document' );
include_once ("../inc/global.inc.php");
$this_section = SECTION_COURSES;
//api_block_anonymous_users();
api_protect_course_script ();
$is_allowed_to_edit = api_is_allowed_to_edit ();
if (! $is_allowed_to_edit) api_not_allowed ();

$course_dir = api_get_course_path () . "/html";
$sys_course_path = api_get_path ( SYS_COURSE_PATH );
$base_work_dir = $sys_course_path . $course_dir; //课程的系统目录
$http_www = api_get_path ( WEB_COURSE_PATH ) . api_get_course_path () . '/html';

$table_courseware = Database::get_course_table ( TABLE_COURSEWARE );

include_once (api_get_path ( LIB_PATH ) . 'pclzip/pclzip.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileDisplay.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'tablesort.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . "export.lib.inc.php");
require_once (api_get_path ( SYS_CODE_PATH ) . "courseware/cw.lib.inc.php");
include_once (api_get_path ( SYS_CODE_PATH ) . 'document/document.inc.php');

$htmlHeadXtra [] = Display::display_thickbox ();
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

$tool_name = get_lang ( "HTMLPackageCourseware" ); // title of the page (should come from the language file)
$interbreadcrumb [] = array ("url" => api_get_path ( WEB_CODE_PATH ) . 'scorm/lp_controller.php?tabAction=lp', "name" => get_lang ( 'Courseware' ) );
$interbreadcrumb [] = array ("url" => "cw_package_list.php", "name" => get_lang ( "HTMLPackageCourseware" ) );
Display::display_header ( $tool_name, FALSE );
$is_allowed_to_edit = api_is_allowed_to_edit ();

echo '<div id="demo" class="yui-navset">';
echo display_cw_action_menus ( 'htmlcw' );
echo '<div class="yui-content"><div id="tab1">';

if (isset ( $_GET ['message'] )) {
	Display::display_normal_message ( urldecode ( getgpc ( 'message' ) ) );
}

if ($is_allowed_to_edit) {
	if (isset ( $_GET ['delete'] )) {
		if (delete_courseware ( $_course, getgpc ( 'delete', 'G' ), $base_work_dir, TOOL_COURSEWARE_PACKAGE )) {
			Display::display_confirmation_message ( get_lang ( 'DocDeleted' ) );
		} else {
			Display::display_error_message ( get_lang ( 'DocDeleteError' ) );
		}
	}
	
	if (isset ( $_POST ['action'] )) {
		switch (getgpc ( 'action', 'P' )) {
			case 'delete' :
				foreach ( $_POST ['path'] as $index => $path ) {
					delete_courseware ( $_course, $path, $base_work_dir, TOOL_COURSEWARE_PACKAGE );
				}
				Display::display_confirmation_message ( get_lang ( 'DocDeleted' ) );
				break;
		}
	}
	
	if ((isset ( $_GET ['set_invisible'] ) && ! empty ( $_GET ['set_invisible'] )) || (isset ( $_GET ['set_visible'] ) && ! empty ( $_GET ['set_visible'] )) and $_GET ['set_visible'] != '*' and $_GET ['set_invisible'] != '*') {
		if (isset ( $_GET ['set_visible'] )) {
			$update_id = getgpc ( 'set_visible', 'G' );
			$visibility_command = 'visible';
			$visibility = 1;
		} else {
			$update_id = getgpc ( 'set_invisible', 'G' );
			$visibility_command = 'invisible';
			$visibility = 0;
		}
		$sql = "UPDATE $table_courseware SET visibility='" . $visibility . "' WHERE cc='" . api_get_course_code () . "' AND  cw_type='html' AND id=" . Database::escape ( $update_id );
		//echo $sql;exit;
		if (api_sql_query ( $sql, __FILE__, __LINE__ )) {
			Display::display_confirmation_message ( get_lang ( "OperationSuccess" ) );
		} else {
			Display::display_error_message ( get_lang ( "OperationFailed" ) );
		}
	}
}

$pacakge_list = get_all_courseware_data ( api_get_course_code (), 'html', $is_allowed_to_edit );

$html = "<div class='actions'>";
$html .= str_repeat ( '&nbsp;', 2 ) . link_button ( 'submit_file.gif', 'UploadHTMLPackage', 'cw_upload.php', '90%', '80%' );
$html .= '</div>';
echo $html;

//准备列表数据
if (is_array ( $pacakge_list ) && count ( $pacakge_list ) > 0) {
	$use_document_title = get_setting ( 'use_document_title' );
	$sortable_data = array ();
	//var_dump($pacakge_list);
	while ( list ( $key, $id ) = each ( $pacakge_list ) ) {
		$row = array ();
		$document_name = $id ['title'];
		$path = $id ['path'];
		$default_page = $id ['attribute'];
		
		$row [] = create_package_link ( $http_www, $id ); //.'<br />'.$invisibility_span_open.nl2br(htmlspecialchars($id['comment'])).$invisibility_span_close;
		$row [] = invisible_wrap ( $id ['display_order'], $id ['visibility'] == 0 );
		$row [] = invisible_wrap ( $id ['learning_time'], $id ['visibility'] == 0 );
		
		$display_size = format_file_size ( $id ["size"] );
		$row [] = invisible_wrap ( $display_size, $id ['visibility'] == 0 );
		
		$display_date = substr ( $id ['created_date'], 0, 10 );
		$row [] = invisible_wrap ( $display_date, $id ['visibility'] == 0 );
		
		$visibility_icon = ($id ['visibility'] == 0) ? 'invisible' : 'visible';
		$visibility_command = ($visibility == 0) ? 'set_visible' : 'set_invisible';
		$row [] = '&nbsp;&nbsp;<a href="cw_package_list.php?' . $visibility_command . '=' . $id ['id'] . '">' . Display::return_icon ( $visibility_icon . '.gif' ) . '</a>';
		
		$row [] = build_action_icons ( $id ['filetype'], $id ['path'], $id ['visibility'], $key, $id ['title'] );
		
		$sortable_data [] = $row;
	}

	//*******************************************************************************************
}

//$table->set_header($column++,get_lang('Type'));
$table_header [] = array (get_lang ( 'Name' ) );
$table_header [] = array (get_lang ( 'DisplayOrder' ), false, null, array ('width' => '80' ) );
$table_header [] = array (get_lang ( 'MinLearningTime' ), true, null, array ('width' => '120' ) );
$table_header [] = array (get_lang ( 'Size' ), true, null, array ('width' => '80' ) );
$table_header [] = array (get_lang ( 'CreationDate' ), true, null, array ('width' => '90' ) );
$table_header [] = array (get_lang ( 'Visible' ), false, null, array ('style' => 'width:70px' ) );
$table_header [] = array (get_lang ( 'Actions' ), false, null, array ('style' => 'width:50px' ) );
echo Display::display_table ( $table_header, $sortable_data );
echo '</div></div></div>';
Display::display_footer ();
