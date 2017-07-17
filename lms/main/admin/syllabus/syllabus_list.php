<?php

$language_file = 'admin';
$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");

$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$table_course_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );

function course_filter($code){
    $result ='';
    $result.='<span style="float:left">'.$code.'</span>';
    return $code;
}

$objCrsMng = new CourseManager ();

function get_sqlwhere() {
    global $restrict_org_id, $objCrsMng;
    $sql_where = "";
    if (is_not_blank ( $_GET ['keyword'] )) {
        $keyword = Database::escape_string ( getgpc('keyword','G') , TRUE );
        $sql_where .= " AND (code LIKE '%" . trim ( $keyword ) . "%' OR class LIKE '%" . trim ( $keyword ) . "%' OR professional LIKE '%" . trim ( $keyword ) . "%' OR week LIKE '%" . trim ( $keyword ) . "%')";
    }

    if (is_not_blank ( $_GET ['category_id'] )) {
        $sql_where .= " AND category_code=" . Database::escape ( intval(getgpc ( 'category_id', 'G' )) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_syllabus() {
    $syllabus_table = Database::get_main_table ( syllabus );
    $sql = "SELECT COUNT(code) AS total_number_of_items FROM $syllabus_table AS t1 ";
    //$sql_where = get_sqlwhere ();
    //if ($sql_where) $sql .= " WHERE " . $sql_where;
    //echo $sql;exit;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}

function get_syllabus_data($from, $number_of_items, $column, $direction) {
    $course_table = Database::get_main_table ( TABLE_MAIN_COURSE );

    //by changzf on 2012/07/18

//    $sql = "SELECT code AS col0,code AS col1,code AS col2,type AS col3,credit as col4,credit_hours as col5,teacher as col6,professional as col7,
//    class as col8,week as col9,mon as col10,tue as col11,wed as col12,thu as col13,fri as col14, sat as col15,sun as col16,id as col17,id as col18
//    FROM syllabus ";
    $sql = "SELECT code ,code ,type ,credit ,credit_hours ,teacher ,professional ,
    class ,week ,mon ,tue ,wed ,thu ,fri , sat ,sun ,id
    FROM syllabus ";
    $sql_where = get_sqlwhere ();
    if ($sql_where) {$sql .= 'where'.$sql_where;}

    $sql .= " LIMIT $from,$number_of_items";
    //echo $sql;
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $courses = array ();
    $objCourse = new CourseManager ();
    $vm= array ();

    while ( $course = Database::fetch_array ( $res, 'NUM' ) ) {
        $objCourse->category_path = '';

        $course[0]='<span style="float:left">&nbsp;'.$course[0].'</span>';
        $vv = $course[1];

        $vb = "SELECT category_code FROM course where title = '$vv'";

        $sql3 = "SELECT name FROM course_category where id = ($vb)";
        $course [1] = Database::getval ( $sql3, __FILE__, __LINE__ );

        $course [1]='<span style="float:left">&nbsp;'.$course[1].'</span>';

        $am = unserialize($course[9]);
        $course[9] = '';
        if($am['monday']==1){
            if($am['class1']==1){ $course[9] .= '第一节;'; }
            if($am['class2']==1){ $course[9] .= '第二节;'; }
            if($am['class3']==1){ $course[9] .= '第三节;'; }
            if($am['class4']==1){ $course[9] .= '第四节;'; }
            if($am['class5']==1){ $course[9] .= '晚自习;'; }
        }else{
            $course[9]='';
        }

        $at = unserialize($course[10]);
        $course[10] = '';
        if($at['tuesday']==1){
            if($at['class1']==1){ $course[10] .= '第一节;'; }
            if($at['class2']==1){ $course[10] .= '第二节;'; }
            if($at['class3']==1){ $course[10] .= '第三节;'; }
            if($at['class4']==1){ $course[10] .= '第四节;'; }
            if($at['class5']==1){ $course[10] .= '晚自习;'; }
        }else{
            $course[10]='';
        }

        $aw = unserialize($course[11]);
        $course[11] = '';
        if($aw['wednesday']==1){
            if($aw['class1']==1){ $course[11] .= '第一节;'; }
            if($aw['class2']==1){ $course[11] .= '第二节;'; }
            if($aw['class3']==1){ $course[11] .= '第三节;'; }
            if($aw['class4']==1){ $course[11] .= '第四节;'; }
            if($aw['class5']==1){ $course[11] .= '晚自习;'; }
        }else{
            $course[11]='';
        }

        $athu = unserialize($course[12]);
        $course[12] = '';
        if($athu['thursday']==1){
            if($athu['class1']==1){ $course[12] .= '第一节;'; }
            if($athu['class2']==1){ $course[12] .= '第二节;'; }
            if($athu['class3']==1){ $course[12] .= '第三节;'; }
            if($athu['class4']==1){ $course[12] .= '第四节;'; }
            if($athu['class5']==1){ $course[12] .= '晚自习;'; }
        }else{
            $course[12]='';
        }

        $af = unserialize($course[13]);
        $course[13] = '';
        if($af['friday']==1){
            if($af['class1']==1){ $course[13] .= '第一节;'; }
            if($af['class2']==1){ $course[13] .= '第二节;'; }
            if($af['class3']==1){ $course[13] .= '第三节;'; }
            if($af['class4']==1){ $course[13] .= '第四节;'; }
            if($af['class5']==1){ $course[13] .= '晚自习;'; }
        }else{
            $course[13]='';
        }

        $as = unserialize($course[14]);
        $course[14] = '';
        if($as['saturday']==1){
            if($as['class1']==1){ $course[14] .= '第一节;'; }
            if($as['class2']==1){ $course[14] .= '第二节;'; }
            if($as['class3']==1){ $course[14] .= '第三节;'; }
            if($as['class4']==1){ $course[14] .= '第四节;'; }
            if($as['class5']==1){ $course[14] .= '晚自习;'; }
        }else{
            $course[14]='';
        }

        $asu = unserialize($course[15]);
        $course[15] = '';
        if($asu['sunday']==1){
            if($asu['class1']==1){ $course[15] .= '第一节;'; }
            if($asu['class2']==1){ $course[15] .= '第二节;'; }
            if($asu['class3']==1){ $course[15] .= '第三节;'; }
            if($asu['class4']==1){ $course[15] .= '第四节;'; }
            if($asu['class5']==1){ $course[15] .= '晚自习;'; }
        }else{
            $course[15]='';
        }

        if( $course[2]==0){
            $course[2] = '必修';
        }else{
            $course[2] = '选修';
        }

        $courses [] = $course;
    }

    return $courses;
}



function  Action_filter($id) {
    $html = "";
    $html .= '&nbsp;' . link_button ( 'edit.gif', 'Edit', 'syllabus_edit.php?syllabus_id=' . $id, '90%', '70%', false);
    $html .= '&nbsp;' . confirm_href ( 'delete.gif', '您确定要执行该操作吗？', 'Delete', 'syllabus_list.php?delete_syllabus=' . $id );
    return $html;
}

//处理批量操作
if (isset ( $_POST ['action'] )) {
    switch ($_POST ['action']) {
        // 批量删除课程
        case 'delete_syllabus' :
            $deleted_syllabus_count = 0;
            $syllabus_codes = getgpc("syllabus");
            if (count ( $syllabus_codes ) > 0) {
                foreach ( $syllabus_codes as $index => $code ) {
                    echo $code;
                    $sql = "DELETE FROM `vslab`.`syllabus` WHERE code='" . escape ( $code ) . "'";
                    api_sql_query ( $sql, __FILE__, __LINE__ );

                    $log_msg = get_lang ( '删除课程表' ) . "code=" . $code;
                    api_logging ( $log_msg, 'COURSE', 'DelCourseInfo' );
                   // if (CourseManager::delete_syllabus (getgpc ( "code" ), false )) $deleted_syllabus_count ++;
                }
            }
            echo count ( $syllabus_codes );
            Display::display_msgbox ( get_lang ( 'OperationSuccess' ), 'main/admin/syllabus/syllabus_list.php' );

//            if (count ( $syllabus_codes ) == $deleted_syllabus_count) {
//                Display::display_msgbox ( get_lang ( 'OperationSuccess' ), 'main/admin/syllabus/syllabus_list.php' );
//
//            } else {
//                Display::display_msgbox ( '某些课程表没有成功删除,可能原因是你没有删除的权限!', 'main/admin/syllabus/syllabus_list.php', 'warning' );
//            }
           // break;
    }
}

//处理删除课程
if (isset ( $_GET ['delete_syllabus'] )) {
    $id = getgpc('delete_syllabus');
    $sql = "DELETE FROM `vslab`.`syllabus` WHERE `syllabus`.`id` = {$id}";
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
}

$tool_name = get_lang ( 'SyllabusList' );
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( $tool_name );

$form = new FormValidator ( 'search_simple', 'get', '', '', null, false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:160px", 'class' => 'inputText','id'=>'searchkey','value'=>'输入搜索关键词' ) );

$sql = "SELECT category_code,count(*) FROM `vslab`.`syllabus` GROUP BY category_code";
$category_cnt = Database::get_into_array2 ( $sql );

$category_tree = $objCrsMng->get_all_categories_tree ( TRUE );
$cate_options [""] = "---所有分类---";
foreach ( $category_tree as $item ) {
   // $cate_name = $item ['name'] . (($category_cnt [$item ['id']]) ? "&nbsp;(" . $category_cnt [$item ['id']] . ")" : "");
    $cate_name = $item ['name'] . (($category_cnt [$item ['id']]) ? "&nbsp;" : "");
    $cate_options [$item ['id']] = str_repeat ( "&nbsp;&nbsp;&nbsp;&nbsp;", intval ( $item ['level'] ) + 1 ) . trim ( $cate_name );
}
$form->addElement ( 'select', 'category_id', get_lang ( 'CourseCategory' ), $cate_options, array ('style' => 'min-width:150px;height:23px;border: 1px solid #999;' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'SearchFilter' ), 'class="inputSubmit"' );



//end

if (isset ( $_GET ['keyword'] ) && is_not_blank ( $_GET ['keyword'] )) $parameters ['keyword'] = getgpc ( 'keyword' );
if (isset ( $_GET ['category_id'] ) && is_not_blank ( $_GET ['category_id'] )) $parameters ['category_id'] = getgpc ( 'category_id' );

$table = new SortableTable ( 'syllabus', 'get_number_of_syllabus', 'get_syllabus_data', 2, NUMBER_PAGE );
$table->set_additional_parameters ( $parameters );
$idx =0;
//$table->set_header ( $idx ++, '', false );
$table->set_header ( $idx ++, get_lang ( 'CourseTitle' ), false, null );
$table->set_header ( $idx ++, get_lang ( '课程类别' )  , false, null , array ('style' => 'width:8%' ));
$table->set_header ( $idx ++, get_lang ( '类型' ), false, null, array ('style' => 'width:3%' ) );
$table->set_header ( $idx ++, get_lang ( '学分' ), false, null, array ('style' => 'width:3%' ) );
$table->set_header ( $idx ++, get_lang ( '学时' ) , false, null, array ('style' => 'width:3%' ) );
$table->set_header ( $idx ++, get_lang ( '讲师' ), false, null ,array('width'=>'4%') );
$table->set_header ( $idx ++, get_lang ( '专业' ), false, null ,array('width'=>'8%') );
$table->set_header ( $idx ++, get_lang ( '年级' ), false, null ,array('width'=>'8%') );
$table->set_header ( $idx ++, get_lang ( '起止周' ), false, null ,array('width'=>'4%') );
$table->set_header ( $idx ++, get_lang ( '周一' ), false, null ,array('width'=>'5%') );
$table->set_header ( $idx ++, get_lang ( '周二' ), false, null ,array('width'=>'5%') );
$table->set_header ( $idx ++, get_lang ( '周三' ), false, null ,array('width'=>'5%') );
$table->set_header ( $idx ++, get_lang ( '周四' ), false, null ,array('width'=>'5%') );
$table->set_header ( $idx ++, get_lang ( '周五' ), false, null ,array('width'=>'5%') );
$table->set_header ( $idx ++, get_lang ( '周六' ), false, null ,array('width'=>'5%') );
$table->set_header ( $idx ++, get_lang ( '周日' ), false, null ,array('width'=>'5%') );

$table->set_header ( $idx ++, get_lang ( '操作' ), false, null ,array('width'=>'5%') );
//$table->set_header ( $idx ++, get_lang ( 'Delete' ), false, null ,array('width'=>'30px') );
//$table->set_form_actions ( array ('delete_syllabus' => get_lang ( '删除所选课程表' ) ), 'syllabus' );
$table->set_column_filter ( 0, 'course_filter' );
$table->set_column_filter ( 16, 'Action_filter' );


//by changzf
//echo '<div class="actions">';
//$form->display ();
//echo '</div>';
////end

//Display::display_footer ( TRUE );
?>
<aside id="sidebar" class="column course open">
    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; 
        <a href="<?=URL_APPEDND;?>/main/admin/course/course_list.php">课程管理</a> &gt; 课程表管理</h4>
    <div class="managerSearch">
        <?php $form->display ();?>
        <span class="searchtxt right">
        <?php
        $sysidfile="/etc/lessonnum";
        $num=file_get_contents($sysidfile);
        if(!$num){
            $num=40;
        }
        $number = "select count(code) from course where description12='1';";
        $count = Database::getval ( $number, __FILE__, __LINE__ );


        $num+=0;
        $count+=0;
        if( $num < $count ){
            echo '&nbsp;&nbsp;' . link_button ( 'edu_miscellaneous_small.gif', '新建课程表', 'syllabus_add.php', '90%', '90%' );
        }

        ?>
		</span>
    </div>
    <article class="module width_full hidden">
        <form name="form1" method="post" action="">
            <table cellspacing="0" cellpadding="0" class="p-table">
               <?php  $table->display ();?>

            </table>
        </form>
    </article>
</section>
</body>
</html>
