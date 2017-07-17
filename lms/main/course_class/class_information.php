<?php
/*
==============================================================================
班级详细信息
==============================================================================
*/
 
$language_file = array ('class_of_course', 'admin' );
require ('../inc/global.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course_class_manager.lib.php');
api_block_anonymous_users ();
api_protect_course_script ();
if (! isset ( getgpc('id') )) api_not_allowed ();
$table_class = Database::get_course_table ( TABLE_TOOL_COURSE_CLASS );
$class_id = intval(getgpc ( 'id' ));
$class = CourseClassManager::get_class_info ( $class_id );
Display::display_header ( null, FALSE );

$users = CourseClassManager::get_users ( $class_id );
$table_header [] = array (get_lang ( 'LoginName' ), true );
$table_header [] = array (get_lang ( 'FirstName' ), true );
$table_header [] = array (get_lang ( 'OfficialCode' ), true );
$table_header [] = array (get_lang ( 'DeptName' ), true );
$table_header [] = array (get_lang ( 'Email' ), true );
//$table_header[] = array ('', false);
$data = array ();
foreach ( $users as $index => $user ) {
	$row = array ();
	$row [] = $user ['username'];
	$row [] = $user ['firstname'];
	$row [] = $user ['official_code'];
	$row [] = $user ['dept_name'];
	$row [] = $user ['email'];//Display::encrypted_mailto_link ( $user ['email'], $user ['email'] );
	//$row[] = '<a href="user_information.php?user_id='.$user['user_id'] . '">' . Display::return_icon('synthese_view.gif',get_lang("Info")) . '</a>';
	$data [] = $row;
}

//Display::display_sortable_table ( $table_header, $data, array (), array (), array ('id' => $_GET ['id'] ) );
echo Display::display_table ( $table_header, $data );

Display::display_footer ();
