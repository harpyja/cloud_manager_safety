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
$table_labs_device = Database::get_main_table (labs_devices);

$labs_id=  intval(getgpc("id","G"));
if(isset( $_GET['action']) && $labs_id!=''){
$labs_id=Database::getval("select `name` from `labs_labs` where `id`=".$labs_id,__FILE__,__LINE__);
    $values['lab_id']=$labs_id;
}

$htmlHeadXtra [] = '
<script src="../syllabus/jquery.js" type="text/javascript"></script>
<script language="JavaScript" type="text/JavaScript">

//var name = "ios_name";

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


function getarea1(){
//当选中Server类型--->则型号只有pc-------Zdan
    var device_type=$("#device_type").val(); //获得设备类型
     if(device_type != ""){
          $.ajax({
              type: "post",
              url: "device_check.php",
              data:"device_type="+device_type,
              cache:false,
              beforeSend: function(XMLHttpRequest){
              },
              success: function(data, textStatus){
                $("#labs_device>table>tbody .containerBody #ios_nm").empty();//清空
                $("#labs_device>table>tbody .containerBody #ios_nm").append(data);//给下拉框添加option

                 },
              complete: function(XMLHttpRequest, textStatus){
              },
              error: function(){

              }
             });
  }

//当选中Server类型--->出现虚拟模板下拉框
    var device_type1=$("#device_type").val(); //获得设备类型
     if(device_type1 != ""){
          $.ajax({
              type: "post",
              url: "device_check.php",
              data:"device_type1="+device_type1,
              cache:false,
              beforeSend: function(XMLHttpRequest){
              },
              success: function(data1, textStatus){
                $("#labs_device>table>tbody .containerBody #lab_vm").empty();//清空
                $("#labs_device>table>tbody .containerBody #lab_vm").append(data1);//给下拉框添加option

                 },
              complete: function(XMLHttpRequest, textStatus){
              },
              error: function(){

              }
             });
  }
}

</script>';

$form = new FormValidator ( 'labs_device','POST','labs_device_add.php?action='.$labs_id,'');
$form->addElement ( 'html', '<div style="margin-top:2px;"></div>');
$form->addElement ( 'text', 'name', "名字", array ('maxlength' => 50, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'name', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );

$form->registerRule('name_only','function','check_name');
//$form->addRule('name','您输入的内容已存在，请重新输入', 'name_only');
function check_name($element_name, $element_value) {
    $table_labs_device = Database::get_main_table ( labs_devices );
    $sql="select name from $table_labs_device";
    $vmdisk_name=Database::get_into_array ( $sql );

    if (in_array($element_value,$vmdisk_name)) {
        return false;
    } else {
        return true;
    }
}
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
////array_push($ios,'请选择设备型号');
//arsort($ios);

$picture = array('cloud'=>'Cloud','desktop'=>'Desktop','framerelay'=>'Frame Relay Switch','l3switch'=>'L3 Switch','mpls'=>'MPLS Router','router'=>'Router','server'=>'Server','switch'=>'Switch');

$form->addElement ( 'select', 'lab_id', "实验拓扑", $lab,array ('maxlength' => 50, 'style' => "width:30%;height:22px;" ) );
$form->addElement ( 'select', 'picture', "设备类型", $picture,array ('id'=>"device_type",'maxlength' => 50, 'style' => "width:30%;height:22px;",'onChange'=>"getarea1()" ) );
$form->addElement('html','<tr id="lab_vm0" class="containerBody"><td class="formLabel">虚拟模板</td><td id ="lab_vm"></td></tr>');
//$form->addElement ( 'select', 'ios', "设备型号", $ios,array ('id' => "ios_name", 'style' => "width:30%;height:22px;",'onChange' => "getarea()" ) );
$form->addElement('html','<tr class="containerBody"><td class="formLabel">设备型号</td><td id ="ios_nm"></td></tr>');
$form->addElement('html','<tr class="containerBody"><td class="formLabel">模块设置</td><td id ="modules"></td></tr>');


//$form->addElement ( 'text', 'top', "上外边距", array ('maxlength' => 2, 'style' => "width:30%", 'class' => 'inputText' ) );
//$form->addElement ( 'text', 'left', "左外边距", array ('maxlength' => 2, 'style' => "width:30%", 'class' => 'inputText' ) );

$form->addElement('html','<tr class="containerBody"><td class="formLabel">上边距</td><td class="formTableTd" align="left"><input maxlength="2" style="width:30%" class="inputText" name="top" type="text">&nbsp;&nbsp;&nbsp;<span style="color:#999999"><i>(只能输入0-99的数字)</i></span></td></tr>');
$form->addRule ( 'top', '您输入的内容不是数字,请重新输入！', 'numeric' );

$form->addElement('html','<tr class="containerBody"><td class="formLabel">左边距</td><td class="formTableTd" align="left"><input maxlength="2" style="width:30%" class="inputText" name="left" type="text">&nbsp;&nbsp;&nbsp;<span style="color:#999999"><i>(只能输入0-99的数字)</i></span></td></tr>');
$form->addRule ( 'left','您输入的内容不是数字,请重新输入！', 'numeric' );

$form->addElement ( 'textarea', 'conf_id', "初始化配置", array ('style' => "width:65%;height:150px;",'class'=>"inputText") );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );
if(isset( $_GET['action'])=='device_add'){
    $form->freeze ( array ("lab_id" ) );
    $labs['lab_id'] = $values['lab_id'];
}

$form->setDefaults ( $values );
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );

if ($form->validate ()) {
    $labs  = $form->getSubmitValues ();
    $name    = $labs['name'];
    $lab_id  = $labs['lab_id'];
    $ios  = $labs['ios_name'];
    $vmdisks=$labs['vm_name'];
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
    if($labs['slot_number']!=='' && $labs['slot_number']!=='0'){
        for($i=0;$i<$labs['slot_number'];$i++){
            $slot='slot'.$i;
            if($labs[$slot]==''){
                $slots.='';
            }else{
                $slots.=$labs[$slot].';';
            }
        }
    }

    //$sql = "INSERT  INTO `vslab`.`labs_devices` (`name`,`lab_id`,`ios`,`slot`,`picture`,`conf_id`) VALUES ('".$name."','".$lab_id."','".$ios."','".$slots."','".$picture."','".$conf_id."')";
    $sql = "INSERT  INTO `vslab`.`labs_devices` (`name`,`lab_id`,`ios`,`vmdisks`,`slot`,`picture`,`conf_id`,`top`,`left`) VALUES ('".$name."','".$lab_id."','".$ios."','".$vmdisks."','".$slots."','".$picture."','".$conf_id."','".$top."','".$left."')";

    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

    if($result){ //设备添加成功后，修改网络拓扑表的netmap字段值-----zd
        $sql1="select id from labs_devices where name='".$name."' and lab_id='".$lab_id."'";
        $device_id=Database::getval ( $sql1, __FILE__, __LINE__ );   //获取设备的id

        $sql1="select netmap from labs_labs where name='".$lab_id."'";
        $netmap=Database::getval ( $sql1, __FILE__, __LINE__ );   //获取拓扑表中的netmap字段值

        $sql1="update labs_labs set netmap='".$netmap."\n".$device_id.":0/0' where name='".$lab_id."'";   //更新拓扑中的netmap
        $re=api_sql_query($sql1,__FILE__,__LINE__);

    }




    if(isset($_GET['action']) && $_GET['action']==''){
         tb_close ( 'labs_device.php');
    }else{
         tb_close ( 'labs_device.php?action=add_device&lab_name='.$lab_id );
    }
}

Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();
?>
