<?php
/**
 * This is an add routing and switching page
 * @changzf
 * on 2013/01/10
 */

$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");

require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$table_labs_ios = Database::get_main_table (labs_ios);


$_SESSION['id'] =  intval(getgpc('id','G'));
$id = $_SESSION['id'];

if(isset($id)){
    $sql="select * from $table_labs_ios where id = '".$id."'";

    $res = api_sql_query( $sql, __FILE__, __LINE__ );
    while($ss = Database::fetch_array ( $res )){
        $default = $ss;
    }
}
$form = new FormValidator ( 'labs_ios','POST','labs_ios_edit.php?id='.$id,'');
$form->addElement ( 'html', '&nbsp;');
$form->addElement ( 'text', 'name', "名字", array ('maxlength' => 30, 'style' => "width:30%", 'class' => 'inputText' ) );
$form->addRule ( 'name', get_lang ( 'ThisFieldIsRequired' ), 'required' );

   //$type=array('c7200'=>'c7200','c3600'=>'c3600','c2600'=>'c2600','c3700'=>'c3700','c1700'=>'c1700');
    $type_sql="SELECT DISTINCT  `name` FROM `labs_type`";
    $ress = api_sql_query ( $type_sql, __FILE__, __LINE__ );
    $type= array ();
    while ( $row = Database::fetch_array ( $ress, "NUM" ) ) {
       $n=$row [0];
       $type[$n] = $n;
     }
$form->addElement ( 'file', 'filename', "文件名称", array ('maxlength' => 20,'style' => "width:30%", 'class' => 'inputText','id'=>'filename' ) );
$form->addElement ( 'text', 'idle', "idle", array ('maxlength' => 20,'style' => "width:30%", 'class' => 'inputText' ) );
$form->addElement ( 'select', 'type',"类型", $type, array ('id' => "type", 'style' => 'height:22px;width:20%' ) );
$form->addElement ( 'text', 'slot_number',"slot数量",  array ('maxlength' => 8,'id' => "type", 'style' => 'width:30%','class' => 'inputText') );
$form->addElement ( 'text', 'ram', "ram", array ('maxlength' => 10,'style' => "width:30%", 'class' => 'inputText' ) );
$form->addElement ( 'text', 'nvram', "nvram", array ('maxlength' => 10,'style' => "width:30%", 'class' => 'inputText' ) );


$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ($default);
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {

    $labs  = $form->getSubmitValues ();
    ini_set ( 'memory_limit', '256M' );
    ini_set ( 'max_execution_time', 1800 ); //设置执行时间

    $tmp_name=$_FILES ['filename']['tmp_name'];
    $file=$_FILES ['filename']['name'];

    $url="/var/www/lms/main/admin/router/file/";
    $file=$url.$file;
    move_uploaded_file($tmp_name, $file);

    if(file_exists($file)){
        $labs['filename'] = $_FILES ['filename']['name'];
    }else{
        $labs['filename']='';
    }
    $name    = $labs['name'];
    $filename    = $labs['filename'];
    $idle  = $labs['idle'];
    $type    = $labs['type'];
    $ram    = $labs['ram'];
    $nvram  = $labs['nvram'];
    $slot_number  = $labs['slot_number'];


    if($filename==''){
       $sql="UPDATE `vslab`.`labs_ios` SET name= '".$name."',idle= '".$idle."',type= '".$type."',ram= '".$ram."',nvram= '".$nvram."',slot_number= '".$slot_number."' WHERE id='".$id."'";
    }else{
       //delete file
       $filename_sql="select `filename` from `labs_ios` where `id`=".$id;
       $get_filename=Database::getval($filename_sql,__FILE__,__LINE__);
       unlink("/var/www/lms/main/admin/router/file/".$get_filename);

       $sql="UPDATE `vslab`.`labs_ios` SET name= '".$name."',filename= '".$filename."',idle= '".$idle."',type= '".$type."',ram= '".$ram."',nvram= '".$nvram."',slot_number= '".$slot_number."' WHERE id='".$id."'";
    }
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );




    tb_close ( 'labs_ios.php' );

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();
