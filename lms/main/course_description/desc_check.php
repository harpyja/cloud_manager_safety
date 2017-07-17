<?php
 header("content-type:text/html;charset=utf-8");
 
require ('../inc/global.inc.php');


   
$topo_type=getgpc('topo_type','P');

//$topo_type=$_POST['topo_type'];   
 
   
            if($topo_type){ 
                 $sql = "SELECT name FROM networkmap where name like '%$topo_type%'";  
            }else{
                $sql = "SELECT name FROM  networkmap ";
            }
             
            $res = api_sql_query ( $sql, __FILE__, __LINE__ );
            $vm= array ();
            while ( $vm = Database::fetch_row ( $res) ) {
                    $vms [] = $vm;
            }
           
            foreach ( $vms as $k1 => $v1){
                 foreach($v1 as $k2 => $v2){
                     $arr[$v2]  = $v2;
                   }
            }
        
   $op="";
  foreach ( $arr as $v1){ 
            $op.="<option value='$v1'>$v1</option>";
        }
     echo "<span style= 'width:100px;padding-left:120px;'>网络拓扑类型 </span><select id='description13' name='description13' onChange='getarea()' > ".$op." </select>";
 
     ?>


 
