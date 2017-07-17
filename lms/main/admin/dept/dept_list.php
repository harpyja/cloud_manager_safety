<?php
$language_file = array ('admin' );
$cidReset = true;
require_once ('../../inc/global.inc.php');
$this_section = SECTION_PLATFORM_ADMIN;

api_protect_admin_script ();

require_once (api_get_path ( LIBRARY_PATH ) . "export.lib.inc.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( SYS_CODE_PATH ) . 'admin/admin.lib.inc.php');

$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
$table_user = Database::get_main_table ( TABLE_MAIN_USER );

$pid = isset ( $_GET ['pid'] ) ? intval(getgpc('pid')) : '0';
$org_id = intval(getgpc ( "org_id" ));

$dept_tree = array ();
$deptObj = new DeptManager ();
$sql = "SELECT pid FROM " . $table_dept . " WHERE id='" . Database::escape_string ( $pid ) . "'";
$parent_id = Database::get_scalar_value ( $sql );

if (isset ( $_GET ['action'] )) {
	switch (getgpc("action","G")) {
		case 'show_message' :
			if (isset ( $_GET ['message'] )) Display::display_normal_message ( stripslashes ( urldecode (getgpc("message","G") ) ) );
			break;
		case 'delete_dept' :
			$res_del = $deptObj->del_dept (intval(getgpc ( 'id', 'G' )) );
			switch ($res_del) {
				case - 1 :
					Display::display_normal_message ( get_lang ( 'DelDenyBecauseHaveSubDepts' ) );
					break;
				case - 2 :
					Display::display_normal_message ( get_lang ( 'DelDenyBecauseHaveUsers' ) );
					break;
				case 1 :
					$log_msg = get_lang ( 'DelDeptInfo' ) . "id=" . intval(getgpc ( 'id', 'G' ));
					api_logging ( $log_msg, 'DEPT' );
					//Display::display_normal_message ( get_lang ( 'DeptDeleteSuccess' ) );
					api_redirect ( "dept_list.php?pid=" . $pid . "&message=" . get_lang ( 'DeptDeleteSuccess' ) );
					echo '<script>parent.Tree.location.reload();</script>';
					break;
			}
			break;
		case 'lock' :
			$message = DeptManager::lock_unlock_dept ( 'lock', intval(getgpc('dept_id')) );
			$log_msg = $message . "id=" . intval(getgpc ( 'id', 'G' ));
			api_logging ( $log_msg, 'DEPT' );
			Display::display_normal_message ( $message );
			break;
		case 'unlock' :
			$message = DeptManager::lock_unlock_dept ( 'unlock', intval(getgpc('dept_id')) );
			Display::display_normal_message ( $message );
			break;
		case 'export' :
			$export_encoding = api_get_setting ( 'platform_charset' );
			$sql = "SELECT * FROM " . $table_dept;
			$data [] = array ('id', 'pid', 'dept_no', 'dept_sn', 'dept_name', 'dept_desc', 'enabled', 'dept_pos' );
			$filename = 'ExportDepts_' . $_user ['username'] . '_' . date ( 'YmdHi' );
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			while ( $dept = Database::fetch_array ( $res, 'ASSOC' ) ) {
				$dept ['dept_name'] = mb_convert_encoding ( $dept ['dept_name'], $export_encoding, SYSTEM_CHARSET );
				$dept ['dept_desc'] = mb_convert_encoding ( $dept ['dept_desc'], $export_encoding, SYSTEM_CHARSET );
				$data [] = $dept;
			}
			switch (getgpc ( 'type', 'G' )) {
				case 'csv' :
					Export::export_table_csv ( $data, $filename );
				case 'xls' :
					Export::export_table_xls ( $data, $filename );
			}
			break;
		case 'moveUp' :
			if (moveNodeUp ( getgpc ( 'id' ), getgpc ( 'dept_pos' ), getgpc ( 'pid' ) )) {
				//echo '<script>refresh_tree();</script>';
				if ($pid == '1')
					api_redirect ( "dept_list.php?refresh=1&message=" . get_lang ( 'DeptMoveUpSuccess' ) );
				else api_redirect ( "dept_list.php?pid=" . $pid . "&refresh=1&message=" . get_lang ( 'DeptMoveUpSuccess' ) );
			
	//echo '<script>parent.Tree.location.reload();</script>';
			}
			break;
		case 'moveDown' :
			if (moveNodeDown ( getgpc ( 'id' ), getgpc ( 'dept_pos' ), getgpc ( 'pid' ) )) {
				//echo '<script>refresh_tree();</script>';
				if ($pid == '1')
					api_redirect ( "dept_list.php?refresh=1&message=" . get_lang ( 'DeptMoveDownSuccess' ) );
				else api_redirect ( "dept_list.php?pid=" . $pid . "&refresh=1&message=" . get_lang ( 'DeptMoveDownSuccess' ) );
			}
			break;
	}
}

if ($top_dept) {
	$dept_options [$top_dept ['id']] = $top_dept ['dept_name'] . ' - ' . $top_dept ['dept_no'];
	foreach ( $dept_tree as $dept_info ) {
		$dept_options [intval($dept_info ['id'])] = str_repeat ( '&nbsp;', 8 * ($dept_info ['level']) ) . $dept_info ['dept_name'] . ' - ' . $dept_info ['dept_no'];
	}
}

$tool_name = get_lang ( 'DeptList' );
$htmlHeadXtra [] = Display::display_thickbox ();
$htmlHeadXtra [] = '<script type="text/javascript">
	function refresh_tree(){ parent.Tree.location.reload();parent.Tree.d.openAll(); }
	</script>';
Display::display_header ( $tool_name, FALSE );
$dept_tree = $deptObj->get_all_dept_tree ();
$top_dept = $deptObj->get_top_dept ();
$dept_user_count = $deptObj->get_dept_user_count ();

if (isset ( $_GET ['message'] )) {
	Display::display_normal_message ( urldecode (getgpc("message","G") ) );
}

if (isset ( $_GET ['refresh'] )) {
	echo '<script>refresh_tree();</script>';
}

//顶部链接
if (isset ( $_GET ['pid'] )) {
	echo '<div class="actions">';
	echo str_repeat ( '&nbsp;', 1 ) . link_button ( 'add_dept.gif', 'AddSubDept', 'dept_add.php?org_id=' . $org_id . '&pid=' . $pid, '50%', '70%' );
	echo '</div>';
}

$dept_path = get_dept_path ( $pid, true );
echo "<p style='margin-left:10px'>" . get_lang ( "CurrentDept" ) . ": <b>" . $dept_path . "</b>,&nbsp;&nbsp;";
$sql = "SELECT COUNT(*) FROM " . $table_user . " WHERE dept_id=0";
echo get_lang ( "UserNotInAnyDeptTotalCount" ) . ":&nbsp;&nbsp;<b>" . Database::get_scalar_value ( $sql ) . "</b></p>";

$table_header [] = array (get_lang ( 'DeptName' ) );
$table_header [] = array (get_lang ( 'DeptNo' ) );
$table_header [] = array (get_lang ( 'DeptUserCount' ), false, null, array ('style' => 'width:80px' ) );
//$table_header [] = get_lang ( 'DeptAdmin' );
$table_header [] = get_lang ( 'Remark' );
$table_header [] = array (get_lang ( 'Actions' ), false, null, array ('style' => 'width:80px' ) );

if (Database::count_rows ( Database::get_main_table ( TABLE_MAIN_DEPT ) ) > 0) {
	$row_index = 0;
	$dept_tree = $deptObj->get_sub_dept_tree ( $pid, TRUE, TRUE );
	$cnt = count ( $dept_tree ) - 1;
	if ($dept_tree && is_array ( $dept_tree ) && count ( $dept_tree ) > 0) {
		foreach ( $dept_tree as $dept_id => $dept_info ) {
			$row = array ();
			$row [] = $row_index > 0 ? str_repeat ( '&nbsp;', 8 ) . $row_index . ". " . $dept_info ['dept_name'] : $dept_info ['dept_name'];
			
			$row [] = $dept_info ['dept_no'];
			//$row [] = $dept_user_count [$dept_info ['id']]; //liyu: 20091115
			$row [] = $deptObj->get_department_user_count ( intval($dept_info ['id']) );
			$row [] = nl2br ( $dept_info ['dept_desc'] );
			$action = '&nbsp;&nbsp;' . link_button ( 'edit.gif', 'Edit', 'dept_edit.php?id=' . intval($dept_info ['id']), '50%', '70%', false );
			
			if ($dept_info ['pid']) {
				$href = 'dept_list.php?action=delete_dept&amp;id=' . intval($dept_info ['id']) . '&amp;pid=' . $dept_info ['pid'];
				$action .= '&nbsp;&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', $href );
			}
			
			$row [] = $action;
			$table_data [] = $row;
			$row_index++;
		}
	}
}
echo Display::display_table ( $table_header, $table_data );


function refresh_dept_cache() {
	cache(CACHE_KEY_ADMIN_DEPT,NULL);
	$deptObj = new DeptManager ();
	cache(CACHE_KEY_ADMIN_DEPT,$deptObj->get_all_dept_tree ());
}

Display::display_footer ();
