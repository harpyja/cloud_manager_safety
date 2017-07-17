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
//首页  左侧菜单
	var $cloudindex = $("<div class='navs'><dl class='nav-list'><dt>选课中心</dt><dd class='two-nav-list'><ul><li><a href='course_applied.php' title='选课记录'>选课记录</a></li></ul></dd></dl>" +
        "<dl class='nav-list'><dt>学习中心</dt><dd class='two-nav-list'><ul><li><a href='select_study.php' title='选课中心'>选课中心</a></li><li><a href='learning_center.php' title='我的课程'>我的课程</a></li>" +
        "<li><a href='assignment_list.php' title='我的课程作业'>我的课程作业</a></li><li><a href='learning_progress.php' title='课程学习档案'>课程学习档案</a></li></ul></dd></dl>" +
        "<dl class='nav-list'><dt>考试中心</dt><dd class='two-nav-list'><ul><li><a href='exam_center.php' title='我的自我测验'>我的自我测验</a></li>" +
        "<li><a href='exam_center.php?type=1' title='我的综合考试'>我的综合考试</a></li><li><a href='exam_center.php?type=2' title='我的课程毕业考试'>我的课程毕业考试</a></li>" +
        "<li><a href='exam_result.php' title='考试成绩查询'>考试成绩查询</a></li></ul></dd></dl><dl class='nav-list'><dt>调查问卷</dt>" +
        "<dd class='two-nav-list'><ul><li><a href='survey.php' title='调查问卷'>调查问卷</a></li></ul></dd></dl><dl class='nav-list'><dt>用户中心</dt><dd class='two-nav-list'><ul><li><a href='user_profile.php'>用户信息修改</a></li>" +
        "<li><a href='user_center.php'>用户密码修改</a></li></ul></dd></dl></div>");
	$(".cloudindex").append($cloudindex);
//选课中心 左侧菜单
	var $select_study = $("<div class='navs'><dl class='nav-list'><dt>选课中心</dt><dd class='two-nav-list'><ul><li><a href='select_study.php' title='选课中心'>选课中心</a></li>" +
        "<li><a href='course_applied.php' title='我的课程'>选课记录</a></li></ul></dd></dl><dl class='nav-list'><dt>课程表</dt>" +
        "<dd class='two-nav-list'><ul><li><a href='syllabus.php' title='课程表'>课程表</a></li></ul></dd></dl></div>");
	$(".study-selected").append($select_study);
//学习中心  左侧菜单
    var $study_Centre = $("<div class='navs'><dl class='nav-list'><dt>学习中心</dt><dd class='two-nav-list'><ul><li><a href='select_study.php' title='选课中心'>选课中心</a></li>" +
        "<li><a href='learning_center.php' title='我的课程'>我的课程</a></li><li><a href='assignment_list.php' title='我的课程作业'>我的课程作业</a></li>" +
        " <li><a href='learning_progress.php' title='课程学习档案'>课程学习档案</a></li></ul></dd></dl><dl class='nav-list'><dt>调查问卷</dt>" +
        "<dd class='two-nav-list'><ul><li><a href='survey.php' title='调查问卷'>调查问卷</a></li></ul></dd></dl><dl class='nav-list'><dt>学习档案</dt>" +
        "<dd class='two-nav-list'><ul><li><a href='learning_progress.php' title='课程学习档案'>课程学习档案</a></li></ul> </dd></dl></div>");
	$(".study-Centre").append($study_Centre);
//考试中心 左侧菜单
	var $examCentre = $("<div class='navs'><dl class='nav-list'><dt>考试中心</dt><dd class='two-nav-list'><ul><li><a href='exam_center.php' title='我的自我测验'>我的自我测验</a></li>" +
        "<li><a href='exam_center.php?type=1' title='我的综合考试'>我的综合考试</a></li><li><a href='exam_center.php?type=2' title='我的课程毕业考试'>我的课程毕业考试</a></li> " +
        "<li><a href='exam_result.php' title='考试成绩查询'>考试成绩查询</a></li></ul></dd></dl></div>");
	$(".exam-Centre").append($examCentre);
//我的桌面
    var $mydesktop = $("<div class='navs'><dl class='nav-list'><dt>我管理课程</dt><dd class='two-nav-list'><ul><li><a href='/lms/portal/sp/teacher_portal.php' title='我的管理课程'>我的管理课程</a></li></ul></dd></dl>" +
        "<dl class='nav-list'><dt>考卷批改</dt><dd class='two-nav-list'><ul><li><a href='/lms/portal/sp/exam/exam_corrected_list.php' title='考卷批改'>考卷批改</a></li></ul></dd></dl>" +
        "<dl class='nav-list'><dt>远程协助</dt><dd class='two-nav-list'><ul><li><a href='/lms/portal/sp/vmmanage_iframe.php' title='远程协助'>远程协助</a></li></ul></dd></dl></div>");
    $(".mydesktop").append($mydesktop);
    
//实验报告 左侧菜单
	var $labsreport = $("<div class='navs'><dl class='nav-list'><dt>实验报告</dt><dd class='two-nav-list'><ul><li><a href='exam_center.php' title='实验报告'>实验报告</a></li>" +
        "<li><a href='exam_center.php?type=1' title='实验报告'>实验报告</a></li><li><a href='exam_center.php?type=2' title='实验报告'>实验报告</a></li> " +
        "<li><a href='exam_result.php' title='实验报告'>实验报告</a></li></ul></dd></dl></div>");
	$(".labs-report").append($labsreport);
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
             $("#sidebar").animate({width:"12%"}).attr("class","column open");
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
		var $screenList = $(".screen-list>dd");
		$screenList.click(function(){
			var index = $screenList.index(this);
			$(".screenContent>div").eq(index).show("slow").siblings().hide("slow");
		})
})
