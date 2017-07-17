<?php

header("Content-Type:text/xml;charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

include("../../../../main/inc/global.inc.php");

$uploadFileName=$_FILES['Filedata']['name'];
$uploadFile=$_FILES['Filedata']['tmp_name'];
if(is_uploaded_file($uploadFile))
{
	$pos=strrpos($uploadFileName,'.');
	$len=strlen($uploadFileName);
	$extendType=substr($uploadFileName,$pos,$len);

	$localFileName=($_GET['fileName']);
	$localFile="wbUpload/".trim($localFileName);

	if(move_uploaded_file($uploadFile,$localFile))
	{
			
	}
	else
	{
		if(DEBUG_MODE) api_error_log('upload zlmeet wbFile -'.$uploadFile." ERROR!",__FILE__,__LINE__,"zlmeet.log");
	}
}
?>