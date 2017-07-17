<?php
header("content-type:text/html;charset=utf-8");
require_once ('../../main/inc/global.inc.php');
require_once ('../../router/html/hub.php');

require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
//require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$lessonId = $_SESSION['_cid'];
$vlanid =getgpc('vlanid','G');
if(!isset($lessonId)){
$lessonId =getgpc('cid','G');   
}
if(!$lessonId){
    header("Location:../../portal/sp/index.php");
}
$userId = $_SESSION['_user']['user_id'];
$system = getgpc('system','G'); 
$nicnum = getgpc('nicnum','G'); 

$vmStartInfo = Database::get_main_table (vmstartinfo);
$vmnum = Database::get_main_table (vmsummary);

$selectsql="system = '$system' and lesson_id = '$lessonId' and user_id = '$userId' ";

if(isset($_GET['manage'])  && $_GET['manage']){
    $selectsql.=" and manage=1";
}else{
    $selectvm = Database::sql_select($vmnum,"vm_num = (SELECT MIN( vm_num ) FROM  `vmsummary` )","addres");
    $selectvm = api_sql_query ( $selectvm, __FILE__, __LINE__ );
    $vmresult = Database::fetch_row ( $selectvm);
    $vmaddres_res = $vmresult[0];
    if(strpos($vmaddres_res,';') !== false){
       $vmaddress = explode(';',$vmaddres_res);
       $vmaddres = $vmaddress[0];
    }else{
       $vmaddres = $vmaddres_res;
    }

    $vm_num=DATABASE::getval("SELECT  `vm_num`   FROM  `vmsummary` where `addres`='".$vmaddres."'", __FILE__,__LINE__);
    $vmnumber=DATABASE::getval("SELECT `number` FROM  `vm_max_num`", __FILE__,__LINE__);
    if(!$vmnumber){
        $vmnumber=10;
    }
   if($vm_num > $vmnumber){
        echo "请注意，开启的虚拟机已经达到最大数量" ;
        exit();
   } 
}
$selectTotal = Database::sql_select("vmtotal",$selectsql,"addres,vmid,proxy_port");
$resault = api_sql_query ($selectTotal, __FILE__,__LINE__);


$vm= array (); 
while ( $vm = Database::fetch_row ( $resault) ) {
    $addres[]  = $vm[0];
    $vms[]= $vm[1];
    $proxy_port[] = $vm[2];
}
if(!$vms){
    $proxy_port=hub("add","proxyhub","");
}else{
    $proxy_port=$proxy_port[0];
}
 
$systems = explode('_',$system);
$name = $systems[0];
$vmdisk = Database::get_main_table (vmdisk);
$sql="select * from $vmdisk where name='".$name."'";

$res = api_sql_query( $sql, __FILE__, __LINE__ );
while($ss = Database::fetch_array ( $res )){
    $vmdisks = $ss;
}
$memory = $vmdisks['memory'];
$CPU_number = $vmdisks['CPU_number'];
$NIC_type = $vmdisks['NIC_type'];
$boot = $vmdisks['boot'];
$mac = $vmdisks['mac'];
$iso = $vmdisks['ISO'];

if($vmdisks['mac']==''){
    $mac = 1;
}else{
    $mac = $vmdisks['mac'];
}

if($vmdisks['ISO']==''){
    $iso = 1;
}else{
    $iso = $vmdisks['ISO'];
}
if($vmdisks['Ide']==''){  
	$Ide = 1; 
}else{
        $Ide = $vmdisks['Ide'];
}
    
if($vmdisks['Display']==''){
        $Display = "std";
    }else{
        $Display = $vmdisks['Display'];
}
$macstr='sudo -u root /sbin/cloudmac.sh';
exec("$macstr",$macinfo);
$mac=$macinfo[0];
//echo $mac;

$local_addres=$_SERVER['HTTP_HOST'];//curring addres

