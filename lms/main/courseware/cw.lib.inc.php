<?php
include_once (api_get_path ( LIB_PATH ) . 'pclzip/pclzip.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileDisplay.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');

/**
 * 验证是否含有中文
 * @param $username
 * @return unknown_type
 */
function validate($username) {
	$filtered_username = eregi_replace ( '[^a-z0-9_.-@]', '_', trim ( $username ) );
	return $filtered_username == $username;
}

/**
 * zip 打包课件列表
 * @param $_course
 * @param $to_user_id
 * @param $can_see_invisible
 * @return unknown_type
 */
function get_all_courseware_data($course_code, $cw_type = 'html', $can_see_invisible = false) {
	if (empty ( $course_code )) $course_code = api_get_course_code ();
	$table_courseware = Database::get_course_table ( TABLE_COURSEWARE );
	$sql = "SELECT * FROM   $table_courseware AS t WHERE t.cc='" . $course_code . "'";
	if ($cw_type) $sql .= " AND cw_type='" . $cw_type . "'";
	if (! $can_see_invisible) $sql .= " AND visibility=1";
	$sql .= " ORDER BY t.display_order,title";
	//echo $sql;
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	if ($result && Database::num_rows ( $result ) > 0) {
		while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
			$document_data [$row ['id']] = $row;
		}
		return $document_data;
	} else {
		return false;
	}
}

/**
 * 标题栏
 * @param $www
 * @param $title
 * @param $path
 * @param $size
 * @param $default_page
 * @param $open_target
 * @param $visibility
 * @return unknown_type
 */
function create_package_link($www, $cw_info = array(), $open_target = '_blank') {
	$url_path = urlencode ( $cw_info ['path'] );
	//$url = api_get_path ( WEB_COURSE_PATH ) . api_get_course_path () . "/html" . $cw_info ['path'] . "/" . $cw_info ['attribute'];
	$goto_url = api_get_path ( WEB_CODE_PATH ) . 'courseware/link_goto.php?' . api_get_cidreq () . '&cw_id=' . $cw_info ["id"];
	$url = WEB_QH_PATH . 'document_viewer.php?cw_id=' . $cw_info ["id"] . '&url=' . urlencode ( $goto_url );
	$icon = Display::return_icon ( 'file_html.gif', $cw_info ['title'], array ('style' => 'vertical-align: middle;' ) );
	return $icon . ' <a href="' . $url . '" target="' . $open_target . '">' . invisible_wrap ( $cw_info ['title'], $cw_info ['visibility'] == 0 ) . '</a>';
}
/*
 *
 * swf
 */
function create_swf_link($www, $cw_info = array(), $open_target = '_blank') {
    $url_path = urlencode ( $cw_info ['path'] );
    //$url = api_get_path ( WEB_COURSE_PATH ) . api_get_course_path () . "/html" . $cw_info ['path'] . "/" . $cw_info ['attribute'];
   // $goto_url = api_get_path ( WEB_CODE_PATH ) . 'courseware/link_goto.php?' . api_get_cidreq () . '&cw_id=' . $cw_info ["id"];

    $sql = "select path from crs_courseware where id = ".$cw_info['id'];

    $path = Database::getval ( $sql);
    $cc = api_get_course_code();//课程编号

    $url = WEB_QH_PATH . 'flex_paper/index.php?cw_id=' . $cw_info ["id"] . '&url='.$path.'&cc='.$cc;
    $icon = Display::return_icon ( 'file_html.gif', $cw_info ['title'], array ('style' => 'vertical-align: middle;' ) );
    return $icon . ' <a href="' . $url . '" target="' . $open_target . '">' . invisible_wrap ( $cw_info ['title'], $cw_info ['visibility'] == 0 ) . '</a>';
}

/**
 * 操作栏
 * @param $type
 * @param $path
 * @param $visibility
 * @param $id
 * @param $title
 * @return unknown_type
 */
