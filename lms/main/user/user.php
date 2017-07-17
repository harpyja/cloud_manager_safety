<?php                 
$language_file = array ('registration', 'userInfo', 'admin', 'class_of_course' );
require_once ("../inc/global.inc.php");
api_protect_course_script ();
$is_allowed_edit = api_is_allowed_to_edit ();
if (! $is_allowed_edit) api_not_allowed ();

require_once (api_get_path ( LIBRARY_PATH ) . "export.lib.inc.php");
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');

$course_code = api_get_course_code ();
$course = CourseManager::get_course_information ( $course_code );

$table_user = Database::get_main_table ( TABLE_MAIN_USER );
$view_user_dept = Database::get_main_table ( VIEW_USER_DEPT );
$table_class = Database::get_course_table ( TABLE_TOOL_COURSE_CLASS );
$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
$course_admin = CourseManager::get_course_admin ( api_get_course_code () );
$course_admin_id = $course_admin ['user_id'];

$htmlHeadXtra [] = Display::display_thickbox ();

if ($is_allowed_edit) {
	if (isset ( $_POST ['action'] )) {
		switch ($_POST ['action']) {
			case 'unsubscribe' : //批量注销课程用户
				if (! empty ( $course ["is_subscribe_enabled"] ) or api_is_platform_admin ()) {
					$user_ids = array_diff ( $_POST ['user'], array (api_get_user_id () ), array ($course_admin_id ) );
					if (count ( $user_ids ) > 0) {
						CourseManager::unsubscribe_user ( $user_ids, api_get_course_code () );
						$message = get_lang ( 'UsersUnsubscribed' );
					}
				} else {
					$message = get_lang ( "YouAreNotAllowedToUnsubscribeUser" );
				}
				break;
			case 'batch_pass_course' : //批量评定通过课程
				$user_ids = array_diff ( $_POST ['user'], array (api_get_user_id () ), array ($course_admin_id ) );
				if (count ( $user_ids ) > 0) {
					$sql = "UPDATE $table_course_user set is_pass=1 WHERE course_code='" . api_get_course_code () . "' AND " . Database::create_in ( $user_ids, "user_id" );
					api_sql_query ( $sql, __FILE__, __LINE__ );
					$message = get_lang ( 'OperationSuccess' );
				}
				break;
			
			case 'batch_not_pass_course' :
				$user_ids = array_diff ( $_POST ['user'], array (api_get_user_id () ), array ($course_admin_id ) );
				if (count ( $user_ids ) > 0) {
					$sql = "UPDATE $table_course_user set is_pass=0 WHERE course_code='" . api_get_course_code () . "' AND " . Database::create_in ( $user_ids, "user_id" );
					api_sql_query ( $sql, __FILE__, __LINE__ );
					$message = get_lang ( 'OperationSuccess' );
				}
				break;
		}
	}
}

if ($is_allowed_edit) {
	if (isset ( $_GET ['action'] )) {
		switch ($_GET ['action']) {
			case 'export' :
				$export_encoding = "GB2312";
				$sql = "SELECT u.user_id as UserId,u.official_code as OfficialCode,u.firstname as FirstName,
					u.email as Email FROM $table_user u, $table_course_user cu
					WHERE cu.user_id = u.user_id AND cu.course_code = '" . api_get_course_code () . "' ORDER BY firstname ASC";
				
				$data [] = array ('UserId', 'OfficialCode', 'FirstName', 'Email' );
				$filename = 'ExportUsers_' . $course ['sysCode'] . '_' . date ( 'YmdHis' );
				$users = api_sql_query ( $sql, __FILE__, __LINE__ );
				while ( $user = Database::fetch_array ( $users, 'ASSOC' ) ) {
					$user ['FirstName'] = mb_convert_encoding ( $user ['FirstName'], $export_encoding, SYSTEM_CHARSET );
					$data [] = $user;
				}
				switch ($_GET ['type']) {
					case 'csv' :
						Export::export_table_csv ( $data, $filename );
					case 'xls' :
						Export::export_table_xls ( $data, $filename );
				}
		
		}
	}
} // end if allowed to edit


if ($is_allowed_edit) {
	if ($_GET ['unregister']) {
		if (! empty ( $course ["is_subscribe_enabled"] ) or api_is_platform_admin ()) {
			if (isset ( $_GET ['user_id'] ) && is_numeric ( $_GET ['user_id'] ) && $_GET ['user_id'] != $_user ['user_id']) {
				//课程管理员不允许注销
				if (intval ( $_GET ['user_id'] ) != $course_admin_id) {
					CourseManager::unsubscribe_user ( $_GET ['user_id'], $_SESSION ['_course'] ['sysCode'] );
					$message = get_lang ( 'UserUnsubscribed' );
				} else {
					$message = get_lang ( 'CourseAdminNotAllowedUnsubscribe' );
				}
			}
		} else {
			$message = get_lang ( "YouAreNotAllowedToUnsubscribeUser" );
		}
	}
}

