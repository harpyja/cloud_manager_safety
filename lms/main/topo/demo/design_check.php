<?php
 header("content-type:text/html;charset=utf-8");
$language_file = array ('admin', 'registration' );
$cidReset = true; 
require ('../../inc/global.inc.php');

$device_type=  getgpc('device_type','P');  //用户输入的关键字 
$initName =  getgpc('initNm','P');
$initName =  urldecode($initName); 
 
//所有类型
        $device = "select * from device_type ";
        $result = api_sql_query($device, __FILE__, __LINE__ );
        while ( $rst = Database::fetch_row ( $result) ) {
            $ste [] = $rst;
        }
        foreach ( $ste as $k1 => $v1){
            foreach($v1 as $k2 => $v2){
                $arr[$k1][]  = $v2;
            }
        } 
      
foreach ($arr as $k1 => $v1){  
    if($initName == $arr[$k1][1]){
        if($initName){
             $sql = "SELECT name FROM vmdisk where CD_mirror!='' &&  category='".$arr[$k1][1]."' && name like '%$device_type%'";  
        }else{
            $sql = "SELECT name FROM vmdisk where CD_mirror!='' &&  category='".$arr[$k1][1]."'";
        }
        $res = api_sql_query ( $sql, __FILE__, __LINE__ );
        $vm= array ();
        while ( $vm = Database::fetch_row ( $res) ) {
            $vms [] = $vm[0];
        }
        $design = array_combine($vms,$vms);
    }
}
 
  foreach ( $design as $v1){ 
            $ios.="<option value='$v1'>$v1</option>";
        }
     echo "<select id='Flag' name='ios_name' onChange='getarea()' > ".$ios." </select>";
 
?>
