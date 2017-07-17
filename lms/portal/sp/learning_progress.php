<?php
$cidReset = true;
include_once ("inc/app.inc.php");
include_once ("inc/page_header.php");

$enabled_exam = api_get_setting ( 'enable_modules', 'exam_center' ) == 'true' ? TRUE : FALSE;
$view_course_user = Database::get_main_table ( VIEW_COURSE_USER );

$offset =(is_not_blank($_GET['offset'])? getgpc ( "offset", "G" ):0);

$sql_where = " user_id=" . Database::escape ( $user_id );
if (isset ( $_GET ["keyword"] ) && is_not_blank ( $_GET ["keyword"] )) {
	if($_GET ['keyword']=='输入搜索关键词'){
	    $_GET ['keyword']='';
	}
	$keyword = Database::escape_str ( urldecode ( $_GET ['keyword'] ), TRUE );
	$sql_where .= " AND (title LIKE '%" . $keyword . "%')";
	$param .= "&keyword=" . urlencode ( $keyword );
}
if ($param {0} == "&") $param = substr ( $param, 1 );

$sql = "SELECT COUNT(*)	FROM " . $view_course_user . " WHERE " . $sql_where;
$total_rows = Database::get_scalar_value ( $sql );

$sql = "SELECT * FROM " . $view_course_user . " WHERE " . $sql_where." ORDER BY creation_time DESC";
$sql .= " LIMIT " . $offset . "," . NUMBER_PAGE;
$personal_course_list = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );

$objStat = new ScormTrackStat ();

$pagination_config = Pagination::get_defult_config ( $total_rows, WEB_QH_PATH . "learning_progress.php", '', NUMBER_PAGE );
$pagination = new Pagination ( $pagination_config );

$interbreadcrumb [] = array ("url" => 'index.php', "name" => "首页" );
$interbreadcrumb [] = array ("url" => 'learning_center.php', "name" => "学习中心" );
$interbreadcrumb [] = array ("url" => 'learning_progress.php', "name" => "学习档案" );


display_tab ( TAB_LEARN_PROGRESS );
?>



<aside id="sidebar" class="column open study-Centre">

    <div id="flexButton" class="closeButton close">

    </div>
</aside><!-- end of sidebar -->

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<?= display_interbreadcrumb ( $interbreadcrumb )?></h4>
    <div class="search">
        <form action="learning_progress.php" method="get">
            <input type="text" name="keyword"    value="输入搜索关键词" id="searchkey"  onfocus="this.select();" />
            <input type="submit" value="搜索"  id="searchbutton" class="submit" /></form>

    </div>
    <article class="module width_full hidden">
        <header><h3>课程学习历史信息</h3></header>
<?php
if (is_array ( $personal_course_list ) && $personal_course_list) {
    ?>
        <div class="module_content">
            <table cellspacing="0" cellpadding="0" class="p-table">
                <tr>
                    <th class="case-table-title">课程名称</th>

                    <th>总体状态</th>
                    <?php if($_configuration ['enable_display_courseware_track_info'] ){?>
                    <th>学习进度</th><?php }?>
                    <th>学习总时间</th>
                    <th>上次学习时间</th>
                    <?php
                    if ($enabled_exam) {
                        ?>
                        <th>成绩</th><?php
                    }
                    ?>
                    <th>查看档案</th>
                </tr>

                <?php

                foreach ( $personal_course_list as $course ) {
                    $course_code = $course ['code'];
                    $course_title = $course ['title'];
                    $course_visibility = $course ['visibility'];

                    //$progress = $objStat->get_course_progress_single_sco ( $course_code, $user_id );
                    //$progress = (empty ( $progress ) ? "0" : $progress) . "%";
                    $progress = $objStat->get_course_progress ( $course_code, $user_id ) . '%';
                    ?>
                    <tr>
                        <td class="line-title">
                            <a href="<?=WEB_QH_PATH?>course_home.php?cidReq=<?=$course_code?>&action=introduction" title="<?=$course_title?>">
                                <?=api_trunc_str2($course_title,20)?></a>
                        </td>
                        <!-- <td class="de1"  valign="middle" align="center"><?=$course ['credit']?></td> -->
                        <td><?php
                            if ($course ["is_pass"] == LEARNING_STATE_NOTATTEMPT)
                                echo "未开始";
                            elseif ($course ["is_pass"] == LEARNING_STATE_COMPLETED)
                                echo "已学完";
                            elseif ($course ["is_pass"] == LEARNING_STATE_IMCOMPLETED)
                                echo "学习中";
                            elseif ($course ["is_pass"] == LEARNING_STATE_PASSED)
                                echo "已通过";
                            ?>
                        </td>
                        <?php if($_configuration ['enable_display_courseware_track_info'] ){?>
                        <td>
                            <?=$progress?>
                        </td><?php }?>
                        <td>
                            <?php
                            echo api_time_to_hms ( $objStat->get_total_learning_time ( $user_id, $course_code ) );
                            ?>
                        </td>

                        <td><?php
                            echo substr ( $objStat->get_last_learning_time ( $user_id, $course_code ), 0, 16 );
                            ?></td>
                        <?php
                        if ($enabled_exam) {
                            ?>
                            <td><?php
                                //echo Tracking::get_avg_student_score ( $user_id, $course_code ); //测验平均分
                                //echo $objStat->get_course_exam_score ( $user_id, $course_code );
                                echo $course['exam_score'];
                                ?></td>
                            <?php
                        }
                        ?>

                        <td><a
                                href="<?php
                                    echo WEB_QH_PATH;
                                    ?>course_home.php?action=progress&cidReq=<?=$course_code?>"
                                title="详细信息"><?=Display::return_icon ( "info3.gif", "详细信息" )?></a></td>
                    </tr>

                    <?php
                }
                ?>




            </table>
            <div class="page">
                <ul class="page-list">
                    <li class="page-num">总计<?=$total_rows?> 条记录</li>
                    <?php
                    echo $pagination->create_links ();
                    ?>

                </ul>
            </div>
        </div>
    <?php
} else {
    ?>
    <div class="error">没有相关课程</div>
    <?php
}
        ?>

    </article>



</section>



</body>
        </html>
