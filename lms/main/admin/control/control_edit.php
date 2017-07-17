<?php

$cidReset = true;
include_once ("../../inc/global.inc.php");
header("content-type:text/html;charset=utf-8");
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
api_protect_admin_script ();

$_SESSION['id'] =  htmlspecialchars($_GET ['id']);
$id = $_SESSION['id'];

if(isset($id)){
    $sql="select * from task where id = '".$id."'";

    $res = api_sql_query( $sql, __FILE__, __LINE__ );
    while($ss = Database::fetch_array ( $res )){
        $default = $ss;
    }
}

$form = new FormValidator ( 'task','POST','control_edit.php?id='.$id,'');
$form->addElement ( 'html', '<div style="margin-top:2px;"></div>');

$sql="select id,name from renwu";
$result = api_sql_query ( $sql, __FILE__, __LINE__ );
$tbl_row = array ();
while ($row = Database::fetch_array ( $result, 'ASSOC' )){
    $name_list[$row['id']]=$row['name'];
}
$form->addElement ( 'select', 'name', get_lang ( '选择任务' ), $name_list, array ('style' => "width:30%;height:30px;" ) );
$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );

/*$form->registerRule('name_only','function','check_name');
$form->addRule('name','您输入的内容已存在，请重新输入', 'name_only');*/
function check_name($element_name, $element_value) {
    $table_task = Database::get_main_table ( task);
    $sql="select name from $table_task";
    $vmdisk_name=Database::get_into_array ( $sql );

    if (in_array($element_value,$vmdisk_name)) {
        return false;
    } else {
        return true;
    }
}
$sql="select id,name from group_user";
$result = api_sql_query ( $sql, __FILE__, __LINE__ );
$tbl_row = array ();
while ($row = Database::fetch_array ( $result, 'ASSOC' )){

    $exam_manager[$row['id']]=$row['name'];

}
$form->addElement ( 'select', 'group', get_lang ( '用户组' ), $exam_manager, array ('style' => "width:30%;height:30px;" ) );
//$form->addElement ( 'textarea', 'description', "任务描述", array ('id' => 'description','class' => 'inputText','type'=>'textarea','style' => 'width:60%;height:150px' ) );

//显，隐
$group = array ();
$group [] = $form->createElement ( 'radio', 'status', null, get_lang ( 'Visible1' ), 1 );
$group [] = $form->createElement ( 'radio', 'status', null, get_lang ( 'Invisible' ), 0 );
$form->addGroup ( $group, null, get_lang ( 'Visible' ), null, false );


$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );
//var_dump($default);
$cate_name_sql="select `name`from `task` where `id`=".$default['labs_category'];
$default['labs_category']=DATABASE::getval($cate_name_sql,__FILE__,__LINE__);
$form->freeze ( array ("labs_type" ) );
$form->setDefaults ($default);
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {

    $labs           = $form->getSubmitValues ();
    $name           = $labs['name'];
//    $description    = $labs['description'];
    $group          = $labs['group'];
    $status          = $labs['status'];
    $red=array();
    $blue=array();
    $red_vm         = serialize($red);
    $blue_vm        = serialize($blue);

    $devices_sql="UPDATE  `vslab`.`task` SET  `name` =  '".$name."',`group`='".$group."',`description`='".$description."',`status`='".$status."',`red_vm`='".$red_vm."',`blue_vm`='".$blue_vm."' WHERE  id=".$id;
    api_sql_query ( $devices_sql, __FILE__, __LINE__ );
    tb_close ( 'control_list.php' );

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();
