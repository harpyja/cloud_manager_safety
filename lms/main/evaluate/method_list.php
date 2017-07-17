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
$pro_id= intval(getgpc ( 'project_id' ));
$_SESSION['project_id']=$pro_id;
$pid=$_SESSION['project_id'];


if (isset ( $_GET ['action'] ) && $_GET ['action']=='delete') {
                $id =  intval(getgpc('id'));
    //delete exam_main
                $sql = "delete from assess where id=$id";
                api_sql_query ( $sql, __FILE__, __LINE__ );
                  $resoult= mysql_affected_rows();
//                $s='select count(id) from `exam_main` where `type`='.$id;
//                $count_exam=Database::getval($s,__FILE__,__LINE__);
                if($resoult>0){
    //delete exam_type
                    $sql = "delete from check_items where assess_id=$id";
                    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                }
                tb_close ( 'method_list.php?project_id='.$pro_id );
}

if (isset ( $_POST ['action'] )) {
    switch ($_POST ['action']) {
        case 'deletes' :
            $documents = getgpc('documents');
            if (count ( $documents ) > 0) {
                foreach ( $documents as $index => $id ) {
                    //delete file
        
                        $sql = "delete from assess where id=$id";
                api_sql_query ( $sql, __FILE__, __LINE__ );
                  $resoult= mysql_affected_rows();
//                $s='select count(id) from `exam_main` where `type`='.$id;
//                $count_exam=Database::getval($s,__FILE__,__LINE__);
                if($resoult>0){
    //delete exam_type
                    $sql = "delete from check_items where assess_id=$id";
                    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                }
        
                    $log_msg = get_lang('删除所选') . "id=" . $id;
                    api_logging ( $log_msg, 'documents', 'documents' );
                }
            }
            break;
    }
}

    

$tool_name = get_lang ( 'CourseList' );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( $tool_name );
function get_sqlwhere() {
    $sql = '';
	if (is_not_blank ( $_GET ['project_id'] )) {
		$sql .= "   pro_id='" . Database::escape_string ( intval( getgpc ( 'project_id', 'G' ) )) . "'";
	}
if (is_not_blank ($pid )) {
		$sql .= "   pro_id='" . $pid;
	}

    if($_GET['keyword']="输入搜索关键字"){$keyword="";}
        else if (is_not_blank ( $_GET ['keyword'] )) {
            $keyword = Database::escape_str ( getgpc ( 'keyword', "G" ), TRUE );
            $sql .= " AND (question_code LIKE '%" . $keyword . "%' OR question LIKE '%" . $keyword . "%')";
            }
	return $sql;
}

function get_number_of_data() {
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM `assess`";
     $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
   return Database::getval ( $sql, __FILE__, __LINE__ );
}




function get_datas($from, $number_of_items, $column, $direction){
 
$sql = "select `id`,`class`,`pro_id`,`check`,`id`,`id` FROM `assess`";

    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= "  where " . $sql_where;

    $sql .= " order by `id`";
    $sql .= " LIMIT $from,$number_of_items";
$result = api_sql_query ( $sql, __FILE__, __LINE__ );
while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
    $arr[]=$row;
    
}
        
//echo $sql;
foreach ( $arr as $va ) {
    $row_render= array ();
   $row_render[]= $va['id'];
    $row_render[] = $va['class'];
    $pro_name=Database::getval ("select name from project where id=".$va['pro_id'], __FILE__, __LINE__ );
    $row_render[] = $pro_name;
    $row_render[] = $va['check'];
    $check_num=Database::getval ("select count(*) from check_items where assess_id=".$va['id'], __FILE__, __LINE__ );
    $row_render[] = $check_num;

 
    $action = "";
    if (isRoot ()) {
        $action .= '&nbsp;&nbsp;' . link_button ( 'edit.gif', 'Edit', 'method_edit.php?action=edit&id=' . $va ['id'], '90%', '70%', FALSE );
        $href = 'method_list.php?action=delete&amp;id=' . $va ["id"].'&amp;project_id='.$va['pro_id'];
        $action .= '&nbsp;&nbsp;' . confirm_href ( 'delete.gif', '确定删除该条记录吗？', 'Delete', $href );
    }
    $row_render[] = $action;
    $table_data [] = $row_render;
    
}
return $table_data;
}

 $parameters = array (  'project_id' => $pro_id );
$table = new SortableTable ( 'method_list', 'get_number_of_data', 'get_datas', 1, NUMBER_PAGE, 'DESC' );
$table->set_additional_parameters ( $parameters );
$header_idx = 0;
$table->set_header ( $header_idx ++, '', false );
$table->set_header ( $header_idx ++, get_lang ( '类别' ), false, null, array ('width' => '10%' ) );
$table->set_header ( $header_idx ++, "当前评估名称", false, null, array ('width' => '20%' ) );
$table->set_header ( $header_idx ++, "检查方法名称", false, null, array ('width' => '20%' ) );
$table->set_header ( $header_idx ++, get_lang ( '检查项数量' ), true, null, array ('width' => '20%' ) );
$table->set_header ( $header_idx ++, get_lang ( 'Actions' ), false, null, array ('width' => '20%' ) );

$table->set_form_actions ( array ('deletes' => '删除所选项' ), 'documents' );
 //$table->set_dispaly_style_navigation_bar(NAV_BAR_BOTTOM);

//Display::display_footer ();



?>


<aside id="sidebar" class="column evaluate open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
     
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt;项目评估 </h4>
    <div class="managerSearch">
        <span class="searchtxt right">
        <?php echo '&nbsp;&nbsp;' . link_button ( 'create.gif', '新建检查方法', "create_method.php?project_id=$pro_id", '90%', '70%' );?>
        </span>
        <?php //$form->display ();?>
    </div>
    <article class="module width_full hidden">
        
       <?php  
       
      $table->display();
                ?>
    </article>
</section>
</body>
</html>
