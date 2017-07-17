<?php
require_once ('learnpath.class.php');
$use_anonymous = true;
require_once ('back_compat.inc.php');
$tbl_courseware = Database::get_course_table ( TABLE_COURSEWARE );
$course_sys_dir = api_get_path ( SYS_COURSE_PATH ) . api_get_course_path () . '/scorm/';
$current_dir = (empty ( $_POST ['current_dir'] ) ? '' : replace_dangerous_char ( trim ( $_POST ['current_dir'] ), 'strict' ));
$file_location = $_POST ['file_location']['location'];

//上传HTTP上传的文件
if (is_equal ( $file_location, "1" ) && IS_POST && count ( $_FILES ) > 0 && ! empty ( $_FILES ['user_file'] ['name'] )) {
	//设置内存及执行时间
	@ini_set ( 'memory_limit', '256M' );
	@ini_set ( 'max_execution_time', 1800 ); //设置执行时间
	$stopping_error = false;
	$upload_ok = process_uploaded_file ( $_FILES ['user_file'] );
	if ($upload_ok) {
		$s = $_FILES ['user_file'] ['name'];
		$info = pathinfo ( $s );
		$filename = $info ['basename'];
		$extension = $info ['extension'];
		$file_base_name = str_replace ( '.' . $extension, '', $filename );
		
		if (! in_array ( strtolower ( $extension ), array ('zip', 'ppt' ) )) {return api_failure::set_failure ( 'not_a_learning_path' );}
		
		$new_dir = replace_dangerous_chars ( trim ( $file_base_name ), 'strict' );
                $p_title=  getgpc('title');
		if (isset ( $p_title )) $file_base_name = trim ( $p_title );
		
		//课件文档类型
		$type = learnpath::get_package_type ( $_FILES ['user_file'] ['tmp_name'], $_FILES ['user_file'] ['name'] );
		api_error_log ( "Import FTP SCORM=" . $_FILES ['user_file'] ['tmp_name'] . ",type=" . $type, __FILE__, __LINE__, "scorm.log" );
		if ($type == 'scorm') {
			require_once ('scorm.class.php');
			$oScorm = new scorm ();
			$manifest = $oScorm->import_package ( $_FILES ['user_file'], $current_dir );
			if (! empty ( $manifest )) {
				$oScorm->parse_manifest ( $manifest );
				$oScorm->import_manifest ( api_get_course_id () );
			}
			$lp_id = $oScorm->get_id ();
			
			$maker = getgpc ( 'content_maker' );
			$oScorm->set_proximity ( getgpc ( 'content_proximity' ) );
			$oScorm->set_maker ( $maker );
			$oScorm->set_name ( $file_base_name );
			$oScorm->set_learning_time ( getgpc ( "learning_time" ) );
			$oScorm->set_learning_order ( getgpc ( "learning_order" ) );
			$lp_path = substr ( $oScorm->subdir, 0, - 2 );
			$path = (substr ( $oScorm->subdir, - 1 ) == "." ? substr ( $oScorm->subdir, 0, - 1 ) : $oScorm->subdir);
			$scorm_cw_path = $course_sys_dir . $path;
			$size = file_exists ( $scorm_cw_path ) ? dir_total_space ( $scorm_cw_path ) : 0;
			if (empty ( $size )) $size = 0;
			$sql_data = array ("path" => "scorm/" . $path, "title" => $file_base_name, "size" => $size, "comment" => $maker, "cw_type" => "scorm" );
			$sql_data ['display_order'] = getgpc ( "learning_order" );
			$sql_data ['learning_time'] = getgpc ( "learning_time" );
			$sql_data ['cc'] = api_get_course_code ();
			$sql_data ['attribute'] = $lp_id;
			$sql_data ['created_date'] = date ( 'Y-m-d H:i:s' );
			$sql = Database::sql_insert ( $tbl_courseware, $sql_data );
			if (api_sql_query ( $sql, __FILE__, __LINE__ )) $cw_id = Database::get_last_insert_id ();
		} else {
			exit ( "不支持课件格式!" );
		}
	}
}

//使用FTP上传的大SCORM文件
if (is_equal ( $file_location, "2" ) && $_SERVER ['REQUEST_METHOD'] == 'POST') {
	//设置内存及执行时间
	@ini_set ( 'memory_limit', '256M' );
	@ini_set ( 'max_execution_time', 1800 ); //设置执行时间
	$stopping_error = false;
	$ftp_path = api_get_path ( SYS_FTP_ROOT_PATH ) . 'scorm/';
	$s = $ftp_path . getgpc ( 'file_name', 'P' );
	
	$info = pathinfo ( $ftp_path . getgpc ( 'file_name', 'P' ) );
	$filename = $info ['basename'];
	$extension = $info ['extension'];
	$file_base_name = str_replace ( '.' . $extension, '', $filename );
	$new_dir = replace_dangerous_char ( trim ( $file_base_name ), 'strict' );
        $p_title=  getgpc('title');
	if (isset ( $p_title )) $file_base_name = $p_title;
	
	$type = learnpath::get_package_type ( $s, $file_base_name ); //echo $type;exit;
	api_scorm_log ( "Import FTP SCORM=" . $s . ",type=" . $type, __FILE__, __LINE__ );
	if ($type == 'scorm') {
		require_once ('scorm.class.php');
		$oScorm = new scorm ();
		$manifest = $oScorm->import_local_package ( $s, $current_dir );
		if (! empty ( $manifest )) {
			$oScorm->parse_manifest ( $manifest );
			$oScorm->import_manifest ( api_get_course_id () );
		}
		$lp_id = $oScorm->get_id ();
		
		$maker = getgpc ( 'content_maker' );
		$oScorm->set_maker ( $maker );
		$oScorm->set_proximity ( getgpc ( 'content_proximity' ) );
		$oScorm->set_name ( $file_base_name );
		$oScorm->set_learning_time ( getgpc ( "learning_time" ) );
		$oScorm->set_learning_order ( 0 );
		$lp_path = substr ( $oScorm->subdir, 0, - 2 );
		$path = (substr ( $oScorm->subdir, - 1 ) == "." ? substr ( $oScorm->subdir, 0, - 1 ) : $oScorm->subdir);
		$size = dir_total_space ( $course_sys_dir . $path );
		$sql_data = array ("path" => "scorm/" . $path, "title" => $file_base_name, "size" => $size, "comment" => $maker, "cw_type" => "scorm" );
		$sql_data ['display_order'] = getgpc ( "learning_order" );
		$sql_data ['learning_time'] = getgpc ( "learning_time" );
		$sql_data ['cc'] = api_get_course_code ();
		$sql_data ['attribute'] = $lp_id;
		$sql_data ['created_date'] = date ( 'Y-m-d H:i:s' );
		$sql = Database::sql_insert ( $tbl_courseware, $sql_data );
		if (api_sql_query ( $sql, __FILE__, __LINE__ )) {
			$cw_id = Database::get_last_insert_id ();
			my_delete ( $s );
		}
	} else {
		exit ( "不支持课件格式!" );
	}
}