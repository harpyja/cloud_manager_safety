<?php
/**----------------------------------------------------------------

liyu: 2011-10-20
 *----------------------------------------------------------------*/
$cidReset = true;
include_once ("inc/app.inc.php");
include_once ("../../main/inc/global.inc.php");
include_once ('../../main/assignment/assignment.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileDisplay.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'tablesort.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . "export.lib.inc.php");
include_once (api_get_path ( SYS_CODE_PATH ) . 'document/document.inc.php');

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
include_once ("inc/page_header.php");
$username=$_SESSION['_user']['username'];
$times=date('YmdHis',time());


$user_id = api_get_user_id ();
//$course_code = api_get_course_code ();
$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$view_course_user = Database::get_main_table ( VIEW_COURSE_USER );
$tbl_assignment = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_MAIN );
$my_course_codes = CourseManager::get_user_subscribe_courses_code ( $user_id );
$offset = getgpc ( "offset", "G" );$offset=(int)$offset;
$hw_status = (empty ( $_GET ['hw_status'] ) ? 'all' : getgpc ( "hw_status", "G" ));

$sql_table = " FROM " . $tbl_assignment . " as t1 LEFT  JOIN  $tbl_course AS t2 ON t1.cc=t2.code";


if( $_GET['action']=='view' && $_GET['ids']!=''){

    $sql = "SELECT `id`,`report_name`,`user`, `screenshot_file` FROM `reporting_info` WHERE id=" . intval(getgpc('ids'));
    $document_info = Database::fetch_one_row ( $sql, FALSE, __FILE__, __LINE__ );
    $files = URL_ROOT.'/www/lms/storage/reporting_info/'.$document_info['user'].'/'.$document_info['screenshot_file'];
    if (file_exists($files)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($files));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($files));
        ob_clean();
        flush();
        readfile($files);
        exit;
    }
}
function get_sqlwhere() {
    $sql_where = "";
    if (is_not_blank ( $_GET ['keyword'] )) {
        if($_GET ['keyword']=='输入搜索关键词'){
            $_GET ['keyword']='';
        }
        $keyword = Database::escape_string ( $_GET ['keyword'], TRUE );
        $sql_where .= " AND (id LIKE '%" . trim ( $keyword ) . "%' OR report_name LIKE '%" . trim ( $keyword ) . "%' OR user LIKE '%" . trim ( $keyword ) . "%'
                             OR description LIKE '%" . trim ( $keyword ) . "%'
                             OR score LIKE '%" . trim ( $keyword ) . "%' OR submit_date LIKE '%" . trim ( $keyword ) . "%'
                             OR screenshot_file LIKE '%" . trim ( $keyword ) . "%'   )";
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}
if (isset ( $_GET ["keyword"] ) && is_not_blank ( $_GET ["keyword"] )) {
    $keyword = escape ( urldecode ( $_GET ['keyword'] ), TRUE );
    $condition [] = " id LIKE '%" . $keyword . "%'";
    $param .= "&keyword=" . urlencode ( $keyword );
}
//$sqlwhere = " WHERE " . implode ( ' AND ', $condition );
$sql1 = "SELECT COUNT(id) from `reporting_info`  where `type` = 2 and `user`='" .$username."' ";
$sql_where = get_sqlwhere ();
if ($sql_where) $sql1 .= " AND " . $sql_where;
$total_rows = Database::getval ( $sql1, __FILE__, __LINE__ );

$sql = "select `id`,`report_name`,`user`,`submit_date`,`screenshot_file`,`description`,`key`,`status` FROM `vslab`.`reporting_info`   where  `type` = 2 and `user`='" .$username."' ";
        
if ($sql_where) $sql .= " AND " . $sql_where;
$sql .= " ORDER BY id ";
$sql .= sql_limit ( $offset, NUMBER_PAGE );

$data_list = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
//echo '<pre>';var_dump($data_list);echo '</pre>';
$url = WEB_QH_PATH . "counterwork_report.php?" . $param;
$pagination_config = Pagination::get_defult_config ( $total_rows, $url, NUMBER_PAGE );
$pagination = new Pagination ( $pagination_config );

$interbreadcrumb [] = array ("url" => 'index.php', "name" => "首页" );
//$interbreadcrumb [] = array ("url" => 'labs_report.php', "name" => '分组对抗报告' );
$interbreadcrumb [] = array (  "name" => "我的分组对抗报告" );
//$nameTools="我的课程";
display_tab ( TAB_LEARNING_CENTER );

