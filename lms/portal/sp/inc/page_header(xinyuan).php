<?php
//if (! defined ( 'IN_QH' )) exit ( 'Access Denied !' );
if ($_user ["status"] == COURSEMANAGER) {
	$default_home_page = api_get_path ( WEB_PATH ) . "user_portal.php";
}
if (api_is_platform_admin ()) {
	$default_home_page = api_get_path ( WEB_CODE_PATH ) . "admin/index.php";
}
$userName = $_SESSION ['_user'] ['firstName'];
$userNo = $_SESSION ['_user'] ['username'];

/**platform type 20130329 changzf start**/
$platforms=file_get_contents(URL_ROOT.'/www'.URL_APPEDND.'/storage/DATA/platform.conf');
$platform_array=explode(':',$platforms);
$platform=intval(trim($platform_array[1]));
/**platform type 20130329 changzf end**/

if($platform==1){
    $platform_path='../../main/admin/platform/index.php';
    $platform_name= PERMEATE;
}elseif($platform==2){
    $platform_path='../../main/admin/platform/index.php';
    $platform_name= TARGET;
}elseif($platform==3){
    $platform_path='../../main/admin/index.php';
    $platform_name= EXAMS;
}else{
    $platform_path=$default_home_page;
    $platform_name=api_get_setting('siteName');
}
$url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
$user_id = $_SESSION['_user']['user_id'];
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8"/>
    <title><?=api_get_setting ( 'siteName' )?></title>
    <link rel="stylesheet" href="<?=WEB_QH_PATH?>css/layout.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="<?=WEB_QH_PATH?>js/jbox-v2.3/jBox/Skins/Blue/jbox.css" media="all"/>
    <!--[if lt IE 9]>
    <link rel="stylesheet" href="<?=WEB_QH_PATH?>css/ie.css" type="text/css" media="screen" />
    <![endif]-->
    <?php
    echo import_assets ( "js/html5.js",WEB_QH_PATH);
    echo import_assets ( "js/jquery-1.5.2.min.js" ,WEB_QH_PATH);
    echo import_assets ( "js/hideshow.js" ,WEB_QH_PATH);
    echo import_assets ( "js/jquery.tablesorter.min.js", WEB_QH_PATH );
    echo import_assets ( "js/jquery.equalHeight.js" ,WEB_QH_PATH);
    ?>
    <?php
      echo import_assets ( "commons.js" );
//    echo import_assets ( "jquery-latest.js", api_get_path ( WEB_JS_PATH ) );
      echo import_assets ( "jquery-plugins/Impromptu.css", api_get_path ( WEB_JS_PATH ) );
      echo import_assets ( "jquery-plugins/jquery-impromptu.2.7.min.js" );
      echo import_assets ( "jquery-plugins/jquery.wtooltip.js" , api_get_path ( WEB_JS_PATH ));
      echo import_assets ( "js/portal.js", WEB_QH_PATH );
    //echo api_get_path ( WEB_JS_PATH );
    ?>
    
    <!--新引入-->
<link href="/themes/js/jquery-plugins/Impromptu.css" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript" src="/themes/js/commons.js"></script>
<script type="text/javascript" src="/themes/js/jquery-plugins/jquery-impromptu.2.7.min.js"></script>
<script type="text/javascript">
	$(document).ready( function() {
		$("#sub").click( function() {
			$.prompt("你确认要提交本次考试答案吗？",{
					buttons:{'确定':true, '取消':false},
					callback: function(v,m,f){
						if(v){
							$.prompt("正在提交答卷,请不要做任何操作,否则有可能影响你的考试成绩!");
 							btnSumbit_onclick();
						}else{
						}
					}
			});
		});
	});
</script>
<script language="JavaScript" type="text/javascript">
	function btnSumbit_onclick(){//提交试卷
		G("formSub").value="1";
		G("sub").disabled=true;
		G("frm_exercise").submit();//将答卷提交到ExamSave.php里面进行后台数据库操作
	}
