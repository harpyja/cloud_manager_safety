<?php
$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'renwu'"))!=1){
  $sql_insert ="CREATE TABLE IF NOT EXISTS `renwu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL DEFAULT ,
  `description` text NOT NULL  ,
  `red_group` varchar(256) DEFAULT NULL,
  `blue_group` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 " ;
  api_sql_query ( $sql_insert,__FILE__, __LINE__ );
}
$htmlHeadXtra [] = Display::display_kindeditor ( 'description', 'normal' );

$form = new FormValidator ( 'task','POST','renwu_add.php','');
$form->addElement ( 'html', '<div style="margin-top:5px;"></div>');
$form->addElement ( 'text', 'name', "任务名称", array ('maxlength' => 50, 'style' => "width:50%; ", 'class' => 'inputText' ) );
$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'name', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );

$form->registerRule('name_only','function','check_name');
$form->addRule('name','您输入的内容已存在，请重新输入', 'name_only');



function check_name($element_name, $element_value) {
    $table_group_user = Database::get_main_table ( renwu);
    $sql="select name from $table_group_user";
    $vmdisk_name=Database::get_into_array ( $sql );

    if (in_array($element_value,$vmdisk_name)) {
        return false;
    } else {
        return true;
    }
}

$form->addElement ( 'textarea', 'description', "任务描述", array ('id' => 'description','type'=>'textarea','style' => 'width:60%;height:150px','class' => 'inputText') );

//显，隐
//$group = array ();
//$group [] = $form->createElement ( 'radio', 'status', null, get_lang ( 'Visible1' ), 1 );
//$group [] = $form->createElement ( 'radio', 'status', null, get_lang ( 'Invisible' ), 0 );
//$form->addGroup ( $group, null, get_lang ( 'Visible' ), null, false );
//$default['status']=0;

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
    $description    = $labs['description'];
//    $group          = $labs['group'];
//    $status          = $labs['status'];


    $sql1 ="INSERT INTO `vslab`.`renwu` (`id`, `name`, `description`) VALUES (NULL, '".$name."','".$description."')";
    $result = api_sql_query ( $sql1, __FILE__, __LINE__ );

    tb_close ( 'renwu.php' );

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();