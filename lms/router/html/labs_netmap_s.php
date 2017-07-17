<?php
include('includes/conf.php');
$language_file = 'admin';
$cidReset = true;
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();

$aaa=$_COOKIE['data'];
$id=$_COOKIE['id'];
$netmap = str_replace(",", "\n", $aaa);
$netmap= str_replace('node','',$netmap);
$netmap= str_replace('e','',$netmap);
$netmap= str_replace('s','',$netmap);
if($netmap==''){
    $sql="select `name` from `labs_labs` where id=".$id;
    $getLabsName=DATABASE::getval($sql);

    $sql1="select `id` from `labs_devices` where `lab_id`='".$getLabsName."'";
    $res = api_sql_query ( $sql1, __FILE__, __LINE__ );
    $arr= array ();

    while ( $arr = Database::fetch_row ( $res) ) {
        $arrs [] = $arr;
    }
    $a='';
    for($i=0;$i<count($arrs);$i++){
        $a .=$arrs[$i][0].":0/0\r\n";
    }

    $sql="UPDATE  `vslab`.`labs_labs` SET  `netmap` =  '".$a."' WHERE  `labs_labs`.`id` =".$id;
    api_sql_query($sql);
}else{
    $sql="UPDATE  `vslab`.`labs_labs` SET  `netmap` =  '".$netmap."' WHERE  `labs_labs`.`id` =".$id;
    api_sql_query($sql);
}

tb_close ();
?>
