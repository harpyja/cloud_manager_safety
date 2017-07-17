<?php


$language_file = array ('admin', 'registration' );
$cidReset = true;

include_once ('../../inc/global.inc.php');
//require_once (api_get_path ( LIBRARY_PATH ) . 'VmManager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

api_protect_admin_script ();

$main_user_table = Database::get_main_table ( VIEW_USER_DEPT );
$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
$table_user = Database::get_main_table ( TABLE_MAIN_USER );

$action = getgpc ( 'action', 'G' );
$dept_id = isset ( $_GET ['keyword_deptid'] ) ? getgpc ( 'keyword_deptid', 'G' ) : '0';

$sql = "SELECT user_id FROM " . $table_user . " WHERE is_admin=1 AND " . Database::create_in ( $_configuration ['default_administrator_name'], 'username' );
$root_user_id = Database::get_into_array ( $sql, __FILE__, __LINE__ );
$redirect_url = 'main/admin/net/vm_list_iframe.php';

$objDept = new DeptManager ();

function get_sqlwhere() {
    global $restrict_org_id, $objCrsMng;
    $sql_where = "";
    if (is_not_blank ( $_GET ['keyword'] )) {
        $keyword = Database::escape_string ( $_GET ['keyword'], TRUE );
        $sql_where .= " AND (id LIKE '%" . intval(trim ( $keyword )) . "%')";
    }

    if (is_not_blank ( $_GET ['id'] )) {
        $sql_where .= " AND id=" . Database::escape (intval(getgpc ( 'id', 'G' )) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

//by changzf
function get_number_of_vm() {
    $networkmap = Database::get_main_table (networkmap);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $networkmap;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}


function info($id, $url_params) {
    global $_configuration, $root_user_id;
   $result .= link_button ( 'synthese_view.gif', 'Info', 'vm_add.php?id='. intval($id), '90%', '90%', FALSE );
    //$result .= link_button ( 'edit.gif', 'Edit', 'vm_edit.php?id='. $id, '90%', '90%', FALSE );
    return $result;
}


function modify_filter($id, $url_params) {
	//global $_configuration, $root_user_id;
   $result .= link_button ( 'edit.gif', 'Edit', 'vm_edit.php?id='. intval($id), '100%', '98%', FALSE );
	//$result .= '&nbsp;' . link_button ( 'edit.gif', 'Edit', 'vm_edit.php?id='. $id, '90%', '80%', FALSE );
	if (api_is_platform_admin ()) {
		$result .= '&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'vm_list.php?action=delete_vm&id=' . intval($id) );
	}
	return $result;
}

//by changzf
function get_vm_data($from, $number_of_items, $column, $direction) {
	$networkmap = Database::get_main_table ( networkmap);
	//$sql = "select id as co5,id as co6,name as co7,id as co8, id as co9 FROM  $networkmap ";
		$sql = "select id as co7,id as co8,name as co9,content as co10,id as co11 ,id as co12 FROM  $networkmap ";
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    // $sql .= " ORDER BY col$column $direction ";
    $sql .= " LIMIT $from,$number_of_items";

	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$vm= array ();

	while ( $vm = Database::fetch_row ( $res) ) {
		$vms [] = $vm;
	}
	return $vms;
}

function lock_unlock_user($status, $user_id) {
	global $_configuration;
	$networkmap = Database::get_main_table ( TABLE_MAIN_USER );
	
	if ($status == 'lock') {
		$status_db = '0';
		$return_message = get_lang ( 'UserLocked' );
	}
	if ($status == 'unlock') {
		$status_db = '1';
		$return_message = get_lang ( 'UserUnlocked' );
	}
	
	if (($status_db == '1' or $status_db == '0') and is_numeric ( $user_id )) {
		$sql = "UPDATE $networkmap SET active='" . escape ( $status_db ) . "' WHERE user_id='" . escape ( $user_id ) . "'  AND username<>'" . $_configuration ['default_administrator_name'] . "'";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		
		api_logging ( $return_message . ": status=" . $status . ",user_id=" . $user_id, 'USER' );
	}
	
	if ($result > 0) {
		return $return_message;
	}
}

function batch_lock_unlock_user($action, $user_ids = array()) {
	$networkmap = Database::get_main_table ( TABLE_MAIN_USER );
	global $_configuration;
	if ($action == 'batchLock') {
		$status_db = '0';
	}
	if ($action == 'batchUnlock') {
		$status_db = '1';
	}

	if (is_array ( $user_ids ) && count ( $user_ids )) {
		$sql = "UPDATE $networkmap SET active='" . $status_db . "' WHERE user_id IN (" . implode ( ",", $user_ids ) . ") AND username<>'" . $_configuration ['default_administrator_name'] . "'";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	}

	api_logging ( get_lang ( "BatchLockUnlockUser" ) . ": action=" . $action . ",user_id=" . implode ( ",", $user_ids ), 'USER' );

	return $result;

}
//dengxin delete
if ( $_GET ['action'] =='delete_vm') {
    $table = "networkmap";
    $id = intval(getgpc('id'));
    //$where = "where id='{$id}'";
    $sql = "DELETE FROM `vslab`.`networkmap` WHERE `networkmap`.`id` = {$id}";
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
    tb_close ( "vm_list_iframe.php" );
}
//处理批量操作
if (isset ( $_POST ['action'] )) {
    switch (getgpc("action","P")) {
        // 批量删除课程
        case 'delete_vms' :
            $deleted_vm_count = 0;
            $vm_id = getgpc('networkmap');
            if (count ( $vm_id ) > 0) {
                foreach ( $vm_id as $index => $id ) {

                    $sql = "DELETE FROM `vslab`.`networkmap` WHERE id='" . intval($id) . "'";
                    api_sql_query ( $sql, __FILE__, __LINE__ );

                    $log_msg = get_lang('删除所选') . "id=" . intval($id);
                    api_logging ( $log_msg, 'networkmap', 'dfgdfgdfg' );
                }
            }
        //  Display::display_msgbox ( get_lang ( 'OperationSuccess' ), 'main/course_description/step.php' );

    }
}



$htmlHeadXtra [] = Display::display_thickbox ();

Display::display_header ( NULL, FALSE );


$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" ) . "/" . get_lang ( "OfficialCode" );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:20%", 'class' => 'inputText', 'title' => $keyword_tip ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );

echo '<div class="actions">';
echo '<span style="float:right; padding-top:5px;">';
$url_add_vm='topo_add.php?keyword_deptid='.(is_not_blank($_GET['keyword_deptid'])?getgpc('keyword_deptid','G'):getgpc('keyword_orgid','G'));
echo link_button ( 'add_user_big.gif', '新建网络拓扑模板', $url_add_vm, '90%', '90%' );

$url_add_device='device_type.php?keyword_deptid='.(is_not_blank($_GET['keyword_deptid'])?getgpc('keyword_deptid','G'):getgpc('keyword_orgid','G'));

echo link_button ( 'add_user_big.gif', '设备类型管理', $url_add_device, '90%', '90%' );
//echo link_button ( 'excel.gif', 'ImportUserListXMLCSV',($_configuration ['enable_user_ext_info']?'user_import2.php':'user_import.php'), '70%', '80%' );
//echo link_button ( 'excel.gif', 'ExportUserListXMLCSV', 'user_export.php', '50%', '80%' );
echo '</span>';
$form->display ();
echo '</div>';

if (isset ( $_GET ['keyword'] ) && is_not_blank ( $_GET ["keyword"] )) {
	$parameters ['keyword'] = getgpc ( 'keyword', 'G' );
	$parameters = array ('keyword' => getgpc('keyword'), 'keyword_status' => getgpc('keyword_status'), 'keyword_org_id' => getgpc("keyword_org_id") );
}

if (is_not_blank ( $_GET ["keyword_org_id"] )) $parameters ['keyword_org_id'] = trim ( getgpc("keyword_org_id") );
if ($dept_id) $parameters ['keyword_deptid'] = $dept_id;


$table = new SortableTable ( 'networkmap', 'get_number_of_vm', 'get_vm_data', 2, NUMBER_PAGE );
$table->set_additional_parameters ( $parameters );

$actions = array ('delete' => get_lang ( 'BatchDelete' ) );
$table->set_form_actions ( $actions );

$idx = 0;
//$table->set_header ( $idx ++, '', false );
$table->set_header ( $idx ++, '', false, null,array ('style' => 'width:100px;' ) );
$table->set_header ( $idx ++, get_lang ('编号'), true, null, array ('style' => 'width:100px;text-align:center' ) );
$table->set_header ( $idx ++, get_lang ( 'Topologytemplatename' ), true, null, array ('style' => 'width:150px;text-align:center' ) );
$table->set_header ( $idx ++, get_lang ( 'describe' ), true, null, array ('style' => 'width:150px;text-align:center' ) );
$table->set_header ( $idx ++, get_lang ( 'preview' ) , true, null, array ('style' => 'width:100px;text-align:center' ) );
$table->set_header ( $idx ++, get_lang ( 'Actions' ), false, null, array ('style' => 'width:100px;text-align:center' ) );
$table->set_form_actions ( array ('delete_vms' => '删除所选项' ), 'networkmap' );
$table->set_column_filter ( 5, 'modify_filter' );
$table->set_column_filter ( 4, 'info' );
$table->display ();

Display::display_footer ();
