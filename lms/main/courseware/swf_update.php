<?php
/*
 ==============================================================================
 上传HTML打包课程文档
 ==============================================================================
 */

$language_file = 'document';
include_once ("../inc/global.inc.php");
$this_section = SECTION_COURSES;
api_block_anonymous_users ();
//api_protect_course_script ();

$is_allowed_to_edit = api_is_allowed_to_edit ();
//if (! $is_allowed_to_edit) api_not_allowed ();
$user_id = api_get_user_id ();
$table_courseware = Database::get_course_table ( TABLE_COURSEWARE );
$courseDir = api_get_course_code ();
$base_work_dir = api_get_path ( SYS_COURSE_PATH ) . $courseDir.'/swf/';
$max_upload_file_size = get_upload_max_filesize ( api_get_setting ( "upload_max_filesize" ) );
$ftp_path = api_get_path ( SYS_FTP_ROOT_PATH ) . 'zip/';

require_once (api_get_path ( SYS_CODE_PATH ) . "courseware/cw.lib.inc.php");
include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');

$cid=trim(htmlspecialchars(getgpc("cidReq","G")));

$htmlHeadXtra [] = Display::display_thickbox ();
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';


$nameTools = get_lang ( 'UploadHTMLPackage' );
$interbreadcrumb [] = array ("url" => "cw_package_list.php", "name" => get_lang ( "HTMLPackageCourseware" ) );

$form = new FormValidator ( 'upload', 'POST', $_SERVER ['PHP_SELF'].'?cid='.$cid, '', 'enctype="multipart/form-data"' );

$form->addElement ( 'header', 'header', get_lang ( '上传可见' ) );

$form->addElement ( 'text', 'title', get_lang ( 'Title' ), array ('size' => '45', 'style' => "width:350px", 'class' => 'inputText' ) );
$form->addRule ( 'title', get_lang ( 'ThisFieldIsRequired' ), 'required' );

//从本地文件中选取
$form->addElement ( 'file', 'user_upload', get_lang ( 'File' ), array ('class' => 'inputText', 'style' => 'width:50%', 'id' => 'upload_file_local' ) );
$form->addRule ( 'user_upload', get_lang ( 'UploadFileSizeLessThan' ) . ($max_upload_file_size) . ' MB', 'maxfilesize', $max_upload_file_size * 1024 * 1024 );
//$form->addRule ( 'user_upload', get_lang ( 'UploadFileNameAre' ) . ' *.zip', 'filename', '/\\.(zip)$/' );

//最小学习时间
$form->add_textfield ( 'learning_time', get_lang ( 'MinLearningTime' ), true, array ('id' => 'learning_time', 'style' => "width:60px;text-align:right;", 'class' => 'inputText', 'maxlength' => 60 ) );
$form->addRule ( 'learning_time', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'learning_time', get_lang ( 'MustLargerThan0' ), 'callback', 'range_check' );
$defaults ['learning_time'] = 30;

//显示顺序
$form->add_textfield ( 'display_order', get_lang ( 'DisplayOrder' ), true, array ('id' => 'learning_order', 'style' => "width:60px;text-align:right;", 'class' => 'inputText', 'maxlength' => 60 ) );
$form->addRule ( 'display_order', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'display_order', get_lang ( 'MustLargerThan0' ), 'callback', 'range_check' );

$defaults ["display_order"] = get_next_disp_order ();
$form->addElement ( 'textarea', 'comment', get_lang ( 'Comment' ), array ('cols' => 40, 'rows' => 3, 'wrap' => 'virtual', 'class' => 'inputText' ) );


