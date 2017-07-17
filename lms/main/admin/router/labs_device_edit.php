<?php
/**
 * This is an add routing and switching page
 * @changzf
 * on 2013/01/10
 */

$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");

require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$table_labs_devices = Database::get_main_table (labs_devices);


$_SESSION['id'] = intval($_GET ['id']);
$id = $_SESSION['id'];

if(isset($id)){
    $sql="select * from $table_labs_devices where id = '".$id."'";

    $res = api_sql_query( $sql, __FILE__, __LINE__ );
    while($ss = Database::fetch_array ( $res )){
        $default = $ss;
    }
}
$default_top=$default['top'];
$default_left=$default['left'];

$default_slot=explode(';',$default['slot']);
$slot_number=count($default_slot);
$result='';
for($i=0;$i<=$slot_number;$i++){
    $result.='<td>slot'.$i.'</td><td><select id="slot'.$i.'" name="slot'.$i.'">'.$default_slot[$i].'</select></td>';
}

$sql="select `slot_number` from `labs_ios` where `name`='".$default['ios']."'";
$get_slotnumber=DATABASE::getval($sql,__FILE__,__LINE__);

//slot=0
$Sql_slot_0 = "select * from `labs_mod` where `type` like '%".$default['ios']."%' and slot0 =0";
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
$Sql_slot_1 = "select * from `labs_mod` where `type` like '%".$default['ios']."%' and slot0 =1";
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



$slot_default_value='';
for($i=0;$i<$get_slotnumber;$i++){
    if($area_option==''){
        $area_option=$area_option_0;
    }
    if($i==0)
    {
        $slot_default_value.='slot'.$i.'<select id="slot'.$i.'" name="slot'.$i.'">'.$area_option.'</select>&nbsp;';
    }else{
        $slot_default_value.='slot'.$i.'<select id="slot'.$i.'" name="slot'.$i.'">'.$area_option_0.'</select>&nbsp;';
    }

}



$htmlHeadXtra [] = '
<script src="../syllabus/jquery.js" type="text/javascript"></script>
<script language="JavaScript" type="text/JavaScript">

var name = "ios_name";
function getarea(){
 var region_id = $("#ios_name").val();//获得下拉框中大区域的值

 if(region_id != ""){
  $.ajax({
  type: "post",
  url: "device_check.php",
  data:"region_id="+region_id,
  cache:false,
  beforeSend: function(XMLHttpRequest){
  },
  success: function(data, textStatus){
    $("#labs_device>table>tbody .containerBody #modules").empty();//清空
    $("#labs_device>table>tbody .containerBody #modules").append(data);//给下拉框添加option
     },
  complete: function(XMLHttpRequest, textStatus){
  },
  error: function(){

  }
 });
 }

}
</script>';


$form = new FormValidator ( 'labs_device','POST','labs_device_edit.php?id='.$id,'');
$form->addElement ( 'html', '<div style="margin-top:2px;"></div>');
$form->addElement ( 'text', 'name', "名字", array ('maxlength' => 50, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'name', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );

$labs_ios = Database::get_main_table ( labs_labs );
$sql = "select name FROM  $labs_ios ";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$vm= array ();
while ( $vm = Database::fetch_row ( $res) ) {
    $vms [] = $vm;
}
foreach ( $vms as $k1 => $v1){
    foreach($v1 as $k2 => $v2){
        $lab[$v2]  = $v2;
    }
}

$sql = "select `name` FROM  `labs_ios` ";
$ress = api_sql_query ( $sql, __FILE__, __LINE__ );
$vms= array ();
while ( $vms = Database::fetch_row ( $ress) ) {
    $vmss [] = $vms;
}
foreach ( $vmss as $k1 => $v1){
    foreach($v1 as $k2 => $v2){
        $ios[$v2]  = $v2;
    }
}
//array_push($ios,'请选择操作系统');
arsort($ios);

$picture = array('cloud'=>'Cloud','desktop'=>'Desktop','framerelay'=>'Frame Relay Switch','l3switch'=>'L3 Switch','mpls'=>'MPLS Router','router'=>'Router','server'=>'Server','switch'=>'Switch');

$form->addElement ( 'select', 'lab_id', "实验拓扑", $lab,array ('maxlength' => 50, 'style' => "width:30%;height:22px;" ) );
$form->addElement ( 'select', 'ios', "设备型号", $ios,array ('id' => "ios_name", 'style' => "width:30%;height:22px;",'onChange' => "getarea()" ) );


$slots='<tr class="containerBody"><td class="formLabel">模块设置</td><td id ="modules">'.$slot_default_value.'</td></tr>';
$form->addElement('html',$slots);


$form->addElement ( 'select', 'picture', "设备类型", $picture,array ('maxlength' => 50, 'style' => "width:30%;height:22px;" ) );

$form->addElement('html','<tr class="containerBody"><td class="formLabel">上边距</td><td class="formTableTd" align="left"><input maxlength="2" style="width:30%" class="inputText" name="top" type="text" value="'.$default_top.'">&nbsp;&nbsp;&nbsp;<span style="color:#999999"><i>(只能输入0-99的数字)</i></span></td></tr>');
$form->addRule ( 'top', '您输入的内容不是数字,请重新输入！', 'numeric' );

$form->addElement('html','<tr class="containerBody"><td class="formLabel">左边距</td><td class="formTableTd" align="left"><input maxlength="2" style="width:30%" class="inputText" name="left" type="text" value="'.$default_left.'">&nbsp;&nbsp;&nbsp;<span style="color:#999999"><i>(只能输入0-99的数字)</i></span></td></tr>');
$form->addRule ( 'left','您输入的内容不是数字,请重新输入！', 'numeric' );

$form->addElement ( 'textarea', 'conf_id', "初始化配置", array ('style' => "width:65%;height:150px;",'class'=>"inputText") );


$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ($default);
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {

    $labs  = $form->getSubmitValues ();
    $name    = $labs['name'];
    $lab_id    = $labs['lab_id'];
    $ios  = $labs['ios'];
    $picture  = $labs['picture'];
    $conf_id  = $labs['conf_id'];
    $left           = $labs['left'];
    $top           = $labs['top'];

    if($top==0){
        $top=rand(20,80);
    }
    if($left==0){
        $left=rand(20,80);
    }

    $slots='';
    if(isset($labs['slot_number']) && $labs['slot_number']!=='' && $labs['slot_number']!=='0'){
        for($i=0;$i<$labs['slot_number'];$i++){
            $slot='slot'.$i;
            if($labs[$slot]==''){
                $slots.='';
            }else{
                $slots.=$labs[$slot].';';
            }
        }
    }else{
        $sql="select `slot_number` from `labs_ios` where `name`='".$labs['ios']."'";
        $get_slotnumber=DATABASE::getval($sql,__FILE__,__LINE__);

        for($i=0;$i<$get_slotnumber;$i++){
            $slot='slot'.$i;
            if($labs[$slot]==''){
                $slots.='';
            }else{
                $slots.=$labs[$slot].';';
            }
    }}

    $sql = "UPDATE  `vslab`.`labs_devices` SET  `name` =  '".$name."',
    `ios` =  '".$ios."',
    `lab_id` = '".$lab_id."',
    `slot` =  '".$slots."',
    `picture` =  '".$picture."',
    `conf_id` =  '".$conf_id."',
    `top` =  '".$top."',
    `left` =  '".$left."'
     where `labs_devices`.`id` =".$id.";";
     $result = api_sql_query ( $sql, __FILE__, __LINE__ );

     tb_close ( 'labs_device.php' );
}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();
