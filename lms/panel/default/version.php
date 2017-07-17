<?php
header("Content-Type: text/html;charset=UTF-8");
if(!defined('SYS_PATH')){
	define('SYS_PATH', str_replace('panel/default', '',str_replace('\\', '/', dirname(__FILE__))));
}
$language_file = array ('index','fx','plugin');
require_once (SYS_PATH.'main/inc/global.inc.php');
api_block_anonymous_users();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE><?= api_get_setting('siteName') ?> - 版本信息</TITLE>
<META content="text/html; charset=UTF-8" http-equiv=Content-Type>
<LINK rel=stylesheet type=text/css href="assets/version.css">
<META name=GENERATOR content="MSHTML 8.00.6001.18904">
</HEAD>
<BODY leftMargin=0 topMargin=0>
<!-- <DIV id=VersionHead><IMG border=0 src="logo.gif"></DIV> -->
<DIV id=VersionNum><b><?=get_lang("SystemName")?></b></DIV>
<UL>
	<LI><B>版本信息：</B></LI>
	<LI style="PADDING-LEFT: 2em">版本号：<?=VERSION?></LI>
	<LI style="PADDING-LEFT: 2em">服务器：<?=$_SERVER['SERVER_SOFTWARE']?></LI>
	<LI style="PADDING-LEFT: 2em">数据库：MySQL &nbsp;&nbsp;<?=Database::get_scalar_value("SELECT VERSION() AS ver")?></LI>


	<LI><B>更多信息请访问官方网站：</B><?=get_lang("FrameRightDownCopyright") ?></LI>
	<LI><B>主要技术及产品咨询人员：</B>黎 宇&nbsp;&nbsp;( E-Mail: zlms@foxmail.com
	&nbsp;&nbsp;&nbsp;&nbsp;QQ:61219645 )</LI>

	<LI><B>警告：</B></LI>
	<LI style="PADDING-LEFT: 2em;width:400px">本计算机程序受到著作权法和国际公约的保护。未经授权擅自修改或再发本程序的部分或全部，可能受到严厉的民事及刑事制裁，并将在法律许可的范围内受到最大可能的起诉。</LI>

	<LI style="TEXT-ALIGN: center; LINE-HEIGHT: 30px; CURSOR: hand"
		onclick=window.close();>&lt;&lt;点击此处关闭窗口&gt;&gt;</LI>

</UL>
</BODY>
</HTML>
