<?php
include('includes/conf.php');

include_once('hub.php');

if (isset($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$action = 'list';
}
$USERID=$_SESSION['_user']['user_id'];
switch ($action) {

    /*************************************************************************
     * Start a device                                                        *
     *************************************************************************/
    case 'device_start':
        if (isset($_GET['dev_id']) && isset($_GET['lab_id'])) {
         $lab_id=htmlspecialchars(trim($_GET['lab_id']));
         $dev_id=htmlspecialchars(trim($_GET['dev_id']));

         $sql_d="select `name` from `labs_labs` where id=".$lab_id;
         $lab_name= DATABASE::getval($sql_d,__FILE__,__LINE__);

         $device_run_count_sql="select count(*) from `labs_run_devices` where `DEVICEID`=".$dev_id." and `labs_name`='".$lab_name."' and `USERID`=".$USERID;
         $device_run_count=DATABASE::getval($device_run_count_sql,__FILE__,__LINE__);

         if($device_run_count!==0){

             $LINKDATA=netmap_str($lab_id,$USERID,$names);//echo $LINKDATA;

             $categorys =array();
             $sql = "select * FROM  `labs_devices` where `lab_id`='".$lab_name."' and `id`=".$dev_id;
             $ress = api_sql_query ( $sql, __FILE__, __LINE__ );
             $vms= array ();
             while ( $vms = Database::fetch_row ( $ress) ) {
                 $vmss [] = $vms;
             }

             $DEVICEID=$vmss[0][0];
             $DEVICEDTYPE=$vmss[0][9];
             $slots=$vmss[0][8];
             $slot=str_replace(";","__",$slots);
             $ROUTETYPE=$vmss[0][3];

                 $sql_i="select `filename`,`idle`,`type`,`ram`,`nvram` from `labs_ios` where `name`='".$ROUTETYPE."'";
                 $result=api_sql_query($sql_i,__FILE__,__LINE__);

                 while ( $ioss = Database::fetch_row ( $result) ) {
                     $labs_ios[] = $ioss;
                 }
                 foreach ( $labs_ios as $k1 => $v1){
                     foreach($v1 as $k2 => $v2){
                         $labs_ioss[]  = $v2;
                     }
                 }

             $IOSFILENAME=$labs_ioss[0];
             $idlepc=$labs_ioss[1];
             $ROUTEMOD=$labs_ioss[2];
             $MEM=$labs_ioss[3];
             $NVRAM=$labs_ioss[$k1][4];
//             $PORT = hub('add','tporthub','1');
//             $p_id='';
//             $run_device_sql = "INSERT INTO `vslab`.`labs_run_devices` (`id`, `course_name`, `labs_name`, `p_id`, `USERID`, `PORT`, `DEVICEID`, `ROUTETYPE`, `ROUTEMOD`, `DEVICEDTYPE`,`status`) VALUES(NULL,'','".$lab_name."','".$p_id."','".$USERID."',".$PORT.",'".$DEVICEID."','".$ROUTETYPE."','".$ROUTEMOD."','".$DEVICEDTYPE."',0);";
//             api_sql_query($run_device_sql,__FILE__,__LINE__);

//             $delete_port_sql="UPDATE  `vslab`.`uporthub` SET  `status` =  '1',`values`='1' WHERE  `Pid` =".$PORT;
//             api_sql_query($delete_port_sql,__FILE__,__LINE__);

             $update_status_sql="update `vslab`.`labs_run_devices` set `status` ='1' WHERE `DEVICEID` =".$dev_id." and `labs_name`='".$lab_name."' and `USERID`=".$USERID;
             api_sql_query($update_status_sql,__FILE__,__LINE__);

            $command='sudo -u root /sbin/cloudvmroute.sh  LABSNAME='.$names.'___USERID='.$USERID.'___PORT='.$PORT.'___DEVICEDTYPE='.$DEVICEDTYPE.'___DEVICEID='.$DEVICEID.'___ROUTETYPE='.$ROUTETYPE.'___IOSFILENAME='.$IOSFILENAME.'___ROUTEMOD='.$ROUTEMOD.'___MEM='.$MEM.'___NVRAM='.$NVRAM.'___idlepc='.$idlepc.'___LINKDATA='.$LINKDATA.'__slot='.$slot;
            $command =  str_replace("____", "___", $command);
            exec("$command  >/dev/null &");
             $start="sudo -u root /tmp/mnt/iostmp/$USERID;";
             exec("$start /sh.sh >/dev/null &");
         }
    }
        header("Location: ".BASE_WWW."/dynamic_maps.php?action=show&id=".$lab_id);
        break;
    /*************************************************************************
     * Stop a device                                                         *
     *************************************************************************/
    case 'device_stop':
        if (isset($_GET['dev_id']) && isset($_GET['lab_id'])) {
//            $sql="select `PORT` from `labs_run_devices` where `DEVICEID`=".$_GET['dev_id'];
//            $device_port=DATABASE::getval($sql,__FILE__,__LINE__);

            $lab_id=htmlspecialchars(trim($_GET['lab_id']));
            $dev_id=htmlspecialchars(trim($_GET['dev_id']));

            $sql_d="select `name` from `labs_labs` where id=".$lab_id;
            $lab_name= DATABASE::getval($sql_d,__FILE__,__LINE__);

            $stop_sql="select `id`,`labs_name`,`USERID`,`DEVICEID`,`DEVICEDTYPE`,`PORT`,`ROUTEMOD` from `labs_run_devices` where `DEVICEID`=".$_GET['dev_id']." and `labs_name`='".$lab_name."' and `USERID`=".$USERID;
            $res=api_sql_query($stop_sql,__FILE__,__LINE__);
            while ( $stop_device = Database::fetch_row ( $res) ) {
                $stop_device_arr[] = $stop_device;
            }
            $d_id     =$stop_device_arr[0][0];
            $labsName =$stop_device_arr[0][1];
            $userId   =$stop_device_arr[0][2];
            $deviceId =$stop_device_arr[0][3];
            $DEVICEDTYPE =$stop_device_arr[0][4];
            $PORT =$stop_device_arr[0][5];
            $ROUTEMOD =$stop_device_arr[0][6];

            $delete_port_sql="UPDATE  `vslab`.`uporthub` SET  `status` =  '0',`values`='0' WHERE  `Pid` ='".$PORT."'";

            api_sql_query($delete_port_sql,__FILE__,__LINE__);

            $update_status_sql="update `vslab`.`labs_run_devices` set `status` ='0' WHERE `DEVICEID` =".$_GET['dev_id']." and `labs_name`='".$lab_name."' and `USERID`=".$USERID;
            api_sql_query($update_status_sql,__FILE__,__LINE__);

            $deletecommand="sudo -u root /sbin/cloudvmroutestop.sh  ".$userId." ".$labsName." ".$userId."_".$labsName."_".$DEVICEDTYPE."_".$deviceId." ".$userId."_".$labsName."_".$PORT;
            exec("$deletecommand  >/dev/null &; killall /tmp/mnt/iostmp/$USERID/$labsName/$DEVICEDTYPE"."_$d_id/$ROUTEMOD"."_i0_nvram");
//            echo "$deletecommand  >/dev/null &<br>";
        }
        header("Location: ".BASE_WWW."/dynamic_maps.php?action=show&id=".$_GET['lab_id']);
        break;
    /*************************************************************************
     * Export startup-config                                                 *
     *************************************************************************/
    case 'config_export':
        if (isset($_GET['dev_id']) && isset($_GET['lab_id'])) {
            $lab_id=htmlspecialchars(trim($_GET['lab_id']));
            $dev_id=htmlspecialchars(trim($_GET['dev_id']));

            $e_sql="select `id`,`labs_name`,`USERID`,`DEVICEID`,`DEVICEDTYPE`,`PORT`,`ROUTEMOD` from `labs_run_devices` where `DEVICEID`=".$_GET['dev_id']." and `labs_name`='".$lab_name."' and `USERID`=".$USERID;
            $res=api_sql_query($e_sql,__FILE__,__LINE__);
            while ( $e_device = Database::fetch_row ( $res) ) {
                $e_device_arr[] = $e_device;
            }
            $d_id     =$e_device_arr[0][0];
            $labsName =$e_device_arr[0][1];
            $userId   =$e_device_arr[0][2];
            $deviceId =$e_device_arr[0][3];
            $DEVICEDTYPE =$e_device_arr[0][4];
            $PORT =$e_device_arr[0][5];
            $ROUTEMOD =$e_device_arr[0][6];

            $config_export="cd /tmp/mnt/iostmp/$USERID/$labsName/$DEVICEDTYPE"."_$d_id/$ROUTEMOD"."_i0_nvram";
            exec("tar -cvf ");
            echo "<script>alert('下载配置');</script>";
        }
        //   header("Location: ".BASE_WWW."/dynamic_maps.php?action=show&id=".$_GET['lab_id']);
        break;
    /*************************************************************************
     * Backup a device                                                       *
     *************************************************************************/
    case 'config_backup':
        if (isset($_GET['dev_id']) && isset($_GET['lab_id'])) {
            $lab_id=htmlspecialchars(trim($_GET['lab_id']));
            $dev_id=htmlspecialchars(trim($_GET['dev_id']));

            $sql_d="select `name` from `labs_labs` where id=".$lab_id;
            $lab_name= DATABASE::getval($sql_d,__FILE__,__LINE__);

            echo "<script>alert('备份镜像');</script>";
        }
        header("Location: ".WEB_QH_PATH."/dynamic_maps.php?action=show&id=".$_GET['lab_id']);
        break;
    /*************************************************************************
     * Restore a laboratory                                                  *
     *************************************************************************/
    case 'config_restore':
        if (isset($_GET['dev_id']) && isset($_GET['lab_id'])) {
            $lab_id=htmlspecialchars(trim($_GET['lab_id']));
            $dev_id=htmlspecialchars(trim($_GET['dev_id']));

            $sql_d="select `name` from `labs_labs` where id=".$lab_id;
            $lab_name= DATABASE::getval($sql_d,__FILE__,__LINE__);

            echo "<script>alert('恢复配置');</script>";
        }
        header("Location: ".BASE_WWW."/dynamic_maps.php?action=show&id=".$_GET['lab_id']);
        break;
    /*************************************************************************
     * Wipe a laboratory                                                     *
     *************************************************************************/
    case 'config_wipe':
        if (isset($_GET['dev_id']) && isset($_GET['lab_id'])) {
            $lab_id=htmlspecialchars(trim($_GET['lab_id']));
            $dev_id=htmlspecialchars(trim($_GET['dev_id']));

            $sql_d="select `name` from `labs_labs` where id=".$lab_id;
            $lab_name= DATABASE::getval($sql_d,__FILE__,__LINE__);

            echo "<script>alert('抓包');</script>";
        }
        header("Location: ".BASE_WWW."/dynamic_maps.php?action=show&id=".$_GET['lab_id']);
        break;

}
