$(function(){
	//网站头部	
	var $header = $("");
	$("#header").append($header);
/**
* LMS 后台菜单栏目JS start  =========================================================================
**/
	$index = $("<div class='navs'>" +
        "<dl class='nav-list'><dt>我的桌面</dt><dd class='two-nav-list hide'><ul>" +
            "<li><a href='/lms/user_portal.php' title='我的管理课程'>我的管理课程</a></li>" +
            "<!--li><a href='/lms/main/exam/exam_corrected_list.php' title='考卷批改'>考卷批改</a></li--></ul></dd></dl>" +
        "<dl class='nav-list'><dt>课程管理</dt><dd class='two-nav-list hide'><ul>" +
            "<li><a href='/lms/main/admin/course/course_list.php' title='课程管理'>课程管理</a></li>" +
            "<li><a href='/lms/main/admin/course/course_plan.php' title='课程调度'>课程调度</a ></li>" +
            "<li><a href='/lms/main/admin/course/setup.php' title='课程体系分类'>课程体系</a ></li>" +
            "<li><a href='/lms/main/admin/course/course_category_iframe.php' title='课程分类管理'>课程分类</a></li> " +
//            "<li><a href='/lms/main/admin/import_export/imex_list.php' title='课件资源库管理'>课件资源库</a></li>" +
            "<li><a href='/lms/main/admin/syllabus/syllabus_list.php' title='课程表管理'>课程表管理</a></li>" +
            "<li><a href='/lms/main/admin/course/course_user_manage.php' title='课程调度查看'>调度查看</a></li>" +
            "<li><a href='/lms/main/reporting/learning_progress.php' title='学习情况查询'>学习情况</a></li>" +
            "<li><a href='/lms/main/survey/index.php' title='调查问卷'>调查问卷</a></li>" +
            "</ul></dd></dl>" +
        "<dl class='nav-list'><dt>实验报告管理</dt><dd class='two-nav-list hide'><ul>" +
            "<li><a href='/lms/main/admin/course/reports.php' title='实验报告管理'>实验报告</a ></li>"+
            "</ul></dd></dl>"+
        "<dl class='nav-list'><dt>云平台管理</dt><dd class='two-nav-list hide'><ul>" +
            "<li><a href='/lms/main/admin/vmmanage/vmmanage_iframe.php' title='虚拟化管理'>虚拟化管理</a></li> " +
            "<li><a href='/lms/main/admin/net/vm_list_iframe.php' title='网络拓扑设计'>网络拓扑设计</a></li>" +
            "<li><a href='/lms/main/admin/vmmanage/centralized.php' title='集中管理设置'>集中管理设置</a></li>" +
            "<li><a href='/lms/main/admin/vmdisk/vmdisk_list.php' title='虚拟模板管理'>虚拟模板管理</a></li>" +
//            "<li><a href='/lms/main/admin/token_bucket/token_bucket_list.php' title='令牌桶管理'>令牌桶管理</a></li>" +
            "<li><a href='/lms/main/admin/vmmanage/unified_shut.php' title='虚拟化统一关机'>虚拟化统一关机</a></li>" +
            "<li><a href='/lms/main/admin/vmmanage/ip_info.php' title='IP地址信息'>IP地址信息</a></li>" +
            "<li><a href='/lms/main/admin/vmdisk/vmmaxnumber.php' title='虚拟机设置'>虚拟机设置</a></li>" +
        "</ul></dd></dl>" +
        "<dl class='nav-list'><dt>云桌面管理</dt> <dd class='two-nav-list hide'><ul>" +
            "<li><a href='/lms/main/admin/cloud/clouddesktop.php' title='云桌面终端'>云桌面终端</a></li>" +
            "<li><a href='/lms/main/admin/cloud/clouddesktopdisk.php' title='云桌面存储空间'>云桌面存储空间</a></li>" +
            "<li><a href='/lms/main/admin/cloud/clouddesktopscan.php' title='云桌面扫描'>云桌面扫描</a></li></ul></dd></dl>" +
        "<dl class='nav-list'><dt>路由交换管理</dt><dd class='two-nav-list hide'><ul>" +
            "<li><a href='/lms/main/admin/router/router_type.php' title='路由交换类型管理'>路由交换类型管理</a></li>" +
            "<li><a href='/lms/main/admin/router/labs_ios.php' title='路由交换管理'>路由交换管理</a></li>" +
            "<li><a href='/lms/main/admin/router/labs_mod.php' title='路由交换模块管理'>路由交换模块管理</a></li>" +
            "<li><a href='/lms/main/admin/router/labs_category_iframe.php' title='路由交换课程分类'>路由交换课程分类</a></li>" +
            "<li><a href='/lms/main/admin/router/labs_topo.php' title='网络拓扑设计'>网络拓扑设计</a></li> " +
            "<li><a href='/lms/main/admin/router/labs_device.php' title='网络设备管理'>网络设备管理</a></li>" +
            "<li><a href='/lms/main/admin/router/labs_experimental_anual.php' title='实验交换课程资料'>实验交换课程资料</a></li></ul></dd></dl>"+
        "<dl class='nav-list'><dt>用户管理</dt><dd class='two-nav-list hide'><ul>" +
            "<li><a href='/lms/main/admin/user/user_online.php'>在线用户</a></li>" +
            "<li><a href='/lms/main/admin/dept/dept_iframe.php' title='组织管理'>组织管理</a></li>" +
            "<li><a href='/lms/main/admin/user/user_list_audit.php' title='审核注册用户'>审核用户</a></li>" +
            "<li><a href='/lms/main/admin/user/user_list.php' title='用户管理'>用户管理</a></li>" +
            "<li><a href='/lms/main/admin/user/work_attendance.php' title='用户考勤'>用户考勤</a></li></ul></dd></dl>" +
        " <dl class='nav-list'><dt>系统管理</dt><dd class='two-nav-list hide'><ul>" +
            "<li><a href='/lms/main/admin/misc/settings.php' title='系统设置'>系统设置</a></li>" +
            "<li><a href='/lms/main/admin/log/logging_list.php' title='系统日志'>系统日志</a></li>" +
            "<li><a href='/lms/main/admin/misc/system_announcements.php' title='系统公告'>系统公告</a></li>" +
            "<li><a href='/lms/main/admin/misc/system_upgrade.php' title='系统升级'>系统升级</a></li> " +
            "<li><a href='/lms/main/admin/misc/system_management.php' title='系统管理'>系统管理</a></li>" +
            "<li><a href='/lms/main/admin/systeminfo.php' title='系统信息'>系统信息</a></li></ul></dd></dl>" +
        " <dl class='nav-list'><dt>license管理</dt><dd class='two-nav-list hide'><ul>" +
            "<li><a href='/lms/main/admin/course_license.php' title='课件license管理'>课件license管理</a></li>" +
            "<li><a href='/lms/main/admin/import_export/imex_list.php' title='课件资源库管理'>课件资源库</a></li>" +
        "</ul></dd></dl>"+
        " <dl class='nav-list'><dt>信息传递</dt><dd class='two-nav-list hide'><ul>" +
            "<li><a href='/lms/main/admin/message_list.php' title='信息传递'>信息传递</a></li>" +
        "</ul></dd></dl>"+
        "</div>");
	$(".index").append($index);
/**我的桌面**/
	$mydesktop = $("<div class='navs'><dl class='nav-list'><dt>我管理课程</dt><dd class='two-nav-list'><ul>" +
          "<li><a href='/lms/user_portal.php' title='我的管理课程'>我的管理课程</a></li></ul></dd></dl>" +
//      "<dl class='nav-list'><dt>考卷批改</dt><dd class='two-nav-list'><ul>" +
//          "<li><a href='/lms/main/exam/exam_corrected_list.php' title='考卷批改'>考卷批改</a></li></ul></dd></dl>"+
          "</div>");
	$(".mydesktop").append($mydesktop);
/**云平台**/
	$cloud =$("<div class='navs'><dl class='nav-list'><dt>云平台管理</dt><dd class='two-nav-list'><ul>" +
          "<li><a href='/lms/main/admin/vmmanage/vmmanage_iframe.php' title='虚拟化管理'>虚拟化管理</a></li>" +
          "<li><a href='/lms/main/admin/net/vm_list_iframe.php' title='网络拓扑设计'>网络拓扑设计</a></li>" +
          "<li><a href='/lms/main/admin/vmmanage/centralized.php' title='集中管理设置'>集中管理设置</a></li>" +
          "<li><a href='/lms/main/admin/vmdisk/vmdisk_list.php' title='虚拟模板管理'>虚拟模板管理</a></li>" +
//          "<li><a href='/lms/main/admin/token_bucket/token_bucket_list.php' title='令牌桶管理'>令牌桶管理</a></li>" +
          "<li><a href='/lms/main/admin/vmmanage/unified_shut.php' title='虚拟化统一关机'>虚拟化统一关机</a></li>" +
          "<li><a href='/lms/main/admin/vmmanage/ip_info.php' title='IP地址信息'>IP地址信息</a></li>" +
          "<li><a href='/lms/main/admin/vmdisk/vmmaxnumber.php' title='虚拟机设置'>虚拟机设置</a></li>" +
        "</ul></dd></dl>" +
        "<dl class='nav-list'><dt>云桌面管理</dt> <dd class='two-nav-list'><ul>" +
          "<li><a href='/lms/main/admin/cloud/clouddesktop.php' title='云桌面终端'>云桌面终端</a></li>" +
          "<li><a href='/lms/main/admin/cloud/clouddesktopdisk.php' title='云桌面存储空间'>云桌面存储空间</a></li>" +
          "<li><a href='/lms/main/admin/cloud/clouddesktopscan.php' title='云桌面扫描'>云桌面扫描</a></li></ul></dd></dl> </div>");
	$(".cloud").append($cloud);
/**系统管理 **/
	$systeminfo = $("<div class='navs'><dl class='nav-list'><dt>系统管理</dt><dd class='two-nav-list'><ul>" +
        "<li><a href='/lms/main/admin/misc/settings.php' title='系统设置'>系统设置</a></li>" +
        "<li><a href='/lms/main/admin/log/logging_list.php' title='系统日志'>系统日志</a></li>" +
        "<li><a href='/lms/main/admin/misc/system_announcements.php' title='系统公告'>系统公告</a></li>" +
        "<li><a href='/lms/main/admin/misc/system_upgrade.php' title='系统升级'>系统升级</a></li>" +
        "<li><a href='/lms/main/admin/systeminfo.php' title='系统信息'>系统信息</a></li>" +
        "<li><a href='/lms/main/admin/misc/system_management.php' title='系统管理'>系统管理</a></li>" +
        "</ul></dd></dl>"+
    "<dl class='nav-list'><dt>license管理</dt><dd class='two-nav-list'><ul>" +
        "<li><a href='/lms/main/admin/course_license.php' title='课件license管理'>课件license管理</a></li>" +
        "<li><a href='/lms/main/admin/import_export/imex_list.php' title='课件资源库管理'>课件资源库管理</a></li>" +
        "</ul></dd></dl></div>");
	$(".systeminfo").append($systeminfo);
/**用户管理**/
	$users = $("<div class='navs'><dl class='nav-list'><dt>用户管理</dt><dd class='two-nav-list'>" +
        "<ul><li><a href='/lms/main/admin/user/user_online.php'>在线用户</a></li>" +
        "<li><a href='/lms/main/admin/dept/dept_iframe.php' title='组织管理'>组织管理</a></li>" +
        "<li><a href='/lms/main/admin/user/user_list_audit.php' title='审核注册用户'>审核用户</a></li>" +
        "<li><a href='/lms/main/admin/user/user_list.php' title='用户管理'>用户管理</a></li>"+
        "<li><a href='/lms/main/admin/user/user_blacklist.php' title='用户黑名单'>用户黑名单</a></li>"+
        "<li><a href='/lms/main/admin/user/work_attendance.php' title='用户考勤'>用户考勤</a></li></ul></dd></dl></div>");
	$(".users").append($users);
///**考试管理**/
	$exercice = $("<div class='navs'><dl class='nav-list'><dt>考试管理</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='/lms/main/exam/pool_iframe.php' title='题库管理'>题库管理</a></li>" +
            "<li><a href='/lms/main/exercice/question_base.php' title='所有考题管理'>所有考题管理</a></li>" +
            "<li><a href='/lms/main/exercice/exercice.php?type=1' title='综合考试管理'>综合考试管理</a></li>" +
            "<li><a href='/lms/main/exercice/exercice.php?type=2' title='课程考试管理'>课程考试管理</a></li>" +
            "<li><a href='/lms/main/exercice/exercice.php?type=3' title='自我测试管理'>自我测试管理</a></li>" +
//            "<li><a href='/lms/main/survey/index.php' title='调查问卷'>调查问卷</a></li>" +
            "<li><a href='/lms/main/reporting/query_quiz.php' title='考试成绩查询'>考试成绩查询</a></li></ul></dd></dl>" +
       		 "<dl class='nav-list'><dt>实验报告管理</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='/lms/main/admin/course/report.php' title='实验报告管理'>实验报告管理</a></li></ul></dd></dl></div>");
	$(".exercice").append($exercice);

    /**路由交换管理**/
$router = $("<div class='navs'><dl class='nav-list'><dt>路由交换管理</dt><dd class='two-nav-list'><ul>" +
        "<li><a href='/lms/main/admin/router/router_type.php' title='路由交换类型管理'>路由交换类型管理</a></li>" +
        "<li><a href='/lms/main/admin/router/labs_ios.php' title='路由交换管理'>路由交换管理</a></li>" +
        "<li><a href='/lms/main/admin/router/labs_mod.php' title='路由交换模块管理'>路由交换模块管理</a></li>" +
        "<li><a href='/lms/main/admin/router/labs_category_iframe.php' title='路由交换课程分类'>路由交换课程分类</a></li>" +
        "<li><a href='/lms/main/admin/router/labs_topo.php' title='网络拓扑设计'>网络拓扑设计</a></li> " +
        "<li><a href='/lms/main/admin/router/labs_device.php' title='网络设备管理'>网络设备管理</a></li>" +
        "<li><a href='/lms/main/admin/router/labs_experimental_anual.php' title='实验交换课程资料'>实验交换课程资料</a></li></ul></dd></dl>");
    $(".router").append($router);
/**课程管理**/
	$course = $("<div class='navs'><dl class='nav-list'><dt>课程管理</dt><dd class='two-nav-list'><ul>" +
        "<li><a href='/lms/main/admin/course/course_plan.php' title='课程调度'>课程调度</a ></li>" +
        "<li><a href='/lms/main/admin/course/course_list.php' title='课程管理'>课程管理</a></li>" +
          "<li><a href='/lms/main/admin/course/setup.php' title='课程体系分类'>课程体系</a ></li>" +
        "<li><a href='/lms/main/admin/course/course_category_iframe.php' title='课程分类管理'>课程分类</a></li>" +
//        "<li><a href='/lms/main/admin/import_export/imex_list.php' title='课件资源库管理'>课件资源库</a></li>" +
        "<li><a href='/lms/main/admin/syllabus/syllabus_list.php' title='课程表管理'>课程表管理</a></li>" +
        "<li><a href='/lms/main/admin/course/course_user_manage.php' title='课程调度查看'>调度查看</a></li>" +
        "<li><a href='/lms/main/reporting/learning_progress.php' title='学习情况查询'>学习情况</a></li>" +
        "<li><a href='/lms/main/survey/index.php' title='调查问卷'>调查问卷</a></li>" +
        "</ul></dd></dl></div>");
	$(".course").append($course);
/**考试实验报告管理**/
    $report = $("<div class='navs'><dl class='nav-list'><dt>实验报告管理</dt><dd class='two-nav-list'><ul>" +
        "<li><a href='/lms/main/admin/course/report.php' title='实验报告管理'>实验报告管理</a ></li></ul></dd></dl></div>");
    $(".report").append($report);
    
/**课程实验报告管理**/
    $reports = $("<div class='navs'><dl class='nav-list'><dt>实验报告管理</dt><dd class='two-nav-list'><ul>" +
        "<li><a href='/lms/main/admin/course/reports.php' title='实验报告管理'>实验报告管理</a ></li></ul></dd></dl></div>");
    $(".reports").append($reports);
/**
 * LMS 后台菜单栏目JS end =========================================================================
 **/

/**消息传递**/
    $cloud1 =$("<div class='navs'>"+
       " <dl class='nav-list'><dt>信息传递</dt><dd class='two-nav-list'><ul>" +
        "<li><a href='/lms/main/admin/message_list.php' title='信息传递'>信息传递</a></li>" +
        "</ul></dd></dl>" +" </div>");
        $(".cloud1").append($cloud1);
/**
*MONITOR 后台菜单栏目JS start =========================================================================
**/
/**monitor首页**/
$sidebars = $("<div class='navs'>" +
    "<dl class='nav-list'>" +
      "<dt>单兵作战</dt><dd class='two-nav-list hide'><ul> " +
        "<li><a href='/lms/main/exam/pool_iframe.php' title='题库管理'>题库管理</a></li>" +
        "<li><a href='/lms/main/exercice/question_base.php' title='考题管理'>考题管理</a></li>" +
        "<li><a href='/lms/main/exam/exam_list.php' title='考试管理'>考试管理</a></li>" +
        "<li><a href='/lms/main/exercice/exercices.php' title='所有考卷'>所有考卷</a></li>" +
        "<li><a href='/lms/main/exam/exam_corrected_list.php' title='考卷批改'>考卷批改</a></li>"+
        "<li><a href='/lms/main/reporting/query_quiz.php' title='考试成绩查询'>考试成绩查询</a></li>"+
        "<li><a href='/lms/main/reporting/quiz_user.php' title='考试汇总'>考试汇总</a></li></ul></dd>" +
    "<dl class='nav-list'><dt>夺旗管理</dt><dd class='two-nav-list hide'><ul>" +
        "<li><a href='/lms/main/admin/misc/flag.php' title='旗子位置'>旗子位置</a></li></ul></dd></dl>"+
    "<dl class='nav-list'><dt>分组对抗管理</dt><dd class='two-nav-list hide'><ul>" +
        "<li><a href='/lms/main/admin/control/control_user_group.php' title='用户分组'>用户分组</a></li>" +
        "<li><a href='/lms/main/admin/control/renwu.php' title='分组任务下发'>分组任务下发</a></li>" +
        "<li><a href='/lms/main/admin/control/control_list.php' title='分组对抗模板分配'>分组对抗模板分配</a></li>" +
        "<li><a href='/lms/main/admin/control/counterwork_info.php' title='分组对抗信息'>分组对抗信息</a></li>" +
        "<li><a href='/lms/main/reporting/counterwork_report.php' title='分组对抗报告'>分组对抗报告</a></li>" +
    "</ul></dd></dl>" +
    "<dl class='nav-list'><dt>用户管理</dt><dd class='two-nav-list hide'><ul>" +
        "<li><a href='/lms/main/admin/dept/dept_iframe.php' title='组织管理'>组织管理</a></li>" +
        "<li><a href='/lms/main/admin/user/user_list_audit.php' title='审核注册用户'>审核用户</a></li>" +
        "<li><a href='/lms/main/admin/user/user_list.php' title='用户管理'>用户管理</a></li>" +
        "<li><a href='/lms/main/admin/user/user_online.php' title='在线用户'>在线用户</a></li>"+
    "</ul></dd></dl>" +
    "<dl class='nav-list'><dt>安全评估</dt> <dd class='two-nav-list hide'><ul>" +
        "<li><a href='/lms/main/evaluate/project.php' title='安全评估'>安全评估</a></li></ul></dd></dl>" +
    "<dl class='nav-list'><dt>云管理</dt><dd class='two-nav-list hide'><ul>" +
        "<li><a href='/lms/main/admin/vmmanage/vmmanage_iframe.php' title='虚拟化管理'>虚拟化管理</a></li> " +
        "<li><a href='/lms/main/admin/net/vm_list_iframe.php' title='网络拓扑设计'>网络拓扑设计</a></li>" +
        "<li><a href='/lms/main/admin/vmmanage/centralized.php' title='集中管理设置'>集中管理设置</a></li>" +
        "<li><a href='/lms/main/admin/vmdisk/vmdisk_list.php' title='虚拟模板管理'>虚拟模板管理</a></li>" +
        "<li><a href='/lms/main/admin/token_bucket/token_bucket_list.php' title='令牌桶管理'>令牌桶管理</a></li>" +
        "<li><a href='/lms/main/admin/vmmanage/unified_shut.php' title='虚拟化统一关机'>虚拟化统一关机</a></li>" +
        "<li><a href='/lms/main/admin/vmmanage/ip_info.php' title='IP地址信息'>IP地址信息</a></li>" +
        "</ul></dd></dl>" +
    "<dl class='nav-list'><dt>云桌面管理</dt> <dd class='two-nav-list hide'><ul>" +
        "<li><a href='/lms/main/admin/cloud/clouddesktop.php' title='云桌面终端'>云桌面终端</a></li>" +
        "<li><a href='/lms/main/admin/cloud/clouddesktopdisk.php' title='云桌面存储空间'>云桌面存储空间</a></li>" +
        "<li><a href='/lms/main/admin/cloud/clouddesktopscan.php' title='云桌面扫描'>云桌面扫描</a></li>" +
        "</ul></dd></dl>" +
    "<dl class='nav-list'><dt>系统管理</dt><dd class='two-nav-list hide'><ul>"+
        "<li><a href='/lms/main/admin/misc/system_announcements.php' title='系统公告'>系统公告</a></li>" +
        "<li><a href='/lms/main/admin/misc/settings.php' title='系统设置'>系统设置</a></li>" +
        "<li><a href='/lms/main/admin/misc/summary.php' title='大赛简介'>大赛简介</a></li>" +
        "<li><a href='/lms/main/admin/misc/tools.php' title='导调工具'>导调工具</a></li>" +
        "</ul></dd></dl>"+
    "</div>");
    $(".sidebars").append($sidebars);

/**MONITOR调度管理**/
    $control =$("<div class='navs'>" +
        "<dl class='nav-list'><dt>分组对抗管理</dt><dd class='two-nav-list'><ul>" +
        "<li><a href='/lms/main/admin/control/control_user_group.php' title='用户分组'>用户分组</a></li>" +
        "<li><a href='/lms/main/admin/control/renwu.php' title='分组任务下发'>分组任务下发</a></li>" +
        "<li><a href='/lms/main/admin/control/control_list.php' title='分组对抗模板分配'>分组对抗模板分配</a></li>" +
        "<li><a href='/lms/main/admin/control/counterwork_info.php' title='分组对抗信息'>分组对抗信息</a></li>" +
        "<li><a href='/lms/main/reporting/counterwork_report.php' title='分组对抗报告'>分组对抗报告</a></li>" +
        "</ul></dd></dl></div>");
    $(".control").append($control);

/**MONITOR态势展示**/
    $trend =$("<div class='navs'>" +
        "<dl class='nav-list'><dt>态势地图</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='/lms/main/admin/trend/trend.php' title='态势展示一'>态势展示一</a></li>" +
            "<li><a href='/lms/main/admin/trend/trend2.php' title='态势展示二'>态势展示二</a></li>" +
        "</ul></dd></dl>"+
//        "<dl class='nav-list'><dt>态势详情</dt><dd class='two-nav-list'><ul>" +
//            "<li><a href='/lms/main/admin/trend/trend_list.php' title='态势详情'>态势详情</a></li>" +
//        "</ul></dd></dl>" +
        "</div>");
    $(".trend").append($trend);


/**MONITOR用户管理**/
    $user = $("<div class='navs'>" +
        "<dl class='nav-list'><dt>用户管理</dt><dd class='two-nav-list'><ul> " +
            "<li><a href='/lms/main/admin/dept/dept_iframe.php' title='组织管理'>组织管理</a></li>" +
            "<li><a href='/lms/main/admin/user/user_list_audit.php' title='审核注册用户'>审核用户</a></li>" +
            "<li><a href='/lms/main/admin/user/user_list.php' title='用户管理'>用户管理</a></li>"+
            "<li><a href='/lms/main/admin/user/user_online.php' title='在线用户'>在线用户</a></li>"+
        "</ul></dd></dl></div>");

    $(".userlist").append($user);
/**MONITOR考试管理**/
    $exam= $("<div class='navs'>" +
        "<dl class='nav-list'><dt>考试管理</dt><dd class='two-nav-list'><ul> " +
            "<li><a href='/lms/main/exam/pool_iframe.php' title='题库管理'>题库管理</a></li>" +
            "<li><a href='/lms/main/exercice/question_base.php' title='考题管理'>考题管理</a></li>" +
            "<li><a href='/lms/main/exam/exam_list.php' title='考试管理'>考试管理</a></li>" +
            "<li><a href='/lms/main/exercice/exercices.php' title='所有考卷'>所有考卷</a></li>" +
            "<li><a href='/lms/main/exam/exam_corrected_list.php' title='考卷批改'>考卷批改</a></li>"+
            "<li><a href='/lms/main/reporting/query_quiz.php' title='考试成绩查询'>考试成绩查询</a></li>"+
            "<li><a href='/lms/main/reporting/quiz_user.php' title='考试汇总'>考试汇总</a></li>"+
        "<li><a href='/lms/main/admin/course/report.php' title='实验报告管理'>实验报告管理</a ></li> "+
        "</ul></dd></dl>"+
        "</div>");
    $(".exercices").append($exam);
/**MONITOR报告管理**/
    $reporting= $("<div class='navs'>" +
        "<dl class='nav-list'><dt>报告管理</dt><dd class='two-nav-list'><ul>" +
        "<li><a href='/lms/main/reporting/report.php' title='实验报告'>实验报告</a></li>" +

        "</ul></dd></dl>"+
        "</div>");
    $(".reporting").append($reporting);
/* MONITOR系统管理*/
    $system =$("<div class='navs'>" +
        "<dl class='nav-list'><dt>系统管理</dt><dd class='two-nav-list'><ul>" +
            "<li><a href='/lms/main/admin/misc/system_announcements.php' title='系统公告'>系统公告</a></li>" +
            "<li><a href='/lms/main/admin/misc/settings.php' title='系统设置'>系统设置</a></li>" +
            "<li><a href='/lms/main/admin/misc/summary.php' title='大赛简介'>大赛简介</a></li>" +
            "<li><a href='/lms/main/admin/misc/tools.php' title='导调工具'>导调工具</a></li>" +
        "</ul></dd></dl></div>");
    $(".system").append($system);
/* MONITOR安全评估*/
    $evaluate =$("<div class='navs'>" +
        "<dl class='nav-list'><dt>安全评估</dt> <dd class='two-nav-list'><ul>" +
            "<li><a href='/lms/main/evaluate/project.php' title='安全评估'>安全评估</a></li>" +
        "</ul></dd></dl></div>");
    $(".evaluate").append($evaluate);
/** monitor夺旗入口 **/
    var $flag =$("<div class='navs'>" +
        "<dl class='nav-list'><dt>夺旗管理</dt><dd class='two-nav-list'><ul>" +
        "<li><a href='/lms/main/admin/misc/flag.php' title='夺旗竞赛管理'>夺旗竞赛管理</a></li>" +
         "<li><a href='/lms/main/reporting/flag_report.php' title='夺旗报告'>夺旗报告</a></li>" +
        "</ul></dd>"+
        "</dl></div>");
    $(".flag").append($flag);
/**
*MONITOR 后台菜单栏目JS end =========================================================================
**/
})
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
	$("#systeminfo tr:even").css("background","#FFC");
	$("#sidebar").css("height","100%");
	$(".labtable tr:even").css("background","#DEE4E5");
	$(".labtable tr:odd").css("background","#EDF2F5");
	var $div_li = $(".manage-tab li");
	$div_li.click(function(){
		$(this).addClass("selected").siblings().removeClass("selected");
		var index = $div_li.index(this);
		$(".manage-tab-content>div").eq(index).show().siblings().hide();
	}).hover(function(){
		$(this).addClass("hover");
	},function(){
		$(this).removeClass("hover");	
	})
	//lab实验选项卡
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
	//后台首页tab
	var $div_dt = $(".pagetab>ul>li");
	$div_dt.click(function(){
		$(this).addClass("selected").siblings().removeClass("selected");
		var index = $div_dt.index(this);
			if(index == 0)
			{
				  $(".f0").animate({left:"0px"},500); 	
			}else if(index == 1){
				  $(".f0").animate({left:"-1500px"},500);
				  $(".f1").animate({left:"0px"},500);		
			}else if(index == 2){
				  $(".f0").animate({left:"-1500px"},500);
				  $(".f1").animate({left:"-1500px"},500);
				  $(".f2").animate({left:"0px"},500);	
			}else if(index == 3){
				  $(".f0").animate({left:"-1500px"},500);
				  $(".f1").animate({left:"-1500px"},500);
				  $(".f2").animate({left:"-1500px"},500);
				  $(".f3").animate({left:"0px"},500);
				  $(".f4").animate({left:"-1500px"},500);	
			}else if(index ==4){
				  $(".f0").animate({left:"-1500px"},500);
				  $(".f1").animate({left:"-1500px"},500);
				  $(".f2").animate({left:"-1500px"},500);
				  $(".f3").animate({left:"-1500px"},500);
				  $(".f4").animate({left:"0px"},500);	
			}
		return false;	
	 });
	 $(".p-table tr:even").css("background-color","#F0F0F0");	
})
//分类管理table JS
$(function(){
		var $tabletr = $(".course-win2");
		$tabletr.click(function(){
			$tr = $(this).parent().siblings("tr");
			if($tr.is(":visible"))
			{
				$tr.hide();	
			}else{
				$tr.show();	
			}
		})
})

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
     $.focusblur("#searchkey");
 });
