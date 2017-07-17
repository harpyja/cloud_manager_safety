<?php
include_once ("../../../../main/inc/global.inc.php");
$tbl_zlmeet_upload_ppt_file = Database::get_course_table ( TABLE_ZLMEET_UPLOAD_PPT_FILE );

$folder = Database::escape_string ( trim ( getgpc ( 'folder', 'G' ) ) );
$sql = "DELETE FROM " . $tbl_zlmeet_upload_ppt_file . " WHERE folder='" . $folder . "'";
$sql .= " AND cc='" . api_get_course_code () . "' ";
$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
if (! $rs) {
	if (DEBUG_MODE) api_error_log ( 'delete zlmeet pptFile -' . $folder . " ERROR!", __FILE__, __LINE__, "zlmeet.log" );
}
unlink ( "./pptUpload/" . $folder . ".ppt" );

$pathdir = "./pptUpload/" . $folder;
$d = dir ( $pathdir );
while ( $a = $d->read () ) {
	if (is_file ( $pathdir . '/' . $a ) && ($a != '.') && ($a != '..')) {
		unlink ( $pathdir . '/' . $a );
	}
}
$d->close ();
rmdir ( $pathdir );

