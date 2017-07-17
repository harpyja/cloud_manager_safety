<?php
/**
 * Created by JetBrains PhpStorm.
 * User: chang
 * Date: 12-11-22
 * Time: 下午9:18
 * To change this template use File | Settings | File Templates.
 */
$language_file = 'admin';
$cidReset = true;

include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();

if(mysql_num_rows(mysql_query("show tables like 'version_number'"))!=1){
    api_sql_query( "CREATE TABLE IF NOT EXISTS `version_number` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `system_version` varchar(128) NOT NULL,
                      `bin_version` varchar(128) DEFAULT NULL,
                      `lession_version` varchar(128) NOT NULL,
                      `version_file` varchar(128) NOT NULL,
                      `description` text,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;
                ", __FILE__, __LINE__ );
}
$sql="select  system_version,bin_version,lession_version from version_number where id = 1";
$res = api_sql_query( $sql, __FILE__, __LINE__ );

while($version_number = Database::fetch_array ( $res )){
    $versions = $version_number;
}
Display::display_header ( $tool_name );

$form = new FormValidator ( 'system_upgrade','POST','system_upgrade.php','');

$form->addElement("html","<tr><td colspan='2' class='title t2'>系统升级</td></tr>");
$form->addElement("html","<tr> <td class='tt'> 当前系统版本号：</td> <td>".$versions['system_version']."</td></tr>");
$form->addElement("html","<tr> <td class='tt'> 引擎版本号：</td> <td>".$versions['bin_version']."</td></tr>");
$form->addElement("html","<tr> <td class='tt'> 课件版本号：</td> <td>".$versions['lession_version']."</td></tr>");
$form->addElement("html",'<tr class="containerBody" style="background-image: initial; background-attachment: initial; background-origin: initial; background-clip: initial; background-color: rgb(255, 255, 204); background-position: initial initial; background-repeat: initial ; "><td align="right" class="formLabel"> 上传系统文件：</td><td class="formTableTd" align="left"> <input class="inputText" style="width:30%" id="upload_file_local" name="user_upload" type="file">&nbsp;&nbsp;</td></tr>');


//$form->addElement ( 'file', 'user_upload', '上传系统文件', array ('class' => 'inputText', 'style' => 'width:30%', 'id' => 'upload_file_local' ) );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="save"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "save", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->freeze (array ("current_version" ));
$system_upgrade['current_version'] = $default['current_version'];
$form->setDefaults ($default);
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '100%' );
if ($form->validate ()) {

    $system_upgrade = $form->getSubmitValues ();
    $tname = $_FILES["user_upload"]["tmp_name"];
    $fname = $_FILES["user_upload"]["name"];

    //后缀名
    $lib=substr(strrchr($fname,"."),1);
    $dir = "/tmp/";
        move_uploaded_file ( $tname, "$dir/$fname" );
        exec("cd $dir ; tar -zxvf $dir/$fname  ");
        exec("cd $dir/update ;sudo ./update.sh ");
        exec("cd $dir/ ;rm -rf update ; rm -rf $fname ");

}
//Display::display_footer ( TRUE );
?>

<aside id="sidebar" class="column systeminfo open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/systeminfo.php">系统管理</a> &gt; 系统升级 </h4>
    <article class="module width_full hidden ip">
            <?php $form->display ();?>
    </article>
</section>
</body>
</html>