if(isset($_GET['manage'])  && $_GET['manage']){//houtai
    $lessonId ='111111111111';
    $manage='1';
    $vm_cont=Database::getval("select count(*) from `vmtotal` where `manage`='".$manage."' and `lesson_id`= '".$lessonId."' and `system`='".$system."'",__FILE__ , __LINE__);
    if(!$vm_cont){
	    $command='sudo -u root /usr/bin/ssh root@'.$local_addres.' /sbin/cloudvmstart.sh addres='.$local_addres.'___nicnum='.$nicnum.'___system='.$system.'___user_id='.$userId.'___lesson_id='.$lessonId.'___mem='.$memory.'___cpu_num='.$CPU_number.'___nic_type='.$NIC_type.'___boot='.$boot.'___iso='.$iso.'___vlanid='.$vlanid.'___mac='.$mac.'___Ide='.$Ide.'___Display='.$Display.'___manage=1';
	    exec("$command",$info);
	    $sidres = $info[0];
	    $pot = $sidres+100+5900;
	    $vmid = $sidres + 100;  
	    $ins = "INSERT INTO vmtotal values(null,'$local_addres','$nicnum','$system','$userId','$lessonId','$vmid','$pot','1','$mac' ,'$proxy_port','1');";
	    $r = api_sql_query ( $ins, __FILE__, __LINE__ );
	}else{
            $vm_data=api_sql_query_array_assoc("select  * from `vmtotal` where `manage`='".$manage."' and `lesson_id`= '".$lessonId."' and `system`='".$system."'",__FILE__,__LINE__);
	    
	    $vmaddres = $vm_data[0]['addres'];
	    $vmid = $vm_data[0]['id'];
	    $pot = $vmid+100+5900;
	}
        $command2='sudo -u root  /sbin/cloudvnc.sh addres='.$local_addres.'___port='.$pot.'___proxy_port='.$proxy_port.'___userId='.$userId.'___manage=1';
	exec("$command2 &");

	if($_GET['nomachine']){
	    echo '<script>window.close();</script>';
	}else{
						$pp="Location: http://$local_addres".URL_APPEDND."/main/admin/vmdisk/vmdisk_list.php";
						if($_GET ['keyword']=='输入搜索关键词'){
				        $_GET ['keyword']='';
				    }
				    if(isset($_GET['keyword'])  && $_GET ['keyword']!=='' ){
				    	$pp.="?keyword=".$_GET ['keyword'];
			    	}
            header($pp);
            
	    //header("Location: http://$vmaddres/$vmid.html");
	  // header("Location: http://$local_addres/lms/main/html5/cloudauto.php?lessonId=$lessonId&&host=$local_addres&port=$proxy_port&system=$system&manage=1");
	}
    
}else{//qiantai
	if(!$vms){
	    $command='sudo -u root /usr/bin/ssh root@'.$vmaddres.' /sbin/cloudvmstart.sh addres='.$vmaddres.'___nicnum='.$nicnum.'___system='.$system.'___user_id='.$userId.'___lesson_id='.$lessonId.'___mem='.$memory.'___cpu_num='.$CPU_number.'___nic_type='.$NIC_type.'___boot='.$boot.'___iso='.$iso.'___vlanid='.$vlanid.'___mac='.$mac.'___Ide='.$Ide.'___Display='.$Display;
	    exec("$command",$info);
	    $sidres = $info[0];
	    $pot = $sidres+100+5900;
	    $vmid = $sidres + 100;  
	    $ins = "INSERT INTO vmtotal values(null,'$vmaddres','$nicnum','$system','$userId','$lessonId','$vmid','$pot','1','$mac' ,'$proxy_port','0');";
	    $r = api_sql_query ( $ins, __FILE__, __LINE__ );
	}else{
	    $vmaddres = $addres[0];
	    $vmid = $vms[0];
	    $pot = $vmid+100+5900;
	}
        $command2='sudo -u root  /sbin/cloudvnc.sh addres='.$vmaddres.'___port='.$pot.'___proxy_port='.$proxy_port.'___userId='.$userId;
	exec("$command2 &");

	if($_GET['nomachine']){
	    echo '<script>window.close();</script>';
	}else{
	    //header("Location: http://$vmaddres/$vmid.html");
            $local_addresx = explode(':',$local_addres);
            $local_addresd = $local_addresx[0];
            header("Location: http://$local_addres".URL_APPEDND."/main/html5/auto.php?lessonId=$lessonId&host=$local_addresd&port=$proxy_port&system=$system");
        }
}	
exit;
?>