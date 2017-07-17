<?php

/**
 * This is exit page
 * by changzf
 * on 2012/06/09
 */
header("content-type:text/html;charset=utf-8");

require_once ('../inc/global.inc.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$uid=intval(getgpc('user_id','G'));
$sqlss = "select vmid,addres FROM  vslab.vmtotal where user_id= ".$uid;
$ress = api_sql_query ( $sqlss, __FILE__, __LINE__ );
$vm= array ();
while ($vm = Database::fetch_row ( $ress)) {

    $vms [] = $vm;

}

                   $sql = "select `vmid`,`addres` FROM  `vmtotal` where `user_id`= '{$uid}' and  `manage`='0'"; 
                $res = api_sql_query ( $sql, __FILE__, __LINE__ );
                $vm= array (); 
                while ($vm = Database::fetch_row ( $res)) { 
                    $vms [] = $vm; 
                }
                foreach ( $vms as $k1 => $v1){ 
                        $vmid = $v1[0];
                        $vmaddres = $v1[1];
                        if($vmid && $vmaddres){
                            $platforms=file_get_contents(URL_ROOT.'/www'.URL_APPEDND.'/storage/DATA/platform.conf');
                            $platform_array=explode(':',$platforms);
                            $platform=intval(trim($platform_array[1]));

                           // if($platform>3){
                                $output = exec("sudo -u root /usr/bin/ssh root@$vmaddres /sbin/cloudvmstop.sh $vmid");
                                $sqla = "delete  FROM  vmtotal where user_id= '{$user_id}'";
                                api_sql_query ( $sqla, __FILE__, __LINE__ );
                           // } 
                        }
                } 


foreach ( $vms as $k1 => $v1){

    $vmid = $v1[0];
    $vmaddres = $v1[1];
    if($vmid && $vmaddres){

        $output = exec("sudo -u root /usr/bin/ssh root@$vmaddres /sbin/cloudvmstop.sh $vmid");

    }
}
$sqla = "delete  FROM  vmtotal where user_id= '{$uid}'";
api_sql_query ( $sqla, __FILE__, __LINE__ );

header('Location:../../portal/sp/login.php?logout=true');
?>
