<?php
header ( 'Content-Type: text/html; charset=' . SYSTEM_CHARSET );
if (isset ( $httpHeadXtra ) && $httpHeadXtra) {
	foreach ( $httpHeadXtra as $thisHttpHead ) {
		header ( $thisHttpHead );
	}
}
$document_language = 'en';
$my_code_path = api_get_path ( WEB_CODE_PATH );
$my_style = 'default';

/**platform type 20130329 changzf start**/
$platforms=file_get_contents('../../../storage/DATA/platform.conf');
$platform_array=explode(':',$platforms);
$platform=intval(trim($platform_array[1]));
/**platform type 20130329 changzf end**/
//var_dump($platform);

if($platform==1){
  $platform_name='渗透系统管理平台';
  $platform_name1='渗透';
}
elseif($platform==2){
  $platform_name='靶机系统管理平台';
  $platform_name1='靶机';
}else{
    $platform_name='网络云教育平台';
    $platform_name1='网络';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title><?=$platform_name;?>欢迎您</title>
    <link rel="stylesheet" href="<?=api_get_path ( WEB_PATH )?>themes/css/layout.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="<?=api_get_path(WEB_PATH).PORTAL_LAYOUT?>js/jbox-v2.3/jBox/Skins/Blue/jbox.css" media="all"/>
    <!--[if lt IE 9]>
    <link rel="stylesheet" href="<?=api_get_path ( WEB_PATH )?>themes/css/ie.css" type="text/css" media="screen" />
  <?php  echo import_assets('themes/js/html5.js',api_get_path ( WEB_PATH ));?>
    <![endif]-->
    <?php
    echo import_assets('themes/js/html5.js',api_get_path ( WEB_PATH ));
    //echo import_assets('themes/css/layout.css',api_get_path ( WEB_PATH ));
    //echo import_assets('themes/css/ie.css',api_get_path ( WEB_PATH ));
    echo import_assets('themes/js/jquery-1.5.2.min.js',api_get_path ( WEB_PATH ));
    //echo import_assets('themes/js/hideshow.js',api_get_path ( WEB_PATH ));
    echo import_assets('themes/js/jquery.tablesorter.min.js',api_get_path ( WEB_PATH ));
    echo import_assets('themes/js/jquery.equalHeight.js',api_get_path ( WEB_PATH ));
    ?>
    <script type="text/javascript">
        $(function(){
            //网站头部
            var $header = $("");
            $("#header").append($header);
            /**
             *  后台菜单栏目JS
             **/
            $cloud =$("<div class='navs'><dl class='nav-list'><dt><?=$platform_name1;?>系统管理</dt><dd class='two-nav-list'><ul><li><a href='/lms/main/admin/platform/vmmanage_iframe.php' title='<?=$platform_name1;?>模板调度管理'><?=$platform_name1;?>模板调度管理</a></li><li><a href='/lms/main/admin/platform/centralized.php' title='集中管理'>集中管理</a></li><li><a href='/lms/main/admin/platform/vmdisk_list.php' title='<?=$platform_name1;?>虚拟模板管理'><?=$platform_name1;?>虚拟模板管理</a></li><li><a href='/lms/main/admin/platform/token_bucket_list.php' title='令牌桶管理'>令牌桶管理</a></li></ul></dd></dl> </div>");
            $(".cloud").append($cloud);
            /**
             * 后台导航栏JS
             **/
            $navigation = $("");
            $("#secondary_bar").append($navigation);
        })
    </script>
    <script type="text/javascript" src="hideshow.js"></script>
	<script type="text/javascript" src="<?=WEB_QH_PATH?>js/jbox-v2.3/jBox/jquery.jBox-2.3.min.js"></script>
	<script type="text/javascript" src="<?=WEB_QH_PATH?>js/jbox-v2.3/jBox/i18n/jquery.jBox-zh-CN.js"></script>
    <script type="text/javascript">

        $(document).ready(function()
                {
                    $(".tablesorter").tablesorter();
                }
        );
        $(document).ready(function() {

            //When page loads...
            $(".tab_content").hide(); //Hide all content
            $("ul.tabs li:first").addClass("active").show(); //Activate first tab
            $(".tab_content:first").show(); //Show first tab content

            //On Click Event
            $("ul.tabs li").click(function() {

                $("ul.tabs li").removeClass("active"); //Remove any "active" class
                $(this).addClass("active"); //Add "active" class to selected tab
                $(".tab_content").hide(); //Hide all tab content

                var activeTab = $(this).find("a").attr("href"); //Find the href attribute value to identify the active tab + content
                $(activeTab).fadeIn(); //Fade in the active ID content
                return false;
            });

        });
    </script>
    <script type="text/javascript">
        $(function(){
            $('.column').equalHeight();
        });
    </script>
    <?php
    if (isset ( $htmlHeadXtra ) && $htmlHeadXtra) {
        foreach ( $htmlHeadXtra as $this_html_head ) {
            echo ($this_html_head);
        }
    }

    if (isset ( $htmlIncHeadXtra ) && $htmlIncHeadXtra) {
        foreach ( $htmlIncHeadXtra as $this_html_head ) {
            include ($this_html_head);
        }
    }
    $userName = $_SESSION ['_user'] ['firstName'] . " " . $_SESSION ['_user'] ['lastName'];
    $userNo = $_SESSION ['_user'] ['username'];
    if (! is_not_blank ( $userNo )) {
        $user_info = api_get_user_info ( api_get_user_id () );
        $userNo = $user_info ['username'];
        $userName = $user_info ['firstName'] . " " . $user_info ['lastName'];
    }

    ?>
<script type="text/javascript">
function openWindow(width,height,url,title){
var w = width;
var h = height;
var u = url;
var t = title;
$('.column').equalHeight();
$.jBox(u, {
	title: t,
	width: w,
	height: h,
	buttons: { '关闭': true }
});
}     
</script>
<script type="text/javascript">
        $(function(){
            var $close = $("#confirmExit");
            $close.click(function(){
                var submit = function (v, h, f){
                    if (v == true)
                        jBox.tip("操作成功..系统正在退出", 'info');
                        location.href="<?=api_get_path(WEB_PATH).PORTAL_LAYOUT?>login.php?logout=true";
                };
                // 自定义按钮
                $.jBox.confirm("你确定退出系统吗？", "系统提示", submit, { buttons: { '确定': true} });
            })
        })

    </script>



</head>

<body>
<header id="header">
<hgroup><h1 class='site_title'><a href='index.php'><?=$platform_name;?></a></h1>
<h2 class="section_title"><span class="welcome">您好：<?php echo '<a class="helpex dd2" href="user_profile.php">',  $userName, "(", $userNo, ")";echo '</a>'?> , 欢迎使用<?=$platform_name;?><a class="helpex dd2" id="confirmExit" target="_top" >退出</a></span></h2>


    <?php if (api_is_admin ()) : ?>
        <div class="btn_view_site"><a href='<?=URL_APPEND . PORTAL_LAYOUT?>' title='后台管理'>前台首页</a></div>
        <?php endif; ?>
</hgroup>
    </header>

<?php
$language_file = array ('index' );
include_once ('../../main/inc/global.inc.php');
api_block_anonymous_users ();

if ($_user ["status"] == COURSEMANAGER) {
    ?>
    <?php
}
if (api_is_platform_admin ()) {
    ?>

<section id="secondary_bar">

    <ul class='nav'>
        <li><a href="<?=URL_APPEND?>main/admin/platform/index.php" title='首页'>首页</a></li>
        <li><a href="<?=URL_APPEDND?>/main/admin/platform/vmmanage_iframe.php" title="<?=$platform_name1;?>模板调度管理"><?=$platform_name1;?>模版调度管理</a></li>
        <li><a href="<?=URL_APPEND?>main/admin/platform/centralized.php" title="集中管理设置">集中管理设置</a></li>
        <li><a href="<?=URL_APPEND?>main/admin/platform/vmdisk_list.php" title="<?=$platform_name1;?>虚拟模板管理"><?=$platform_name1;?>虚拟模板管理</a></li>
        <li><a href="<?=URL_APPEND?>main/admin/platform/token_bucket_list.php" title='令牌桶管理'>令牌桶管理</a></li>
    </ul>

</section>

<?php
}
?>


