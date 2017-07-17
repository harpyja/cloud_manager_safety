<?php
$language_file=array("customer_qihang");
include("../../main/inc/global.inc.php");
api_block_anonymous_users();

if ($_user["status"]==COURSEMANAGER) {
	$default_home_page= api_get_path(WEB_PATH)."user_portal.php";
}
if (api_is_platform_admin ()){
	$default_home_page= api_get_path(WEB_CODE_PATH)."admin/index.php";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo api_get_setting('siteName'); ?></title>
<link href="assets/style.css" rel="stylesheet" type="text/css">
<?php echo import_assets("commons.js");
echo import_assets("jquery-latest.js");
echo import_assets("jquery-plugins/Impromptu.css",api_get_path ( WEB_JS_PATH ));
echo import_assets("jquery-plugins/jquery-impromptu.2.7.min.js");
echo import_assets("jquery-plugins/jquery.wtooltip.js");
?>

<script type="text/javascript">
	function confirmExit(){
		var txt='<?=get_lang("ConfirmExit") ?>';
		$.prompt(txt,{
			buttons:{'确定':true, '取消':false},
			callback: function(v,m,f){
				if(v){
					location.href="<?=api_get_path(WEB_PATH).PORTAL_LAYOUT?>login.php?logout=true";
				}
				else{}
			}
		});
	}
	
	$(document).ready( function() {
		$("#confirmExit").click(function(){
				confirmExit();
		});

		$("a,img").wTooltip();
	});
</script>


<script type="text/javascript">
	function updateIframe(){
	    var wHeight = $(window).height();
	    var mainHeight=wHeight - 165;
	    $('#main').height(mainHeight);
	}

	$(document).ready( function() {
		updateIframe();
	});
	

	$(window).resize(function() {
		updateIframe();
	});
</script>

<?php
if($htmlHeadXtra && is_array($htmlHeadXtra)){
	foreach($htmlHeadXtra as $head_html){
		echo $head_html;
	}
}
?>
</head>
<style>
.right_top_exit{margin-top:0px;z-index:999;display:inline;}
.right_top_exit ul{list-style-type:none;float:right;width:180px}
.right_top_exit li{float:left;}
</style>
<body>
<ul class="logo" style="margin-top:0px;background:url(assets/images/admin_01.gif) repeat-x; float:left;height:94px;width:100%">
<li style="margin-top:0px;background:url(assets/images/admin_02.gif) ; float:left;height:93px;width:45px"></li>
<li style="margin-top:0px;background:url(assets/images/admin_03.gif) ; float:left;height:93px;width:425px"></li>
<li style="margin-top:0px;background:url(assets/images/admin_04.gif) ; width:534px;height:93px;float:right"></li>
</ul>

<link rel="stylesheet" type="text/css"
	href="assets/js/smoothmenu/ddsmoothmenu.css" />
<script type="text/javascript" src="assets/js/smoothmenu/ddsmoothmenu.js">
</script>
<script type="text/javascript">
ddsmoothmenu.init({
	mainmenuid: "smoothmenu1", //menu DIV id
	orientation: 'h', //Horizontal or vertical menu: Set to "h" or "v"
	classname: 'ddsmoothmenu', //class added to menu's outer DIV
	customtheme: ["#B31000", "#F5A830"],
	contentsource: ["smoothmenu1", "menu_static.inc.php"]
})
</script>
<div class="body_banner_up">
<div id="smoothmenu1" class="ddsmoothmenu"></div>
</div>

<div class="index">

<div style=""><iframe id="main" name="main"
	src="<?php echo $default_home_page;?>" height="400" width="100%"
	frameborder="0" scrolling="auto"></iframe></div>

<div id="footer"><iframe id="bottomFrame"
	src="bottom.php"
	width="100%" height="35" frameborder="0" scrolling="no"></iframe></div> 
	
</div>
</body>
</html>
