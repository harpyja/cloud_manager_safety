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
    $sql="select * from renwu where id = '".$id."'";

    $res = api_sql_query( $sql, __FILE__, __LINE__ );
    while($ss = Database::fetch_array ( $res )){
        $default = $ss;
    }
}
$htmlHeadXtra [] = Display::display_kindeditor ( 'description', 'normal' );

$form = new FormValidator ( 'task','POST','renwu_edit.php?id='.$id,'');
$form->addElement ( 'html', '<div style="margin-top:2px;"></div>');
$form->addElement ( 'text', 'name', "任务名称", array ('maxlength' => 50, 'style' => "width:50%;", 'class' => 'inputText' ) );
$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'name', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );

//$sql="select id,name from group_user";
//$result = api_sql_query ( $sql, __FILE__, __LINE__ );
//$tbl_row = array ();
//while ($row = Database::fetch_array ( $result, 'ASSOC' )){
//
//    $exam_manager[$row['id']]=$row['name'];
//
//}
//$form->addElement ( 'select', 'group', get_lang ( '用户组' ), $exam_manager, array ('style' => "width:15%" ) );
$form->addElement ( 'textarea', 'description', "任务描述", array ('id' => 'description','class' => 'inputText','type'=>'textarea','style' => 'width:60%;height:150px' ) );

//显，隐
//$group = array ();
//$group [] = $form->createElement ( 'radio', 'status', null, get_lang ( 'Visible1' ), 1 );
//$group [] = $form->createElement ( 'radio', 'status', null, get_lang ( 'Invisible' ), 0 );
//$form->addGroup ( $group, null, get_lang ( 'Visible' ), null, false );


$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );
//var_dump($default);
//$cate_name_sql="select `name`from `task` where `id`=".$default['labs_category'];
$default['labs_category']=DATABASE::getval($cate_name_sql,__FILE__,__LINE__);
$form->freeze ( array ("labs_type" ) );
$form->setDefaults ($default);
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {

    $labs           = $form->getSubmitValues ();
    $name           = $labs['name'];
    $description    = $labs['description'];
//    $group          = $labs['group'];
//    $status          = $labs['status'];



    $devices_sql="UPDATE  `vslab`.`renwu` SET  `name` =  '".$name."',`description`='".$description."' WHERE  id=".$id;
    api_sql_query ( $devices_sql, __FILE__, __LINE__ );
    tb_close ( 'renwu.php' );

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();