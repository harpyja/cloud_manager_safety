<?php
/*
 ==============================================================================

 ==============================================================================
 */

session_cache_limiter('public');
header('Expires: Wed, 01 Jan 1990 00:00:00 GMT');
header('Cache-Control: public');
header('Pragma: no-cache');
include_once('../inc/global.inc.php');
api_block_anonymous_users();

$doc_url = urldecode(getgpc('doc_url'));
$doc_url = str_replace(array('../','\\..','\\0','..\\'),array('','','',''), $doc_url); //echo $doc_url;
include_once (api_get_path(LIBRARY_PATH).'fileManage.lib.php');
include_once (api_get_path(LIBRARY_PATH).'document.lib.php');
event_download($doc_url);

$full_file_name = api_get_path(SYS_PATH).$doc_url;
if(!file_exists($full_file_name)) exit('文件不存在!');
DocumentManager::file_send_for_download($full_file_name);
