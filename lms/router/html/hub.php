<?php

require_once ('../../main/inc/global.inc.php');
 
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
function create_run_device(){
    if(mysql_num_rows(mysql_query("SHOW TABLES LIKE labs_run_devices"))!=1){
        $sql_insert ="CREATE TABLE IF NOT EXISTS `labs_run_devices` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `course_name` varchar(256) CHARACTER SET utf8 NOT NULL,
            `labs_name` varchar(32) CHARACTER SET utf8 NOT NULL,
            `p_id` int(11) NOT NULL,
            `USERID` int(11) NOT NULL,
            `GROUPID` int(11),
            `LEADID` int(11),
            `PORT` int(11) NOT NULL,
            `DEVICEID` varchar(256) NOT NULL,
            `DEVICEDNAME` varchar(256) NOT NULL,
            `ROUTETYPE` varchar(256) NOT NULL,
            `ROUTEMOD` varchar(256) NOT NULL,
            `DEVICEDTYPE` varchar(256) NOT NULL,
            `status` int(11) NOT NULL,
            `uport`  varchar(256) CHARACTER SET utf8 NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MEMORY DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
        api_sql_query ( $sql_insert,__FILE__, __LINE__ );
    }
}

function  hub($action,$hub_type,$values){
        $sql= "select ranges, parameter from  token_bucket where token_bucket_name='".$hub_type."'";
        $query=api_sql_query($sql,__FILE__,__LINE__);

        $token_bucket=array();
        while( $token_bucket = Database::fetch_row ($query)){
            $token_buckets[]=$token_bucket;
        }

        $ranges=unserialize($token_buckets[0][0]);
 
        if($action=='add'){

            $to =  api_sql_query ("CREATE TABLE if not exists `vslab`.`$hub_type` ("
                . "`Pid` INT NOT NULL AUTO_INCREMENT ,"
                . "`status`SMALLINT  NOT NULL ,"
                . "`values`varchar(256)  NOT NULL ,"
                . " PRIMARY KEY ( `pid` )"
                . ") ENGINE = MEMORY auto_increment=0 charset=utf8;",__FILE__,__LINE__);
            $to_sql="select count(*) from $hub_type";
            $to_count=DATABASE::getval($to_sql,__FILE__,__LINE__);

            if($to_count==0){

                    for($i =$ranges[0] ;$i < $ranges[1];$i++)
                    {
                        $ins = "INSERT INTO  $hub_type (`Pid`,`status`,`values`) values(".$i.",0,0);";
                        api_sql_query ( $ins, __FILE__, __LINE__ );

                    }
            }

                $selectRand ="select Pid from  $hub_type  where Pid>= ".$ranges[0]." and  Pid<=".$ranges[1]." and status=0 order by rand() limit 1";

                $resault = api_sql_query ($selectRand, __FILE__,__LINE__);
                $hubrut = Database::fetch_row ( $resault);
                $rand = array_rand($hubrut[0],1);
                $sql_data = array ( 'status' => 1 );
                $sql = Database::sql_update ( $hub_type, $sql_data ,"pid='$rand[0]'");
                api_sql_query ( $sql, __FILE__, __LINE__ );
                if(isset($values) && $values!==''){
                    $values_sql =  "UPDATE `".$hub_type."` SET `values`= ".$values." WHERE pid='$rand'";
                    api_sql_query ( $values_sql, __FILE__, __LINE__ );
                }
            return  $hubrut[0];
            $port=$hubrut[0];

//=============================================================================================================
//parameter
            $token_buckets_parameter=explode(',',$token_buckets[0][1]);
            for($i=0;$i<=count($token_buckets_parameter)-1;$i++){
                $hub_1='';
                $hub_1.="`$token_buckets_parameter[$i]` varchar(256)  NOT NULL ,\n";
            }

            $token_bucket_sql="CREATE TABLE if not exists `vslab`.`$hub_type` (`id` INT NOT NULL AUTO_INCREMENT ,$hub_1 `port` INT  NOT NULL,PRIMARY KEY ( `id` )) ENGINE = MEMORY auto_increment=1 charset=utf8;";

            $sql_show= "show tables like '".$hub_type."'";
            $query_show=api_sql_query($sql_show,__FILE__,__LINE__);
            $hub_show = Database::num_rows($query_show);
            if($hub_show!=1){//create
                $result_insert = api_sql_query (  $token_bucket_sql, __FILE__, __LINE__ );
            }
        }
}


