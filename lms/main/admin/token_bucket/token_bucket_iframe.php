<?php
/**
 * Created by JetBrains PhpStorm.
 * User: chang
 * Date: 12-11-18
 * Time: 上午9:19
 * To change this template use File | Settings | File Templates.
 */
$language_file = array ('courses','admin');
$cidReset=true;
include_once ("../../inc/global.inc.php");
$this_section=SECTION_PLATFORM_ADMIN;

api_protect_admin_script ();
/*$required_roles=array(ROLE_TRAINING_ADMIN);
if(validate_role_base_permision($required_roles)===FALSE){
	api_deny_access(TRUE);
}
$restrict_org_id=$_SESSION['_user']['role_restrict'][ROLE_TRAINING_ADMIN];*/

$display_admin_menushortcuts=(api_get_setting('display_admin_menushortcuts')=='true'?TRUE:FALSE);
include_once(api_get_path(SYS_CODE_PATH).'course/course.inc.php');
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
$htmlHeadXtra[]='<script type="text/javascript">
if(document.attachEvent)  window.attachEvent("onload",  iframeAutoFit);
else  window.addEventListener("load",  iframeAutoFit,  false);
</script>

<style type="text/css">
html,body{height:100%;min-height:100%,_height:100%;}
#treeview{float:left;height:100%;width:15%;border-right:2px solid #666;}
#frm{float:left;width:82%;margin-left:20px;}
</style>';

//$interbreadcrumb [] = array ('url' => api_get_path(WEB_ADMIN_PATH).'index.php', "name" => get_lang ( 'PlatformAdmin' ), 'target' => '_self' );
//$interbreadcrumb [] = array ('url' => 'vm_list_iframe.php', "name" => get_lang ( 'AdminCategories' ), 'target' => '_self' );

Display::display_header();

?>

            <div id="treeview">
            	<iframe id="CategoryTree" name="CategoryTree" src="token_bucket_tree.php" frameborder="0" width="100%" height="100%"></iframe>
            </div>
            <div id="frm">
            	<h4 class="page-mark">当前位置：<a href="index.html">平台首页</a> &gt; <a href="cloudMenu.html">云平台</a> &gt; <a href="/lms/main/admin/token_bucket/token_bucket_list.php">令牌桶管理</a> &gt; <a href="/lms/main/admin/token_bucket/token_bucket_iframe.php" title="令牌桶状态">令牌桶状态</a> </h4>
                <div class="sd" style="margin-left:35px;">
            	<iframe id="List" name="List" src="token_bucket_status.php" frameborder="0" width="96%"></iframe>
                </div>
            </div>
</body>
</html>
