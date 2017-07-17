<?php
/**
 * User: chang
 * Date: 13-4-14
 * Time: 下午5:27
 * To change this template use File | Settings | File Templates.
 */
$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");
$language_file = array ('admin', 'registration' );
//$htmlHeadXtra [] = Display::display_kindeditor ( 'description', 'normal' );
$htmlHeadXtra [] = Display::display_kindeditor ( 'comment', 'normal' );

//require_once ('../../../main/inc/global.inc.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$_SESSION['id'] =  htmlspecialchars($_GET ['id']);
$id = intval($_SESSION['id']);
$act=getgpc("action");
 
if(isset($id)){
    $sql="select * from  `vslab`.`setup` where id = '".$id."'";

    $res = api_sql_query( $sql, __FILE__, __LINE__ );
    while($ss = Database::fetch_array ( $res )){
        $default = $ss;
    }
}
 
//$t_sql ="select `title` from `vslab`.`course` where `code`=".$default['code'];
//$default['code']=Database::getval($t_sql,__FILE__,__LINE___);

function credit_hours_range_check($inputValue) {
    return (intval ( $inputValue ) > 0);
}

function fee_check($inputValue) {
    //`var_dump($inputValue);
    if (isset ( $inputValue ) && is_array ( $inputValue )) {
        if ($inputValue ['is_free'] == '0') {
            return floatval ( $inputValue ['payment'] ) > 0;
        } else {
            return true;
        }
    }
    return false;
}

function upload_max_filesize_check($inputValue) {
    return (intval ( $inputValue ) > 0 && intval ( $inputValue ) <= get_upload_max_filesize ( 0 ));
}

$form = new FormValidator ( 'setup_edit','POST','setup_edit.php?id='.$id,'');
 
$form->add_textfield ('title', "标题", false, array ('maxlength' => 50, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'title', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addElement ( 'textarea', 'description', '描述', array ('type'=>'textarea','rows'=>'15','cols'=>'80' ) );

 
$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

//$form->freeze ( array ("report_name" ) );
//$form->freeze ( array ("code" ) );
//$form->freeze ( array ("screenshot_file" ) );
////$form->freeze ( array ("description" ) );
//$form->freeze ( array ("submit_date" ) );
//$report['report_name'] = $default['report_name'];
//$report['code'] = $default['code'];
//if($default['screenshot_file']==''){
//    $default['screenshot_file']='无';
//}
//$report['screenshot_file'] = $default['screenshot_file'];
////$report['description'] = $default['description'];
//$report['submit_date'] = $default['submit_date'];

$form->setDefaults ($default);
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {

    $data      = $form->getSubmitValues ();
     
    $title  = $data['title'];
    $description        = $data['description'];
 
        $sql = "UPDATE `setup` SET `title`= '".$title."',`description`= '".$description."'  WHERE `id`='".$id."'";
         
        $result = api_sql_query ( $sql, __FILE__, __LINE__ );
        tb_close ( 'setup.php' );
 
}
Display::display_header ( $tool_name, FALSE );
$form->display ();