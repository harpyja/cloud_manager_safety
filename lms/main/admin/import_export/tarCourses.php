 
<?php
/*
 ==============================================================================
 上传HTML打包课程文档
 ==============================================================================
 */
$export_id=htmlspecialchars($_GET['courses_export']);

$language_file = 'document';
include_once ("../../inc/global.inc.php");

require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
require_once (api_get_path ( SYS_CODE_PATH ) . "courseware/cw.lib.inc.php");
include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');

$rootpath=URL_ROOT."/www";
  
$form = new FormValidator ( 'upload', 'POST','tarCourses.php?courses_export='.$export_id, '', 'enctype="multipart/form-data"' );

$form->addElement ( 'header', 'header','打包设置' );

$sq = "SELECT id,name FROM course_category where id=".$export_id;
$course_category = Database::get_into_array2 ( $sq, __FILE__, __LINE__ );
$form->addElement ( 'select', 'category', "选择课程类别", $course_category, array ('style' => "min-width:25%", 'id' => 'course_code' ) );


$group = array ();
$group [] = $form->createElement ( 'submit', 'submitDocument', get_lang ( 'Ok' ), 'class="inputSubmit"' ,array( 'id'=>'sub'  ,'onclick' => 'javascript:choseMe();' ) );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false ); 
$form->setDefaults ( $defaults );
Display::setTemplateBorder ( $form, '98%' );
//$form->add_real_progress_bar ( 'DocumentUpload', 'user_upload' );


if ($form->validate ()) {  
    $data = $form->getSubmitValues ();
    $category_id = trim ( $data ['category'] );
    $time_dir=date("YmdHis");
    $url_path=URL_ROOT."/www".URL_APPEDND;
     
//    $path=$url_path."/storage/coursesbak/";
  $path=URL_ROOT."/mnt/www/coursesbak";  //server
  $patha=URL_ROOT."/mnt/www/coursesbak";  
  $path=$path."/".$time_dir;
 
//导出文件名称的定义
    $export_name = $category_id."-".$time_dir;
    $paths=$path."/".$export_name;   //课件信息存放目录 
    if(!file_exists($paths)){    
        exec("mkdir -p ".$paths);
        exec("chmod -R 777 $paths"); 
    }

//获取课程类型数据
    $cate_sql="select * from course_category where id =".$category_id;
    $category_datas= api_sql_query_array_assoc($cate_sql, __FILE__, __LINE__ );
//获取课程名称
    $cate_name=$category_datas[0]['name'];//echo $cate_name;
//存储课程类型数据
    $cc = fopen("$paths/course_category","w");
    fwrite($cc,serialize($category_datas));
    fclose($cc);
//获取该分类下所有课程内容数据//`code`,`title`,`description13`
    $course_sql="SELECT * FROM `course` WHERE `category_code` =".$category_id;
    $course_datas=api_sql_query_array_assoc($course_sql, __FILE__, __LINE__ );
//所有课程内容数据
    $cou = fopen("$paths/courses","w");
    fwrite($cou,serialize($course_datas));
    fclose($cou);
	
    $course_var='';
//    $coursename='';
    for($i=0;$i<count($course_datas);$i++){
 	$course_code=$course_datas[$i]['code'];//课程编号
        $course_title=$course_datas[$i]['title'];
        $networkmap_name=$course_datas[$i]['description13'];//课程编号
	if(!file_exists($paths/$course_code)){
	   exec("mkdir $paths/$course_code");
	   exec("chmod -R 777 $paths/$course_code/"); 
	}
  
    //课件文件夹var
        $c_var=$url_path."/storage/courses/".$course_code;
        $course_var .=" ".$c_var." ";
 
//        $coursename .=$course_title."\n";
            $coursename[ ]=$course_title;

//存储课件SQL
        $courseware_sql="SELECT * FROM `crs_courseware` WHERE cc=".$course_code;
        $courseware_data=api_sql_query_array_assoc($courseware_sql, __FILE__, __LINE__ );
        $cware = fopen("$paths/$course_code/courseware","w");
        fwrite($cware,serialize($courseware_data));
        fclose($cware);

//存储拓扑SQL
        $networkmap_sql="SELECT * FROM `networkmap` WHERE `name`='".$networkmap_name."'";
        $networkmap_data=api_sql_query_array_assoc($networkmap_sql, __FILE__, __LINE__ );
	$net = fopen("$paths/$course_code/networkmap","w");
        fwrite($net,serialize($networkmap_data));
        fclose($net);

//模板名称   
	$net_xml=$networkmap_data[0]['xml'];
	$vm = str_replace (array('<','>','\\','/',';',' ','"','&','='),array('','','','','','','','',''),$net_xml);   //过滤特殊符号；
	$patten = '/quotgt.*lth1/Uis';
	preg_match_all($patten,$vm, $ss);
	$ss1=$ss[0];
	$num=  count($ss1);
	for($j=0;$j<$num;$j++){
	    if($j%2==0){
		$ss2=explode("_",$ss1[$j]);
		$ss2_arr=  str_replace("quotgt",'',$ss2[0]);
		$ress1[]=$ss2_arr;
	    }
	}
	$vmdiskName_arr=  array_unique($ress1);//去重

	$raw_file='/tmp/mnt/vmdisk/images/99';    //server
 
	$vm_sql='';
	$raw_var='';
	foreach($vmdiskName_arr as $v){//键位重写
           
//模板文件
  //模板raw    拓扑设计添加设备时，选择模板时过滤基础镜像。
            $sql="select CD_mirror from vmdisk where name='".$v."'";
            $res_vm=  Database::getval($sql);   
            if($res_vm){   //$res_vm不为空时是增量镜像
                $raw_var.=" ".$raw_file."/".$v.".raw "; 
            }

	    if($vm_sql!==''){
		$vm_sql.=" OR name='".$v."'";
	    }else{
	        $vm_sql.="name='".$v."'";
	    }
	    
	}
 
//模板sql
	$v_sql="select * from vmdisk where ".$vm_sql;
	$vms=api_sql_query_array_assoc( $v_sql, __FILE__, __LINE__ );

        $vv = fopen("$paths/$course_code/vmdisk","w");
        fwrite($vv,serialize($vms));
        fclose($vv);
    }
    
    $fiilename=$export_name.".lib";
//readme
    $readme_data["filename"]=$fiilename;//文件名称
    $readme_data["date"]=$time_dir;//时间
    $readme_data["category_name"]=$cate_name;//分类名称
    $readme_data["coursename"]=$coursename;//课程列表
    $readmedate = fopen($path."/readme","w");
    fwrite($readmedate,serialize($readme_data));
    fclose($readmedate);
    
    //重新打包后删除旧的包
    $old_file=$patha."/".$category_id."-*.lib";
    exec("rm -rf  $old_file");
    
    //压缩
    $filename=$export_name.".lib";
    exec("cd ".$path ."/ ; tar -zcvf  ".$filename ."  ".$export_name."  ".$course_var."  ".$raw_var ."   readme; mv ".$filename." ../; cd   ../ ;rm  -rf $time_dir" );
        

 
   //压缩后跳转   
    $redirect_url = 'imex_list.php';
    tb_close ( $redirect_url );
}

Display::display_header ( $nameTools, FALSE );

$form->display ();


?>

 