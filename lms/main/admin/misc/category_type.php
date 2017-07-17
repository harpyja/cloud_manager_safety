<?php
$language_file = array ('admin');
require_once ('../../inc/global.inc.php');
$this_section = SECTION_PLATFORM_ADMIN;
api_protect_admin_script ();
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">


<title></title>
<script src="<?=api_get_path ( WEB_PATH )?>res/dtree/dept_tree.js"
	type=text/javascript></script>

<!-- <link href="<?=api_get_path ( WEB_CSS_PATH )?>default.css"
	type=text/css rel=StyleSheet>	 -->

<link href="<?=api_get_path ( WEB_PATH )?>res/dtree/dtree.css"
	type=text/css rel=StyleSheet>


<script language="JavaScript" type="text/JavaScript">
	var $$ = function(id) {return document.getElementById(id);}
	var is_show=0;
	function show_hide_oc(){
		if(is_show==1){
			d.closeAll();
			$$("oc").innerText="<?=get_lang('OpenAll') ?>";
			is_show=0;
		}else{
			
			d.openAll();
			$$("oc").innerText="<?=get_lang('CloseAll') ?>";
			is_show=1;
		}
	}
</script>
<style type="text/css">
html {
	SCROLLBAR-ARROW-COLOR: #88bad3;
	SCROLLBAR-FACE-COLOR: #dff1f5;
	SCROLLBAR-DARKSHADOW-COLOR: #f1fcff;
	SCROLLBAR-HIGHLIGHT-COLOR: #f1fcff;
	SCROLLBAR-3DLIGHT-COLOR: #a3c7df;
	SCROLLBAR-SHADOW-COLOR: #a3c7df;
	SCROLLBAR-TRACK-COLOR: #f2f7f9;
}
</style>
</head>
<body>
<a href='javascript:show_hide_oc();'>
<div id='oc' style='float: middle;'><?=get_lang('OpenAll') ?></div>
</a>
<div class=dtree>
<table cellspacing="5">
	<tr>
		<td align="left"></td>
	</tr>
	<tr>
		<td><script type=text/javascript>
		d = new dTree('d');
		/*d.config.closeSameLevel=true;*/
		d.config.useCookies=true;
		d.add(0,-1,'所有类别');
		d.add(1,0,"系统公告分类","category_list.php?module=sys_announce","","List");
		d.add(2,0,"新闻文章分类","category_list.php?module=sys_cms","","List");
		<?php if (api_get_setting ( 'enable_modules', 'survey_center' ) == 'true') {?>
		d.add(3,0,"调查问卷分类","category_list.php?module=survey","","List");
		d.add(5,0,"调查问卷题目分类","category_list.php?module=survey_question","","List");
		<?php } ?>
		//d.add(2,0,"课程包分类","category_list.php?module=course_pkg","","List");
		//d.add(3,0,"岗位分类","category_list.php?module=position","","List");
		/*d.add(3,0,"试卷分类","category_list.php?module=exam_paper","","List");
		d.add(4,0,"考试分类","category_list.php?module=exam","","List");
		d.add(5,0,"必修课学习班分类","category_list.php?module=class","","List");*/
		document.write(d);


		</script></td>
			</tr>
		</table>
		</div>
		</body>
		</html>
				