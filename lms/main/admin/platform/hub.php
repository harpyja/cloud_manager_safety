<?php
header("content-type:text/html;charset=utf-8");
require_once ('../../inc/global.inc.php');

require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');


$hub_type = htmlspecialchars($_GET['hub_type']);
$action = htmlspecialchars($_GET['action']);

$sql= "select ranges, parameter from  token_bucket where token_bucket_name='".$hub_type."'";
$query=api_sql_query($sql,__FILE__,__LINE__);

$token_bucket=array();
while( $token_bucket = Database::fetch_row ($query)){
    $token_buckets[]=$token_bucket;
}
//var_dump($token_buckets);

//ranges
$ranges=unserialize($token_buckets[0][0]);

if($action=='add'){
//hub
$to =  api_sql_query ("CREATE TABLE if not exists `vslab`.`hub` (\n"
    . "`Pid` INT NOT NULL AUTO_INCREMENT ,\n"
    . "`status`SMALLINT  NOT NULL ,\n"
    . " PRIMARY KEY ( `pid` )\n"
    . ") ENGINE = MEMORY auto_increment=0 charset=utf8;");
$hub = Database::fetch_row ( $to);

$sql = Database::sql_select("hub","Pid = 1024","Pid");
$select = api_sql_query ( $sql, __FILE__, __LINE__ );
$result = Database::fetch_row ( $select);
//var_dump($result);

if($result==false){
    for($i =1024 ;$i < 65535;$i++)
    {
        //$arr['$i'] = 0;
        $ins = "INSERT INTO hub values(null,'0');";
        $r = api_sql_query ( $ins, __FILE__, __LINE__ );
    }
}
$port = getgpc('port');
if($_GET['port']){
    $sql_data = array (
        'status' => 0
    );

    $sql = Database::sql_update ( "hub", $sql_data ,"Pid='$port'");
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
}else{
//   $selectRand = Database::sql_select("hub","status=0 order by rand() limit 1","Pid");
   $selectRand ="select Pid from `hub` where Pid>= ".$ranges[0]." and  Pid<=".$ranges[1]." and status=0 order by rand() limit 1";

    //echo $selectRand,"<br/>";
    $resault = api_sql_query ($selectRand, __FILE__,__LINE__);
    $hubrut = Database::fetch_row ( $resault);

    $rand = $hubrut[0];
    $sql_data = array ( 'status' => 1 );
    $sql = Database::sql_update ( "hub", $sql_data ,"pid='$rand'");

    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
}
echo "Port:".$hubrut[0]."<br>";
$port=$hubrut[0];

//=============================================================================================================
//parameter
$token_buckets_parameter=explode(',',$token_buckets[0][1]);
for($i=0;$i<=count($token_buckets_parameter)-1;$i++){
    $hub_1.="`$token_buckets_parameter[$i]` varchar(256)  NOT NULL ,\n";
}

$token_bucket_sql="CREATE TABLE if not exists `vslab`.`$hub_type` (`id` INT NOT NULL AUTO_INCREMENT ,$hub_1 `port` INT  NOT NULL,PRIMARY KEY ( `id` )) ENGINE = MEMORY auto_increment=1 charset=utf8;";

$sql_show= "show tables like '".$hub_type."'";
$query_show=api_sql_query($sql_show,__FILE__,__LINE__);
$hub_show = Database::num_rows($query_show);
if($hub_show!=1){//create
    $result_insert = api_sql_query (  $token_bucket_sql, __FILE__, __LINE__ );
}


if($hub_type=="porthub"){
    $porthub_data = array (
        'userid' => intval(htmlspecialchars($_GET ['userid'])),
        'lessionid' =>  intval(htmlspecialchars($_GET ['lessionid'])),
        'vmid' => intval(htmlspecialchars($_GET ['vmid'])),
        'port' => $hubrut[0],
    );
    $sql_insert = Database::sql_insert ( 'porthub', $porthub_data );
    echo $sql_insert;
    $result_insert = api_sql_query ( $sql_insert, __FILE__, __LINE__ );
}if($hub_type=="vlanhub"){
    $porthub_data = array (
        'userid' => intval(htmlspecialchars($_GET ['userid'])),
        'lessionid' =>  intval(htmlspecialchars($_GET ['lessionid'])),
        'vlan' => htmlspecialchars($_GET ['vlan']),
        'system' => htmlspecialchars($_GET ['system']),
        'interface' => htmlspecialchars($_GET ['interface']),
        'hper_interface' => htmlspecialchars($_GET ['hper_interface']),
        'port' => $hubrut[0],
    );
    $sql_insert = Database::sql_insert ( 'vlanhub', $porthub_data );
    echo $sql_insert;
    $result_insert = api_sql_query ( $sql_insert, __FILE__, __LINE__ );
}

}
//header("Location: http://$vmaddres/$vmid.html");
//exit;

?>

