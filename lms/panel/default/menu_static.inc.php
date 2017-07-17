<?php
$language_file = array ('index' );
include_once ('../../main/inc/global.inc.php');
api_block_anonymous_users ();

if ($_user ["status"] == COURSEMANAGER) {
	?>
<ul style="margin-left:200px;"><!--by changzf on 8 line-->
	<li><a href="<?=URL_APPEND?>user_portal.php" class="top" target="_self">我管理的课程</a></li>

	<li><a href="<?=URL_APPEND?>main/exam/exam_corrected_list.php"
		class="top" target="_self">我批改的考卷</a></li>
	<li><a href="<?=URL_APPEND?>main/admin/vmmanage/vmmanage_iframe.php"
                   target="_self">远程协助</a></li>
</ul>
<?php
}
if (api_is_platform_admin ()) {
	?>
<ul style="margin-left:200px;"><!--by changzf on 18 line-->
	<li><a href="<?=URL_APPEND?>main/admin/index.php" class="top"
		target="_self">首页</a></li>
	<li><a href="<?=URL_APPEND?>user_portal.php" class="top" target="_self">我管理的课程</a></li>
	<li><a href="<?=URL_APPEND?>main/exam/exam_corrected_list.php"
		class="top" target="_self">考卷批改</a></li>
	<li><a href="<?=URL_APPEND?>main/admin/course/course_list.php"
		target="_self">课程管理</a>
		<ul>
			<li><a href="<?=URL_APPEND?>main/admin/course/course_list.php"
				target="_self">课程管理</a></li>
      <li><a href="<?=URL_APPEND?>main/admin/course/course_plan.php"
                   target="_self">课程调度</a></li>
<li><a href="<?=URL_APPEND?>main/admin/course/course_category_iframe.php"
                   target="_self">课程分类管理</a></li>
<li><a href="<?=URL_APPEND?>main/admin/import_export/imex_list.php"
                   target="_self">导入导出</a></li>
 <li><a href="<?=URL_APPEND?>main/admin/syllabus/syllabus_list.php"
                   target="_self">课程表管理</a></li>
      <li><a href="<?=URL_APPEND?>main/admin/course/course_user_manage.php"
                   target="_self">课程调度查看</a></li>
     </ul></li>
	<?php if(api_get_setting ( 'enable_modules', 'exam_center' ) == 'true'){?>
	<li><a href="<?=URL_APPEND?>main/exercice/exercice.php" target="_self">考试管理</a>
		<ul>
			<li><a href="<?=URL_APPEND?>main/exam/pool_iframe.php" target="_self">题库管理</a></li>
			<li><a href="<?=URL_APPEND?>main/exercice/question_base1.php" target="_self">所有考题管理</a></li>
			<!--li><a href="<?=URL_APPEND?>main/exercice/exercice.php?type=3"
				target="_self">测验练习管理</a></li-->
			<li><a href="<?=URL_APPEND?>main/exercice/exercice.php"
				target="_self">综合考试管理</a></li>
			<li><a href="<?=URL_APPEND?>main/exercice/exercice.php?type=2"
				target="_self">课程考试管理</a></li>



<li><a href="" target="_self">实训考试管理</a></li>

            <?php if(api_get_setting ( 'enable_modules', 'survey_center' ) == 'true' AND $_configuration ['enable_module_survey']){?>
            <li><a href="<?=URL_APPEND?>main/survey/index.php" target="_self"><?=get_lang ( 'Survey' ) ?></a></li>
            <?php } ?>
		</ul></li>
	<?php } ?>
	<li><a href="<?=URL_APPEND?>main/reporting/learning_progress.php"
		target="_self">查询统计</a>
		<ul>
			<li><a href="<?=URL_APPEND?>main/reporting/learning_progress.php"
				target="_self">学习情况查询</a></li>
		<?php if(api_get_setting ( 'enable_modules', 'exam_center' ) == 'true'){?>
		<li><a href="<?=URL_APPEND?>main/reporting/query_quiz.php"
				target="_self">考试成绩查询</a></li><?php } ?>
		<!--li><a href="<?=URL_APPEND?>main/admin/statistics/stat_login.php"
				target="_self">登录访问统计</a></li>
			<li><a href="<?=URL_APPEND?>main/reporting/user_stat.php"
				target="_self">学员统计</a></li-->
			<li><a href="<?=URL_APPEND?>main/admin/user/user_online.php"
				target="_self">在线用户</a></li>
		</ul></li>
	<li><a href="<?=URL_APPEND?>main/admin/user/user_list_iframe.php"
		target="_self">用户管理</a>
		<ul>
			<li><a href="<?=URL_APPEND?>main/admin/dept/dept_iframe.php"
				target="_self">组织架构管理</a></li>
			<li><a href="<?=URL_APPEND?>main/admin/user/user_list_iframe.php"
				target="_self">用户管理</a></li>
			<li><a
				href="<?=URL_APPEND?>main/admin/user/user_list_audit.php?status=1"
				target="_self">审核注册用户</a></li>
	</ul></li>

    <li>
        <a href="<?=URL_APPEND?>main/admin/router/labs_ios.php" target="_self">路由交换管理</a>
        <ul>
            <li><a href="<?=URL_APPEND?>main/admin/router/labs_ios.php" target="_self">路由交换型号管理</a></li>
            <li><a href="<?=URL_APPEND?>main/admin/router/labs_mod.php" target="_self">路由交换模块管理</a></li>
            <li><a href="<?=URL_APPEND?>main/admin/router/labs_category_iframe.php" target="_self">路由交换课程分类</a></li>
            <li><a href="<?=URL_APPEND?>main/admin/router/labs_topo.php" target="_self">网络拓扑设计</a></li>
            <li><a href="<?=URL_APPEND?>main/admin/router/labs_device.php" target="_self">拓扑设备管理</a></li>

        </ul>
    </li>

    <li><a href="<?=URL_APPEND?>main/admin/net/vm_list_iframe.php"
           target="_self">云管理</a>

        <ul>
            <li><a href="<?=URL_APPEND?>main/admin/vmmanage/vmmanage_iframe.php"
                   target="_self">虚拟化管理</a></li>

            <li><a href="<?=URL_APPEND?>main/admin/net/vm_list_iframe.php"
                   target="_self">网络拓扑设计</a></li>
<li><a href="<?=URL_APPEND?>main/admin/vmmanage/centralized.php"
                   target="_self">集中管理设置</a></li>
          <!--  <li><a href="<?=URL_APPEND?>main/admin/vmdisk/vmdisk_list.php" target="_self">虚拟模板管理</a></li>-->
            <?php
            $lessonedit="/etc/lessonedit";
            $lessonedit=file_get_contents($lessonedit);
            $lessonedit+=0;
            if($lessonedit == '1'){
                 echo '<li><a href="'.URL_APPEND.'main/admin/vmdisk/vmdisk_list.php" target="_self">虚拟模板管理</a></li>';
            }
            ?>
<li><a href="<?=URL_APPEND?>main/admin/token_bucket/token_bucket_list.php"
                   target="_self">令牌桶管理</a></li>

         </ul></li>
    <li><a href="<?=URL_APPEND?>main/admin/cloud/clouddesktop.php"
           target="_self">云桌面管理</a>
        <ul>
            <li><a href="<?=URL_APPEND?>main/admin/cloud/clouddesktop.php"
                   target="_self">云桌面终端</a></li>
            <li><a href="<?=URL_APPEND?>main/admin/cloud/clouddesktopdisk.php"
                   target="_self">云桌面存储空间</a></li>
            <li><a href="<?=URL_APPEND?>main/admin/cloud/clouddesktopscan.php"
                   target="_self">云桌面扫描</a></li>

        </ul></li>
         
    <li><a href="<?=URL_APPEND?>main/admin/misc/system_management.php"
           target="_self">系统管理</a>
        <ul>
            <?php if(isRoot()){?>
            <li><a href="<?=URL_APPEND?>main/admin/systeminfo.php"
                   target="_self">系统信息</a></li>
            <li><a href="<?=URL_APPEND?>main/admin/misc/settings.php"
                   target="_self">系统设置</a></li>
            <li><a href="<?=URL_APPEND?>main/admin/log/logging_list.php"
                   target="_self">系统日志</a></li>
            <li><a href="<?=URL_APPEND?>main/admin/misc/system_announcements.php"
                   target="_self">系统公告</a></li>
            <li><a href="<?=URL_APPEND?>main/admin/misc/system_upgrade.php"
                   target="_self">系统升级</a></li>
            <li><a href="<?=URL_APPEND?>main/admin/misc/system_management.php"
                   target="_self">系统管理</a></li>
            <li><a href="<?=URL_APPEND?>main/admin/misc/system_status.php"
                   target="_self">系统运行状态</a></li>
            <!--li><a href="<?=URL_APPEND?>main/admin/misc/cms_list.php"
			             target="_self">信息发布</a></li-->



            <?php } ?>
        </ul></li>

</ul>

</ul>
<?php
}
?>

<!-- <div style="float: right">
<ul>
	<li><a class="helpex dd2" href="<?=URL_APPEND . PORTAL_LAYOUT?>"
		target="_blank">前台首页</a></li>
	<li><a class="helpex dd2" id="confirmExit" target="_top"
		href="javascript:confirmExit();"><span style="font-weight:bold;color:#FFF">退出</span></a></li>
</ul>
</div> -->
<br style="clear: left" />
