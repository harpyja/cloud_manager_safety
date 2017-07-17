//  Andy Langton's show/hide/mini-accordion @ http://andylangton.co.uk/jquery-show-hide

// this tells jquery to run the function below once the DOM is ready
$(document).ready(function() {

// choose text for the show/hide link - can contain HTML (e.g. an image)
var showText='展开';
var hideText='关闭';

// initialise the visibility check
var is_visible = false;

// append show/hide links to the element directly preceding the element with a class of "toggle"
$('.toggle').prev().append(' <a href="#" class="toggleLink">'+hideText+'</a>');

// hide all of the elements with a class of 'toggle'
$('.toggle').show();

// capture clicks on the toggle links
$('a.toggleLink').click(function() {

// switch visibility
is_visible = !is_visible;

// change the link text depending on whether the element is shown or hidden
if ($(this).text()==showText) {
$(this).text(hideText);
$(this).parent().next('.toggle').slideDown('slow');
}
else {
$(this).text(showText);
$(this).parent().next('.toggle').slideUp('slow');
}
// return false so any link destination is not followed
return false;

});
});

//首页导航 && 菜单栏目
$(function(){
//网站头部
	var $header = $("");
	$("#header").append($header);
//前台页面全局导航
	var $nav_li = $("");
	$("#secondary_bar").append($nav_li);

//====== LMS start ===========================================================
/** LMS首页  左侧菜单 **/
	var $cloudindex = $("<div class='navs'>" +
        "<dl class='nav-list'><dt>选课中心</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='select_study.php?type=jc' title='基础选课中心'>基础选课中心</a></li>" +
	    "<li><a href='select_study.php' title='信安选课中心'>信安选课中心</a></li>" +
            "<li><a href='select_study.php?type=gf' title='攻防实训'>攻防实训</a></li>" +
            "<li><a href='select_study.php?type=xy' title='协议分析与开发'>协议分析与开发</a></li>" +
            "<li><a href='course_applied.php' title='选课记录'>选课记录</a></li></ul></dd></dl>" +
        "<dl class='nav-list'><dt>学习中心</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='learning_center.php' title='我的课程'>我的课程</a></li>" +
            "<li><a href='assignment_list.php' title='课程作业'>课程作业</a></li>" +
            "<li><a href='learning_progress.php' title='学习档案'>学习档案</a></li>" +
            "</ul></dd></dl>" +
        "<dl class='nav-list'><dt>课程表</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='syllabus.php' title='课程表'>课程表</a></li>" +
            "</ul></dd></dl>" +
        "<dl class='nav-list'><dt>调查问卷</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='survey.php' title='调查问卷'>调查问卷</a></li>" +
            "</ul></dd></dl>" +
//        "<dl class='nav-list'><dt>考试中心</dt><dd class='two-nav-list'><ul>" +
//            "<li><a href='exam_center.php' title='我的自我测验'>我的自我测验</a></li>" +
//            "<li><a href='exam_center.php?type=1' title='我的综合考试'>我的综合考试</a></li>" +
//            "<li><a href='exam_center.php?type=2' title='我的课程毕业考试'>我的课程毕业考试</a></li>" +
//            "<li><a href='exam_result.php' title='考试成绩查询'>考试成绩查询</a></li>" +
//            "</ul></dd></dl>" +
        "<dl class='nav-list'><dt>用户中心</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='user_profile.php'  title='信息修改'>信息修改</a></li>" +
            "<li><a href='user_center.php'  title='密码修改'>密码修改</a></li>" +
            "<li><a href='work_attendance.php' title='我的考勤'>我的考勤</a></li>" +
            "</ul></dd></dl>" +
        "</div>");
	$(".cloudindex").append($cloudindex);
/** LMS选课中心 左侧菜单 **/
	var $select_study = $("<div class='navs'>" +
        "<dl class='nav-list'><dt>选课中心</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='select_study.php?type=jc' title='基础选课中心'>基础选课中心</a></li>" +
	    "<li><a href='select_study.php' title='信安选课中心'>信安选课中心</a></li>" +
             "<li><a href='select_study.php?type=gf' title='攻防实训'>攻防实训</a></li>" +
            "<li><a href='select_study.php?type=xy' title='协议分析与开发'>协议分析与开发</a></li>" +
            "<li><a href='course_applied.php' title='我的课程'>选课记录</a></li>" +
            "</ul></dd></dl>" +
        "<dl class='nav-list'><dt>课程表</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='syllabus.php' title='课程表'>课程表</a></li>" +
            "</ul></dd></dl>" +
    "</div>");
	$(".study-selected").append($select_study);
/** LMS学习中心  左侧菜单 **/
    var $study_Centre = $("<div class='navs'>" +
        "<dl class='nav-list'><dt>学习中心</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='learning_center.php' title='我的课程'>我的课程</a></li>" +
            "<li><a href='assignment_list.php' title='课程作业'>课程作业</a></li>" +
             "<li><a href='learning_progress.php' title='学习档案'>学习档案</a></li>" +
//            "</ul></dd></dl>" +
//        "<dl class='nav-list'><dt>调查问卷</dt><dd class='two-nav-list'><ul>" +
//            "<li><a href='survey.php' title='调查问卷'>调查问卷</a></li>" +
//            "</ul></dd></dl>" +
//        "<dl class='nav-list'><dt>学习档案</dt><dd class='two-nav-list'><ul>" +
//            "<li><a href='learning_progress.php' title='学习档案'>学习档案</a></li>" +
            "</ul> </dd></dl></div>");
    $(".study-Centre").append($study_Centre);
    
 /** LMS调查问卷  左侧菜单 **/
        var $survey = $("<div class='navs'>" +
        "<dl class='nav-list'><dt>调查问卷</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='survey.php' title='调查问卷'>调查问卷</a></li>" +
            "</ul> </dd></dl></div>");
    $(".survey").append($survey);
   
    /** LMS学习档案  左侧菜单 **/
        var $learning_progress = $("<div class='navs'>" +
        "<dl class='nav-list'><dt>学习档案</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='learning_progress.php' title='学习档案'>学习档案</a></li>" +
            "</ul> </dd></dl></div>");
    $(".learning_progress").append($learning_progress);
/** LMS考试中心 左侧菜单**/
 var $examCentre = $("<div class='navs'>" +
        "<dl class='nav-list'><dt>考试中心</dt><dd class='two-nav-list'><ul>" +
//            "<li><a href='exam_center.php' title='我的自我测验'>我的自我测验</a></li>" +
//            "<li><a href='exam_center.php?type=1' title='我的综合考试'>我的综合考试</a></li>" +
//            "<li><a href='exam_center.php?type=2' title='我的课程毕业考试'>我的课程毕业考试</a></li> " +
            "<li><a href='exam_center_list.php' title='我的考试'>我的考试</a></li>" +
            "<li><a href='exam_result.php' title='考试成绩查询'>考试成绩查询</a></li></ul></dd></dl></div>");
 $(".exam-Centre").append($examCentre);



/** LMS实验报告管理左侧 **/
    var $labsreport = $("<div class='navs'>" +
        "<dl class='nav-list'><dt>实验报告</dt><dd class='two-nav-list'><ul> " +
            "<li><a href='labs_report.php' title='我的报告'>我的实验报告</a></li>" +
            "<li><a href='/lms/portal/sp/course_snapshot_list.php' title='我的实验图片录像'>我的实验图片录像</a></li></ul></dd></dl></div>");
    $(".labsreport").append($labsreport);
/** LMS我的桌面 **/
//    var $mydesktop = $("<div class='navs'>" +
//        "<dl class='nav-list'><dt>我管理课程</dt><dd class='two-nav-list'><ul>" +
//            "<li><a href='/lms/portal/sp/teacher_portal.php' title='我的管理课程'>我的管理课程</a></li>" +
//            "</ul></dd></dl>" +
//        "<dl class='nav-list'><dt>考卷批改</dt><dd class='two-nav-list'><ul>" +
//            "<li><a href='/lms/portal/sp/exam/exam_corrected_list.php' title='考卷批改'>考卷批改</a></li>" +
//            "</ul></dd></dl></div>");
//    $(".mydesktop").append($mydesktop);
//======   LMS  start ===========================================================
//站内信
    var $msg = $("<div class='navs'>" +
        "<dl class='nav-list'><dt>站内信</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='msg_view.php' title='站内信'>站内信</a></li>"+
             "</li></ul></dd>" +
            "</dl></div>");
    $(".msg").append($msg);


//======monitor start ===========================================================
/** monitor首页  左侧菜单 **/
    var $monitorindex = $("<div class='navs'>" +
        "<dl class='nav-list'><dt>基础关入口</dt><dd class='two-nav-list'><ul>"+
            "<li><a href='exam_list.php' title='考试管理'>考试管理</a></li>"+
            "<li><a href='exam_results.php' title='考试成绩查询'>考试成绩查询</a></li>"+
            "</ul></dd></dl>"+
        "<dl class='nav-list'><dt>任务调度</dt><dd class='two-nav-list'><ul>"+
            "<li><a href='template_list.php' title='我的模板调度'>我的模板调度</a></li>"+
            "<li><a href='group_list.php' title='我的用户调度'>我的用户调度</a></li>"+
            "</ul></dd>"+
//        "<dl class='nav-list'><dt>我的成绩</dt><dd class='two-nav-list'><ul>"+
//          "<li><a href='exam_result.php' title='我的竞赛成绩'>我的竞赛成绩</a></li>" +
//          "<li><a href='exam_result.php' title='我的实战成绩'>我的实战成绩</a></li>" +
//        "</ul></dd></dl>" +
        "<dl class='nav-list'><dt>公告信息</dt><dd class='two-nav-list'><ul>"+
            "<li><a href='system_announcements.php' title='演练公告'>演练公告</a></li>" +
            "</ul></dd></dl>"+
        "<dl class='nav-list'><dt>大赛信息</dt><dd class='two-nav-list'><ul>"+
            "<li><a href='summary.php' title='大赛简介'>大赛简介</a></li>" +
            "</ul></dd></dl>"+
        "<dl class='nav-list'><dt>安全评估</dt> <dd class='two-nav-list'><ul>" +
            "<li><a href='pro_index.php' title='安全评估'>安全评估</a></li>" +
            "</ul></dd></dl>" +
        "<dl class='nav-list'><dt>用户中心</dt><dd class='two-nav-list'><ul>"+
            "<li><a href='user_profile.php' title='信息修改'>信息修改</a></li>" +
            "<li><a href='user_center.php?type=1' title='密码修改'>密码修改</a></li>"+
            "<li><a href='work_attendance.php' title='我的考勤'>我的考勤</a></li>" +
            "</ul></dd></dl>"+
        "</div>");
    $(".monitorindex").append($monitorindex);
/** monitor竞赛入口-考试  左侧菜单 **/
    var $examCentre = $("<div class='navs'>" +
        "<dl class='nav-list'>" +
            "<dt>基础关入口</dt><dd class='two-nav-list'><ul>"+
                "<li><a href='exam_list.php' title='考试管理'>考试管理</a></li>"+
                "<li><a href='exam_results.php' title='考试成绩查询'>考试成绩查询</a></li>"+
            "</ul> </dd></dl>" +
        "<dl class='nav-list'><dt>实验报告</dt><dd class='two-nav-list'><ul>"+
                "<li><a href='labs_report.php' title='我的实验报告'>我的实验报告</a></li>" +
            "</ul></dd></dl>"+
    "</div>");
    $(".exam").append($examCentre);

/** monitor 演练信息 **/
    var $info = $("<div class='navs'>" +
        "<dl class='nav-list'><dt>公告信息</dt><dd class='two-nav-list'><ul>" +
                "<li><a href='system_announcements.php' title='演练公告'>演练公告</a></li>" +
            "</ul></dd></dl>"+
        "<dl class='nav-list'><dt>用户中心</dt><dd class='two-nav-list'><ul>"+
                "<li><a href='user_profile.php' title='信息修改'>信息修改</a></li>" +
                "<li><a href='user_center.php?type=1' title='密码修改'>密码修改</a></li>"+
                "<li><a href='work_attendance.php' title='我的考勤'>我的考勤</a></li>" +
            "</ul></dd></dl>"+
    "</div>");
    $(".info").append($info);
/** monitor实战入口-调度  左侧菜单**/
    var $control = $("<div class='navs'>" +
        "<dl class='nav-list'>" +
            "<dt>分组对抗入口</dt><dd class='two-nav-list'><ul>" +
                "<li><a href='template_list.php' title='我的模板'>我的模板</a></li>"+
                "<li><a href='group_list.php' title='我的小组'>我的小组</a></li>" +
                "</li></ul></dd>"+
        "<dl class='nav-list'>" +
            "<dt>分组对抗报告</dt><dd class='two-nav-list'><ul>"+
                "<li><a href='counterwork_report.php' title='我的分组对抗报告'>分组对抗报告</a></li>" +
                "</ul></dd></dl>"+
        "</dl></div>");
    $(".control").append($control);
/** monitor我的成绩  左侧菜单 **/
    var $score = $("<div class='navs'>" +
        "<dl class='nav-list'><dt>我的成绩</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='template_list.php' title='我的竞赛成绩'>我的竞赛成绩</a></li>"+
            "<li><a href='group_list.php' title='我的实战成绩'>我的实战成绩</a></li>" +
            "</li></ul></dd>" +
            "</dl></div>");
    $(".score").append($score);
/** monitor用户中心 左侧菜单 **/
    var $examCentre = $("<div class='navs'>" +
        "<dl class='nav-list'><dt>用户中心</dt><dd class='two-nav-list'><ul>"+ 
             "<li><a href='user_profile.php'  title='信息修改'>信息修改</a></li>" +
            "<li><a href='user_center.php'  title='密码修改'>密码修改</a></li>" +
            "<li><a href='work_attendance.php' title='我的考勤'>我的考勤</a></li>" +
            "</ul></dd></dl></div>");
    $(".user-Centre").append($examCentre);
/** monitor公告信息 **/
    var $announcement =$("<div class='navs'>" +
        "<dl class='nav-list'><dt>公告信息</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='system_announcements.php' title='演练公告'>演练公告</a></li>" +
            "</ul></dd>"+
            "</dl></div>");
    $(".announcement").append($announcement);
/** monitor大赛简介 **/
    var $summary =$("<div class='navs'>" +
        "<dl class='nav-list'><dt>大赛信息</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='summary.php' title='大赛简介'>大赛简介</a></li>" +
            "</ul></dd>"+
            "</dl></div>");
    $(".summary").append($summary);
/* MONITOR安全评估*/
    $evaluate =$("<div class='navs'>" +
        "<dl class='nav-list'><dt>安全评估</dt> <dd class='two-nav-list'><ul>" +
        "<li><a href='pro_index.php' title='安全评估'>安全评估</a></li>" +
        "</ul></dd></dl></div>");
    $(".evaluate").append($evaluate);
/* MONITOR导调工具*/
    $tools =$("<div class='navs'>" +
        "<dl class='nav-list'><dt>导调工具</dt> <dd class='two-nav-list'><ul>" +
        "<li><a href='tools.php' title='导调工具'>导调工具</a></li>" +
        "</ul></dd></dl></div>");
    $(".tools ").append($tools );
/** monitor夺旗入口 **/
    var $flag =$("<div class='navs'>" +
        "<dl class='nav-list'><dt>夺旗入口</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='flag.php' title='夺旗位置 '>夺旗位置</a></li>" +
        "</ul></dd>"+
        "<dl class='nav-list'><dt>夺旗报告</dt><dd class='two-nav-list'><ul>" +
        "<li><a href='flag_report.php' title='我的夺旗报告'>我的夺旗报告</a></li>" +
        "</ul></dd>"+
        "</dl></div>");
    $(".flag").append($flag);
/** monitor远程协助 **/
    var $remote =$("<div class='navs'>" +
        "<dl class='nav-list'><dt>远程协助</dt><dd class='two-nav-list'><ul>" +
        "<li><a href='vmmanage_iframe.php' title='远程协助'>远程协助</a></li>" +
        "</ul></dd>"+
        "</dl></div>");
    $(".remote ").append($remote);
//======monitor end ===========================================================

})

