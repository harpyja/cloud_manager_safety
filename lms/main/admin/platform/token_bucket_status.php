<?php
/**
==============================================================================
 * 令牌桶状态
==============================================================================
 */
$language_file = 'admin';
$cidReset = true;
include_once ("../../inc/global.inc.php");

api_protect_admin_script ();
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");

$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$table_course_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );
$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );

$redirect_url = 'main/admin/vmdisk/vmdisk_list.php';

$objCrsMng = new CourseManager ();
$status = htmlspecialchars($_GET['status']);


$tool_name = get_lang ( 'CourseList' );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = '<style type="text/css">body{background:#F8F8F8;}</style>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( NULL, FALSE );

$html = '<div id="demo" class="yui-navset">';
echo '</div></div>';

if($status=="vlanhub"){
    function delete_vlanhub($id) {
        $result = "";
        $result .= '&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', '删除', 'token_bucket_status.php?action=delete_vlanhub&status=vlanhub&hub_type=vlanhub&id=' . intval($id) );
        return $result;
    }
    if (isset ( $_GET ['action'] )) {
        switch (getgpc("action","G")) {
            case 'delete_vlanhub' :
                if ( $_GET ['action'] =='delete_vlanhub') {
                    $id = intval(htmlspecialchars($_GET['id']));

                    $d_sql="UPDATE  `vslab`.`hub` SET  `status` =  '0'  WHERE  `hub`.`Pid` =(SELECT `port` FROM  `vlanhub`  WHERE id = '$id');";
                    $s=api_sql_query ( $d_sql, __FILE__, __LINE__ );

                    $sql = "DELETE FROM `vslab`.`vlanhub` WHERE `vlanhub`.`id` = '".$id."'";
                    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                    //tb_close ( "token_bucket_status.php?action=vlanhub");
                }
                break;
        }
    }
    function get_sqlwhere() {

        $sql_where = "";
        if (is_not_blank ( $_GET ['keyword'] )) {
            $keyword = Database::escape_string (getgpc("keyword","G"), TRUE );
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
    function get_number_of_vlanhub() {
        $sql = "SELECT COUNT(id) AS total_number_of_items FROM vlanhub";

        $sql_where = get_sqlwhere ();
        if ($sql_where) $sql .= " WHERE " . $sql_where;
        return Database::getval( $sql, __FILE__, __LINE__ );
    }
    function get_vlanhub_data($from, $number_of_items, $column, $direction) {

        $sql = "select id as co9,userid as co10,lessionid as co11,vlan as co12,system as co13, interface as co14, hper_interface as co15,port as co16 ,id as co17 FROM `vlanhub`";

        $sql .= " LIMIT $from,$number_of_items";
//echo $sql;
        $res = api_sql_query ( $sql, __FILE__, __LINE__ );
        $vm= array ();

        while ( $vm = Database::fetch_row ( $res) ) {
            $vms [] = $vm;
        }
        return $vms;
    }

    $table = new SortableTable ( 'hub', 'get_number_of_vlanhub', 'get_vlanhub_data', 0, 10, 'ASC' );
    $table->set_additional_parameters ( $parameters );
    $table->set_header ( 0, 'id', false, array ('style' => 'width:150px;text-align:center' ) );
    $table->set_header ( 1, 'userid', false, null, array ('style' => 'width:150px;text-align:center' ) );
    $table->set_header ( 2, 'lessionid' , false, null, array ('style' => 'width:100px;text-align:center' ) );
    $table->set_header ( 3, 'vlan', false, null, array ('style' => 'width:100px;text-align:center' ) );
    $table->set_header ( 4, 'system', false, null, array ('style' => 'width:100px;text-align:center' ) );
    $table->set_header ( 5, 'interface', false, null, array ('style' => 'width:100px;text-align:center' ) );
    $table->set_header ( 6, 'hper_interface', false, null, array ('style' => 'width:100px;text-align:center' ) );
    $table->set_header ( 7, 'port', false, null, array ('style' => 'width:100px;text-align:center' ) );
    $table->set_header ( 8, '操作', false, null, array ('style' => 'width:80px;text-align:center' ) );

    $table->set_column_filter ( 8, 'delete_vlanhub' );
    $table->display ();
}else{
    function delete_filter($id) {
        $result = "";
        $result .= '&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', '删除', 'token_bucket_status.php?action=delete_porthub&hub_type=porthub&id=' . intval($id));
        return $result;
    }

    if (isset ( $_GET ['action'] )) {
        switch (getgpc("action","G")) {
            case 'delete_porthub' :
                if ( $_GET ['action'] =='delete_porthub') {
                    $id = intval(htmlspecialchars($_GET['id']));

                    $update_sql="UPDATE  `vslab`.`hub` SET  `status` =  '0'  WHERE  `hub`.`Pid` =(SELECT `port` FROM  `porthub`  WHERE id ='$id');";
                    $result_u = api_sql_query ( $update_sql, __FILE__, __LINE__ );

                    $sql = "DELETE FROM `vslab`.`porthub` WHERE `porthub`.`id` = ".$id;
                    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

                    tb_close ( "token_bucket_iframe.php");
                }
                break;
        }
    }
    function get_sqlwhere() {
        global $restrict_org_id, $objCrsMng;

        $sql_where = "";
        if (is_not_blank ( $_GET ['keyword'] )) {
            $keyword = Database::escape_string (getgpc("keyword","G"), TRUE );
            $sql_where .= " AND (id LIKE '%" . trim ( $keyword ) . "%')";
        }
        if (is_not_blank ( $_GET ['id'] )) {
            $sql_where .= " AND id=" . Database::escape (intval(getgpc ( 'id', 'G' ) ));
        }
        $sql_where = trim ( $sql_where );
        if ($sql_where)
            return substr ( ltrim ( $sql_where ), 3 );
        else return "";
    }
    function get_number_of_porthub() {
        $sql = "SELECT COUNT(id) AS total_number_of_items FROM porthub";
        $sql_where = get_sqlwhere ();
        if ($sql_where) $sql .= " WHERE " . $sql_where;
        return Database::getval( $sql, __FILE__, __LINE__ );
    }
    function get_porthub_data($from, $number_of_items, $column, $direction) {

        $sql = "select id as co6,userid as co7,lessionid as co8,vmid as co9,port as co10, id as co12 FROM `porthub`";


        $sql .= " LIMIT $from,$number_of_items";
//echo $sql;
        $res = api_sql_query ( $sql, __FILE__, __LINE__ );
        $vm= array ();

        while ( $vm = Database::fetch_row ( $res) ) {
            $vms [] = $vm;
        }
        return $vms;
    }

    $table = new SortableTable ( 'hub', 'get_number_of_porthub', 'get_porthub_data', 0, 10, 'ASC' );
    $table->set_additional_parameters ( $parameters );
    $table->set_header ( 0, 'id', false,array ('style' => 'width:150px;text-align:center' ));
    $table->set_header ( 1, 'userid', null, null, array ('style' => 'width:150px;text-align:center' ) );
    $table->set_header ( 2, 'lessionid' , false, null, array ('style' => 'width:100px;text-align:center' ) );
    $table->set_header ( 3, 'vmid', false, null, array ('style' => 'width:100px;text-align:center' ) );
    $table->set_header ( 4, 'port', false, null, array ('style' => 'width:100px;text-align:center' ) );
    $table->set_header ( 5, '操作', false, null, array ('style' => 'width:80px;text-align:center' ) );

    $table->set_column_filter ( 5, 'delete_filter' );
    $table->display ();
}

?>
</table>
</div>
</div>
</div>
</div>