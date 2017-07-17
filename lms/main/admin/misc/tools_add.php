<?php
$cidReset = true;
include_once ("../../inc/global.inc.php");
header("content-type:text/html;charset=utf-8");
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$htmlHeadXtra [] = Display::display_kindeditor ( 'content', 'normal' );

$form = new FormValidator ( 'task','POST','tools_add.php','');
$form->addElement ( 'html', '<div style="margin-top:5px;"></div>');
$form->add_textfield ( 'title', '名称', true, array ('style' => "width:80%", 'class' => 'inputText' ) );

//$form->addElement ( 'text', 'name', "任务名称", array ('maxlength' => 50, 'style' => "width:50%;height:25px", 'class' => 'inputText' ) );
$form->addRule ( 'title', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$form->addRule ( 'title', get_lang ( '最大字符长度为30' ), 'maxlength', 30 );

$form->registerRule('title_only','function','check_title');
$form->addRule('title','您输入的内容已存在，请重新输入', 'title_only');
function check_title($element_name, $element_value) {
    $table_task = Database::get_main_table ( tools );
    $sql="select title from $table_task";
    $vmdisk_name=Database::get_into_array ( $sql );

    if (in_array($element_value,$vmdisk_name)) {
        return false;
    } else {
        return true;
    }
}
$form->addElement ( 'file', 'screenshot_file', "文件", array ('style' => "width:30%", 'class' => 'inputText','id'=>'screenshot_file' ) );
$form->addElement ( 'textarea', 'content', '描述', array ('id' => 'content', 'wrap' => 'virtual', 'class' => 'inputText', 'style' => 'width:100%;height:260px' ) );

//显，隐
$group = array ();
$group [] = $form->createElement ( 'radio', 'visible', null, get_lang ( 'Visible1' ), 1 );
$group [] = $form->createElement ( 'radio', 'visible', null, get_lang ( 'Invisible' ), 0 );
$form->addGroup ( $group, null, get_lang ( 'Visible' ), null, false );
$default['visible']=0;
$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ($default);
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {

    $labs              = $form->getSubmitValues ();
    $title             = $labs['title'];
    $visible           = $labs['visible'];
    $content           = $labs['content'];
    $created_user      = $_SESSION["_user"]["user_id"];
    $date_start        = date ( 'Y-m-d H:i' );


    ini_set ( 'memory_limit', '256M' );
    ini_set ( 'max_execution_time', 1800 ); //设置执行时间

    $tmp_name=$_FILES["screenshot_file"]["tmp_name"];
    $name=$_FILES["screenshot_file"]["name"];

    $path=URL_ROOT.'/www/'.URL_APPEDND.'/storage/tools';
    //  $file1='report_'.$user.'_'.date("His",time()).'.'.$new_name;
    //  $file=$path.'/'.$file1;
    $file=$path."/".$name;

    if(!file_exists($path)){
        mkdir($path, 0777); //新建文件夹
        chmod($path, 0777); //修改文件夹权限
    }
    move_uploaded_file($tmp_name, $file);

    $sql_data = array (
        'title'        =>  $title,
        'visible'      =>  $visible,
        'created_user' =>  $created_user,
        'content'      =>  $content,
        'file'         =>  $name,
        'date_start'   =>  $date_start
    );
    $sql = Database::sql_insert ( tools, $sql_data );
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

    tb_close ( 'tools.php' );

}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();