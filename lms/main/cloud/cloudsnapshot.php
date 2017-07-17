<?php
header("content-type:text/html;charset=utf-8");
require_once ('../../main/inc/global.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
$snapshotdesc=$_POST['desc'];
$host=$_POST['host'];
$proxyport=$_POST['port'];
if(empty($snapshotdesc)){
    $snapshotdesc='无';
}
$sql="select * from vmtotal where proxy_port=$proxyport";
$ress = api_sql_query ( $sql, __FILE__, __LINE__ );
$vm= array ();
while ($vm = Database::fetch_row ( $ress)) {
    $vms [] = $vm;
}

$addres=$vms[0][1];
$system=$vms[0][3];
$user_id=(int)$vms[0][4]; 
$lesson_id=(int)$vms[0][5];
$vmid=(int)$vms[0][6];
$port1=(int)$vms[0][7];
$mac_id=$vms[0][9];
$proxy_port=(int)$vms[0][10];
$nicnum=(int)$vms[0][2];

$time=date('Y-m-d-H-i-s',time());
$filename=$user_id.'_'.$time.'_1_'.$lesson_id;

$strsql2="INSERT INTO snapshot(addres,system,user_id,lesson_id,vmid,port,mac_id,proxy_port,status,type,filename,time,snapshotdesc) VALUES('".$addres."','".$system."',$user_id,$lesson_id,$vmid,$port1,'".$mac_id."', $proxy_port,0,1,'".$filename."','".$time."','".$snapshotdesc."')";
$res= api_sql_query ( $strsql2, __FILE__, __LINE__ );

$command2='sudo -u root /sbin/cloudvncsnapshot.sh addres='.$addres.'___port='.$port1.'___proxy_port='.$proxy_port.'___filename='.$filename.'';
exec("$command2 &");
?>
<script type="text/javascript">
window.close();
</script>