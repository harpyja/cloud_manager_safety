<?php
header("Location: course_list.php");exit;

$language_file = array ('courses', 'admin' );
$cidReset = true;
include_once ("../../inc/global.inc.php");
$this_section = SECTION_PLATFORM_ADMIN;

api_protect_admin_script ();

$display_admin_menushortcuts = (api_get_setting ( 'display_admin_menushortcuts' ) == 'true' ? TRUE : FALSE);
include_once (api_get_path ( SYS_CODE_PATH ) . 'course/course.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');

/*
$htmlHeadXtra[]='<script type="text/javascript">
var updateContentArea=function(){
		 var winHeight = $(window).height();		
		 $("#CategoryTree").height(winHeight-20);
		 $("#List").height(winHeight-15);
		 
		 var winWidth = $(window).width();		 
		 var leftWidth=($("#CategoryTree").width());
		 $("#List").width(winWidth-leftWidth);
}

$(window).load(function() {
	updateContentArea();
});

$(window).resize(function() {
	updateContentArea();
});
</script>';*/

$htmlHeadXtra [] = '<script type="text/javascript">
if(document.attachEvent)  window.attachEvent("onload",  iframeAutoFit);  
else  window.addEventListener("load",  iframeAutoFit,  false);
</script>		

<style type="text/css">
.framePage {border-top-style:none;	width:100%;	padding-top:0px;	text-align:left;}
#Resources {width:100%;}
#Resources #treeview {	float:left;	border:#999 solid 1px;	width:20%;	}
#Resources #frm {	float:left;	width:78%;}
</style>
';

$interbreadcrumb [] = array ('url' => api_get_path ( WEB_ADMIN_PATH ) . 'index.php', "name" => get_lang ( 'PlatformAdmin' ), 'target' => '_self' );
$interbreadcrumb [] = array ('url' => 'course_category_iframe.php', "name" => get_lang ( 'AdminCategories' ), 'target' => '_self' );

Display::display_header ( NULL ,FALSE);

?>

<body>
<center>
<div class="framePage stud">
<div id="Resources">
<div id="treeview"><iframe id="CategoryTree" name="CategoryTree"
	src="course_category_tree2.php" frameborder="0" width="100%"></iframe></div>
<div id="frm"><iframe id="List" name="List" src="course_list.php"
	frameborder="0" width="100%"></iframe></div>
</div>
</div>
</center>
</body>
</html>
