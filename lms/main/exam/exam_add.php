<?php
$language_file = 'admin';
$cidReset = true;

include_once ("../inc/global.inc.php");
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();
header("content-type:text/html;charset=utf-8");
$htmlHeadXtra [] = Display::display_kindeditor ( 'description', 'normal' );
$exam_type = Database::get_main_table (exam_type);

$form = new FormValidator ( 'examtype_new','POST','exam_add.php','');

$form->addElement ( 'text', 'name', "竞赛名称", array ('maxlength' => 50, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'name', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );
$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule('name_only','function','check_name');
$form->addRule('name','您输入的内容已存在，请重新输入', 'name_only');

function check_name( $element_value,$exam_type) {
    $sql="select `name` from ".$exam_type;
    $Host_name=Database::get_into_array ( $sql );
    if (in_array($element_value,$Host_name)) {
        return false;
    } else {
        return true;
    }
}
$form->addElement ( 'textarea', 'description', '描述', array ('type'=>'textarea','rows'=>'15','cols'=>'80' ) );

//显，隐
$group = array ();
$group [] = $form->createElement ( 'radio', 'enable', null, get_lang ( '开场' ), 1 );
$group [] = $form->createElement ( 'radio', 'enable', null, get_lang ( '结束' ), 0 );
$form->addGroup ( $group, null, get_lang ( '考场状态' ), null, false );
$values['enable']='1';

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ( $values );
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {
    $exam_list  = $form->getSubmitValues ();

    $name           = $exam_list['name'];
    $description    = $exam_list['description'];
    $enable         = $exam_list['enable'];

    $sql ="INSERT INTO exam_type (`name` ,`description`,`enable`) VALUES ('".$name."','".$description."','".$enable."')";
    api_sql_query ( $sql, __FILE__, __LINE__ );

    tb_close( 'exam_list.php' );

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();
