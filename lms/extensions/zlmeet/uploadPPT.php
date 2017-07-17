<?php
header ( "Content-Type:text/xml;charset=UTF-8" );
header ( "Cache-Control: no-store, no-cache, must-revalidate" );
header ( "Cache-Control: post-check=0, pre-check=0", false );

include_once ("../../../../main/inc/global.inc.php");

$tbl_zlmeet_upload_ppt_file = Database::get_course_table ( TABLE_ZLMEET_UPLOAD_PPT_FILE );

$uploadFileName = $_FILES ['Filedata'] ['name'];
$uploadFile = $_FILES ['Filedata'] ['tmp_name'];

$localFormat = getFileExt ( $uploadFileName );

$is_allowed_upload = true;
$forbbidenFileType = array ("php", "sh", "exe", "bat" );
foreach ( $forbbidenFileType as $value ) {
	if ($localFormat == $value) {
		$is_allowed_upload = false;
		break;
	}
}
if (strtolower ( $localFormat ) != "ppt") {
	$is_allowed_upload = false;
}

if ($is_allowed_upload && is_uploaded_file ( $uploadFile )) {
	/*$pos=strrpos($uploadFileName,'.');
	 $len=strlen($uploadFileName);
	 $extendType=substr($uploadFileName,$pos,$len);*/
	//$extendType=getFileExt($uploadFileName);
	$localFileName = trim ( $_GET ['fileName'] );
	$localFile = "pptUpload/" . $localFileName; //上传之后保存的文件
	

	if (move_uploaded_file ( $uploadFile, $localFile )) {
		$pos = strrpos ( $localFileName, '.' );
		$len = strlen ( $uploadFileName );
		$folder = substr ( $localFileName, 0, $pos );
		$create_date = date ( "Y-m-d h:i:s" );
		
		try {
			
			$ppt_convert_method = OOGIE_CONVERT_METHOD;
			
			$ppt_size = api_get_setting ( "service_ppt2lp", "size" );
			list ( $slide_width, $slide_height ) = split ( "x", $ppt_size );
			if (! isset ( $slide_width )) $slide_width = 640;
			if (! isset ( $slide_height )) $slide_height = 480;
			
			//通过MSOffice转换
			$localFile = realpath ( $localFile ); //服务器上的PPT文件存储物理路径
			$exportPath = realPath ( "./pptUpload" ) . "/" . $folder; //导出的文件夹
			if ($ppt_convert_method == PPT_CONVERT_METHOD_MSOFFICE) {
				$pptCount = convert_ppt ( $localFile, $exportPath );
			}
			
			//通过OpenOffice转换
			if ($ppt_convert_method == PPT_CONVERT_METHOD_OPENOFFICE) {
				$classpath = '-cp .:ridl.jar:js.jar:juh.jar:jurt.jar:jut.jar:java_uno.jar:java_uno_accessbridge.jar:edtftpj-1.5.2.jar:unoil.jar';
				if (IS_WINDOWS_OS) {
					$classpath = str_replace ( ':', ';', $classpath );
				}
				
				if (IS_WINDOWS_OS) {
					$cmd = 'cd ' . str_replace ( '/', '\\', api_get_path ( SYS_CODE_PATH ) ) . 'inc/lib/ppt2png && java ' . $classpath . ' DocumentConverter localhost ' . OPENOFFICE_PORT . '  "' . $localFile . '" "' . $exportPath . '"' . ' ' . $slide_width . ' ' . $slide_height;
				} else {
					$cmd = 'cd ' . api_get_path ( SYS_CODE_PATH ) . 'inc/lib/ppt2png && java ' . $classpath . ' DocumentConverter localhost ' . OPENOFFICE_PORT . '  "' . $localFile . '" "' . $exportPath . '"' . ' ' . $slide_width . ' ' . $slide_height;
				}
				if (! file_exists ( $exportPath )) mkdir ( $exportPath, CHMOD_NORMAL );
				if (DEBUG_MODE) {
					api_error_log ( $cmd, __FILE__, __LINE__, "zlmeet.log" );
				}
				chmod ( $exportPath, CHMOD_NORMAL );
				
				$shell = exec ( $cmd, $files, $return );
				if (DEBUG_MODE) api_error_log ( $files, __FILE__, __LINE__, "zlmeet.log" );
				$pptCount = count ( $files );
			}
		
		} catch ( Exception $e ) {
			echo 'Exception Message: <BR>' . $e->getMessage ();
			api_error_log ( $e->getMessage (), __FILE__, __LINE__, "zlmeet.log" );
			return 0;
		}
		$create_date = date ( "Y-m-d H:i:s" );
		//$sql="insert into ".$tbl_zlmeet_upload_ppt_file." (name,folder,room_id,total_frame,created_date) values ('".$uploadFileName."','".$folder."','".mysql_real_escape_string(trim($_GET['roomID']))."','".$pptCount."',NOW())";
		$sql_data = array ('name' => $uploadFileName, 'folder' => $folder, 'room_id' => getgpc ( "roomID", "G" ), 'total_frame' => $pptCount, 'created_date' => $create_date );
		$sql_data ['cc'] = api_get_course_code ();
		$sql = Database::sql_insert ( $tbl_zlmeet_upload_ppt_file, $sql_data );
		
		if (DEBUG_MODE) api_error_log ( $sql, __FILE__, __LINE__, "zlmeet.log" );
		$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		if (! $rs) {
			if (DEBUG_MODE) api_error_log ( 'insert zlmeet pptFile -' . $uploadFileName . " ERROR!", __FILE__, __LINE__, "zlmeet.log" );
		}
		
		$path2 = './pptUpload/' . $folder . "/";
		$handle = opendir ( $path2 );
		while ( $file = readdir ( $handle ) ) {
			if (! is_dir ( $file )) {
				/*$len=strlen($file);
				 $newName=substr($file,6,$len);
				 rename($path2.$file,$path2.$newName);*/
				if ($ppt_convert_method == PPT_CONVERT_METHOD_MSOFFICE) {
					preg_match_all ( "/(\d+)\.JPG$/", $file, $name_arr );
				}
				if ($ppt_convert_method == PPT_CONVERT_METHOD_OPENOFFICE) {
					preg_match_all ( "/(\d+)\.png$/", $file, $name_arr );
				}
				$newName = $name_arr [0] [0];
				rename ( $path2 . $file, $path2 . $newName );
			}
		}
		closedir ( $handle );
	} else {
		if (DEBUG_MODE) api_error_log ( 'upload zlmeet pptFile -' . $uploadFileName . " ERROR!", __FILE__, __LINE__, "zlmeet.log" );
	}
}

/**
 * 转换PPT文件为图片
 * @param $localFile 上传到服务器上的PPT文件（物理路径）
 * @param $exportPath 导出的目录
 * @param $width 宽度
 * @param $height 高度
 * @return 成功转换的图片总数
 */
function convert_ppt($localFile, $exportPath, $width = 640, $height = 480) {
	try {
		$ppt = new COM ( "powerpoint.application" ) or die ( "Unable to Find MS PowerPoint 2003! Seem NOT install in this server,Please Install it First" );
		$ppt->Visible = true;
		//echo $localFile;
		$ppt->Presentations->Open ( ($localFile) );
		$pptCount = $ppt->activePresentation->Slides->Count;
		$ppt->activePresentation->Export ( $exportPath, "JPG", $width, $height );
		$ppt->Quit ();
		//$ppt->Release();
		return $pptCount;
	} catch ( Exception $e ) {
		echo 'Exception Message: <BR>' . $e->getMessage ();
		return 0;
	}
}

?>