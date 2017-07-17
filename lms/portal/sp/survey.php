<?php
$cidReset = true;
include_once ("inc/app.inc.php");

if (! (api_get_setting ( 'enable_modules', 'survey_center' ) == 'true' and $_configuration ['enable_module_survey'])) api_redirect ( 'index.php' );

include_once (api_get_path ( SYS_CODE_PATH ) . "survey/survey.inc.php");

include_once ("inc/page_header.php");

$url = "survey.php";

$interbreadcrumb [] = array ("url" => 'index.php', "name" => get_lang ( 'HomePage' ) );
$interbreadcrumb [] = array ("url" => 'survey.php', "name" => get_lang ( 'MySurvey' ) );

$nameTools = null;

//待我参加调查
$rtn_data = SurveyManager::get_user_surveys_pagelist ( $user_id, "t2.status=" . STATE_PUBLISHED, NUMBER_PAGE, getgpc ( "offset", "G" ) );
$datalist = $rtn_data ["data_list"];
$total_rows = $rtn_data ["total_rows"];

$pagination_config = Pagination::get_defult_config ( $total_rows, $url );
$pagination = new Pagination ( $pagination_config );

display_tab ( TAB_SURVEY_CENTER );
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
                        <a class="f-thide f-f1" title="我的实验报告" href="labs_report.php" >我的实验报告</a>
                    </li> 
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="我的实验图片录像" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="我的实验图片录像" href="course_snapshot_list.php" >我的实验图片录像</a>
                    </li>
                     <li class="navitm it f-f0 f-cb haschildren couse-mess" data-id="-1" data-name="调查问卷" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="调查问卷" href="survey.php" style="color:green;font-weight:bold">调查问卷</a>
                    </li>

                </ul>
            </div>
        </div>
                  
        <div class="g-mn1" > 
                    <div class="g-mn1c m-cnt" style="display:block;">

                        <div class="top f-cb j-top">
                            <h3 class="left f-thide j-cateTitle title">
                                <span class="f-fc6 f-fs1" id="j-catTitle">调查问卷</span>
                            </h3>
                        </div>
                        <?php
if ($datalist && is_array ( $datalist )) {
    ?>
        <div class="module_content">
            <table cellspacing="0" cellpadding="0" class="p-table">
                <tr>
                    <th>标题名称</th>
                    <th>有效时间</th>
                    <th>状态</th>
                    <th>答卷时间</th>
                    <th>操作</th>
                </tr>

                <?php
                foreach ( $datalist as $v ) {
                    ?>
                    <tr>
                        <td><img alt="" src="<?=api_get_path ( WEB_IMG_PATH )?>quiz.gif">&nbsp;
                            <a  href="survey_intro.php?id=<?=$v ['id']?>" title="进入调查"><?=$v ['title']?></a></td>
                        <td><?=substr ( $v ['start_date'], 0, 16 )?>
                            至 <?=substr ( $v ['end_date'], 0, 16 )?></td>
                        <td>
                            <?=(($v ['last_attempt_time'] and ! is_equal ( $v ["last_attempt_time"], '0000-00-00 00:00:00' )) ? '已完成' : '未开始')?>
                        </td>
                        <td><?=$v ['last_attempt_time']?></td>
                        <td><a  href="survey_intro.php?id=<?=$v ['id']?>" title="进入调查">
                            <img src="<?=api_get_path ( WEB_IMG_PATH )?>starblue.jpg" /></a>
                        </td>
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

    <?php
} else {
    ?>
    <div class="error">没有相关测验考试</div>
    <?php
}
        ?>

        </div>
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
