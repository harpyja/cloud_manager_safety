<?php
/**
 *by changzf
 *on 2012/07/11
 *This is a page to export a single course
 */
header("content-type:text/html;charset=utf-8");
include_once ("../../inc/global.inc.php");
Display::display_header (null, FALSE );

//$path = getcwd();//获取当前系统目录
if($_POST['sub'])
{
    
    $tname = $_FILES["ufile"]["tmp_name"];
    $fname = $_FILES["ufile"]["name"];
 
    $path=URL_ROOT."/www/lms";  
//    $raw_file='/var/www/';//local
    $raw_file='/tmp/mnt/vmdisk/images/99/';   //server
    
    $name=explode('.',$fname);
    $tar=substr(strrchr($fname,"."),1);
 
    if($tar=='tar'){

        move_uploaded_file($tname, "$path/storage/$fname"); 
        
//解压tar包
       exec("cd $path/storage ; tar -xvf $path/storage/$fname");   
       exec("chmod -R 777  $path/storage/$name[0] ");   
        
//删除tar 
       exec("rm $path/storage/$fname");   

 //获取course_name，netmap_name，（course_category）
        $resss= file_get_contents($path.'/storage/'.$name[0].'/datas');
        $resultss=  unserialize($resss); //var_dump($resultss); 
       
//获取分类数据
        $ccc = file_get_contents($path.'/storage/'.$name[0].'/course_category.txt');
        $aaa=  unserialize($ccc); //var_dump($aaa);  
        
//查询是否有该类
        $category=$resultss['course_category'];
        $s='select `id` from `vslab`.`course_category` where name="'.$category.'"';
        $cate=Database::getval ( $s, __FILE__, __LINE__ );
 
        if($cate==''){    
//判断插入新的分类
            $sq = "INSERT INTO  `vslab`.`course_category` ( `id` , `parent_id` , `sn` , `name` , `code` ,`tree_pos` , `children_count` ,`auth_cat_child` ,`last_updated_date` ,`org_id` ,`CourseDescription` ,`CurriculumStandards` ,`AssessmentCriteria` ,`TeachingProgress` ,`StudyGuide` ,`TeachingGuide` ,`InstructionalDesignEvaluation`)VALUES
(''".",'$aaa[1]'".",'','$aaa[3]'".",'$aaa[4]'".",'$aaa[5]'".",'$aaa[6]'".",'$aaa[7]'".",'$aaa[8]'".",'$aaa[9]'".",'$aaa[10]'".",'$aaa[11]'".",'$aaa[12]'".",'$aaa[13]'".",'$aaa[14]'".",'$aaa[15]'".",'$aaa[16]');";
            $res = api_sql_query ( $sq, __FILE__, __LINE__ );

            exec("rm -r ".URL_ROOT."/www/lms/storage/DATA/temp/"); 
         }
 //查出'新'分类ID
        $s1='select `id` from `vslab`.`course_category` where name="'.$aaa[3].'"';
        $cate1=Database::getval ( $s, __FILE__, __LINE__ ); 
//判断是否该课程
      $course=$resultss['course_name'];
        $sql="select `code` from `course` where `title`='".$course."'"; 
        $bool_course=  Database::getval($sql,__FILE__, __LINE__ );
        
       if(!$bool_course){    
//插入该类课程数据
           //local
//        exec("mysql -u".DB_USER." -p".DB_PWD." ".DB_NAME."< $path/storage/$name[0]/courses.sql");  
          //server
           exec("mysql -u".DB_USER." ".DB_NAME."< $path/storage/$name[0]/courses.sql"); 
//更新课程的所属分类
           $sql_update="update course set category_code=".$cate1." where title='".$course."'";
           api_sql_query ( $sql_update, __FILE__, __LINE__ );
//课程文件 
          $corswr= file_get_contents($path.'/storage/'.$name[0].'/crs_courseware.txt');
          $corswrs=  unserialize($corswr);  
          
          $sql_ware="select `cc` from `course` where `title`='".$corswrs[0]['title']."'"; 
          $bool_crsware=  Database::getval($sql_ware,__FILE__, __LINE__ );
          if(!$bool_crsware){
          $sql1="insert into crs_courseware(`id`, `cc`, `cw_type`, `path`, `title`, `size`, `comment`, `attribute`, `display_order`, `created_date`, `visibility`, `learning_time`) VALUES ('','{$corswrs[0]['cc']}', '{$corswrs[0]['cw_type']}', '{$corswrs[0]['path']}', '{$corswrs[0]['title']}', '{$corswrs[0]['size']}', '{$corswrs[0]['comment']}', '{$corswrs[0]['attribute']}', '{$corswrs[0]['display_order']}', '{$corswrs[0]['created_date']}', '{$corswrs[0]['visibility']}', '{$corswrs[0]['learning_time']}')";
          $resss=api_sql_query($sql1);
          }
 
            $bools=file_exists($path."/storage/courses/".$name[0]);        
            if(!$bools){
               exec("cp -rf $path/storage/$name[0]/$name[0] $path/storage/courses/");
               exec("cd $path/storage/courses/");  
               exec("chmod -R 777 $name[0] ");
            }
 
        $bool_courses=  Database::getval("select `code` from `course` where `title`='".$course."'",__FILE__, __LINE__ );
           
        if($bool_courses){
            $net_id_sql="select id from networkmap where name='".$resultss['netmap_name']."'"; 
            $bool_topo=  Database::getval($net_id_sql);    
  //是否有该拓扑 
            if(!$bool_topo){   
                $topo= file_get_contents($path.'/storage/'.$name[0].'/networkmap.txt');  
                $topos=  unserialize($topo); 
                $xml=  str_replace('"', '\"', $topos[0]['xml']);  //转义xml
  //插入topo记录         
                $sql_topo="insert into networkmap (`id`, `name`, `describe`, `content`, `xml`) VALUES ('','{$topos[0]['name']}', '{$topos[0]['describe']}', '{$topos[0]['content']}', '{$xml}')";
 
                $r1=api_sql_query($sql_topo);
                
                if($r1){
      //获取模板sql信息
                    $vm= file_get_contents($path.'/storage/'.$name[0].'/vmdisk.txt');   
                    $vms=  unserialize($vm);
                    for($i=0;$i<count($vms);$i++){
                      $vm_nmae=$vms[$i]['name'];
                      $sql_vm="select id from vmdisk where name='".$vm_nmae."'"; 
                      $bool_vm=  Database::getval($sql_vm); 
                      if(!$bool_vm){
    //插入模板记录
                        $sql_vms="insert into vmdisk (`id`, `category`, `name`, `version`, `size`, `active`, `ISO`, `boot`, `memory`, `CPU_number`, `NIC_type`, `CD_mirror`, `mac`, `platform`, `vlan`, `nodeId`, `server_ip`, `group_id`, `type`, `task_id`, `mod_type`) VALUES ('','{$vms[$i]['category']}','{$vms[$i]['name']}','{$vms[$i]['version']}','{$vms[$i]['size']}','{$vms[$i]['active']}','{$vms[$i]['ISO']}','{$vms[$i]['boot']}','{$vms[$i]['memory']}','{$vms[$i]['CPU_number']}','{$vms[$i]['NIC_type']}','{$vms[$i]['CD_mirror']}','{$vms[$i]['mac']}','{$vms[$i]['platform']}','{$vms[$i]['vlan']}','{$vms[$i]['nodeId']}','{$vms[$i]['server_ip']}','{$vms[$i]['group_id']}','{$vms[$i]['type']}','{$vms[$i]['task_id']}','{$vms[$i]['mod_type']}')";
                        $r=api_sql_query($sql_vms); 
    //模板文件 
                        if($r){
                            exec("cp -rf $path/storage/$name[0]/$vm_nmae.raw $raw_file");   
                        }
                      }     
                    }
                }

              }
             
            }
        }
//delete解压缩文件
        exec("rm -rf $path/storage/$name[0]"); 
        tb_close ('../course/course_list.php'); 
        exit;
    }else{
        $a='<span style="font-size:14px;color:red;border:1px;float:left;line-height:14px;">&nbsp;上传的文件名只限于: *.tar</span>';
    }

}
?>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
</head>
<style type="text/css">
	table{border-collapse:collapse;margin:0;}
	table,table tr td{border:1px dashed #CCCCCC;padding:0 10px 0 10px;}
</style>
<body>
    <div class="import" style="color:white;font-size:16px;">&nbsp;导入课件压缩包(tar)</div>
    <div style="height:10px"></div>
    <form action="" method="post" enctype="multipart/form-data">
        <table cellpadding="1" cellspacing="1" border="0" align="center" width="100%">
            <tr>
                <td align="right" width="20%" bgcolor="#EEE" height="50px">选择导入文件:</td>
                <td><input type="file" name="ufile"><?php echo $a;?></td>
            </tr>
            <tr>
                <td align="right" width="20%" bgcolor="#EEE" height="50px">&nbsp;</td>
                <td>&nbsp;<input type="submit" name="sub" value="确定" class="inputSubmit">
&nbsp;<button type="button" class="cancel" onClick="javascript:self.parent.tb_remove();" name="cancle">取消</button> 

            </tr>
        </table>
    </form>
</body>
</html>
