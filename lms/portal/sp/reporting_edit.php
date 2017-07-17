<?php
$cidReset = true;
include_once ("inc/app.inc.php");
$user_id = api_get_user_id ();
//include_once ("inc/page_header.php");
$view_course_user = Database::get_main_table ( VIEW_COURSE_USER );
$report = Database::get_main_table (report);
//$htmlHeadXtra [] = Display::display_kindeditor ( 'description', 'normal' );

$_SESSION['id'] =  htmlspecialchars($_GET ['id']);
$id = htmlspecialchars($_GET ['id']);

$get_action=  getgpc('action');
if (is_equal ( $get_action, 'edit' )) {

    if(isset($id)){
        $sql="select * from `vslab`.`reporting_info` where `id` = '".$id."'";

        $res = api_sql_query( $sql, __FILE__, __LINE__ );
        while($ss = Database::fetch_array ( $res )){
            $default = $ss;
        }
    }
}
if($default['type']==1){
    $t='夺旗';
}
if($default['type']==2){
    $t='分组对抗';
}
function credit_range_check($inputValue) {
    return (intval ( $inputValue ) > 0);
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

$form = new FormValidator ( 'reporting_info','POST','reporting_edit.php?id='.$id,'');
$form->addElement ( 'text', 'report_name',$t."报告名称", array ('maxlength' => 30, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'report_name', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$form->addElement ( 'file', 'screenshot_file', "报告", array ('style' => "width:30%", 'class' => 'inputText','id'=>'filename' ) );
$form->addElement ( 'textarea', 'description', '描述', array ('type'=>'textarea','rows'=>'8','cols'=>'60' ) );
//type
$form->addElement ( 'text', 'key', "KEY", array ('maxlength' => 30, 'style' => "width:30%", 'class' => 'inputText' ) );

$form->addElement('hidden','type',$t);

$group = array ();
//$group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit', '提交', 'class="add"' );
//if (! is_equal ( $_GET ['action'], 'edit' )) {
//   $group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit_plus', '保存', 'class="plus"' );
//}
$group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit', '保存', 'class="plus"' );

$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.location.reload();self.parent.tb_remove();' ) );
$form->addGroup ( $group, null, '&nbsp;', '&nbsp;&nbsp;' );
$form->applyFilter ( '__ALL__', 'trim' );

$labs_report['screenshot_file']=$default['screenshot_file'];
$form->setDefaults ($default);
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '100%' );
if ($form->validate ()) {

    $labs_report = $form->getSubmitValues ();
    $user            = $_SESSION['_user']['username'];
    $report_name     = $labs_report['report_name'];
    $type            = $labs_report['type'];
    $key             = $labs_report['key'];
    $description = $labs_report['description'];

    //$status
    if (isset ( $user ['submit_plus'] )) {
        $status=0;
    } else {
        $status=1;
    }

    ini_set ( 'memory_limit', '256M' );
    ini_set ( 'max_execution_time', 1800 ); //设置执行时间

//create report
    $reports_file=URL_ROOT.'/www/'.URL_APPEDND.'/storage/report';
    if(!file_exists($reports_file)){
        mkdir($reports_file, 0777);
        chmod($reports_file, 0777);
    }

    if($type==1){
        $report_file=$reports_file.'/flag';//夺旗报告
    }if($type==2){
        $report_file=$reports_file.'/counterwork';//分组对抗报告
    }
//create flag OR create counterwork
    if(!file_exists($report_file)){
        mkdir($report_file, 0777);
        chmod($report_file, 0777);
    }

    //old file
    $filename_sql="select `screenshot_file` from `reporting_info` where `id`=".$id;
    $get_filename=Database::getval($filename_sql,__FILE__,__LINE__);

    //new file
    $tmp_name=$_FILES["screenshot_file"]["tmp_name"];
    $name=$_FILES["screenshot_file"]["name"];

    $path=$report_file.'/'.$user;
    $file=$path."/".$name;
//create user file
    if(!file_exists($path)){
        mkdir($path, 0777);
        chmod($path, 0777);
    }
    move_uploaded_file($tmp_name, $file);

    $labs_report['screenshot_file'] = $_FILES ['screenshot_file']['name'];
    $screenshot_file=$labs_report['screenshot_file'];

    if($screenshot_file==''){
        $sql="UPDATE  `vslab`.`reporting_info` SET  `report_name` =  '".$report_name."',`user` =  '".$user."',  `status` =  ".$status.",  `description` =  '".$description."',  `key` =  '".$key."' WHERE  `reporting_info`.`id` ='".$id."'";
    }else{
        unlink($path.'/'.$get_filename);
        $sql="UPDATE  `vslab`.`reporting_info` SET  `report_name` =  '".$report_name."',`user` =  '".$user."',   `screenshot_file` =  '".$screenshot_file."', `status` =  ".$status.",  `description` =  '".$description."',  `key` =  '".$key."' WHERE  `reporting_info`.`id` ='".$id."'";
    }
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

    tb_close ();
}
Display::display_header ( null, FALSE );
echo '<br>';
$form->display ();
Display::display_footer ();
