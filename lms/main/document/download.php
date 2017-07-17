<?php
 
session_cache_limiter ( 'public' );
include_once ('../inc/global.inc.php');
include (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');
header ( 'Expires: Wed, 01 Jan 1990 00:00:00 GMT' );
header ( 'Cache-Control: public' );
header ( 'Pragma: no-cache' );

api_protect_course_script ();

$doc_url = getgpc('doc_url');
$doc_url = str_replace ( '///', '&', $doc_url );
$doc_url = str_replace ( ' ', '+', $doc_url );
$doc_url = str_replace ( array ('../', '\\..', '\\0', '..\\' ), array ('', '', '', '' ), $doc_url ); //echo $doc_url;


if (! isset ( $_course )) api_not_allowed ();

if (is_dir ( api_get_path ( SYS_COURSE_PATH ) . api_get_course_code () . "/document" . $doc_url )) {
	while ( $doc_url {$dul = strlen ( $doc_url ) - 1} == '/' )
		$doc_url = substr ( $doc_url, 0, $dul );
	
	$document_explorer = api_get_path ( WEB_CODE_PATH ) . 'document/document.php?curdirpath=' . urlencode ( $doc_url ) . '&cidReq=' . Security::remove_XSS ( $_GET ['cidReq'] );
	header ( 'Location: ' . $document_explorer );
}

event_download ( $doc_url );

$sys_course_path = api_get_path ( SYS_COURSE_PATH );
$full_file_name = $sys_course_path . api_get_course_code () . '/document' . $doc_url;

$is_allowed_to_edit = api_is_allowed_to_edit ();
if (! $is_allowed_to_edit && ! DocumentManager::is_visible ( $doc_url, $_course )) {
	exit ( "document not allow to download" ); // you shouldn't be here anyway
}

DocumentManager::file_send_for_download ( $full_file_name );
