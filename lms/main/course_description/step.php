<?php
/*
 ==============================================================================
 实验步骤的编辑与显示
 ==============================================================================
 */


$language_file = array ('course_description' );
include_once ('../inc/global.inc.php');
api_protect_course_script ();

include_once ('desc.inc.php');
if (! $allowed_to_edit) api_not_allowed ();

header("content-type:text/html;charset=utf-8");
$language_file = array ('admin', 'registration' );
$cidReset = true;

echo "<style type='text/css'>
.yui-skin-sam .yui-navset .yui-nav #step a,.yui-skin-sam .yui-navset .yui-nav #step a:focus,.yui-skin-sam .yui-navset .yui-nav #step a:hover
    </style>";
//$lessonid = $_GET['cidReq'];
$lessonid = $_SESSION['_cid'];
//echo $lessonid;
$htmlHeadXtra [] = Display::display_thickbox ( TRUE );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

Display::display_header(null,FALSE);

$description_id = isset ( $_REQUEST ['description_id'] ) ? intval ( getgpc ( 'description_id' ) ) :9;


$html = '<div id="demo" class="yui-navset">';
$html .= '<ul class="yui-nav">';
$html .= '<li  ' . ($description_id == 10 ? 'class="selected"' : '') . '><a href="../admin/course/course_edit.php?cidReq='.$lessonid.'&description_id=' . 10 . '"><em>' . get_lang ( '课程设置' ) . '</em></a></li>';

$html .= '<li  ' . ($description_id == 0 ? 'class="selected"' : '') . '><a href="index.php?cidReq='.$lessonid.'&description_id=' . 0 . '"><em>' . get_lang ( '课程信息' ) . '</em></a></li>';
$html .= '<li  ' . ($description_id == 8 ? 'class="selected"' : '') . '><a href="index.php?cidReq='.$lessonid.'&description_id=' . 8 . '"><em>' . get_lang ( 'Sybzh' ) . '</em></a></li>';
$html .= '<li class="selected" id="step"><a href="step.php?cidReq='.$lessonid.'"><em>' . get_lang ( '模拟仿真实验' ) . '</em></a></li>';
$html .= '<li  ' . ($description_id == 7 ? 'class="selected"' : '') . '><a href="index.php?cidReq='.$lessonid.'&description_id=' . 7 . '"><em>' . get_lang ( '教学大纲' ) . '</em></a></li>';
$html .= '<li  ' . ($description_id == 14 ? 'class="selected"' : '') . '><a href="lessontop.php?cidReq='.$lessonid.'"><em>' . get_lang ( 'Topology') . '</em></a></li>';

$html .= '<li style="float:right;margin-right:5px;">' . link_button ( 'edit.gif', '新建&nbsp;&nbsp;', 'step_add.php?action=edit&cidReq=' . $_SESSION['_cid'], '100%', '100%' ) . '</li>';
$html .= '</ul>';
$html .= '<div class="yui-content"><div id="tab1">';
echo $html;

function action($action) {
    $html = "";
    if($action==1){  $html="左键";  }
    if($action==2){  $html="右键";  }
    if($action==3){  $html="命令行";}
    return $html;
}
function  Edit_filter($id) {
    $html = "";
    $html .= '&nbsp;&nbsp;&nbsp;' . link_button ( 'edit.gif', 'Edit', 'step_edit.php?id=' . $id . '&course_id='. getgpc('cidReq','G') .''  , '100%', '98%', FALSE );
    return $html;
}
function  Delete_filter($id) {
    $html = "";
    $html .="&nbsp;&nbsp;&nbsp;" . confirm_href ( 'delete.gif', '您确定要执行该操作吗？', 'Delete', 'step.php?delete_step=' . $id );
    return $html;
}

if ( isset (getgpc('delete_step'))) {

    $tbl_step = Database::get_main_table (simulation);
//    $s_sql="SELECT COUNT( * ) FROM  $tbl_step WHERE course_id=".$lessonid;
//    $s_sql="SELECT cont(step_id) FROM  WHERE id= ".$id;
//    $max_step = Database::getval( $s_sql, __FILE__, __LINE__ );
    $id = getgpc('delete_step');

    $s_sql="SELECT image_url FROM  $tbl_step WHERE id= ".$id;
    $image_url = Database::getval( $s_sql, __FILE__, __LINE__ );
    //$image_name = getExt($image_url);

    $sql = "DELETE FROM $tbl_step WHERE id= ".$id;
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

//exec("cd /tmp/www/lms/storage/courses/$lessonid/document/images/");
exec("chmod -R 777 * /tmp/www/lms/storage/courses/$lessonid/document/images/ ");

exec("rm /tmp/www$image_url");



//    for($i = $step_id+1;$i <= $step_max; $i++){
//        $sql_data = array(
//            'step_id' => $i
//
//        );
//        $sql = Database::sql_update ( "simulation", $sql_data," id ='$id'" );
//        $result = api_sql_query ( $sql, __FILE__, __LINE__ );
//
//    }


}

