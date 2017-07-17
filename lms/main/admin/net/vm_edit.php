<?php
include_once ("../../inc/global.inc.php");
if($_GET['id']){
    $id = $_GET['id'];
    //global $view_id;
    header("location:../../../main/topo/demo/topodesign.php?id=$id");
}
//else{
//
//    header("location:../../../main/topo/demo/topodesign.php");
//}


?>
