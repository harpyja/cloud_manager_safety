<?php
/**
 * Created by JetBrains PhpStorm.
 * User: chang
 * Date: 12-12-01
 * Time: 下午2:17
 * To change this template use File | Settings | File Templates.
 */
$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");

api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();
$table_img_upload = Database::get_main_table ( img_upload );
if(mysql_num_rows(mysql_query("SHOW TABLES LIKE clouddesktopscan"))!=1){
    $sql_insert ="CREATE TABLE IF NOT EXISTS `img_upload` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `img_file` varchar(256) NOT NULL,
      `storage_space_type` varchar(256) NOT NULL,
      PRIMARY KEY (`id`)
      ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
    api_sql_query ( $sql_insert,__FILE__, __LINE__ );
}

require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
$objCrsMng = new CourseManager ();

$form = new FormValidator ( 'clouddesktopdisk_upload','POST','clouddesktopdisk_upload.php','');
$form->addElement("html","<div style='height:10px;'></div>");

$group = array ();
$group [] = $form->createElement ( 'radio', 'storage_space_type', null, '共享', '1' ,array('id' => 'underlyingMirror','onclick'=>'check1()'));
$group [] = $form->createElement ( 'radio', 'storage_space_type', null, '独占', '2',array('id' => 'incrementalMirror','onclick'=>'check2()'));
$form->addGroup ( $group, 'storage_space_type', '存储空间类型', '&nbsp;&nbsp;&nbsp;&nbsp;', false );
$form->addRule ( 'storage_space_type', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$form->addElement ( 'file', 'img_upload', '云桌面系统镜像文件', array ('class' => 'inputText', 'style' => 'width:30%', 'id' => 'upload_file_local' ) );
$form->addRule ( 'img_upload', get_lang ( 'UploadFileNameAre' ) . ' *.img', 'filename', '/\\.(img)$/' );
$form->addRule ( 'img_upload', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="save"' );
//$group [] = $form->createElement ( 'style_submit_button', 'submit_plus', get_lang ( '确定并继续添加' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->freeze (array ("current_version" ));
$form->setDefaults ($default);
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {
//    var_dump($_FILES);
    $data = $form->getSubmitValues ();
    $tname = $_FILES["img_upload"]["tmp_name"];
    $fname = $_FILES["img_upload"]["name"];


    $img_file =$fname;
    $storage_space_type  = $data['storage_space_type'];


    $sql_data = array (
        'img_file' => $img_file,
        'storage_space_type' => $storage_space_type
    );
    $sql = Database::sql_insert ( $table_img_upload, $sql_data );

    //文件后缀名
    $file_type=substr(strrchr($fname,"."),1);
    if($file_type=="img"){
        $re=move_uploaded_file($tname,"/tmp/mnt/pxe/$fname");

         if(file_exists("/tmp/mnt/pxe/$fname")){
             api_sql_query ( $sql, __FILE__, __LINE__ );
         }

         tb_close ( 'clouddesktopdisk.php' );
    }else{
        exit;
    }

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();