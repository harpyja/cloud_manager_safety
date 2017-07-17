<?php
/**
 ==============================================================================
 
 ==============================================================================
 */

$language_file = 'document';
include_once ("../inc/global.inc.php");
api_protect_course_script ();
$is_allowed_to_edit = api_is_allowed_to_edit ();
if (! $is_allowed_to_edit) api_not_allowed ();
include_once (api_get_path ( LIBRARY_PATH ) . 'fileDisplay.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'tablesort.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . "export.lib.inc.php");
include_once (api_get_path ( SYS_CODE_PATH ) . 'document/document.inc.php');

$table_document = Database::get_course_table ( TABLE_DOCUMENT );
$TABLE_ITEMPROPERTY = Database::get_course_table ( TABLE_ITEM_PROPERTY );

if (isset ( $_REQUEST ['curdirpath'] ) && ! empty ( $_REQUEST ['curdirpath'] )) {
	$curdirpath = Security::remove_XSS ( urldecode ( $_REQUEST ['curdirpath'] ) );
} else {
	$curdirpath = '/';
}
$curdirpathurl = urlencode ( $curdirpath );
//$course_dir = api_get_course_path () . "/document";  
$course_dir = api_get_course_code () . "/document";    
$sys_course_path = api_get_path ( SYS_COURSE_PATH );
$base_work_dir = $sys_course_path . $course_dir; //课程文档存储的物理目录
$http_www = api_get_path ( WEB_COURSE_PATH ) . api_get_course_code () . '/document'; //课程文档WEB访问路径
                

if (! DocumentManager::get_document_id ( $_course, $curdirpath )) {
	$curdirpath = '/';
	$curdirpathurl = urlencode ( $curdirpath );
}

if (isset ( $_GET ['action'] )) {
	switch (getgpc ( 'action', 'G' )) {
		case 'download' :
			$document_id =intval( getgpc ( 'id', 'G') );
			$dbTable = Database::get_course_table ( TABLE_DOCUMENT );
			$sql = "SELECT comment,title,path,display_order FROM $dbTable WHERE id=" . Database::escape ( $document_id );
			$document_info = Database::fetch_one_row ( $sql, FALSE, __FILE__, __LINE__ );
			if (! $document_info) {
				header ( "HTTP/1.0 404 Not Found" );
				$error404 = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">';
				$error404 .= '<html><head>';
				$error404 .= '<title>404 Not Found</title>';
				$error404 .= '</head><body>';
				$error404 .= '<h1>Not Found</h1>';
				$error404 .= '<p>The requested URL was not found on this server.</p>';
				$error404 .= '<hr>';
				$error404 .= '</body></html>';
				echo ($error404);
				exit ();
			}
			$full_file_name = $base_work_dir . $document_info ['path'];  //echo $full_file_name;
			$download_name = $document_info ['title']. '.' . file_ext ( $document_info ['path'] );

                        DocumentManager::file_send_for_download ( $full_file_name, true, $download_name );
			exit ();
			break;
	}
}

