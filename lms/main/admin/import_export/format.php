<?php
$language_file = 'admin';
$cidReset = true;
require_once ('../../../main/inc/global.inc.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
 
$file=getgpc('bid','G');
$act=getgpc('action','G');
 
if($act=='bale' && $file!==''){
      $path=URL_ROOT."/www/lms/storage";//local

    //判断升级包是否存在
    if(file_exists($path."/coursesinit/".$file)){
        $exec_var="cd ".$path."/coursesinit/;  sudo -u root /bin/tar -zxf  ".$file." readme";
        exec($exec_var); //echo $exec_var." <br>";

         //判断readme文件是否存在
          if(file_exists($path."/coursesinit/readme")){ //echo 'yes';
                        //获取filename，category_name，date 
                        $readme_var= file_get_contents($path."/coursesinit/readme");
                        $readme_data=  unserialize($readme_var); 
                        
                        $category_name=$readme_data['category_name'];
                        $date=$readme_data['date'];
                        
                         //判断license
                        $sysid_path="/etc/sysid.sys";
                        $sysid= file_get_contents($sysid_path);
                        $sysid=trim($sysid);
                        $category_name=trim($category_name);
                        $md5Str=$sysid.$category_name; 
                        $license=  md5($md5Str);
                        $sql="SELECT   `license` FROM `course_license` WHERE  `course_category`='".$category_name."'";
                        $get_license= Database::getval($sql,__FILE__,__LINE__);
                        if($license==$get_license){
                         $license_judge=TRUE;   
                        }else{
                          $license_judge=FALSE;
                        }
                        if($date && $category_name && $license_judge){
                            $sql="SELECT `id`  FROM  `course_category`  WHERE  `name` ='".$category_name."'";
                            $cata_id=  Database::getval($sql,__FILE__,__LINE__);

                        //判断该分类是否存在
                                 if($cata_id){
                                            //如果没有coursesnew，则新建
                                             if(!file_exists($path."/coursesnew/")){
                                                  exec("mkdir ".$path."/coursesnew/");
                                                  exec("chmod -R 777 ".$path."/coursesnew/"); 
                                             }
                                             //delete old tgz
                                            $old_dir=$path."/coursesnew/".$cata_id."-*.lib";
                                             exec("rm -rf  ".$old_dir);
                                             
                                             $old_file="rm -rf $path/coursesnew/".$cata_id."*";
                                             exec($old_file);
                                             
                                          //tgz文件
                                             $tgz_filename=$cata_id."-".$date.".lib";
                                            $exec_var1="mv $path/coursesinit/$file $path/coursesnew/".$tgz_filename;
                                            exec($exec_var1);
                                            
                                          //readme文件
                                             $exec_var3="mv $path/coursesinit/readme $path/coursesnew/".$cata_id."_".$date."_readme";
                                             exec($exec_var3);
                                             
                                             //删除缓存文件
                                             $exec_temp="rm -rf /var/tmp/CGItemp*";
                                             exec($exec_temp);
                                             
                                            api_redirect ("import.php");
                                        }else{
                                            echo "<script language='javascript'>alert('您没有该分类课程，请增加后重新执行！'); self.location='import.php'; </script>";
                                        }
                        }else{
                            exec("rm -f ".$path."/coursesinit/readme");
                            echo "<script language='javascript'>alert('您导入的压缩包文件错误或者没有课件升级许可，请检查！'); self.location='import.php'; </script>";
                        }
          }else{ 
             echo "<script language='javascript'>alert('您导入的压缩包文件错误，请检查！'); self.location='import.php'; </script>";
          } 
    }else{
         echo "<script language='javascript'>alert('对不起，找不到需要升级的课件包！'); self.location='import.php'; </script>";
         //api_redirect ("import.php");
    } 
} 
?>
