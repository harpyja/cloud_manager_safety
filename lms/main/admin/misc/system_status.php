<?php

include_once ('../../inc/global.inc.php');
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$language_file = 'admin';
$cidReset = true;

define("MONITORED_IP", "172.16.0.191");

function get_used_status(){
    $fp = popen(' top -bcisSH -n 2 | grep -E "^(Cpu|Mem|Tasks)"',"r");//获取某一时刻系统cpu和内存使用情况
    $rs = "";
    while(!feof($fp)){
        $rs .= fread($fp,1024);
    }
    pclose($fp);
    $sys_info = explode("\n",$rs);

    $tast_info = explode(",",$sys_info[3]);//进程 数组
    $cpu_info = explode(",",$sys_info[4]); //CPU占有量 数组
    $mem_info = explode(",",$sys_info[5]); //内存占有量 数组

//正在运行的进程数
    $tast_running = trim(trim($tast_info[1],'running'));

//CPU占有量
    $cpu_usage = trim(trim($cpu_info[0],'Cpu(s): '),'%us'); //百分比

//内存占有量
    $mem_total = trim(trim($mem_info[0],'Mem: '),'k total');
    $mem_used = trim($mem_info[1],'k used');
    $mem_usage = round(100*intval($mem_used)/intval($mem_total),2); //百分比


    /*硬盘使用率 begin*/
    $fp = popen('df -lh | grep -E "^(/dev/pve/root)"',"r");
    $rs = fread($fp,1024);
    pclose($fp);
    $rs = preg_replace("/\s{2,}/",' ',$rs); //把多个空格换成 “_”
    $hd = explode(" ",$rs);
    $hd_avail_root = trim($hd[3],'G'); //磁盘可用空间大小 单位G
    $hd_usage_root = trim($hd[4],'%'); //挂载点 百分比
//print_r($hd);
    /*硬盘使用率 end*/

    /*硬盘使用率 begin*/
    $fp1 = popen('df -lh | grep -E "^(www)"',"r");
    $rs1 = fread($fp1,1024);
    pclose($fp1);
    $rs1 = preg_replace("/\s{2,}/",' ',$rs1); //把多个空格换成 “_”
    $hd1 = explode(" ",$rs1);
    $hd_avail_www = trim($hd1[3],'G'); //磁盘可用空间大小 单位G
    $hd_usage_www = trim($hd1[4],'%'); //挂载点 百分比
//print_r($hd);
    /*硬盘使用率 end*/


    /*硬盘使用率 begin*/
    $fp2 = popen('df -lh | grep -E "^(/dev/sdb1)"',"r");
    $rs2 = fread($fp2,1024);
    pclose($fp2);
    $rs2 = preg_replace("/\s{2,}/",' ',$rs2); //把多个空格换成 “_”
    $hd2 = explode(" ",$rs2);
    $hd_avail_sdb1 = trim($hd2[3],'G'); //磁盘可用空间大小 单位G
    $hd_usage_sdb1 = trim($hd2[4],'%'); //挂载点 百分比
//print_r($hd);
    /*硬盘使用率 end*/

//检测时间
    $fp = popen("date +\"%Y-%m-%d %H:%M\"","r");
    $rs = fread($fp,1024);
    pclose($fp);
    $detection_time = trim($rs);

    /*获取IP地址 begin*/
    /*
    $fp = popen('ifconfig eth0 | grep -E "(inet addr)"','r');
    $rs = fread($fp,1024);
    pclose($fp);
    $rs = preg_replace("/\s{2,}/",' ',trim($rs)); //把多个空格换成 “_”
    $rs = explode(" ",$rs);
    $ip = trim($rs[1],'addr:');
    */
    /*获取IP地址 end*/
    /*
    $file_name = "/tmp/data.txt"; // 绝对路径: homedata.dat
    $file_pointer = fopen($file_name, "a+"); // "w"是一种模式，详见后面
    fwrite($file_pointer,$ip); // 先把文件剪切为0字节大小， 然后写入
    fclose($file_pointer); // 结束
    */

    return array('cpu_usage'=>$cpu_usage,'mem_usage'=>$mem_usage,'hd_avail_root'=>$hd_avail_root,'hd_usage_root'=>$hd_usage_root,'tast_running'=>$tast_running,'detection_time'=>$detection_time,'hd_avail_www'=>$hd_avail_www,'hd_usage_www'=>$hd_usage_www,'hd_avail_sdb1'=>$hd_avail_sdb1,'hd_usage_sdb1'=>$hd_usage_sdb1);
}

//echo date("Y-m-d H:i:s",time())."<br>";

Display::display_header ( NULL );
$status=get_used_status();

//$sql = "insert into performance(ip,cpu_usage,mem_usage,hd_avail,hd_usage,tast_running,detection_time) value('".MONITORED_IP."','".$status['cpu_usage']."','".$status['mem_usage']."','".$status['hd_avail']."','".$status['hd_usage']."','".$status['tast_running']."','".$status['detection_time']."')";

//$query = api_sql_query($sql) or die("SQL 语句执行失败!");

?>
<table align="center" width="80%" cellpadding="4" cellspacing="0">
    <tr class="containerBody">
        <td colspan="2"><b>&nbsp;&nbsp;系统运行状态</b></td>
    </tr>
    <tr class="containerBody">
        <td class="formLabel">CPU及内存使用率&nbsp;&nbsp;&nbsp;</td>
        <td class="formTableTd" align="left">&nbsp;&nbsp;&nbsp;<?php echo "CPU使用率：".$status['cpu_usage']."%".'&nbsp;&nbsp;&nbsp;'.'内存使用率：'.$status['mem_usage']."%";?></td>
    </tr>
    <tr class="containerBody">
        <td class="formLabel">系统磁盘使用情况 &nbsp;&nbsp;&nbsp;</td>
        <td class="formTableTd" align="left">&nbsp;&nbsp;&nbsp;<?php echo '可用空间大小：'.$status['hd_avail_root'].'G&nbsp;&nbsp;&nbsp;'.'挂载点 百分比：'.$status['hd_usage_root']."%";?></td>
    </tr>
    <tr class="containerBody">
        <td class="formLabel">学习系统磁盘使用情况 &nbsp;&nbsp;&nbsp;</td>
        <td class="formTableTd" align="left">&nbsp;&nbsp;&nbsp;<?php echo '可用空间大小：'.$status['hd_avail_www'].'G&nbsp;&nbsp;&nbsp;'.'挂载点 百分比：'.$status['hd_usage_www']."%";?></td>
    </tr>
    <tr class="containerBody">
        <td class="formLabel">数据磁盘使用情况 &nbsp;&nbsp;&nbsp;</td>
        <td class="formTableTd" align="left">&nbsp;&nbsp;&nbsp;<?php echo '可用空间大小:'.$status['hd_avail_sdb1'].'G&nbsp;&nbsp;&nbsp;'.'挂载点 百分比:'.$status['hd_usage_sdb1']."%";?></td>
    </tr>
    <tr class="containerBody">
        <td class="formLabel">运行进程数&nbsp;&nbsp;&nbsp;</td>
        <td class="formTableTd" align="left">&nbsp;&nbsp;&nbsp;<?php echo $status['tast_running'];?></td>
    </tr>
    <tr class="containerBody">
        <td class="formLabel">检测时间&nbsp;&nbsp;&nbsp;</td>
        <td class="formTableTd" align="left">&nbsp;&nbsp;&nbsp;<?php echo $status['detection_time'];?></td>
    </tr>
</table>

<?php
unset($status);
Display::display_footer ( TRUE );