function delete_port($action,$hub_type,$portId){
    if($hub_type!=='' && $action=='delete' && $portId!==''){
        $delete_port_sql="UPDATE  `vslab`.`".$hub_type."` SET  `status` =  '0',`values`='0' WHERE  `Pid` =".$portId;
        $api=api_sql_query($delete_port_sql,__FILE__,__LINE__);

        if($api==1){
            //return $portId.'删除port成功！';
        }
    }

}
function netmap_str($ids,$USERID,$names){
    $sql_n="select `netmap` from `labs_labs` where id=".$ids;
    $netmap= DATABASE::getval($sql_n,__FILE__,__LINE__);
    $netmap1 = str_replace(" ", "_", $netmap);
    $netmap2 =  str_replace("\r\n", "_", $netmap1);
    $netmap3 =  str_replace(":", "A", $netmap2);
    $netmap4 =  str_replace("/", "B", $netmap3);
    $netmap4 =  str_replace("\n", "_", $netmap4);
    $netmap5=explode('_',$netmap4);
    $i=1;

    $ehub='';
    foreach($netmap5 as $net){
        $uport= hub('add','uporthub','1');
        if($i%2==0){
            $ehub.= $net."C".$uport."__";
        }else{
            $ehub.= $net."C".$uport."_";
        }
        $i++;

        $sql="select `uport` from `labs_run_devices` WHERE  `USERID` ='".$USERID."' and  `labs_name`='".$names."'  limit 0,1";
       // $uports=DATABASE::getval($sql,__FILE__,__LINE__);
        $ress = api_sql_query ( $sql, __FILE__, __LINE__ );
        $vms= array ();
        while ( $vms = Database::fetch_row ( $ress) ) {
            $vmss [] = $vms;
        }
        $uportz='';
        foreach ( $vmss as $k1 => $v1){

            if($v1!==''){
                $uportz.=$v1.";".$uport;
            }
            $update_uport_sql="UPDATE  `vslab`.`uporthub` SET  `status` =  '1', `values`='".$USERID."_".$names."_".$DEVICEDNAME."' WHERE  `Pid` ='".$uport."'";
            api_sql_query($update_uport_sql,__FILE__,__LINE__);

            $uport_sql="UPDATE  `vslab`.`labs_run_devices` SET  `uport` = '".$uportz."'  WHERE  `USERID` ='".$USERID."' and  `labs_name`='".$names."'";
            api_sql_query($uport_sql,__FILE__,__LINE__);
        }

    }
     return $ehub;
}

