<?php 
header("Content-Type:text/xml;charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

include_once("../../../../main/inc/global.inc.php");

$uploadFileName=$_FILES['Filedata']['name'];
$uploadFile=$_FILES['Filedata']['tmp_name'];
	
$pos=strrpos($uploadFileName,'.');
$len=strlen($uploadFileName);
$localFormat=substr($uploadFileName,$pos+1,$len);
	
if(!in_array(strtolower($localFormat),array("php","sh","exe","bat")) && is_uploaded_file($uploadFile))
{
		$pos=strrpos($uploadFileName,'.');
		$len=strlen($uploadFileName);
		$extendType=substr($uploadFileName,$pos,$len);
		$localFileName=trim($_GET['fileName']);
		$localFile="upload/temp/".$localFileName;
		
		if(move_uploaded_file($uploadFile,$localFile))
		{
			
		}
		else
		{
			if(DEBUG_MODE) api_error_log('private zlmeet uploadfile -'.$localFileName." Failed!",__FILE__,__LINE__,"zlmeet.log");
		}
}
?>