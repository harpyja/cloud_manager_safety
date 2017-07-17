<?php
/*
 ==============================================================================
 注册用户（学生，老师）到本门课程
 ==============================================================================
 */

$language_file = array ('registration', 'admin' );
include ("../inc/global.inc.php");
$this_section = SECTION_COURSES;

require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$is_allowed_edit = api_is_allowed_to_edit ();
$currentCourseID = $_course ['sysCode'];
$course = CourseManager::get_course_information ( $currentCourseID );
if (! $is_allowed_edit or ($course ["is_subscribe_enabled"] == 0)) {
	api_not_allowed ();
}

$table_user = Database::get_main_table ( TABLE_MAIN_USER );

$tool_name = get_lang ( "SubscribeUser2ThisCourse" );
Display::display_header ( $tool_name, FALSE );

//部门数据
$deptObj = new DeptManager ();
$dept_options [0] = get_lang ( 'All' );

$dept_tree = $deptObj->get_sub_dept_ddl ( $_SESSION ['_user'] ['org_id'] );
foreach ( $dept_tree as $dept_info ) {
	$dept_options [$dept_info ['id']] = str_repeat ( '&nbsp;', 2 * ($dept_info ['level']) ) . $dept_info ['dept_name'] . ($dept_info ['dept_no'] ? ' - ' . $dept_info ['dept_no'] : "");
}

$g_register=  getgpc('register');
if (isset ( $g_register )) {
	CourseManager::subscribe_user ( intval(getgpc ('user_id','G')), $_course ['sysCode'] );
}
$g_action=  getgpc('action',"G");
if (isset ( $g_action )) {
	switch ($g_action) {
		case 'subscribe' :
			if (is_array ( getgpc('user','P') )) {
				foreach ( getgpc('user','P') as $index => $user_id ) {
					$sql = "SELECT status FROM " . $table_user . " WHERE user_id='" . escape ( intval($user_id )) . "'";
					CourseManager::subscribe_user ( $user_id, $_course ['sysCode'], Database::get_scalar_value ( $sql ) );
				}
			}
			break;
	}
}

function get_sqlwhere() {
	global $deptObj;
	$sql_where = "";
	
	$sql_where .= " AND u.status=" . STUDENT;
	
	if (is_not_blank ( $_REQUEST ['keyword'] )) {
		$keyword = Database::escape_string ( getgpc ('keyword','G') );
		$sql_where .= " AND (firstname LIKE '%" . $keyword . "%'  OR username LIKE '%" . $keyword . "%'  OR official_code LIKE '%" . $keyword . "%')";
	}
	if (isset ( $_REQUEST ['keyword_deptid'] ) && ! is_equal ( getgpc('keyword_deptid'), '0' )) {
		$dept_id = intval ( Database::escape_string ( getgpc ( 'keyword_deptid', 'G' ) ) );
		$dept_sn = $deptObj->get_sub_dept_sn ( $dept_id );
		if ($dept_sn) $sql_where .= " AND dept_sn LIKE '" . $dept_sn . "%'";
	}
	
	if (! api_is_platform_admin ()) {
		$restrict_org_id = $_SESSION ['_user'] ['org_id'];
		$sql_where .= " AND org_id='" . $restrict_org_id . "'";
	}
	return $sql_where;
}

/**
 * * Get the users to display on the current page.
 */
function get_number_of_users() {
	global $_configuration;
	$user_table = Database::get_main_table ( VIEW_USER_DEPT );
	$course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	
	$sql = "SELECT	COUNT(*) FROM $user_table u	WHERE u.user_id NOT IN (
			SELECT cu.user_id FROM $course_user_table cu WHERE cu.course_code='" . api_get_course_code () . "')
			AND u.username NOT " . Database::create_in ( $_configuration ['default_administrator_name'] );
	
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= $sql_where;
	
	return Database::get_scalar_value ( $sql );
}

/**
 * Get the users to display on the current page.
 */
