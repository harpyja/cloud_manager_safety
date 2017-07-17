<?php
$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$form = new FormValidator ( 'task','POST','auto_jz.php','');
$form->addElement ( 'text', 'name', "组的前缀", array ('maxlength' => 50, 'style' => "width:30%; ", 'class' => 'inputText' ) );
$form->addElement ( 'text', 'number', "组的数量", array ('maxlength' => 50, 'style' => "width:30%; ", 'class' => 'inputText' ) );
$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

if ($form->validate ()) {
    $sql_up="TRUNCATE TABLE `group_user`";
    api_sql_query($sql_up);
    $sql_update="UPDATE `vslab`.`user` SET `group_id` = '0',`type` = '0' WHERE `group_id`!= 0 or `type`!=0";
    api_sql_query($sql_update,__FILE__,__LINE__);
    $group           = $form->getSubmitValues ();
    $name           = $group['name'];
    $description    = $group['name'];
    $num            = $group['number'];
    for($i=1;$i<=$num;$i++){
        $a=$name;
        $names=$a."_".$i;
        $description=$a."_".$i;
        if($i%2==0){
            $type=1;
        }else{
            $type=2;
        }
        $sql1 ="INSERT INTO `vslab`.`group_user` (`id`, `name`, `description`,`type` ) VALUES (NULL, '".$names."','".$description."' ,'".$type."')";
        $result = api_sql_query ( $sql1, __FILE__, __LINE__ );

    }
    tb_close ( 'control_user_group.php' );
}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();