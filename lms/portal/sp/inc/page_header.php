 <?php
include_once '../../main/inc/global.inc.php';

if(isset($_GET['action']) && $_GET['action']=='shutdown'){
       exec("sudo -u root /sbin/cloudconfigreboot.sh shutdown"); // echo "关闭系统！";
} 
$user_id = $_SESSION['_user']['user_id'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=api_get_setting ( 'siteName' )?></title>
<link rel="stylesheet" href="<?=WEB_QH_PATH?>css/layout.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?=WEB_QH_PATH?>js/jbox-v2.3/jBox/Skins/Blue/jbox.css" media="all"/>
<link type="text/css" rel="stylesheet" href="<?= WEB_QH_PATH?>css/base.css">
<link type="text/css" rel="stylesheet" href="<?= WEB_QH_PATH?>css/course-intr.css">
<link type="text/css" rel="stylesheet" href="<?= WEB_QH_PATH?>css/lab-need.css">
<link href="/themes/js/jquery-plugins/Impromptu.css" rel="stylesheet" type="text/css" media="screen" />
<link type="text/css" rel="stylesheet" href="css/learn-center.css">
<script type="text/javascript" src="/themes/js/commons.js"></script>
<script type="text/javascript" src="/themes/js/jquery-plugins/jquery-impromptu.2.7.min.js"></script>
<!-- <script src="js/core.js" type="text/javascript"></script>  -->
<script src="<?= WEB_QH_PATH?>js/jquery-1.7.2.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?=WEB_QH_PATH?>js/jbox-v2.3/jBox/jquery.jBox-2.3.min.js"></script>
<script type="text/javascript" src="<?=WEB_QH_PATH?>js/jbox-v2.3/jBox/i18n/jquery.jBox-zh-CN.js"></script>
<script type="text/javascript">
        $(function(){
            $('.column').equalHeight();
        });
</script>

<script type="text/javascript">
function openWindow(width,height,url,title){
	var w = width;
	var h = height;
	var u = url;
	var t = title;
	$('.column').equalHeight();
	$.jBox(u, {
		title: t,
		width: w,
		height: h,
		buttons: { '关闭': true }
	});
}     
</script>

<script type="text/javascript">
 
//input失去焦点和获得焦点
 $(document).ready(function(){
 //focusblur
     jQuery.focusblur = function(focusid) {
 var focusblurid = $(focusid);
 var defval = focusblurid.val();
         focusblurid.focus(function(){
 var thisval = $(this).val();
 if(thisval==defval){
                 $(this).val("");
             }
         });
         focusblurid.blur(function(){
 var thisval = $(this).val();
 if(thisval==""){
                 $(this).val(defval);
             }
         });
         
     };
 /*下面是调用方法*/
     $.focusblur("#searchkey");
 });

	
//	$(function(){
//		var $close = $("#confirmExit");
//		$close.click(function(){
//			var submit = function (v, h, f){
//			if (v == true)
//				jBox.tip("操作成功..系统正在退出", 'info');
//				location.href="../../main/cloud/cloudvmstop.php?user_id=<?=$user_id?>";
//			}
//            // 自定义按钮
//            $.jBox.confirm("你确定退出系统吗？", "系统提示", submit, { buttons: { '确定': true} });
//		})
//	})
//上面的退出中jBox和$(function()在页面中都存在冲突
  function closebtn(){     
             if(confirm("你确定退出系统吗？")){
                 location.href="<?=URL_APPEND?>main/cloud/cloudvmstop.php?user_id=<?=$user_id?>";
             }
  }
 
</script>
<?php
   
      echo import_assets ( "js/html5.js",WEB_QH_PATH);
      echo import_assets ( "js/jquery-1.5.2.min.js" ,WEB_QH_PATH);
      echo import_assets ( "js/hideshow.js" ,WEB_QH_PATH);
      echo import_assets ( "js/jquery.tablesorter.min.js", WEB_QH_PATH );
      echo import_assets ( "js/jquery.equalHeight.js" ,WEB_QH_PATH);
      echo import_assets ( "commons.js" );
      echo import_assets ( "jquery-plugins/Impromptu.css", api_get_path ( WEB_JS_PATH ) );
      echo import_assets ( "jquery-plugins/jquery-impromptu.2.7.min.js" );
      echo import_assets ( "jquery-plugins/jquery.wtooltip.js" , api_get_path ( WEB_JS_PATH ));
      echo import_assets ( "js/portal.js", WEB_QH_PATH );

    ?>
 
  <script type="text/javascript">
 $(document).ready(function(){
 	$(".navitm").mouseover(function(){
 		$(this).children(".i-mc").css("display","block");
 		 $(this).addClass("selected"); 
 		$(".navitm").mouseout(function(){
 			$(this).children(".i-mc").css("display","none");
 			 $(this).removeClass("selected"); 
 		});
 	});
 }) 


 $(document).ready(function(){
		$('.u-categ li').bind('click',function(){
			var thisIndex = $(this).index();
//			$(this).addClass('cur').siblings().removeClass('cur');
			$('.g-mn1 .g-mn1c').eq(thisIndex)
			.show().siblings().hide();	
		});	

  $('.u-card').mouseover(function(){
      $(this).children('.card').children('.descd').css('bottom','0');
      $(this).children('.card').children('.descd').css('display','block');
  });
  $('.u-card').mouseout(function(){
      $(this).children('.card').children('.descd').css('bottom','-136');
      $(this).children('.card').children('.descd').css('display','none');
  })

	})

 function enterclick(){
    $("#j-search2").css("background","#fff");
	$("#auto-id-rTOGAi3MiQOM7HrB").css("background","#fff");
    $("#auto-id-24pyTEn5cDBJ6Hon").css("display","none");
 }
 function onmouseout(){
    $("#j-search2").css("background","#fff");
	$("#auto-id-rTOGAi3MiQOM7HrB").css("background","#fff");
    $("#auto-id-24pyTEn5cDBJ6Hon").css("display","none");
 }
 </script> 
<style>
    #sub1{
      width:50px; 
      height:27px;
      background:#5C5F68;
      color:#bbb;
      font-size: 14px;
     margin: -29px 0 0 205px;
      border: 0;
      box-shadow:0 0 0;
    }
