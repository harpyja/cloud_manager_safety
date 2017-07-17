<?php
$language_file = array ("scorm", "scormdocument", "learnpath", "document" );
include_once ("../inc/global.inc.php");
$is_allowed_to_edit = api_is_allowed_to_edit ();
if (! $is_allowed_to_edit) api_not_allowed ();

require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');

$sys_course_path = api_get_path ( SYS_COURSE_PATH );
$courseDir = api_get_course_code () . "/document";
$base_work_dir = $sys_course_path . $courseDir;

$path = (isset ( $_REQUEST ['curdirpath'] ) ? $_REQUEST ['curdirpath'] : '/');

if (isset ( $_REQUEST ['tool'] )) {
	$my_tool = $_REQUEST ['tool'];
	$_SESSION ['my_tool'] = $_REQUEST ['tool'];
} elseif (! empty ( $_SESSION ['my_tool'] )) {
	$my_tool = $_SESSION ['my_tool'];
} else {
	$my_tool = 'document';
	$_SESSION ['my_tool'] = $my_tool;
}

$htmlHeadXtra [] = import_assets ( 'inc/lib/javascript/upload.js', api_get_path ( WEB_CODE_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">
	var myUpload = new upload(0);
	function check_unzip() {
		if(document.upload.unzip.checked==true){
		document.upload.if_exists[0].disabled=true;
		document.upload.if_exists[1].checked=true;
		document.upload.if_exists[2].disabled=true;
		}
		else {
		document.upload.if_exists[0].checked=true;
		document.upload.if_exists[0].disabled=false;
		document.upload.if_exists[2].disabled=false;
		}
	}
</script>';

switch ($my_tool) {
	case TOOL_LEARNPATH :
		require ('form.scorm.php');
		break;
	case TOOL_DOCUMENT :
	default :
		require ('form.document.php');
		break;
}