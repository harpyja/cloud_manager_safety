<?php
$cidReset = true;
include_once ("inc/app.inc.php");
include_once ('../../main/exercice/exercise.class.php');

include_once ("inc/page_header.php");
$tbl_exam_rel_user = Database::get_main_table ( TABLE_MAIN_EXAM_REL_USER ); //考试用户关联表
$tbl_exam_main = Database::get_main_table ( TABLE_QUIZ_TEST ); //crs_quiz
if (api_get_setting ( 'enable_modules', 'exam_center' ) != 'true') api_redirect ( 'learning_center.php' );
$type = (isset ( $_GET ['type'] ) ? getgpc ( 'type', 'G' ) : 3);
$sel=$_POST['auto-id-rTOGAi3MiQOM7HrB'];
$url = "exam_center.php?type=" . $type;
$interbreadcrumb [] = array ("url" => 'index.php', "name" => "首页" );
$interbreadcrumb [] = array ("url" => 'exam_center.php', "name" => "在线测验与考试" );
if ($type == 3)
	$nameTools = '我的自我测验';
elseif ($type == 2)
	$nameTools = '我的课程毕业考试';
elseif ($type == 1)
	$nameTools = '我的综合考试';
else $nameTools = '';

//待我参加考试
$sql = "SELECT t2.type,COUNT(*) FROM $tbl_exam_rel_user AS t1," . $tbl_exam_main . " AS t2 WHERE t1.user_id=" . Database::escape ( $user_id ) . " AND t1.exam_id=t2.id AND t2.active=1 GROUP BY t2.type";
$total_cnt = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );

$sqlwhere = "t2.active=1 AND t2.type=" . Database::escape ( $type );
if($sel){
    $sqlwhere.=" AND  t2.title  like  '%".trim($sel)."%'";
}
$rtn_data = Exercise::get_user_exam_pagelist ( $user_id, $sqlwhere, NUMBER_PAGE, getgpc ( "offset", "G" ) );
$datalist = $rtn_data ["data_list"];
$total_rows = $rtn_data ["total_rows"];

$pagination_config = Pagination::get_defult_config ( $total_rows, $url );
$pagination = new Pagination ( $pagination_config );
display_tab ( TAB_EXAM_CENTER );
?>
<link href="<?=api_get_path ( WEB_JS_PATH )?>yui/tabview/assets/skins/sam/tabview.css" rel="stylesheet" type="text/css" />
 
<div class="clear"></div> 
	<div class="m-moclist">
	  <div class="g-flow" id="j-find-main">
               <div class="b-30"></div>
	<div class="g-container f-cb">	
            <div class="g-sd1 nav">
                <div class="m-sidebr" id="j-cates">
                    <ul class="u-categ f-cb">
                        <li class="navitm it f-f0 f-cb haschildren cur" data-id="-1" data-name="考试中心" id="auto-id-D1Xl5FNIN6cSHqo0">
                             <a class="f-thide f-f1" title="考试中心">考试中心</a>
                        </li>
                        <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="我的考试" id="auto-id-D1Xl5FNIN6cSHqo0">
                            <a class="f-thide f-f1" title="我的考试" href="exam_list.php" style="color:green;font-weight:bold">我的考试</a>
                        </li>
                        <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="考试成绩查询" id="auto-id-D1Xl5FNIN6cSHqo0">
                            <a class="f-thide f-f1" title="考试成绩查询" href="exam_result.php" >考试成绩查询</a>
                        </li> 
                    </ul>
                </div>
            </div>
            <div class="g-mn1" >
                <div class="g-mn1c m-cnt" style="display:block;">
                    <div class="top f-cb j-top">
                        <h3 class="left">
                           <span class="f-fc6 f-fs1" id="j-catTitle">
                               <?php
                               $tit="<a href='exam_list.php'>我的考试</a>";
                               if(isset($_GET['type']) && $_GET['type']!==''){
                                   $exam_cate=DATABASE::getval("select  name FROM  `exam_type` where  id=".getgpc("type","G"));
                                   if($exam_cate!==''){
                                      $tit.=" > ".$exam_cate;
                                   }
                               } 
                               echo $tit;
                               ?>
                               
                           </span>
                        </h3>
                        <div class="j-nav nav f-cb">  </div>
                    </div>
                    <div class="j-list lists" id="j-list"> 
                      <div class="u-content">
		         <h3 class="sub-simple u-course-title"></h3>
<?php
  
         if ($datalist && is_array ( $datalist )) {
             ?>
                                <div class="lab-cont">
                                    <div class="study-content study-content1 ">
                                        <table cellspacing="0" cellpadding="0" width="100%" class="tbl_course">
                                            <tr>
                                                <th class="case-table-title">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;考试名称</th>
                                                <th class="case-table-time" align="center">考试时间</th>
                                                <th align="center">状态</th>
                                                <th align="center">已考次数</th>
                                                <th class="case-table-time" align="center">最后一次答题时间</th>
                                                <th align="center">进入考试</th>
                                            </tr>
                                            <tr>
                                                 <td colspan="10"><div  class="sub-simple u-course-title"></div> </td>
                                            </tr>
                                            <?php
                                            foreach ( $datalist as $v ) {
                                                $attempt_times = Exercise::get_exam_user_attempts ( $v ['id'], $user_id );
                                                $types=$v ['type'];
                                                ?>
                                                <tr>
                                                    <td class="line-title">
                                                        <img src="<?=api_get_path ( WEB_IMG_PATH )?>quiz.gif">&nbsp;
                                                        <a href="quiz_intro.php?exerciseId=<?=$v ['id'] . ($v ['type'] == 2 ? '&cidReq=' . $v ['cc'] : '')?>&type=<?=$types?>"
                                                            title="进入考试"><?=api_trunc_str2($v ['title'],25)?></a></td>
                                                    <td align="center"><?=substr ( $v ['available_start_date'], 0, 16 )?>
                                                        至 <?=substr ( $v ['available_end_date'], 0, 16 )?></td>
                                                    <td align="center">
                                                        <?=($attempt_times > 0 ? '已完成' : '未开始')?>
                                                    </td>
                                                    <td align="center"><?=$attempt_times ? $attempt_times : '0'?></td>
                                                    <td align="center"><?=substr ( $v ['last_attempt_date'], 0, 16 )?></td>
                                                    <td align="center">
                                                        <a href="quiz_intro.php?exerciseId=<?=$v ['id'] . ($v ['type'] == 2 ? '&cidReq=' . $v ['cc'] : '')?>&type=<?=$types?>"title="进入考试">
                                                         <img src="../../themes/img/desktop.png" width="24" height="24">
                                                        </a>
                                                    </td>
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
                                                echo $pagination->create_links ();
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
                                     <?php
                                 } else {
                                     ?>
                                     <div class="error b">没有相关测验考试</div>
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
        include_once('./inc/page_footer.php');
?>
</body>
</html>