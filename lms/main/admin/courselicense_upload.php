<?php
$cidReset = true;
include_once ("../inc/global.inc.php");
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");

require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$table_course_license = Database::get_main_table (course_license);

$course_category = Database::get_main_table ( course_category);
$cate_sql="SELECT id,name   FROM $course_category where parent_id !=0 limit  0,10";
$category_arr=  api_sql_query_array_assoc($cate_sql,__FILE__,__FILE__);
$course_arr=array();
foreach($category_arr as  $key=>$val){
    $cid=$val['id'];
    $course_arr[$val['id']]=$val['name'];
}

$form = new FormValidator ( 'courselicense_upload','POST','courselicense_upload.php','');
$form->addElement ( 'html', '&nbsp;');
//$form->addElement ( 'text', 'description', "描述", array ('maxlength' => 50, 'style' => "width:50%", 'class' => 'inputText' ) );
//$form->addRule ( 'description', get_lang ( 'ThisFieldIsRequired' ), 'required' );
 
//$form->addElement ( 'select', 'course_code',"课程分类", $course_arr, array ('id' => "type", 'style' => 'height:22px;width:20%' ) );

$form->addElement ( 'file', 'filename', "课程license文件", array ('maxlength' => 20,'style' => "width:50%", 'class' => 'inputText','id'=>'filename' ) );
$form->addRule ( 'filename', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

Display::setTemplateBorder ( $form, '90%' );
if ($form->validate ()) {
    
    $course_licenses = $form->getSubmitValues ();
    ini_set ( 'memory_limit', '256M' );
    ini_set ( 'max_execution_time', 1800 ); //设置执行时间

    $tmp_name=$_FILES ['filename']['tmp_name'];
    $file_name="license".date ( 'smdHi' ).".lib";
    $path1=URL_ROOT."/www/lms/";
    $path2=URL_ROOT."/www/lms/storage/course_license";
    $file=URL_ROOT."/www/lms/storage/course_license/".$file_name;
    
    //判断course_license文件夹是否存在不存在创建，并修改权限
    if(!file_exists($path2)){
        exec("chmod -R 777".$path1."/");
        exec("mkdir -p ".$path2);
        exec("chmod -R 777 ".$path2."/");
    }

    //上传文件到course_license文件夹
     $course_licenses['filename'] = $file_name;
     move_uploaded_file($tmp_name, $file);
    
     if($license  =  opendir($path2)){
        exec("cd ".$path2."/;tar -xf ".$file); 
        while ($read_license=  readdir($license)){
            if(is_dir($path2."/".$read_license) && $read_license!='.' && $read_license!='..'){
                $license_path=URL_ROOT."/www/lms/storage/course_license/".$read_license;
                exec("chmod -R 777 ".$license_path);
            }
        }
     }
     closedir($path2);
    if($license_dir  =  opendir($license_path)){
        while ($category =  readdir($license_dir)){
            if(!is_dir($license_path."/".$category)){
            $category_path=$license_path."/".$category;
            exec("cd ".$license_path."/;tar -xf ".$category_path); 
            }
       }
    }
    closedir($license_path);
    if($license_dir2  =  opendir($license_path)){
       while ($category2 =  readdir($license_dir2)){
            if(is_dir($license_path."/".$category2) && $category2 != '.' && $category2 != '..'){
                   $category3=$license_path."/".$category2;
                   exec("chmod -R 777 ".$category3);         
                   $license_file=$category3."/license";
                   $license_file= file_get_contents($license_file);
                   $readme=$category3."/readme";
                   $course_category=file_get_contents($readme);
                   $time=date ( 'Y-m-d H:i:s' );
                   $packname=$course_licenses['filename'];
                   $sql="select filename from $table_course_license where course_category='$course_category'";
                   $filename=  Database::getval($sql);
                   if($filename){
                       $sql="update $table_course_license set filename='$packname' where course_category='$course_category'";
                       $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                       $filename=URL_ROOT."/www/lms/storage/course_license/".$filename;
                       //如果update执行成功，则删除
                       if($result){
                           if(file_exists($filename)){
                                $exec_var="rm  -rf ".$filename;
                                exec($exec_var);
                            }
                       }
                   }else{
                       $sql="INSERT INTO $table_course_license (course_category,time,filename,license) VALUES ('".$course_category."','".$time."','".$packname."','".$license_file."')";
                       $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                   }
                   
            }
            exec("rm -rf ".$category3);
        }          
    }
    closedir($license_path);
    exec("rm -rf ".$license_path);
    tb_close ( 'course_license.php' );
}
Display::display_header ( $tool_name, FALSE );
$form->display ();
Display::display_footer ();
