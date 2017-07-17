<?php
$cidReset = true;
include_once ("inc/app.inc.php");
$user_id = api_get_user_id ();
//include_once ("inc/page_header.php");
$view_course_user = Database::get_main_table ( VIEW_COURSE_USER );
$report = Database::get_main_table (report);
//$htmlHeadXtra [] = Display::display_kindeditor ( 'description', 'normal' );

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
//$form = new FormValidator ( 'labs_report','POST','report_add.php','');
$form = new FormValidator ( 'category_update' );
$form->addElement ( 'text', 'report_name', "实验报告名称", array ('maxlength' => 30, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'report_name', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$form->registerRule('name_only','function','check_name');
$form->addRule('report_name','您输入的内容已存在，请重新输入', 'name_only');
function check_name($element_name, $element_value) {
    $sql="select`report_name` FROM `vslab`.`report`";
    $report_name=Database::get_into_array ( $sql );

    if (in_array($element_value,$report_name)) {
        return false;
    } else {
        return true;
    }
}
$count=Database::getval("select count(*) from `course`",__FILE__,__LINE__);
$sql = "select `code`,`title` FROM  `course` limit 0,$count ";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$vm= array ();
while ( $vm = Database::fetch_row ( $res) ) {
    $c=$vm[0];
    $lab [$c] = $vm[1];
}
if($platform!=3){
$form->addElement ( 'select', 'course', "课程名称" , $lab, array ('id' => "course", 'style' => 'height:22px;') );
//$form->addRule ( 'course', get_lang ( 'ThisFieldIsRequired' ), 'required' );
}else{
    $form->addElement ( 'hidden', 'course', '' );  
}
$form->addElement ( 'hidden', 'report_type',$platform);

 $form->addElement ( 'file', 'screenshot_file', "文件", array ('style' => "width:30%", 'class' => 'inputText','id'=>'screenshot_file' ) );
 $form->addElement ( 'textarea', 'description', '描述', array ('type'=>'textarea','rows'=>'8','cols'=>'60' ) );
$form->addElement ( 'text', 'key', "KEY", array ('maxlength' => 30, 'style' => "width:30%", 'class' => 'inputText' ) );

$group = array ();
//$group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit', '提交', 'class="add"' );
$group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit_plus', '保存', 'class="save"' );
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

    ini_set ( 'memory_limit', '256M' );
    ini_set ( 'max_execution_time', 1800 ); //设置执行时间

    $tmp_name=$_FILES["screenshot_file"]["tmp_name"];
    $name=$_FILES["screenshot_file"]["name"];
 
    $path=URL_ROOT.'/www/lms/storage/report/'.$user;
    $file=$path."/".$name;
    if(!file_exists($path)){ 
         exec("mkdir -p ".$path); //创建$path1
                exec("chmod -R 777 ".URL_ROOT.'/www/lms/storage/report');//改权限
    }
      move_uploaded_file($tmp_name, $file);
    if (isset ( $user ['submit_plus'] )) {
        $status=0;
    } else {
        $status=1;
    }

//    $sql_data = array (
//        'report_name' => $report_name,
//        'user' => $user,
//        'screenshot_file' => $_FILES["screenshot_file"]["name"],
//        'description' => $description,
//        'status'=>$status
//    );
    if($labs_report['report_type']!=4){
        $code='';
    }else{
        $type = 1;
    }
     $scrname=$_FILES["screenshot_file"]["name"];
     $sql="INSERT INTO report (`report_name`,`user`,`code`,`screenshot_file`,`description`,`status`,`key`,`type`)
     VALUES ('".$report_name."','".$user."','".$code."','".$scrname."','".$description."','".$status."','".$key."','".$type."')";
   //   $sql = Database::sql_insert ( 'report', $sql_data );
      $result = api_sql_query ( $sql, __FILE__, __LINE__ );
 
      tb_close ( 'labs_report.php' );
}
Display::display_header ( null, FALSE );
echo '<br>';
$form->display ();
Display::display_footer ();
