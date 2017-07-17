<?php
$language_file = array ('admin' );
$cidReset = true;
include ('../../main/inc/global.inc.php');
api_protect_admin_script ();

if (! isRoot ()) api_not_allowed ();

$sql = "SELECT COUNT(user_id) FROM " . Database::get_main_table ( TABLE_MAIN_USER );
$user_count = Database::get_scalar_value ( $sql );

$sql = "SELECT COUNT(user_id) FROM " . Database::get_main_table ( TABLE_MAIN_USER_REGISTER ) . " WHERE reg_status=" . AUDIT_REGISTER_INIT;
$reg_user_count = Database::get_scalar_value ( $sql );

$sql = "SELECT COUNT(code) FROM " . Database::get_main_table ( TABLE_MAIN_COURSE );
$course_count = Database::get_scalar_value ( $sql );

$sql="SELECT(id) FROM ".Database::get_main_table(TABLE_QUIZ_TEST)." WHERE active=1";
$quiz_count= Database::get_scalar_value ( $sql );

$sql="SELECT(id) FROM ".Database::get_main_table(TABLE_MAIN_SYSTEM_ANNOUNCEMENTS)." WHERE visible=1";
$anno_count= Database::get_scalar_value ( $sql );

$tbl_track_cw = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_CW );
$sql = "SELECT SUM(total_time) FROM " . $tbl_track_cw ;
$total_learning_time=api_time_to_hms(Database::get_scalar_value ( $sql ));

$sql2=$sql." WHERE MONTH(FROM_UNIXTIME(last_access_time))=MONTH(NOW())";
$total_learning_time2=api_time_to_hms(Database::get_scalar_value ( $sql2 ));

$this_year=date('Y');
$sql="SELECT COUNT(login_id) FROM ".Database::get_main_table(TABLE_STATISTIC_TRACK_E_LOGIN)." WHERE YEAR(login_date)=".$this_year;
$total_login_count= Database::get_scalar_value ( $sql );



define("MONITORED_IP", "172.16.0.191");

