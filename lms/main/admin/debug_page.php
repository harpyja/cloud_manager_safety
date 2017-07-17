<?php
$language_file = array ('admin' );
$cidReset = true;
include_once ('../inc/global.inc.php');
//api_protect_admin_script ();
//if (! isRoot ()) api_not_allowed ();
Display::display_header ();

//调试页面


?>



            <aside id="sidebar" class="column cloud open">
                    <div id="flexButton" class="closeButton close"></div>
            </aside>
            <section id="main" class="column">
                     <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; 云平台</h4>
                     <div class="cloud-menu boxPublic">
<!--                         <a href="<?=URL_APPEND?>main/admin/vmmanage/vmmanage_iframe.php" title="虚拟化管理" class="cp m1">虚拟化管理</a>
                         <a href="<?=URL_APPEND?>main/admin/net/vm_list_iframe.php" title="网络拓扑设计" class="cp m2">网络拓扑设计</a>
                         <a href="<?=URL_APPEND?>main/admin/vmmanage/centralized.php" title="集中管理设置" class="cp m3">集中管理设置</a>
                         <a href="<?=URL_APPEND?>main/admin/vmdisk/vmdisk_list.php" title="虚拟模板设置" class="cp m4">虚拟模板管理</a>-->
                         <a href="<?=URL_APPEND?>main/admin/token_bucket/token_bucket_list.php" title="令牌桶管理" class="cp m5">令牌桶管理</a>
<!--                         <a href="<?=URL_APPEND?>main/admin/cloud/clouddesktop.php" title="云桌面终端" class="cp m6">云桌面终端</a>
                         <a href="<?=URL_APPEND?>main/admin/cloud/clouddesktopscan.php" title="云桌面扫描" class="cp m7">云桌面扫描</a>
                         <a href="<?=URL_APPEND?>main/admin/cloud/clouddesktopdisk.php" title="云桌面存储空间" class="cp m8">云桌面存储空间</a>
                         <a href="<?=URL_APPEND?>main/admin/vmmanage/unified_shut.php" title="虚拟化统一关机" class="cp m9">云桌面存储空间</a>-->
                     </div>
         </section>   
</body>
</html>