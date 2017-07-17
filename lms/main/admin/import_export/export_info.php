<?php
/*
 ==============================================================================
 课程分类管理
 ==============================================================================
 */
$category=  intval(getgpc('category_id'));

$language_file = 'admin';
$cidReset = true;
include_once ("../../inc/global.inc.php");
$this_section = SECTION_PLATFORM_ADMIN;
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');

api_protect_admin_script ();

require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( SYS_CODE_PATH ) . 'admin/course/course_category.inc.php');

$objDept = new DeptManager ();
Display::display_header ( NULL, FALSE );

function grade($description) {
    $grade="";
    if($description=='0'){
        $grade .="初级";
    }if($description=='1'){
        $grade .="中级";
    }if($description=='2'){
        $grade .="高级";
    }if($description!=='0' && $description!=='1' && $description!=='2'){
        $grade .="初级";
    }
    return $grade;
}
function get_sqlwhere() {
    global $restrict_org_id, $objCrsMng;
    $sql_where = "";
//    if (is_not_blank ( $_GET ['keyword'] )) {
//        $keyword = Database::escape_string ( $_GET ['keyword'], TRUE );
//        $sql_where .= " AND ( code LIKE '%" . trim ( $keyword ) . "%')";
//    }

    if (is_not_blank ( $_GET ['category_id'] )) {
        $sql_where .= " AND category_code=" . Database::escape (intval(getgpc ( 'category_id', 'G' )) );
    }
    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else
        return "";
}
function get_number_of_course() {
    $course_table = Database::get_main_table ( course );
    $sql = "SELECT COUNT(code) AS total_number_of_items FROM $course_table AS t1";
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " where " . $sql_where;
//    echo $sql;exit;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_course_data($from, $number_of_items, $column, $direction) {
    $course= Database::get_main_table ( course );
    $sql = "SELECT code as col0,description as col1,title as col2 FROM $course";

    $sql_where = get_sqlwhere ();
    if ($sql_where) {$sql .="where ".$sql_where;}

    $sql .= " LIMIT $from,$number_of_items";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $c= array ();
    while ( $c = Database::fetch_row ( $res) ) {
        $cc[] = $c;
    }
    return $cc;
}

$objCrsMng = new CourseManager ();
$category_tree = $objCrsMng->get_all_categories_tree ( TRUE );

function _get_course_count($parent_id) {
    $GLOBALS ['objCrsMng']->sub_category_ids = array ();
    $sub_category_ids = $GLOBALS ['objCrsMng']->get_sub_category_tree_ids ( $parent_id, TRUE );
    $tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
    $sql = "SELECT COUNT(code) FROM " . $tbl_course . " WHERE category_code=".  intval(getgpc('category_id'));
    //echo $sql;
    return Database::get_scalar_value ( $sql );
}

 
$table = new SortableTable ( 'cc', 'get_number_of_course', 'get_course_data', 2, NUMBER_PAGE );
//$table->set_additional_parameters ( $parameters );

$table->set_header (0, "课程编号",false, null ,array('width'=>'200px'));
$table->set_header (1,"等级",false, null ,array('width'=>''));
$table->set_header (2, "课程名称",false, null ,array('width'=>''));
$table->set_column_filter ( 1, 'grade' );

$table->display ();
