<?php
/**
 * Created by JetBrains PhpStorm.
 * User: chang
 * Date: 13-4-21
 * Time: 下午2:28
 * To change this template use File | Settings | File Templates.
 */
include_once ('exercise.class.php');
include_once ('question.class.php');
include_once ('answer.class.php');

$language_file = 'exercice';
include_once ('../../inc/global.inc.php');
include_once ('exercise.lib.php');

define ( "QUESTION_OPTION_SPLIT_CHAR", "|" );
set_time_limit ( 0 );
include (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include (api_get_path ( LIBRARY_PATH ) . 'export.lib.inc.php');

$alpha = array ('', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z' );

$form = new FormValidator ( 'export_report' );
$form->addElement ( 'hidden', 'type', getgpc ( 'type', 'G' ) );
// $form->addElement ( 'header', 'header', get_lang ( 'ExportQuestions' ) );
$form->addElement ( 'hidden', 'file_type', 'xls' );

$sql = "SELECT distinct `username` FROM `vslab`.`work_attendance`";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$vm= array ();
while ( $vm = Database::fetch_row ( $res) ) {
    $vms [] = $vm;
}
foreach ( $vms as $k1 => $v1){
    foreach($v1 as $k2 => $v2){
        $all_courses[$v2]  = $v2;
    }
}
$form->addElement ( 'select', 'username', '请选择用户', $all_courses,array('style'=>'width:30%;height:20px;') );

include (api_get_path ( INCLUDE_PATH ) . "conf/templates.php");

$group = array ();
$group [] = $form->createElement ( 'submit', 'submit', get_lang ( 'Ok' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ( $defaults );

Display::setTemplateBorder ( $form, '98%' );
if ($form->validate ()) {
    $export = $form->exportValues ();
    $export = $form->getSubmitValues ();
    $file_type = $export ['file_type'];
    $question_type = $export ['question_type'];
    //var_dump($question_type);exit;
    $username = trim ( $export ['username'] );
    $data = array ();

    $data [] = array ('编号', "用户帐号", "用户姓名","签到时间", '签退时间', "出勤状态", "上课时间", "结果" );
    $filename = 'ExportAttendance_' . date ( 'YmdHis' ); //导出文件名
    $sql = "select `id`,`username`,`name`,`sign_date`,`sign_return_date`,`range`,`mode`,`status` from `vslab`.`work_attendance` where `username`='".$username."'";
    // echo $sql;exit;
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $questions = array ();
    $line = 2;
    while ( $row = Database::fetch_array ( $res) ) {
       //  echo '<pre>';  var_dump($row);echo '</pre>';exit;
        $rows ['编号'] = $row['id'];
        $rows ['用户帐号'] = $row['username'];
        $rows ['用户姓名'] = $row['name'];
        $rows ["签到时间"] = $row ["sign_date"];
        $rows ["签退时间"] = $row ["sign_return_date"];


        //出勤状态
        if($row ["mode"]==1){
            $mode='签到成功';
        }elseif($row ["mode"]==2){
            $mode='签退成功';
        }else{
            $mode='旷课';
        }
        $rows ["出勤状态"] =$mode ;
        $rows ["上课时间"] = $row ["range"];
        //考勤结果
        if($row ['status']==1){
            $return='完成考勤';
        }elseif($row ['status']==2){
            $return='迟到';
        }else{
            $return='旷课';
        }
        $rows ['结果'] = $return;
        unset ( $row ["id"], $rows ["id"] );
        $questions [] = $rows;
        $line ++;
    }

    foreach ( $questions as $question ) {
        $data [] = $question;
    }

      //echo '<pre>';var_dump($data);echo '</pre>';  exit;
    switch ($file_type) {
        case 'xls' :
            Export::export_table_data ( $data, $filename, 'xls', false );
            break;
    }
  //  tb_close ( 'work_attendancee.php' );
}
Display::display_header ( null, FALSE );

$form->display ();

Display::display_footer ();