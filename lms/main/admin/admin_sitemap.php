<?php
$language_file = array('admin');
$cidReset = true;
include ('../inc/global.inc.php');

api_block_anonymous_users();
$page_prefix=api_get_path(WEB_CODE_PATH);
$ctx_path=api_get_path(REL_PATH);
$menuNo=isset($_GET['menuNo'])?intval(getgpc('menuNo','G')):'19';

$user_roles=explode(",",$_user ['roles']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>


<style type="text/css">
* {
	padding: 0px;
	margin: 0px;
}

html,body {
	width: 100%;
	height: 100%;
}

body {
	height: 100%;
	font-size: 12px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	overflow: hidden;
	z-index: 1;
}

body.showmenu {
	background: url(leftmenu_bg.gif) -11px top repeat-y;
}

.allmenu {
	width: 700px;
	background: #FFF;
	border: 2px solid #CCC;
	z-index: 999;
	position: absolute;
	left: 50%;
	top: 38px;
	margin-left: -350px;
	padding: 5px 0px; 
	text-align: center; 
	width: 708px;
	min-height:300px;
}

.allmenu .allmenu-box {
	width: 686px;
	margin: 0px auto;
	text-align: left;
	overflow: hidden;
	padding-left: 2px;
}

.maptop {
	float: left;
	width: 107px;
	overflow: hidden;
	padding-right: 3px;
	padding-left: 3px;
	border-right: 1px solid #EEE;
	border-left: 1px solid #EEE;
	margin-left: -1px; 
	width: 115px;
}

.maptop dt.bigitem {
	padding: 2px;
	/*background: #455656;*/
	background: #1086DE;
	color: #FFF;
	line-height: 19px;
	font-weight: bold;
	margin-bottom: 3px;
	text-indent: 3px;
}

.mapitem dt {
	line-height: 21px;
	font-weight: bold;
	text-indent: 10px;
	background: #EFF1F1;
}

.mapitem ul {
	margin-top: 2px;
	margin-bottom: 5px;
}

.mapitem ul li {
	text-indent: 13px;
	line-height: 19px;
	background: url(arrr.gif) 4px 6px no-repeat;
}

.allmenu a {
	color: #5C604F;
	text-decoration: none;
}

.allmenu a:hover {
	color: #F63;
}
</style>
</head>
<body class="showmenu">
<div class="allmenu">
<div class="allmenu-box">

<?php 
$idx=1;
$main_menu_table = Database :: get_main_table(TABLE_MAIN_MENU);
$sql2="SELECT * FROM ".$main_menu_table." WHERE is_enabled=1 AND menu_no LIKE '".$menuNo."__' ORDER BY menu_no "; 
$sql_result2 = api_sql_query($sql2, __FILE__, __LINE__);
while($row2=Database::fetch_array($sql_result2,"ASSOC")){
	if(is_display_menu_item($row2['priv_roles'],$row2['priv_status'])){
		$style=($idx==1?'style="display: ;"':'style="display: none;"');
?>
<dl class='maptop'>
	<dt class='bigitem'><?=$row2['menu_name']?></dt>
	<dd>
	<dl class='mapitem'>

		<dd>
		<ul class='item'>
		<?php 
		$sql3="SELECT * FROM ".$main_menu_table." WHERE is_enabled=1 AND menu_no LIKE '".$row2['menu_no']."__' ORDER BY menu_no ";		$sql_result3 = api_sql_query($sql3, __FILE__, __LINE__);
		while($row3=Database::fetch_array($sql_result3,"ASSOC")){
			if(is_display_menu_item($row3['priv_roles'],$row3['priv_status'])){
	?>
			<li><a href="<?=$ctx_path.$row3['menu_url']?>" target="main"><?=$row3['menu_name']?></a></li>
			<?php } } ?>
		</ul>
		</dd>
	</dl>

	</dd>
</dl>
<?php 
$idx++;
}
}
	
	?>
</div>
</div>
</body>
</html>