function build_action_icons($type, $path, $visibility, $id, $title,$cw_type) {

    if($cw_type == 'swf'){
        $sql = "select path from crs_courseware where id = ".$id;

        $path = Database::getval ( $sql);
        $cc = api_get_course_code();//课程编号
        $modify_icons = '';
        $modify_icons .= '&nbsp;' . link_button ( 'edit.gif', 'EditUploadHTMLPackage', 'cw_upload_edit.php?id=' . $id, '60%', '70%', false );
        $modify_icons .= '&nbsp;' . confirm_href ( 'delete.gif', 'AreYouSureToDelete', 'Delete', 'cw_list.php?cw_type=swf&action=delete&path=' .$path );

        return $modify_icons;
    }else{
        $modify_icons = '';
        $modify_icons .= '&nbsp;' . link_button ( 'edit.gif', 'EditUploadHTMLPackage', 'cw_upload_edit.php?id=' . $id, '60%', '70%', false );
        $modify_icons .= '&nbsp;' . confirm_href ( 'delete.gif', 'AreYouSureToDelete', 'Delete', 'cw_list.php?cw_type=html&action=delete&path=' . urlencode ( $path ) );
        return $modify_icons;
    }


}

/**
 * 处理 上传zip及解压
 * @param $_course
 * @param $uploaded_file
 * @param $base_work_dir
 * @param $user_id
 * @param $to_user_id
 * @param $maxFilledSpace
 * @param $unzip
 * @param $title
 * @param $comment
 * @param $default_page
 * @param $open_target
 * @param $what_if_file_exists
 * @param $output
 * @return unknown_type
 */
function handle_uploaded_package($_course, $uploaded_file, $base_work_dir, $user_id, $to_user_id = NULL, $unzip = 0, $title = null, $comment = NULL, $default_page = "index.html", $open_target = '_self', $learning_time = 30, $disp_order = 1) {
	if (! $user_id) return false;
	$uploaded_file ['name'] = stripslashes ( $uploaded_file ['name'] );
	$uploaded_file ['name'] = add_ext_on_mime ( $uploaded_file ['name'], $uploaded_file ['type'] );
	if ($unzip == 1) {
		if (preg_match ( "/.zip$/", strtolower ( $uploaded_file ['name'] ) )) {
			$destination = $base_work_dir . time () . '.zip';
			if (move_uploaded_file ( $_FILES ['user_upload'] ['tmp_name'], $destination )) {
				$uploaded_file ['dest_filepath'] = $destination;
				$package_id = unzip_uploaded_package ( $uploaded_file, $base_work_dir, $default_page, $title, $comment, $open_target, $learning_time, $disp_order );
				if ($package_id) {
					api_item_property_update ( $_course, TOOL_COURSEWARE_PACKAGE, $package_id, 'HTMLCoursewarePackageAdded', $user_id, 0, $to_user_id );
					Display::display_confirmation_message ( get_lang ( 'UplUploadSucceeded' ), false );
					return $package_id;
				}
			}
			return false;
		} else {
			Display::display_error_message ( get_lang ( 'UplNotAZip' ) . " " . get_lang ( 'PleaseTryAgain' ) );
		}
	}
	return false;
}

/**
 * 解压zip
 * @param $uploaded_file
 * @param $base_work_dir
 * @param $default_page
 * @param $title
 * @param $comment
 * @param $open_target
 * @param $max_filled_space
 * @param $output
 * @return unknown_type
 */
