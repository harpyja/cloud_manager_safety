<?php 
// name of the language file that needs to be included
$language_file='admin';
$cidReset=true;
include_once ('../../inc/global.inc.php');
$this_section=SECTION_PLATFORM_ADMIN;

api_protect_admin_script();
$display_admin_menushortcuts=(api_get_setting('display_admin_menushortcuts')=='true'?TRUE:FALSE);


include_once(api_get_path(LIBRARY_PATH).'fileManage.lib.php');

$interbreadcrumb[]=array('url' => api_get_path(WEB_ADMIN_PATH).'index.php',"name" => get_lang('PlatformAdmin'));
$tool_name = get_lang('Statistics');
Display::display_header($tool_name);

//api_display_tool_title($tool_name);

Display::display_footer();
?>