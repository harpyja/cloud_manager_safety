<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$language_file = 'admin';
$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");

$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$table_course_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );
$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
$tbl_courseware = Database::get_course_table ( TABLE_COURSEWARE );

 
$export_id = intval(getgpc('export_id'));
if (isset ( $export_id )) {
    
     /**远程下载 断点续传 start **/

    class httpdownload {   
            var $data = null;   
            var $data_len = 0;   
            var $data_mod = 0;   
            var $data_type = 0;   
            var $data_section = 0; //section download   
            var $sentSize=0;   
            var $handler = array('auth' => null);   
            var $use_resume = true;   
            var $use_autoexit = false;   
            var $use_auth = false;   
            var $filename = null;   
            var $mime = null;   
            var $bufsize = 2048;   
            var $seek_start = 0;   
            var $seek_end = -1;   
            var $totalsizeref = 0;   
            var $bandwidth = 0;   
            var $speed = 0;  
      
    /* 
     *初始化一些下载参数 
     */    
    function initialize()   
    {   
        global $HTTP_SERVER_VARS;   
        if (!$this->use_auth) //use authentication   
        {   
              if ($this->_auth()) //no authentication   
              {   
                      header('WWW-Authenticate: Basic realm="Please enter your username and password"');   
                      header('HTTP/1.0 401 Unauthorized');   
                      header('status: 401 Unauthorized');   
                      if ($this->use_autoexit) exit();   
                      return false;   
              }   
        }   
        if ($this->mime == null) $this->mime = "application/octet-stream"; //default mime  
          
        //如果设置了range  
        if (isset($_SERVER['HTTP_RANGE']) || isset($HTTP_SERVER_VARS['HTTP_RANGE']))   
        {   
            if (isset($HTTP_SERVER_VARS['HTTP_RANGE'])) $seek_range = substr($HTTP_SERVER_VARS['HTTP_RANGE'] , strlen('bytes='));   
            else $seek_range = substr($_SERVER['HTTP_RANGE'] , strlen('bytes='));//for example:Range:  bytes=0-100  
            $range = explode('-',$seek_range); //explode seek_range to range array  
              
            //处理开始字节与结束字节  
            if ($range[0] > 0) {   
                  $this->seek_start = intval($range[0]);   
            }   
            if ($range[1] > 0) $this->seek_end = intval($range[1]);   
            else $this->seek_end = -1;  
              
            if (!$this->use_resume)   
            {   
                  $this->seek_start = 0;   
                  //header("HTTP/1.0 404 Bad Request");   
                  //header("Status: 400 Bad Request");   
                  //exit;   
                  //return false;   
            } else {   
                  $this->data_section = 1;   
            }   
        } else {  
                /* 
                Range头域可以请求实体的一个或者多个子范围，Range的值为0表示第一个字节，也就是Range计算字节数是从0开始的 
                表示头500个字节：bytes=0-499 
                表示第二个500字节：bytes=500-999 
                表示最后500个字节：bytes=-500 
                表示500字节以后的范围：bytes=500- 
                第一个和最后一个字节：bytes=0-0,-1 
                同时指定几个范围：bytes=500-600,601-999 
                */  
            $this->seek_start = 0;//第一个字节   
            $this->seek_end = -1; //最后一个字节  
        }   
        $this->sentSize=0;   
        return true;  
    }  
      
    /* 
     *配置请求头 
     */  
    function header($size,$seek_start=null,$seek_end=null) {   
          header('Content-type: ' . $this->mime); //类型  
          header('Content-Disposition: attachment; filename="' . $this->filename . '"'); //文件名  
          header('Last-Modified: ' . date('D, d M Y H:i:s \G\M\T' , $this->data_mod)); //修改时间  
            
          //断点续传  
          if ($this->data_section && $this->use_resume) {   
                header("HTTP/1.0 206 Partial Content");   
                header("Status: 206 Partial Content");   
                header('Accept-Ranges: bytes');   
                header("Content-Range: bytes $seek_start-$seek_end/$size");   
                header("Content-Length: " . ($seek_end - $seek_start + 1));   
          } else { //重新下载  
                header("Content-Length: $size");   
          }  
    }  
    function download_ex($size) {   
          if (!$this->initialize()) return false;   
          ignore_user_abort(true);   
          //Use seek end here   
          if ($this->seek_start > ($size - 1)) $this->seek_start = 0;   
          if ($this->seek_end <= 0) $this->seek_end = $size - 1;   
            
          $seek = $this->seek_start;  
          $this->header($size,$seek,$this->seek_end);   
          $this->data_mod = time();   
          return true;  
    }  
    function download() {   
        if (!$this->initialize()) return false;   
        try {   
                $seek = $this->seek_start;   
                $speed = $this->speed;   
                $bufsize = $this->bufsize;   
                $packet = 1;   
                //do some clean up   
                @ob_end_clean();   
                $old_status = ignore_user_abort(true);   
                @set_time_limit(0);   
                $this->bandwidth = 0;   
                $size = $this->data_len;   
                if ($this->data_type == 0) //download from a file   
                {   
                    $size = filesize($this->data);   
                    if ($seek > ($size - 1)) $seek = 0;   
                    if ($this->filename == null) $this->filename = basename($this->data);   
                    $res = fopen($this->data,'rb');//以二进制读模式打开文件   
                    if ($seek) fseek($res , $seek);//定位文件中设置文件指针位置   
                    if ($this->seek_end < $seek) $this->seek_end = $size - 1;   
                    $this->header($size,$seek,$this->seek_end); //always use the last seek   
                    $size = $this->seek_end - $seek + 1;   
                    while (!(connection_aborted() || connection_status() == 1) && $size > 0)   
                    {   
                        if ($size < $bufsize) {//如果剩下的尚未下载的文件size小于bufsize，就一次下载完   
                            echo fread($res , $size);   
                            $this->bandwidth += $size;   
                            $this->sentSize+=$size;   
                        } else {   
                            echo fread($res , $bufsize);   
                            $this->bandwidth += $bufsize;   
                            $this->sentSize+=$bufsize;   
                        }   
                        $size -= $bufsize; //扣除已经下载好的size  
                        flush(); //刷新缓冲 实现echo动态输出  
                        //限制下载速度  
                        if ($speed > 0 && ($this->bandwidth > $speed*$packet*1024)) {   
                            sleep(1);   
                            $packet++;   
                        }   
                    }   
                    fclose($res);   
                }   
                elseif ($this->data_type == 1) //download from a string   
                {   
                    if ($seek > ($size - 1)) $seek = 0;   
                    if ($this->seek_end < $seek) $this->seek_end = $this->data_len - 1;   
                    $this->data = substr($this->data , $seek , $this->seek_end - $seek + 1);   
                    if ($this->filename == null) $this->filename = time();   
                    $size = strlen($this->data);   
                    $this->header($this->data_len,$seek,$this->seek_end);   
                    while (!connection_aborted() && $size > 0) {   
                          if ($size < $bufsize) {   
                                $this->bandwidth += $size;   
                                $this->sentSize+=$size;   
                          } else {   
                                $this->bandwidth += $bufsize;   
                                $this->sentSize+=$bufsize;   
                          }   
                          echo substr($this->data , 0 , $bufsize);   
                          $this->data = substr($this->data , $bufsize);   
                          $size -= $bufsize;   
                          flush();   
                          if ($speed > 0 && ($this->bandwidth > $speed*$packet*1024)) {   
                                sleep(1);   
                                $packet++;   
                          }   
                    }   
                } else if ($this->data_type == 2) {   
                    //just send a redirect header   
                    header('location: ' . $this->data);   
                }   
                if($this->totalsizeref==$this->sentSize ) echo "end download\n";//error_log("end download\n", 3,"/usr/local/www/apache22/LOGS/apache22_php.err");   
                else echo "download is canceled\n";//error_log("download is canceled\n", 3,"/usr/local/www/apache22/LOGS/apache22_php.err");   
                if ($this->use_autoexit) exit();   
                //restore old status   
                ignore_user_abort($old_status);   
                set_time_limit(ini_get("max_execution_time"));   
            }  
            catch(Exception $e) {   
                  //error_log("cancel download\n".$e, 3,"/usr/local/www/apache22/LOGS/apache22_php.err");  
                  echo "cancel download\n".$e;  
            }   
          return true;  
    }  
    function set_byfile($dir) {   
          if (is_readable($dir) && is_file($dir)) {   
                $this->data_len = 0;   
                $this->data = $dir;   
                $this->data_type = 0;   
                $this->data_mod = filemtime($dir);   
                $this->totalsizeref = filesize($dir);   
                return true;   
          } else return false;  
    }  
    function set_bydata($data) {   
          if ($data == '') return false;   
                $this->data = $data;   
                $this->data_len = strlen($data);   
                $this->data_type = 1;   
                $this->data_mod = time();   
                return true;  
    }  
    function set_byurl($data) {   
          $this->data = $data;   
          $this->data_len = 0;   
          $this->data_type = 2;   
          return true;  
    }  
    function set_lastmodtime($time) {   
          $time = intval($time);   
          if ($time <= 0) $time = time();   
          $this->data_mod = $time;  
    }  
    function _auth() {   
          if (!isset($_SERVER['PHP_AUTH_USER'])) return false;   
          if (isset($this->handler['auth']) && function_exists($this->handler['auth'])) {   
                return $this->handler['auth']('auth' , $_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);   
          }   
          else return true; //you must use a handler   
    }  
    }
/** 远程下载 断点续传 end **/
    
    $path=URL_ROOT."/www".URL_APPEDND ;
    $path1=URL_ROOT."/www".URL_APPEDND."/courses";

    $sq="select category_code from course where code =".$export_id;
    $id = Database::getval ( $sq, __FILE__, __LINE__ );

    //该课程的分类
    $sql0 = "SELECT * FROM course_category where id=".intval($id);
    $res = api_sql_query ( $sql0, __FILE__, __LINE__ );
    $vm= array ();
    while ( $vm = Database::fetch_row ( $res) ) {
        $vms [] = $vm;
    }
    foreach ( $vms as $k1 => $v1){
        foreach($v1 as $k2 => $v2){
            $arr[]  = $v2;
        }
    }
    $StrConents=serialize($arr);

    if(!file_exists("$path1/$export_id")){
        exec("mkdir  $path1/$export_id");
        exec("chmod -R 777 $path1/$export_id"); 
    }

//分类
    $fh = fopen("$path1/$export_id/course_category.txt","w");
    fwrite($fh,serialize($StrConents));
    fclose($fh);

//课程sql
      //server
      exec("mysqldump -u".DB_USER." ".DB_NAME." course -w code=$export_id --no-create-info > $path1/$export_id/courses.sql");
      //local
   //   exec("mysqldump -u".DB_USER."  -p".DB_PWD." ".DB_NAME." course -w code=$export_id --no-create-info > $path/$export_id/courses.sql");
               
//课件sql
    $crs_courseware=api_sql_query_array_assoc( "select * from crs_courseware where cc=".$export_id, __FILE__, __LINE__ );
    $cc = fopen("$path1/$export_id/crs_courseware.txt","w");
    fwrite($cc,serialize($crs_courseware));
    fclose($cc);
    //$crs_courseware = exec("mysqldump -u".DB_USER." -p".DB_PWD." ".DB_NAME." crs_courseware -w cc=".$export_id." --no-create-info > $path/$export_id/crs_courseware.sql");
                
//拓扑sql
    $netmap_name=Database::getval("select `description13` from `course` where `code`='".$export_id."'",__FILE__,__LINE__);
    $netmap_id=Database::getval("select id from networkmap where name='".$netmap_name."'",__FILE__,__LINE__);

     if($netmap_id!==''){
	$nets=api_sql_query_array_assoc( "select * from networkmap where id=".intval($netmap_id), __FILE__, __LINE__ );
        $nn = fopen("$path1/$export_id/networkmap.txt","w");
        fwrite($nn,serialize($nets));
        fclose($nn);
     //$networkmap = exec("mysqldump -u".DB_USER." -p".DB_PWD." ".DB_NAME." networkmap -w id=".$netmap_id." --no-create-info > $path/$export_id/networkmap.sql");
     }

$datas['course_name']=Database::getval("select `title` from `course` where `code`='".$export_id."'",__FILE__,__LINE__);
$datas['course_category']=$arr[3]; 
$datas['netmap_name']=$netmap_name;

//课件文件
//    $courseware_copy=exec("cp -rf $path/storage/courses/$export_id  $path1/$export_id/");
  $course_var=$path."/storage/courses/".$export_id;
    
                
if($netmap_id!==''){
//模板名称
	$sqll1="select `xml` from `networkmap` where `id`=".intval($netmap_id);
	$s= Database::getval ( $sqll1, __FILE__, __LINE__ );
	$vm = str_replace (array('<','>','\\','/',';',' ','"','&','='),array('','','','','','','','',''),$s);   //过滤特殊符号；
	$patten = '/quotgt.*lth1/Uis';
	preg_match_all($patten,$vm, $ss);
	$ss1=$ss[0];
	$num=  count($ss1);
	for($i=0;$i<$num;$i++){
	    if($i%2==0){
		$ss2=explode("_",$ss1[$i]);
		$ss2_arr=  str_replace("quotgt",'',$ss2[0]);
		$ress1[]=$ss2_arr;
	    }
	}
	$vmdiskName_arr=  array_unique($ress1);//去重
	$vm_sql='';
 	 $raw_file='/tmp/mnt/vmdisk/images/99';   //server
	//$raw_file='/var/www';//echo $raw_file;    //local
                
	foreach($vmdiskName_arr as $v){//键位重写
	    $vmdisk_names[]=$v; 
//模板文件
                
//		$vmdisk_copy=exec("cp -rf $raw_file/$v.raw  $path1/$export_id/");  
            
             $raw_var.=" ".$raw_file."/".$v.".raw "; 
            
		if($vm_sql!==''){
		$vm_sql.=" OR name='".$v."'";
		}else{
		$vm_sql.="name='".$v."'";
		}
	    
	} 
	exec("chmod -R 777 $path1/$export_id");
//模板sql
	$v_sql="select * from vmdisk where ".$vm_sql;
	$vms=api_sql_query_array_assoc( $v_sql, __FILE__, __LINE__ );

	$vv = fopen("$path1/$export_id/vmdisk.txt","w");
	fwrite($vv,serialize($vms));
	fclose($vv);
	//$vmdisk = exec('mysqldump -u'.DB_USER.' -p'.DB_PWD.' '.DB_NAME.' vmdisk -w "'.$vm_sql.'" --no-create-info > '.$path.'/'.$export_id.'/vmdisk.sql');
                
} 
    $x = fopen("$path1/$export_id/datas","w");
    fwrite($x,serialize($datas));
    fclose($x);
    //打包
//    exec("cd $path1/ ; sudo -u root tar -zcvf  ".$export_id.".tgz  $export_id "); 
     exec("cd $path1/ ; sudo -u root tar -zcvf  ".$export_id.".tgz  $export_id $course_var  $raw_var"); 
     exec(" rm -rf ".$path1."/".$export_id);
     //exec("cd $path/ ;tar -cvf ".$export_id.".tar $export_id;sudo -u root /sbin/cloudenc.sh e ".$category_name.".tgz");
                
    $path_tar= $path1."/".$export_id.".tgz";
                
     if (file_exists($path_tar)){
                
            $fiilename="$export_id.tgz";
            $downfiles="http://".$_SERVER['HTTP_HOST'].URL_APPEDND."/courses/$export_id.tgz";
            //usage  
            $object = new httpdownload();  
            $object->speed = 512;//限速512KB/s
            $object->set_byurl($downfiles);//服务器文件名,包括路径 
            $object->filename =$fiilename; //下载另存为的文件名
            $object->download(); 
//           unlink("$path1/$export_id.tgz");
        }else{
          echo "<script>alert('操作失败,请重试！');</script>";
        }
                
    $redirect_url = 'course_list.php';
    tb_close ( $redirect_url );
    exit;
}
?>