//处理批量操作
if (isset ( getgpc('action','P') )) {
    $id =getgpc('delete_step'); 
    switch ( getgpc('action','P') ) {
        // 批量删除课程
        case 'delete_steps' :
            $deleted_step_count = 0;
            $stepid = $_POST ['step'];
            exec("chmod -R 777 * /tmp/www/lms/storage/courses/$lessonid/document/images/ ");
            if (count ( $stepid ) > 0) {
                foreach ( $stepid as $index => $id ) {
                    $s_sql="SELECT image_url FROM  simulation WHERE id=".$id;
                    $image_url = Database::getval( $s_sql, __FILE__, __LINE__ );
                    exec("rm /tmp/www$image_url");
                    $sql = "DELETE FROM `vslab`.`simulation` WHERE id='" . $id . "'";
                    api_sql_query ( $sql, __FILE__, __LINE__ );

                    $log_msg = get_lang('删除步骤') . "id=" . $id;
                 //  api_logging ( $log_msg, 'step', 'dfgdfgdfg' );


                }
            }
        //  Display::display_msgbox ( get_lang ( 'OperationSuccess' ), 'main/course_description/step.php' );

    }
}

function get_sqlwhere() {
    global $restrict_org_id, $objCrsMng;
    $sql_where = "";
    if (is_not_blank (  getgpc('keyword','G'))) {
        $keyword = Database::escape_string ( getgpc('keyword','G'), TRUE );
        $sql_where .= " AND (id LIKE '%" . trim ( $keyword ) . "%')";
    }

    if (is_not_blank (getgpc('id','G'))) {
        $sql_where .= " AND id=" . Database::escape ( intval(getgpc ( 'id', 'G' )) );
    }

    $sql_where = trim ( $sql_where );
  //  echo $sql_where;
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}
//var_dump($_SESSION);
function get_number_of_step() {
    $tbl_step = Database::get_main_table (simulation);
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM $tbl_step AS t1 where course_id=".$_SESSION['_cid'];
    //echo $sql;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND " . $sql_where;
  //  echo $sql,'<br/>';exit;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}

function get_step_data($from, $number_of_items, $column, $direction) {
    $tbl_step = Database::get_main_table (simulation);
    $sql = "SELECT id AS col0,step AS col1,action AS col2,id as col3,id as col3 FROM $tbl_step AS t1 where course_id=".$_SESSION['_cid'];
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " and " . $sql_where;
   // $sql .= " ORDER BY col$column $direction ";
    $sql .= " LIMIT $from,$number_of_items";
    //echo $sql;
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $step= array ();

    while ( $step = Database::fetch_row ( $res) ) {
        $steps [] = $step;
    }
    return $steps;
}



require_once (api_get_path(INCLUDE_PATH ).'lib/mail.lib.inc.php');

//require_once (api_get_path ( LIBRARY_PATH ) . 'networkmap.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');




$table = new SortableTable ( 'step', 'get_number_of_step', 'get_step_data', 2, NUMBER_PAGE );
$table->set_additional_parameters ( $parameters );
$idx = 0;

$table->set_header ( $idx ++, '', false ,null ,array('width'=>'120px'));
$table->set_header ( $idx ++, '步骤名称',false ,null ,array(''));
$table->set_header($idx ++, '动作',false ,null ,array(''));
$table->set_header ( $idx ++, get_lang ( 'Edit' ), false, null ,array('width'=>'70px') );
$table->set_header ( $idx ++, get_lang ( 'Delete' ), false, null ,array('width'=>'70px') );





$table->set_column_filter ( 2, 'action' );
$table->set_column_filter ( 3, 'Edit_filter' );
$table->set_column_filter ( 4, 'Delete_filter' );
$table->set_form_actions ( array ('delete_steps' => '删除所选步骤' ), 'step' );
$table->display ();
Display::display_footer ();
?>