/*    #j-fixed-head{
        position:relative;
    }*/
</style>
</head>

<body> 
<!--     <div class="m-maintainInfo" style="display:none;">
         <div id="maintain_info_box" class="g-flow"></div>
     </div>-->
     <div id="j-fixed-head" class="g-hd f-bg1">
         <div class="g-flow">
             <div class="m-header f-pr f-cb"> 
                 <div class="m-logo logo-img left">
                    <a hidefocus="true" href="#">
                        <img src="<?=URL_APPEDND."/panel/default/assets/images/logo4.gif"?>" width="218px" height="50px">
                    </a>
                 </div>
                <div class="n-logo logo-img left">
                    <a hidefocus="true" href="#">
                        <img src="<?= WEB_QH_PATH?>images/home5.png">
                    </a>
                </div>
                 <div class="m-nav f-cb left" id="j-navFind">
                     <?php
                     if(api_get_setting ( 'enable_modules', 'course_center' ) == 'true'){
                        $sql =  "select id from setup order by id LIMIT 0,1";
                        $courseId= DATABASE::getval ( $sql, __FILE__, __LINE__ );
                        if($courseId){
                            echo '<a hidefocus="true" href="'.URL_APPEND.'portal/sp/select_study.php?id='.$courseId.'"  title="选课中心">选课中心</a>';
                        }else{
                            echo '<a hidefocus="true" href="'.URL_APPEND.'portal/sp/select_study.php"  title="选课中心">选课中心</a>';
                        }
                     }
                     ?>
                        
                        <a hidefocus="true" href="<?= URL_APPEND?>portal/sp/learning_before.php" title='学习中心'>学习中心</a>
                        <?php
                        if(api_get_setting ( 'enable_modules', 'exam_center' ) == 'true'){
                        ?>
                        <a hidefocus="true" href="<?= URL_APPEND?>portal/sp/exam_list.php" title='考试中心'>考试中心</a>
                        <?php
                        }
                        ?>
                        <a hidefocus="true" href="<?= URL_APPEND?>portal/sp/labs_report.php" title='报告管理'>报告管理</a>
                        <a hidefocus="true" href="<?= URL_APPEND?>portal/sp/user_profile.php" title='用户中心'>用户中心</a>
                        <?php 
                        if(api_get_setting ( 'enable_modules', 'router_center' ) == 'true'){
                        ?>
                        <a hidefocus="true" href="<?= URL_APPEND?>router/html/labs.php" title='路由交换'>路由交换</a>
                        <?php
                        }
                        ?>
                </div>
                <div class="m-links right" id="j-topnav">
                    <div class="unlogin">
                           <!--<a href="<?= URL_APPEND?>portal/sp/login.php" title="登录/注册">登录/注册</a>-->
                         <?php
                            if (api_is_admin ()){
                                echo '<a class="helpex dd2" target="_top" onclick="closebtn()" title="安全退出">退出</a>';
                                echo '<a style="text-decoration:none;"> | </a>';
                                echo '<a style="text-decoration:none;" href="'.$url.'?action=shutdown" class="shutdown" title="关机" target="_top" >关机</a>';
                                echo '<a style="text-decoration:none;"> | </a>';
                                echo '<a style="text-decoration:none;" href="'.URL_APPEDND.'/main/admin/index.php" title="后台管理" target="_blank">后台</a>';
                            }else{
                                echo '<a class="helpex dd2" target="_top" onclick="closebtn()"title="安全退出">安全退出</a>';
                            }
    //                    ?>
                   </div>
                </div>
                 <?php  
                        $page_name=  page_name();
                        if($page_name=="select_study.php"){
                            echo '<form action="course_catalog.php" method="post"  name="form_sel">';
                        }else{
                            echo '<form action="#" method="post"  name="form_sel">';
                        }
                 ?>
                 
                <div class="nav-search u-searchUI right" id="j-searchP">
                 <?php   
                        if($page_name=="select_study.php" || $page_name=="course_catalog.php" || $page_name=="learning_center.php" || $page_name=="labs_report.php"  ||$page_name=="exam_list.php"||$page_name=="course_snapshot_list.php"){
                    ?>
                    <div class="box j-search f-cb off" id="j-search2" onclick="enterclick()">
                        <div class="submit j-submit f-hide left">搜索课程</div> 
                           <input type="text" name='auto-id-rTOGAi3MiQOM7HrB' class="j-input left" id="auto-id-rTOGAi3MiQOM7HrB" onclick="enterclick()">
                           <label class="j-label"  id="auto-id-24pyTEn5cDBJ6Hon" style>请输入搜索关键词</label> 
                          
                    </div>
                        <?php } ?>
