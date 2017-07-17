<?php
header("Content-type:text/html;charset=utf-8"); 
$w=intval(trim($_GET['w']));
$h=intval(trim($_GET['h']));
$f=trim($_GET['f']);

$paths='../../main/cloud/cloudplay.php';
if($w && $h){
  $paths.="?w=".$w."&h=".$h;
}else{
  $paths.="?w=1024&h=768";
}

if($f){
  $paths.="&f=".$f;
  header("location:".$paths);
}else{
   echo "<br><br><h1 align=center>对不起，没有该录屏文件！</h1>";
   exit;
}
?>
