<?php
/*
 ==============================================================================
导出课程管理
 ==============================================================================
 */
$language_file = 'admin';
$cidReset = true;
include_once ("../../inc/global.inc.php");
$this_section = SECTION_PLATFORM_ADMIN;
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');

api_protect_admin_script ();

require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( SYS_CODE_PATH ) . 'admin/course/course_category.inc.php');

$objDept = new DeptManager ();

$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ('course');
//$org_id = (isset ( $_REQUEST ["org_id"] ) ? getgpc ( 'org_id' ) : "-1");
 
 // 获取tgz文件路径
function get_tgz_path($filename,$cate_id){ 
    $paths=URL_ROOT."/www".URL_APPEDND."/storage/";//local 
    $files1 = glob($paths.$filename."/".$cate_id.'-*.lib');
    $tgz_path=$files1[0];
     return $tgz_path;
}
// 获取readme文件路径
function get_readme_path($filename,$cate_id){ 
    $paths=URL_ROOT."/www".URL_APPEDND."/storage/";//local 
    $files2 = glob($paths.$filename."/".$cate_id.'_*_readme');
    $readme_path=$files2[0]; 
    return $readme_path;
}

$paths=URL_ROOT."/www".URL_APPEDND."/storage/";//local 
$mulu="tmp";
if(!file_exists($paths."/coursesbak")){
   exec("mkdir ".$paths."/coursesbak");
   exec("chmod -R 777 ".$paths."/coursesbak");
}
if(!file_exists($paths."/coursesinit")){
   exec("mkdir ".$paths."/coursesinit");
   exec("chmod -R 777 ".$paths."/coursesinit");
}    
if(!file_exists($paths."/coursesnew")){
   exec("mkdir ".$paths."/coursesnew");
   exec("chmod -R 777 ".$paths."/coursesnew");
}
if(!file_exists($paths."/coursescur")){
   exec("mkdir ".$paths."/coursescur");
   exec("chmod -R 777 ".$paths."/coursescur");
}
$action=getgpc('action','G');
$code=getgpc('code','G');
if($code!='' && $action!=''){
     if($action=='update'){//升级
          
                $readme_paths=get_readme_path('coursesnew',$code);
                $tgz_paths=get_tgz_path('coursesnew',$code);
                 
                if(file_exists($readme_paths) && file_exists($tgz_paths)){
                    
                    //获取到tgz文件
                    $pathinfo = pathinfo($tgz_paths); 
                    $dirname=$pathinfo['dirname'];
                    $get_filename=$pathinfo['basename']; 
                     
                    //解压tgz
                     $exec_var="cd ".$dirname."; tar  -zxf ".$get_filename;//解压tmp
                     exec($exec_var);
                     $exec_var1="cd /;tar -zxf  ".$tgz_paths." ".$mulu." -C ".URL_ROOT;   
                     exec($exec_var1);
                       
                      exec("chmod -R 777 ". $dirname."/*"); 
                      
                        $readme_var= file_get_contents($readme_paths);
                        $readme_data=  unserialize($readme_var);
                        $filename=$readme_data['filename'];
                        //获取文件夹名称
                       $files=  explode( '.',$filename); 
                     
                    //获取 course_category  数据
                            $get_category= file_get_contents($dirname."/". $files[0]."/course_category");
                            $category_data=  unserialize($get_category); 
//                            echo $dirname."/". $files[0]."/course_category"."<br>";
                            
                            $category_parent_id      =$category_data[0]['parent_id'];
                            $category_sn                  =$category_data[0]['sn'];
                            $category_name             =$category_data[0]['name'];
                            $category_code              =$category_data[0]['code'];
                            $category_tree_pos        =$category_data[0]['tree_pos'];
                            $category_children_count  =$category_data[0]['children_count'];
                            $category_auth_cat_child   =$category_data[0]['auth_cat_child'];
                            $category_last_updated_date      =$category_data[0]['last_updated_date'];        
                            $category_org_id             =$category_data[0]['org_id'];
                            $category_CourseDescription     =$category_data[0]['CourseDescription'];        
                             $category_CurriculumStandards =$category_data[0]['CurriculumStandards'];        
                            $category_AssessmentCriteria   =$category_data[0]['AssessmentCriteria'];
                            $category_TeachingProgress     =$category_data[0]['TeachingProgress'];
                            $category_StudyGuide                =$category_data[0]['StudyGuide'];
                            $category_TeachingGuide          =$category_data[0]['TeachingGuide'];
                             $category_InstructionalDesignEvaluation=$category_data[0]['InstructionalDesignEvaluation'];
                             $category_status=$category_data[0]['status'];  
 
                    //查询是否有该类 
                            $s="select `id` from `vslab`.`course_category` where name='".$category_name."'" ; 
                            $cate=  intval(Database::getval ( $s, __FILE__, __LINE__ )); 
                            if($cate){
                            //判断插入新的分类
                                     $sql_cate="UPDATE `course_category` SET `sn`='".$category_sn."',`name`='".$category_name."',`code`='".$category_code."',`tree_pos`='".$category_tree_pos."',`children_count`='".$category_children_count."',`auth_cat_child`='".$category_auth_cat_child."',`last_updated_date`='".$category_last_updated_date."',`org_id`='".$category_org_id."',`CourseDescription`='".$category_CourseDescription."',`CurriculumStandards`='".$category_CurriculumStandards."',`AssessmentCriteria`='".$category_AssessmentCriteria."',`TeachingProgress`='".$category_TeachingProgress."',`StudyGuide`='".$category_StudyGuide."',`TeachingGuide`='".$category_TeachingGuide."',`InstructionalDesignEvaluation`='".$category_InstructionalDesignEvaluation."',`status`='".$category_status."' WHERE id=".$cate;
                            }else{
                                     $sql_cate="INSERT INTO `course_category`(`id`, `parent_id`, `sn`, `name`, `code`, `tree_pos`, `children_count`, `auth_cat_child`, `last_updated_date`, `org_id`, `CourseDescription`, `CurriculumStandards`, `AssessmentCriteria`, `TeachingProgress`, `StudyGuide`, `TeachingGuide`, `InstructionalDesignEvaluation`, `status`) VALUES ('','".$category_parent_id."','".$category_sn."','".$category_name."','".$category_code."','".$category_tree_pos."','".$category_children_count."','".$category_auth_cat_child."', '".$category_last_updated_date."','".$category_org_id."','".$category_CourseDescription."','".$category_CurriculumStandards."','".$category_AssessmentCriteria."','".$category_TeachingProgress."','".$category_StudyGuide."','".$category_TeachingGuide."','".$category_InstructionalDesignEvaluation."','".$category_status."');";
                            }
                            $c_res = api_sql_query ( $sql_cate, __FILE__, __LINE__ ); 
//                             echo $c_res."&nbsp;分类".$sql_cate."<hr>";
                             
                             $cate_new_sql="select `id` from `vslab`.`course_category` where name='".$category_name."'" ; 
                             $cate_new_id=Database::getval ( $cate_new_sql, __FILE__, __LINE__ ); 
                              
 //course数据插入
                            $get_course= file_get_contents($dirname."/". $files[0]."/courses");
                            $course_data=  unserialize($get_course);
                            
                            for($j=0;$j<count($course_data);$j++){
                                   $course_code=$course_data[$j]['code'];
                                   $course_title=$course_data[$j]['title'];
                                   $course_topo=$course_data[$j]['description13'];
                                   
                                   $code_sql="select `code` from `vslab`.`course` where title='".$course_title."'" ; 
                                   $course_id=Database::getval ( $code_sql, __FILE__, __LINE__ ); 
                                     
                                   $description=str_replace("'","\'",$course_data[$j]['description']);
                                   $description1=str_replace("'","\'",$course_data[$j]['description1']);
                                   $description2=str_replace("'","\'",$course_data[$j]['description2']);
                                   $description3=str_replace("'","\'",$course_data[$j]['description3']);
                                   $description4=str_replace("'","\'",$course_data[$j]['description4']);
                                   $description5=str_replace("'","\'",$course_data[$j]['description5']);
                                   $description6=str_replace("'","\'",$course_data[$j]['description6']);
                                   $description7=str_replace("'","\'",$course_data[$j]['description7']);
                                   $description8=str_replace("'","\'",$course_data[$j]['description8']);
                                   
                                   //课程
                                        if($course_id!=''){//更新
                                            $c_sql="UPDATE `course` SET `org_id`='".$course_data[$j]['org_id']."',`title`='".$course_data[$j]['title']."',`category_code`='".$cate_new_id."',`status`='".$course_data[$j]['status']."',`visibility`='".$course_data[$j]['visibility']."',`is_audit_enabled`='".$course_data[$j]['is_audit_enabled']."',`is_shown`='".$course_data[$j]['is_shown']."',`is_subscribe_enabled`='".$course_data[$j]['is_subscribe_enabled']."',`pass_condition`='".$course_data[$j]['pass_condition']."',`expiration_date`='".$course_data[$j]['expiration_date']."',`start_date`='".$course_data[$j]['start_date']."',`disk_quota`='".$course_data[$j]['disk_quota']."',`subscribe`='".$course_data[$j]['subscribe']."',`unsubscribe`='".$course_data[$j]['unsubscribe']."',`last_visit`='".$course_data[$j]['last_visit']."',`creation_date`='".$course_data[$j]['creation_date']."',`created_user`='".$course_data[$j]['created_user']."',`last_edit`='".$course_data[$j]['last_edit']."',`credit`='".$course_data[$j]['credit']."',`credit_hours`='".$course_data[$j]['credit_hours']."',`is_free`='".$course_data[$j][' ']."',`fee`='".$course_data[$j]['fee']."',`apply_id`='".$course_data[$j][' ']."',`directory`='".$course_data[$j]['directory']."',`db_name`='".$course_data[$j]['db_name']."',`course_language`='".$course_data[$j]['course_language']."',`tutor_name`='".$course_data[$j]['tutor_name']."',`visual_code`='".$course_data[$j]['visual_code']."',`default_learing_days`='".$course_data[$j]['default_learing_days']."',`description`='".$description."',`description1`='".$description1."',`description2`='".$description2."',`description3`='".$description3."',`description4`='".$description4."',`description5`='".$description5."',`description6`='".$description6."',`description7`='".$description7."',`description8`='".$description8."',`description9`='".$course_data[$j]['description9']."',`description10`='".$course_data[$j]['description10']."',`description11`='".$course_data[$j]['description11']."',`description12`='".$course_data[$j]['description12']."',`description13`='".$course_data[$j]['description13']."',`nodeId`='".$course_data[$j]['nodeId']."' WHERE  title='".$course_title."'" ; 
                                        }else{//插入
                                            $c_sql="INSERT INTO `course`( `code`,`org_id`, `title`, `category_code`, `status`, `visibility`, `is_audit_enabled`, `is_shown`, `is_subscribe_enabled`, `pass_condition`, `expiration_date`, `start_date`, `disk_quota`, `subscribe`,`unsubscribe`, `last_visit`, `creation_date`, `created_user`, `last_edit`, `credit`, `credit_hours`, `is_free`, `fee`, `apply_id`, `directory`, `db_name`, `course_language`, `tutor_name`, `visual_code`, `default_learing_days`, `description`, `description1`, `description2`, `description3`, `description4`, `description5`, `description6`, `description7`, `description8`, `description9`, `description10`, `description11`, `description12`, `description13`, `nodeId`) VALUES ( '".$course_code."','".$course_data[$j]['org_id']."',  '".$course_data[$j]['title']."',  '".$cate_new_id."',  '".$course_data[$j]['status']."',  '".$course_data[$j]['visibility']."',    '".$course_data[$j]['is_audit_enabled']."',  '".$course_data[$j]['is_shown']."',  '".$course_data[$j]['is_subscribe_enabled']."', '".$course_data[$j]['pass_condition']."', '".$course_data[$j]['expiration_date']."',  '".$course_data[$j]['start_date']."',  '".$course_data[$j]['disk_quota']."', '".$course_data[$j]['subscribe']."',   '".$course_data[$j]['unsubscribe']."',  '".$course_data[$j]['last_visit']."',   '".$course_data[$j]['creation_date']."',  '".$course_data[$j]['created_user']."',  '".$course_data[$j]['last_edit']."',  '".$course_data[$j]['credit']."', '".$course_data[$j]['credit_hours']."', '".$course_data[$j]['is_free']."',   '".$course_data[$j]['fee']."',  '".$course_data[$j]['apply_id']."',  '".$course_data[$j]['directory']."', '".$course_data[$j]['db_name']."',  '".$course_data[$j]['course_language']."', '".$course_data[$j]['tutor_name']."',  '".$course_data[$j]['visual_code']."',  '".$course_data[$j]['default_learing_days']."',  '".$description."',  '".$description1."',   '".$description2."',  '".$description3."',   '".$description4."',  '".$description5."','".$description6."',  '".$description7."',  '".$description8."','".$course_data[$j]['description9']."',  '".$course_data[$j]['description10']."',  '".$course_data[$j]['description11']."',  '".$course_data[$j]['description12']."',  '".$course_data[$j]['description13']."',   '".$course_data[$j]['nodeId']."')";
                                        }
                                        $course_res = api_sql_query ( $c_sql, __FILE__, __LINE__ );
                                        
//                                     if($course_res){
//                                      echo $course_data[$j]['title']."&nbsp;&nbsp;执行成功"."<hr>";
//                                      }else{
//                                          echo $course_data[$j]['title']."&nbsp;&nbsp;执行失败！<br>";
//                                          var_dump($c_sql);echo "<hr>";
//                                      }
                                        
                                        //移动课程文件夹
                                        $new_code=DATABASE::getval("select code from course  where title='".$course_title."'", __FILE__, __LINE__);
                                        
                                        if($new_code!==$course_code){
                                            $exec_chmod="chmod -R 777 ". URL_ROOT."/www".URL_APPEDND."/storage/courses/".$course_code;
                                            exec($exec_chmod);exec($exec_chmod."/*");
                                            $exec_var9=" mv -f ".URL_ROOT."/www".URL_APPEDND."/storage/courses/".$course_code." ".URL_ROOT."/www".URL_APPEDND."/storage/courses/".$new_code;
                                            exec($exec_var9);
                                        }else{
//                                            echo "code 相同";
                                        }
                                     
                                        //课件  courseware sql数据
                                        if($course_res){
                                                $get_courseware= file_get_contents($dirname."/". $files[0]."/".$course_code."/courseware");
                                                $courseware_data=  unserialize($get_courseware);
                                                for($m=0;$m<count($courseware_data);$m++) {
                                                            $courseware_cc                    = $courseware_data[$m]['cc'];
                                                            $courseware_cw_type          = $courseware_data[$m]['cw_type'];
                                                            $courseware_path                = $courseware_data[$m]['path'];
                                                            $courseware_title                 = $courseware_data[$m]['title'];
                                                            $courseware_size                 = $courseware_data[$m]['size'];
                                                            $courseware_comment        = $courseware_data[$m]['comment'];
                                                            $courseware_attribute         = $courseware_data[$m]['attribute'];
                                                            $courseware_display_order= $courseware_data[$m]['display_order'];
                                                            $courseware_created_date = $courseware_data[$m]['created_date'];
                                                            $courseware_visibility         = $courseware_data[$m]['visibility']; 
                                                            $courseware_learning_time        = $courseware_data[$m]['learning_time']; 


                                                              $cid_sql="select `id` from `vslab`.`crs_courseware` where `title`='".$courseware_title."'" ; 
                                                              $c_id=  intval(Database::getval ( $cid_sql, __FILE__, __LINE__ ));
                                                              if($c_id!=''){
                                                                   $cc_sql="UPDATE `crs_courseware` SET  `cc`='".$new_code."',`cw_type`='".$courseware_cw_type."',`path`='".$courseware_path."',`size`='".$courseware_size."',`comment`='".$courseware_comment."',`attribute`='".$courseware_attribute."',`display_order`='".$courseware_display_order."',`created_date`='".$courseware_created_date."',`visibility`='".$courseware_visibility."',`learning_time`='".$courseware_learning_time."' WHERE  id='".$c_id."'";
                                                              }else{
                                                                   $cc_sql="INSERT INTO `crs_courseware`(`id`, `cc`, `cw_type`, `path`, `title`, `size`, `comment`, `attribute`, `display_order`, `created_date`, `visibility`, `learning_time`) VALUES ('','".$new_code."','".$courseware_cw_type."','".$courseware_path."','".$courseware_title."','".$courseware_size ."','".$courseware_comment."','".$courseware_attribute."','".$courseware_display_order."','".$courseware_created_date."','".$courseware_visibility."','".$courseware_learning_time."')";
                                                              }
                                                              $cc_res= api_sql_query (  $cc_sql, __FILE__, __LINE__ );
                                                }
                                                
                                        }
                                       //拓扑
                                       $get_networkmap= file_get_contents($dirname."/". $files[0]."/".$course_code."/networkmap");
                                       $networkmap_data=  unserialize($get_networkmap);  
                                       for($k=0;$k<count($networkmap_data);$k++){
                                                $networkmap_id	         =$networkmap_data[0]['id'];
                                                $networkmap_name       =$networkmap_data[0]['name'];
                                                $networkmap_describe  =$networkmap_data[0]['describe'];	
                                                $networkmap_content    =$networkmap_data[0]['content'];	 
                                                
                                                $networkmap_xml =  str_replace('"', '\"', $networkmap_data[0]['xml']);  //转义xml
                                                
                                                if($course_topo==$networkmap_name){
                                                        $networkmap_sql="select `id` from `vslab`.`networkmap` where name='".$course_topo."'" ; 
                                                        $networkmap_id=Database::getval ( $networkmap_sql, __FILE__, __LINE__ );
                                                            if($networkmap_id!=''){
                                                                    $networkmap_sql="UPDATE `networkmap` SET   `describe`='".$networkmap_describe."',`content`='".$networkmap_content."',`xml`='".$networkmap_xml."' WHERE id='".intval($networkmap_id)."'";
                                                            }else{
                                                                    $networkmap_sql="INSERT INTO `networkmap`(`id`, `name`, `describe`, `content`, `xml`) VALUES ('','".$networkmap_name."','".$networkmap_describe."','".$networkmap_content."','".$networkmap_xml."')";
                                                            }
                                                       $networkmap_res = api_sql_query ( $networkmap_sql, __FILE__, __LINE__ );
                                                       
                                                            if($networkmap_res){
                                                 //模板
                                                             $get_vmdisk= file_get_contents($dirname."/". $files[0]."/".$course_code."/vmdisk");
                                                             $vmdisk_data=  unserialize($get_vmdisk); 
                                                                    for($h=0;$h<count($vmdisk_data);$h++){
                                                                        $vmdisk_id =$vmdisk_data[$h]['id'];
                                                                        $vmdisk_category=$vmdisk_data[$h]['category'];
                                                                        $vmdisk_name	=$vmdisk_data[$h]['name'];
                                                                        $vmdisk_version=$vmdisk_data[$h]['version'];
                                                                        $vmdisk_size	=$vmdisk_data[$h]['size'];
                                                                        $vmdisk_active=$vmdisk_data[$h]['active'];
                                                                        $vmdisk_ISO	=$vmdisk_data[$h]['ISO'];
                                                                        $vmdisk_boot	=$vmdisk_data[$h]['boot'];
                                                                        $vmdisk_memory=$vmdisk_data[$h]['memory'];	
                                                                        $vmdisk_CPU_number=$vmdisk_data[$h]['CPU_number'];	
                                                                        $vmdisk_NIC_type =$vmdisk_data[$h]['NIC_type'];
                                                                        $vmdisk_CD_mirror=$vmdisk_data[$h]['CD_mirror'];	
                                                                        $vmdisk_mac	=$vmdisk_data[$h]['mac'];
                                                                        $vmdisk_platform=$vmdisk_data[$h]['platform'];	
                                                                        $vmdisk_vlan	=$vmdisk_data[$h]['vlan'];
                                                                        $vmdisk_nodeId	=$vmdisk_data[$h]['nodeId'];
                                                                        $vmdisk_server_ip=$vmdisk_data[$h]['server_ip'];	
                                                                        $vmdisk_group_id=$vmdisk_data[$h]['group_id'];	
                                                                        $vmdisk_type=$vmdisk_data[$h]['type'];
                                                                        $vmdisk_task_id	=$vmdisk_data[$h]['task_id'];
                                                                        $vmdisk_mod_type=$vmdisk_data[$h]['mod_type'];

                                                                        $vmdisk_sql="select `id` from `vslab`.`vmdisk` where name='".$vmdisk_name."'" ; 
                                                                        $vmdisk_id=Database::getval ( $vmdisk_sql, __FILE__, __LINE__ );

                                                                        if($vmdisk_id){
                                                                            $networkmap_sql="UPDATE `vmdisk` SET  `category`='".$vmdisk_category."',`name`='".$vmdisk_name."',`version`='".$vmdisk_version."',`size`='".$vmdisk_size."',`active`='".$vmdisk_active."',`ISO`='".$vmdisk_ISO."',`boot`='".$vmdisk_boot."',`memory`='".$vmdisk_memory."',`CPU_number`='".$vmdisk_CPU_number."',`NIC_type`='".$vmdisk_NIC_type."',`CD_mirror`='".$vmdisk_CD_mirror."',`mac`='".$vmdisk_mac."',`platform`='".$vmdisk_platform."',`vlan`='".$vmdisk_vlan."',`nodeId`='".$vmdisk_nodeId."',`server_ip`='".$vmdisk_server_ip."',`group_id`='".$vmdisk_group_id."',`type`='".$vmdisk_type."',`task_id`='".$vmdisk_task_id."',`mod_type`='".$vmdisk_mod_type."' WHERE  `id`='".intval($vmdisk_id)."'";
                                                                        }else{
                                                                            $networkmap_sql="INSERT INTO `vmdisk`(`id`, `category`, `name`, `version`, `size`, `active`, `ISO`, `boot`, `memory`, `CPU_number`, `NIC_type`, `CD_mirror`, `mac`, `platform`, `vlan`, `nodeId`, `server_ip`, `group_id`, `type`, `task_id`, `mod_type`) VALUES ('','". $vmdisk_category."','".$vmdisk_name."','".$vmdisk_version."','".$vmdisk_size."','".$vmdisk_active."','".$vmdisk_ISO."','".$vmdisk_boot."','".$vmdisk_memory."','".$vmdisk_CPU_number."','".$vmdisk_NIC_type."','".$vmdisk_CD_mirror."','".$vmdisk_mac."','".$vmdisk_platform."','".$vmdisk_vlan."','".$vmdisk_nodeId."','".$vmdisk_server_ip."','".$vmdisk_group_id."','".$vmdisk_type."','".$vmdisk_task_id."','".$vmdisk_mod_type."')";
                                                                        }
                                                                        $networkmap_res = api_sql_query ( $networkmap_sql, __FILE__, __LINE__ );

                                                                  } 
                                                        }
                                                }         
                                       }
                                       
                            }
                            
                            
                         //删除解压文件
                         $exec_var5="rm  -rf ".$dirname."/". $files[0]." ".$dirname . URL_ROOT." ".$dirname."/readme";
                         exec($exec_var5);
                          
                         if($cate_new_id!==''){
                             $exec_var8="rm -rf  ".URL_ROOT."/www".URL_APPEDND."/storage/coursescur/".$cate_new_id."*";
                             exec($exec_var8);
                         }
                          //拷贝到coursescur文件夹
                         $exec_var6="cp  -rf ".$readme_paths." ". URL_ROOT."/www".URL_APPEDND."/storage/coursescur/";
                         $exec_var7="cp  -rf ".$tgz_paths." ". URL_ROOT."/www".URL_APPEDND."/storage/coursescur/";
                         exec($exec_var6);
                         exec($exec_var7);
                         
                         //删除缓存文件
                         $exec_temp="rm -rf /var/tmp/CGItemp*";
                         exec($exec_temp);
                }
           tb_close("imex_list.php"); 
      }elseif($action=='regain'){//恢复
     
                $readme_paths=get_readme_path('coursescur',$code);
                $tgz_paths=get_tgz_path('coursescur',$code);
                 
                if(file_exists($readme_paths) && file_exists($tgz_paths)){
                    
                    //获取到tgz文件
                    $pathinfo = pathinfo($tgz_paths); 
                    $dirname=$pathinfo['dirname'];
                    $get_filename=$pathinfo['basename']; 
                     
                    //解压tgz
                     $exec_var="cd ".$dirname."; tar  -zxf ".$get_filename;//解压tmp
                     exec($exec_var);
                     $exec_var1="cd /;tar -zxf  ".$tgz_paths." ".$mulu." -C ".URL_ROOT;   
                     exec($exec_var1);
                       
                      exec("chmod -R 777 ". $dirname."/*"); 
                      
                      $readme_var= file_get_contents($readme_paths);
                        $readme_data=  unserialize($readme_var);
                        $filename=$readme_data['filename'];
                        //获取文件夹名称
                       $files=  explode( '.',$filename); 
                       
                    //获取 course_category  数据
                            $get_category= file_get_contents($dirname."/". $files[0]."/course_category");
                            $category_data=  unserialize($get_category); 
                            $category_parent_id      =$category_data[0]['parent_id'];
                            $category_sn                  =$category_data[0]['sn'];
                            $category_name             =$category_data[0]['name'];
                            $category_code              =$category_data[0]['code'];
                            $category_tree_pos        =$category_data[0]['tree_pos'];
                            $category_children_count  =$category_data[0]['children_count'];
                            $category_auth_cat_child   =$category_data[0]['auth_cat_child'];
                            $category_last_updated_date      =$category_data[0]['last_updated_date'];        
                            $category_org_id             =$category_data[0]['org_id'];
                            $category_CourseDescription     =$category_data[0]['CourseDescription'];        
                             $category_CurriculumStandards =$category_data[0]['CurriculumStandards'];        
                            $category_AssessmentCriteria   =$category_data[0]['AssessmentCriteria'];
                            $category_TeachingProgress     =$category_data[0]['TeachingProgress'];
                            $category_StudyGuide                =$category_data[0]['StudyGuide'];
                            $category_TeachingGuide          =$category_data[0]['TeachingGuide'];
                             $category_InstructionalDesignEvaluation=$category_data[0]['InstructionalDesignEvaluation'];
                             $category_status=$category_data[0]['status'];  
 
                    //查询是否有该类 
                            $s="select `id` from `vslab`.`course_category` where name='".$category_name."'" ; 
                            $cate=Database::getval ( $s, __FILE__, __LINE__ ); 
                            if($cate){
                            //判断插入新的分类
                                     $sql_cate="UPDATE `course_category` SET `sn`='".$category_sn."',`name`='".$category_name."',`code`='".$category_code."',`tree_pos`='".$category_tree_pos."',`children_count`='".$category_children_count."',`auth_cat_child`='".$category_auth_cat_child."',`last_updated_date`='".$category_last_updated_date."',`org_id`='".$category_org_id."',`CourseDescription`='".$category_CourseDescription."',`CurriculumStandards`='".$category_CurriculumStandards."',`AssessmentCriteria`='".$category_AssessmentCriteria."',`TeachingProgress`='".$category_TeachingProgress."',`StudyGuide`='".$category_StudyGuide."',`TeachingGuide`='".$category_TeachingGuide."',`InstructionalDesignEvaluation`='".$category_InstructionalDesignEvaluation."',`status`='".$category_status."' WHERE id=".$cate;
                            }else{
                                     $sql_cate="INSERT INTO `course_category`(`id`, `parent_id`, `sn`, `name`, `code`, `tree_pos`, `children_count`, `auth_cat_child`, `last_updated_date`, `org_id`, `CourseDescription`, `CurriculumStandards`, `AssessmentCriteria`, `TeachingProgress`, `StudyGuide`, `TeachingGuide`, `InstructionalDesignEvaluation`, `status`) VALUES ('','".$category_parent_id."','".$category_sn."','".$category_name."','".$category_code."','".$category_tree_pos."','".$category_children_count."','".$category_auth_cat_child."', '".$category_last_updated_date."','".$category_org_id."','".$category_CourseDescription."','".$category_CurriculumStandards."','".$category_AssessmentCriteria."','".$category_TeachingProgress."','".$category_StudyGuide."','".$category_TeachingGuide."','".$category_InstructionalDesignEvaluation."','".$category_status."');";
                            }
                            $c_res = api_sql_query ( $sql_cate, __FILE__, __LINE__ ); 
                             
                             $cate_new_sql="select `id` from `vslab`.`course_category` where name='".$category_name."'" ; 
                             $cate_new_id=Database::getval ( $cate_new_sql, __FILE__, __LINE__ ); 
                              
                             //删除原有升级包
                             if($cate_new_id!==''){
                                $exec_varr="rm -rf ".URL_ROOT."/www".URL_APPEDND."/storage/coursescur/".$cate_new_id."*";
                                exec($exec_varr);
                             }
                             
 //course数据插入
                            $get_course= file_get_contents($dirname."/". $files[0]."/courses");
                            $course_data=  unserialize($get_course);
                            
                            for($j=0;$j<count($course_data);$j++){
                                   $course_code=$course_data[$j]['code'];
                                   $course_title=$course_data[$j]['title'];
                                   $course_topo=$course_data[$j]['description13'];
                                   
                                   $code_sql="select `code` from `vslab`.`course` where title='".$course_title."'" ; 
                                   $course_id=Database::getval ( $code_sql, __FILE__, __LINE__ ); 
                                     
                                   $description=str_replace("'","\'",$course_data[$j]['description']);
                                   $description1=str_replace("'","\'",$course_data[$j]['description1']);
                                   $description2=str_replace("'","\'",$course_data[$j]['description2']);
                                   $description3=str_replace("'","\'",$course_data[$j]['description3']);
                                   $description4=str_replace("'","\'",$course_data[$j]['description4']);
                                   $description5=str_replace("'","\'",$course_data[$j]['description5']);
                                   $description6=str_replace("'","\'",$course_data[$j]['description6']);
                                   $description7=str_replace("'","\'",$course_data[$j]['description7']);
                                   $description8=str_replace("'","\'",$course_data[$j]['description8']);
                                   //课程
                                        if($course_id!=''){//更新
                                            $c_sql="UPDATE `course` SET `org_id`='".$course_data[$j]['org_id']."',`title`='".$course_data[$j]['title']."',`category_code`='".$cate_new_id."',`status`='".$course_data[$j]['status']."',`visibility`='".$course_data[$j]['visibility']."',`is_audit_enabled`='".$course_data[$j]['is_audit_enabled']."',`is_shown`='".$course_data[$j]['is_shown']."',`is_subscribe_enabled`='".$course_data[$j]['is_subscribe_enabled']."',`pass_condition`='".$course_data[$j]['pass_condition']."',`expiration_date`='".$course_data[$j]['expiration_date']."',`start_date`='".$course_data[$j]['start_date']."',`disk_quota`='".$course_data[$j]['disk_quota']."',`subscribe`='".$course_data[$j]['subscribe']."',`unsubscribe`='".$course_data[$j]['unsubscribe']."',`last_visit`='".$course_data[$j]['last_visit']."',`creation_date`='".$course_data[$j]['creation_date']."',`created_user`='".$course_data[$j]['created_user']."',`last_edit`='".$course_data[$j]['last_edit']."',`credit`='".$course_data[$j]['credit']."',`credit_hours`='".$course_data[$j]['credit_hours']."',`is_free`='".$course_data[$j][' ']."',`fee`='".$course_data[$j]['fee']."',`apply_id`='".$course_data[$j][' ']."',`directory`='".$course_data[$j]['directory']."',`db_name`='".$course_data[$j]['db_name']."',`course_language`='".$course_data[$j]['course_language']."',`tutor_name`='".$course_data[$j]['tutor_name']."',`visual_code`='".$course_data[$j]['visual_code']."',`default_learing_days`='".$course_data[$j]['default_learing_days']."',`description`='".$description."',`description1`='".$description1."',`description2`='".$description2."',`description3`='".$description3."',`description4`='".$description4."',`description5`='".$description5."',`description6`='".$description6."',`description7`='".$description7."',`description8`='".$description8."',`description9`='".$course_data[$j]['description9']."',`description10`='".$course_data[$j]['description10']."',`description11`='".$course_data[$j]['description11']."',`description12`='".$course_data[$j]['description12']."',`description13`='".$course_data[$j]['description13']."',`nodeId`='".$course_data[$j]['nodeId']."' WHERE  title='".$course_title."'" ; 
                                        }else{//插入
                                            $c_sql="INSERT INTO `course`( `code`,`org_id`, `title`, `category_code`, `status`, `visibility`, `is_audit_enabled`, `is_shown`, `is_subscribe_enabled`, `pass_condition`, `expiration_date`, `start_date`, `disk_quota`, `subscribe`,`unsubscribe`, `last_visit`, `creation_date`, `created_user`, `last_edit`, `credit`, `credit_hours`, `is_free`, `fee`, `apply_id`, `directory`, `db_name`, `course_language`, `tutor_name`, `visual_code`, `default_learing_days`, `description`, `description1`, `description2`, `description3`, `description4`, `description5`, `description6`, `description7`, `description8`, `description9`, `description10`, `description11`, `description12`, `description13`, `nodeId`) VALUES ( '".$course_code."','".$course_data[$j]['org_id']."',  '".$course_data[$j]['title']."',  '".$cate_new_id."',  '".$course_data[$j]['status']."',  '".$course_data[$j]['visibility']."',    '".$course_data[$j]['is_audit_enabled']."',  '".$course_data[$j]['is_shown']."',  '".$course_data[$j]['is_subscribe_enabled']."', '".$course_data[$j]['pass_condition']."', '".$course_data[$j]['expiration_date']."',  '".$course_data[$j]['start_date']."',  '".$course_data[$j]['disk_quota']."', '".$course_data[$j]['subscribe']."',   '".$course_data[$j]['unsubscribe']."',  '".$course_data[$j]['last_visit']."',   '".$course_data[$j]['creation_date']."',  '".$course_data[$j]['created_user']."',  '".$course_data[$j]['last_edit']."',  '".$course_data[$j]['credit']."', '".$course_data[$j]['credit_hours']."', '".$course_data[$j]['is_free']."',   '".$course_data[$j]['fee']."',  '".$course_data[$j]['apply_id']."',  '".$course_data[$j]['directory']."', '".$course_data[$j]['db_name']."',  '".$course_data[$j]['course_language']."', '".$course_data[$j]['tutor_name']."',  '".$course_data[$j]['visual_code']."',  '".$course_data[$j]['default_learing_days']."',  '".$description."',  '".$description1."',   '".$description2."',  '".$description3."',   '".$description4."',  '".$description5."','".$description6."',  '".$description7."',  '".$description8."','".$course_data[$j]['description9']."',  '".$course_data[$j]['description10']."',  '".$course_data[$j]['description11']."',  '".$course_data[$j]['description12']."',  '".$course_data[$j]['description13']."',   '".$course_data[$j]['nodeId']."')";
                                        }
                                        $course_res = api_sql_query ( $c_sql, __FILE__, __LINE__ );
                                        
                                        //移动课程文件夹
                                        $new_code=DATABASE::getval("select code from course  where title='".$course_title."'", __FILE__, __LINE__);
                                        
                                        if($new_code!==$course_code){
                                            $exec_chmod="chmod -R 777 ". URL_ROOT."/www".URL_APPEDND."/storage/courses/".$course_code;
                                            exec($exec_chmod);exec($exec_chmod."/*");
                                            $exec_var9=" mv -f ".URL_ROOT."/www".URL_APPEDND."/storage/courses/".$course_code." ".URL_ROOT."/www".URL_APPEDND."/storage/courses/".$new_code;
                                            exec($exec_var9);
                                        }else{
//                                            echo "code 相同";
                                        }
                                        
                                        //课件  courseware sql数据
                                        if($course_res){
                                                $get_courseware= file_get_contents($dirname."/". $files[0]."/".$course_code."/courseware");
                                                $courseware_data=  unserialize($get_courseware);
                                                for($m=0;$m<count($courseware_data);$m++) {
                                                            $courseware_cc                    = $courseware_data[$m]['cc'];
                                                            $courseware_cw_type          = $courseware_data[$m]['cw_type'];
                                                            $courseware_path                = $courseware_data[$m]['path'];
                                                            $courseware_title                 = $courseware_data[$m]['title'];
                                                            $courseware_size                 = $courseware_data[$m]['size'];
                                                            $courseware_comment        = $courseware_data[$m]['comment'];
                                                            $courseware_attribute         = $courseware_data[$m]['attribute'];
                                                            $courseware_display_order= $courseware_data[$m]['display_order'];
                                                            $courseware_created_date = $courseware_data[$m]['created_date'];
                                                            $courseware_visibility         = $courseware_data[$m]['learning_time'];


                                                              $cid_sql="select `id` from `vslab`.`crs_courseware` where `title`='".$courseware_title."'" ; 
                                                              $c_id=Database::getval ( $cid_sql, __FILE__, __LINE__ );
                                                              if($c_id!=''){
                                                                   $cc_sql="UPDATE `crs_courseware` SET  `cc`='".$new_code."',`cw_type`='".$courseware_cw_type."',`path`='".$courseware_path."',`size`='".$courseware_size."',`comment`='".$courseware_comment."',`attribute`='".$courseware_attribute."',`display_order`='".$courseware_display_order."',`created_date`='".$courseware_created_date."',`visibility`='".$courseware_visibility."',`learning_time`='".$courseware_learning_time."' WHERE  id='".$c_id."'";
                                                              }else{
                                                                   $cc_sql="INSERT INTO `crs_courseware`(`id`, `cc`, `cw_type`, `path`, `title`, `size`, `comment`, `attribute`, `display_order`, `created_date`, `visibility`, `learning_time`) VALUES ('','".$new_code."','".$courseware_cw_type."','".$courseware_path."','".$courseware_title."','".$courseware_size ."','".$courseware_comment."','".$courseware_attribute."','".$courseware_display_order."','".$courseware_created_date."','".$courseware_visibility."','".$courseware_learning_time."')";
                                                              }
                                                              $cc_res= api_sql_query (  $cc_sql, __FILE__, __LINE__ );
                                                }
                                                
                                        }
                                       //拓扑
                                       $get_networkmap= file_get_contents($dirname."/". $files[0]."/".$course_code."/networkmap");
                                       $networkmap_data=  unserialize($get_networkmap);  
                                       for($k=0;$k<count($networkmap_data);$k++){
                                                $networkmap_id	         =$networkmap_data[0]['id'];
                                                $networkmap_name       =$networkmap_data[0]['name'];
                                                $networkmap_describe  =$networkmap_data[0]['describe'];	
                                                $networkmap_content    =$networkmap_data[0]['content'];	 
                                                
                                                $networkmap_xml =  str_replace('"', '\"', $networkmap_data[0]['xml']);  //转义xml
                                                
                                                if($course_topo==$networkmap_name){
                                                        $networkmap_sql="select `id` from `vslab`.`networkmap` where name='".$course_topo."'" ; 
                                                        $networkmap_id=Database::getval ( $networkmap_sql, __FILE__, __LINE__ );
                                                            if($networkmap_id!=''){
                                                                    $networkmap_sql="UPDATE `networkmap` SET   `describe`='".$networkmap_describe."',`content`='".$networkmap_content."',`xml`='".$networkmap_xml."' WHERE id='".$networkmap_id."'";
                                                            }else{
                                                                    $networkmap_sql="INSERT INTO `networkmap`(`id`, `name`, `describe`, `content`, `xml`) VALUES ('','".$networkmap_name."','".$networkmap_describe."','".$networkmap_content."','".$networkmap_xml."')";
                                                            }
                                                       $networkmap_res = api_sql_query ( $networkmap_sql, __FILE__, __LINE__ );
                                                       
                                                            if($networkmap_res){
                                                 //模板
                                                             $get_vmdisk= file_get_contents($dirname."/". $files[0]."/".$course_code."/vmdisk");
                                                             $vmdisk_data=  unserialize($get_vmdisk); 
                                                                    for($h=0;$h<count($vmdisk_data);$h++){
                                                                        $vmdisk_id =$vmdisk_data[$h]['id'];
                                                                        $vmdisk_category=$vmdisk_data[$h]['category'];
                                                                        $vmdisk_name	=$vmdisk_data[$h]['name'];
                                                                        $vmdisk_version=$vmdisk_data[$h]['version'];
                                                                        $vmdisk_size	=$vmdisk_data[$h]['size'];
                                                                        $vmdisk_active=$vmdisk_data[$h]['active'];
                                                                        $vmdisk_ISO	=$vmdisk_data[$h]['ISO'];
                                                                        $vmdisk_boot	=$vmdisk_data[$h]['boot'];
                                                                        $vmdisk_memory=$vmdisk_data[$h]['memory'];	
                                                                        $vmdisk_CPU_number=$vmdisk_data[$h]['CPU_number'];	
                                                                        $vmdisk_NIC_type =$vmdisk_data[$h]['NIC_type'];
                                                                        $vmdisk_CD_mirror=$vmdisk_data[$h]['CD_mirror'];	
                                                                        $vmdisk_mac	=$vmdisk_data[$h]['mac'];
                                                                        $vmdisk_platform=$vmdisk_data[$h]['platform'];	
                                                                        $vmdisk_vlan	=$vmdisk_data[$h]['vlan'];
                                                                        $vmdisk_nodeId	=$vmdisk_data[$h]['nodeId'];
                                                                        $vmdisk_server_ip=$vmdisk_data[$h]['server_ip'];	
                                                                        $vmdisk_group_id=$vmdisk_data[$h]['group_id'];	
                                                                        $vmdisk_type=$vmdisk_data[$h]['type'];
                                                                        $vmdisk_task_id	=$vmdisk_data[$h]['task_id'];
                                                                        $vmdisk_mod_type=$vmdisk_data[$h]['mod_type'];

                                                                        $vmdisk_sql="select `id` from `vslab`.`vmdisk` where name='".$vmdisk_name."'" ; 
                                                                        $vmdisk_id=Database::getval ( $vmdisk_sql, __FILE__, __LINE__ );

                                                                        if($vmdisk_id){
                                                                            $networkmap_sql="UPDATE `vmdisk` SET  `category`='".$vmdisk_category."',`name`='".$vmdisk_name."',`version`='".$vmdisk_version."',`size`='".$vmdisk_size."',`active`='".$vmdisk_active."',`ISO`='".$vmdisk_ISO."',`boot`='".$vmdisk_boot."',`memory`='".$vmdisk_memory."',`CPU_number`='".$vmdisk_CPU_number."',`NIC_type`='".$vmdisk_NIC_type."',`CD_mirror`='".$vmdisk_CD_mirror."',`mac`='".$vmdisk_mac."',`platform`='".$vmdisk_platform."',`vlan`='".$vmdisk_vlan."',`nodeId`='".$vmdisk_nodeId."',`server_ip`='".$vmdisk_server_ip."',`group_id`='".$vmdisk_group_id."',`type`='".$vmdisk_type."',`task_id`='".$vmdisk_task_id."',`mod_type`='".$vmdisk_mod_type."' WHERE  `id`='".$vmdisk_id."'";
                                                                        }else{
                                                                            $networkmap_sql="INSERT INTO `vmdisk`(`id`, `category`, `name`, `version`, `size`, `active`, `ISO`, `boot`, `memory`, `CPU_number`, `NIC_type`, `CD_mirror`, `mac`, `platform`, `vlan`, `nodeId`, `server_ip`, `group_id`, `type`, `task_id`, `mod_type`) VALUES ('','". $vmdisk_category."','".$vmdisk_name."','".$vmdisk_version."','".$vmdisk_size."','".$vmdisk_active."','".$vmdisk_ISO."','".$vmdisk_boot."','".$vmdisk_memory."','".$vmdisk_CPU_number."','".$vmdisk_NIC_type."','".$vmdisk_CD_mirror."','".$vmdisk_mac."','".$vmdisk_platform."','".$vmdisk_vlan."','".$vmdisk_nodeId."','".$vmdisk_server_ip."','".$vmdisk_group_id."','".$vmdisk_type."','".$vmdisk_task_id."','".$vmdisk_mod_type."')";
                                                                        }
                                                                        $networkmap_res = api_sql_query ( $networkmap_sql, __FILE__, __LINE__ );

                                                                  } 
                                                        }
                                                }         
                                       }
                            }
                            
                            
                         //删除解压文件
                         $exec_var5="rm  -rf ".$dirname."/". $files[0]." ".$dirname . URL_ROOT." ".$dirname."/readme";
                         exec($exec_var5);
                }
                
        //删除缓存文件
           $exec_temp="rm -rf /var/tmp/CGItemp*";
           exec($exec_temp);
           
           tb_close("imex_list.php"); 
     }
}
if($_GET['action']=="deleteTar" && getgpc("cid",'G')!==''){
      $cid=getgpc("cid",'G');
     $file_paths=URL_ROOT."/www".URL_APPEDND."/storage/coursesbak";//local 
     $deleteVar="rm  -rf ".$file_paths ."/".$cid."*";
     exec($deleteVar);
     
     $file_paths1=URL_ROOT."/www".URL_APPEDND."/storage/coursesnew";
     $deleteVar1="rm  -rf ".$file_paths1 ."/".$cid."*";
     exec($deleteVar1);
     tb_close("imex_list.php"); 
     }
