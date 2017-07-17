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
$_SESSION['platfrom_types']= $platform;
$username=$_SESSION['_user']['username'];
$times=date('YmdHis',time());
$sel=$_POST['auto-id-rTOGAi3MiQOM7HrB'];

$user_id = api_get_user_id ();
$course_code = api_get_course_code ();
$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$view_course_user = Database::get_main_table ( VIEW_COURSE_USER );
$tbl_assignment = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_MAIN );
$my_course_codes = CourseManager::get_user_subscribe_courses_code ( $user_id );
$offset = getgpc ( "offset", "G" );
$hw_status = (empty ( $_GET ['hw_status'] ) ? 'all' : getgpc ( "hw_status", "G" ));

$sql_table = " FROM " . $tbl_assignment . " as t1 LEFT  JOIN  $tbl_course AS t2 ON t1.cc=t2.code";

function get_sqlwhere() {
    $sql_where = "";
    if (is_not_blank ( $_GET ['keyword'] )) {
        if($_GET ['keyword']=='输入搜索关键词'){
            $_GET ['keyword']='';
        }
        $keyword = Database::escape_string ( $_GET ['keyword'], TRUE );
        $sql_where .= " AND (id LIKE '%" . trim ( $keyword ) . "%'
                             OR report_name LIKE '%" . trim ( $keyword ) . "%'
                             OR user LIKE '%" . trim ( $keyword ) . "%'
                             OR description LIKE '%" . trim ( $keyword ) . "%'
                             OR score LIKE '%" . trim ( $keyword ) . "%'
                             OR submit_date LIKE '%" . trim ( $keyword ) . "%'
                             OR screenshot_file LIKE '%" . trim ( $keyword ) . "%'
                             OR code LIKE '%" . trim ( $keyword ) . "%' )";
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}
if (is_not_blank ( $sel )) {
	$keyword = Database::escape_str ( urldecode ($sel ), TRUE );   
	$sql_where.= "  ( `report_name`  LIKE '%" . trim($keyword) . "%'  or `user` LIKE '%" . trim($keyword) . "%' or `code` LIKE '%" . trim($keyword) . "%' ) ";
	$param .= "&keyword=" . urlencode ( $keyword );
}
//$sqlwhere = " WHERE " . implode ( ' AND ', $condition );
$sql1 = "SELECT COUNT(id) from `report`  where `user`='" .$username."' ";
if($_SESSION['platfrom_types']<1 OR $_SESSION['platfrom_types']>3){
    $sql1 .= " AND type=1 ";
}
if ($sql_where) $sql1 .= " AND " . $sql_where;
$total_rows = Database::getval ( $sql1, __FILE__, __LINE__ );

$sql = "select `id`,`report_name`,`user`,`code`,`submit_date`,`screenshot_file`,`description`,`status` FROM  `report`   where `user`='" .$username."' ";

if ($sql_where) $sql .= " AND " . $sql_where;
if($_SESSION['platfrom_types']<1 OR $_SESSION['platfrom_types']>3){
    $sql .= " AND type=1 ";
}
$sql .= " ORDER BY id ";
$sql .= " LIMIT " . (empty ( $offset ) ? 0 : $offset) . ",10";
$data_list = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );

$arr= array ();

while ( $arr = Database::fetch_row ( $data_list) ) {
	$arrs [] = $arr;
}
$rtn_data=array ("data_list" => $arrs, "total_rows" => $total_rows );
$personal_course_list = $rtn_data ["data_list"];
$total_rows = $rtn_data ["total_rows"];

//$total_rows = intval(count($data_list));
//echo '<pre>';var_dump($data_list);echo '</pre>';
$url = WEB_QH_PATH . "labs_report.php?" . $param;
//echo $url;
$pagination_config = Pagination::get_defult_config ( $total_rows, $url, NUMBER_PAGE );
$pagination = new Pagination ( $pagination_config );

$interbreadcrumb [] = array ("url" => 'index.php', "name" => "首页" );
//$interbreadcrumb [] = array ("url" => 'labs_report.php', "name" => '实验报告' );
$interbreadcrumb [] = array (  "name" => "我的实验报告" );
//$nameTools="我的课程";
display_tab ( TAB_LEARNING_CENTER );

$action=htmlspecialchars($_GET ['action']);
if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id=htmlspecialchars($_GET ['delete_id']);
            if ( isset($delete_id)){
                $file=Database::getval('select `screenshot_file` from `report` where `id`='.$delete_id,__FILE__,__LINE__);

                    $sql = "DELETE FROM `vslab`.`report` WHERE `report`.`id`='" . $delete_id . "'";
                    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                    if($result){
                        $get_files=URL_ROOT.'/www/lms/storage/report/'.$_SESSION['_user']['username'].'/'.$file;
                        unlink($get_files);
                    }

                    $redirect_url = "labs_report.php";
                    tb_close ( $redirect_url );
            }
            break;
        case 'submit_report' :
            $submit_id=htmlspecialchars($_GET ['id']);
            if ( isset($submit_id)){
                $sql = "UPDATE  `vslab`.`report` SET  `status` =  '1'";
                if($_SESSION['platfrom_types']<1 OR $_SESSION['platfrom_types']>3){
                    $sql.=",`type` =  '1'";
                }
               
                $sql.=" WHERE  `report`.`id` =".$submit_id;
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                if($result){
                   //   echo '提交成功！';
                }

                $redirect_url = "labs_report.php";
                tb_close ( $redirect_url );
            }
            break;
    }
}
if($platform==3){
    $navigate='exam';
}else{
    $navigate='labsreport';
}
?>


