<?php
header("content-type:text/html;charset=utf-8");
include_once ("../../inc/global.inc.php");
Display::display_header ( NULL, FALSE );

$title_sql = "select name FROM  `labs_labs` ";
$title_res = api_sql_query ( $title_sql, __FILE__, __LINE__ );
$vm= array ();
while ( $vm = Database::fetch_row ( $title_res ) ) {
    $vms [] = $vm;
}
foreach ( $vms as $k1 => $v1){  //获取拓扑名称（下拉框）
    foreach($v1 as $k2 => $v2){
        $title[$v2]  = $v2;   //键,值都是拓扑name，需将"键"转换成拓扑id
    }
}
//文件大小转换格式
function sizecount($filesize) {
    if($filesize >= 1073741824) {
        $filesize = round($filesize / 1073741824 * 100) / 100 . ' gb';
    } elseif($filesize >= 1048576) {
        $filesize = round($filesize / 1048576 * 100) / 100 . ' mb';
    } elseif($filesize >= 1024) {
        $filesize = round($filesize / 1024 * 100) / 100 . ' kb';
    } else {
        $filesize = $filesize . ' bytes';
    }
    return $filesize;
}

if(isset($_POST['submit'])){

    $labs_name= getgpc("title","P");
    $labs_id_sql="select `id` from `labs_labs` where `name`='".$labs_name."'";
    $labs_id=Database::getval($labs_id_sql,__FILE__,__LINE__);

    $document_type=  getgpc("type","P");
//echo $document_type;
//var_dump($_FILES);
    if(is_uploaded_file($_FILES["uploadfile"]["tmp_name"])){
        $upfile=$_FILES["uploadfile"];
        $name=$upfile["name"];
    // $document_type=$upfile["type"];
        $size=$upfile["size"];
        $tmp_name=$upfile["tmp_name"];
        $error=$upfile["error"];//错误信息

        //文件后缀名
        $type=substr(strrchr($name, '.'), 1);//echo $type;
        switch($type){
          case "pdf": $ok=1;
            break;
          case "chm": $ok=1;
            break;
          case "doc" : $ok=1;
            break;
          case "docx" : $ok=1;
            break;
          case "xls" : $ok=1;
            break;
          case "ppt" : $ok=1;
            break;
          default:$ok=0;
            break;
        }

        $path_name=URL_ROOT.'/www/lms/storage/routerdoc/'.$name;   //(本地)var-->tmp（服务器）

        if($ok && $error=='0'){
            if(file_exists($path_name)){
                echo "<script language=\"javascript\">alert('该手册已经存在！')</script>";
            }else{
                move_uploaded_file($tmp_name,$path_name);
                //操作成功后，提示成功
                if(file_exists($path_name)){ //当文件上传成功并保存到指定目录来，才执行插入语句
                    $sql = "INSERT INTO `labs_document` (`id`, `document_name`,`type`, `document_size`, `labs_id`) VALUES(null, '".$name."', '".$document_type."', '".$size."', '".$labs_id."')";

                    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
                    if($res){
                       echo "<script language=\"javascript\">alert('资料上传成功！')</script>";

                        tb_close ('labs_experimental_anual.php');
                    }
                 }
            }

        }else{
            //如果文件上传过程中有错误，提示失败
            echo "<script language=\"javascript\">alert('资料上传失败')</script>";
        }
    }
}

$form = new FormValidator ( 'uploadform', 'POST', 'document_upload.php', '', 'enctype="multipart/form-data"' );
//$form->addElement ( 'header', 'header', get_lang ( 'UplUploadDocument' ) );
$form->addElement ( 'html', '<i>上传文件尺寸应该小于:2M;</i>' );
$form->addElement ( 'hidden', 'curdirpath', urldecode ( $path ) );
$form->addElement ( 'select', 'title',"请选择网络拓扑", $title, array ('style' => 'height:25px;width:20%' ) );

//类别（单选框形式）---zd
$group = array ();
$group [] = $form->createElement ( 'radio', 'type', null, get_lang ( '初始化配置' ), 1 );
$group [] = $form->createElement ( 'radio', 'type', null, get_lang ( '实验指导书' ), 0 );
$form->addGroup ( $group, 'type', get_lang ( 'Type' ), null, false );

//上传文件
$form->addElement ( 'file', 'uploadfile', '选择文件', array ('style' => "width:50%", 'class' => 'inputText' ) );
$form->addRule ( 'uploadfile', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$group = array ();
$group [] = $form->createElement ( 'submit', 'submit', get_lang ( 'Ok' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.location.reload();self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$default['type']=1;
$form->setDefaults ($default);  
Display::setTemplateBorder ( $form, '100%' );
$form->display ();
