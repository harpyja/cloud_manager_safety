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
//考卷表不存在时新建
if(mysql_num_rows(mysql_query("SHOW TABLES LIKE exam_type"))!=1){
    $sql_insert ="CREATE TABLE IF NOT EXISTS `exam_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `name` varchar(128) CHARACTER SET utf8 NOT NULL COMMENT '试卷名称',
  `desc` text CHARACTER SET utf8 NOT NULL COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='试卷表' AUTO_INCREMENT=0 ;";
    api_sql_query ( $sql_insert,__FILE__, __LINE__ );
}

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
                OR name LIKE '%" . trim ( $keyword ) . "%'
                OR description LIKE '%" . trim ( $keyword ) . "%' )";
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
$sql1 = "SELECT COUNT(id) from `exam_type`  where 1 ";
$sql_where = get_sqlwhere ();
if ($sql_where) $sql1 .= " AND " . $sql_where;
$total_rows = Database::getval ( $sql1, __FILE__, __LINE__ );
$exam_type = Database::get_main_table ( exam_type );
$sql = "select id,name,description FROM  $exam_type where 1";

if ($sql_where) $sql .= " AND " . $sql_where;
$sql .= " ORDER BY id ";
$sql .= sql_limit ( $offset, NUMBER_PAGE );
$data_list = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
$url = WEB_QH_PATH . "exam_list.php?" . $param;
$pagination_config = Pagination::get_defult_config ( $total_rows, $url, NUMBER_PAGE );
$pagination = new Pagination ( $pagination_config );

$interbreadcrumb [] = array ("url" => 'index.php', "name" => "首页" );
$interbreadcrumb [] = array ("url" => 'exam_result.php', "name" => '考试成绩查询' );
display_tab ( TAB_LEARNING_CENTER );

//导航判断
if($platform==3){
    $nav='exam';
}else{
    $nav='exam-Centre';
}
?>
<aside id="sidebar" class="column open <?=$nav?>">

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
        </header><br>
          <table cellspacing="0" cellpadding="0" class="p-table">
                <tr>
                    <th>序号</th>
                    <th>竞赛名称</th>
                    <th>描述</th>
                    <th>考试项目</th>
                    <th>查看考试成绩</th>
                </tr>
        <?php if (is_array ( $data_list ) && $data_list) {?>
        <div class="module_content">
                <?php
                foreach ( $data_list as $item ) {
                    $item_id=$item ['id'];
                    ?>
                    <tr>
                        <td><?= $item ['id'] ?></td>
                        <td><?= $item ['name'] ?></td>
                        <td><?=$item ['description'] ?></td>
                        <td><?php
                         $q="SELECT COUNT(*) FROM exam_rel_user AS t1,exam_main AS t2 WHERE t1.user_id=".Database::escape ( $user_id )." AND t1.exam_id=t2.id AND t2.active=1 AND t2.type='".$item ['id']."'";
                         echo Database::getval ( $q, __FILE__, __LINE__ );
                        ?></td>
                        <td><a href="exam_result.php?type=<?= $item ['id'] ?>"><img src="../../themes/img/questionsdb.gif" width="24" height="24"></a></td>
                    <?php
                }
                ?>
            <tr><td colspan='10'>
            <div class="page">
                <ul class="page-list">
                    <li class="page-num">总计<?=$total_rows?>条记录</li>
                    <?php
                    echo $pagination->create_links ();
                    ?>
                </ul>
            </div>
            </td></tr>
        </div>

        <?php
    } else {
        ?>
        <tr><td colspan='10' align="center">没有相关竞赛</td></tr>
        <?php
    }
        ?>
</table>
    </article>



</section>


</body>
</html>
