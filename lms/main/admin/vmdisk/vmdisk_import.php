<?php
/**
==============================================================================
 *磁盘镜像管理（上传/删除）
==============================================================================
 */
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

$upfile = "/vmdisk/images/99";
$objDept = new DeptManager ();
Display::display_header ( NULL, FALSE );

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
    //var_dump($list);
    return $list;
}

echo '<div class="actions">';
echo '<span style="float:left; padding-top:2px;">';

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
$form->addElement ( 'file', 'FILE1', '上传虚拟磁盘镜像:', array ('class' => 'inputText', 'style' => 'width:60%', 'id' => 'upload_file_local' ) );
$form->addRule ( 'FILE1', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$form->addElement ( 'html', '<input type="hidden" name="uploaddir" value="'.$upfile.'"><input type="hidden" name="rej_url" value="/lms/main/admin/vmdisk/vmdisk_import.php">');
$form->addElement ( 'submit', 'submit', get_lang ( '提交' ), 'class="inputSubmit"' );

$form->display ();
echo '</span>';
//$form->display ();
echo '</div>';


?>
<script type="text/javascript">
   function aler(){
       if(!confirm('您确定要执行该操作吗？')) return false;
   }
</script>
<?php
if (isset ( $_GET ['iso'] )) {
    $isos=  getgpc("iso");
    $file =URL_ROOT."/mnt".$upfile."/".$isos;
    chmod($file,0777);
    exec("rm -rf $file");
}
$iso = myreaddir(URL_ROOT."/mnt".$upfile);
?>
<style type="text/css">
    .data_table .row_odd th,.data_table .row_odd th{color:#000}
</style>
<table class="data_table">
    <tr class="row_odd">
        <th>序号</th>
        <th>磁盘镜像文件</th>
        <th>删除</th>
    </tr>
 <?php
    $j=$i-1;
    for ($i= 0;$i<= count($iso)-1; $i++){
        $j=$i+1;
        echo "<tr>";
        echo "<td width='25%'>$j</td>";
        echo "<td>$iso[$i]</td>";
        echo "<td align=center width='25%'><a href='vmdisk_import.php?iso=$iso[$i]' target='_self' onclick='aler();'>";
        echo "<img src='/lms/themes/img/delete.gif'/></a></div></div></td></tr>";
    }
?>
</table>
