<?php

$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");

require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$table_clouddesktop = Database::get_main_table (clouddesktop);
$table_clouddesktopscan = Database::get_main_table (clouddesktopscan);

$ids=getgpc('ids');
if(isset( $_GET['action']) && $ids!=''){
    $sql="select IP_address,physical_address from $table_clouddesktopscan where id = '".intval($ids)."'";
    $res = api_sql_query( $sql, __FILE__, __LINE__ );
    while($ss = Database::fetch_array ( $res )){
        $values = $ss;
    }
}

function myreaddir($dir) {
    $handle=opendir($dir);
    $i=0;
    while($file=readdir($handle)) {
        $file_type=explode('.',$file);
        if($file_type[1]=='img'){
            if (($file!=".")and($file!="..")) {
                $list[]=$file;
                $i=$i+1;
            }
        }
    }
    closedir($handle);
//var_dump($list);
    return $list;
}
$img=myreaddir("/tmp/mnt/pxe/");
foreach($img as $k=>$v){
    $type_i=explode('.',$v);
    if($type_i[1]=="img"){
        $imgs[$v]=$v;
    }
}
$form = new FormValidator ( 'clouddesktop_new','POST','clouddesktop_add.php','');

$form->addElement ( 'text', 'host_name', "主机名称", array ('maxlength' => 50, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'host_name', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );
$form->addRule ( 'host_name', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule('name_only','function','check_name');
$form->addRule('host_name','您输入的内容已存在，请重新输入', 'name_only');

function check_name($element, $element_value) {
    $sql="select `host_name` from 'clouddesktop'";
    $Host_name=Database::get_into_array ( $sql );

    if (in_array($element_value,$Host_name)) {
        return false;
    } else {
        return true;
    }
}

$form->addElement ( 'text', 'physical_address', "物理地址", array ('maxlength' => 50, 'style' => "width:30%", 'class' => 'inputText' ) );


$form->addElement ( 'text', 'IP_address', "分配的IP地址", array ('maxlength' => 50, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addElement ( 'select', 'cloud_mirror',"云桌面系统镜像", $imgs, array ('id' => "selectMirror", 'style' => 'height:22px;' ) );

$group = array ();
$group [] = $form->createElement ( 'radio', 'storage_space_type', null, '共享', '1' ,array('id' => 'underlyingMirror','onclick'=>'check1()'));
$group [] = $form->createElement ( 'radio', 'storage_space_type', null, '独占', '2',array('id' => 'incrementalMirror','onclick'=>'check2()'));
$form->addGroup ( $group, 'storage_space_type', '存储空间类型', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
$group = array ();
$group [] = $form->createElement ( 'radio', 'permissions', null, '只读', '1' ,array('id' => 'underlyingMirror','onclick'=>'check1()'));
$group [] = $form->createElement ( 'radio', 'permissions', null, '读写', '2',array('id' => 'incrementalMirror','onclick'=>'check2()'));
$form->addGroup ( $group, 'permissions', '读写权限', '&nbsp;&nbsp;&nbsp;&nbsp;', false );

$form->addElement ( 'text', 'group_name', "组名", array ('maxlength' => 20,'style' => "width:30%", 'class' => 'inputText' ) );
$form->addElement ( 'text', 'user_name', "使用人员名称", array ('maxlength' => 20,'style' => "width:30%", 'class' => 'inputText' ) );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

if(isset( $_GET['action']) && $ids!=''){
    $form->freeze ( array ("physical_address" ) );
    $clouddesktop['IP_address'] = $values['IP_address'];
    $clouddesktop['physical_address'] = $values['MAC_address'];
}
$form->setDefaults ( $values );
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {
//`id`, `host_name`, `physical_address`, `IP_address`, `cloud_mirror`, `storage_space_type`, `permissions` group_name user_name
    $clouddesktop  = $form->getSubmitValues ();

    $host_name    = $clouddesktop['host_name'];
    $IP_address   = $clouddesktop['IP_address'];
    $cloud_mirror = $clouddesktop['cloud_mirror'];
    $permissions  = $clouddesktop['permissions'];
    $group_name   = $clouddesktop['group_name'];
    $user_name    = $clouddesktop['user_name'];
    $storage_space_type= $clouddesktop['storage_space_type'];
    $physical_address  = $clouddesktop['physical_address'];


    $sql_data = array (
        'host_name ' => $host_name ,
        'physical_address' => $physical_address,
        'IP_address' => $IP_address,
        'cloud_mirror' => $cloud_mirror,
        'storage_space_type' => $storage_space_type,
        'permissions' => $permissions,
        'group_name' => $group_name,
        'user_name' => $user_name,
    );
    $sql = Database::sql_insert ( $table_clouddesktop, $sql_data );
    api_sql_query ( $sql, __FILE__, __LINE__ );

    exec("sudo -u root /sbin/clouddesktopadd.sh");

    if (isset ( $user ['submit_plus'] )) {
        api_redirect ( 'clouddesktop_add.php');
    } else {
        tb_close( 'clouddesktop.php' );
    }

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();