<?php
/**
 * This is a new virtual template page
 * @changzf
 * on 2012/06/15
 */

$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");

//require_once ('../../../main/inc/global.inc.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$table_vmdisk = Database::get_main_table (vmdisk);
$htmlHeadXtra [] = '<script type="text/javascript">
	$(document).ready( function() {
		$("tr.containerBody:eq(2)").hide();
		//$("tr.containerBody:eq(5)").hide();
$("#Flags").hide();

		$("#underlyingMirror").click(function(){
			if($("#underlyingMirror").attr("checked")){
				$("tr.containerBody:eq(2)").hide();
 $("#Flags").hide();
			}
		});

        $("#incrementalMirror").click(function(){
			if($("#incrementalMirror").attr("checked")){
			 $("tr.containerBody:eq(2)").show();
 $("#Flags").show();
			}
		});

	});

	</script>';
function credit_range_check($inputValue) {
    return (intval ( $inputValue ) > 0);
}

function credit_hours_range_check($inputValue) {
    return (intval ( $inputValue ) > 0);
}

function fee_check($inputValue) {
    //var_dump($inputValue);
    if (isset ( $inputValue ) && is_array ( $inputValue )) {
        if ($inputValue ['is_free'] == '0') {
            return floatval ( $inputValue ['payment'] ) > 0;
        } else {
            return true;
        }
    }
    return false;
}

