<?php
$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$form = new FormValidator ( 'task','POST','control_add.php','');
$form->addElement ( 'html', '<div style="margin-top:5px;"></div>');
//$form->addElement ( 'text', 'name', "任务名称", array ('maxlength' => 50, 'style' => "width:50%; ", 'class' => 'inputText' ) );

$sql="select id,name from renwu";
$result = api_sql_query ( $sql, __FILE__, __LINE__ );
$tbl_row = array ();
while ($row = Database::fetch_array ( $result, 'ASSOC' )){
    $name_list[$row['id']]=$row['name'];
}
$form->addElement ( 'select', 'name', get_lang ( '选择任务' ), $name_list, array ('style' => "width:30%;height:30px;" ) );
$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );


$sql="select id,name from group_user";
$result = api_sql_query ( $sql, __FILE__, __LINE__ );
$tbl_row = array ();
while ($row = Database::fetch_array ( $result, 'ASSOC' )){
    $exam_manager[$row['id']]=$row['name'];
}
$form->addElement ( 'select', 'group', get_lang ( '选择用户组' ), $exam_manager, array ('style' => "width:30%;height:30px;" ) );

//$form->addElement ( 'textarea', 'description', "任务描述", array ('id' => 'description','type'=>'textarea','style' => 'width:60%;height:150px','class' => 'inputText') );

//显，隐
$group = array ();
$group [] = $form->createElement ( 'radio', 'status', null, get_lang ( 'Visible1' ), 1 );
$group [] = $form->createElement ( 'radio', 'status', null, get_lang ( 'Invisible' ), 0 );
$form->addGroup ( $group, null, get_lang ( 'Visible' ), null, false );
$default['status']=0;

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ($default);
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {

    $labs           = $form->getSubmitValues ();
    $name           = $labs['name'];
//    $description    = $labs['description'];
    $group          = $labs['group'];
    $status         = $labs['status'];
    $red=array();
    $blue=array();
    $red_vm         = serialize($red);
    $blue_vm        = serialize($blue);


    $sql1 ="INSERT INTO `vslab`.`task` (`id`, `name`, `group`, `status`, `red_vm`, `blue_vm`) VALUES (NULL, '".$name."', '".$group."', '".$status."', '".$red_vm."', '".$blue_vm."')";
    $result = api_sql_query ( $sql1, __FILE__, __LINE__ );

    tb_close ( 'control_list.php' );

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();