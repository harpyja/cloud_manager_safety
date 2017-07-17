<?php
if (! defined ( "WEB_QH_PATH" )) define ( "WEB_QH_PATH", api_get_path ( WEB_PATH ) . PORTAL_LAYOUT );
if (! defined ( "SYS_QH_PATH" )) define ( "SYS_QH_PATH", api_get_path ( SYS_PATH ) . PORTAL_LAYOUT );
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'system_announcements.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'image.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'tracking.lib.php');
require_once (api_get_path ( SYS_CODE_PATH ) . 'reporting/cls.track_stat.php');
require_once (api_get_path ( LIBRARY_PATH ) . "pagination.class.php");

function get_user_picture($user_id) {
	global $_user;
	$image = get_user_image ( $user_id );
	$path = "storage/users_picture/{$image}";
	$image_file = ((! empty ( $image ) && file_exists ( api_get_path ( SYS_PATH ) . $path )) ? api_get_path ( WEB_PATH ) . $path : WEB_QH_PATH . 'images/empty_photo_male.png');
	$img_attributes = 'src="' . $image_file . '?rand=' . time () . '" ' . 'alt="' . $_user ['firstname'] . '" ';
	
	/*$image_size = @getimagesize ( $image_file );
	if ($image_size [0] > 88) //limit display width to 150px
		$img_attributes .= 'width="88" ';
	if ($image_size [1] > 88)
		$img_attributes .= 'height="88" ';*/
	$img_attributes .= 'width="90"';
	return $img_attributes;
}

function get_user_image($user_id) {
	$table_user = Database::get_main_table ( TABLE_MAIN_USER );
	$sql = "SELECT picture_uri FROM $table_user WHERE user_id = '$user_id'";
	$image = Database::get_scalar_value ( $sql );
	return $image;
}

function upload_user_image($user_id) {
	$image_repository = api_get_path ( SYS_PATH ) . 'storage/users_picture/';
	$existing_image = get_user_image ( $user_id );
	$file_extension = strtolower ( getFileExt ( $_FILES ['picture'] ['name'] ) );
	if (! file_exists ( $image_repository )) mkpath ( $image_repository );
	if ($existing_image) {
		$picture_filename = $existing_image;
		$old_picture_filename = 'saved_' . date ( 'YmdHis' ) . '_' . uniqid ( '' ) . '_' . $existing_image;
		rename ( $image_repository . $existing_image, $image_repository . $old_picture_filename );
	} else {
		$picture_filename = $user_id . '_' . uniqid ( '' ) . '.' . $file_extension;
	}
	
	//生成缩略图
	$temp = new image ( $_FILES ['picture'] ['tmp_name'] );
	$picture_infos = getimagesize ( $_FILES ['picture'] ['tmp_name'] );
	$new_height = round ( (100 / $picture_infos [0]) * $picture_infos [1] );
	$temp->resize ( 100, $new_height, 0 );
	$type = $picture_infos [2];
	
	switch ($type) {
		case 2 :
			$temp->send_image ( 'JPG', $image_repository . $picture_filename );
			break;
		case 3 :
			$temp->send_image ( 'PNG', $image_repository . $picture_filename );
			break;
		case 1 :
			$temp->send_image ( 'GIF', $image_repository . $picture_filename );
			break;
	}
	
	return $picture_filename;
}

function remove_user_image($user_id) {
	$image_repository = api_get_path ( SYS_PATH ) . 'storage/users_picture/';
	$image = get_user_image ( $user_id );
	if ($image) rename ( $image_repository . $image, $image_repository . 'deleted_' . date ( 'YmdHis' ) . '_' . $image );
}

