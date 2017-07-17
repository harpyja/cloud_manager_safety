<?php

/*
 ==============================================================================
 创建课程
 ==============================================================================
 */

/**
 * 创建课程
 * @return true if the course creation was succesful, false otherwise.
 */
function create_course($wanted_code, $course_code, $created_user, $course_admin_id, $title, $other_data = array()) {
	global $_configuration;
	$db_prefix = $_configuration ['db_prefix'];
	
	$keys = define_course_keys ( $wanted_code, "", $db_prefix );
	
	if ($keys && is_array ( $keys )) {
		$code = $keys ["currentCourseId"];
		if (! empty ( $course_code )) $code = $course_code;
		
		$db_name = $_configuration ['main_database'];
		$directory = $keys ["currentCourseRepository"];
		$visual_code = $keys ["currentCourseCode"];

		$course_language = $other_data ['course_language'];
		$description = ($other_data ['description'] ? $other_data ['description'] : $title);
		$category = $other_data ['category_code'];
		$defaultVisibilityForANewCourse = $other_data ['visibility'];
		$tutor_name = $other_data ['tutor_name'];
		$disk_quota = 0;
		$subscribe = $other_data ['subscribe'];
		$unsubscribe = $other_data ['unsubscribe'];
		$credit = $other_data ['credit'];
		$is_free = $other_data ['is_free'];
		$fee = $other_data ['fee'];
		$is_audit_enabled = $other_data ['is_audit_enabled'];
		$credit_hours = $other_data ['credit_hours'];
		$is_subscribe_enabled = $other_data ['is_subscribe_enabled'];
		$is_shown = $other_data ['is_shown'];
		$pass_condition = $other_data ['pass_condition'];
		$org_id = $other_data ['org_id'];
		$default_learing_days = $other_data ["default_learing_days"];
        $nodeId=$other_data["nodeId"];  //自定义编号
		
		$start_date = $other_data ['start_date'];
		$expiration_date = $other_data ['expiration_date'];
		if (empty ( $expiration_date )) {
			$expiration_date = "0000-00-00 00:00:00";
		}
		$course_managers = explode ( ',', $course_admin_id );
		
		//写课程记录到数据库表course
		$course_data = array (
				'code' => $code, 
					'db_name' => $db_name, 
					'directory' => $directory, 
					'course_language' => $course_language, 
					'title' => $title, 
					'description' => $title,
            //dengxin   20120609
                    'description12' => '1',
                    'category_code' => $category,
					'visibility' => $defaultVisibilityForANewCourse, 
					'tutor_name' => $tutor_name,
					'visual_code' => $visual_code, 
					'disk_quota' => $disk_quota, 
					'expiration_date' => $expiration_date, 
					'start_date' => $start_date, 
					'subscribe' => $subscribe, 
					'unsubscribe' => $unsubscribe, 
					'credit' => $credit, 
					'credit_hours' => $credit_hours, 
					'is_free' => $is_free, 
					'fee' => $fee, 
					'is_audit_enabled' => $is_audit_enabled, 
					'creator_id' => $created_user, 
					'course_teachers' => $course_managers, 
					'tutor_id' => $created_user, 
					'is_subscribe_enabled' => $is_subscribe_enabled, 
					'is_shown' => $is_shown, 
					'pass_condition' => $pass_condition, 
					'org_id' => $org_id,
            'nodeId'=>$nodeId,
					'default_learing_days' => $default_learing_days );
		$reg_result = register_course ( $course_data );
		
		//创建课程仓库目录及设置目录访问权限,创建相关文件
		prepare_course_repository ( $directory, $code );
		
		//创建课程目录中的示例文件
		$pictures_array = fill_course_repository ( $directory );
		
		//创建课程的示例初始数据
		fill_Db_course ( $code, $db_name, $directory, $course_language, $pictures_array );
		
		return $reg_result;
	} else {
		return false;
	}
}

/**
 * Defines the four needed keys to create a course based on several parameters.
 * @return array with the needed keys ["currentCourseCode"], ["currentCourseId"], ["currentCourseDbName"], ["currentCourseRepository"]
 *
 * @param	$wantedCode the code you want for this course
 * @param	string prefix // prefix added for ALL keys
 * @todo	eliminate globals
 */