function courses_number($id) {
    $count = "";
    $s="SELECT COUNT( title ) FROM  `course` WHERE category_code =".$id;
    $count = "<span style='text-align:center'>".Database::getval( $s, __FILE__, __LINE__ )."</span>";
    return $count;
}

//打包/重新打包,下载
function action_Category($id) {
    $tgz_path=get_tgz_path('coursesbak',$id);
    $html=" ";  
    if(file_exists($tgz_path)){
        $html .= link_button ( 'save_zip.gif','重新打包','tarCourses.php?courses_export=' . $id,'50%','50%' ); 
        $html .='<span style="text-align:center">' ;
        $html .= link_button ( 'arrow_down_0.gif', '下载', 'export.php?courses_export=' . $id,'50%','50%');
        $html .=confirm_href ( 'delete.gif', '你确定删除打包文件吗？', '删除打包文件', 'imex_list.php?action=deleteTar&cid=' . $id )."删除";
        $html .='</span>';
     }else{
        $html.= link_button ( 'zip_save.gif','打包','tarCourses.php?courses_export=' . $id,'50%','50%' ); 
     }
   return $html;
}

//信息查看/升级/下载升级包/无 
function update_filter($id){
    $html = "";
    
     $readme_path=get_readme_path('coursesnew',$id);
     $tgz_path=get_tgz_path('coursesnew',$id);
     
       $html .='<span style="text-align:center">';
    if(file_exists($readme_path) && file_exists($tgz_path)){
       $html.=link_button ( 'info3.gif', '查看', 'bale_info.php?action=info&category_id='.$id,'80%','70%' )."&nbsp;"; 
       $html .= confirm_href ( 'folder_up.gif', '你确定要升级吗？', '升级', 'imex_list.php?action=update&code=' . $id).'升级&nbsp;';
       $html .=link_button ( 'folder_zip.gif', '下载升级包', 'tgz_export.php?code='.$id,'25%','30%' )."&nbsp;"; 
    }else{
        $html.=Display::return_icon (  'wrong.gif','无升级包' )."无升级包";
    } 
    $html.='</span>';
    return $html;
}

