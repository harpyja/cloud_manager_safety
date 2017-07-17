<?php
/**
 * This is an edit router type page
 * @changzf
 * on 2013/11/20
 */

$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");

require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$table_labs_type = Database::get_main_table (labs_type);


$_SESSION['id'] =  intval(getgpc('id','G'));
$id = $_SESSION['id'];

if(isset($id)){
    $sql="select * from $table_labs_type where id = '".$id."'";

    $res = api_sql_query( $sql, __FILE__, __LINE__ );
    while($ss = Database::fetch_array ( $res )){
        $default = $ss;
    }
}
$form = new FormValidator ( 'labs_type','POST','router_type_edit.php?id='.$id,'');
$form->addElement ( 'html', '&nbsp;');
$form->addElement ( 'text', 'name', "名字", array ('maxlength' => 30, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );
 
$form->addElement ( 'textarea', 'desc', "描述", array ('maxlength' => 200,'style' => "width:400px;height:150px;",'class'=>"inputText") );


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
    $desc    = $labs['desc']; 
    $sql="UPDATE `vslab`.`labs_type` SET `name`= '".$name."',`desc`= '".$desc."'  WHERE id='".$id."'";
    $result = api_sql_query ( $sql, __FILE__, __LINE__ ); 
   tb_close ( 'router_type.php' );

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();
