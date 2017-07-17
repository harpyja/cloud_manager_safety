<?php 
header("content-type:text/html;charset=utf-8");
$language_file = array ('admin', 'registration' );
$cidReset = true; 
require ('../../inc/global.inc.php');

 
$vm_name=  getgpc("vm_name");
if($vm_name){
    $sql = "select `name` from `vmdisk` where name like '%".$vm_name."%' ";
}else{
    $sql = "select `name` from `vmdisk` ";
} 
 
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$vm= array ();
while ( $vm = Database::fetch_row ( $res) ) {
    $vms [] = $vm[0];
}
$design = array_combine($vms,$vms);
foreach ( $design as $v1){ 
            $ios.="<option value='$v1'>$v1</option>";
        }
        
$designs='<td style="border: 1px dotted #e1e1e1;text-align:right;background-color:#F7F7F7;">增量镜像</td><td><select id= "selectMirror" name= "CD_mirror" > '.$ios.'</td></select>';
 
echo $designs;
?>
