<?php 
header("content-type:text/html;charset=utf-8");
require_once ('../inc/global.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
if(isset($_POST['desc'])){
      $vmaddres=$_POST['host'];
      $proxy_port=$_POST['port'];
      $var=$_POST['desc'];
      if($_POST['desc']==''){
          $var='无';
      }
   }else{
    $vmaddres=$_GET['host'];
    $proxy_port=$_GET['port'];
}

$sql="select user_id,port,vmid from vmtotal where proxy_port=$proxy_port";
$ress = api_sql_query ( $sql, __FILE__, __LINE__ );
$vm = Database::fetch_row ( $ress);
$user_id=$vm[0];
$port=$vm[1];
$port1=$vm[1];
$vmid=$vm[2];
$sql_snap="select id from `snapshot` where `user_id`='".$user_id."' and  `type`=2 and `port`='".$port."' and `vmid`='".$vmid."' and `status`='1' ";
$dada=Database::getval( $sql_snap,__FILE__,__LINE__);
//var_dump($data);
if($dada!=null)
{
    $sqlss = "select * FROM  vmtotal where proxy_port=$proxy_port";
    $ress = api_sql_query ( $sqlss, __FILE__, __LINE__ );
    $vm = Database::fetch_row ( $ress);
    $user_id=$vm[4];
    $vmid=$vm[6];
    $port=$vm[7];
    $rec=1;         
        $sqlssx = "select filename FROM snapshot where  user_id = $user_id and vmid = $vmid and port = $port and status = 1 ";
        $ressx = api_sql_query ( $sqlssx, __FILE__, __LINE__ );
        $vmx = Database::fetch_row ( $ressx);
        $filename=$vmx[0];
    if($rec){
        $sqla="update snapshot set `status`= 0 where user_id = $user_id and vmid = $vmid and port = $port";
        api_sql_query ( $sqla, __FILE__, __LINE__ );  
        Database::fetch_row ( $res1);
        $command2='sudo -u root /sbin/cloudvncrec.sh addres='.$addres.'___port='.$port1.'___proxy_port='.$proxy_port.'___filename='.$filename.'___status=0';
        exec("$command2 &");
     }else{
           exit();
     }   
}else{
        $time=date("Y-m-d-H-i-s",time()); 
        $sqlss = "select * FROM  vmtotal where proxy_port=$proxy_port";
        $ress = api_sql_query ( $sqlss, __FILE__, __LINE__ );
        $vm = Database::fetch_row ( $ress);
        $id=$vm[0];
        $addres=$vm[1];
        $nicnum=$vm[2];
        $system=$vm[3];
        $user_id=$vm[4];
        $lesson_id=$vm[5];
        $vmid=$vm[6];
        $port=$vm[7];
        $group_id=$vm[8];
        $mac_id=$vm[9];
        $proxy_port=$vm[10]; 
        $filename=$user_id.'_'.$time.'_2_'.$lesson_id; 
        $sqla = "insert into snapshot(addres,system,user_id,lesson_id,vmid,port,mac_id,proxy_port,status,type,filename,time,snapshotdesc) values('$addres','$system','$user_id','$lesson_id','$vmid','$port','$mac_id','$proxy_port','1','2','$filename','$time','$var')";
        api_sql_query ( $sqla, __FILE__, __LINE__ );
        $command2='sudo -u root /sbin/cloudvncrec.sh addres='.$addres.'___port='.$port1.'___proxy_port='.$proxy_port.'___filename='.$filename.'___status=1';
        exec("$command2 &");
        //tb_close ( "../html5/cloudvmconsle.php?host=$vmaddres&port=$proxy_port");
        //winclose();
}

?>
<script type="text/javascript">
    window.opener.location.reload();
    self.close();
</script>
