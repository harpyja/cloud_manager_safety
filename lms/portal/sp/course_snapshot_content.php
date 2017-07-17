<?php
include_once ("inc/app.inc.php");
include_once ("inc/page_header.php");

$user_id=$_GET['userid'];
$lesson_id=$_GET['lessonid'];
$type=intval($_GET['type']);
$status=$_GET['status'];
$id=$_GET['id'];


$paThs="http://".$_SERVER["SERVER_ADDR"].$_SERVER["SCRIPT_NAME"]."?";
if($_GET['type'] || $_GET['action']='upload'){
          $paThs.= "type=".trim($_GET['type']);
        }
        if($_GET['lessonid']){
          $paThs.= "&lessonid=".trim($_GET['lessonid']);
        }
        if($_GET['userid']){
          $paThs.= "&userid=".trim($_GET['userid']);
        }
        if($_GET['status']){
          $paThs.= "&status=".trim($_GET['status']);
}

$mp4path=URL_ROOT."/www".URL_APPEDND."/storage/"; 
if(!file_exists($mp4path."/snapMp4")){
   exec("mkdir ".$mp4path."/snapMp4");
   exec("chmod -R 777 ".$mp4path."/snapMp4");
}
if(isset($_GET['action']) && $_GET['action']=="delete" && $id) {
    $desc = 'select filename from snapshot where id='.$id;
    $res= api_sql_query_array( $desc, __FILE__, __LINE__ );
    $filename=$res[0][0];
    if($type==1){
           exec("sudo rm -rf ".URL_ROOT."/www".URL_APPEDND."/storage/snapshot/".$filename."*.jpg");
    }  else {
           exec("sudo rm -rf ".URL_ROOT."/www".URL_APPEDND."/storage/snapshot/".$filename.".fbs");
    }
    
    $desc = 'delete from snapshot where id='.$id;
    $res= api_sql_query ( $desc, __FILE__, __LINE__ );
    tb_close($paThs);
}


if(isset($_GET['action']) && $_GET['action']=="upload") {
    if($_GET['f']!==''){
        $filename=trim($_GET['f']).".mp4";
        $mp4file=$mp4path."snapMp4/".$filename;
        //echo $mp4file; 
    dl_file($mp4file);
    }
   tb_close($paThs);
}
 
$offset =(is_not_blank($_GET['offset'])? getgpc ( "offset", "G" ):0);



    $sql="select id,filename,snapshotdesc,user_id from snapshot where user_id=$user_id and lesson_id=$lesson_id and status=0 and type=$type";
    $sql .= " LIMIT " . $offset . "," . NUMBER_PAGE;
    $res = api_sql_query_array( $sql, __FILE__, __LINE__ );
    
    $course_snapshot_content = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
    $sql_n="select count(*) from snapshot where user_id=$user_id and lesson_id=$lesson_id and status=0 and type=$type";
    $total_rows = Database::get_scalar_value ( $sql_n );
    
$rtn_data=array ("data_list" => $course_snapshot_content, "total_rows" => $total_rows );
$personal_course_list = $rtn_data ["data_list"];
$total_rows = $rtn_data ["total_rows"];

$cur_path= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; 
$objStat = new ScormTrackStat ();
$url=WEB_QH_PATH."course_snapshot_content.php?type=$type&lessonid=$lesson_id&userid=$user_id&status=$status".$param;
$pagination_config = Pagination::get_defult_config ( $total_rows,$url,'', NUMBER_PAGE );
$pagination = new Pagination ( $pagination_config );

