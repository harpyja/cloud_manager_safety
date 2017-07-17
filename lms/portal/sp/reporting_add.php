<?php
$cidReset = true;
include_once ("inc/app.inc.php");
$user_id = api_get_user_id ();
//include_once ("inc/page_header.php");
$view_course_user = Database::get_main_table ( VIEW_COURSE_USER );
$report = Database::get_main_table (report);
//$htmlHeadXtra [] = Display::display_kindeditor ( 'description', 'normal' );
$t=  getgpc('type');$t=(int)$t;
if($t==1){
    $type='夺旗';
}if($t==2){
    $type='分组对抗';
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
$htmlHeadXtra [] = Display::display_kindeditor ( 'description', 'normal' );
//$form = new FormValidator ( 'labs_report','POST','report_add.php','');
$form = new FormValidator ( 'category_update' );
$form->addElement ( 'text', 'report_name', $type."报告名称", array ('maxlength' => 30, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'report_name', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$form->registerRule('name_only','function','check_name');
$form->addRule('report_name','您输入的内容已存在，请重新输入', 'name_only');
function check_name($element_name, $element_value) {
    $sql="select`report_name` FROM `vslab`.`reporting_info`";
    $report_name=Database::get_into_array ( $sql );

    if (in_array($element_value,$report_name)) {
        return false;
    } else {
        return true;
    }
}
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
//$form->addElement ( 'select', 'course', "课程名称" , $lab, array ('id' => "course", 'style' => 'height:22px;') );
//$form->addRule ( 'course', get_lang ( 'ThisFieldIsRequired' ), 'required' );
//$default['course']=$lab['1'];
$form->addElement('hidden','type',$t);

 $form->addElement ( 'file', 'screenshot_file', "报告", array ('style' => "width:30%", 'class' => 'inputText','id'=>'screenshot_file' ) );
// $form->addElement ( 'textarea', 'description', '描述', array ('type'=>'textarea','rows'=>'15','cols'=>'80' ) );
$form->addElement ( 'textarea', 'description', "描述", array ('type'=>'textarea','rows'=>'15','cols'=>'60' ) );
$form->addElement ( 'text', 'key', "KEY", array ('maxlength' => 30, 'style' => "width:30%", 'class' => 'inputText' ) );
//$group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit', '提交', 'class="add"' );
$group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit_plus', '保存', 'class="save"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.location.reload();self.parent.tb_remove();' ) );
$form->addGroup ( $group, null, '&nbsp;', '&nbsp;&nbsp;' );
$form->applyFilter ( '__ALL__', 'trim' );

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
$form->setDefaults ($default);
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '100%' );
if ($form->validate ()) {
    $labs_report = $form->getSubmitValues ();
    $user            = $_SESSION['_user']['username'];
    $report_name     = $labs_report['report_name'];
    $type            = $labs_report['type'];
    $description     = $labs_report['description'];
    $key             = $labs_report['key'];
    $description=strip_tags( htmlspecialchars(addslashes($description)));
 
    ini_set ( 'memory_limit', '256M' );
    ini_set ( 'max_execution_time', 1800 ); //设置执行时间

    $tmp_name=$_FILES["screenshot_file"]["tmp_name"];
    $name=$_FILES["screenshot_file"]["name"];

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
    $path=$report_file.'/'.$user;
    $file=$path."/".$name;
//create user file
    if(!file_exists($path)){
        mkdir($path, 0777);
        chmod($path, 0777);
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
//        'status'=>$status,
//        'type'=>$type,
//        'kkeys'=>$key
//    );
    $scrname=$_FILES["screenshot_file"]["name"];
 $sql="INSERT INTO reporting_info (report_name,user,screenshot_file,description,status,type,`key`)
     VALUES ('".$report_name."','".$user."','".$scrname."','".$description."','".$status."','".$type."','".$key."')";
    //  $sql = Database::sql_insert ( 'reporting_info', $sql_data );
      $result = api_sql_query ( $sql, __FILE__, __LINE__ );
      tb_close (  );
}
Display::display_header ( null, FALSE );
echo '<br>';
$form->display ();
Display::display_footer ();
