<?php
/**
 * Created by JetBrains PhpStorm.
 * User: chang
 * Date: 12-12-2
 * Time: 下午8:14
 * To change this template use File | Settings | File Templates.
 */
$language_file = 'admin';
$cidReset = true;

include_once ("../inc/global.inc.php");
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");
$redirect_url = 'main/exam/exam_list.php';


if(mysql_num_rows(mysql_query("SHOW TABLES LIKE exam_type"))!=1){
    $sql_insert ="CREATE TABLE IF NOT EXISTS `exam_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `name` varchar(128) CHARACTER SET utf8 NOT NULL COMMENT '试卷名称',
  `desc` text CHARACTER SET utf8 NOT NULL COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='试卷表' AUTO_INCREMENT=0 ;";
    api_sql_query ( $sql_insert,__FILE__, __LINE__ );
}
function edit_filter($id, $url_params) {
    $result = "";
    global $_configuration, $root_user_id;
  //  if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result .= '&nbsp;&nbsp;&nbsp;' . link_button ( 'edit.gif', 'Edit', 'exam_edit.php?action=edit&ids='.$id, '90%', '70%', FALSE );
   // }
    return $result;
}
function xm_filter($id) {
    $result = "";
    $result .= Database::getval("select count(id) FROM  `exam_main` where type=".$id,__FILE__,__LINE__);
    return $result;
}
function allExam_filter($id) {
    $result = "";
    $result .= "<a href='../exercice/exercices.php?type={$id}'><img src='../../themes/img/questionsdb.gif' width='24' height='24'/></a>";
    return $result;
}
function delete_filter($id, $url_params) {
    $result = "";
    global $root_user_id;
  //  if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result .= '&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'exam_list.php?action=delete&id=' . $id );
  //  }
    return $result;
}
if (isset ( $_GET ['action'] ) && $_GET ['action']=='delete') {
                $id = intval(getgpc('id'));
    //delete exam_main
                $sql = "DELETE FROM `vslab`.`exam_main` WHERE `exam_main`.`type` = ".$id;
                $results = api_sql_query ( $sql, __FILE__, __LINE__ );

                $s='select count(id) from `exam_main` where `type`='.$id;
                $count_exam=Database::getval($s,__FILE__,__LINE__);
                if($count_exam==0){
    //delete exam_type
                    $sql = "DELETE FROM `vslab`.`exam_type` WHERE `exam_type`.`id` = {$id}";
                    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                }
                tb_close ( "exam_list.php" );
}


function get_sqlwhere() {
    global $restrict_org_id, $objCrsMng;
    $sql_where = "";

    $g_keyword=  getgpc('keyword');
    if($g_keyword=='输入搜索关键词'){
        $g_keyword='';
    }
    if (is_not_blank ( $g_keyword )) {
        $keyword = Database::escape_string ( $_GET ['keyword'], TRUE );
        $sql_where .= " AND (id LIKE '%" . trim ( $keyword ) . "%'
        or name  LIKE '%" . trim ( $keyword ) . "%'
        or description  LIKE '%" . trim ( $keyword ) . "%')";
    }

//    if (is_not_blank ( $_GET ['id'] )) {
//        $sql_where .= " AND id=" . Database::escape ( getgpc ( 'id', 'G' ) );
//    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}
function status_filter($enable){
    $result ='';
    if($enable=='1'){
        $result.='开启';
    }else{
        $result.='关闭';
    }
    return $result;
}
function get_number_of_exam() {
    $exam = Database::get_main_table ( exam_type );
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $exam;
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    //echo $sql;exit;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}

function get_exam_data($from, $number_of_items, $column, $direction) {
    $exam_type = Database::get_main_table ( exam_type );
    $sql = "select id,name,description,id,enable,id,id,id  FROM  $exam_type ";

    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;

    // $sql .= " ORDER BY col$column $direction ";
    $sql .= " LIMIT $from,$number_of_items";
//echo $sql;
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $vm= array ();

    while ( $vm = Database::fetch_row ( $res) ) {
        $vms [] = $vm;
    }
    return $vms;
}

$tool_name = get_lang ( 'CourseList' );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
// Display::display_header ( $tool_name );
include ('../inc/header.inc.php');


$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" ) . "/" . get_lang ( "OfficialCode" );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:60%", 'class' => 'inputText', 'title' => $keyword_tip,'id'=>'searchkey','value'=>'输入搜索关键词' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );

//by changzf

$table = new SortableTable ( 'exam', 'get_number_of_exam', 'get_exam_data',2, NUMBER_PAGE  );
$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++,get_lang ( '序号' ), false, null, array ('style' => 'width:4%' ));
$table->set_header ( $idx ++, get_lang ( '竞赛名称' ), false, null, array ('style' => 'width:25%' ));
$table->set_header ( $idx ++, get_lang ( '描述' ), false, null, array ('style' => 'width:25%' ));
$table->set_header ( $idx ++, get_lang ( '考试项目' ), false, null, array ('style' => 'width:10%' ));
$table->set_header ( $idx ++, get_lang ( '考场状态' ), false, null, array ('style' => 'width:10%' ));
$table->set_header ( $idx ++, get_lang ( '所有考卷' ), false, null, array ('style' => 'width:10%' ));
$table->set_header ( $idx ++, '编辑', false, null, array ('style' => 'width:8%;text-align:center' ) );
$table->set_header ( $idx ++, '删除', false, null, array ('style' => 'width:8%;text-align:center' ) );

//$table->set_form_actions ( array ('deletes' => '删除所选项' ), 'labs' );
//$table->set_column_filter ( 2, 'desc_filter' );
$table->set_column_filter ( 3, 'xm_filter' );
$table->set_column_filter ( 4, 'status_filter' );
$table->set_column_filter ( 5, 'allExam_filter' );
$table->set_column_filter ( 6, 'edit_filter' );
$table->set_column_filter ( 7, 'delete_filter' );



//Display::display_footer ( TRUE );


if($platform==3){
$nav='exercices';
}else{
$nav='exercice';
}
?>
<aside id="sidebar" class="column exercices open">
        <div id="flexButton" class="closeButton close">
        </div>
</aside>
<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt;考试管理 </h4>
    <div class="managerSearch">
        <span class="searchtxt right">
        <?php echo '&nbsp;&nbsp;' . link_button ( 'create.gif', '新增', 'exam_add.php', '90%', '70%' );?>
        </span>
        <?php $form->display ();?>
    </div>
    <article class="module width_full hidden">
       <?php $table->display ();?>
    </article>
</section>
</body>
</html>

