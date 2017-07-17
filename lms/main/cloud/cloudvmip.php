<?php
header("content-type:text/html;charset=utf-8");
echo '<meta http-equiv="Refresh" content="5">';
require_once ('../../main/inc/global.inc.php');

require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$userId = intval(getgpc('user_id','G')); 
if(isset($userId) && $userId!=''){

    exec("sudo -u root /sbin/clouddhcplease.sh;");
    exec("sudo -u root /sbin/cloudscanning.sh dhcp;");
    $mac= Database::getval("select `mac_id` from `vmtotal` where `user_id`=".$userId,__FILE__,__LINE__);
    $ip_sql="select `IP_address` from `clouddesktopscan` where `physical_address`='".$mac."'";
    $ip= Database::getval($ip_sql,__FILE__,__LINE__);
    if($ip==''){
        echo '服务器正在启动,请耐心等待目标服务器地址的显示........';
    }else{
        echo '您当前的目标服务器地址为：<b>'.$ip.'</b>,请进行相关操作！';
    }
}

?>

