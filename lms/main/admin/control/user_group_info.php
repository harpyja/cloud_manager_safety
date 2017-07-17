<?php
$cidReset = TRUE;
$language_file = array ('exercice', 'admin' );
include_once ('../../inc/global.inc.php');
api_protect_quiz_script ();
require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$group_id = escape ( getgpc ( 'ids' ) );


$objDept = new DeptManager ();

if ($exerciseId) {
    $sql = "SELECT title FROM $TBL_EXERCICES WHERE id='" . intval($exerciseId) . "'";
    $exerciseTitle = Database::get_scalar_value ( $sql );
}

$redirect = 'main/admin/control/user_group_info.php?ids=' . $type;

//if(isset($_GET['action']) && $_GET['action']=='delete'){
//    $sql="delete"
//    api_redirect ( "exercise_result.php?exerciseId=" . $exerciseId);
//}
//$htmlHeadXtra [] = Display::display_thickbox ();
$nameTools = get_lang ( 'Exercices' );
Display::display_header ( $nameTools, FALSE );

$table_header [] = array (get_lang ( 'FirstName' ), false );
$table_header [] = array (get_lang ( 'LoginName' ), false );
$table_header [] = array (get_lang ( 'OfficialCode' ), false);
$table_header [] = array (get_lang ( 'Actions' ), false );

$table_data = array ();
$data_list = get_result_date ( $exerciseId );
foreach ( $data_list as $data ) {
    $table_data [] = $data;
}
$sorting_options = array ('column' => 0, 'default_order_direction' => 'ASC' );
Display::display_sortable_table ( $table_header, $table_data, $sorting_options, array (), $query_vars, array (), NAV_BAR_BOTTOM );

function get_result_date($group_id, $is_export = FALSE) {
    $group_id = intval(escape ( getgpc ( 'id' ) ));
    $sql = "SELECT `user_id`,`username`,`firstname`,`user_id` FROM `user` WHERE `group_id`=".$group_id ;
    $sql .= get_result_sqlwhere ();
    $sql .= " ORDER BY `user_id`";
    echo $sql;
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
    $table_data = array ();
    while ( $data = Database::fetch_array ( $result, 'ASSOC' ) ) {
        $row = array ();
        $row [] = $data ['user_id'];
        $row [] = $data ['username'];
        $row [] = $data ['firstname'];
        $act= confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'user_group_info.php?action=delete&id='.intval($data ['user_id']));

        $row [] =$act;
        $table_data [] = $row;
    }
    return $table_data;
}

function get_result_sqlwhere() {
    $objDept = new DeptManager ();
    $dept_id = isset ( $_GET ['keyword_deptid'] ) ? getgpc ( 'keyword_deptid', 'G' ) : '0';
    $sql_where = "";
    if (is_not_blank ( $_GET ['keyword'] )) {
        $query_vars ['keyword'] = getgpc ( 'keyword', 'G' );
        $keyword = trim ( Database::escape_str ( getgpc ( 'keyword', 'G' ), TRUE ) );
        if (! empty ( $keyword )) {
            $sql_where .= " AND  (firstname LIKE '%" . $keyword . "%'  OR username LIKE '%" . $keyword . "%')";
        }
    }
//    if ($dept_id and ! is_equal ( $dept_id, 'null' )) {
//        $query_vars ['keyword_deptid'] = $dept_id;
//        $dept_sn = $objDept->get_sub_dept_sn ( $dept_id );
//        if ($dept_sn) $sql_where .= " AND t3.dept_sn LIKE '" . $dept_sn . "%'";
//    }
    return $sql_where;
}

Display::display_footer ();