function run_labs($USERID,$ids,$names){

    create_run_device();
    $sql_n="select `netmap` from `labs_labs` where id=".$ids;
    $netmap= DATABASE::getval($sql_n,__FILE__,__LINE__);
    $sqlname="select `name` from `labs_labs` where id=".$ids;
    $LABSS= DATABASE::getval($sqlname,__FILE__,__LINE__);
    $netmap1 = str_replace(" ", "_", $netmap);
    $netmap2 =  str_replace("\r\n", "_", $netmap1);
    $netmap3 =  str_replace(":", "A", $netmap2);
    $netmap4 =  str_replace("/", "B", $netmap3);
    $netmap4 =  str_replace("\n", "_", $netmap4);
    $netmap5=explode('_',$netmap4);
    $i=1;
    $ehub='';
    $uporta='';
    foreach($netmap5 as $net){
        $uport= hub('add','uporthub','1');
        if($i%2==0){
            $ehub.= $net."C".$uport."__";
        }else{
            $ehub.= $net."C".$uport."_";
        }
        $i++;
	if($uporta==''){
	    $uporta.= $uport;
	}else{
	   $uporta.=';'.$uport;
	}
	$deviceid=explode('A',$net);
	$deviceidnet=$deviceid[0];	
	$deviceidnetmod=$deviceid[1];	
        $update_uport_sql="UPDATE  `vslab`.`uporthub` SET  `status` =  '1', `values`='".$USERID."_".$names."_".$deviceidnet."_$deviceidnetmod' WHERE  `Pid` ='".$uport."'";
        api_sql_query($update_uport_sql,__FILE__,__LINE__);
    }
    $LINKDATA=$ehub;


        $categorys =array();
        $sql = "select * FROM  `labs_devices` where `lab_id`='".$names."'";
        $ress = api_sql_query ( $sql, __FILE__, __LINE__ );
        $vms= array ();
        while ( $vms = Database::fetch_row ( $ress) ) {
            $vmss [] = $vms;
        }
        foreach ( $vmss as $k1 => $v1){

            foreach($v1 as $k2 => $v2){
                $categorys[]  = $v2;
            }
            $DEVICEID=$vmss[$k1][0];
            $DEVICEDNAME=$vmss[$k1][2];
            $ROUTETYPE=$vmss[$k1][3];
            $DEVICEDTYPE=$vmss[$k1][9];
            $slots=$vmss[$k1][8];
            $slot=str_replace(";","__",$slots);
            $ios=$vmss[$k1][3];
            $sql_i="select `filename`,`idle`,`type`,`ram`,`nvram` from `labs_ios` where `name`='".$ios."'";
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
            $PORT = hub('add','tporthub','1');
            $p_id='';

//echo $PORT;


            $sql_count="select count(*) from `vslab`.`labs_run_devices` where `labs_name`='".$names."' and `DEVICEID` ='".$DEVICEID."' and `USERID`='".$USERID."'";
            $devices=DATABASE::getval($sql_count,__FILE__,__LINE__);

            if($devices==0){
                $run_device_sql = "INSERT INTO `vslab`.`labs_run_devices` (`id`, `course_name`, `labs_name`, `p_id`, `USERID`, `PORT`, `DEVICEID`, `ROUTETYPE`, `ROUTEMOD`, `DEVICEDTYPE`,`DEVICEDNAME`,`status`) VALUES(NULL,'','".$names."','".$p_id."','".$USERID."','".$PORT."','".$DEVICEID."','".$ROUTETYPE."','".$ROUTEMOD."','".$DEVICEDTYPE."','".$DEVICEDNAME."','1');";
                api_sql_query($run_device_sql,__FILE__,__LINE__);
            }else{
                $delete_port_sql="UPDATE `vslab`.`labs_run_devices` SET  `p_id` =  '".$p_id."',`PORT`='".$PORT."',`ROUTETYPE`='".$ROUTETYPE."',`ROUTEMOD`='".$ROUTEMOD."',`DEVICEDTYPE`='".$DEVICEDTYPE."',`status`='1' WHERE `labs_name`='".$names."' and `DEVICEID` ='".$DEVICEID."' and `USERID`='".$USERID."'";
                api_sql_query($delete_port_sql,__FILE__,__LINE__);
            }

            $delete_port_sql="UPDATE  `vslab`.`tporthub` SET  `status` =  '1',`values`='".$USERID."_".$names."_".$DEVICEDNAME."' WHERE  `Pid` ='".$PORT."'";
            api_sql_query($delete_port_sql,__FILE__,__LINE__);

    	$sql_vmdisks="select `vmdisks` from `labs_devices` where `name`='".$DEVICEDNAME."' and `lab_id`='".$LABSS."'" ;
    $sql_systemmod= DATABASE::getval($sql_vmdisks,__FILE__,__LINE__);
            $command='sudo -u root /sbin/cloudvmroute.sh  system='.$sql_systemmod.'___LABSNAME='.$ids.'___USERID='.$USERID.'___PORT='.$PORT.'___DEVICEDTYPE='.$DEVICEDTYPE.'___DEVICEDNAME='.$DEVICEDNAME.'___DEVICEID='.$DEVICEID.'___ROUTETYPE='.$ROUTETYPE.'___IOSFILENAME='.$IOSFILENAME.'___ROUTEMOD='.$ROUTEMOD.'___MEM='.$MEM.'___NVRAM='.$NVRAM.'___idlepc='.$idlepc.'___LINKDATA='.$LINKDATA.'__slot='.$slot;
    		$command =  str_replace("____", "___", $command);

		//echo "$command";
		exec("echo $command '&' >> /tmp/www/$USERID'_'$ids'.sh' ; chmod 777 /tmp/www/$USERID'_'$ids'.sh' ");
		//exec("$command  >/dev/null &");
        }
    $uport_sql="UPDATE  `vslab`.`labs_run_devices` SET  `uport` = '".$uporta."'  WHERE  `USERID` ='".$USERID."' and  labs_name='".$names."'";
    api_sql_query($uport_sql,__FILE__,__LINE__);
}
