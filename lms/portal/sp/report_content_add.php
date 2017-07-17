<?php
$cidReset = true;
include_once ("inc/app.inc.php");
$user_id = api_get_user_id ();
$code=$_GET['cidReq'];//cidReq
$b=$_GET['b'];
$user=api_get_user_name ();//用户名称
$sql_class="select `title` from `course` where `code` = '$code' ";
$class=  Database::getval($sql_class,__FILE__, __LINE__ );  //课程名称 

$sql_id="select `id` from `report` where `user` = '$user' and `code` = '$class'";
$id=  Database::getval($sql_id,__FILE__, __LINE__ );  
$sql_content_old="select `content` from `report` where `id` = '$id' ";
$content_old=Database::getval($sql_content_old,__FILE__, __LINE__ );  
//
//$snapshot=Database::getval($sql_snapshot,__FILE__, __LINE__ ); 
//$res_snapshot = api_sql_query ( $snapshot, __FILE__, __LINE__ );
//$vm_snapshot= array ();
//while ( $vm_snapshot = Database::fetch_row ( $res_snapshot) ) {
//    var_dump($vm_snapshot);
//}

$view_course_user = Database::get_main_table ( VIEW_COURSE_USER );
$report = Database::get_main_table (report);
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
$form = new FormValidator ( 'category_update' );
function check_name($element_name, $element_value) {
    $sql="select`report_name` FROM `vslab`.`report`";
    $report_name=Database::get_into_array ( $sql );

    if (in_array($element_value,$report_name)) {
        return false;
    } else {
        return true;
    }
}
//$count=Database::getval("select count(*) from `course`",__FILE__,__LINE__);
$sql="select `id`,`filename`,`snapshotdesc` from `snapshot` where `lesson_id`='$code' and `type`= '1' and `user_id`= '$user_id'";
//$sql = "select `code`,`title` FROM  `course` limit 0,$count ";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$vm= array ();
while ( $vm = Database::fetch_row ( $res) ) {
    $c=$vm[1];
    $lab [$c] = $vm[1].'('.$vm[2].')';
}
if(count($lab)==0){
    $lab['000']="当前实验没有截图。";
}
$form->addElement ( 'hidden', 'report_id', $id);
$form->addElement ( 'hidden', 'cidReq', $code);
$form->addElement ( 'hidden', 'content_old', $content_old);
$form->addElement ( 'hidden', 'b', $b);
$form->addElement ( 'textarea', 'content', '试验内容与步骤', array ('type'=>'textarea','rows'=>'8','cols'=>'60' ) );
$form->addElement ( 'select', 'filesnap', "实验截图" , $lab, array ('id' => "filesnap", 'style' =>"width:30%" ) );
//$form->addElement ( 'file', 'screenshot_file', "文件", array ('style' => "width:30%", 'class' => 'inputText','id'=>'screenshot_file' ) );
$group = array ();
$group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit_plus', '保存', 'class="save"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.location.reload();self.parent.tb_remove();' ) );
$form->addGroup ( $group, null, '&nbsp;', '&nbsp;&nbsp;' );
$form->applyFilter ( '__ALL__', 'trim' );
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '100%' );
if ($form->validate ()) {
    $labs_report = $form->getSubmitValues ();
    $user            = $_SESSION['_user']['username'];
    $id         = $labs_report['report_id'];
    $b          = $labs_report['b'];
    $content    = $labs_report['content'];
    $cidReq     = $labs_report['cidReq'];
    $content_old= $labs_report['content_old'];
    $filesnap   = $labs_report['filesnap'];
    if($filesnap==000){
        unset ($filesnap);
    }
    $path=URL_ROOT.'/www/lms3/storage/report/'.$user;
    $file=$path."/".$name;
    if(!file_exists($path)){ 
        exec("mkdir -p ".$path); //创建$path1
        exec("chmod -R 777 ".URL_ROOT.'/www/lms3/storage/report');//改权限
    }
     $content=$content."_".$filesnap;
     if($content_old!=NULL){
         $content=$content_old.";".$content;
     }
     echo $content;
     $sql="UPDATE `report` SET  `content`= '".$content."' ,`type`='1'  WHERE `id`= ".$id ;
     $result = api_sql_query ( $sql, __FILE__, __LINE__ );
     if($b!=NULL){
         $url="report_test.php?cidReq=$cidReq&b=$b";
     }else{
         $url="report_test.php?cidReq=$cidReq";
     }
     
     tb_close ( $url );
}
Display::display_header ( null, FALSE );
$form->display ();
Display::display_footer ();