function define_course_keys($wantedCode, $prefix4all = "", $prefix4baseName = "", $prefix4path = "", $addUniquePrefix = false, $useCodeInDepedentKeys = true) {
	global $prefixAntiNumber, $_configuration;
	$course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
	$wantedCode = ereg_replace ( "[^A-Z0-9]", "", strtoupper ( $wantedCode ) );
	if (empty ( $wantedCode )) {
		$wantedCode = "CL";
	}
	
	$keysCourseCode = $wantedCode;
	if (! $useCodeInDepedentKeys) {
		$wantedCode = '';
	}
	
	$uniquePrefix = ($addUniquePrefix ? substr ( md5 ( uniqid ( rand () ) ), 0, 10 ) : '');
	$uniqueSuffix = ($addUniqueSuffix ? substr ( md5 ( uniqid ( rand () ) ), 0, 10 ) : '');
	
	$keys = array ();
	
	$finalSuffix = array ('CourseId' => '', 'CourseDb' => '', 'CourseDir' => '' );
	$limitNumbTry = 100;
	$keysAreUnique = false;
	$tryNewFSCId = $tryNewFSCDb = $tryNewFSCDir = 0;
	while ( ! $keysAreUnique ) {
		$keysCourseId = $prefix4all . $uniquePrefix . $wantedCode . $uniqueSuffix . $finalSuffix ['CourseId'];
		$keysCourseDbName = $prefix4baseName . $uniquePrefix . strtoupper ( $keysCourseId ) . $uniqueSuffix . $finalSuffix ['CourseDb'];
		$keysCourseRepository = $prefix4path . $uniquePrefix . $wantedCode . $uniqueSuffix . $finalSuffix ['CourseDir'];
		$keysAreUnique = true;
		
		// check if they are unique
		$query = "SELECT 1 FROM " . $course_table . " WHERE code='" . $keysCourseId . "' LIMIT 1";
		$result = api_sql_query ( $query, __FILE__, __LINE__ );
		
		if ($keysCourseId == DEFAULT_COURSE || mysql_num_rows ( $result )) {
			$keysAreUnique = false;
			$tryNewFSCId ++;
			$finalSuffix ['CourseId'] = substr ( md5 ( uniqid ( rand () ) ), 0, 4 );
		}
		
		$query = "SHOW TABLES FROM `" . $_configuration ['main_database'] . "` LIKE '" . $_configuration ['table_prefix'] . "$keysCourseDbName" . $_configuration ['db_glue'] . "%'";
		$result = api_sql_query ( $query, __FILE__, __LINE__ );
		
		if (mysql_num_rows ( $result )) {
			$keysAreUnique = false;
			$tryNewFSCDb ++;
			$finalSuffix ['CourseDb'] = substr ( '_' . md5 ( uniqid ( rand () ) ), 0, 4 );
		}
		
		// @todo: use and api_get_path here instead of constructing it by yourself
		if (file_exists ( api_get_path ( SYS_COURSE_PATH ) . $keysCourseRepository )) {
			$keysAreUnique = false;
			$tryNewFSCDir ++;
			$finalSuffix ['CourseDir'] = substr ( md5 ( uniqid ( rand () ) ), 0, 4 );
		}
		
		if (($tryNewFSCId + $tryNewFSCDb + $tryNewFSCDir) > $limitNumbTry) {return $keys;}
	}
	
	// db name can't begin with a number
	if (! stristr ( "abcdefghijklmnopqrstuvwxyz", $keysCourseDbName [0] )) {
		$keysCourseDbName = $prefixAntiNumber . $keysCourseDbName;
	}
	
	$keys ["currentCourseCode"] = $keysCourseCode;
	$keys ["currentCourseId"] = $keysCourseId;
	$keys ["currentCourseDbName"] = $keysCourseDbName;
	$keys ["currentCourseRepository"] = $keysCourseRepository;
	
	return $keys;
}

