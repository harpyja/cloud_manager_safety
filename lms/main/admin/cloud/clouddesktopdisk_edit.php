<?php

$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");
$id=htmlspecialchars($_GET ['id']);

require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$table_clouddesktop = Database::get_main_table (img_upload);

if(isset($id)){
    $s="select img_file,storage_space_type from `img_upload` where id ='".$id."'";
    $res = api_sql_query( $s, __FILE__, __LINE__ );
    while($ss = Database::fetch_array ( $res )){
        $values = $ss;
    }

}



$form = new FormValidator ( 'clouddesktopdisk_new','POST','clouddesktopdisk_edit.php?id='.$id,'');

//$form->addElement ( 'text', 'host_name', "主机名称", array ('maxlength' => 50, 'style' => "width:30%", 'class' => 'inputText' ) );

$form->addElement ( 'text', 'img_file',"云桌面系统镜像",  array ('id' => "selectMirror",'class' => 'inputText', 'style' => 'height:22px;' ) );

$group = array ();
$group [] = $form->createElement ( 'radio', 'storage_space_type', null, '共享(ROMOS)', '1' ,array('id' => 'underlyingMirror','onclick'=>'check1()'));
$group [] = $form->createElement ( 'radio', 'storage_space_type', null, '独占(HDD)', '2',array('id' => 'incrementalMirror','onclick'=>'check2()'));
$form->addGroup ( $group, 'storage_space_type', '存储空间类型', '&nbsp;&nbsp;&nbsp;&nbsp;', false );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
//$group [] = $form->createElement ( 'style_submit_button', 'submit_plus', get_lang ( '确定并继续添加' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->freeze ( array ("img_file" ) );
$clouddesktopdisk['img_file'] = $values['img_file'];
$form->setDefaults ( $values );
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {
//`id`, `host_name`, `physical_address`, `IP_address`, `cloud_mirror`, `storage_space_type`, `permissions` group_name user_name
    $clouddesktopdisk  = $form->getSubmitValues ();

    $cloud_mirror = $clouddesktopdisk['cloud_mirror'];
    $storage_space_type= $clouddesktopdisk['storage_space_type'];


    $sql_data = array (
        'img_file' => $img_file,
        'storage_space_type' => $storage_space_type
    );


    $sql = Database::sql_update( $table_clouddesktop, $sql_data ,"id='$id'");
    api_sql_query ( $sql, __FILE__, __LINE__ );

    $typeSql = "UPDATE  `vslab`.`clouddesktop` SET  `storage_space_type` =  '".$storage_space_type."' WHERE  `clouddesktop`.`cloud_mirror` ='".$img_file."';";

    api_sql_query ( $typeSql, __FILE__, __LINE__ );

//    exec("sudo -u root /sbin/clouddesktopadd.sh");




    tb_close( 'clouddesktopdisk.php' );

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();