function upload_max_filesize_check($inputValue) {
    return (intval ( $inputValue ) > 0 && intval ( $inputValue ) <= get_upload_max_filesize ( 0 ));
}
//循环文件夹下的文件夹和文件
function myreaddir($dir) {
    $handle=opendir($dir);
    $i=0;
    while($file=readdir($handle)) {
        if (($file!=".")and($file!="..")) {
            $list[$file]=$file;
            $i=$i+1;
        }
    }
    closedir($handle);

    return $list;
}
$form = new FormValidator ( 'vmdisk_new' );
//名称
$form->addElement ( 'text', 'name', "名字", array ('maxlength' => 50, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'name', get_lang ( '您输入的内容只能为字母或者数字'), 'alphanumeric' );
$form->addRule ( 'name', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );
//changzf on 2012/07/20
$form->registerRule('name_only','function','check_name');
$form->addRule('name','您输入的内容已存在，请重新输入', 'name_only');
//镜像
$group = array ();
$group [] = $form->createElement ( 'radio', 'mirror', null, '基础镜像', '1' ,array('id' => 'underlyingMirror','onclick'=>'check1()'));
$group [] = $form->createElement ( 'radio', 'mirror', null, '增量镜像', '2',array('id' => 'incrementalMirror','onclick'=>'check2()'));
$form->addGroup ( $group, 'mirror', '镜像', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
$sql = "select `name` from `vmdisk` where `CD_mirror`=''";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$vm= array ();
while ( $vm = Database::fetch_row ( $res) ) {
    $vms [] = $vm[0];
}
$design = array_combine($vms,$vms); 
 foreach ( $design as $v1){ 
            $ios.="<option value='$v1'>$v1</option>";
        }
$designs='<td style="border: 1px dotted #e1e1e1;text-align:right;background-color:#F7F7F7;">增量镜像</td><td><select id= "selectMirror" name= "CD_mirror" > '.$ios.'</td></select>';

$form->addElement ( 'text', 'mirror_keyword', "增量镜像关键字", array ('id'=>'mirror_keyword','maxlength' => 50, 'style' => "width:30%", 'class' => 'inputText','onkeyup'=>'mirrorCheck()' ) );
//$form->addElement ( 'select', 'CD_mirror',"选择增量镜像", $design, array ('id' => "selectMirror", 'style' => 'height:22px;' ) );
$form->addElement ( 'html', '<tr id="Flags">'.$designs.'</tr>');

$values ['CD_mirror'] = 1;
function check_name($element_name, $element_value) {
    $tbl_vmdisk = Database::get_main_table ( vmdisk );
    $sql="select name from $tbl_vmdisk";
    $vmdisk_name=Database::get_into_array ( $sql );

    if (in_array($element_value,$vmdisk_name)) {
        return false;
    } else {
        return true;
    }
}
//自定义编号
$form->addElement ( 'text', 'nodeId', "自定义编号", array ('style' => "width:250px", 'class' => 'inputText' ) );
$form->addRule ( 'nodeId', get_lang ( '最大字符长度为50' ), 'maxlength', 50 );
$sizes = array ();
$sizes[] = $form->createElement ( 'radio', 'size', null, '4G', '4096' );
$sizes[] = $form->createElement ( 'radio', 'size', null, '6G', '6144');
$sizes[] = $form->createElement ( 'radio', 'size', null, '8G', '8192' );
$sizes[] = $form->createElement ( 'radio', 'size', null, '16G', '16384');
$sizes[] = $form->createElement ( 'radio', 'size', null, '32G', '32768' );
$sizes[] = $form->createElement ( 'radio', 'size', null, '64G', '65536' );
$form->addGroup ( $sizes, 'size', '磁盘大小', '&nbsp;&nbsp;&nbsp;&nbsp;', false ); 
$values ['size'] = 4096;

//$form->addElement ( 'text', 'size', "磁盘大小", array ('maxlength' => 20,'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'size', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'name', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );
$form->addElement ( 'text', 'version', "版本", array ('maxlength' => 20,'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'version', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'version', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );

$device = "select * from device_type ";
$result = api_sql_query($device, __FILE__, __LINE__ );
while ( $rst = Database::fetch_row ( $result) ) {
    $ste [] = $rst;
}
foreach ( $ste as $k1 => $v1){
    foreach($v1 as $k2 => $v2){
        $dna[$k1][]  = $v2;
    }
}
$group = array ();
foreach($dna as $k1 => $v1){
$deviceName = $dna[$k1][1];
$group [] = $form->createElement ( 'radio', 'category', null, "$deviceName", "$deviceName" );

}
$values['category'] = 'route';
$form->addGroup ( $group, 'category', '设备类型', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
$form->addRule ( 'category', get_lang ( 'ThisFieldIsRequired' ), 'required' );
//$form->addElement ( 'text', 'memory', "内存(M)", array ('maxlength' => 20,'style' => "width:30%", 'class' => 'inputText' ) );
$memorys = array ();
$memorys[] = $form->createElement ( 'radio', 'memory', null, '128', '128' );
$memorys[] = $form->createElement ( 'radio', 'memory', null, '512', '512');
$memorys[] = $form->createElement ( 'radio', 'memory', null, '1024', '1024' );
$memorys[] = $form->createElement ( 'radio', 'memory', null, '2048', '2048');
$memorys[] = $form->createElement ( 'radio', 'memory', null, '4096', '4096' ); 
$form->addGroup ( $memorys, 'memory', '内存(M)', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
$values ['memory'] = 512;

//$form->addRule ( 'memory', get_lang ( '最大字符长度为30' ), 'maxlength', 10 );
//$form->addElement ( 'text', 'CPU_number', "CPU数量", array ('maxlength' => 20,'style' => "width:30%", 'class' => 'inputText' ) );
$CPUnumber = array ();
$CPUnumber[] = $form->createElement ( 'radio', 'CPU_number', null, '1个', '1' );
$CPUnumber[] = $form->createElement ( 'radio', 'CPU_number', null, '2个', '2');
$CPUnumber[] = $form->createElement ( 'radio', 'CPU_number', null, '4个', '4' );
$form->addGroup ( $CPUnumber, 'CPU_number', 'CPU数量', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
//$form->addRule ( 'CPU_number', get_lang ( '最大字符长度为30' ), 'maxlength', 10 );
$form->addRule ( 'mirror', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$values ['CPU_number'] = 1;
$group = array ();
$group [] = $form->createElement ( 'radio', 'NIC_type', null, 'Intel', '1' );
$group [] = $form->createElement ( 'radio', 'NIC_type', null, 'Reltek', '2');
$form->addGroup ( $group, 'NIC_type', '网卡类型', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
$values ['NIC_type'] = 1;

//Mac
$form->addElement ( 'text', 'mac', "Mac地址", array ('maxlength' => 20,'style' => "width:150px", 'class' => 'inputText' ) );

$group = array ();
$group [] = $form->createElement ( 'radio', 'platform', null, '渗透', '1' );
$group [] = $form->createElement ( 'radio', 'platform', null, '靶机', '2');
$group [] = $form->createElement ( 'radio', 'platform', null, '其他', '3');
$form->addGroup ( $group, 'platform', '平台类型', '&nbsp;&nbsp;&nbsp;&nbsp;', false );$values ['platform'] = 3;
$form->addElement ( 'text', 'vlan', "网卡接口数量", array ('maxlength' => 20,'style' => "width:150px", 'class' => 'inputText' ) );

//磁盘数量
//$Ide=array("1"=>"1","2"=>"2","3"=>"3","4"=>"4","5"=>"5","6"=>"6");
//$form->addElement ( 'select', 'Ide',"磁盘数量", $Ide, array ('id' => "selectMirror", 'style' => 'height:22px;width:150px;' ) );
$Ides = array ();
$Ides[] = $form->createElement ( 'radio', 'Ide', null, '1', '1' );
$Ides[] = $form->createElement ( 'radio', 'Ide', null, '2', '2');
$Ides[] = $form->createElement ( 'radio', 'Ide', null, '3', '3' );
$Ides[] = $form->createElement ( 'radio', 'Ide', null, '4', '4');
$Ides[] = $form->createElement ( 'radio', 'Ide', null, '5', '5' ); 
$Ides[] = $form->createElement ( 'radio', 'Ide', null, '6', '6' ); 
$form->addGroup ( $Ides, 'Ide', '磁盘数量', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
$values ['Ide'] = 1;

$Display = array ();
$Display[] = $form->createElement ( 'radio', 'Display', null, 'std', 'std' );
$Display[] = $form->createElement ( 'radio', 'Display', null, 'cirrus', 'cirrus');
$Display[] = $form->createElement ( 'radio', 'Display', null, 'vmware', 'vmware' );
$Display[] = $form->createElement ( 'radio', 'Display', null, 'qxl', 'qxl');
$Display[] = $form->createElement ( 'radio', 'Display', null, 'xenfb', 'xenfb' );
$form->addGroup ( $Display, 'Display', '显卡类型', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
$values['Display'] = "std";

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );
$form->setDefaults ( $values );
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {

    $vmdisk  = $form->getSubmitValues ();    
    $name    = $vmdisk['name'];
    $size    = $vmdisk['size'];
    $memory  = $vmdisk['memory'];
    $CPU_number    = $vmdisk['CPU_number'];
    $NIC_type    = $vmdisk['NIC_type'];
    $mirror  = $vmdisk['mirror'];
    $mac    = $vmdisk['mac'];
//    $nodeId=$vmdisk['nodeId'];//自定义编号
    $platform    = $vmdisk['platform'];
    $vlan        = $vmdisk['vlan'];
    
   if($vmdisk['Ide']==''){
        $Ide = 1;
    }else{
        $Ide = $vmdisk['Ide'];
    }
    
    if($vmdisk['Display']==''){
        $Displays = 'std';
    }else{
        $Displays = $vmdisk['Display'];
    }
    
    if ($mirror ==1){
        $CD_mirror    = '';
        exec("sudo -u root /usr/bin/qemu-img create -f qcow2 /tmp/mnt/vmdisk/images/99/$name.raw ".$size."M");
      //  exec("chmod  777  /tmp/mnt/vmdisk/images/99/$name.raw ");
    }elseif($mirror ==2){
        $CD_mirror    = $vmdisk['CD_mirror'];
        exec("sudo -u root /usr/bin/qemu-img create -b /tmp/mnt/vmdisk/images/99/$CD_mirror.raw -f qcow2 /tmp/mnt/vmdisk/images/99/$name.raw ".$size."M");
       // exec("chmod  777  /tmp/mnt/vmdisk/images/99/$name.raw ");
    }
    $type    = 1;
    $category= $vmdisk['category'];
    $version = $vmdisk['version'];
    $sql_data = array (
        'version' => $version,
        'name' => $name,
        'size' => $size,
        'category' => $category,
        'memory' => $memory,
        'CPU_number' => $CPU_number,
        'NIC_type' => $NIC_type,
        'type' => $type,
        'CD_mirror' => $CD_mirror,
        'mac'  => $mac,
//        'nodeId'=>$nodeId,//自定义编号
        'vlan'  => $vlan,
        'platform'  =>$platform,
        'Ide'  => $Ide,
        'Display'  =>$Displays
    );
    $sql = Database::sql_insert ( $table_vmdisk, $sql_data );
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
    if (isset ( $user ['submit_plus'] )) {
        api_redirect ( 'vmdisk_new.php');
    } else {
        tb_close ( 'vmdisk_list.php' );
    }
}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();


?>

<script type="text/javascript" src="../../../themes/js/jquery.js"></script>
<script type="text/javascript">
    function mirrorCheck(){ 
       var vm=$("#mirror_keyword").val(); 
        
          $.ajax({
              type: "post",
              url: "vm_check.php",
              data:"vm_name="+vm,  
              cache:false,
              beforeSend: function(XMLHttpRequest){
              },
              success: function(data){
                        $("#Flags").empty();//清空
                        $("#Flags").append(data);   //给下拉框添加option 
                        
                    
                 },
              complete: function(XMLHttpRequest, textStatus){
              },
              error: function(){

              }
             });
             
    }

 
</script>
