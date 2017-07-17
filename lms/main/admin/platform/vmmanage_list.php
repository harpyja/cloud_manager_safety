<?php
/**
==============================================================================
 * 虚拟化管理
==============================================================================
 */
$language_file = 'admin';
$cidReset = true;
include_once ("../../inc/global.inc.php");

//api_protect_admin_script ();
api_block_anonymous_users ();
if (! api_is_admin ()) api_not_allowed ();

require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");

$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$table_course_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );
$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );

$redirect_url = 'main/admin/vmdisk/vmdisk_list.php';

$objCrsMng = new CourseManager ();
$vmaddres = getgpc('vmaddres');
//var_dump($vmaddres);


function get_sqlwhere() {
    global $restrict_org_id, $objCrsMng;

    $sql_where = "";
    if (is_not_blank ( $_GET ['keyword'] )) {
        $keyword = Database::escape_string (getgpc("keyword","G"), TRUE );
        $sql_where .= " AND (title LIKE '%" . trim ( $keyword ) . "%' OR code LIKE '%" . trim ( $keyword ) . "%')";
    }

    if (is_not_blank ( $_GET ['category_id'] )) {
        $sql_where .= " AND category_code=" . Database::escape (intval(getgpc ( 'category_id', 'G' )) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

//function get_number_of_courses() {
//    $course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
//    $sql = "SELECT COUNT(code) AS total_number_of_items FROM $course_table AS t1 ";
//    $sql_where = get_sqlwhere ();
//    if ($sql_where) $sql .= " WHERE " . $sql_where;
//    //echo $sql;exit;
//    return Database::getval ( $sql, __FILE__, __LINE__ );
//}

function get_number_of_vmtotal() {
    $vmtotal = Database::get_main_table ( vmtotal);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $vmtotal;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}

//function category_filter($category){
//    if($_GET['vmaddres']){
//        //$result='localhost';
//    }else{
//        //$result='127.0.0.1';
//        $vmaddres = $_GET['vmaddres'];
//    }
//
//
//    //return $result;
//    return $vmaddres;
//}


//function modify_filter($code) {
//    $html = "<a></a>";
//    $html .= '&nbsp;' . link_button ( 'blog_user.gif', 'CourseSubscribeUserList', 'ISO_edit.php?code=' . $code, '90%', '96%', FALSE );
//    //$html .= '&nbsp;' . link_button ( 'add_user_big.gif', 'CourseAdmin', 'course_admins.php?code=' . $code, '70%', '76%', FALSE );
//    return $html;
//}

function  lesson_filter($code) {
    //var_dump();
    $user = "select title from course where code = $code";
    $res = api_sql_query($user , __FILE__, __LINE__);
    $result =  Database::fetch_row ( $res);
    return $result[0];

}
function  con_filter($id) {
    $result='';
    $addres_sql = 'select `addres` from `vmtotal` where id='.intval($id);
    $addre  = Database::getval ( $addres_sql, __FILE__, __LINE__ );

    $vmid_sql = 'select `vmid` from `vmtotal` where id='.intval($id);
    $vmids  = Database::getval ( $vmid_sql, __FILE__, __LINE__ );


    $result='<a href="http://'.$addre.'/'.$vmids.'.html">远程协助</a>';
    return $result;
}


function user_filter($active, $url_params, $row) {

   // $result = $row[4];
$user = "select username from user where user_id = $row[4]";
    $res = api_sql_query($user , __FILE__, __LINE__);
   $result =  Database::fetch_row ( $res);
    //var_dump($row) ;
   return $result[0];
}



function get_vm_data($from, $number_of_items, $column, $direction) {

    $vmaddres = getgpc('vmaddres');
   // var_dump($vmaddres);
    if($vmaddres){
        $select = "select id as co9,addres as co10 , nicnum as co11, system as co12 ,user_id as co13 ,lesson_id as co14,vmid as co15,port as co16 ,group_id as co17, id as co18 FROM vmtotal where addres = '$vmaddres'";
    }else{
        $select = "select id as co9,addres as co10 , nicnum as co11, system as co12 ,user_id as co13 ,lesson_id as co14,vmid as co15,port as co16 ,group_id as co17, id as co18  FROM vmtotal ";
    }


   $res = api_sql_query ( $select, __FILE__, __LINE__ );


    $vm= array ();

    while ( $vm = Database::fetch_row ( $res) ) {
        $vms [] = $vm;
    }
    //var_dump($vms);
    return $vms;
}

$tool_name = get_lang ( 'CourseList' );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( NULL, FALSE );

$table = new SortableTable ( 'vmdisk', 'get_number_of_vmtotal', 'get_vm_data', 0, 10, 'ASC' );

$idx=0;
$table->set_header ( $idx ++, '序号', false);
$table->set_header ( $idx ++, '地址', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, 'nicnum', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, '系统' , false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, '用户名', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, '课程名称', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, 'vmid', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, 'port', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, '组', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, '远程协助', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_column_filter ( 4, 'user_filter' );
$table->set_column_filter ( 5, 'lesson_filter' );
$table->set_column_filter ( 9, 'con_filter' );

$table->display ();


//system("/sbin/cloudvmweb.sh ");
//    $sysidfile="/tmp/www/lms/main/admin/vminfo.html";
//    $num=file_get_contents($sysidfile);
//    echo $num;

?>




