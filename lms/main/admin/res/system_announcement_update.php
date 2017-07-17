<?php
$cidReset = true;
include_once ("../../inc/global.inc.php");
//api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");

require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$htmlHeadXtra [] = Display::display_kindeditor ( 'content', 'normal' );

$_SESSION['id'] =  htmlspecialchars($_GET ['id']);
$id = $_SESSION['id'];

if(isset($id)){
    $sql="select * from sys_announcement where id = '".$id."'";

    $res = api_sql_query( $sql, __FILE__, __LINE__ );
    while($ss = Database::fetch_array ( $res )){
        $default = $ss;
    }
}
$form = new FormValidator ( 'task','POST','system_announcement_update.php?id='.$id,'');
$form->addElement ( 'html', '<div style="margin-top:5px;"></div>');
$form->add_textfield ( 'title', get_lang ( 'Title' ), true, array ('style' => "width:80%", 'class' => 'inputText' ) );

//$form->addElement ( 'text', 'name', "任务名称", array ('maxlength' => 50, 'style' => "width:50%;height:25px", 'class' => 'inputText' ) );
$form->addRule ( 'title', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'title', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );

$form->registerRule('title_only','function','check_title');
$form->addRule('title','您输入的内容已存在，请重新输入', 'title_only');
function check_title($element_name, $element_value) {
    $table_task = Database::get_main_table ( task );
    $sql="select name from $table_task";
    $vmdisk_name=Database::get_into_array ( $sql );

    if (in_array($element_value,$vmdisk_name)) {
        return false;
    } else {
        return true;
    }
}

//$form->addElement( 'hidden','created_user', get_lang ( '发布者' ), true, array ('style' => "width:80%", 'class' => 'inputText','value'=>'$_session["_user"]["user_id"]' ) );
//$form->addElement ( 'calendar_datetime', 'date_start', get_lang ( "PublishTime" ), null, array ('show_time' => TRUE ) );

//内容
$form->addElement ( 'textarea', 'content', get_lang ( 'Content' ), array ('id' => 'description', 'wrap' => 'virtual', 'class' => 'inputText', 'style' => 'width:100%;height:260px' ) );

//显，隐
$group = array ();
$group [] = $form->createElement ( 'radio', 'visible', null, get_lang ( 'Visible1' ), 1 );
$group [] = $form->createElement ( 'radio', 'visible', null, get_lang ( 'Invisible' ), 0 );
$form->addGroup ( $group, null, get_lang ( 'Visible' ), null, false );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ($default);
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {

    $labs              = $form->getSubmitValues ();
    $created_user      = $_SESSION["_user"]["user_id"];
    $title              = $labs['title'];
    $visible       = $labs['visible'];
    $content           = $labs['content'];
  
    $sql_data = array (
        'title'         =>  $title,
        'visible'      =>  $visible,
        'created_user' =>  $created_user,
        'content'      =>  $content
    );
    $sql = Database::sql_update( 'sys_announcement', $sql_data,"id='$id'");
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

    tb_close ( 'system_announcements.php' );

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();