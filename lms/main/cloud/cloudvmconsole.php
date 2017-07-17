<?php
 require_once ('../inc/global.inc.php');
 $vmid = intval( getgpc('vmid','G'));
 $action   = getgpc('action','G'); 
if ($action == "start")
  {
	$output = exec("sudo -u root /sbin/cloudvmstart.sh $vmid ");
  }
if ($action == "stop")
  {
	$output = exec("sudo -u root qm stop $vmid ");
  }


	$output = exec("sudo -u root /sbin/cloudvmweb.sh xxx ");
	header ( "Location: /lms/main/admin/vmmanage.php" );
?>
