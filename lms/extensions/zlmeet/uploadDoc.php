<?php
header ( "Content-Type:text/xml;charset=UTF-8" );
header ( "Cache-Control: no-store, no-cache, must-revalidate" );
header ( "Cache-Control: post-check=0, pre-check=0", false );

include_once ("../../../../main/inc/global.inc.php");
$tbl_zlmeet_upload_file = Database::get_course_table ( TABLE_ZLMEET_UPLOAD_FILE );

$uploadFileName = $_FILES ['Filedata'] ['name'];
$uploadFile = $_FILES ['Filedata'] ['tmp_name'];

$pos = strrpos ( $uploadFileName, '.' );
$len = strlen ( $uploadFileName );
$localFormat = substr ( $uploadFileName, $pos + 1, $len );

$is_allowed_upload = true;
$forbbidenFileType = array ("php", "sh", "exe", "bat" );
foreach ( $forbbidenFileType as $value ) {
	if ($localFormat == $value) {
		$is_allowed_upload = false;
		break;
	}
}

if (! is_array ( strtolower ( $localFormat, $forbbidenFileType ) ) && is_uploaded_file ( $uploadFile )) { //V1.4
	//if ($is_allowed_upload && is_uploaded_file ( $uploadFile )) {
	$pos = strrpos ( $uploadFileName, '.' );
	$len = strlen ( $uploadFileName );
	$extendType = substr ( $uploadFileName, $pos, $len );
	$localFileName = date ( "Ymdhis" ) . $extendType;
	$localFile = "upload/" . $localFileName;
	
	if (move_uploaded_file ( $uploadFile, $localFile )) {
		$fsize = filesize ( $localFile ) / 1024;
		$create_date = date ( "Y-m-d H:i:s" );
		$sql_data = array ('original_name' => $uploadFileName, 'store_name' => $localFileName, 'room_id' => getgpc ( "roomID", "G" ), 'file_size' => $fsize, 'created_date' => $create_date );
		$sql_data ['cc'] = api_get_course_code ();
		$sql = Database::sql_insert ( $tbl_zlmeet_upload_file, $sql_data );
		//$sql = "insert into " . $tbl_zlmeet_upload_file . " (original_name,store_name,room_id,file_size,created_date) values ('" . $uploadFileName . "','" . $localFileName . "','" . mysql_real_escape_string ( trim ( $_GET ['roomID'] ) ) . "','" . $fsize . "',NOW())";
		$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		if (! $rs) {
			if (DEBUG_MODE) api_error_log ( 'insert zlmeet file -' . $uploadFileName . " ERROR!", 0, __FILE__, __LINE__, "zlmeet.log" );
		}
	} else {
		if (DEBUG_MODE) api_error_log ( 'upload zlmeet file -' . $uploadFileName . " ERROR!", __FILE__, __LINE__, "zlmeet.log" );
	}
}
