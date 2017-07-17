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
//api_protect_admin_script ();
//if (! isRoot ()) api_not_allowed ();
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");
$redirect_url = 'main/exam/exam_list.php';
include_once ('cls.project.php');
$objProject = new Project ();

 
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
                $id =  intval(getgpc('id'));
               
                $sql = "DELETE FROM  `project` WHERE `id` = ".$id;
                api_sql_query ( $sql, __FILE__, __LINE__ );

//                $s='select count(id) from `exam_main` where `type`='.$id;
//                $count_exam=Database::getval($s,__FILE__,__LINE__);
//                if($count_exam==0){
//    //delete exam_type
//                    $sql = "DELETE FROM `monitor`.`exam_type` WHERE `exam_type`.`id` = {$id}";
//                    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
//                }
             //   tb_close ( "exam_list.php" );
}


function get_sqlwhere() {
    global $restrict_org_id, $objCrsMng;
    $sql_where = "";
$get_keyword=  getgpc('keyword');
    if($get_keyword=='输入搜索关键词'){
       $get_keyword='';
    }
    if (is_not_blank ( $_GET ['keyword'] )) {
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
Display::display_header ( $tool_name );



$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" ) . "/" . get_lang ( "OfficialCode" );
//$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:60%", 'class' => 'inputText', 'title' => $keyword_tip,'id'=>'searchkey','value'=>'输入搜索关键词' ) );
//$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );
 
$table_header [] = array (get_lang ( "序号" ) );
$table_header [] = array (get_lang ( "名称" ) );
$table_header [] = array (get_lang ( "检查项" ) );
$table_header [] = array (get_lang ( "等级1" ) );
$table_header [] = array (get_lang ( "等级2" ) );
$table_header [] = array (get_lang ( "等级3" ) );
$table_header [] = array (get_lang ( "等级4" ) );
$table_header [] = array (get_lang ( "发布" ) );
//$table_header [] = array (get_lang ( "UniqueSelect" ) );
//$table_header [] = array (get_lang ( "MultipleSelect" ) );
//$table_header [] = array (get_lang ( "TrueFalseAnswer" ) );
//$table_header [] = array (get_lang ( "FreeAnswer" ) );
//$table_header [] = array (get_lang ( "DisplayOrder" ), false, null, array ('width' => '80' ) );
//
$table_header [] = array (get_lang ( "Actions" ), false, null, array ('width' => '120' ) );

$sql = "SELECT * FROM project";
			

$result = api_sql_query ( $sql, __FILE__, __LINE__ );
while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
    $arr[]=$row;
    
}

$cnt = count($poolset);
$i=1;


foreach ( $arr as $va ) {
    $row = array ();
    $row [] = $i;
    $row [] = $va['name'];
   $row [] = $objProject->get_project_count ( $va['id']);
    $row [] = $objProject->get_project_count ( $va ['id'] ,1);
    $row [] = $objProject->get_project_count ( $va ['id'] ,2);
    $row [] = $objProject->get_project_count ( $va ['id'] ,3);
    $row [] = $objProject->get_project_count ( $va ['id'] ,4);
//    $row [] = $objQuestionPool->get_pool_question_count ( $pool_set ['id'], 2 );
//    $row [] = $objQuestionPool->get_pool_question_count ( $pool_set ['id'], 3 );
//    $row [] = $objQuestionPool->get_pool_question_count ( $pool_set ['id'], 6 );
//
//    $row [] = $pool_set ['display_order'];
    
    $i++; 
	if($va['release']==0){
         $row[]="<a class='re' href='#' id='".$va['id']."' title='发布评估'><img src='".api_get_path ( WEB_IMAGE_PATH ) ."wrong.gif'></a>";
     }else{
         $row[]="<img src='".api_get_path ( WEB_IMAGE_PATH ) ."right.gif'>"; 
     }
    
    
    $action = '&nbsp;&nbsp;<a href="method_list.php?project_id=' . $va ["id"] . '">' . Display::return_icon ( 'questionsdb.gif', get_lang ( '添加评估项' ), array ('style' => 'vertical-align: middle;', 'width' => 24, 'height' => 24 ) ) . '</a>&nbsp;';
    if (isRoot ()) {
        $action .= '&nbsp;&nbsp;' . link_button ( 'edit.gif', 'Edit', 'project_add.php?action=edit_project&id=' . $va ['id'], 220, 600, FALSE );
        $href = 'project.php?action=delete&amp;id=' . $va ["id"] ;
        $action .= '&nbsp;&nbsp;' . confirm_href ( 'delete.gif', '您确定要执行该操作吗？', 'Delete', $href );
    }
    $row [] = $action;
    $table_data [] = $row;
}


?>
<script>
$(function(){
  $('.re').click(function(){
      var pid=$(this).attr('id');
      
      $.post("project_add.php","pid="+pid,function(a){
          if(a==1){
              //pid.parent().html("<img src='right.gif'>")
              location.reload();
          }
          
      })
      
  })  
    
})
</script>

<aside id="sidebar" class="column evaluate open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
     
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt;评估 </h4>
    <div class="managerSearch">
        <span style="float:right" class="searchtxt rright">
        <?php echo  link_button ( 'create.gif', '新增', 'project_add.php?action=add_project', '90%', '70%' ).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' ;?>
        </span>
        <?php $form->display ();?>
    </div>
    <article class="module width_full hidden">
       <?php  echo Display::display_table ( $table_header, $table_data );
                ?>
    </article>
</section>
</body>
</html>
