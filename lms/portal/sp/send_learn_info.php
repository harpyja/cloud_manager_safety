<?php 
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */ 
header("Content-Type:text/html;charset=utf-8");
$language_file = array ("registration" );
$cidReset = true;
include_once ('../../main/inc/global.inc.php'); 

$userId=$_SESSION['_user']['user_id'];     
 
$course_code=$_GET['cidReq'];     //201211922390

//设备编号
$url=URL_ROOT."/www".URL_APPEDND."/deviceId";
$dev_id=file_get_contents($url);
 
$sysid_file="/etc/sysid.sys";
$sysid=file_get_contents($sysid_file);
$cloud_ip="/tmp/cloudIp";
$cloudip=file_get_contents($cloud_ip);
$cloudip=  trim($cloudip);

$info_array=array(); 
$sq="select status from user where user_id=".$userId;
$u_status=  Database::getval($sq);
if($u_status==5){
   //学习情况信息course,course_rel_user,user,sys_user_dept
    $sql2="select rel.course_code,c.title,c.tutor_name,c.credit,rel.is_course_admin,rel.creation_time,rel.is_required_course,c.category_code,rel.begin_date,rel.finish_date,rel.is_pass,rel.got_credit,rel.last_access_time,rel.exam_score,rel.learning_status,rel.exam_status,rel.access_times,rel.progress from course as c join course_rel_user as rel on c.code=rel.course_code join user as u  on  rel.user_id=u.user_id  join sys_user_dept as d on u.user_id=d.user_id  where rel.user_id=".$userId." and u.status=5  and rel.course_code=".$course_code;
    $result2= api_sql_query_array_assoc($sql2, __FILE__, __LINE__);
    $info_array['learning_info']=$result2;
}
 
$results='';
$results= $info_array;
//$re4是学习情况信息
$l=$results['learning_info'][0];
$learn_infos="&course_code=".$l['course_code']."&title=".$l['title']."&tutor_name=".$l['tutor_name']."&credit=".$l['credit']."&is_course_admin=".$l['is_course_admin']."&creation_time=".$l['creation_time']."&is_required_course=".$l['is_required_course']."&category_code=".$l['category_code']."&begin_date=".$l['begin_date']."&finish_date=".$l['finish_date']."&is_pass=".$l['is_pass']."&got_credit=".$l['got_credit']."&last_access_time=".$l['last_access_time']."&exam_score=".$l['exam_score']."&learning_status=".$l['learning_status']."&exam_status=".$l['exam_status']."&access_times=".$l['access_times']."&progress=".$l['progress'];

$ress="deviceid=".$dev_id."&user_id=".$userId."&sysid=".$sysid.$learn_infos;

 function WriteData($conn,$host,$data)
{
//           $header = "POST /lms/portal/sp/read.php HTTP/1.1\r\n";
    $header = "POST /backend/www/read_learn.php HTTP/1.1\r\n";    
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
    if (!$conn)
    {
        die("Con error");
    }
    //循环发送1次数据
   
    for($i = 0;$i<1;$i++)
    {
       WriteData($conn,$host,$data);
        
    }
   
    fclose($conn);
}
 
//判断联网状况
 $conn1 = fsockopen($cloudip,80);
 if($cloudip){
 if($conn1){
    if($u_status==5){
        if($dev_id){   
            Post($cloudip,80,$ress);
    //        Post('192.168.1.202',80,$ress); 
        }
    }
 }
 }
 

?>