function unzip_uploaded_package($uploaded_file, $base_work_dir, $default_page, $title, $comment, $open_target, $learning_time, $disp_order) {
	global $_course;
	if (! file_exists ( $base_work_dir )) mkdir ( $base_work_dir, CHMOD_NORMAL );
	$upload_path = "/htmlpkg_" . time ();
	$dest_path = $base_work_dir . $upload_path;
	if (! file_exists ( $dest_path )) @mkdir ( $dest_path );
	if (! file_exists ( $dest_path )) exit ( "目标解压路径不存在: " . $dest_path );
	$zip_file_path = $uploaded_file ['dest_filepath'];
	if (unzip_file ( $zip_file_path, $dest_path )) {
		$real_filesize = dir_total_space ( $dest_path );
		filter_all_documents_in_folder ( $_course, api_get_user_id (), $base_work_dir, $upload_path == '/' ? '' : $upload_path );
		$package_id = add_package ( $_course, $upload_path, $real_filesize, $default_page, $title, $comment, $open_target, $learning_time, $disp_order );
		my_delete ( $zip_file_path );
		my_delete ( $uploaded_file ['tmp_name'] );
	}
	return $package_id;
	
	/* global $_user;
	if (! file_exists ( $base_work_dir )) mkdir ( $base_work_dir, CHMOD_NORMAL );
	$zip_file = new pclZip ( $uploaded_file ['tmp_name'] );
	$zip_content_array = $zip_file->listContent ();
	$folder_count = $file_count = 0;
	
	//计算总大小
	foreach ( ( array ) $zip_content_array as $this_content ) {
		$real_filesize += $this_content ['size'];
	}
	
	$save_dir = getcwd (); // D:\ZLMS\htdocs\zlms\main\document
	$new_dir_name = get_unique_name ();
	$upload_path = "/htmlpkg_" . $new_dir_name;
	if (! file_exists ( $base_work_dir . $upload_path )) mkdir ( $base_work_dir . $upload_path );
	chdir ( $base_work_dir . $upload_path );
	$unzipping_state = $zip_file->extract ( PCLZIP_CB_PRE_EXTRACT, 'clean_up_files_in_zip' );
	
	filter_all_documents_in_folder ( $_course, api_get_user_id (), $base_work_dir, $upload_path == '/' ? '' : $upload_path );
	$package_id = add_package ( $_course, $upload_path, $real_filesize, $default_page, $title, $comment, $open_target, $learning_time, $disp_order ); */
	
	return $package_id;
}

/**
 * 过滤所有文件，只能含有英文及数字的文件名
 * @param $_course
 * @param $user_id
 * @param $base_work_dir
 * @param $current_path
 * @return unknown_type
 */
function filter_all_documents_in_folder($_course, $user_id, $base_work_dir, $current_path = '') {
	$path = $base_work_dir . $current_path;
	$handle = opendir ( $path );
	while ( $file = readdir ( $handle ) ) {
		if ($file == '.' || $file == '..') continue;
		$completepath = $path . '/' . $file;
		$match = preg_match ( "/^[A-Za-z0-9_.\-@]+$/", $file );
		if (! $match) {
			$extension = get_file_ext ( $file );
			$safe_file = get_file_name ( $base_work_dir . $current_path . '/', $extension );
			@rename ( $base_work_dir . $current_path . '/' . $file, $base_work_dir . $current_path . '/' . $safe_file );
		}
		
		if (is_dir ( $completepath )) {
			//递归处理子目录下文件
			filter_all_documents_in_folder ( $_course, $user_id, $base_work_dir, $current_path . '/' . (isset ( $safe_file ) ? $safe_file : $file) );
		} else {
			if (! preg_match ( "/^[A-Za-z0-9_.\-@]+$/", $file )) {
				$extension = get_file_ext ( $file );
				$safe_file = get_file_name ( $base_work_dir . $current_path . '/', $extension );
				@rename ( $base_work_dir . $current_path . '/' . $file, $base_work_dir . $current_path . '/' . $safe_file );
			}
			
			$clean_name = disable_dangerous_file ( $file );
			if (! filter_extension ( $safe_file )) {
				@rename ( $base_work_dir . $current_path . '/' . $file, $base_work_dir . $current_path . '/' . $clean_name . ".dangerous" );
			}
		}
	
	}
}

/**
 * 打包课件处理完成后，写DB记录
 * @param $_course
 * @param $path
 * @param $filesize
 * @param $default_page
 * @param $title
 * @param $comment
 * @param $open_target
 * @return unknown_type
 */
function add_package($_course, $path, $filesize, $default_page, $title, $comment = NULL, $open_target = '_self', $learning_time = 30, $disp_order = 1) {
	$table_courseware = Database::get_course_table ( TABLE_COURSEWARE );
	$sql_data = array ('path' => $path, 'attribute' => $default_page, 'size' => $filesize, 'title' => $title, 'comment' => $comment, 'cw_type' => 'html' );
	//'open_target' => $open_target,
	$sql_data ['cc'] = api_get_course_code ();
	$sql_data ['learning_time'] = $learning_time;
	$sql_data ['display_order'] = $disp_order;
	$sql_data ['created_date'] = date ( 'Y-m-d H:i:s' );
	$sql = Database::sql_insert ( $table_courseware, $sql_data );
	if (api_sql_query ( $sql, __FILE__, __LINE__ )) {
		return Database::get_last_insert_id ();
	} else {
		return false;
	}
}