$htmlHeadXtra [] = "<script type=\"text/javascript\">
function confirmation (name){
if (confirm(\"" . get_lang ( "AreYouSureToDelete" ) . " \" + name + \" ?\"))	{return true;}
else{return false;}
}</script>";

$htmlHeadXtra [] = Display::display_thickbox ();
$tool_name = get_lang ( "CourseDocuments" ); // title of the page (should come from the language file)
Display::display_header ( $tool_name, FALSE );

function check_dir_store_name($inputValue) {
	$ret = true;
	$post_dir_name = Security::remove_XSS ( $inputValue );
	if ($post_dir_name == '../' || $post_dir_name == '.' || $post_dir_name == '..') {
		$ret = false;
	} else {
		$filtered_filename = eregi_replace ( '[^a-z0-9_.-@]', '_', strtr ( $post_dir_name, ' ', '' ) );
		if ($filtered_filename != $post_dir_name) {
			$msgError = get_lang ( 'DirNameOnlyLettersAndNumbersAllowed' );
			$ret = false;
		}
	}
	return $ret;
}

/*======================================
	 DELETE FILE OR DIRECTORY  删除文件或目录
	 ======================================*/
if (is_equal ( $_GET ['action'], 'delete' )) {
	include_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
	if (DocumentManager::delete_document ( $_course, intval(getgpc ( 'id', 'G' )), $base_work_dir )) {
		Display::display_confirmation_message ( get_lang ( 'DocDeleted' ) );
	} else {
		Display::display_error_message ( get_lang ( 'DocDeleteError' ) );
	}
}

if ((isset ( $_GET ['set_invisible'] ) && ! empty ( $_GET ['set_invisible'] )) || (isset ( $_GET ['set_visible'] ) && ! empty ( $_GET ['set_visible'] )) and $_GET ['set_visible'] != '*' and $_GET ['set_invisible'] != '*') {
	if (isset ( $_GET ['set_visible'] )) {
		$update_id = getgpc('set_visible');
		$visibility_command = 'visible';
	} else {
		$update_id = getgpc('set_invisible');
		$visibility_command = 'invisible';
	}
	if (api_item_property_update ( $_course, TOOL_DOCUMENT, $update_id, $visibility_command, api_get_user_id () )) {
		Display::display_confirmation_message ( get_lang ( "ViMod" ) );
	} else {
		Display::display_error_message ( get_lang ( "ViModProb" ) );
	}

}

$html = '<div class="actions">';
$html .= link_button ( 'submit_file.gif', 'UplUploadDocument', 'upload.php?path=' . $curdirpathurl, '40%', '50%' );
$html .= "</div>\n";
echo $html;
//============================================================================== 显示文件列表


$docs_and_folders = DocumentManager::get_all_document_data ( $_course, $is_allowed_to_edit );
//准备列表数据
$table_data = array ();
if ($docs_and_folders && is_array ( $docs_and_folders )) {
	while ( list ( $key, $id ) = each ( $docs_and_folders ) ) {
		$row = array ();
		$id ['visibility'] = 1;
		$invisibility_span_open = ($id ['visibility'] == 0) ? '<span class="invisible">' : '';
		$invisibility_span_close = ($id ['visibility'] == 0) ? '</span>' : '';
		$size = $id [size];
		$document_name = $id ['title'];
		$row [] = build_document_icon_tag ( $id ['filetype'], $id ['path'] );
                $row [] = create_document_link ( $http_www, $document_name, $key, $id ['filetype'], $size, $id ['visibility'] ) . '<br />' . $invisibility_span_open . '<em>' . nl2br ( htmlspecialchars ( $id ['comment'] ) ) . '</em>' . $invisibility_span_close;
		$display_size = format_file_size ( $size );
		$row [] = '<span style="display:none;">' . $size . '</span>' . $invisibility_span_open . $display_size . $invisibility_span_close;
		
		//$display_date = format_date ( strtotime ( $id ['lastedit_date'] ) );
		//$row [] = '<span style="display:none;">' . $id ['lastedit_date'] . '</span>' . $invisibility_span_open . $display_date . $invisibility_span_close;
		$row [] = $id ['display_order'] ? $id ['display_order'] : '';
		$edit_icons = build_edit_icons ( $key, $id ['filetype'], $id ['path'], $id ['visibility'], $id ['title'] );
		$row [] = $edit_icons;
		$table_data [] = $row;
	}
}

$table_header [] = array (get_lang ( 'Type' ), NULL, NULL, array ('width' => '40' ) );
$table_header [] = array (get_lang ( 'Name' ) );
$table_header [] = array (get_lang ( 'Size' ), NULL, NULL, array ('width' => '80' ) );
//$table_header [] = array (get_lang ( 'LastEditDate' ) );
$table_header [] = array (get_lang ( 'DisplayOrder' ), NULL, NULL, array ('width' => '60' ) );
$table_header [] = array (get_lang ( 'Actions' ), NULL, NULL, array ('width' => '50' ) );
echo Display::display_table ( $table_header, $table_data );

Display::display_footer ();
