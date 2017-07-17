<?php
include_once ("./inc/page_header.php"); 
$sql =  "select id from setup order by id LIMIT 0,1";
$courseId= DATABASE::getval ( $sql, __FILE__, __LINE__ );
if($courseId){
    echo '<script language="javascript"> document.location = "./select_study.php?id='.$courseId.'";</script>';
}else{
    echo '<script language="javascript"> document.location = "./select_study.php";</script>';
}
exit ();
