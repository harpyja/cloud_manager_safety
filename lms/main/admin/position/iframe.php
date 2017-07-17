<?php
$language_file = array ('exam','admin');
$cidReset=true;

require_once ('../../inc/global.inc.php');
$this_section = SECTION_PLATFORM_ADMIN;


api_protect_admin_script ();
/*$required_roles=array(ROLE_TRAINING_ADMIN);
if(validate_role_base_permision($required_roles)===FALSE){
	api_deny_access(TRUE);
}*/


$htmlHeadXtra[]='<script type="text/javascript" src="'.api_get_path(WEB_JS_PATH).'commons.js"></script>';
$htmlHeadXtra[]='<script type="text/javascript">

if(document.attachEvent)  window.attachEvent("onload",  iframeAutoFit);  
else  window.addEventListener("load",  iframeAutoFit,  false);

</script>	
	
<style type="text/css">
.framePage {
	/*border:#CACACA solid 1px;*/
	border-top-style:none;
	width:100%;
	padding-top:0px;
	text-align:left;
}

#Resources {
	width:100%;
}
#Resources #treeview {
	float:left;
	border:#999 solid 1px;
	width:20%;
	//height:480px;
}
#Resources #frm {
	float:left;
	width:79%;
}
</style>
';

Display::display_header($tool_name,FALSE);

 ?>
 <body>
 <center>
<div class="framePage">
<div id="Resources">
<div id="treeview"><iframe id="Tree" name="Tree"
	src="category_tree.php" frameborder="0" width="100%"></iframe></div>
<div id="frm"><iframe id="List" name="List"
	src="position_list.php" frameborder="0" width="100%" height="500px;"></iframe></div>
</div>
</div></center>
</body>
</html>
