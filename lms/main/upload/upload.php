<?php
$language_file [] = "document";
$language_file [] = "learnpath";
include_once ("../inc/global.inc.php");
$is_allowed_to_edit = api_is_allowed_to_edit ();
if (! $is_allowed_to_edit) api_not_allowed ();

switch ($_SESSION ['my_tool']) {
	case TOOL_LEARNPATH :
		require ('upload.scorm.php');
		break;
	//the following cases need to be distinguished later on
	case TOOL_DROPBOX :
	case TOOL_STUDENTPUBLICATION :
	case TOOL_DOCUMENT :
	default :
		require ('upload.document.php');
		break;
}