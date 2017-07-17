<?php
include_once ("../../../../main/inc/global.inc.php");
$tbl_zlmeet_upload_file = Database::get_course_table ( TABLE_ZLMEET_UPLOAD_FILE );
$fileName = escape ( getgpc ( 'fileName', 'G' ) );
$sql = "DELETE FROM " . $tbl_zlmeet_upload_file . " WHERE store_name='" . $fileName . "'";
$sql .= " AND cc='" . api_get_course_code () . "' ";
$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
if (! $rs) {
	if (DEBUG_MODE) api_error_log ( 'delete zlmeet file -' . $fileName . " ERROR!", __FILE__, __LINE__, "zlmeet.log" );
}

unlink ( "./upload/" . $fileName );

