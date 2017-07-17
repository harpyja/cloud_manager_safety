<?php 
$platforms=file_get_contents('../../storage/DATA/platform.conf');
$platform_array=explode(':',$platforms);
$platform=intval(trim($platform_array[1]));
//echo $platform;  //4-----lms
if($platform==3){
	include_once("../exam/exam_list.php");
}else{
	include_once("exercice.php");
}

?>