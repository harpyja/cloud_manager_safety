<?php
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
include_once ("inc/page_header_report.php");
$cidReq=$_GET['cidReq'];
$user_id=  api_get_user_id();
$sql="SELECT * FROM `snapshot` WHERE `lesson_id`='".$cidReq."' and `user_id`='".$user_id."' and `status`='0'";
$result= api_sql_query_array_assoc($sql,__FILE__,__LINE__);
$num=count($result);
$url="image_and_media.php?action=delete&cidReq=".$cidReq."&id=";

if($_GET['action']==delete){
    $id=$_GET['id'];
    $url_1="image_and_media.php?cidReq=".$cidReq;
    $sql="SELECT  `type`,`filename` FROM `snapshot` WHERE `id` = ".$id;
    $result= api_sql_query_array_assoc($sql,__FILE__,__LINE__);
    $result=$result['0'];
    $filename=$result['filename'];
//    echo '<pre>';var_dump($result);echo '<hr>';
    if($result['type']==1){
        exec("sudo rm -rf ".URL_ROOT."/www".URL_APPEDND."/storage/snapshot/".$filename."*.jpg");
    }
    if($result['type']==2){
        exec("sudo rm -rf ".URL_ROOT."/www".URL_APPEDND."/storage/snapshot/".$filename.".fbs");
    }
    $desc = 'delete from snapshot where id='.$id;
    $res= api_sql_query ( $desc, __FILE__, __LINE__ );
    echo '<script type="text/javascript">';
    echo 'location.href = "'.$url_1.'"';
    echo '</script>';
    }

    
?>
<!--<div class="j-nav nav f-cb"> 
    <div id="j-tab">    class="sub-simple u-course-title"
        <input type=button width="80px" height="30px" style="color:#13a654;" name=go value="&nbsp;播放所有&nbsp;"  
        onclick="Javascript:window.open('../../main/cloud/cloudplay_learn.php?user=<?=$user_id?>&lesson=<?=$cidReq?>&type=2')">
    </div>
</div>        -->
<?php
//图片
for($i=0;$i<$num;$i++){
    ?>

    <div class="g-cell1_m u-card j-href ie6-style" data-href="#">
        <div class="card">    
            <?php
            $var=$result[$i];
            $filename=$var['filename'];
            $snapshotdesc=$var['snapshotdesc'];
        //    echo '<pre>';var_dump($var);echo '<hr>';
            if($var['type']==1){  ?>
                <div class="u-img f-pr">
                    <td><a  href="/lms/storage/snapshot/<?= $filename?>.jpg" title="<?= $snapshotdesc?>"><img alt="" src="/lms/storage/snapshot/<?= $filename?>_s.jpg" /></a></td>
                </div>
                <div class="descd j-d">
                    <span class="dbtn">
                        <a href="javascript:if(confirm('你确定删除吗?'))window.location='<?= $url.$var['id']?>'">删除</a>&nbsp;
                    </span> 
                </div>
            <?php  
            }
            ?>
        </div>
    </div>
                <?php
}
//录屏
for($i=0;$i<$num;$i++){
?>
    <div class="g-cell1_m u-card j-href ie6-style" data-href="#">
        <div class="card">    
            <?php
            $var=$result[$i];
            $filename=$var['filename'];
            $snapshotdesc=$var['snapshotdesc'];
        //    echo '<pre>';var_dump($var);echo '<hr>';
            if($var['type']==2){  ?>
                <div class="u-img f-pr">
                    <td><a  href="../../playvnc/play.php?filename=/lms/storage/snapshot/<?= $filename?>.fbs"><img  alt="高清视频"  title="<?= $snapshotdesc?>" src="/lms/themes/img/video1.png" /></a></td>
                </div>
                <div class="descd j-d">
                    <span class="dbtn"><a href="javascript:if(confirm('你确定删除吗?'))window.location='<?= $url.$var['id']?>'">删除</a>&nbsp;</span> 
                </div>
            <?php   
            }
            ?>
        </div>
    </div>
    <?php
}
?>