function get_user_data($from, $number_of_items, $column, $direction) {
	global $_configuration;
	global $deptObj;
	$user_table = Database::get_main_table ( VIEW_USER_DEPT );
	$course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	
	$sql = "SELECT	u.user_id AS col0,   u.username AS col1,  u.firstname AS col2, u.official_code   AS col3,
					 u.email 	AS col4,   u.dept_id   AS col5,   u.user_id   AS col6
				FROM $user_table u	WHERE u.user_id NOT IN (SELECT cu.user_id FROM $course_user_table cu
				WHERE cu.course_code='" . api_get_course_code () . "'
				) AND u.username<>'" . $_configuration ['default_administrator_name'] . "'";
	
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= $sql_where;
	
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	//echo $sql;
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$users = array ();
	while ( $user = Database::fetch_row ( $res ) ) {
		$deptObj->dept_path = "";
		$user [5] = $deptObj->get_dept_path ( $user [5], FALSE );
		$users [] = $user;
	}
	return $users;
}

/**
 * Returns a mailto-link
 * @param string $email An email-address
 * @return string HTML-code with a mailto-link
 */
function email_filter($email) {
	return Display::encrypted_mailto_link ( $email, $email );
}

/**
 * Build the reg-column of the table
 * @param int $user_id The user id
 * @return string Some HTML-code
 */
function reg_filter($user_id) {
    $g_type=  getgpc('type');
	if (isset ( $g_type ) && $g_type == 'teacher')
		$type = 'teacher';
	else $type = 'student';
	//$result = "<a href=\"".$_SERVER['PHP_SELF']."?register=yes&amp;type=".$type."&amp;user_id=".$user_id."\">".get_lang("reg")."</a>";
	$result = "<a href=\"" . $_SERVER ['PHP_SELF'] . "?register=yes&amp;type=" . $type . "&amp;user_id=" . $user_id . "\">" . Display::return_icon ( 'enroll.gif', get_lang ( 'reg' ), array ('style' => 'vertical-align: middle;' ) ) . "</a>";
	return $result;
}

// Build search-form
$form = new FormValidator ( 'search_user', 'get', '', '', null, false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" ) . "/" . get_lang ( "OfficialCode" );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:20%", 'class' => 'inputText', 'title' => $keyword_tip ) );

/*$form->add_textfield('keyword', '', false, array('style'=>"width:20%;font-style:italic",'class'=>'inputText',
 'value'=>$keyword_tip,'onfocus'=>"javascript:this.value='';",'onblur'=>'javascript:if(this.value=="")this.value="'.$keyword_tip.'";'));*/
$form->addElement ( 'select', 'keyword_deptid', get_lang ( 'UserInDept' ), $dept_options, array ('title' => get_lang ( 'UserInDept' ) ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Filter' ), 'class="inputSubmit"' );

$form->setDefaults ( $defaults );

// Build table
$table = new SortableTable ( 'subscribe_users', 'get_number_of_users', 'get_user_data', 2, NUMBER_PAGE );
$parameters ['keyword'] = getgpc ( 'keyword', 'G' );
$parameters ['keyword_deptid'] = getgpc ( 'keyword_deptid', 'G' );
$parameters ['type'] = getgpc ( 'type', 'G' );
$table->set_additional_parameters ( $parameters );
$col = 0;
$table->set_header ( $col ++, '', false );
$table->set_header ( $col ++, get_lang ( 'LoginName' ) );
$table->set_header ( $col ++, get_lang ( 'FirstName' ) );
$table->set_header ( $col ++, get_lang ( 'OfficialCode' ) );
$table->set_header ( $col ++, get_lang ( 'JobTitle' ) );
$table->set_header ( $col ++, get_lang ( 'Email' ) );
$table->set_header ( $col ++, get_lang ( 'UserInDept' ) );
$table->set_header ( $col ++, get_lang ( "Actions" ), false );
$table->set_column_filter ( $col - 1, 'email_filter' );
$table->set_column_filter ( $col - 1, 'reg_filter' );
$table->set_form_actions ( array ('subscribe' => get_lang ( 'reg' ) ), 'user' );
$table->set_dispaly_style_navigation_bar ( 'top' );

$form->display ();
$table->display ();

Display::display_footer ();
?>