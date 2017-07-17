<?php
header ( "Content-Type:text/xml;charset=UTF-8" );
header ( "Cache-Control: no-store, no-cache, must-revalidate" );
header ( "Cache-Control: post-check=0, pre-check=0", false );

include ("../../../../main/inc/global.inc.php");
$tbl_zlmeet_upload_ppt_file = Database::get_course_table ( TABLE_ZLMEET_UPLOAD_PPT_FILE );

$roomID = escape ( getgpc ( 'roomID', 'G' ) );
$sql = "select * from " . $tbl_zlmeet_upload_ppt_file . " where room_id='" . $roomID . "'";
$sql .= " AND cc='" . api_get_course_code () . "' ";
$xml = "<FileList>";

$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
while ( $row = mysql_fetch_array ( $rs ) ) {
	$xml .= "<File id='" . $row ['id'] . "' name='" . $row ['name'] . "' folder='" . $row ['folder'] . "' totalFrame='" . $row ['total_frame'] . "' date='" . $row ['created_date'] . "'></File>";
}
$xml .= "</FileList>";
echo $xml;
