<?php

/*
 ==============================================================================
 课程详细信息
 ==============================================================================
 */
$language_file = array ('registration', 'admin', 'course_home', 'create_course' );
$cidReset = true;
include_once ("../../inc/global.inc.php");
//api_protect_admin_script ();

//require_once (api_get_path ( LIBRARY_PATH ) . 'sortabletable.class.php');
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$code = getgpc ( 'code', 'G' );
if (empty ( $code )) api_not_allowed ();

$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$course = CourseManager::get_course_information ( $code );
$objDept = new DeptManager ();
/*****************************************************************/

//$interbreadcrumb [] = array ("url" => api_get_path ( WEB_ADMIN_PATH ) . 'index.php', "name" => get_lang ( 'PlatformAdmin' ) );
//$interbreadcrumb [] = array ("url" => 'course_list.php', "name" => get_lang ( 'CourseList' ) );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

$tool_name = $course ['title'] . ' (' . $course ['code'] . ')';
Display::display_header ( $tool_name, FALSE );

$myTools ['General'] = array (get_lang ( 'GeneralInfo' ), 'courses.gif' );
$myTools ['CourseUser'] = array (get_lang ( 'Courses4User' ), 'students.gif' );
$tabAction = (isset ( $_GET ['tabAction'] ) ? getgpc('tabAction') : 'General');
$html = '<div id="demo" class="yui-navset">';
$html .= '<ul class="yui-nav">';
foreach ( $myTools as $key => $value ) {
	$strClass = ($tabAction == $key ? 'class="selected"' : '');
	$html .= '<li  ' . $strClass . '><a href="course_information.php?code=' . getgpc('code') . '&tabAction=' . $key . '"><em>' . $value [0] . '</em></a></li>';
}
$html .= '</ul>';
$html .= '<div class="yui-content"><div id="tab1">';
echo $html;

if ($tabAction == "General") {
	echo '<blockquote>';
	/*$table_header [] = array (get_lang ( "Properties" ) );
	$table_header [] = array (get_lang ( "Value" ) );*/
	$table_data [] = array (get_lang ( 'CourseCode' ), $course ['code'] );
	$table_data [] = array (get_lang ( 'CourseTitle' ), $course ['title'] );
	$admin_info = CourseManager::get_course_admin ( $code );
	$table_data [] = array (get_lang ( 'CourseTeachers' ), $admin_info ['firstname'] . "(" . $admin_info ['username'] . ")" );
	$table_data [] = array (get_lang ( 'CourseCredit' ), $course ['credit'] );
	$table_data [] = array (get_lang ( 'CourseCreditHours' ), $course ['credit_hours'] );
	$table_data [] = array (get_lang ( 'CourseTitular' ), $course ['tutor_name'] );
	//$table_data [] = array (get_lang ( 'Course_description' ), $course['description'] );
	echo Display::display_table ( $table_header, $table_data );
	//echo '<br/>', $course ['description'];
	echo '</blockquote>';
}

if ($tabAction == "CourseUser") {
	//	echo '<blockquote>';
	$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	$table_user = Database::get_main_table ( VIEW_USER_DEPT );
	
	$form = new FormValidator ( 'search_simple', 'get', '', '', null, false );
	$form->addElement('hidden','code',$code);
	$form->addElement('hidden','tabAction',$tabAction);
	$renderer = $form->defaultRenderer ();
	$renderer->setElementTemplate ( '<span>{label}{element}</span> ' );
	$form->addElement ( 'text', 'keyword_username', get_lang ( 'FirstName' ), array ('style' => "width:120px", 'class' => 'inputText' ) );
	
	$depts = $objDept->get_sub_dept_ddl2 ( 0, 'array' );
	$form->addElement ( 'select', 'keyword_deptid', get_lang ( 'UserInDept' ), $depts, array ('id' => "dept_id", 'style' => 'height:22px;' ) );
	
	$courseType = array ("" => "", "1" => get_lang ( 'RequiredCourse' ), "0" => get_lang ( "OpticalCourse" ) );
	$form->addElement ( 'select', 'keyword_is_reqcrs', get_lang ( 'CourseStudyType' ), $courseType, array ('id' => "keyword_is_reqcrs", 'style' => 'height:22px;' ) );
	$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );
	
	echo '<div class="actions">';
	$form->display ();
	echo '</div>';
	
	$parameters=array ('code' => $code, 'tabAction' => $tabAction );
	$sql = 'SELECT * FROM ' . $table_course_user . ' cu, ' . $table_user . " u WHERE cu.user_id = u.user_id AND cu.course_code = '" . escape ( $code ) . "' ";
	if (isset ( $_GET ['keyword_username'] ) && $_GET ['keyword_username']) {
		$keyword = trim ( Database::escape_str (getgpc("keyword_username","G") ), TRUE );
		$sql .= " AND  (firstname LIKE '%" . $keyword . "%' OR username LIKE '%".$keyword."%')";
		$parameters['keyword_username']=getgpc('keyword_username','G');
	}
	
	if (isset ( $_GET ['keyword_is_reqcrs'] ) && $_GET ['keyword_is_reqcrs'] != '') {
		$sql .= " AND is_required_course=" . Database::escape ( getgpc('keyword_is_reqcrs','G') );
		$parameters['keyword_is_reqcrs']=getgpc('keyword_is_reqcrs','G');
	}
	
	if (isset ( $_GET ['keyword_deptid'] ) and getgpc ( 'keyword_deptid' ) != "0") {
		$dept_id = intval ( escape ( getgpc ( 'keyword_deptid', 'G' ) ) );
		$dept_sn = $objDept->get_sub_dept_sn ( $dept_id );
		if ($dept_sn) $sql .= " AND dept_sn LIKE '" . $dept_sn . "%'";
		$parameters['keyword_deptid']=getgpc('keyword_deptid','G');
	}
	
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	if (Database::num_rows ( $res ) > 0) {
		$users = array ();
		while ( $obj = Database::fetch_object ( $res ) ) {
			$user = array ();
			$user [] = $obj->username;
			$user [] = $obj->firstname;
			$user [] = $obj->org_name . '/' . $obj->dept_name;
			$user [] = $obj->begin_date . get_lang ( "To" ) . $obj->finish_date;
			$user [] = $obj->creation_time;
			$user [] = ($obj->is_required_course == 1 ? get_lang ( "RequiredCourse" ) : get_lang ( "OpticalCourse" ));
			$users [] = $user;
		}
		$table = new SortableTableFromArray ( $users, 0, NUMBER_PAGE, 'ASC', 'array_course_information_user' );
		$table->set_additional_parameters ( $parameters );
		$table->set_other_tables ( array ('usage_table', 'class_table' ) );
		$i = 0;
		$table->set_header ( $i, get_lang ( 'LoginName' ), true );
		$table->set_header ( ++ $i, get_lang ( 'FirstName' ), true );
		$table->set_header ( ++ $i, get_lang ( 'OrgName' ) . '/' . get_lang ( 'DeptName' ), true );
		$table->set_header ( ++ $i, get_lang ( 'ValidLearningDate' ), true );
		$table->set_header ( ++ $i, get_lang ( 'RegistrationDate' ), true );
		$table->set_header ( ++ $i, get_lang ( 'CourseType' ), true );
		//$table->set_header(5,get_lang('Actions'), false);
		$table->display ();
	} else {
		Display::display_normal_message ( get_lang ( 'NoUsersInCourse' ) );
	}

	//	echo '</blockquote>';
}
echo '</div></div></div>';
Display::display_footer ();