/**
 * 查询zip课件信息
 * @param $_course
 * @param $path
 * @return unknown_type
 */
function get_package_id($_course, $path) {
	$table_courseware = Database::get_course_table ( TABLE_COURSEWARE );
	$sql = "SELECT id FROM $table_courseware WHERE path = " . Database::escape ( $path );
	return Database::get_scalar_value ( $sql );
}

function remove_courseware($course_code, $cw_id, $cw_type) {
	switch ($cw_type) {
		case 'scorm' :
			break;
		case 'media' :
			break;
		case 'html' :
			break;
		case 'link' :
			break;
	}
}

function delete_courseware($_course, $path, $base_work_dir, $cwtype = TOOL_COURSEWARE_PACKAGE) {

	$TABLE_DOCUMENT = Database::get_course_table ( TABLE_COURSEWARE );
	$TABLE_ITEMPROPERTY = Database::get_course_table ( TABLE_ITEM_PROPERTY );
	$document_id = get_package_id ( $_course, $path );
	$new_path = $path . '_DELETED_' . $document_id;
	if ($document_id) {
		$delete_policy = get_setting ( 'permanently_remove_deleted_files' );
		if ($delete_policy == 'true') {
			$what_to_delete_sql = "SELECT id FROM " . $TABLE_DOCUMENT . " WHERE path='" . $path . "' OR path LIKE '" . $path . "/%'";
			$what_to_delete_result = api_sql_query ( $what_to_delete_sql, __FILE__, __LINE__ );
			if ($what_to_delete_result && Database::num_rows ( $what_to_delete_result ) != 0) {
				//delete all item_property entries
				while ( $row = Database::fetch_array ( $what_to_delete_result, "ASSOC" ) ) {
					$remove_from_item_property_sql = "DELETE FROM " . $TABLE_ITEMPROPERTY . " WHERE ref = " . $row ['id'] . " AND tool='" . $cwtype . "'";
					$remove_from_document_sql = "DELETE FROM " . $TABLE_DOCUMENT . " WHERE id = " . $row ['id'] . "";
					api_sql_query ( $remove_from_item_property_sql, __FILE__, __LINE__ );
					api_sql_query ( $remove_from_document_sql, __FILE__, __LINE__ );
				}
				my_delete ( $base_work_dir . $path );
				return true;
			} else {
				return false;
			}
		} else { //set visibility to 2 and rename file/folder to qsdqsd_DELETED_#id
			if (api_item_property_update ( $_course, $cwtype, $document_id, 'delete', api_get_user_id () )) {
				if (rename ( $base_work_dir . $path, $base_work_dir . $new_path )) {
					$sql = "UPDATE $TABLE_DOCUMENT set path='" . $new_path . "' WHERE id='" . $document_id . "'";
					if (api_sql_query ( $sql, __FILE__, __LINE__ )) {
						$sql = "SELECT id,path FROM " . $TABLE_DOCUMENT . " WHERE path LIKE '" . $path . "/%'";
						$result = api_sql_query ( $sql, __FILE__, __LINE__ );
						if ($result && mysql_num_rows ( $result ) > 0) {
							while ( $deleted_items = Database::fetch_array ( $result, "ASSOC" ) ) {
								api_item_property_update ( $_course, $cwtype, $deleted_items ['id'], 'delete', api_get_user_id () );
								$old_item_path = $deleted_items ['path'];
								$new_item_path = $new_path . substr ( $old_item_path, strlen ( $path ) );
								$sql = "UPDATE $TABLE_DOCUMENT set path = '" . $new_item_path . "' WHERE id = " . $deleted_items ['id'];
								api_sql_query ( $sql, __FILE__, __LINE__ );
							}
						}
						return true;
					}
				}
			} else {
				return false;
			}
		}
	}
}

/**
 * 显示学习课件的标签
 * @param $cur_tab_action
 * @return unknown_type
 */