Display::display_header ( NULL, FALSE );

if (isset ( $message )) Display::display_normal_message ( $message );

$is_allowed_to_track = api_is_allowed_to_edit () && $_configuration ['tracking_enabled'];

$form = new FormValidator ( 'search_user', 'get', '', '', null, false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$form->add_textfield ( 'keyword', '', false, '  style="width:160px" class="inputText"' );

$sql = "SELECT id,name FROM " . $table_class . " WHERE cc='" . escape ( $course_code ) . "' ";
$all_course_class = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
$all_course_class = array_insert_first ( $all_course_class, array ('' => '所有课程班级' ) );
$form->addElement ( 'select', 'class_id', null, $all_course_class );
$form->addElement ( 'submit', 'submit', get_lang ( 'SearchButton' ), 'class="inputSubmit"' );

//右上角课程相关链接
//echo '<div class="actions">';
//echo '<span style="float:right; padding-top:5px;">';
//echo link_button ( 'excel.gif', 'ExportUserListXMLCSV', 'user_export.php', '70%', '70%' );
if (! empty ( $course ["is_subscribe_enabled"] )) { // or api_is_platform_admin ()) {
	//echo '&nbsp;&nbsp;', link_button ( 'add_user_big.gif', 'SubscribeUserToCourse', 'subscribe_user.php', 380, 900 );
//	echo '&nbsp;&nbsp;', link_button ( 'add_user_big.gif', 'SubscribeUserToCourse', 'add_user2course.php?' . api_get_cidreq (), '70%', '70%' );
}
//echo '&nbsp;' . link_button ( 'edit_group.gif', 'CourseClassManagement', "../course_class/class_list.php?" . api_get_cidreq (), '90%', '80%', TRUE, TRUE );
if ($course ['is_audit_enabled'] == 1) { //只允许课程管理员审核时
//	echo '&nbsp;&nbsp;&nbsp;&nbsp;<a href="audit_subscribe_list.php">' . Display::return_icon ( 'add_user_big.gif', get_lang ( "AuditSubscribeToCourseUserList" ) ) . get_lang ( "AuditSubscribeToCourseUserList" ) . '</a>';
}
//echo '</span>';
//$form->display (); //查询表单
//echo '</div>';

function get_number_of_users() {
	$user_table = Database::get_main_table ( TABLE_MAIN_USER );
	$course_user_table = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	global $course_code;
	$sql = "SELECT COUNT(u.user_id) AS number_of_users FROM $user_table u,$course_user_table cu WHERE u.user_id = cu.user_id and course_code='" . $course_code . "' AND is_course_admin<>1";
	
	if (is_not_blank ( $_GET ['keyword'] )) {
		$keyword = trim ( Database::escape_str ( $_GET ['keyword'], TRUE ) );
		$sql .= " AND (firstname LIKE '%" . $keyword . "%' OR username LIKE '%" . $keyword . "%')";
	}
	if (is_not_blank ( $_GET ['class_id'] )) {
		$sql .= " AND class_id=" . Database::escape ( getgpc ( 'class_id', 'G' ) );
	}
	return Database::get_scalar_value ( $sql );
}

function sort_users($a, $b) {
	$a = trim ( strtolower ( $a [$_GET ['users_column']] ) );
	$b = trim ( strtolower ( $b [$_GET ['users_column']] ) );
	if ($_GET ['users_direction'] == 'DESC') return strcmp ( $b, $a );
	else return strcmp ( $a, $b );
}

function get_user_data($from, $number_of_items, $column, $direction) {
	global $table_course_user, $view_user_dept;
	$a_users = array ();
	$course_code = api_get_course_code ();
	$sql_where = " AND is_course_admin<>1 ";
	if (is_not_blank ( $_GET ['keyword'] )) {
		$keyword = trim ( Database::escape_str ( $_GET ['keyword'], TRUE ) );
		$sql_where .= " AND (firstname LIKE '%" . $keyword . "%' OR username LIKE '%" . $keyword . "%')";
	}
	if (is_not_blank ( $_GET ['class_id'] )) {
		$sql_where .= " AND class_id=" . Database::escape ( getgpc ( 'class_id', 'G' ) );
	}
	//$a_course_users = CourseManager::get_user_list_from_course_code ( $course_code, $sql_where );
	$sql = "SELECT t.*,DATE_FORMAT(creation_time,'%Y-%m-%d') AS reg_date,u.status as user_status,u.username,u.firstname,u.official_code,u.dept_name
		FROM $table_course_user t, $view_user_dept as u WHERE t.`course_code` = '" . escape ( $course_code ) . "' AND t.user_id=u.user_id " . $sql_where;
	$a_course_users = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
	$all_user_class_info = CourseManager::get_courseclass_rel_user ( $course_code );
	$is_allow_to_edit = api_is_allowed_to_edit ();
	foreach ( $a_course_users as $user_id => $o_course_user ) {
		$user_id = $o_course_user ['user_id'];
		$course_class_info = $all_user_class_info [$user_id];
		$temp = array ();
		$temp [] = $user_id;
		$temp [] = $o_course_user ['username'];
		$temp [] = $o_course_user ['firstname'];
		$temp [] = $o_course_user ['official_code'];
		$temp [] = $o_course_user ['dept_name'];
		$temp [] = $course_class_info ['name'];
		$temp [] = $o_course_user ['begin_date'] . ' ~ ' . $o_course_user ['finish_date'];
		$temp [] = $o_course_user ['reg_date'];
		
		//$temp [] = (is_equal ( $o_course_user ['is_pass'], LEARNING_STATE_PASSED ) ? Display::return_icon ( 'right.gif' ) : Display::return_icon ( 'wrong.gif' )) . "&nbsp;" . get_learning_status ( $o_course_user ['is_pass'] );
		$temp [] = get_learning_status ( $o_course_user ['is_pass'] );
		$temp [] = $user_id;
		$a_users [$user_id] = $temp;
	
	}
	usort ( $a_users, 'sort_users' );
	//return $a_users;
	return array_slice ( $a_users, $from, $number_of_items );
}

function modify_filter($user_id) {
	global $_user, $_course, $is_allowed_to_track;
	global $course_admin_id, $course;
	$result = "<div>";
	//$result .= '&nbsp;<a href="userInfo.php?uInfo=' . $user_id . '&height=360&width=750&TB_iframe=true&KeepThis=true&modal=true" class="thickbox">' . Display::return_icon ( 'user_info.gif', get_lang ( 'Info' ) ) . "</a>&nbsp;";
	if (api_is_allowed_to_edit ( true )) {
		//编辑
		$result .= '&nbsp;' . link_button ( 'edit.gif', 'Edit', 'userInfo.php?editMainUserInfo=' . $user_id, '70%', '80%', false );
		//注销
		if (($user_id != $_user ['user_id'] and $user_id != $course_admin_id && ! empty ( $course ["is_subscribe_enabled"] )) or api_is_platform_admin ()) {
			$href = $_SERVER ['PHP_SELF'] . '?unregister=yes&amp;user_id=' . $user_id . '&amp;' . $sort_params;
			$result .= '&nbsp;&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Unreg', $href );
		}
	}
	
	//V2.2
	//$result .= '&nbsp;<a class="thickbox" href="../reporting/user_learning_stat.php?user_id=' . $user_id . '&amp;course_code=' . $_course ['id'] . '&height=420&width=970&TB_iframe=true&KeepThis=true&modal=true">' . Display::return_icon ( 'statistics.gif', get_lang ( 'Tracking' ) ) . '</a>&nbsp;';
	$result .= "</div>";
	return $result;
}