function get_used_status(){
    $fp = popen('top -b -n 2 | grep -E "^(Cpu|Mem|Tasks)"',"r");//获取某一时刻系统cpu和内存使用情况
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
    $fp = popen('df -lh | grep -E "^(/)"',"r");
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
//    $fp2 = popen('df -lh | grep -E "^(/dev/sdb1)"',"r");
    $fp2 = popen('df -lh | grep -E "^(/dev/sda5)"',"r");
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
Display::display_header ();

$status=get_used_status();


?>



<aside id="sidebar" class="column systeminfo open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/systeminfo.php">系统管理</a> &gt; 系统信息</h4>
    <article class="module width_full hidden ip">
        <table cellpadding="0" cellspacing="0" id="systeminfo">
            <tbody>
            <tr>
                <td class="title t2">系统运行环境信息</td>
                <td class="title"></td>
            </tr>
            <tr>
                <td class="tt">CPU及内存使用率：</td>
                <td><?php echo "CPU使用率：".$status['cpu_usage']."%".'&nbsp;&nbsp;&nbsp;'.'内存使用率：'.$status['mem_usage']."%";?></td>
            </tr>
            <tr>
                <td class="tt">系统磁盘使用情况：</td>
                <td><?php echo '可用空间大小：'.$status['hd_avail_root'].'G&nbsp;&nbsp;&nbsp;'.'挂载点 百分比：'.$status['hd_usage_root']."%";?></td>
            </tr>
           <tr>
                <td class="tt">学习系统磁盘使用情况：</td>
                <td><?php echo '可用空间大小：'.$status['hd_avail_www'].'G&nbsp;&nbsp;&nbsp;'.'挂载点 百分比：'.$status['hd_usage_www']."%";?></td>
            </tr><!--
            <tr>
                <td class="tt">数据磁盘使用情况：</td>
                <td><?php echo '可用空间大小:'.$status['hd_avail_sdb1'].'G&nbsp;&nbsp;&nbsp;'.'挂载点 百分比:'.$status['hd_usage_sdb1']."%";?></td>
            </tr>-->
            <tr>
                <td class="tt">运行进程数：</td>
                <td><?php echo $status['tast_running'];?></td>
            </tr>
            <tr>
                <td class="tt">检测时间：</td>
                <td><?php echo $status['detection_time'];?></td>
            </tr>
            <tr>
                <td class="tt">系统账号数:</td>
                <td><a href="<?=URL_APPEND?>main/admin/user/user_list.php"><?=$user_count?></a> 个 (新注册: <a href="<?=URL_APPEND?>main/admin/user/user_list_audit.php?status=1"><?=$reg_user_count?></a> 个)</td>
            </tr>
            <tr>
                <td class="tt">系统课程数: </td>
                <td><a href="<?=URL_APPEND?>main/admin/course/course_list.php"><?=$course_count?></a> 门</td>
            </tr>
            <tr>
                <td class="tt">公告总数：</td>
       <td><a href="<?=URL_APPEND?>main/admin/misc/system_announcements.php"><?php if($anno_count==''){ $anno_count=0;}echo $anno_count;?></a>个</td>     
        	</tr>
			<tr>
				<td class="tt">系统版本：</td>
				<td>
					<?php
						$file = file("/etc/product.conf");
						$file =  $file[1];
						$fil = explode('=',$file,2);
						echo $fil[1] ;    
					?>
				</td>
			</tr>
			<?php
				$sysidfile="/etc/sysid.sys";
				$sysid=file_get_contents($sysidfile);
				$license="/etc/license.lic";
				$license=file_get_contents($license);
				$lessonnum="/etc/lessonnum";
				$lessonnum=file_get_contents($lessonnum);
				$lessonuser="/etc/lessonuser";
				$lessonuser=file_get_contents($lessonuser);
			?>
			<tr>
				<td class="title t2">概要信息：</td>
				<td class="title"></td>
			</tr>

            <tr>
                <td class="tt">系统序列号:</td>
                <td><?=$sysid?></td>
            </tr>
            <tr>
                <td class="tt">系统许可证号: </td>
                <td><?=$license?></td>
            </tr>
            <tr>
                <td class="tt">并发用户数: </td>
                <td><?=$lessonuser?></td>
            </tr>
            <tr>
                <td class="tt">更新license 文件: </td>
                <td>
					<?php

            $is_allowed_to_edit = api_is_allowed_to_edit ();

            if (! $is_allowed_to_edit) api_not_allowed ();

            $user_id = api_get_user_id ();

            $table_courseware = Database::get_course_table ( TABLE_COURSEWARE );

            $courseDir = api_get_course_code () . '/html/';

            $base_work_dir = api_get_path ( SYS_COURSE_PATH ) . $courseDir;

            $max_upload_file_size = get_upload_max_filesize ( api_get_setting ( "upload_max_filesize" ) );

            $ftp_path = api_get_path ( SYS_FTP_ROOT_PATH ) . 'zip/';



            require_once (api_get_path ( SYS_CODE_PATH ) . "courseware/cw.lib.inc.php");

            include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');





            $form = new FormValidator ( 'upload', 'POST', "systeminfo.php", '', 'enctype="multipart/form-data"' );

            Display::setTemplateBorder ( $form, '98%' );





            if ($form->validate ()) {

                //设置内存及执行时间

                ini_set ( 'memory_limit', '256M' );

                ini_set ( 'max_execution_time', 1800 ); //设置执行时间

                $upfile = '/tmp/t.z';

                move_uploaded_file ( $_FILES ['user_upload'] ['tmp_name'], $upfile );

                system ('/sbin/cloudlicense /tmp/t.z') ;

                system ('rm -rf  /tmp/t.z') ;



            }

            $form->display ();

            $form = new FormValidator ( 'upload', 'POST', "systeminfo.php", '', 'enctype="multipart/form-data"' );

            $renderer = $form->defaultRenderer ();

            $renderer->setElementTemplate ( '<span>&nbsp;{element}</span> ' );

            $form->addElement ( 'file', 'user_upload', '', array ('class' => 'inputText', 'id' => 'upload_file_local' ) );



            $form->addElement ( 'submit', 'submit', get_lang ( '提交' ), 'class="inputSubmit"' );



            echo '<div >';

            $form->display ();

            echo '</div>';



            ?>
				</td>
            </tr>
            </tbody>
        </table>
    </article>
</section>
</body>
</html>