//信息查看/恢复/无当前课程备份包
function info($id) {
    $html = "";
    $readme_path=get_readme_path('coursescur',$id);
     $tgz_path=get_tgz_path('coursescur',$id);
     
    $html .='<span style="text-align:center">';
    if(file_exists($readme_path) && file_exists($tgz_path)){
       $html.=link_button ( 'info3.gif', '查看', 'current_info.php?category_id='.$id,'80%','70%')."&nbsp;";
       $html .= confirm_href ( 'kaboodleloop.gif', '你确定要恢复吗？', '恢复', 'imex_list.php?action=regain&code=' . $id).'恢复&nbsp;';
    }else{
            $html.=Display::return_icon (  'wrong.gif','无当前课程备份包' )."无备份包";
    }
    $html.='</span>';
    return $html;
}
function ExportCategory($id) {
    $html = "";
    $html .='<span style="text-align:center">' .link_button ( 'enroll.gif', '', 'export.php?courses_export=' . $id,'50%','50%').'</span>';
    return $html;
}
function get_sqlwhere() {
    global $restrict_org_id, $objCrsMng;
    $sql_where = "";
    if (is_not_blank ( $_GET ['keyword'] )) {
        $keyword = Database::escape_string (getgpc("keyword","G"), TRUE );
        $sql_where .= " AND (name LIKE '%" . trim ( $keyword ) . "%' OR id LIKE '%" . trim ( $keyword ) . "%')";
    }

    if (is_not_blank ( $_GET ['id'] )) {
        $sql_where .= " AND id=" . Database::escape (intval(getgpc ( 'id', 'G' )) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else
        return "";
}
function get_number_of_course_category() {
    $category_table = Database::get_main_table ( course_category );
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM $category_table AS t1  where parent_id !=0 ";
     $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " and " . $sql_where;
    //echo $sql;exit;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_course_category_data($from, $number_of_items, $column, $direction) {
    $course_category = Database::get_main_table ( course_category);
    $sql = "SELECT name ,id ,id ,id ,id  FROM $course_category where parent_id !=0 ";
    $sql_where = get_sqlwhere ();
    if ($sql_where) {$sql .= $sql_where;}

    $sql .= " LIMIT $from,$number_of_items";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $c= array ();
    while ( $c = Database::fetch_row ( $res) ) {
        $cc[] = $c;
    }
    return $cc;
}

$objCrsMng = new CourseManager ();
$category_tree = $objCrsMng->get_all_categories_tree ( TRUE );

function _get_course_count($parent_id) {
    $GLOBALS ['objCrsMng']->sub_category_ids = array ();
    $sub_category_ids = $GLOBALS ['objCrsMng']->get_sub_category_tree_ids ( $parent_id, TRUE );
    $tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
    $sql = "SELECT COUNT(*) FROM " . $tbl_course . " WHERE category_code " . Database::create_in ( $sub_category_ids );
    //echo $sql;
    return Database::get_scalar_value ( $sql );
}
  
$table = new SortableTable ( 'cc', 'get_number_of_course_category', 'get_course_category_data', 2, NUMBER_PAGE );
$table->set_additional_parameters ( $parameters );

$table->set_header (0, get_lang ( 'CategoryName' ),false, null ,array());
$table->set_header (1, "当前课程数量",false, null ,array('width'=>'10%')); 
$table->set_header(2, "当前课程信息", false, null ,array('width'=>'20%') );
$table->set_header(3, "课件升级包信息", false, null ,array('width'=>'20%') );

$table->set_header(4, "操作", false, null ,array('width'=>'20%') );
//$table->set_header(5, "下载", false, null ,array('width'=>'15%') );

$table->set_column_filter ( 1, 'courses_number' );
$table->set_column_filter ( 2, 'info' );
$table->set_column_filter ( 3, 'update_filter' );
$table->set_column_filter ( 4, 'action_Category' );
//$table->set_column_filter ( 5, 'ExportCategory' ); 

//$table->display ();



//Display::display_footer ( TRUE );
?>

<aside id="sidebar" class="column systeminfo open">
    <div id="flexButton" class="closeButton close"></div>
</aside>

<section id="main" class="column">
   <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php" title="平台首页">平台首页</a> &gt; 
        <a href="<?=URL_APPEDND;?>/main/admin/course_license.php" title="license管理">license管理</a> &gt; <span title="课件资源库管理">课件资源库</span></h4>

    <div class="managerTool">
        <span class="searchtxt right"><?php echo '&nbsp;&nbsp;' . link_button ( 'top_6.gif', '导入课件包', '../import_export/import.php', '80%', '70%' );
echo '&nbsp;&nbsp;' . link_button ( 'aw2.gif', '立即更新', '../import_export/import.php', '80%', '70%' );
?>  </span>
    </div>
    <article class="module width_full hidden">

                <?php $table->display ();?>
    </article>
</section>
</body>
</html>