$table = new SortableTable ( 'users', 'get_number_of_users', 'get_user_data' );
if (is_not_blank ( $_GET ['keyword'] )) $parameters ['keyword'] = getgpc ( 'keyword', 'G' );
if (is_not_blank ( $_GET ['class_id'] )) $parameters ['class_id'] = getgpc ( 'class_id', 'G' );
$table->set_additional_parameters ( $parameters );

$idx = 0;
$table->set_header ( $idx ++, '', false );
$table->set_header ( $idx ++, get_lang ( 'LoginName' ) );
$table->set_header ( $idx ++, get_lang ( 'FirstName' ) );
$table->set_header ( $idx ++, get_lang ( 'OfficialCode' ) );
$table->set_header ( $idx ++, get_lang ( 'InDept' ) );
$table->set_header ( $idx ++, get_lang ( 'Class_of_course' ), TRUE );
$table->set_header ( $idx ++, get_lang ( 'Duration' ) );
$table->set_header ( $idx ++, get_lang ( 'RegistrationTime' ), TRUE );
$table->set_header ( $idx ++, get_lang ( 'LearningState' ) );
$table->set_header ( $idx ++, get_lang ( 'Actions' ), false );
$table->set_column_filter ( 9, 'modify_filter' );
$actions = array ('batch_pass_course' => get_lang ( "BatchPassCourse" ), 'batch_not_pass_course' => get_lang ( "BatchNotPassCourse" ) );
if (! empty ( $course ["is_subscribe_enabled"] ) or api_is_platform_admin ()) {
	$actions ["unsubscribe"] = get_lang ( 'Unreg' );
}

$table->set_form_actions ( $actions, 'user' );

$table->display (); //列表


Display::display_footer ();
