<?php
/*
 ==============================================================================
 设备类型管理
 ==============================================================================
 */
include_once ('../../inc/global.inc.php');
header("content-type:text/html;charset=utf-8");
$language_file = array ('admin', 'registration' );
$cidReset = true;
api_protect_admin_script ();//User rights  @chang_z_f 2013-07-27
include_once ('desc.inc.php');
require_once (api_get_path(INCLUDE_PATH ).'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

echo "<style type='text/css'>
.yui-skin-sam .yui-navset .yui-nav #step a,.yui-skin-sam .yui-navset .yui-nav #step a:focus,.yui-skin-sam .yui-navset .yui-nav #step a:hover
    </style>";
$htmlHeadXtra [] = Display::display_thickbox ( TRUE );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

Display::display_header(null,FALSE);

echo '<div class="actions">';
echo '<span style="float:right; padding-top:2px;">';
echo '&nbsp;&nbsp;' . link_button ( 'new_step.gif', '新建设备类型', 'device_add.php', '80%' , '70%'  );
echo '</span>';
//$form->display ();
echo '</div>';

function  Edit_filter($id) {
    $html = "";
    $html .= '&nbsp;&nbsp;&nbsp;' . link_button ( 'edit.gif', 'Edit', 'device_edit.php?id=' . intval($id) . '&course_id='  , '100%', '98%', FALSE );
    return $html;
}
function  Delete_filter($id) {


    $desc = "select `default` from device_type where id=".intval($id);
    $default  = Database::getval ( $desc, __FILE__, __LINE__ );
    $lessonedit="/etc/lessonedit";
    $lessonedit=file_get_contents($lessonedit);
    $lessonedit+=0;
    $default+=0;
    if($lessonedit == '1'){
        $html = "";
        $html .="&nbsp;" . confirm_href ( 'delete.gif', '您确定要执行该操作吗？', 'Delete', 'device_type.php?delete_type=' . intval($id));
        return $html;

    }else{
        if($default =='0'){
            $html = "";
            $html .="&nbsp;" . confirm_href ( 'delete.gif', '您确定要执行该操作吗？', 'Delete', 'device_type.php?delete_type=' . intval($id) );
            return $html;
        }else{
            $html = "";
            $html .="默认";
            return $html;
        }
    }

//    $html = "";
//    $html .="&nbsp;&nbsp;&nbsp;" . confirm_href ( 'delete.gif', '您确定要执行该操作吗？', 'Delete', 'device_type.php?delete_type=' . $id );
//    return $html;
}

if ( isset ( $_GET ['delete_type'] )) {
    $tbl_device = Database::get_main_table (device_type);
    $id = intval(getgpc('delete_type'));

    $s_sql="SELECT image_url FROM  $tbl_device WHERE id= ".$id;
    $image_url = Database::getval( $s_sql, __FILE__, __LINE__ );

    $sql = "DELETE FROM $tbl_device WHERE id= ".$id;
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

    exec("chmod -R 777 * /var/www/lms/storage/images/ ");
    exec("rm /var/www$image_url");
}

function get_sqlwhere() {
    global $restrict_org_id, $objCrsMng;
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

function get_device_type_data($from, $number_of_items, $column, $direction) {
    $tbl_device = Database::get_main_table (device_type);
    $sql = "SELECT device_name AS col0,id as col1 FROM $tbl_device AS t1 ";
    // $sql = "SELECT id AS col0,device_name AS col1,id as col2,id as col3 FROM $tbl_device AS t1 ";
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " and " . $sql_where;
    // $sql .= " ORDER BY col$column $direction ";
    $sql .= " LIMIT $from,$number_of_items";

    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $step= array ();

    while ( $step = Database::fetch_row ( $res) ) {
        $steps [] = $step;
    }
    return $steps;
}

function get_number_of_device_type() {
    $tbl_devicetype = Database::get_main_table (device_type);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM $tbl_devicetype AS t1";
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " where " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}

$table = new SortableTable ( 'device_type', 'get_number_of_device_type', 'get_device_type_data', 2, NUMBER_PAGE );
$table->set_additional_parameters ( $parameters );
$idx = 0;

//$table->set_header ( $idx ++, '', false ,null ,array('width'=>'120px'));
$table->set_header ( $idx ++, '类型名称',false ,null ,array(''));
//$table->set_header($idx ++, '预览',false ,null ,array(''));
//$table->set_header ( $idx ++, get_lang ( 'Edit' ), false, null ,array('width'=>'70px') );
$table->set_header ( $idx ++, get_lang ( 'Delete' ), false, null ,array('width'=>'70px') );
//$table->set_column_filter ( 1, 'Edit_filter' );
$table->set_column_filter ( 1, 'Delete_filter' );
//$table->set_form_actions ( array ('delete_steps' => '删除所选项' ), 'device_type' );
$table->display ();
Display::display_footer ();
?>
