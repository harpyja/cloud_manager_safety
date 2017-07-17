<?php
$language_file = array ('admin' );
$cidReset = true;
include_once ('../../inc/global.inc.php');
include_once ('header.inc.php');

?>

	<aside id="sidebar" class="column cloud open">
        <div id="flexButton" class="closeButton close">
         	   	
        </div>
	</aside>
	<section id="main" class="column">
		<h4 class="page-mark">当前位置：<a href="#">平台首页</a></h4>
		<div class="cloud-menu boxPublic">

        	<a href="<?=URL_APPEND?>main/admin/platform/vmmanage_iframe.php" title="<?=$platform_name1;?>模板调度管理" class="cp m1"><?=$platform_name1;?>模板调度管理</a>

            <a href="<?=URL_APPEND?>main/admin/platform/centralized.php" title="集中管理设置" class="cp m3">集中管理设置</a>
            <a href="<?=URL_APPEND?>main/admin/platform/vmdisk_list.php" title="<?=$platform_name1;?>虚拟模板管理" class="cp m4"><?=$platform_name1;?>虚拟模板管理</a>
            <a href="<?=URL_APPEND?>main/admin/platform/token_bucket_list.php" title="令牌桶管理" class="cp m5">令牌桶管理</a>
        </div>
	 </section>   
</body>
</html>