<div class="clear"></div>
<div class="m-moclist">
    <div class="g-flow" id="j-find-main">
          <div class="b-30"></div> 
          <div class="g-container f-cb">
        <div class="g-sd1 nav">
            <div class="m-sidebr" id="j-cates">
                <ul class="u-categ f-cb">
                    <li class="navitm it f-f0 f-cb first cur" data-id="-1" data-name="报告管理" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1"  style="background-color:#13a654;color:#FFF" title="报告管理">报告管理</a>
                    </li>
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="我的实验报告" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="我的实验报告" href="labs_report.php" style="color:green;font-weight:bold">我的实验报告</a>
                    </li> 
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="我的实验图片录像" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="我的实验图片录像" href="course_snapshot_list.php" >我的实验图片录像</a>
                    </li>
                     <li class="navitm it f-f0 f-cb haschildren couse-mess" data-id="-1" data-name="调查问卷" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="调查问卷" href="survey.php" >调查问卷</a>
                    </li>

                </ul>
            </div>
        </div>
                  
        <div class="g-mn1" > 
            <div class="g-mn1c m-cnt" style="display:block;">
             
                <div class="top f-cb j-top">
                    <h3 class="left f-thide j-cateTitle title">
                    <span class="f-fc6 f-fs1" id="j-catTitle">我的实验报告</span>
                    </h3>
                    <div class="j-nav nav f-cb"> 
                        <div id="j-tab">  <!--  class="sub-simple u-course-title"-->
                          <?php  echo link_button ( 'create.gif', '添加实验报告', 'report_test.php', '80%', '90%', FALSE );   ?>
                            <!--//<a class="u-btn u-btn-sm u-btn-left u-btn-active" title="添加实验报告" href="report_test.php" target="_blank"><img src="./images/create.gif"></a>-->
                        </div>
                    </div>
                </div>
        <?php if (is_array ( $data_list ) && $data_list) { ?>
        <div class="j-list lists" id="j-list"> 
            <div class="u-content">
                <h3 class="sub-simple u-course-title"></h3>
            <table cellspacing="0" border="0" width="100%" class="tbl_course"> 
                <tr class="u-course-time">
                    <th width="8%" height=50 >&nbsp;&nbsp;序号</th>
                    <th width="15%">实验报告名称</th>
                    <th width="15%">学习课程</th> 
                    <th width="8%">用户名</th>
                    <th width="10%">时间</th>
                    <th width="10%">提交文件</th>
                    <th width="15%">描述</th>
                    <th width="10%">状态</th>
                    <th width="10%">操作</th>
                </tr>
                <tr>
                     <td colspan="10"><div  class="sub-simple u-course-title"></div> </td>
                                
                <?php
                $i=1;
                foreach ( $data_list as $item ) {
                    $item_id=$item ['id'];
                    ?>
                    <tr class="u-course-time">
                        <td height=50>&nbsp;&nbsp;<?= $i++ ?></td>
                        <!--<td>&nbsp;<img src="images/lab.png" width="32" height="32" style="vertical-align: middle;"></td>-->
                        <td><?= $item ['report_name'] ?></td>
                        <?php
                        if($platform!=3){
                            echo '<td>'.$item ['code'].'</td>';
                        }
                        ?>

                        <td ><?= $item ['user'] ?></td>
                        <td ><?= $item ['submit_date'] ?></td>
                        <td ><?= $item ['screenshot_file'] ?></td>
                        <td ><?= $item ['description'] ?></td>
                        <td ><?php
                            if($item ['status']==1){
                                echo '已提交';
                            }else{
                                echo '未提交';
                            } ?></td>
                        <td>
                            <?php
                            //判断查看、编辑页面的平台
                            if($platform==3){
                                $t_var='&report_type='.$platform;
                            }
                           $status= Database::getval("select `status` from `report` where `id`='".$item ['id']."'",__FILE__,__LINE__);
                            if($status==1){
                                echo link_button ( 'statistics.gif', '查看实验报告', 'report_check.php?action=check&id='.$item ['id'].$t_var, '80%', '80%', FALSE );
                            }else{
                                $sql="select `code` from `course` where `title` = '".$item ['code']."'";
                                $cidReq=Database::getval($sql,__FILE__,__LINE__);
                                echo '<a href="labs_report.php?action=submit_report&id='.$item_id.'" title="提交实验报告结果"><img src="../../themes/img/folder_up.gif" alt="提交实验报告结果" title="提交实验报告结果" style="vertical-align: middle;"></a>';
                                echo link_button ( 'exercise22.png', '编辑实验报告', 'report_test.php?cidReq='.$cidReq, '80%', '90%', FALSE ); 
                                //echo '<a href="report_test.php?cidReq='. $cidReq .'" title="编辑实验报告"><img src="../../themes/images/exercise22.png"></a>';
                                echo confirm_href ( 'delete.gif', '你确定执行此操作？', 'Delete', 'labs_report.php?action=delete&delete_id=' . $item ['id'] );
                            }
                            ?></td>
                    </tr>
                                <tr>
                                        <td colspan="10"><div  class="sub-simple u-course-title"></div> </td>
                                </tr>
                    
                    <?php
                }
                
                ?>
            </table>
                <div class="page">
                    <ul class="page-list">
                        <li class="page-num">总计<?=$total_rows?>条记录</li>
                        <?php
                        echo $pagination-> create_links ();
                        ?>
                    </ul>
                </div>
        

        <?php
    } else {
        ?>
        <div class="error" >没有相关实验报告</div>
        <?php
    }
        ?>

    <!--</article>-->



<!--</section>-->


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php
        include_once './inc/page_footer.php';
?>
</body>
</html>