function display_cw_action_menus($cur_tab_action) {
    //$myTools ['swf'] = array (get_lang ( 'MultiMediaCourseware' ), Display::return_icon ( 'file_flash.gif' ), api_get_path ( WEB_CODE_PATH ) . 'courseware/cw_media_list.php' );

    $myTools ['lp'] = array (get_lang ( 'LearnpathCW' ), Display::return_icon ( 'scorm.gif' ), api_get_path ( WEB_CODE_PATH ) . SCORM_PATH . 'lp_controller.php' );
	$myTools ['htmlcw'] = array (get_lang ( 'HTMLPackageCourseware' ), Display::return_icon ( 'file_zip.gif' ), api_get_path ( WEB_CODE_PATH ) . 'courseware/cw_package_list.php' );
	$myTools ['mediacw'] = array (get_lang ( 'MultiMediaCourseware' ), Display::return_icon ( 'file_flash.gif' ), api_get_path ( WEB_CODE_PATH ) . 'courseware/cw_media_list.php' );
	$myTools ['links'] = array (get_lang ( 'CourseLinks' ), Display::return_icon ( 'links_ad.gif' ), api_add_url_param ( api_get_path ( WEB_CODE_PATH ) . 'courseware/link_list.php?' . api_get_cidreq (), null ) );
	$html = '';
    $html .= '<ul class="yui-nav">';
	foreach ( $myTools as $key => $value ) {
		$strClass = ($cur_tab_action == $key ? 'class="selected"' : '');
		$html .= '<li  ' . $strClass . '><a href="' . api_add_url_param ( $value [2], 'tabAction=' . $key ) . '"><em>' . $value [0] . '</em></a></li>';
	}
	$html .= '</ul>';
	return $html;
}

function display_cw_type_tab($cur_tab_action) {
    $myTools ['swf'] = array (get_lang ( '在线浏览课件' ), api_get_path ( WEB_CODE_PATH ) . 'courseware/swf_update.php?action=add&' . api_get_cidreq () );
	$myTools ['lp'] = array (get_lang ( 'LearnpathCW' ), api_get_path ( WEB_CODE_PATH ) . 'upload/index.php?' . api_get_cidreq () . '&curdirpath=/&tool=' . TOOL_LEARNPATH );
	$myTools ['htmlcw'] = array (get_lang ( 'Html_courseware_package' ), api_get_path ( WEB_CODE_PATH ) . 'courseware/cw_upload.php?' . api_get_cidreq () );
	$myTools ['mediacw'] = array (get_lang ( 'MultiMediaCourseware' ), api_get_path ( WEB_CODE_PATH ) . 'courseware/cw_media_upload.php?' . api_get_cidreq () );
	$myTools ['links'] = array (get_lang ( 'CourseLinks' ), api_get_path ( WEB_CODE_PATH ) . 'courseware/link_update.php?action=add&' . api_get_cidreq () );
    $html = '';
	$html .= '<ul class="yui-nav">';
	foreach ( $myTools as $key => $value ) {
		$strClass = ($cur_tab_action == $key ? 'class="selected"' : '');
		$html .= '<li  ' . $strClass . '><a href="' . api_add_url_param ( $value [1], 'tabAction=' . $key ) . '"><em>' . $value [0] . '</em></a></li>';
	}
	$html .= '</ul>';
	return $html;
}

/*******************************************************************************************************/

function build_media_courseware_action_icons($path, $visibility, $id, $title) {
    $modify_icons = '';
	$modify_icons .= '&nbsp;' . link_button ( 'edit.gif', 'Modify', 'cw_upload_edit.php?type=mediacw&id=' . $id, '60%', '70%', false );
	$modify_icons .= '&nbsp;' . confirm_href ( 'delete.gif', 'AreYouSureToDelete', 'Delete', 'cw_list.php?cw_type=media&action=delete&path=' . urlencode ( $path ) );
	return $modify_icons;
}

/**
 * 课件处理完成后，写入courseware记录
 * @param $_course
 * @param $path
 * @param $filesize
 * @param $default_page
 * @param $title
 * @param $comment
 * @param $open_target
 * @return unknown_type
 */
