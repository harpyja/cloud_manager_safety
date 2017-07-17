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
  
$form = new FormValidator ( 'upload', 'POST','export.php?courses_export='.$export_id, '', 'enctype="multipart/form-data"' );

$form->addElement ( 'header', 'header','导出设置' );

$sq = "SELECT id,name FROM course_category where id=".$export_id;
$course_category = Database::get_into_array2 ( $sq, __FILE__, __LINE__ );
$form->addElement ( 'select', 'category', "选择课程类别", $course_category, array ('style' => "min-width:25%", 'id' => 'course_code' ) );


$group = array ();
$group [] = $form->createElement ( 'submit', 'submitDocument', get_lang ( 'Ok' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->setDefaults ( $defaults );
Display::setTemplateBorder ( $form, '98%' );
//$form->add_real_progress_bar ( 'DocumentUpload', 'user_upload' );


if ($form->validate ()) {

    $data = $form->getSubmitValues ();
    $category_id = trim ( $data ['category'] );
    
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

      $url_path=URL_ROOT."/www".URL_APPEDND;
      $path=$url_path."/storage/coursesbak";
      $dir=$path."/".$category_id."*";
      $file_na=glob($dir);
//     $export_name = $category_id."-201205187661";//.date("YmdHi");   
//      $filename=$export_name.".tgz";
      if($file_na){
        $filename=explode("/", $file_na[0]);
        $filename1=end($filename);
        }
         
//        if (file_exists("$path/$filename1")){     
//          header('Content-type: application/tar');
//          header("Cache-Control: public");
//          header("Content-Description: File Transfer");
//          header("Content-Disposition: attachment; filename=".$export_name.".tar");
//          header("Content-Transfer-Encoding: binary");
//          readfile("$path/$export_name.tar");

//	  exit;
            if($filename1){
            $downfiles="http://".$_SERVER['HTTP_HOST'].URL_APPEDND."/storage/coursesbak/$filename1";
           //usage
            $object = new httpdownload();
            $object->speed = 512;
            $object->set_byurl($downfiles);
            $object->filename =$filename1;
            $object->download();                                                                                                                                          
           //unlink("$path/$export_name.tar");
            }else{
                echo "<script>alert('操作失败,请重试！');</script>";
            }
//        }else{  
//          echo "<script>alert('操作失败,请重试！');</script>";
//        }
     
    $redirect_url = 'imex_list.php';
    tb_close ( $redirect_url );
}

Display::display_header ( $nameTools, FALSE );

$form->display ();



