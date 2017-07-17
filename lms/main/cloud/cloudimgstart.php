<?php
require_once ('../inc/global.inc.php');
 $vmid     =  intval(getgpc('vmid','G'));
 $hostname = getgpc('hostname','G'); 
 $disktype = getgpc('disktype','G'); 
 $action   = getgpc('action','G'); 
if ($action == "start")
  {
	$output = exec("sudo -u root /sbin/cloudimgstart.sh $vmid $hostname $disktype ");
  }
if ($action == "stop")
  {
	$vmid = $vmid+1024;
	$output = exec("sudo -u root qm stop $vmid ");
  }


	$output = exec("sudo -u root /sbin/cloudvmweb.sh xxx ");
	header ( "Location: /lms/main/admin/vmdisk/vmdisk_list.php" );
?>
