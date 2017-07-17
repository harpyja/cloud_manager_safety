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

$_SESSION['report_type']=htmlspecialchars($_GET ['report_type']);
$platform=$_SESSION['report_type'];

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

$form = new FormValidator ( 'labs_report','POST','report_edit.php?id='.$id,'');
$form->addElement ( 'text', 'report_name', "实验报告名称", array ('maxlength' => 30, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'report_name', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$count=Database::getval("select count(*) from `course`",__FILE__,__LINE__);
$sql = "select `title` FROM  `course` limit 0,$count ";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$vm= array ();
while ( $vm = Database::fetch_row ( $res) ) {
    $vms [] = $vm;
}
foreach ( $vms as $k1 => $v1){
    foreach($v1 as $k2 => $v2){
        $lab[$v2]  = $v2;
    }
}
if($platform!=3){
    $form->addElement ( 'select', 'course', "课程名称" , $lab, array ('id' => "course", 'style' => 'height:22px;') );
   // $form->addRule ( 'course', get_lang ( 'ThisFieldIsRequired' ), 'required' );
    $default['course']=$lab['1'];
}else{
    $form->addElement ( 'hidden', 'course', '');
}
   $form->addElement ( 'hidden', 'report_type',$platform);
$form->addElement ( 'file', 'screenshot_file', "文件", array ('style' => "width:30%", 'class' => 'inputText','id'=>'filename' ) );
$form->addElement ( 'textarea', 'description', '描述', array ('type'=>'textarea','rows'=>'8','cols'=>'60' ) );
$form->addElement ( 'text', 'key', "KEY", array ('maxlength' => 30, 'style' => "width:30%", 'class' => 'inputText' ) );

$group = array ();
//$group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit', '提交', 'class="add"' );
//if (! is_equal ( $_GET ['action'], 'edit' )) {
//   $group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit_plus', '保存', 'class="plus"' );
//}
$group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit', '保存', 'class="plus"' );

$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.location.reload();self.parent.tb_remove();' ) );
$form->addGroup ( $group, null, '&nbsp;', '&nbsp;&nbsp;' );
$form->applyFilter ( '__ALL__', 'trim' );

$get_action=  getgpc('action');
if (is_equal ( $get_action, 'edit' )) {

    if(isset($id)){
        $sql="select * from `vslab`.`report` where `id` = '".$id."'";

        $res = api_sql_query( $sql, __FILE__, __LINE__ );
        while($ss = Database::fetch_array ( $res )){
            $default = $ss;
        }
    }
}
$labs_report['screenshot_file']=$default['screenshot_file'];
$form->setDefaults ($default);
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '100%' );
if ($form->validate ()) {

    $labs_report = $form->getSubmitValues ();
    $user            = $_SESSION['_user']['username'];
    $report_name     = $labs_report['report_name'];
    $code            = $labs_report['course'];
    $description = $labs_report['description'];
     $key             = $labs_report['key'];
    //$status
    if (isset ( $user ['submit_plus'] )) {
        $status=0;
    } else {
        $status=1;
    }

    ini_set ( 'memory_limit', '256M' );
    ini_set ( 'max_execution_time', 1800 ); //设置执行时间

    //old file
    $filename_sql="select `screenshot_file` from `report` where `id`=".$id;
    $get_filename=Database::getval($filename_sql,__FILE__,__LINE__);

    //new file
    $tmp_name=$_FILES["screenshot_file"]["tmp_name"];
    $name=$_FILES["screenshot_file"]["name"];
    $path=URL_ROOT.'/www/lms/storage/report/'.$user;
    $file=$path."/".$name;
    if(!file_exists($path)){
        mkdir($path, 0777); //新建文件夹
        chmod($path, 0777); //修改文件夹权限
    }
    move_uploaded_file($tmp_name, $file);

    $labs_report['screenshot_file'] = $_FILES ['screenshot_file']['name'];
    $screenshot_file=$labs_report['screenshot_file'];

    if($labs_report['report_type']!=3){
        $code_var="`code` =  '".$code."',`type`=1,";
    }

    if($screenshot_file==''){
        $sql="UPDATE  `vslab`.`report` SET  `report_name` =  '".$report_name."',`user` =  '".$user."', ".$code_var."`status` =  ".$status.",  `description` =  '".$description."' , `key` =  '".$key."' WHERE  `report`.`id` ='".$id."'";
    }else{
        unlink($path.'/'.$get_filename);
        $sql="UPDATE  `vslab`.`report` SET  `report_name` =  '".$report_name."',`user` =  '".$user."', ".$code_var."`screenshot_file` =  '".$screenshot_file."', `status` =  ".$status.",  `description` =  '".$description."', `key` =  '".$key."' WHERE  `report`.`id` ='".$id."'";
    }
     
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
     tb_close ( 'labs_report.php' );
}
Display::display_header ( null, FALSE );
echo '<br>';
$form->display ();
Display::display_footer ();
