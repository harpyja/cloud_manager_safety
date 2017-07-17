<?php
header("content-type:text/html;charset=utf-8");

$language_file = array ('admin', 'registration' );
$cidReset = true;

require ('../../inc/global.inc.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');

//require_once (api_get_path ( LIBRARY_PATH ) . 'networkmap.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$code = $_POST["code"];//request.open中的url传参

if($_POST["region_id"]){

    $code =  intval(getgpc("region_id"));
    $courseSql = "select * from course where category_code = '$code'";
    $result = api_sql_query ( $courseSql, __FILE__, __LINE__ );
    $vm= array ();

    while ( $vm = Database::fetch_row ( $result) ) {

        $vms [] = $vm[2];
        $vs[] = $vm[0];
    }
    $array_ab=array_combine($vs,$vms);
   // var_dump($vms);

}
//$region_id = $_POST['region_id'];
foreach ($array_ab as $k => $v ){
    $area_option = "<option value='$v'>$v</option>";
    echo $area_option;
    //break;
}



?>