</script>
<script type="text/javascript" src="<?=WEB_QH_PATH?>js/jbox-v2.3/jBox/jquery.jBox-2.3.min.js"></script>
<script type="text/javascript" src="<?=WEB_QH_PATH?>js/jbox-v2.3/jBox/i18n/jquery.jBox-zh-CN.js"></script>
    <script type="text/javascript" >

        $(document).ready(function()
                {
                    $(".tablesorter").tablesorter();
                }
        );
        $(document).ready(function() {

            //When page loads...
            $(".tab_content").hide(); //Hide all content
            $("ul.tabs li:first").addClass("active").show(); //Activate first tab
            $(".tab_content:first").show(); //Show first tab content

            //On Click Event
            $("ul.tabs li").click(function() {

                $("ul.tabs li").removeClass("active"); //Remove any "active" class
                $(this).addClass("active"); //Add "active" class to selected tab
                $(".tab_content").hide(); //Hide all tab content

                var activeTab = $(this).find("a").attr("href"); //Find the href attribute value to identify the active tab + content
                $(activeTab).fadeIn(); //Fade in the active ID content
                return false;
            });

        });
    </script>
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
<style type ="text/css">#searchkey{color:gray;}</style>
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
                 location.href="../../main/cloud/cloudvmstop.php?user_id=<?=$user_id?>";
             }
  }
</script>
<?php
    if(isset($_GET['action']) && $_GET['action']=='shutdown'){
        exec("sudo -u root /sbin/cloudconfigreboot.sh shutdown"); // echo "关闭系统！";
    }
    ?>
</head>
<body>
<header id="header">
    <hgroup>
        <h1 class='site_title'>
            <a href='index.php'><?php
                if($platform==1 OR $platform==2 OR $platform==3){
                  echo $platform_name;
                }else{
                  echo '<image src="/lms/panel/default/assets/images/logo4.gif" height="55px"/>';
                }
                ?></a>
        </h1>
        <h2 class="section_title"><span class="welcome"><?php echo '您好:<a class="helpex dd2" href="user_profile.php">'.$userName."(".$userNo.")";echo '</a>'?>,欢迎使用<?=$platform_name?></span>
        </h2>
        <div class="btn">
            <a class="helpex dd2" id="confirmExit" target="_top" onclick="closebtn()">退出</a>
            <?php
            if (api_is_admin ()){
                echo '<a href="'.$url.'?action=shutdown" class="shutdown" title="关机" target="_top" >关机</a>';
            }
            ?>

        <?php if (api_is_platform_admin ()) {
              echo  '<a href="'.$platform_path.'" title="后台管理" target="_blank">后台管理</a></div>';
             }
        ?>
        <?php
        $username = $_SESSION['_user']['username'];
        $name = $_SESSION['_user']['firstName'];
        $dept_id = $_SESSION['_user']['dept_id'];
        $sign_date =date('Y-m-d H:i:s',time());
        $sql="select count(*) from work_attendance where username='".$username."' and mode=1";
        $mode=Database::getval($sql,__FILE__,__LINE__);
