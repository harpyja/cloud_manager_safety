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


		$("#underlyingMirror").click(function(){
			if($("#underlyingMirror").attr("checked")){
				$("tr.containerBody:eq(2)").hide();
			}
		});

        $("#incrementalMirror").click(function(){
			if($("#incrementalMirror").attr("checked")){
			 $("tr.containerBody:eq(2)").show();
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
$form->addRule ( 'name', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );
//changzf on 2012/07/20
$form->registerRule('name_only','function','check_name');
$form->addRule('name','您输入的内容已存在，请重新输入', 'name_only');
//镜像
$group = array ();
$group [] = $form->createElement ( 'radio', 'mirror', null, '基础镜像', '1' ,array('id' => 'underlyingMirror','onclick'=>'check1()'));
$group [] = $form->createElement ( 'radio', 'mirror', null, '增量镜像', '2',array('id' => 'incrementalMirror','onclick'=>'check2()'));
$form->addGroup ( $group, 'mirror', '镜像', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
$sql = "select `name` from `vmdisk` ";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$vm= array ();
while ( $vm = Database::fetch_row ( $res) ) {
    $vms [] = $vm[0];
}
$design = array_combine($vms,$vms);

$form->addElement ( 'select', 'CD_mirror',"选择增量镜像", $design, array ('id' => "selectMirror", 'style' => 'height:22px;' ) );
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
$form->addElement ( 'text', 'size', "大小(M)", array ('maxlength' => 20,'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'size', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'name', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );
$form->addElement ( 'text', 'version', "版本", array ('maxlength' => 20,'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'version', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'version', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );
$form->addElement ( 'text', 'memory', "内存(M)", array ('maxlength' => 20,'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'memory', get_lang ( '最大字符长度为30' ), 'maxlength', 10 );
$form->addElement ( 'text', 'CPU_number', "CPU数量", array ('maxlength' => 20,'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'CPU_number', get_lang ( '最大字符长度为30' ), 'maxlength', 10 );
$form->addRule ( 'mirror', get_lang ( 'ThisFieldIsRequired' ), 'required' );


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
$group = array ();
$group [] = $form->createElement ( 'radio', 'NIC_type', null, 'Intel', '1' );
$group [] = $form->createElement ( 'radio', 'NIC_type', null, 'Reltek', '2');
$form->addGroup ( $group, 'NIC_type', '网卡类型', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
$values ['NIC_type'] = 1;

//Mac
$form->addElement ( 'text', 'mac', "Mac地址", array ('maxlength' => 20,'style' => "width:150px", 'class' => 'inputText' ) );

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
