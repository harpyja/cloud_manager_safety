<?php
header("content-type:text/html;charset=utf-8");
require_once ('../../main/inc/global.inc.php');
include_once ("inc/app.inc.php");
include_once ("inc/page_header.php");
include_once("../../main/inc/lib/main_api.lib.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');

$sel=$_POST['auto-id-rTOGAi3MiQOM7HrB'];
$user_id = api_get_user_id ();//获取用户id
//超级管理员删除
if(isset($_GET['lessonid'])&&isset($_GET['status'])&&isset($_GET['s_user_id'])){
    $lessonid=$_GET['lessonid'];
    $s_user_id=$_GET['s_user_id'];
    $sql_filename="select filename from snapshot where `user_id` = '$s_user_id' and `lesson_id`='$lessonid'";
    $result= api_sql_query ( $sql_filename, __FILE__, __LINE__ );
    while($vm = Database::fetch_row ( $result)){
          $filename=$vm[0];
          exec("sudo rm -rf ".URL_ROOT."/www".URL_APPEDND."/storage/snapshot/".$filename."*");    
          }
    $sql="DELETE FROM `snapshot` WHERE `user_id` = '$s_user_id' and `lesson_id`='$lessonid'" ;
    api_sql_query ( $sql, __FILE__, __LINE__ );
}

//普通用户删除
if(isset($_GET['lessonid'])&&isset($_GET['user_id'])){
    $lessonid=$_GET['lessonid'];
    $s_user_id=$_GET['user_id'];
    $sql_filename="select filename from snapshot where `user_id` = '$s_user_id' and `lesson_id`='$lessonid'";
    $result= api_sql_query ( $sql_filename, __FILE__, __LINE__ );
    while($vm = Database::fetch_row ( $result)){
          $filename=$vm[0];
          exec("sudo rm -rf ".URL_ROOT."/www".URL_APPEDND."/storage/snapshot/".$filename."*");  
          }
    $sql="DELETE FROM `snapshot` WHERE `user_id` = '$user_id' and `lesson_id`='$lessonid'" ;
    api_sql_query ( $sql, __FILE__, __LINE__ );
}

//一键清空
if(isset($_GET['delete'])&&isset($_GET['user_id'])&&($_GET['user_id'])!==5){
    //清空snapshot表
    $sql="truncate table `snapshot` ";
    $res=api_sql_query ( $sql, __FILE__, __LINE__ );
    //echo $res;
    if($res){
       //清空截屏录屏文件
        exec("sudo rm -rf ".URL_ROOT."/www".URL_APPEDND."/storage/snapshot/");
    }
    $url = "course_snapshot_list.php";
    echo "<script language='javascript' type='text/javascript'>";
    echo "window.location.href='$url'";
    echo "</script>";
}

$vm = Database::getval ( "select status from user where user_id='$user_id'", __FILE__, __LINE__ );  
$status=$vm[0];

$objStat = new ScormTrackStat ();

if(is_not_blank($sel)){
    $sqlwhere.="  AND title LIKE '%" . trim($sel) . "%'";
}

if($status==5){  ?>
    
<div class="clear"></div>
<div class="m-moclist">
    <div class="g-flow" id="j-find-main">
        <div class="b-30"></div>
   <div class="g-container f-cb">	 
       <div class="g-sd1 nav">
            <div class="m-sidebr" id="j-cates">
                <ul class="u-categ f-cb">
                    <li class="navitm it f-f0 f-cb first cur" data-id="-1" data-name="报告管理" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" style="background-color:#13a654;color:#FFF"  title="报告管理">报告管理</a>
                    </li>
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="报告管理" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="我的实验报告" href="labs_report.php">我的实验报告</a>
                    </li> 
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="报告管理" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="我的实验图片录像" href="course_snapshot_list.php" style="color:green;font-weight:bold">我的实验图片录像</a>
                    </li>
                    <li class="navitm it f-f0 f-cb haschildren couse-mess" data-id="-1" data-name="调查问卷" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="调查问卷" href="survey.php">调查问卷</a>
                    </li>
                     <li class="navitm it f-f0 f-cb haschildren couse-mess" data-id="-1" data-name="系统公告" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="系统公告" href="index.php?learn-status=sysance">系统公告</a>
                    </li>

                </ul>
            </div>
        </div>
        
              <div class="g-mn1" > 
            <div class="g-mn1c m-cnt" style="display:block;">
                
                <div class="top f-cb j-top">
                    <h3 class="left f-thide j-cateTitle title">
                        <span class="f-fc6 f-fs1" id="j-catTitle">我的实验图片录像</span>
                    </h3>
                </div>
<div class="j-list lists" id="j-list"> 
          <div class="u-content">
              <h3 class="sub-simple u-course-title"></h3>
            <table cellspacing="0" border="0" width="100%" class="tbl_course"> 
                <tr>
                   <th height=50>&nbsp;&nbsp;编号</th>
                   <th>课程名称</th>
                   <th>用户</th>
                   <th>图片数量</th>
                   <th>录像数量</th>
                   <th>查看图片</th>
                   <th>查看录像</th>
                   <th>编辑</th>
                </tr> <tr>
                    <td colspan="10"><div  class="sub-simple u-course-title"></div> </td>
                </tr>
    <?php
    $sql="select lesson_id from snapshot where user_id='$user_id' ";
    if($sqlwhere){  
        $sqlwheree="  title LIKE '%" . trim($sel) . "%'";
        $sql .=" and lesson_id = ( SELECT `code` FROM `course` WHERE ".$sqlwheree."  )";
    }
    $sql .=" group by lesson_id";
    $ress= api_sql_query ( $sql, __FILE__, __LINE__ );
    while($vm = Database::fetch_row ( $ress)){
          $vms[]=$vm;  
          }
    $lesson_num=count($vms);
    for($i=0;$i<$lesson_num;$i++){
        $lesson_id=$vms[$i];
        $sql="select title from course where code=$lesson_id[0]";
        if($sqlwhere)  $sql .=$sqlwhere;
        $title = Database::getval ( $sql, __FILE__, __LINE__ );  
        $sqla="select count(*) from snapshot where user_id='$user_id' and lesson_id='$lesson_id[0]' and type='1'";
        $num1 = Database::getval ( $sqla, __FILE__, __LINE__ );  
        $sqlb="select count(*) from snapshot where user_id='$user_id' and lesson_id='$lesson_id[0]' and type='2' and status=0";
        $num2 = Database::getval ( $sqlb, __FILE__, __LINE__ );  
        ?>
            <tr>								
                <td  height=50>&nbsp;&nbsp;<?php echo $i+1+$offset;?></td>
                <td><?php echo $title;?></td>
                <td><?php
                   if($user_id!==''){
                       echo Database::getval("select  username from user where  user_id=".$user_id,__FILE__,__LINE__);
                   } ?>
                </td>
                <td><?php echo $num1[0];?></td>
                <td><?php echo $num2[0];?></td>
                <td>
                    <?php
                   $count1 =intval($num1[0]);
                    if($count1>0){?>
                    <a href="course_snapshot_content.php?type=1&lessonid=<?php echo $lesson_id[0]; ?>&userid=<?php echo $user_id; ?>&status=<?php echo $status;?>">
                        <img src="../../themes/img/message_normal.gif" height="24px" width="24px" align="center">
                    </a>
                  <?php 
                  }else{
                    echo ' <img src="../../themes/img/message_normal.png" height="24px" width="24px" align="center">';
                      } ?>
                </td> 
                <td>
                    <?php
                   $count2 =intval($num2[0]);
                    if($count2>0){?>
                    <a href="course_snapshot_content.php?type=2&lessonid=<?php echo $lesson_id[0]; ?>&userid=<?php echo $user_id; ?>&status=<?php echo $status;?>">
                        <img src="../../themes/img/message_normal.gif" align="center">
                    </a>
                  <?php 
                  }else{
                    echo ' <img src="../../themes/img/message_normal.png" height="24px" width="24px" align="center">';
                      } ?>

                </td>
                <td><a href="course_snapshot_list.php?lessonid=<?= $lesson_id[0] ?>&user_id=<?= $user_id ?>"><img src="../../themes/img/delete.gif" align="center"></a></td>
            </tr> 
            <tr>
               <td colspan="10"><div  class="sub-simple u-course-title"></div> </td>
           </tr>
        <?php                
     } ?>
        </table>
            <?php if($i){ ?>
            <div class="page">
                <ul class="page-list"><li class="page-num">总计<?=$lesson_num?> 条记录</li><?php  echo $pagination->create_links (); ?> </ul>
            </div>
            </div> <?php }else{?>
                <div class="error">没有相关记录</div>
            <?php } ?>
                    </div> </div> </div>
   </div>
    </div>
</div>
                    

     <?php
  }else{
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
                        <a class="f-thide f-f1"  style="background-color:#13a654;color:#FFF"  title="报告管理">报告管理</a>
                    </li>
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="课程分类" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="我的实验报告" href="labs_report.php">我的实验报告</a>
                    </li> 
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="课程分类" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="我的实验图片录像" href="course_snapshot_list.php" style="color:green;font-weight:bold">我的实验图片录像</a>
                    </li>
                    <li class="navitm it f-f0 f-cb haschildren couse-mess" data-id="-1" data-name="调查问卷" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="调查问卷" href="<?= URL_APPEND?>portal/sp/survey.php" >调查问卷</a>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="g-mn1" > 
            <div class="g-mn1c m-cnt" style="display:block;">
                
                <div class="top f-cb j-top">
                    <h3 class="left f-thide j-cateTitle title">
                        <span class="f-fc6 f-fs1" id="j-catTitle">我的实验图片录像</span>
                    </h3>
                     <div class="j-nav nav f-cb"> 
                        <div id="j-tab">  <!--  class="sub-simple u-course-title"-->
                            <a class="u-btn u-btn-sm u-btn-left u-btn-active" title="一键清空我的实验图片录像" href="course_snapshot_list.php?delete=empty&user_id=<?= $user_id ?>">一键清空</a>
                        </div>
                    </div>
                </div>
            <div class="j-list lists" id="j-list"> 
            <div class="u-content">
                <h3 class="sub-simple u-course-title"></h3>
            <table cellspacing="0" border="0" width="100%" class="tbl_course"> 
                <tr>
                   <th height="50">&nbsp;&nbsp;编号</th>
                   <th>课程名称</th>
                   <th>用户</th>
                   <th>图片数量</th>
                   <th>录像数量</th>
                   <th>查看图片</th>
                   <th>查看录像</th>
                   <th>编辑</th>
                </tr>
                <tr>
                    <td colspan="10"><div  class="sub-simple u-course-title"></div> </td>
                </tr>
    <?php
    $m=0;
    $sql="select user_id from snapshot  group by user_id";  //获取snapshot表用户的汇总
    $ress = api_sql_query ( $sql, __FILE__, __LINE__ );
    while($vm = Database::fetch_row ( $ress)){
        $vms[]=$vm;   
    }
    $s_user_num=count($vms);

    for($i=0;$i<$s_user_num;$i++){
        $s_user_id=$vms[$i];
        $sqla="select lesson_id from snapshot where user_id='$s_user_id[0]' "; //获取用户的课程汇总
        if($sqlwhere){  
        $sqlwheree="  title LIKE '%" . trim($sel) . "%'";
        $sqla .=" and lesson_id = ( SELECT `code` FROM `course` WHERE ".$sqlwheree."  )";
        }
        $sqla .="group by lesson_id";
        $vmsa= api_sql_query_array_assoc($sqla,__FILE__, __LINE__);
        $lesson_num=count($vmsa);
        
        for($j=0;$j<$lesson_num;$j++){
            $lesson_id=$vmsa[$j];
            $sql="select `title` from course where code='$lesson_id[lesson_id]'";  //获取课程的名称
            if($sqlwhere)  $sql .=$sqlwhere;
            $title=Database::getval($sql, __FILE__,__LINE__);
            $m++;
            $sqlb="select count(*) from snapshot where user_id='$s_user_id[0]' and lesson_id='$lesson_id[lesson_id]' and type='1'";
            $num1=Database::getval($sqlb, __FILE__,__LINE__);
            $sqlc="select count(*) from snapshot where user_id='$s_user_id[0]' and lesson_id='$lesson_id[lesson_id]' and type='2' and status=0";
            $num2 =Database::getval($sqlc, __FILE__,__LINE__);
            ?>
                <tr>								
                    <td height="50">&nbsp;&nbsp;<?php echo $m+$offset;?></td>
                    <td><?php echo $title;?></td>
                    <td><?php
                           if($s_user_id[0]!==''){
                               echo Database::getval("select  username from user where  user_id=".$s_user_id[0],__FILE__,__LINE__);
                           } ?></td>
                    <td><?php echo $num1[0];?></td>
                    <td><?php echo $num2[0];?></td>
                    <td>
                            <?php
                           $count1 =intval($num1[0]);
                            if($count1>0){?>
                             <a href="course_snapshot_content.php?type=1&lessonid=<?php echo $lesson_id[lesson_id]; ?>&userid=<?php echo $s_user_id[0]; ?>&status=<?php echo $status;?>">
                                <img src="../../themes/img/message_normal.gif" height="22px" width="22px" align="center">
                            </a>
                          <?php 
                          }else{
                            echo ' <img src="../../themes/img/message_normal.png" height="20px" width="20px" align="center">';
                              } ?>
                        </td> 
                        <td>
                            <?php
                           $count2 =intval($num2[0]);
                            if($count2>0){?>
                           <a href="course_snapshot_content.php?type=2&lessonid=<?php echo $lesson_id[lesson_id]; ?>&userid=<?php echo $s_user_id[0]; ?>&status=<?php echo $status;?>">
                                <img src="../../themes/img/message_normal.gif" height="22px" width="22px"  align="center">
                            </a>
                          <?php 
                          }else{
                            echo ' <img src="../../themes/img/message_normal.png" height="20px" width="20px" align="center">';
                              } ?>
                            
                        </td>
                    <td><a href="course_snapshot_list.php?lessonid=<?= $lesson_id[lesson_id] ?>&status=<?= $status ?>&s_user_id=<?= $s_user_id[0] ?>"><img src="../../themes/img/delete.gif" align="center"></a></td>
                </tr>
                <tr>
                    <td colspan="10"><div  class="sub-simple u-course-title"></div> </td>
                </tr>
                  <?php }
                                 } ?>
            </table>
            <?php if($i){ 
                    $total_rows=$m;
                    $url=WEB_QH_PATH."course_snapshot_list.php";
                    $pagination_config = Pagination::get_defult_config ( $total_rows,$url, NUMBER_PAGE );
                    $pagination = new Pagination ( $pagination_config );
                ?>
            <div class="page">
                <ul class="page-list"><li class="page-num">总计<?=$m?> 条记录</li><?php  //echo $pagination->create_links (); ?> </ul>
            </div>
             <?php }else{?>
                <div class="error">没有相关记录</div>
            <?php } ?>
        </div></div></div></div>
     </div>
    </div>
 </div>
        <?php
    }
    include_once './inc/page_footer.php';
?>