/**
 * 创建课程仓库目录及设置目录访问权限,创建相关文件
 * @param $courseRepository  课程目录名
 * @param $courseId	课程编号
 * @return unknown_type
 */
function prepare_course_repository($courseRepository, $courseId) {
	umask ( 0 );
	if (! file_exists ( api_get_path ( SYS_COURSE_PATH ) )) mkdir ( api_get_path ( SYS_COURSE_PATH ), CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository, CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/document", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/document/images", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/document/images/gallery", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/document/audio", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/document/flash", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/document/flv", CHMOD_NORMAL ); //liyu
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/document/video", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/document/learnpath", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/document/others", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/attachments", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/html", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/scorm", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/temp", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/upload", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/upload/forum", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/upload/test", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/upload/blog", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/upload/learning_path", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/upload/learning_path/images", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/work", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/assignment", CHMOD_NORMAL );
	
	//创建zlmeet的相关目录
	//if(api_get_setting('course_create_active_tools','zlmeet_creation')=='true'){
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/zlmeet", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/zlmeet/upload", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/zlmeet/upload/temp", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/zlmeet/wbUpload", CHMOD_NORMAL );
	mkdir ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/zlmeet/pptUpload", CHMOD_NORMAL );
	//}
	

	//create .htaccess in dropbox
	$fp = fopen ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/dropbox/.htaccess", "w" );
	fwrite ( $fp, "AuthName AllowLocalAccess
	               AuthType Basic

	               order deny,allow
	               deny from all

	               php_flag zlib.output_compression off" );
	fclose ( $fp );
	
	// build index.php of course
	$fd = fopen ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/index.php", "w" );
	// str_replace() removes \r that cause squares to appear at the end of each line
	$string = str_replace ( "\r", "", "<?" . "php
	\$cidReq = \"$courseId\";
	\$dbname = \"$courseId\";

	include('../../../main/course_home/course_home.php');
	?>" );
	fwrite ( $fd, "$string" );
	
	$fd = fopen ( api_get_path ( SYS_COURSE_PATH ) . $courseRepository . "/group/index.php", "w" );
	$string = "<html></html>";
	fwrite ( $fd, "$string" );
	fclose ( $fd );
	return 0;
}

/**
 * 创建单门课程的数据库及表格, V1.4.0 启用，读取SQL方式
 *
 * @param string $courseDbName 课程数据库的名称
 * @return unknown
 */
function update_Db_course($courseDbName) {
	global $_configuration;
	global $_database_connection;
	
	$sql_file = SERVER_DATA_DIR . "db/course_db.sql";
	if (file_exists ( $sql_file ) && is_file ( $sql_file ) && is_readable ( $sql_file )) {
		
		if (! $_configuration ['single_database']) {
			api_sql_query ( "CREATE DATABASE IF NOT EXISTS `" . $courseDbName . "` default charset utf8 COLLATE utf8_bin", __FILE__, __LINE__ );
		}
		
		$sql_file_content = sreadfile ( $sql_file );
		if ($sql_file_content) $file_content_lines = explode ( ";", $sql_file_content );
		
		if (is_array ( $file_content_lines ) && $file_content_lines) {
			mysql_select_db ( $courseDbName, $_database_connection );
			foreach ( $file_content_lines as $sql_stmt ) {
				$sql_stmt = trim ( $sql_stmt, "\r\n" );
				if ($sql_stmt && $sql != "\r\n") {
					$sql_stmt = str_replace ( "\r\n", "", $sql_stmt );
					api_sql_query ( $sql_stmt, __FILE__, __LINE__ );
				}
			}
		}
		mysql_select_db ( $_configuration ['main_database'], $_database_connection );
		return TRUE;
	}
	return FALSE;
}

/**
 * 递归得到所有的文件, 返回的数组分为两类: dir,file;
 * 内容为去掉前面路径后的相对路径,如(D:/ZLMS/htdocs/zlms/main/zlmeet_cp/)upload/temp/
 * @param unknown_type $path 源目录
 * @param unknown_type $files 外部变量,文件数组
 * @param unknown_type $media 要复制的文件类别
 * @return unknown
 */
function browse_folders($path, $files, $media) {
	if ($media == 'images') {
		$code_path = api_get_path ( SYS_PATH ) . "storage/default_course_document/images/";
	}
	if ($media == 'audio') {
		$code_path = api_get_path ( SYS_PATH ) . "storage/default_course_document/audio/";
	}
	if ($media == 'flash') {
		$code_path = api_get_path ( SYS_PATH ) . "storage/default_course_document/flash/";
	}
	if ($media == 'video') {
		$code_path = api_get_path ( SYS_PATH ) . "storage/default_course_document/video/";
	}
	if ($media == 'zlmeet') {
		//$code_path = api_get_path(SYS_PATH)."storage/zlmeet/";
		$code_path = api_get_path ( SYS_EXTENSIONS_PATH ) . "zlmeet/";
	}
	if (is_dir ( $path )) {
		$handle = opendir ( $path );
		while ( false !== ($file = readdir ( $handle )) ) {
			if (is_dir ( $path . $file ) && strpos ( $file, '.' ) !== 0) {
				$files [] ["dir"] = str_replace ( $code_path, "", $path . $file . "/" );
				$files = browse_folders ( $path . $file . "/", $files, $media );
			} elseif (is_file ( $path . $file ) && strpos ( $file, '.' ) !== 0) {
				$files [] ["file"] = str_replace ( $code_path, "", $path . $file );
			}
		}
	}
	return $files;
}

/**
 * 过滤得到某种类型的文件数组
 *
 * @param unknown_type $files 递归所得的某目录下的所有文件,包括目录
 * @param unknown_type $type 为dir/file
 * @return unknown
 */
function sort_pictures($files, $type) {
	$pictures = array ();
	foreach ( $files as $key => $value ) {
		if ($value [$type] != "") {
			$pictures [] [$type] = $value [$type];
		}
	}
	return $pictures;
}

/**
 * 在课程数据的目录中放些示例文件
 * Fills the course repository with some example content.
 * @return 各类型文件的数组
 * @version	 1.2
 */
function fill_course_repository($courseRepository) {
	$sys_course_path = api_get_path ( SYS_COURSE_PATH );
	$web_code_path = api_get_path ( WEB_CODE_PATH );
	
	$doc_html = file ( api_get_path ( SYS_PATH ) . 'storage/default_course_document/document/example_document.html' );
	$fp = fopen ( $sys_course_path . $courseRepository . '/document/example_document.html', 'w' );
	foreach ( $doc_html as $key => $enreg ) {
		$enreg = str_replace ( '{IMGPATH}', api_get_path ( WEB_IMG_PATH ) . 'gallery/', $enreg );
		fputs ( $fp, $enreg );
	}
	fclose ( $fp );
	
	$default_document_array = array ();
	
	$default_document_array ['zlmeet'] = fill_course_repository_with_zlmeet ( $courseRepository );
	
	return $default_document_array;
}

/**
 * 创建zlmeet视频会议的相关目录及文件,每个课程一份
 *
 * @param unknown_type $courseRepository
 */
function fill_course_repository_with_zlmeet($courseRepository) { //liyu
	$sys_course_path = api_get_path ( SYS_COURSE_PATH );
	$web_code_path = api_get_path ( WEB_CODE_PATH );
	$sys_code_path = api_get_path ( SYS_CODE_PATH );
	$sys_extensions_path = api_get_path ( SYS_EXTENSIONS_PATH );
	
	//$zlmeet_code_path = api_get_path(SYS_PATH)."storage/zlmeet/";
	$zlmeet_code_path = $sys_extensions_path . "zlmeet/"; //源目录
	$course_zlmeet_folder = $sys_course_path . $courseRepository . '/zlmeet/'; //目标目录
	

	$files = array ();
	$files = browse_folders ( $zlmeet_code_path, $files, 'zlmeet' );
	
	$zlmeet_array = sort_pictures ( $files, "dir" );
	$zlmeet_array = array_merge ( $zlmeet_array, sort_pictures ( $files, "file" ) );
	//这时的 $zlmeet_array 即为一维的某目录下所有文件(包括目录)
	

	mkdir ( $course_zlmeet_folder, CHMOD_NORMAL );
	$handle = opendir ( $zlmeet_code_path );
	foreach ( $zlmeet_array as $key => $value ) {
		if ($value ["dir"] != "") {
			mkdir ( $course_zlmeet_folder . $value ["dir"], CHMOD_NORMAL );
		}
		if ($value ["file"] != "") {
			copy ( $zlmeet_code_path . $value ["file"], $course_zlmeet_folder . $value ["file"] );
			chmod ( $course_zlmeet_folder . $value ["file"], CHMOD_NORMAL );
		}
	}
	$default_document_array ['zlmeet'] = $zlmeet_array;
	return $default_document_array;
}

function fillin_course_db($table, $sql_arr, $currentCourseCode) {
	return Database::insert_into_course_table ( $table, $sql_arr, $currentCourseCode );
}

/**
 * 往新建的课程中增加一些必需的数据或示例内容
 * Fills the course database with some required content and example content.
 *
 * @param unknown_type $courseDbName
 * @param unknown_type $courseRepository
 * @param unknown_type $language
 * @param unknown_type $default_document_array
 * @return unknown
 */
function fill_Db_course($currentCourseCode, $courseDbName, $courseRepository, $language, $default_document_array) {
	global $_configuration, $_user;
	
	$courseDbName = $_configuration ['table_prefix'];
	
	$TABLEITEMPROPERTY = $courseDbName . "item_property";
	
	$TABLETOOLANNOUNCEMENTS = $courseDbName . "announcement";
	$TABLETOOLDOCUMENT = $courseDbName . "document";
	
	$TABLEQUIZ = $courseDbName . "quiz";
	$TABLEQUIZQUESTION = $courseDbName . "quiz_rel_question";
	$TABLEQUIZQUESTIONLIST = "exam_question";
	$TABLEQUIZANSWERSLIST = "exam_answer";
	$TABLESETTING = $courseDbName . "course_setting";
	
	include_once (api_get_path ( SYS_PATH ) . "lang/english/create_course.inc.php");
	include_once (api_get_path ( SYS_PATH ) . "lang/" . $language . "/create_course.inc.php");
	
	$visible4all = 1;
	$visible4AdminOfCourse = 0;
	$visible4AdminOfClaroline = 2;
	$now = date ( 'Y-m-d H:i:s' );
	
	$sql_arr = array ('path' => '/learnpath', 'title' => get_lang ( 'Learnpath' ), 'filetype' => 'folder', 'size' => '0' );
	fillin_course_db ( $TABLETOOLDOCUMENT, $sql_arr, $currentCourseCode );
	$example_doc_id = Database::get_last_insert_id ();
	$sql_arr = array ('tool' => TOOL_DOCUMENT, 'insert_user_id' => api_get_user_id (), 'insert_date' => $now, 'lastedit_date' => $now, 'ref' => $example_doc_id, 'lastedit_type' => 'DocumentAdded', 'lastedit_user_id' => api_get_user_id (), 'visibility' => '0' );
	fillin_course_db ( $TABLEITEMPROPERTY, $sql_arr, $currentCourseCode );
	
	if (FALSE) {
		/*
	 -----------------------------------------------------------
		写入课程简介的标签  2009120
		-----------------------------------------------------------
		*/
		$tbl_course_description = $courseDbName . "course_description";
		$TABLETOOLCOURSEDESC = $courseDbName . "course_description";
		$default_description_titles = array (); //课程简介的标签名
		$default_description_titles [1] = get_lang ( 'GeneralDescription' );
		$default_description_titles [2] = get_lang ( 'Objectives' );
		$default_description_titles [3] = get_lang ( 'Topics' );
		/*$default_description_titles [4] = get_lang ( 'Methodology' );
	$default_description_titles [5] = get_lang ( 'CourseMaterial' );
	$default_description_titles [6] = get_lang ( 'HumanAndTechnicalResources' );
	$default_description_titles [7] = get_lang ( 'Assessment' );
	$default_description_titles [8] = get_lang ( 'NewBloc' );*/
		for($desc_id = 1; $desc_id <= 3; $desc_id ++) {
			$sql_arr = array ('id' => $desc_id, 'title' => $default_description_titles [$desc_id], 'enabled' => 1, 'display_order' => $desc_id );
			fillin_course_db ( $tbl_course_description, $sql_arr, $currentCourseCode );
		}
		
		/*-----------------------------------------------------------
			论坛
		 -----------------------------------------------------------
		 */
		$TABLEFORUMCATEGORIES = $courseDbName . "forum_category";
		$TABLEFORUMS = $courseDbName . "forum_forum";
		$TABLEFORUMTHREADS = $courseDbName . "forum_thread";
		$TABLEFORUMPOSTS = $courseDbName . "forum_post";
		
		$sql_arr = array ('cat_title' => lang2db ( get_lang ( 'ExampleForumCategory' ) ), 'cat_comment' => "", 'cat_order' => '1', 'locked' => '0' );
		fillin_course_db ( $TABLEFORUMCATEGORIES, $sql_arr, $currentCourseCode );
		$insert_id = Database::get_last_insert_id ();
		$sql_arr = array (
				'tool' => 'forum_category', 
					'insert_user_id' => api_get_user_id (), 
					'insert_date' => $now, 
					'lastedit_date' => $now, 
					'ref' => $insert_id, 
					'lastedit_type' => 'ForumCategoryAdded', 
					'lastedit_user_id' => api_get_user_id (), 
					'to_group_id' => '0', 
					'to_user_id' => 'NULL', 
					'visibility' => 1 );
		fillin_course_db ( $TABLEITEMPROPERTY, $sql_arr, $currentCourseCode );
		
		$sql_arr = array (
				'forum_title' => lang2db ( get_lang ( 'ExampleForum' ) ), 
					'forum_comment' => "", 
					'forum_threads' => '0', 
					'forum_posts' => '0', 
					'forum_last_post' => '0', 
					'forum_category' => '1', 
					'allow_anonymous' => '0', 
					'allow_edit' => '1', 
					'approval_direct_post' => '0', 
					'allow_attachments' => '1', 
					'allow_new_threads' => '1', 
					'default_view' => 'flat', 
					'forum_of_group' => '0', 
					'forum_group_public_private' => 'public', 
					'forum_order' => '1', 
					'locked' => '0' );
		fillin_course_db ( $TABLEFORUMS, $sql_arr, $currentCourseCode );
		$insert_id = Database::get_last_insert_id ();
		$sql_arr = array ('tool' => TOOL_FORUM, 'insert_user_id' => api_get_user_id (), 'insert_date' => $now, 'lastedit_date' => $now, 'ref' => $insert_id, 'lastedit_type' => 'ForumAdded', 'lastedit_user_id' => api_get_user_id (), 'to_group_id' => '0', 'to_user_id' => 'NULL', 'visibility' => 1 );
		fillin_course_db ( $TABLEITEMPROPERTY, $sql_arr, $currentCourseCode );
		
		$sql_arr = array ('thread_title' => lang2db ( get_lang ( 'ExampleThread' ) ), 'forum_id' => '1', 'thread_replies' => '0', 'thread_poster_id' => '1', 'thread_poster_name' => '', 'thread_views' => '0', 'thread_last_post' => '1', 'thread_date' => $now, 'thread_sticky' => '0', 'locked' => '0' );
		fillin_course_db ( $TABLEFORUMTHREADS, $sql_arr, $currentCourseCode );
		$insert_id = Database::get_last_insert_id ();
		$sql_arr = array (
				'tool' => 'forum_thread', 
					'insert_user_id' => api_get_user_id (), 
					'insert_date' => $now, 
					'lastedit_date' => $now, 
					'ref' => $insert_id, 
					'lastedit_type' => 'ForumThreadAdded', 
					'lastedit_user_id' => api_get_user_id (), 
					'to_group_id' => '0', 
					'to_user_id' => 'NULL', 
					'visibility' => 1 );
		fillin_course_db ( $TABLEITEMPROPERTY, $sql_arr, $currentCourseCode );
		
		$sql_arr = array (
				'post_title' => lang2db ( get_lang ( 'ExampleThread' ) ), 
					'post_text' => lang2db ( get_lang ( 'ExampleThreadContent' ) ), 
					'thread_id' => '1', 
					'forum_id' => '1', 
					'poster_id' => '1', 
					'poster_name' => '', 
					'post_date' => $now, 
					'post_notification' => '0', 
					'post_parent_id' => '0', 
					'visible' => '1' );
		fillin_course_db ( $TABLEFORUMPOSTS, $sql_arr, $currentCourseCode );
	}
	
	return 0;
}

/**
 * 创建课程: 写课程记录到数据库表course
 * function register_course to create a record in the course table of the main database
 *
 * @param string $courseSysCode 课程代码(ID标识)
 * @param unknown_type $courseScreenCode 即visual_code,显示的代码
 * @param unknown_type $courseRepository 课程目录
 * @param unknown_type $courseDbName 课程数据库名
 * @param unknown_type $titular 即tutor_name
 * @param unknown_type $category 分类
 * @param unknown_type $title 标题
 * @param unknown_type $course_language 语言
 * @param unknown_type $uidCreator 授课老师
 * @param unknown_type $teachers 课程管理员
 * @param string $start_date 课程开始时间
 * @param unknown_type $expiration_date 过期时间
 * @param int $credit 学分
 * @param int $is_free 是否为免费课程。1：免费课程
 * @param float $fee 课程金额，当$is_free=0时设置
 * @param int $is_audit_enabled 是否允许课程管理员，主讲教师审批学员注册课程，1允许
 * @return unknown
 */
function register_course($data) {
	global $_configuration, $firstExpirationDelay;
	
	$courseSysCode = $data ['code'];
	$courseScreenCode = $data ['visual_code'];
	$courseRepository = $data ['directory'];
	$courseDbName = $data ['db_name'];
	$titular = $data ['tutor_name'];
	$category = $data ['category_code'];
	$visibility = $data ["visibility"];
	$title = $data ['title'];
	$course_language = $data ['course_language'];
	$disk_quota = 0;
	$uidCreator = $data ['creator_id']; //讲师
	$tutor_id = $data ['tutor_id']; //讲师
	$teachers = $data ['course_teachers']; //课程管理员
	$start_date = $data ['start_date'];
	$expiration_date = $data ['expiration_date'];
	$subscribe = $data ['subscribe'];
	$unsubscribe = $data ['unsubscribe'];
	$credit = $data ['credit'];
	$credit_hours = $data ['credit_hours'];
	$is_free = $data ['is_free'];
	$fee = $data ['fee'];
	$is_audit_enabled = $data ['is_audit_enabled'];
	$is_subscribe_enabled = $data ['is_subscribe_enabled'];
	$is_shown = $data ['is_shown'];
	$pass_condition = $data ['pass_condition'];
	$org_id = $data ['org_id'];
	$default_learing_days = $data ['default_learing_days'];
    $nodeId=$data['nodeId'];
	
	$TABLECOURSE = Database::get_main_table ( TABLE_MAIN_COURSE );
	$TABLECOURSUSER = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	
	$okForRegisterCourse = true;
	$required = array ($courseSysCode, $courseScreenCode, $courseDbName, $courseRepository, $title );
	foreach ( $required as $v ) {
		if (empty ( $v )) {
			$okForRegisterCourse = false;
			break;
		}
	}
	if (empty ( $expiration_date )) $expiration_date = "0000-00-00 00:00:00";
	if ($is_free) $fee = 0;
	
	if ($okForRegisterCourse) {
		$now = date ( 'Y-m-d H:i:s' );
		$sql_data = array (
				'code' => $courseSysCode, 
					'db_name' => $courseDbName, 
					'directory' => $courseRepository, 
					'course_language' => $course_language, 
					'title' => $title, 
					'description' => lang2db ( get_lang ( 'CourseDescription' ) ),
                    //dengxin  20120609
					'description12' => '1',
					'category_code' => $category,
					'visibility' => $visibility, 
					'tutor_name' => $titular, 
					'visual_code' => $courseScreenCode, 
					'disk_quota' => $disk_quota, 
					'creation_date' => $now, 
					"created_user" => $uidCreator, 
					'expiration_date' => $expiration_date, 
					'last_edit' => $now, 
					'last_visit' => 'NULL', 
					'subscribe' => $subscribe, 
					'unsubscribe' => $unsubscribe, 
					'start_date' => $start_date, 
					'credit' => $credit, 
					'credit_hours' => $credit_hours, 
					'is_free' => $is_free, 
					'fee' => $fee, 
					'is_audit_enabled' => $is_audit_enabled, 
					'is_subscribe_enabled' => $is_subscribe_enabled, 
					'is_shown' => $is_shown, 
					'pass_condition' => $pass_condition, 
					'org_id' => $org_id,
            'nodeId'=>$nodeId,  //自定义编号
					'default_learing_days' => $default_learing_days );
		$sql = Database::sql_insert ( $TABLECOURSE, $sql_data );
		//echo $sql;exit;
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		if (is_string ( $teachers )) $course_managers = array ($teachers );
		elseif (is_array ( $teachers )) $course_managers = $teachers;
		foreach ( $course_managers as $admin_id ) {
			if ($admin_id) {
				$sql_data = array (
						'course_code' => $courseSysCode, 
							'user_id' => $admin_id, 
							'status' => COURSEMANAGER, 
							'role' => get_lang ( "CourseAdmin" ), 
							'is_course_admin' => '1', 
							'tutor_id' => '1', 
							'is_required_course' => 1, 
							'begin_date' => date ( "Y-m-d" ), 
							'finish_date' => date ( "Y-m-d", strtotime ( "+ $firstExpirationDelay seconds" ) ) );
				$sql = Database::sql_insert ( $TABLECOURSUSER, $sql_data, TRUE );
				api_sql_query ( $sql, __FILE__, __LINE__ );
			}
		}
		return TRUE;
	}
	return FALSE;
}

function checkArchive($pathToArchive) {
	return TRUE;
}

function readPropertiesInArchive($archive, $isCompressed = TRUE) {
	include (api_get_path ( LIB_PATH ) . "pclzip/pclzip.lib.php");
	printVar ( dirname ( $archive ), "Zip : " );
	$zipFile = new pclZip ( $archive );
	$tmpDirName = dirname ( $archive ) . "/tmp" . $uid . uniqid ( $uid );
	if (mkpath ( $tmpDirName )) $unzippingSate = $zipFile->extract ( $tmpDirName );
	else die ( "mkpath failed" );
	$pathToArchiveIni = dirname ( $tmpDirName ) . "/archive.ini";
	$courseProperties = parse_ini_file ( $pathToArchiveIni );
	rmdir ( $tmpDirName );
	return $courseProperties;
}

/**
 * function string2binary converts the string "true" or "false" to the boolean true false (0 or 1)
 * This is used for the ZLMS Config Settings as these store true or false as string
 * and the api_get_setting('course_create_active_tools') should be 0 or 1 (used for
 * the visibility of the tool)
 * @param string	$variable
 * @author Patrick Cool, patrick.cool@ugent.be
 */
function string2binary($variable) {
	if ($variable == "true") {
		return true;
	} elseif ($variable == "false") {
		return false;
	} else {
		return false;
	}
}

function string2binary2($variable) {
	if ($variable == "true") {
		return 1;
	} elseif ($variable == "false") {
		return 0;
	} else {
		return 0;
	}
}

/**
 * Function to convert a string from the ZLMS language files to a string ready
 * to insert into the database.
 * @author Bart Mollet (bart.mollet@hogent.be)
 * @param string $string The string to convert
 * @return string The string converted to insert into the database
 */
function lang2db($string) {
	$string = str_replace ( "\\'", "'", $string );
	$string = Database::escape_string ( $string );
	return $string;
}
?>