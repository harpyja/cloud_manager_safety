<?php

/**
 ==============================================================================
 * @package zllms.admin
 ==============================================================================
 */

$language_file = array ('class_of_course' );

require ('../inc/global.inc.php');
api_block_anonymous_users ();
api_protect_course_script ();
require_once (api_get_path ( LIBRARY_PATH ) . 'course_class_manager.lib.php');

$table_class = Database::get_course_table ( TABLE_TOOL_COURSE_CLASS );
$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );

$is_allowed_edit = api_is_allowed_to_edit ();

$tool_name = get_lang ( 'ClassList' );
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( $tool_name, FALSE );

if (isset ( getgpc('action') )) {
	switch (getgpc('action')) {
		case 'delete_class' :
			$res_code = CourseClassManager::delete_class ( intval(getgpc('class_id')) );
			if ($res_code == 1) {
				Display::display_normal_message ( get_lang ( 'ClassDeleted' ) );
			}
			if ($res_code == - 1) {
				Display::display_normal_message ( get_lang ( 'ClassDeletedFailedBecauseContainingStud' ) );
			}
			break;
		case 'show_message' :
			Display::display_normal_message ( stripslashes ( urldecode ( getgpc('message') ) ) );
			break;
	}
}

if ($is_allowed_edit) {
	echo '<div class="actions">';
	echo '&nbsp;' . link_button ( 'add_user_big.gif', 'AddClasses', 'class_add.php', '40%', '60%' );
	
	echo '<div style="float:right;margin-right:20px">'; //str_repeat('&nbsp;',8).
	echo get_lang ( 'NoCategoryClass' ) . ": " . Database::get_scalar_value ( "SELECT count(*) FROM " . $table_course_user . " WHERE status=" . STUDENT . " AND course_code='" . api_get_course_code () . "' AND class_id=0" );
	echo '</div>';
	echo '</div>';

}
$table_header [] = array (get_lang ( 'ClassName' ), true );
$table_header [] = array (get_lang ( 'NumberOfUsers' ), true, null, array ('style' => 'width:100px' ) );
$table_header [] = array (get_lang ( 'Actions' ), false, null, array ('style' => 'width:100px' ) );
$sql = "SELECT * FROM " . $table_class . " WHERE cc='" . api_get_course_code () . "' ";
$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
while ( $data = Database::fetch_array ( $rs, 'ASSOC' ) ) {
	$row = array ();
	$row [] = $data ['name'];
	
	$users = CourseClassManager::get_users ( $data ['id'] );
	$row [] = ($users && is_array ( $users ) ? count ( $users ) : 0);
	
	$row [] = modify_filter ( $data ['id'] );
	$table_data [] = $row;
}
echo Display::display_table ( $table_header, $table_data );

function modify_filter($class_id) {
	$result = link_button ( 'synthese_view.gif', 'Info', 'class_information.php?id=' . $class_id, '90%','90%', FALSE );
	if (api_is_allowed_to_edit ()) { //教师视图
		$result .= '&nbsp;' . link_button ( 'edit.gif', 'Edit', 'class_edit.php?idclass=' . $class_id, '40%', '60%', false );
		$result .= '&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'class_list.php?action=delete_class&amp;class_id=' . $class_id );
		$result .= '&nbsp;' . link_button ( 'add_multiple_users.gif', 'AddUsersToAClass', 'subscribe_user2class.php?idclass=' . $class_id, '70%', '80%', false );
	}
	return $result;
}

Display::display_footer ();
