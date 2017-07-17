<?php
/**
 * This is an add router type page
 * @changzf
 * on 2013/01/10
 */
$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");

require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$table_labs_type = Database::get_main_table (labs_type);

$form = new FormValidator ( 'router_type','POST','router_type_add.php','');
$form->addElement ( 'html', '&nbsp;');
$form->addElement ( 'text', 'name', "名字", array ('maxlength' => 30, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$form->registerRule('name_only','function','check_name');
$form->addRule('name','您输入的内容已存在，请重新输入', 'name_only');
function check_name($element_name, $element_value) {
    $table_labs_type = Database::get_main_table ( labs_type );
    $sql="select name from $table_labs_type";
    $vmdisk_name=Database::get_into_array ( $sql );

    if (in_array($element_value,$vmdisk_name)) {
        return false;
    } else {
        return true;
    }
}  
$form->addElement ( 'textarea', 'desc', "描述", array ('maxlength' => 200,'style' => "width:400px;height:150px;",'class'=>"inputText") );
$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {

    $labs  = $form->getSubmitValues (); 
    $name    = $labs['name'];
    $desc    = $labs['desc'];
 
    $sql="INSERT INTO  `vslab`.`labs_type` ( `name` , `desc` ) VALUES (  '".$name."','".$desc."' )";
    $result = api_sql_query ( $sql, __FILE__, __LINE__ ); 
    tb_close ( 'router_type.php' );
}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();