<?php
//$language_file = array ("registration", "admin" );
$cidReset = true;
include_once ('../../inc/global.inc.php');
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

$default_values['number']=DATABASE::getval("select  `number` from `vm_max_num` where `description`='vm_max_num'",__FILE__,__LINE__);

$form = new FormValidator ( 'settings', 'post', 'centralized.php?category=' . $strCategory );
Display::setTemplateSettings ( $form, '97%' );

$form->addElement ( 'text', 'number', '每台服务器虚拟机限制数量（请输入数字）',array('type'=>'textarea','rows'=>'5','cols'=>'80'));
$form->addRule ( 'number', get_lang ( '您的输入不是数字，请重试！' ), 'numeric' );
$form->addElement ( 'style_submit_button', null, get_lang ( '确定' ), 'class="save"' );
$form->setDefaults ( $default_values );

if ($form->validate ()) { //处理保存
    $values = $form->exportValues ();
    $number=$values['number'];
    $number=(int)$number;
    $counts=DATABASE::getval("select count(*) from `vm_max_num`",__FILE__,__LINE__);
    if($counts){
        $sql="UPDATE `vm_max_num` SET  `number`= '".$number."' WHERE  `description`='vm_max_num'";
    }else{
        $sql="INSERT INTO `vslab`.`vm_max_num` (`id`, `number`, `description`) VALUES (NULL, '".$number."', 'vm_max_num')";
    }
    $result=api_sql_query ($sql, __FILE__,__LINE__);
    if($result){
        api_redirect ( 'vmmaxnumber.php?action=success' );    
    }else{
        api_redirect ( 'vmmaxnumber.php' );
    }
}

Display::display_header ( NULL );
?>
<aside id="sidebar" class="column cloud open">
    <div id="flexButton" class="closeButton close">

    </div>
</aside>
<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/cloud_menu.php">云平台</a> &gt; 虚拟机设置</h4>

<!--    <div class="managerSearch">
       
    </div>-->
    <br>
    <div class="manage-tab-content">
        <div class="manage-tab-content-list">
            <article class="synchro ip">
                <form action="#" method="post">
                    <table cellpadding="0" cellspacing="0" class="settingstable">
                        <tbody>
                       <?php $form->display ();?>
                        </tbody>
                    </table>
                </form>
            </article>

        </div>
</section>
</body>
</html>