$group = array ();
$group [] = $form->createElement ( 'submit', 'submitDocument', get_lang ( 'Ok' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ( $defaults );
Display::setTemplateBorder ( $form, '98%' );
$form->add_real_progress_bar ( 'DocumentUpload', 'user_upload' );

//创建swf目录/
if(!file_exists("$base_work_dir")){
    make_dir("$base_work_dir");
   // exec("cd $base_work_dir; chmod 777 * ;");
}
//exec("cd /tmp/www/lms/storage/courses/201107211628/swf/swf_1357461110/ ;  unoconv -f pdf linuxdoc2pdf.docx ; pdf2swf -o linuxdoc2pdf.swf -T -z -t -f linuxdoc2pdf.pdf");

//var_dump()
//exec("cd $base_work_dir ;  pdf2swf -o $base_work_dir/output.swf -T -z -t -f $base_work_dir/aa.pdf -s ");
// exec("unoconv -f pdf aa.doc");
if ($form->validate ()) {
    //设置内存及执行时间
    $date = time();
    exec("cd $base_work_dir; chmod 777 * ;");
    make_dir($base_work_dir.'swf_'.$date);
    $base_work_dir = $base_work_dir.'swf_'.$date.'/';
    exec("cd $base_work_dir; chmod 777 * ;");
    ini_set ( 'memory_limit', '256M' );
    ini_set ( 'max_execution_time', 1800 ); //设置执行时间
    $data = $form->getSubmitValues ();
    $cc = api_get_course_code();//课程编号


    $title = trim ( $data ['title'] );
    $size =$_FILES["user_upload"]["size"];
    $comment = $data ['comment'];
    $attribute =$data['attribute'];
    $display_order = trim ( $data ["display_order"] );
    $learning_time = trim ( $data ['learning_time'] );

        $upload_ok = process_uploaded_file ( $_FILES ['user_upload'] );


        if ($upload_ok) {
            $tname = $_FILES["user_upload"]["tmp_name"];
            $fname = $_FILES["user_upload"]["name"];


            move_uploaded_file($tname,$base_work_dir.$fname);
            $path_parts=pathinfo($fname); //返回指定路径中的关联数组，目录名、基本名、扩展名
            //echo $path_parts["extension"];
            $names =  basename($fname,$path_parts["extension"]);
            $name=explode('.',$names);
            $name = $name[0];
            //echo $path_parts["extension"];
            if( $path_parts["extension"]=='pdf'){
               // exec("cd $base_work_dir ;  pdf2swf -o $name.swf -T -z -t -f $name.pdf");
                exec("sudo -u root /sbin/clouddoc.sh  $base_work_dir $fname $name pdf &");

            }else{
             // sudo -u root ; cd /tmp/www/lms/storage/courses/201107211628/swf/swf_1357456535/ ; unoconv -f pdf linuxdoc2pdf.docx ; pdf2swf -o linuxdoc2pdf.swf -T -z -t -f linuxdoc2pdf.pdf
              //  echo "cd $base_work_dir ;  unoconv -f pdf $fname ; pdf2swf -o $name.swf -T -z -t -f $name.pdf";
               // exec("sudo -u root ;cd $base_work_dir ;  /usr/bin/unoconv -f pdf $base_work_dir$fname ; /usr/local/bin/pdf2swf -o $base_work_dir$name.swf -T -z -t -f $base_work_dir$name.pdf");
                exec("sudo -u root /sbin/clouddoc.sh  $base_work_dir $fname $name doc &");


            }
        }

    $path =URL_APPEDND.'/storage/courses/'.$cc.'/swf/swf_'.$date.'/'.$name.'.swf';
    $time = date ( "Y-m-d H:i:s", time () );
    $sql_data = array (
        'cw_type' => 'swf',
        'cc' => trim(htmlspecialchars(getgpc("cid","G"))),
        'path' => $path,
        'title' => $title,
        'size' => $size,
        'comment' => $comment,
        'attribute' => "$fname",
        'display_order' => $display_order,
        'created_date' =>$time,
        'visibility' => 1,
        'learning_time' => $learning_time,

    );

    $sql = Database::sql_insert ( $table_courseware, $sql_data );
    $result = api_sql_query ( $sql, __FILE__, __LINE__);

    tb_close();
}

Display::display_header ( $nameTools, FALSE );


echo '<div id="demo" class="yui-navset" style="margin:10px">';
echo display_cw_type_tab ( 'swf' );
echo '<div class="yui-content"><div id="tab1">';
//Display::display_confirmation_message ( get_lang ( 'UploadDocTip' ), false );
$form->display ();
echo '</div></div></div>';
Display::display_footer ();