if(isset($_GET['format']) ){
    $fart=trim($_GET['format']);
    if($fart=='format'){
        if(isset($_GET['f'])  && $_GET['f']!==''){
             $ff=trim($_GET['f']);
           $exec1= "cd  ".URL_ROOT."/www".URL_APPEDND."/storage/snapshot/; sudo -u root  /sbin/cloudfbstomp4.sh ".$ff.".fbs ".$mp4path."snapMp4/".$ff." ;";
            exec($exec1); 
        } 
        tb_close($paThs);
    }
}
display_tab ( TAB_LEARN_PROGRESS );
?>
<html>
<head>
<script type="text/javascript" src="../../themes/js/jquery-fanxybox/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../../themes/js/jquery-fanxybox/js/jquery.fancybox-1.3.4.js"></script>
<!--鼠标控制滚动-->
<script type="text/javascript" src="../../themes/js/jquery-fanxybox/js/jquery.mousewheel-3.0.4.js"></script>
<link rel="stylesheet" type="text/css" href="../../themes/js/jquery-fanxybox/css/jquery.fancybox-1.3.4.css" media="screen" />
<style type="text/css">
*{margin:0;padding:0;list-style-type:none;}
body{font:normal 12px/18px Verdana, sans-serif;}
#content{width:410px;margin:40px auto 0 auto;padding:0 60px 30px 60px;border:solid 1px #cbcbcb;background:#fafafa;-moz-box-shadow:0px 0px 10px #cbcbcb;-webkit-box-shadow:0px 0px 10px #cbcbcb;}
hr{border:none;height:1px;line-height:1px;background:#E5E5E5;margin-bottom:20px;padding:0;}
#content p{margin:0;padding:7px 0;}
#content a img{border:1px solid #BBB;padding:2px;margin:10px 20px 10px 0;vertical-align:top;}
#content a img.last{margin-right:0;}
#content ul{margin-bottom:24px;padding-left:30px;}
#fancybox-close{position:absolute;top:-15px;right:-15px;width:30px;height:30px;background:transparent url('../../themes/js/jquery-fanxybox/images/fancybox.png') -40px 0px;cursor:pointer;z-index:1103;display:none;}
</style>
<script type="text/javascript">
	$(document).ready(function() {

			$("#various1").fancybox({
			'width':'70%',
			'height':'110%',
			'autoScale':false,
			'transitionIn':'none',
			'transitionOut':'none',
			'type':'iframe'
		});
                
		$("a[rel=example_group]").fancybox({
			'transitionIn':'none',
			'transitionOut':'none',
			'titlePosition':'over',
			'titleFormat':function(title, currentArray, currentIndex, currentOpts) {
				return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + '</span>';
			}
		});


	});
</script>
</head>

<div class="clear"></div>
<div class="m-moclist">
    <div class="g-flow" id="j-find-main">
             <div class="b-30"></div>
	<div class="g-container f-cb">	
            <div class="g-sd1 nav">
            <div class="m-sidebr" id="j-cates">
                <ul class="u-categ f-cb">
                   <li class="navitm it f-f0 f-cb first cur" data-id="-1" data-name="报告管理" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" style="background-color:#13a654;color:#FFF" title="报告管理">报告管理</a>
                    </li>
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="课程分类" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="我的实验报告" href="labs_report.php">我的实验报告</a>
                    </li> 
                    <li class="navitm it f-f0 f-cb haschildren" data-id="-1" data-name="课程分类" id="auto-id-D1Xl5FNIN6cSHqo0">
                        <a class="f-thide f-f1" title="我的实验图片录像" href="course_snapshot_list.php"  style="color:green;font-weight:bold">我的实验图片录像</a>
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
                            <span class="f-fc6 f-fs1" id="j-catTitle">
                                <?php 
                                if($type==1){
                                        echo '课程截图';
                                }  else {
                                        echo '课程录像'; 
                                } 
                                ?>
                            </span>
                        </h3>
                        <div class="j-nav nav f-cb"> 
                            <div id="j-tab">  <!--  class="sub-simple u-course-title"-->
                                <input type=button width="80px" height="30px" style="color:#13a654;" name=go value="&nbsp;播放所有&nbsp;"  
                   onclick="Javascript:window.open('../../main/cloud/cloudplay.php?user=<?=$user_id?>&lesson=<?=$lesson_id?>&type=<?=$type?>')">
                            </div>
                        </div>
                    </div>

<!--<aside id="sidebar" class="column open labsreport">
    <div id="flexButton" class="closeButton close"> </div>
</aside>-->


<!--<section id="main" class="column">
    <h4 class="page-mark">当前位置：<?= display_interbreadcrumb ( $interbreadcrumb )?></h4>
    <article class="module width_full hidden">-->
<!--        <header style="line-height:25px;"><h3>-->
      
        
            <!--</h3><p align="right" style="line-height:35px;margin-right: 30px;border-radius:2px;">-->
            
				<?php  
				if (is_array ( $course_snapshot_content ) && $course_snapshot_content) {
				?>
        <!--<div class="module_content">-->
            
             <div class="j-list lists" id="j-list"> 
            <div class="u-content">
                <h3 class="sub-simple u-course-title"></h3>
             <table cellspacing="0" border="0" width="100%" class="tbl_course"> 
                <tr>
                   <th height="50">&nbsp;&nbsp;&nbsp;&nbsp;序号</th>
		   <th>文件</th>
		   <th>时间</th>
                   <th>用户</th>
		   <th>内容描述</th>
		   <th>下载</th>
		   <th>操作</th>
                </tr>
                <tr>
                    <td colspan="10"><div  class="sub-simple u-course-title"></div> </td>
                </tr>
                <?php
                $i=1;
                foreach ( $res as $arr ) {
                           $id=$arr['id'];
                           $filename=$arr['filename'];
                           $snapshotdesc=$arr['snapshotdesc'];
                    ?>
                    <tr>
                        <td height="50">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $i+$offset;$i++;?></td>
                    	<td>
                    	<?php if($type==1){ ?>
                    	<p><a rel="example_group" href="/lms/storage/snapshot/<?php echo $filename;?>.jpg" title="<?php echo $snapshotdesc;?>"><img alt="" src="/lms/storage/snapshot/<?php echo $filename;?>_s.jpg" /></a></p>
                   		<?php }else { ?>
                   		<p>
                                    <a id="various1" href="../../playvnc/play.php?filename=/lms/storage/snapshot/<?php echo $filename;?>.fbs"><img  alt="高清视频"  title="高清视频" src="/lms/themes/img/play_green.gif" /></a>
                                    <a id="various1" href="<?=$paThs?>&format=format&f=<?=$filename?>"><img alt="格式化视频" title="格式化视频" src="/lms/themes/img/kaboodleloop.gif" width="25px"  height="25px" /></a>
                                    <?php
                                   // if(file_exists($mp4path."/snapMp4/".$filename.".mp4")){
                                        echo '<a id="various1" target="_blank" href="/lms/portal/sp/player.php?f='.$filename.'"><img alt="观看视频" title="观看视频" src="/lms/themes/img/avi.gif" width="30px"  height="30px" /></a>';
                                   //  }
                                    ?>
                                </p>
                   		<?php } ?>
                        </td>
                   		<td>
				<?php
				$dates=explode("_",$filename); 
                                if($dates[1]!==''){
                                   $times = explode("-",$dates[1]); 
                                   if($times[0]){
                                     echo $times[0]."-".$times[1]."-".$times[2]." ".$times[3].":".$times[4].":".$times[5];
			           }
				}
				 
				?>
				</td>
                        <td><?php
                           if($arr['user_id']!==''){
                               echo Database::getval("select  username from user where  user_id=".$arr['user_id'],__FILE__,__LINE__);
                           }
                        ?></td>
			<td><?php echo $snapshotdesc;?></td>
                        <td>

                        <?php if(file_exists($mp4path."/snapMp4/".$filename.".mp4")){?>
                            <a target="_blank" href="../../storage/snapMp4/<?=$filename?>.mp4"><img src="../../themes/images/download.jpg" title="点击下载" width="25" height="25"/></a>
                        <?php }else{?>
                            <img src="../../themes/images/dowload2.jpg" title="没有文件" width="25" height="25"/>
                        <?php }?>

                        </td>
                        <td><a href=course_snapshot_content.php?action=delete&id=<?=$id?>&type=<?=$type?>&userid=<?=$user_id?>&lessonid=<?=$lesson_id?>&status=<?=$status?>><img src="../../themes/img/delete.gif" align="center"></a></td>
                    </tr>
                    <tr>
                    <td colspan="10"><div  class="sub-simple u-course-title"></div> </td>
                </tr>
                <?php  } 	?>
            </table>
            <div class="page">
                <ul class="page-list"><li class="page-num">总计<?=$total_rows?> 条记录</li><?php  echo $pagination->create_links (); ?> </ul>
            </div>
        </div>
    <?php
} else {
	if($type==1){
        echo '<div class="error"\>没有相关截图记录</div\>';
        }  else {
           echo '<div class="error"\>没有相关录像记录</div\>'; 
        }
}
?>
        </div></div></div>
        </div>
    </div>
</div>
        <?php
        include_once './inc/page_footer.php';
?>
</html>
