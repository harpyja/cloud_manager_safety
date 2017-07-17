<?php 
$language_file = 'admin';
$cidReset = true;
require_once ('../../../main/inc/global.inc.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( SYS_CODE_PATH ) . "courseware/cw.lib.inc.php");
include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');
api_protect_admin_script ();
header("content-type:text/html;charset=utf-8");


 $upfile ="/www/coursesinit";
$objDept = new DeptManager ();
Display::display_header ( NULL, FALSE );
 
//如果没有coursesinit，则新建
if(!file_exists(URL_ROOT."/mnt".$upfile)){
        exec("mkdir ".URL_ROOT."/mnt".$upfile);
        exec("chmod -R 777 ".URL_ROOT."/mnt".$upfile); 
    }

//循环文件夹下的文件夹和文件
function myreaddir($dir) {
    $handle=opendir($dir);
    $i=0;
    while($file=readdir($handle)) {
        if (($file!=".")and($file!="..")) {
            $list[]=$file;
            $i=$i+1;
        }
    }
    closedir($handle);
    return $list;
}

echo '<div class="actions">';
echo '<span style=" padding-top:2px;">';

$is_allowed_to_edit = api_is_allowed_to_edit ();
if (! $is_allowed_to_edit) api_not_allowed ();
$user_id = api_get_user_id ();
$table_courseware = Database::get_course_table ( TABLE_COURSEWARE );
$courseDir = api_get_course_code () . '/html/';
$base_work_dir = api_get_path ( SYS_COURSE_PATH ) . $courseDir;



$max_upload_file_size = get_upload_max_filesize ( api_get_setting ( "upload_max_filesize" ) );
$form = new FormValidator ( 'upload', 'POST', "/lib2/upload.cgi", '', 'enctype="multipart/form-data"' );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>&nbsp;{element}</span> ' );
$form->addElement("html","&nbsp;&nbsp;导入课件压缩包（lib）");
$form->addElement ( 'file', 'FILE1', '上传虚拟磁盘镜像:', array ('class' => 'inputText', 'style' => 'width:30%', 'id' => 'upload_file_local' ) );
$form->addElement ( 'html', '<input type="hidden" name="uploaddir" value="'.$upfile.'"><input type="hidden" name="rej_url" value="/lms/main/admin/import_export/import.php">');
//$form->addElement ( 'submit', 'submit', get_lang ( '提交' ), 'class="inputSubmit"' );
$form->addElement ('html','<input class="inputSubmit" name="submit" value="提交" type="submit">');
$form->display ();
echo '</span>'; 
echo '</div>';
?>
<script type="text/javascript">
   function aler(){
       if(!confirm('您确定要执行该操作吗？')) return false;
   }
</script>
<?php
if (isset ( $_GET ['iso'] )) {
    $isos=getgpc('iso','G');
    $file =URL_ROOT."/mnt".$upfile."/".$isos;
    chmod($file,0777);
    exec("rm -rf $file");
}
$iso = myreaddir(URL_ROOT."/mnt".$upfile);
  $counts=count($iso);
?>
<style type="text/css">
    .data_table .row_odd th,.data_table .row_odd th{color:#000}
</style>
<table class="data_table">
    <tr class="row_odd">
        <th>序号</th>
        <th>课件包文件</th>
        <th>格式化课件包</th>
        <th>删除</th>
    </tr>
 <?php
  
    if( $counts==0){
        echo "<tr><td colspan=10 align=center>没有相关文件</td></tr>";
    }else{
    $j=$i-1;
    for ($i= 0;$i<=  $counts-1; $i++){
        $j=$i+1;
        echo "<tr>";
        echo "<td width='25%' align=center>$j</td>";
        echo "<td>$iso[$i]</td>";
        echo "<td align='center'><a href='format.php?action=bale&bid=$iso[$i]' target='_self' onclick='aler();'>";
        echo "<img src='/lms/themes/img/right.gif'/></a></td>";
        echo "<td align=center width='25%'><a href='import.php?iso=$iso[$i]' target='_self' onclick='aler();'>";
        echo "<img src='/lms/themes/img/delete.gif'/></a></div></div></td></tr>";
    }
}
?>
</table>
