<?php
/**----------------------------------------------------------------

 liyu: 2011-10-20
 *----------------------------------------------------------------*/
$cidReset = true;
include_once ("inc/app.inc.php");
include_once ('../../main/assignment/assignment.lib.php');
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
include_once ("inc/page_header.php");
$user_id = api_get_user_id ();
$course_code = api_get_course_code ();
$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$view_course_user = Database::get_main_table ( VIEW_COURSE_USER );
$tbl_assignment = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_MAIN );
$my_course_codes = CourseManager::get_user_subscribe_courses_code ( $user_id );
$offset = getgpc ( "offset", "G" );
$hw_status = (empty ( $_GET ['hw_status'] ) ? 'all' : getgpc ( "hw_status", "G" ));

$sql_table = " FROM " . $tbl_assignment . " as t1 LEFT  JOIN  $tbl_course AS t2 ON t1.cc=t2.code";

$condition [] = "assignment_type='INDIVIDUAL' and is_published=1";
if ($my_course_codes) $condition [] = Database::create_in ( $my_course_codes, 't1.cc' );
if (isset ( $_GET ["keyword"] ) && is_not_blank ( $_GET ["keyword"] )) {
	$keyword = escape ( urldecode ( getgpc ('keyword') ), TRUE );
	$condition [] = " t1.title LIKE '%" . $keyword . "%'";
	$param .= "&keyword=" . urlencode ( $keyword );
}
if (isset ( $_GET ["keyword2"] ) && is_not_blank ( $_GET ["keyword2"] )) {
	$keyword2 = escape ( urldecode ( getgpc ('keyword2') ), TRUE );
	$condition [] = " t2.title LIKE '%" . $keyword2 . "%'";
	$param .= "&keyword2=" . urlencode ( $keyword2 );
}
if (isset ( $_GET ["hw_status"] ) && $hw_status != "all") {

}
$sqlwhere = " WHERE " . implode ( ' AND ', $condition );
$sql1 = "SELECT COUNT(*) " . $sql_table . $sqlwhere;
$total_rows = Database::getval ( $sql1, __FILE__, __LINE__ );

$sql = "SELECT  t1.id,t1.title, IF(published_time='0000-00-00 00:00:00','',published_time) as published_time,
		t1.creation_time,deadline,is_published,	t1.assignment_type ,t1.cc,t2.title AS course_name " . $sql_table . $sqlwhere;
$sql .= " ORDER BY t1.creation_time desc";
$sql .= sql_limit ( $offset, NUMBER_PAGE );
//echo $sql;
$data_list = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );

$url = WEB_QH_PATH . "assignment_list.php?" . $param;
$pagination_config = Pagination::get_defult_config ( $total_rows, $url, NUMBER_PAGE );
$pagination = new Pagination ( $pagination_config );

$interbreadcrumb [] = array ("url" => 'index.php', "name" => "首页" );
$interbreadcrumb [] = array ("url" => 'learning_center.php', "name" => "学习中心" );
$interbreadcrumb [] = array ("url" => 'assignment_list.php', "name" => "我的课程作业" );
//$nameTools="我的课程";
display_tab ( TAB_LEARNING_CENTER );
?>

<aside id="sidebar" class="column open study-Centre">

    <div id="flexButton" class="closeButton close">

    </div>
</aside><!-- end of sidebar -->

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<?=display_interbreadcrumb ( $interbreadcrumb, null )?></h4>
    <article class="module width_full hidden">
        <header>
            <h3>我的课程作业</h3>
            <div class="submit_link">
                <form action="assignment_list.php" method="get">
                    <span class="link_title">作业名称:</span>
                    <input type="text"  name="keyword"  value="<?=getgpc ( 'keyword' )?>" onfocus="this.select();"  class="searchtext"/>
                    <span class="link_title">课程名称:</span>
                        <input type="text" name="keyword2" value="<?=getgpc ( 'keyword2' )?>" onfocus="this.select();" />

                        <input type="submit" value="搜索"  class="submit alt_btn" />
                </form>

            </div>
        </header>
        <?php if (is_array ( $data_list ) && $data_list) {?>
        <div class="module_content">
            <table cellspacing="0" cellpadding="0" class="p-table">
                <tr>
                    <th class="case-table-title">作业名称</th>
                    <th class="case-table-title">课程名称</th>
                    <th>发布时间</th>
                    <th>截收时间</th>
                    <th>是否提交?</th>
                    <th>是否批改?</th>
                    <th>作业信息</th>
                </tr>


                <?php

                    foreach ( $data_list as $item ) {
                        $isSubmitted = is_submitted_assignment ( $user_id, $item ['id'], $item ['cc'] );
                        $isFeedback = is_feedback_assignment ( $user_id, $item ['id'], $item ['cc'] );
                        $sql = "SELECT status FROM " . $assignment_submission_table . " WHERE student_id='" . escape ( $user_id ) . "' AND assignment_id='" . escape ( $item ['id'] ) . "'";
                        $submission_status = Database::get_scalar_value ( $sql );

                        $status_html = '<span >';
                        if ($isSubmitted) {
                            $status_html .= Display::return_icon ( 'right.gif', '已提交' );
                            $status_html2 = ($isFeedback ? Display::return_icon ( 'right.gif', '已批改' ) : Display::return_icon ( 'wrong.gif', "待批改" ));
                        } else {
                            $status_html .= Display::return_icon ( 'wrong.gif', '未提交' );
                        }
                        if ($submission_status == 2) $status_html .= '回退';
                        $status_html .= '</span>';

                        $href = api_get_path ( WEB_CODE_PATH ) . "assignment/assignment_info_stud.php?id=" . $item ['id'] . "&cidReq=" . $item ['cc'];
                        $action_html = link_button ( 'synthese_view.gif', 'Info', $href, '90%', '960', FALSE );
                        //$action_html = '<a href="'.api_get_path(WEB_CODE_PATH).'assignment/assignment_info_stud.php?id=' . $item ['id'] . '">' . Display::return_icon ( 'synthese_view.gif', get_lang ( 'Info' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>&nbsp;';
                        ?>
                        <tr>
                            <td><?=api_trunc_str2 ( $item ['title'] )?></td>
                            <td><a href="course_home.php?cidReq=<?=$item ['cc']?>&action=introduction"><?=$item ['course_name']?></a></td>
                            <td><?=substr ( $item ['published_time'], 0, 16 )?></td>
                            <td><?=substr ( $item ['deadline'], 0, 16 )?></td>
                            <td><?=$status_html?></td>
                            <td><?=$status_html2?></td>
                            <td><?=$action_html?></td>
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
                    <div class="error" >没有相关课程作业</div>
                    <?php
                }
        ?>

    </article>



</section>


</body>
        </html>










