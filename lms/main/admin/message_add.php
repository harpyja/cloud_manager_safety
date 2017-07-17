<?php
$cidReset = true;
include_once ("../inc/global.inc.php");
//api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$htmlHeadXtra [] = Display::display_kindeditor ( 'content', 'normal' );

$form = new FormValidator ( 'task','POST','message_add.php','');
$form->addElement ( 'html', '<div style="margin-top:5px;"></div>');

//内容
$form->addElement ( 'textarea', 'content', get_lang ( 'Content' ), array ('id' => 'content', 'wrap' => 'virtual', 'class' => 'inputText', 'style' => 'width:100%;height:260px' ) );
$form->addRule ( 'content', get_lang ( 'ThisFieldIsRequired' ), 'required' );
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
    $date_start        = date ( 'Y-m-d H:i' );
    $content           =strip_tags( htmlspecialchars(addslashes( $labs['content'])));  

    $sqlu = 'select user_id from user';
    if($_SESSION['_user']['user_id']!==''){ 
        $u=$_SESSION['_user']['user_id'];
        $sqlu.="  where user_id  !='".$u."'";
    }
    $user_data= Database::get_into_array( $sqlu, __FILE__, __LINE__ ); 
    
    foreach ($user_data as $recipient){
        $sql="INSERT INTO message (created_user,date_start,content,status,recipient) VALUES ('".$created_user."','".$date_start."','".$content."','0','".$recipient."')";
        $result = api_sql_query ( $sql, __FILE__, __LINE__ );
        // echo $sql.'&nbsp;'.$result.'<br>';
    }
   tb_close ( 'message_list.php' );

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();