$action=htmlspecialchars($_GET ['action']);
if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id=htmlspecialchars($_GET ['delete_id']);$delete_id=intval($delete_id);
            if ( isset($delete_id)){
                $file=Database::getval('select `screenshot_file` from `reporting_info` where `id`='.$delete_id,__FILE__,__LINE__);

                    $sql = "DELETE FROM `vslab`.`reporting_info` WHERE `reporting_info`.`id`='" . $delete_id . "'";
                    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                    if($result){
                        $get_files=URL_ROOT.'/www/'.URL_APPEDND.'/storage/report/counterwork/'.$_SESSION['_user']['username'].'/'.$file;
                        unlink($get_files);
                    }

                    $redirect_url = "counterwork_report.php";
                    tb_close ( $redirect_url );
            }
            break;
        case 'submit_report' :
            $submit_id=intval(htmlspecialchars($_GET ['id']));
            if ( isset($submit_id)){
                $sql = "UPDATE  `vslab`.`reporting_info` SET  `status` =  '1' WHERE  `reporting_info`.`id` =".$submit_id;
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                if($result){
                   //   echo '提交成功！';
                }

               
                tb_close ( 'counterwork_report.php' );
            }
            break;
    }
}
        
?>
<aside id="sidebar" class="column open control">

    <div id="flexButton" class="closeButton close">

    </div>
</aside><!-- end of sidebar -->

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<?=display_interbreadcrumb ( $interbreadcrumb, null )?></h4>
    <article class="module width_full hidden">
        <header>
            <h3><form>
                <input type="text" name="keyword" value="输入搜索关键词" id="searchkey" onfocus="this.select();" />
                <input type="submit" value=" 搜 索 " class="submit alt_btn"></form>
            </h3>
            <div class="submit_link">
                <form action="assignment_list.php" method="get">

                    <?php
                    echo '<span style="color:black"><b>'.link_button ( 'create.gif', '添加分组对抗报告', 'reporting_add.php?type=2', '70%', '60%' )."</b></span>";
                    ?>
                </form>

            </div>
        </header>
 
        <?php if (is_array ( $data_list ) && $data_list) {?>
        <div class="module_content">
            <table cellspacing="0" cellpadding="0" class="p-table">
                <tr>
                    <th>序号</th>
                    <th>分组对抗报告名称</th>
<!--                    <th>学习课程</th>-->
                     
                    <th>时间</th>
                    <th>提交文件</th>
                    <th>描述</th>
                    <th>KEY</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
                <?php
                foreach ( $data_list as $item ) {
                    $item_id=$item ['id'];
                    ?>
                    <tr>
                        <td width="5%"><?= $item ['id'] ?></td>
                        <td width="15%"><?= $item ['report_name'] ?></td>
                        <td width="10%"><?= $item ['submit_date'] ?></td>
                        <td width="15%"><?= $item ['screenshot_file'] ?>
<!--                            <a href="../../storage/report/--><?//= $item ['user'] ?><!--/--><?//= $item ['screenshot_file'] ?><!--" target="_blank">--><?//= $item ['screenshot_file'] ?><!--</a>-->
<!--                            <a href="labs_report.php?action=view&ids=--><?//= $item ['id'] ?><!--" >-->
<!--                                <span style="float:left">--><?//= $item ['screenshot_file'] ?><!--</span>-->
<!--                                <img src="../../themes/img/filesave.gif" style="float:right;" alt="下载" title="下载" width="16" height="16"></a>-->
                        </td>
                        <td ><?= $item ['description'] ?></td>
                        <td width="5%"><?= $item ['key'] ?></td>
                        <td width="5%"><?php
                            if($item ['status']==1){
                                echo '已提交';
                            }else{
                                echo '未提交';
                            } ?></td>
                        <td width="8%">
                            <?php
                           $status= Database::getval("select `status` from `reporting_info` where `id`='".$item ['id']."'",__FILE__,__LINE__);
                            if($status==1){
                                echo link_button ( 'statistics.gif', '查看分组对抗报告', 'reporting_info.php?action=info&id='.$item ['id'], '70%', '60%', FALSE );
                            }else{
                                echo '<a href="counterwork_report.php?action=submit_report&id='.$item_id.'" title="提交分组对抗报告结果"><img src="../../themes/img/folder_up.gif" alt="提交分组对抗报告结果" title="提交分组对抗报告结果" style="vertical-align: middle;"></a>';
                                echo link_button ( 'exercise22.png', '编辑分组对抗报告结果', 'reporting_edit.php?action=edit&id='.$item ['id'], '70%', '60%', FALSE );
                                echo confirm_href ( 'delete.gif', '你确定执行此操作？', 'Delete', 'counterwork_report.php?action=delete&delete_id=' . $item ['id'] );
                            }
                            ?></td>
                    </tr>
                    <?php
                }
                ?>

            </table>
            <div class="page">
                <ul class="page-list">
                    <li class="page-num">总计<?=$total_rows?>条记录</li>
                    <?php
                    echo $pagination->create_links ();
                    ?>
                </ul>
            </div>
        </div>

        <?php
    } else {
        ?>
        <div class="error" >没有相关分组对抗报告</div>
        <?php
    }
        ?>

    </article>



</section>


</body>
</html>
