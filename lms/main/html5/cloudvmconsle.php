<?php
header("content-type:text/html;charset=utf-8");
require_once ('../inc/global.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');

$port =$_GET['port'];   
$str='';
$str2='';
   if(isset($_GET['host']) && $_GET['host']!==''){
       $str.="?host=".$_GET['host'];
       if(isset($_GET['port']) && $_GET['port']!==''){
                $str.="&port=".$_GET['port'];
       }       
   }
   if(isset($_GET['host']) && $_GET['host']!==''){
       $str2.="host=".$_GET['host'];
       if(isset($_GET['port']) && $_GET['port']!==''){
                $str2.="&port=".$_GET['port'];
       }       
   }
$sql="select user_id,port,vmid from vmtotal where proxy_port=$port";
$ress = api_sql_query ( $sql, __FILE__, __LINE__ );
$vm = Database::fetch_row ( $ress);
$sql_snap="select id from `snapshot` where `user_id`='".$vm[0]."' and  `type`=2 and `port`='".$vm[1]."' and `vmid`='".$vm[2]."' and `status`='1' ";
$dada=Database::getval( $sql_snap,__FILE__,__LINE__);
?>
<!doctype html>
 <html>
 <head>
<script LANGUAGE="JavaScript"> 
function openwin1() {
    var url = location.search;
    url = "/lms/main/cloud/snapshot_form.php"+url;
    window.open (url, "newwindow", "height=200, width=400, top=100px,left=0,toolbar=no, menubar=no, scrollbars=no, resizable=no,alwaysRaised=yes,dependent=yes,location=no, status=no,directories=no");
} 
function openwin2() {
    var url = location.search;
    url = "/lms/main/cloud/rec_form.php"+url;
    window.open (url, "newwindow", "height=200, width=400, top=100px,left=0,toolbar=no, menubar=no, scrollbars=no, resizable=no,alwaysRaised=yes,dependent=yes,location=no, status=no,directories=no");
} 
</script>
<style>
    a{   color:black;    font-size:12px;     text-decoration: none;  padding-left:30px;display:block;height:18px;line-height:18px;float:left}
    a:hover{   color:black;  font-size:14px;  text-decoration: none; }
    .content1{  background:url(clound.png) no-repeat 0px -16px; }
    .content2{ background:url(clound.png) no-repeat 0px -33px;}
    .content3{background:url(clound.png) no-repeat 0px -49px; }
    .content4{ background:url(clound.png) no-repeat 0px -69px; }
    .content5{background:url(clound.png) no-repeat 0px -88px;}
    .content6{  background:url(clound.png) no-repeat 0px -107px;}
    
    .content11{background:url(clound2.png) no-repeat 0px -17px;}
    .content21{  background:url(clound2.png) no-repeat 0px -34px;}
    .content31{ background:url(clound2.png) no-repeat 0px -51px;}
    .content41{background:url(clound2.png) no-repeat 0px -70px;}
   .content51{background:url(clound2.png) no-repeat 0px -90px;}
   .content61{ background:url(clound2.png) no-repeat 0px -109px;}
</style>
</head>
<body border="0" style="margin-top:2px;height:25px">
    <a href="#" onclick="openwin1()"  class="content11">实验截屏</a> 
<?php
    
    if($dada!=null ){
        echo ' <a  href="/lms/main/cloud/cloudvmrec.php'.$str.'"  class="content2">停止录屏</a>';
    }else{
        echo '<a  href="#" onclick="openwin2()"  class="content21">实验录屏</a>';
    }

?>
    <a  href="/lms/main/cloud/cloudvmstatus.php?status=suspend&<?=$str2?>"  class="content31">暂停实验</a> 
    <a  href="/lms/main/cloud/cloudvmstatus.php?status=resume&<?=$str2?>" class="content41">恢复实验</a> 
    <a  href="/lms/main/cloud/cloudvmstatus.php?status=stop&<?=$str2?>" class="content51">关闭实验</a> 
    <a  href="/lms/main/cloud/cloudvmstatus.php?status=reset&<?=$str2?>" class="content61">重启实验</a>
    <!--
    <a  href="/lms/main/cloud/cloudsnapshot.php<?=$str?>"  class="content1">实验截屏</a> 
    <a  href="/lms/main/cloud/cloudvmstatus.php?rec.php<?=$str?>"  class="content2">实验录屏</a> 
    <a  href="/lms/main/cloud/cloudvmstatus.php?suspend.php<?=$str?>"  class="content3">暂停实验</a> 
    <a  href="/lms/main/cloud/cloudvmstatus.php?resume.php<?=$str?>" class="content4">恢复实验</a> 
    <a  href="/lms/main/cloud/cloudvmstatus.php?shutdown.php<?=$str?>" class="content5">关闭实验</a> 
    <a  href="/lms/main/cloud/cloudvmstatus.php?reset.php<?=$str?>" class="content6">重启实验</a>
    -->
</body>
</html>