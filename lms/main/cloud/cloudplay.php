<?php
header("Content-type:text/html;charset=utf-8");
include_once ("../inc/global.inc.php"); 
echo '<title>'.get_setting ( 'siteName' ).'</title>';
$w=intval(trim($_GET['w']));
$h=intval(trim($_GET['h']));
$f=trim($_GET['f']);
$user_id=trim($_GET['user']);
$lesson_id=trim($_GET['lesson']);
$type=trim($_GET['type']);
if(!$h){
   $h='768';
}
if(!$w){
   $w='1024';
}
 
if($f){
    if( isset($_GET['momi']) && $_GET['momi']='qiants'){
        if($lesson_id){
            $course_name=  Database::getval('select title from course where code='.$lesson_id,__FILE__,__LINE__);
            echo  "<p align=left>".$course_name."</p><div style='text-align:center;'>";
            echo '<video width="95%" height="90%" controls="controls">
  		<source src="'.URL_APPEDND.'/storage/courses/'.$lesson_id.'/document/flv/'.$f.'.mp4" type="video/mp4">
  		your browser does not support the video tag.
	  </video></div>';
        } 
    }else{
        echo '<video width="'.$w.'" height="'.$h.'" controls="controls">
  		<source src="'.URL_APPEDND.'/storage/snapMp4/'.$f.'.mp4" type="video/mp4">
  		your browser does not support the video tag.
	  </video>';
    }
          
}elseif($user_id && $lesson_id && $type){
             if( isset($_GET['momi']) && $_GET['momi']='qiant'){
                 if($lesson_id!==''){
//                     $dir=URL_ROOT.'/www'.URL_APPEDND.'/storage/courses/'.$lesson_id."/document/flv/";
//                     $arr=myreaddir($dir);
                     
                     $sql="SELECT `id`,`cc`,`cw_type`,`path`,`title` FROM `crs_courseware` WHERE `cc` = ".$lesson_id." AND cw_type = 'media' AND visibility =1";
                     $arrs=api_sql_query_array_assoc($sql,__FILE__, __LINE__);
//                     var_dump($arrs);
                     $arr=array();
                     foreach($arrs as  $k=>$v){
                         $name=str_replace('.mp4','',$v['title']);
                         $arr1['title']=$name;
                         $arr1['id']=$v['id'];
                         $arr1['cc']=$v['cc'];
                         $arr1['cw_type']=$v['cw_type'];
                         $arr1['path']=$v['path'];
                         $arr[]=$arr1;
                     }
//                     var_dump($arr);
                     $counts=count($arr); 
                    $pages=ceil($counts/2);
                    if($counts%2==1){
                         if($pages>1){
                             for($i=1;$i<=$pages-1;$i++){
                                 $qians=$i*2-2;
                                 $hous=$i*2-1;
                                 ?>
                                <table width="100%" border=0 cellpadding="2px" cellspacing="1px">
                                  <tr>
                                  <td width="50%"><?=$arr[$qians]['title']?>
                                        <video width="100%" height="" controls="controls">
                                            <source src="<?=URL_APPEDND?>/storage/courses/<?=$lesson_id?>/document<?=$arr[$qians]['path']?>" type="video/mp4">
                                                        your browser does not support the video tag.
                                        </video>
                                  </td>
                                  <td width="50%"><?=$arr[$hous]['title']?>
                                        <video width="100%" height="" controls="controls">
                                                        <source src="<?=URL_APPEDND?>/storage/courses/<?=$lesson_id?>/document<?=$arr[$hous]['path']?>" type="video/mp4">
                                                        your browser does not support the video tag.
                                        </video>
                                  </td>
                                  </tr>
                                </table>
                             <?php }
                             $end=$pages*2-2;
                             ?>
                           <table width="100%" border=0 cellpadding="2px" cellspacing="1px">
                                <tr>
                                  <td width="50%"><?=$arr[$end]['title']?>
                                        <video width="100%" height="" controls="controls">
                                                        <source src="<?=URL_APPEDND?>/storage/courses/<?=$lesson_id?>/document/<?=$arr[$end]['path']?>" type="video/mp4">
                                                        your browser does not support the video tag.
                                        </video>
                                  </td>
                                  <td>
                                      &nbsp;
                                  </td>
                                </tr>
                            </table>
                                 <?php
                         }else{?>
                           <table width="100%" border=0 cellpadding="2px" cellspacing="1px">
                                <tr>
                                  <td width="50%"><?=$arr[0]['title']?>
                                        <video width="100%" height="" controls="controls">
                                                        <source src="<?=URL_APPEDND?>/storage/courses/<?=$lesson_id?>/document<?=$arr[0]['path']?>" type="video/mp4">
                                                        your browser does not support the video tag.
                                        </video>
                                  </td>
                                  <td>
                                      &nbsp;
                                  </td>
                                </tr>
                            </table>
                          <?php 
                         }
                    }else{
                        if($pages>1){
                            for($i=1;$i<=$pages;$i++){
                                $qian=$i*2-1;
                                $hou=$i*2;
                                $hou1=$hou-1;
                                $qian1=$qian-1;
                                ?>
                                <table width="100%" border=0 cellpadding="2px" cellspacing="1px">
                                <tr>
                                  <td width="50%"><?=$arr[$qian1]['title']?>
                                        <video width="100%" height="" controls="controls">
                                                        <source src="<?=URL_APPEDND?>/storage/courses/<?=$lesson_id?>/document<?=$arr[$qian1]['path']?>" type="video/mp4">
                                                        your browser does not support the video tag.
                                        </video>
                                  </td>
                                  <td width="50%"><?=$arr[$hou1]['title']?>
                                        <video width="100%" height="" controls="controls">
                                                        <source src="<?=URL_APPEDND?>/storage/courses/<?=$lesson_id?>/document<?=$arr[$hou1]['path']?>" type="video/mp4">
                                                        your browser does not support the video tag.
                                        </video>
                                  </td>
                                </tr>
                            </table>
                             <?php } 
                        }else{
                            $qian1=1-1;
                            $hou1=2-1;
                            ?>
                            <table width="100%" border=0 cellpadding="2px" cellspacing="1px">
                                <tr>
                                  <td width="50%"><?=$arr[$qian1]['title']?>
                                        <video width="100%" height="" controls="controls">
                                                        <source src="<?=URL_APPEDND?>/storage/courses/<?=$lesson_id?>/document<?=$arr[$qian1]['path']?>" type="video/mp4">
                                                        your browser does not support the video tag.
                                        </video>
                                  </td>
                                  <td width="50%"><?=$arr[$hou1]['title']?>
                                        <video width="100%" height="" controls="controls">
                                                        <source src="<?=URL_APPEDND?>/storage/courses/<?=$lesson_id?>/document<?=$arr[$hou1]['path']?>" type="video/mp4">
                                                        your browser does not support the video tag.
                                        </video>
                                  </td>
                                </tr>
                            </table>
                        <?php 
                        }

                         }
                    } 
//======================================================================================================        
             }else{
                    $sql="select id,filename,snapshotdesc from snapshot where user_id=$user_id and lesson_id=$lesson_id and status=0 and type=$type";
                    $res = api_sql_query_array_assoc( $sql, __FILE__, __LINE__ );
                    $counts=count($res);//总数量
                    $pages=ceil($counts/2); 
                    if($counts%2==1){
                         if($pages>1){
                             for($i=1;$i<=$pages-1;$i++){
                                 $qians=$i*2-2;
                                 $hous=$i*2-1;
                                 ?>
                                <table width="100%" border=0 cellpadding="2px" cellspacing="1px">
                                  <tr>
                                  <td width="50%"><?=$res[$qians]['snapshotdesc']?>
                                        <video width="100%" height="" controls="controls">
                                                        <source src="<?=URL_APPEDND?>/storage/snapMp4/<?=$res[$qians]['filename']?>.mp4" type="video/mp4">
                                                        your browser does not support the video tag.
                                        </video>
                                  </td>
                                  <td width="50%"><?=$res[$hous]['snapshotdesc']?>
                                        <video width="100%" height="" controls="controls">
                                                        <source src="<?=URL_APPEDND?>/storage/snapMp4/<?=$res[$hous]['filename']?>.mp4" type="video/mp4">
                                                        your browser does not support the video tag.
                                        </video>
                                  </td>
                                  </tr>
                                </table>
                             <?php }
                             $end=$pages*2-2;
                             ?>
                           <table width="100%" border=0 cellpadding="2px" cellspacing="1px">
                                <tr>
                                  <td width="50%"><?=$res[$end]['snapshotdesc']?>
                                        <video width="100%" height="" controls="controls">
                                                        <source src="<?=URL_APPEDND?>/storage/snapMp4/<?=$res[$end]['filename']?>.mp4" type="video/mp4">
                                                        your browser does not support the video tag.
                                        </video>
                                  </td>
                                  <td>
                                      &nbsp;
                                  </td>
                                </tr>
                            </table>
                                 <?php
                         }else{?>
                           <table width="100%" border=0 cellpadding="2px" cellspacing="1px">
                                <tr>
                                  <td width="50%"><?=$res[0]['snapshotdesc']?>
                                        <video width="100%" height="" controls="controls">
                                                        <source src="<?=URL_APPEDND?>/storage/snapMp4/<?=$res[0]['filename']?>.mp4" type="video/mp4">
                                                        your browser does not support the video tag.
                                        </video>
                                  </td>
                                  <td>
                                      &nbsp;
                                  </td>
                                </tr>
                            </table>
                          <?php 
                         }
                    }else{
                        if($pages>1){
                            for($i=1;$i<=$pages;$i++){
                                $qian=$i*2-1;
                                $hou=$i*2;
                                $hou1=$hou-1;
                                $qian1=$qian-1;
                                ?>
                                <table width="100%" border=0 cellpadding="2px" cellspacing="1px">
                                <tr>
                                  <td width="50%"><?=$res[$qian1]['snapshotdesc']?>
                                        <video width="100%" height="" controls="controls">
                                                        <source src="<?=URL_APPEDND?>/storage/snapMp4/<?=$res[$qian1]['filename']?>.mp4" type="video/mp4">
                                                        your browser does not support the video tag.
                                        </video>
                                  </td>
                                  <td width="50%"><?=$res[$hou1]['snapshotdesc']?>
                                        <video width="100%" height="" controls="controls">
                                                        <source src="<?=URL_APPEDND?>/storage/snapMp4/<?=$res[$hou1]['filename']?>.mp4" type="video/mp4">
                                                        your browser does not support the video tag.
                                        </video>
                                  </td>
                                </tr>
                            </table>
                             <?php } 
                        }else{
                            $qian1=1-1;
                            $hou1=2-1;
                            ?>
                            <table width="100%" border=0 cellpadding="2px" cellspacing="1px">
                                <tr>
                                  <td width="50%"><?=$res[$qian1]['snapshotdesc']?>
                                        <video width="100%" height="" controls="controls">
                                                        <source src="<?=URL_APPEDND?>/storage/snapMp4/<?=$res[$qian1]['filename']?>.mp4" type="video/mp4">
                                                        your browser does not support the video tag.
                                        </video>
                                  </td>
                                  <td width="50%"><?=$res[$hou1]['snapshotdesc']?>
                                        <video width="100%" height="" controls="controls">
                                                        <source src="<?=URL_APPEDND?>/storage/snapMp4/<?=$res[$hou1]['filename']?>.mp4" type="video/mp4">
                                                        your browser does not support the video tag.
                                        </video>
                                  </td>
                                </tr>
                            </table>
                        <?php 
                        }

                         }
             }
}else{
   echo "<br><br><h1 align=center>对不起，录屏文件发生错误，请检查！</h1>";
   exit;
}
?>
