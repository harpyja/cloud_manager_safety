<?php
header("content-type:text/html;charset=utf-8");
$files=htmlspecialchars($_GET ['files']);
$rootpath="/tmp/www/lms/storage";

//Chrome_non_defaultV4
if($files=='chrome'){
//exec("cd $rootpath/ ;zip -r Chrome_non_defaultV4.zip Chrome_non_defaultV4.exe");
//chmod("$rootpath/Chrome_non_defaultV4.zip",0777);
//exec("chmod -R 777 $rootpath/Chrome_non_defaultV4.zip");
//if (file_exists("$rootpath/Chrome_non_defaultV4.zip")){
//        header('Content-type: application/zip');
//        header("Cache-Control: public");
//        header("Content-Description: File Transfer");
//        header("Content-Disposition: attachment; filename=Chrome_non_defaultV4.zip");
//        header("Content-Transfer-Encoding: binary");
//        readfile("Chrome_non_defaultV4.zip");
//        //exec("rm -rf $rootpath/Chrome_non_defaultV4.zip");
//        exit;
//    }
    if (file_exists("$rootpath/Chrome_non_defaultV4.tar")){
        header('Content-type: application/tar');
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=Chrome_non_defaultV4.tar");
        header("Content-Transfer-Encoding: binary");
        readfile("$rootpath/Chrome_non_defaultV4.tar");
        // unlink("$path/$export_id.tar");
        exit;
    }
else{
        echo "<script>alert('操作失败,请重试！');</script>";
    }
}
//jre-7u7-windows-i586
if($files=='jre'){
//exec("cd $rootpath/ ;zip -r jre-7u7-windows-i586.zip jre-7u7-windows-i586.exe");

//exec("chmod -R 777 $rootpath/jre-7u7-windows-i586.zip");
//if (file_exists("$rootpath/jre-7u7-windows-i586.exe")){
//        header("Content-type: application/exe");
//        header("Cache-Control: public");
//        header("Content-Description: File Transfer");
//        header("Content-Disposition: attachment; filename=jre-7u7-windows-i586.exe");
//        header("Content-Transfer-Encoding: binary");
//        readfile("jre-7u7-windows-i586.exe");
//        //exec("rm -rf $rootpath/jre-7u7-windows-i586.zip");
//        exit;
//    }
    if (file_exists("$rootpath/jre-7u7-windows-i586.tar")){
        header('Content-type: application/tar');
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=jre-7u7-windows-i586.tar");
        header("Content-Transfer-Encoding: binary");
        readfile("$rootpath/jre-7u7-windows-i586.tar");
       // unlink("$path/$export_id.tar");
        exit;
    }
else{
        echo "<script>alert('操作失败,请重试！');</script>";
    }
}