<!--                   <div class="j-suggest sug"></div>-->
                    <input type="submit" id='sub1' value="提交" class='sub1' style="display:none;"/>
                </div>
                </form>
             </div>
         </div>
         
       
     </div>
  
             <?php  
                $url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
                $name = $_SESSION['_user']['firstName'];
                $dept_id = $_SESSION['_user']['dept_id'];
                $sign_date =date('Y-m-d H:i:s',time());
                $username = $_SESSION['_user']['username'];
                $sql="select count(*) from work_attendance where username='".$username."' and mode=1";
                $mode=Database::getval($sql,__FILE__,__LINE__);  
                if(!api_is_admin()){ 
                   if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'work_attendance'"))!=1){}else{ 
                           if($mode==0 OR $mode==''){
                               echo '<div class="dayq"><span id="mydate"></span> <a class="qian" href="'.$url.'?action=sign"   target="_top" >签到</a></div>';
                           }else{
                               echo '<div class="dayq"><span id="mydate"></span> <a class="qian" href="'.$url.'?action=sign_return" target="_top" >签退</a></div>';
                           }
                       
                   }
               }
               
                   if (isset ( $_GET ['action'] )) {   
                    switch ($_GET ['action']) {
                        case 'sign' :
                            $sql= "INSERT INTO `work_attendance` (`id`, `username`, `name`, `dept_name`, `sign_date`, `sign_return_date`, `mode`) VALUES (NULL, '".$username ."', '".$name."', '".$dept_id."', '".$sign_date."', '0000-00-00 00:00:00', '1');";
                            $result = api_sql_query ( $sql, __FILE__, __LINE__ );   
                            if($result){
                                echo "<script language='javascript' type='text/javascript'>";
                                echo "window.location.href='$url'";
                                echo "</script>";
                            }
                            break;
                        case 'sign_return' :
                            $sql="select sign_date from work_attendance where  `work_attendance`.`username` ='".$username."' and `mode` ='1'";
                            $res=api_sql_query($sql,__FILE__,__LINE__);
                            $dates=Database::fetch_row($res);
                            $startdate= $dates[0];
                            $range=floor((strtotime($sign_date)-strtotime($startdate))%86400/60);
                            $sql= "UPDATE  `work_attendance` SET  `sign_return_date` =  '".$sign_date."',`mode` =  '2',`range`='".$range."' WHERE  `work_attendance`.`username` ='".$username."' and `mode` ='1'";
                            $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                            if($result){
                                echo "<script language='javascript' type='text/javascript'>";
                                echo "window.location.href='$url'";
                                echo "</script>";
                            }
                            break;
                    }
                }
            ?>
 
         
<!--   <div class="b-70"></div>-->
    
    <?php
    //获取当前url中php页面名
    function page_name(){
        $page_name=$_SERVER['PHP_SELF'];
        $page_name=  explode('/', $page_name);
        $page_name=$page_name[count($page_name)-1];
        return $page_name;
    }
if ($htmlHeadXtra && is_array ( $htmlHeadXtra )) {
	foreach ( $htmlHeadXtra as $head_html ) {
		echo $head_html;
	}
}
?>
<script type="text/javascript">
    $(function(){
        var dtime = new Date();
        var week = dtime.getDay();   
        switch(week){
            case 0:
                week = "星期日";
                break;
            case 1:
                week = "星期一";
                break;
            case 2:
                week = "星期二";
                break;
            case 3:
                week = "星期三";
                break;
            case 4:
                week = "星期四";
                break;
            case 5:
                week = "星期五";
                break;
            case 6:
                week = "星期六";
                break;
        }
 
        $("#mydate").html(week);
    })
</script>