<?php
/**
 * User: chang
 * Date: 13-4-14
 * Time: 下午5:27
 * To change this template use File | Settings | File Templates.
 */
$cidReset = true;
include_once ("../inc/global.inc.php");
//api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");
$language_file = array ('admin', 'registration' );
$htmlHeadXtra [] = Display::display_kindeditor ( 'comment', 'normal' );

require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$_SESSION['id'] =  htmlspecialchars($_GET ['id']);
$id = $_SESSION['id'];

if(isset($id)){
    $sql="select * from  `vslab`.`reporting_info` where id = '".$id."'";

    $res = api_sql_query( $sql, __FILE__, __LINE__ );
    while($ss = Database::fetch_array ( $res )){
        $default = $ss;
    }
}

function credit_hours_range_check($inputValue) {
    return (intval ( $inputValue ) > 0);
}

function fee_check($inputValue) {
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

$form = new FormValidator ( 'report_edit','POST','reporting_edit.php?id='.$id,'');
//名称
$form->add_textfield ('report_name', "实验报告名称", false, array ('maxlength' => 50, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addElement ( 'text', 'screenshot_file', '提交文件', array ('type'=>'text'  ) );
$form->addElement ( 'text', 'submit_date', '提交时间', array ('type'=>'text' ) );
$form->addElement('text','description','描述',array('type'=>'text'));
$form->addElement('text','key','KEY',array('type'=>'text'));
$form->add_textfield ('score', "得分", false, array ('maxlength' => 50, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'score', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addElement ( 'textarea', 'comment', '教师评语', array ('type'=>'textarea','rows'=>'15','cols'=>'80' ) );

$group = array ();
$group [] = $form->createElement ( 'radio', 'return', null, '未通过', '0' );
$group [] = $form->createElement ( 'radio', 'return', null, '通过', '1');
$form->addGroup ( $group, 'return', '结果', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
$form->addRule ( 'return', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->freeze ( array ("report_name" ) );
$form->freeze ( array ("key" ) );
$form->freeze ( array ("screenshot_file" ) );
$form->freeze ( array ("description" ) );
$form->freeze ( array ("submit_date" ) );
$report['report_name'] = $default['report_name'];
if($default['screenshot_file']==''){
    $default['screenshot_file']='无';
}
$form->setDefaults ($default);
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {

    $report       = $form->getSubmitValues ();
    $report_name  = $report['report_name'];
    $score        = $report['score'];
    $comment      = $report['comment'];
    $return       = $report['return'];

    $sql = "UPDATE `reporting_info` SET `report_name`= '".$report_name."',`score`= '".$score."',`comment`= '".$comment."',`return`= '".$return."', `marking_status`=1 WHERE `id`='".$id."'";
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
    tb_close ();
}
Display::display_header ( $tool_name, FALSE );
$form->display ();