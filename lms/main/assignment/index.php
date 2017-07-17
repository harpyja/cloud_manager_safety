<?php
/*
 ==============================================================================
 ==============================================================================
 */

$language_file = array ('assignment', 'admin' );
require ('../inc/global.inc.php');
require_once ('assignment.lib.php');
api_block_anonymous_users ();
api_protect_course_admin_script ();
$is_allowed_to_edit = api_is_allowed_to_edit ();
if (! $is_allowed_to_edit) api_not_allowed ();
$user_id = api_get_user_id ();
$course_code = api_get_course_code ();

$id = intval (getgpc("id") );
$action = getgpc ( 'action' );
$delete = getgpc ( 'delete' );
$display_tool_options = getgpc ( 'display_tool_options' );
$display_assignment_form = getgpc ( 'display_assignment_form' );
$base_work_dir = api_get_path ( SYS_COURSE_PATH ) . $course_code . '/assignment';
$http_www = api_get_path ( WEB_COURSE_PATH ) . $course_code . '/assignment';
$redirect_url = URL_APPEND . 'main/assignment/index.php?cidReq=' . $course_code;
$htmlHeadXtra [] = Display::display_thickbox ();

if (isset ( $_GET ['action'] )) {
	switch ($_GET ['action']) {
		case 'publish' :
			$message = publish_unpublish_assignment ( 'publish', intval(getgpc("id","G")) );
			//Display::display_msgbox ( $message, $redirect_url );
			api_redirect ( $redirect_url );
			break;
		case 'unpublished' :
			$message = publish_unpublish_assignment ( 'unpublish', intval(getgpc("id","G")) );
			//Display::display_msgbox ( $message, $redirect_url );
			api_redirect ( $redirect_url );
			break;
		case 'assign_del' :
			if (assignment_del ( $_GET ['id'] )) {
				remove_dir ( $sys_work_dir . "/" . intval(getgpc("id","G") ));
				$sql = "DELETE FROM " . $table_attachment . " WHERE type='ASSIGNMENT_MAIN' AND ref_id=" . Database::escape (getgpc("id","G") );
				api_sql_query ( $sql, __FILE__, __LINE__ );
			
	//Display::display_msgbox ( get_lang ( 'AssignmentDeleted' ), $redirect_url );
			} else {
				//Display::display_msgbox ( get_lang ( 'CannotDeleteAssignment' ), $redirect_url );
			}
			api_redirect ( $redirect_url );
			break;
	}
}
Display::display_header ( null, false );

echo display_action_links ();

display_teacher_assignment_list ();

Display::display_footer ();