/*
 * 后台主页选项卡切换
 * Author:Yuhao
 * Date: 2012/12/25
 */
$(function(){
	$("#flexButton").click(function(){
		if($("#sidebar").hasClass("open"))
		{
			 $("#sidebar").animate({width:"1%"}).removeClass("open").attr("class","column close");
			 $(".toggle").hide();
			 $(".navs").hide();
			 $("#main").animate({width:"99%"});
			 $(this).attr("class","closeButton open");
		}else{
             $("#sidebar").animate({width:"15%"}).attr("class","column open");
			 $(".toggle").show();
			 $(".navs").show();
			 $("#main").animate({width:"85%"});
			 $(this).attr("class","closeButton close");
        }
	})
	$("#sidebar").css("height","100%");
	$(".labtable tr:even").css("background","#DEE4E5");
	$(".labtable tr:odd").css("background","#EDF2F5");
	var $div_li = $(".tab li");
	$div_li.click(function(){
		$(this).addClass("selected").siblings().removeClass("selected");
		var index = $div_li.index(this);
		$(".lab-cont>div").eq(index).show().siblings().hide();
	}).hover(function(){
		$(this).addClass("hover");
	},function(){
		$(this).removeClass("hover");
	})
	//lab试验选项卡
	var $nav_dt = $(".nav-list dt");
	$nav_dt.click(function(){
		var $url = $(this).siblings("dd");
		if($url.is(":visible"))
		{
			$url.hide();
		}else{
			$url.show();

		}
		return false;
	});
	//首页tab
	var $div_dt = $(".pagetab>ul>li");
	$div_dt.click(function(){
		$(this).addClass("selected").siblings().removeClass("selected");
		var index = $div_dt.index(this);
		$(".welcome-lin>article").eq(index).show(3000).siblings().hide();
	}).hover(function(){
		$(this).addClass("hover");
	},function(){
		$(this).removeClass("hover");
	});
})
$(function(){
	var $screenList = $(".screen-list>a");
	$screenList.click(function(){
		$(this).addClass("screen-liston").siblings().removeClass("screen-liston");
		$(this).find(".Mitop").show().parent().siblings().find(".Mitop").hide();
		var index = $screenList.index(this);
		$(".screenContent>div").eq(index).show("slow").siblings().hide("slow");
		$(".Mitop").css("left","+=300px");
	})
})
