<?php
include('../../../main/inc/global.inc.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=api_get_setting ( 'siteName' )?> 使用帮助</title>
<link href="../index.css" rel="stylesheet" type="text/css">
</head>

<body>
<div class="logo" style="margin-top: 0px;"><img src="<?=api_get_setting ( 'logoUrl' )?>"
	style="margin-top: 20px;" /></div>
<div class="register_banner"><a href="../help/" class="dd5" target="_self">帮助</a> <img
	src="../images/space1.gif" /> <a href="../login.php" class="dd5">登录</a> <img
	src="../images/space1.gif" /> <a href="../user_register.php" class="dd5">注册</a></div>
<div style="width:960px; margin:0 auto;">
    <div class="helpCenter">
                <div class="title">帮助中心</div>
                <div style="text-align:center;"><img src="../images/register_pic1.gif" style="margin:10px auto;" /></div>
                <div><strong>第一步：注册登录</strong></div>
                <div style="text-indent:24px;">打开<?=api_get_setting ( 'siteName' )?>，点击"注册"按钮进入学员注册页面，填写基本信息完成注册</div>
                <div style="text-indent:24px;">如果你已经拥有<?=api_get_setting ( 'siteName' )?>学员帐号请直接登录网络学习平台。</div>
                <div><strong>第二步：选修课程</strong></div>
                <div style="text-indent:24px;">登录进入<?=api_get_setting ( 'siteName' )?>后，点击"选课中心"栏目，找到需要学习的课程，点击"选修课程"按钮，完成该课程的选修。</div>
                <div><strong>第三步：学习课程</strong></div>
                <div style="text-indent:24px;">点击"学习中心"栏目，在"我的课程"中可以看到已经选修的课程。点击进入到该课程的内容详细页，点击"开始学习"按钮可以顺序学习该课程，也可以通过目录，直接点击相应章节进行学习。</div>
                <div style="text-indent:24px;">同时，可以进入"课程介绍"查看课程的相关说明，"教材下载"下载课程的所有讲义资料。</div>
                <div><strong>第四步：测试练习</strong></div>
                <div style="text-indent:24px;">点击每门课程内容页的"测试练习"按钮，开始答题测试。你可以通过点击"学习进度"按钮查看整个课程学习的进度与测试练习的进度。</div>
                <div><strong>第五步：申请结业</strong></div>
                <div style="text-indent:24px;">当你学习完相关课程后，你可以通过一门课程考试来结束学业。</div>
                <div style="text-indent:24px;">感谢您使用<?=api_get_setting ( 'siteName' )?>！我们期待您的宝贵意见。</div>
            </div>
</div>
<?php //include_once("../inc/page_footer.php")?>
<div style="height: 0px; clear: both; overflow: hidden;"></div>
<div class="body_ft dd2" style="margin-top: 20px;"><a href="<?=api_get_setting ( 'InstitutionUrl' )?>" class="dd2"><?=api_get_setting ( 'Institution' )?></a>
 </div>
<div class="body_hint dd2" style="text-align: center">
<?=get_lang('LoginPageCopyright')?></div>

</body>
</html>
