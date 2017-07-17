<?php
header ( "Content-Type:text/xml;charset=UTF-8" );
header ( "Cache-Control: no-store, no-cache, must-revalidate" );
header ( "Cache-Control: post-check=0, pre-check=0", false );

include_once ("../../../../main/inc/global.inc.php");
$tbl_zlmeet_upload_file = Database::get_course_table ( TABLE_ZLMEET_UPLOAD_FILE );
$roomID = Database::escape_string ( trim ( getgpc ( 'roomID', 'G' ) ) );
$sql = "select * from " . $tbl_zlmeet_upload_file . " where room_id='" . $roomID . "'";
$sql .= " AND cc='" . api_get_course_code () . "' ";
//echo $sql;
$xml = "<FileList>";
$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
while ( $row = mysql_fetch_array ( $rs ) ) {
	$xml .= "<File id='" . $row ['id'] . "' name='" . $row ['original_name'] . "' fileName='" . $row ['store_name'] . "' size='" . $row ['file_size'] . "' date='" . $row ['created_date'] . "'></File>";
}
$xml .= "</FileList>";
echo $xml;
