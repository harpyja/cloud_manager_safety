<?php
header("content-type:text/html;charset=utf-8");

$language_file = array ('admin', 'registration' );
$cidReset = true;

require ('../../inc/global.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');


if($_POST["region_id"]){

    $id = $_POST["region_id"];

//slot_number
    $sql="select `slot_number` from `labs_ios` where `name`='".$id."'";
    $get_slotnumber=DATABASE::getval($sql,__FILE__,__LINE__);

//slot=0
    $Sql_slot_0 = "select * from `labs_mod` where `type` like '%".$id."%' and slot0 =0";
    $result_0 = api_sql_query ( $Sql_slot_0, __FILE__, __LINE__ );
    $vm_0= array ();
    while ( $vm_0 = Database::fetch_row ( $result_0) ) {
        $vms_0[] = $vm_0[1];
        $vs_0[] = $vm_0[0];
    }
    $array_ab_0=array_combine($vs_0,$vms_0);
    $area_option_0 = "";
    foreach ($array_ab_0 as $k => $v ){
        $area_option_0 .= "<option value='$v'>$v</option>";
    }


//slot=1
    $Sql_slot_1 = "select * from `labs_mod` where `type` like '%".$id."%' and slot0 =1";
    $result_1 = api_sql_query ( $Sql_slot_1, __FILE__, __LINE__ );
    $vm_1= array ();
    while ( $vm_1 = Database::fetch_row ( $result_1) ) {
        $vms_1[] = $vm_1[1];
        $vs_1[] = $vm_1[0];
    }
    $array_ab=array_combine($vs_1,$vms_1);
    $area_option = "";
    foreach ($array_ab as $k => $v ){
        $area_option .= "<option value='$v'>$v</option>";
    }

    
     for($i=0;$i<$get_slotnumber;$i++){
        if($area_option==''){
            $area_option=$area_option_0;
        }
        if($i==0)
        {
            echo '<td>slot'.$i.'</td><td><select id="slot'.$i.'" name="slot'.$i.'">'.$area_option.'</select></td>';
        }else{
            echo '<td>slot'.$i.'</td><td><select id="slot'.$i.'" name="slot'.$i.'">'.$area_option_0.'</select></td>';
        }

    }

echo '<input type="hidden" value="'.$get_slotnumber.'" name="slot_number">';

}
 


//当选中Server类型--->则型号只有pc-------Zdan
if($_POST["device_type"]){
    $device_type = getgpc("device_type","P");
//echo $device_type;
    if($device_type=="server"){  //添加一个空的option，使得当只有pc一个选项时，能够触发onChange='getarea()'

            echo "<select id='ios_name' name='ios_name' onChange='getarea()' ><option value=''></option><option value='pc'>pc</option></select>";
     }
    else {
        $sql = "select `name` FROM  `labs_ios` ";
        $ress = api_sql_query ( $sql, __FILE__, __LINE__ );
        $vms= array ();
        while ( $vms = Database::fetch_row ( $ress) ) {
            $vmss [] = $vms;
        }
//print_r($vmss);
        foreach ( $vmss as $k1 => $v1){
            $ios0=$v1[0];
            $ios.="<option value='$ios0'>$ios0</option>";
        }
             echo "<select id='ios_name' name='ios_name' onChange='getarea()' > ".$ios." </select>";
    }

}

//当选中Server类型--->出现虚拟模板下拉框------Zdan
if($_POST["device_type1"]){
    $device_type1 = getgpc("device_type1","P");
//echo $device_type1;
    if($device_type1=="server"){

        $sql = "select `name` FROM  `vmdisk` ";
        $ress = api_sql_query ( $sql, __FILE__, __LINE__ );
        $vms= array ();
        while ( $vms = Database::fetch_row ( $ress) ) {
            $vmss [] = $vms;
        }
//print_r($vmss);
        foreach ( $vmss as $k1 => $v1){
            $vm0=$v1[0];
            $vm.="<option value='$vm0'>$vm0</option>";
        }
        echo "<select id='vm_name' name='vm_name' > ".$vm." </select>";
    }
    else {
        echo "";
    }

}


?>