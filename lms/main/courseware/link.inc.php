<?php
$table_courseware = Database::get_course_table ( TABLE_COURSEWARE );
$TABLE_ITEM_PROPERTY = Database::get_course_table ( TABLE_ITEM_PROPERTY );
require_once (api_get_path ( SYS_CODE_PATH ) . "courseware/cw.lib.inc.php");

function change_visibility($id, $visibility = 1) {
	global $_course, $_user;
	global $table_courseware, $TABLE_ITEM_PROPERTY;
	if ($id) {
		$sql="UPDATE $table_courseware SET visibility='".$visibility."' WHERE cc='".api_get_course_code()."' AND  cw_type='link' AND id=".Database::escape($id);
		api_sql_query($sql, __FILE__, __LINE__);
		
		$sqlselect = "UPDATE $TABLE_ITEM_PROPERTY SET visibility=" . Database::escape ( $visibility ) . " WHERE cc='".api_get_course_code()."' AND tool='" . TOOL_LINK . "' and ref='" . $id . "'";
		api_sql_query ( $sqlselect );
		api_item_property_update ( $_course, TOOL_LINK, $id, getgpc("action","G"), $_user ['user_id'] );
		
		Display::display_confirmation_message ( get_lang ( 'VisibilityChanged' ) );
	}
}


function delete_link($id) {
	global $_course, $_user;
	global $table_courseware, $TABLE_ITEM_PROPERTY;
	if ($id) {
		//api_item_property_update ( $_course, TOOL_LINK, $id, "DeleteLink", $_user ['user_id'] );
		$sql = "DELETE FROM $TABLE_ITEM_PROPERTY WHERE tool='" . TOOL_LINK . "' and ref='" . $id . "'";
		api_sql_query ( $sql );
		
		$sql = "DELETE FROM $table_courseware WHERE id=" . Database::escape ( $id );
		api_sql_query ( $sql );
		Display::display_confirmation_message ( get_lang ( 'LinkDeleted' ) );
	}
}