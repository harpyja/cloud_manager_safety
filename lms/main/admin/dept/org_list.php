<?php
$language_file = array ('admin' );
$cidReset = true;
require_once ('../../inc/global.inc.php');
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();

require_once (api_get_path ( LIBRARY_PATH ) . "export.lib.inc.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( SYS_CODE_PATH ) . 'admin/admin.lib.inc.php');

$deptObj = new DeptManager ();

$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
$table_user = Database::get_main_table ( TABLE_MAIN_USER );
$table_user_role = Database::get_main_table ( TABLE_MAIN_USER_ROLE );
$view_sys_user = Database::get_main_table ( VIEW_USER );

if (isset ( $_GET ['action'] )) {
	switch (getgpc("action","G")) {
		case 'show_message' :
			if (isset ( $_GET ['message'] )) Display::display_normal_message ( stripslashes ( urldecode (getgpc("message","G") ) ) );
			break;
		case 'delete_dept' :
			$res_del = $deptObj->org_del (intval(getgpc ( 'id', 'G' )) );
			switch ($res_del) {
				case 1 :
					$log_msg = get_lang ( 'DelDeptInfo' ) . "id=" . intval(getgpc ( 'id', 'G' ));
					api_logging ( $log_msg, 'DEPT' );
					api_redirect ( "org_list.php?refresh=1&message=" . get_lang ( 'DeptDeleteSuccess' ) );
					break;
			}
			break;
	}
}

$htmlHeadXtra [] = Display::display_thickbox ();
$htmlHeadXtra [] = '<script type="text/javascript">
	function refresh_tree(){ parent.Tree.location.reload();parent.Tree.d.openAll(); }
</script>';

Display::display_header ( NULL, FALSE );
if (isset ( $_GET ['refresh'] )) echo '<script>refresh_tree();</script>';

echo '<div class="actions">';
echo link_button ( 'add_dept.gif', 'OrgAdd', 'org_update.php?action=add&pid=' . DEPT_TOP_ID, '50%', '70%' );
echo '</div>';

$sorting_options = array ();
$sorting_options ['column'] = 0;
$sorting_options ['default_order_direction'] = 'ASC';

$table_header [] = array (get_lang ( 'OrgNo' ), true );
$table_header [] = array (get_lang ( 'OrgName' ), true );
$table_header [] = array (get_lang ( 'Description' ), false );
//$table_header[] = array(get_lang('OrgAdmin'),true);
//$table_header[] = array(get_lang('OrgAdministrator'),true);
//$table_header[] = array(get_lang('LastUpdatedDate'),true);
$table_header [] = array (get_lang ( 'Actions' ), false, null, array ('style' => 'width:80px' ) );

$all_org = $GLOBALS ['deptObj']->get_all_org ();
/*$sql = "SELECT t1.*,t.dept_admins FROM " . $table_dept . " AS t1
			LEFT JOIN (SELECT org_id,GROUP_CONCAT(username) AS dept_admins
			FROM $table_user_role AS t2 LEFT JOIN sys_user AS t3
			ON t2.user_id=t3.user_id WHERE role_no='".ROLE_TRAINING_ADMIN."'
			GROUP BY org_id) AS t ON t.org_id=t1.id
			WHERE pid=" . DEPT_TOP_ID . " ORDER BY t1.dept_pos";
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$all_org=api_store_result_array($res);*/
foreach ( $all_org as $item ) {
	$row = array ();
	$row [] = $item ['dept_no'];
	$row [] = $item ['dept_name'];
	$row [] = nl2br ( $item ['dept_desc'] );
	
	//$dept_admin=UserManager::get_user_info_by_id($item["dept_admin"]);
	//$row[]=!empty($item['dept_admin'])?'<a href="'.api_get_path(WEB_CODE_PATH).'user_info.php?uid='.$item['dept_admin'].'&height=320&width=700&KeepThis=true&TB_iframe=true" class="thickbox">'.$dept_admin['firstname']."-".$dept_admin['username'].'</a>':"";

	/*$sql="SELECT GROUP_CONCAT(username) AS dept_admins	FROM $table_user_role AS t2 LEFT JOIN $view_sys_user AS t3
			ON t2.user_id=t3.user_id WHERE role_no='".ROLE_TRAINING_ADMIN."' AND t2.org_id='".$item["id"]
			."' AND t2.user_id<>'".$item["dept_admin"]."' GROUP BY org_id";
		$row[]=Database::get_scalar_value($sql);*/
	
	$action = "";
	$action .= link_button ( 'edit.gif', 'Edit', 'org_update.php?action=edit&id=' . intval($item ['id']), '50%', '70%', FALSE );
	$href='org_list.php?action=delete_dept&amp;id=' . intval($item ['id']) . '&amp;pid=' . intval($item ['pid']);
	$action .= '&nbsp;&nbsp;' . confirm_href ( 'delete.gif', 'OrgDelConfirm', 'Delete', $href );
	$row [] = $action;
	
	$table_data [] = $row;
}
//Display::display_sortable_table($table_header,$table_data,$sorting_options,array(),$query_vars);
echo Display::display_table ( $table_header, $table_data );

Display::display_footer ();