<?php
$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

//$htmlHeadXtra [] = Display::display_kindeditor ( 'content', 'normal' );

$_SESSION['id'] =  htmlspecialchars($_GET ['id']);
$id = $_SESSION['id'];

if(isset($id)){
    $sql="select * from flag where id = '".$id."'";

    $res = api_sql_query( $sql, __FILE__, __LINE__ );
    while($ss = Database::fetch_array ( $res )){
        $default = $ss;
    }
}
$form = new FormValidator ( 'flag','POST','flag_edit.php?id='.$id,'');
$form->addElement ( 'html', '<div style="margin-top:5px;"></div>');
$form->add_textfield ( 'title', get_lang ( 'Title' ), true, array ('style' => "width:80%", 'class' => 'inputText' ) );

$form->addRule ( 'title', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'title', get_lang ( '最大字符长度为30' ), 'maxlength', 50 );

$form->addElement ( 'textarea', 'content', '旗子位置描述', array ('id' => 'description', 'wrap' => 'virtual', 'class' => 'inputText', 'style' => 'width:100%;height:260px' ) );

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

    $flag              = $form->getSubmitValues ();
    $created_user      = $_SESSION["_user"]["user_id"];
    $title             = $flag['title'];
    $visible           = $flag['visible'];
    $content           = $flag['content'];

    $sql_data = array (
        'title'         =>  $title,
        'visible'      =>  $visible,
        'created_user' =>  $created_user,
        'content'      =>  $content
    );
    $sql = Database::sql_update( 'flag', $sql_data,"id='$id'");
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

    tb_close ( 'flag.php' );

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();