function download_document($_course, $doc_url) {
	$tbl_document = Database::get_course_table ( TABLE_DOCUMENT );
	if (! DocumentManager::get_document_id ( $_course, $doc_url )) {
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
	
	event_download ( $doc_url );
	$sql = "SELECT title,path FROM $tbl_document WHERE path=" . Database::escape ( $doc_url );
	$sql .= " AND cc='" . api_get_course_code () . "' ";
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$file_row = Database::fetch_array ( $res, 'ASSOC' );
	if ($file_row) {
		$download_name = str_replace ( " ", "", $file_row ['title'] ) . "." . getFileExt ( $file_row ['path'] );
	}
	$full_file_name = api_get_path ( SYS_COURSE_PATH ) . api_get_course_code () . "/document" . $doc_url;
	DocumentManager::file_send_for_download ( $full_file_name, true, $download_name );
	exit ();
}

function get_trial_lp($course_code) {
	$tbl_outline = Database::get_course_table ( TABLE_COURSE_OUTLINKE );
	$sql = "SELECT id,lp_id FROM " . $tbl_outline . " WHERE cc='" . escape ( $course_code ) . "' AND is_trial=1 ORDER BY id";
	$row = Database::fetch_one_row ( $sql, TRUE, __FILE__, __LINE__ );
	return $row;
}

function get_my_course_list($user_id, $sql_where = "", $page_size = NULL, $offset = 0) {
	//	$main_course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
	//	$main_course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	$view_course_user = Database::get_main_table ( VIEW_COURSE_USER );
	
	$sql = "SELECT COUNT(*)	FROM " . $view_course_user . " WHERE user_id = '" . $user_id . "' and (visibility!='') and (is_valid_date!='0')";
	if ($sql_where) $sql .= $sql_where;
	$total_rows = Database::get_scalar_value ( $sql );
	
	/*	$sql = "SELECT course.code, course.title,tutor_name, t2.status,	t2.tutor_id,t2.is_course_admin,t2.creation_time,course.credit,
	t2.is_pass,t2.begin_date,t2.finish_date,course.credit_hours,course.visibility, t2.is_required_course,
	IF(UNIX_TIMESTAMP(t2.begin_date)-UNIX_TIMESTAMP(NOW())<0 AND UNIX_TIMESTAMP(t2.finish_date)-UNIX_TIMESTAMP(NOW())>0,1,0) AS is_valid_date
	FROM  " . $main_course_table . " AS course," . $main_course_user_table . " AS t2
	WHERE course.code = t2.course_code	AND  t2.user_id = '" . $user_id . "' ";*/
	$sql = "SELECT * FROM  " . $view_course_user . " WHERE user_id = '" . $user_id . "' and (visibility!='') and (is_valid_date!='0')";
	if ($sql_where) $sql .= $sql_where;
	$sql .= " ORDER BY category_code,title";
	if (isset ( $page_size )) $sql .= " LIMIT " . (empty ( $offset ) ? 0 : $offset) . "," . $page_size;
	//echo $sql;
	//$rs=api_sql_query($sql,__FILE__,__LINE__);
	

	$course_list = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
	return array ("data_list" => $course_list, "total_rows" => $total_rows );
}

function display_interbreadcrumb($interbreadcrumb = array(), $nameTools = null, $echo = true) {
	$navigation = array ();
	if (is_array ( $interbreadcrumb )) {
		foreach ( $interbreadcrumb as $breadcrumb_step ) {
			//$sep = (strrchr ( $breadcrumb_step ['url'], '?' ) ? '&amp;' : '?');
			$navigation_item ['url'] = $breadcrumb_step ['url'] . $sep; // . api_get_cidreq ();
			$navigation_item ['title'] = $breadcrumb_step ['name'];
			$navigation_item ['target'] = ((isset ( $breadcrumb_step ['target'] ) && ! empty ( $breadcrumb_step ['target'] )) ? $breadcrumb_step ['target'] : '_self');
			$navigation [] = $navigation_item;
		}
	}
	
	if (isset ( $nameTools )) {
		$navigation_item ['url'] = '#';
		$navigation_item ['title'] = $nameTools;
		$navigation [] = $navigation_item;
	}
	
	foreach ( $navigation as $index => $navigation_info ) {
		$navigation [$index] = '<a href="' . $navigation_info ['url'] . '" target="' . $navigation_info ['target'] . '" class="dd2">' . $navigation_info ['title'] . '</a>';
	}
	$html = implode ( ' &gt; ', $navigation );
	if ($echo) {
		echo $html;
	} else {
		return $html;
	}
}

//by changzf on 174 line
function display_tab($this_tab = TAB_HOME_PAGE) {
	global $_configuration;
	if (empty ( $this_tab )) $this_tab = TAB_HOME_PAGE;
	$html = '<div class="body_banner_up">';
	$html .= '<a href="' . WEB_QH_PATH . 'index.php" target="_top" class="label dt2' . ($this_tab == TAB_HOME_PAGE ? " foucs" : '') . '"
	style="#margin-left: 50px;margin-left: 400px; _margin-left: 25px;">首页</a><img src="' . WEB_QH_PATH . 'images/body_banner_up_pic1.jpg" style="float: left; margin: 10px 3px;" />';

 $html .= '<a href="' . WEB_QH_PATH . 'syllabus.php" target="_top" class="label dt2' . ($this_tab == TAB_SYLLABUS ? " foucs" : '') . '">课程表</a>
 <img src="' . WEB_QH_PATH . 'images/body_banner_up_pic1.jpg" style="float: left; margin: 10px 3px;" />';
	if (api_get_setting ( 'enable_modules', 'course_center' ) == 'true') {
		$html .= '<a href="' . WEB_QH_PATH . 'course_catalog.php" target="_top" class="label dt2' . ($this_tab == TAB_COURSE_CENTER ? " foucs" : '') . '">选课中心</a>
	<img src="' . WEB_QH_PATH . 'images/body_banner_up_pic1.jpg" style="float: left; margin: 10px 3px;" />';
	}
	
	$html .= '<a href="' . WEB_QH_PATH . 'learning_center.php" target="_top" class="label dt2' . ($this_tab == TAB_LEARNING_CENTER ? " foucs" : '') . '">学习中心</a>
	<img src="' . WEB_QH_PATH . 'images/body_banner_up_pic1.jpg" style="float: left; margin: 10px 3px;" />';
	
	if (api_get_setting ( 'enable_modules', 'exam_center' ) == 'true') {
		$html .= '<a href="' . WEB_QH_PATH . 'exam_center.php" target="_top"	class="label dt2' . ($this_tab == TAB_EXAM_CENTER ? " foucs" : '') . '">考试中心</a>
				<img src="' . WEB_QH_PATH . 'images/body_banner_up_pic1.jpg" style="float: left; margin: 10px 3px;" /> ';
	}
	
	if (api_get_setting ( 'enable_modules', 'survey_center' ) == 'true' && $_configuration ['enable_module_survey']) {
		$html .= '<a href="' . WEB_QH_PATH . 'survey.php" target="_top" class="label dt2' . ($this_tab == TAB_SURVEY_CENTER ? " foucs" : '') . '">' . get_lang ( 'Survey' ) . '</a>
		<img src="' . WEB_QH_PATH . '/images/body_banner_up_pic1.jpg"style="float: left; margin: 10px 3px;" />';
	}
	
	$html .= '<a href="' . WEB_QH_PATH . 'learning_progress.php" target="_top"	class="label dt2' . ($this_tab == TAB_LEARN_PROGRESS ? " foucs" : '') . '">学习档案</a>';


    $html .= '<img src="' . WEB_QH_PATH . 'images/body_banner_up_pic1.jpg" style="float: left; margin: 10px 3px;" />
    <a href="'.URL_APPEND.'iou/html/labs.php" target="_top" class="label dt2' . ($this_tab == IOU ? " foucs" : '') . '">路由实训</a>';

	//<img src="images/body_banner_up_pic1.jpg" style="float: left; margin: 10px 3px;" />' ;
	if (api_is_admin ())  {

			//by changzf comment 207-208 line 
     //  $html .= '<span style = "float:right" ><a class="helpex dd2" id="confirmExit" target="_top"
		//	href="javascript:void(0);">['.(get_lang("ExitSys")).']</a>&nbsp;|&nbsp;<a  href="/main/admin/index.php" target="_blank">后台管理&nbsp;&nbsp;</a></span>';

    }

	/*$html .= '<a href="bulletin.php" target="_top"	class="label dt2' . ($this_tab == TAB_ANNO_CENTER ? " foucs" : '') . '">公告消息</a>
<img src="images/body_banner_up_pic1.jpg" style="float: left; margin: 10px 3px;" /> ';
	
	$html .= '<a href="user_center.php" target="_top"
	class="label dt2' . ($this_tab == TAB_USER_CENTER ? " foucs" : '') . '">我的信息</a>';*/
	
	$html .= '</div>';
	$html .= '<div style="clear:both;"></div>';
	
	//echo $html;
}
