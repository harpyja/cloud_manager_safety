<?php

$cidReset = true;
include_once ("../../inc/global.inc.php");
header("content-type:text/html;charset=utf-8");

require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');


$_SESSION['id'] =  htmlspecialchars($_GET ['id']);
$id = $_SESSION['id'];

if(isset($id)){
    $sql="select * from group_user where id = '".$id."'";

    $res = api_sql_query( $sql, __FILE__, __LINE__ );
    while($ss = Database::fetch_array ( $res )){
        $default = $ss;
    }
}


$form = new FormValidator ( 'group_user','POST','user_group_add.php?id='.$id,'');
$form->addElement ( 'html', '<div style="margin-top:2px;"></div>');
$form->addElement ( 'text', 'name', "分组名称", array ('maxlength' => 50, 'style' => "width:50%;height:25px;", 'class' => 'inputText' ) );
$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'name', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );

/*$form->registerRule('name_only','function','check_name');
$form->addRule('name','您输入的内容已存在，请重新输入', 'name_only');*/
function check_name($element_name, $element_value) {
    $table_group_user = Database::get_main_table ( group_user);
    $sql="select name from $table_group_user";
    $vmdisk_name=Database::get_into_array ( $sql );

    if (in_array($element_value,$vmdisk_name)) {
        return false;
    } else {
        return true;
    }
}

$form->addElement ( 'textarea', 'description', "描述", array ('id' => 'description','class' => 'inputText','type'=>'textarea','style' => 'width:60%;height:150px' ) );

//小组类型
$group = array ();
$group [] = $form->createElement ( 'radio', 'type', null, '红方', 1 );
$group [] = $form->createElement ( 'radio', 'type', null, '蓝方', 2 );
$form->addGroup ( $group, null,'小组类型', null, false );
$default['type']=1;

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
    $type    = $labs['type'];
    $description    = $labs['description'];

    $sql_data = array (
        'name' => $name,
        'type' => $type,
        'description' => $description
    );
    $sql = Database::sql_insert ( group_user, $sql_data );
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

    tb_close ( 'control_user_group.php' );

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();