if(!api_is_admin()){
        if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'work_attendance'"))!=1){}else{
            if($platform==1 OR $platform==2 OR $platform==3){}else{
                if($mode==0 OR $mode==''){
                    echo '<div class="dayq"><span id="mydate"></span>你今天还没有签到！ <a class="qian" href="'.$url.'?action=sign"   target="_top" >签到</a></div>';
                }else{
                    echo '<div class="dayq"><span id="mydate"></span><a class="qian" href="'.$url.'?action=sign_return" target="_top" >签退</a></div>';
                }
            }
        }
}
        if (isset ( $_GET ['action'] )) {
            switch ($_GET ['action']) {
                case 'sign' :
                    $sql= "INSERT INTO `vslab`.`work_attendance` (`id`, `username`, `name`, `dept_name`, `sign_date`, `sign_return_date`, `mode`) VALUES (NULL, '".$username ."', '".$name."', '".$dept_id."', '".$sign_date."', '0000-00-00 00:00:00', '1');";
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
                    $sql= "UPDATE  `vslab`.`work_attendance` SET  `sign_return_date` =  '".$sign_date."',`mode` =  '2',`range`='".$range."' WHERE  `work_attendance`.`username` ='".$username."' and `mode` ='1'";
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
    </hgroup>
</header> <!-- end of header bar -->
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
                week = "星期三";
                break;
            case 3:
                week = "星期四";
                break;
            case 4:
                week = "星期五";
                break;
            case 5:
                week = "星期六";
                break;
        }
        $("#mydate").html(week);
    })
</script>
<section id="secondary_bar">
    <ul class='nav'>
        <?php
          if($platform==3){//monitor nav
              $nav_html  = "<li><a href='".URL_APPEND."portal/sp/index.php' title='首页'>首页</a></li>
                    <li><a href='".URL_APPEND."portal/sp/exam_center_list.php' title='基础关入口'>基础关入口</a></li>
                    <li><a href='".URL_APPEND."portal/sp/template_list.php' title='分组对抗入口'>分组对抗入口</a></li>
                    <li><a href='".URL_APPEND."portal/sp/flag.php' title='夺旗入口'>夺旗入口</a></li>
                    <li><a href='".URL_APPEND."portal/sp/exam_results.php' title='演练信息'>演练信息</a></li>
                    <li><a href='".URL_APPEND."portal/sp/pro_index.php' title='安全评估'>安全评估</a></li>
                    <li><a href='".URL_APPEND."portal/sp/tools.php' title='资源下载'>资源下载</a></li>";
        }else{//LMS nav
              $nav_html  = "<li><a href='".URL_APPEND."portal/sp/index.php' title='首页'>首页</a></li>
                  <li><a href='".URL_APPEND."portal/sp/select_study.php?type=jc' title='基础选课中心'>基础选课中心</a></li>
                    <li><a href='".URL_APPEND."portal/sp/select_study.php' title='信安选课中心'>信安选课中心</a></li>
                    <li><a href='".URL_APPEND."portal/sp/learning_center.php' title='学习中心'>学习中心</a></li>
                    <!--li><a href='".URL_APPEND."portal/sp/exam_center.php' title='考试中心'>考试中心</a></li-->
                    <li><a href='".URL_APPEND."router/html/labs.php' title='路由交换实训'>路由交换实训</a></li>
                    <li><a href='".URL_APPEND."portal/sp/labs_report.php' title='实验报告管理'>实验报告管理</a></li>
                    <li><a href='".URL_APPEND."portal/sp/pro_index.php' title='安全评估'>安全评估</a></li>
                    <li><a href='".URL_APPEND."portal/sp/user_profile.php' title='用户中心'>用户中心</a></li>";
                   if ($_user ["status"] == COURSEMANAGER) {
             // $nav_html.=" <li><a href='".URL_APPEND."portal/sp/teacher_portal.php' title='我的桌面'>我的桌面</a></li>";
                   }
        }
    if ($_user ["status"] == COURSEMANAGER) {
        $nav_html.="<li><a href='".URL_APPEND."portal/sp/vmmanage_iframe.php' title='远程协助'>远程协助</a></li>";
    }
   $nav_html.=" <li><a href='".URL_APPEND."portal/sp/msg_view.php' title='站内信'>站内信<span id='vmm' style='color :red'></span></a></li>";
        echo $nav_html;
        ?>

     </ul>
</section><!-- end of secondary bar -->
<?php
if ($htmlHeadXtra && is_array ( $htmlHeadXtra )) {
	foreach ( $htmlHeadXtra as $head_html ) {
		echo $head_html;
	}
}
?>