function add_media_courseware($_course, $path, $filesize, $title, $comment = NULL, $open_target = '_self', $cw_type = 'media', $totalTime = 0, $display_order = 1) {
	$table_courseware = Database::get_course_table ( TABLE_COURSEWARE );
	$learning_time = round ( $totalTime / 60, 2 );
	if ($learning_time < 1.00) $learning_time = 1;
	$sql_data = array ('path' => $path, 'size' => $filesize, 'title' => $title, 'display_order' => $display_order, 'comment' => $comment, 'cw_type' => $cw_type, 'attribute' => $totalTime, 'learning_time' => $learning_time ); //'open_target' => $open_target,
	$sql_data ['cc'] = api_get_course_code ();
	$sql_data ['created_date'] = date ( 'Y-m-d H:i:s' );
	$sql = Database::sql_insert ( $table_courseware, $sql_data );
	if (api_sql_query ( $sql, __FILE__, __LINE__ )) {
		return Database::get_last_insert_id ();
	} else {
		return false;
	}
}

function handle_uploaded_media_courseware($_course, $uploaded_file, $new_filename, $user_id, $to_user_id = NULL, $title = null, $comment = NULL, $open_target = '_self', $display_order = 1, $what_if_file_exists = '', $output = true) {
	if (! $user_id) die ( "Not a valid user." );
	
	$uploaded_file ['name'] = stripslashes ( $uploaded_file ['name'] );
	$uploaded_file ['name'] = add_ext_on_mime ( $uploaded_file ['name'], $uploaded_file ['type'] );
	
	if (preg_match ( "/(.flv)|(.mp4)|(.mov)|(.mp3)$/i", strtolower ( $uploaded_file ['name'] ) )) {
		$base_work_dir = api_get_path ( SYS_COURSE_PATH ) . api_get_course_path () . "/document/flv/";
		$audio_total_play_time = getFLVDuration ( $base_work_dir . $new_filename );
		$audio_total_play_time = round ( floatval ( $audio_total_play_time ) );
		$cw_id = add_media_courseware ( $_course, "/flv/" . $new_filename, $uploaded_file ['size'], $title, $comment, $open_target, 'media', $audio_total_play_time, $display_order );
		if ($cw_id) {
			api_item_property_update ( $_course, TOOL_COURSEWARE_MEDIA, $cw_id, 'MediaCoursewareAdded', $user_id, NULL, $to_user_id );
			Display::display_confirmation_message ( get_lang ( 'UplUploadSucceeded' ), false );
			return true;
		} else {
			return false;
		}
	} else {
		Display::display_error_message ( get_lang ( 'UplNotAZip' ) . " " . get_lang ( 'PleaseTryAgain' ) );
		return false;
	}
	
	return false;
}

function create_media_link($www, $cw_info = array(), $target = '_self') {
	$url_path = urlencode ( $cw_info ['path'] );
	$file_id = basename ( strtolower ( $cw_info ['path'] ), '.flv' );
	if ($target == '_blank') {
		$visibility_class = ($cw_info ['visibility'] == 0) ? ' class="invisible"' : ' ';
		$url = api_get_path ( REL_PATH ) . "main/courseware/flv_player.php?cw_id=" . $cw_info ['id'] . "&target=" . $target;
	} elseif ($target == '_self') {
		$visibility_class = ($cw_info ['visibility'] == 0) ? ' class="invisible thickbox"' : ' class="thickbox"';
		$url = api_get_path ( REL_PATH ) . "main/courseware/flv_player.php?cw_id=" . $cw_info ['id'] . "&target=_blank&KeepThis=true&TB_iframe=true&height=420&width=580&modal=true";
	}
	$icon = Display::return_icon ( 'videos.gif', get_lang ( 'FLV' ), array ('style' => 'vertical-align: middle;' ) );
	return '<a href="' . $url . '" target="' . $target . '"' . $visibility_class . ' style="float:left" title="' . $cw_info ['title'] . '">' . $icon . '&nbsp;' . $cw_info ['title'] . '</a>';
}

function get_next_disp_order($course_code) {
	if (empty ( $course_code )) $course_code = api_get_course_code ();
	$sql = "SELECT MAX(display_order) FROM " . Database::get_course_table ( TABLE_COURSEWARE ) . " WHERE cc='" . escape ( $course_code ) . "'";
	$order = Database::getval ( $sql, __FILE__, __LINE__ );
	$disp_order = (empty ( $order ) ? 1 : intval ( $order ) + 1);
	return $disp_order;
}