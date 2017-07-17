<?php
/**
 * @author Andrea Dainese
 */
/**
 * Function to print an error msg
 *
 * @param	string	$msg	String to print
 */
$cidReset = true;
include_once ("../../portal/sp/inc/app.inc.php");




function msg_error($msg) {
	print "<p><font color='red'><b>Error: $msg</b></font></p>";
}
/**
 * Function to print a warning msg
 *
 * @param	string	$msg	String to print
 */
function msg_warning($msg) {
	print "<p><font color='purple'><b>Warning: $msg</b></font></p>";
}
/**
 * Function to print an info msg
 *
 * @param	string	$msg	String to print
 */
function msg_info($msg) {
	print "<p>$msg</p>";
}
/**
 * Function to print all configured IOSes
 */
function ios_list_print() {
?>
	<TABLE id='templatemo_table' width="100%">
		<TR>
			<TH align="center">Alias</TH>
			<TH align="center">Filename</TH>
			<TH align="center">Actions</TH>
		</TR>
<?php	
	$result = ios_list();
	foreach ($result as $row) { ?>
		<TR id="file_<?php print $row['name'] ?>">
			<TD><?php print $row['alias'] ?></TD>
			<TD><?php print BASE_BIN."/".$row['name'] ?></TD>
			<TD align="center"><IMG onclick="deleteFile('<?php print $row['name'] ?>')" src="images/wipe.png" width="32" height="32" title="Delete this file"></TD>
		</TR>
<?php
	}
?>
	</TABLE>
<?php
}
/**
 * Function to print all initial configurations
 */
function config_list_print() {
?>
	<TABLE id='templatemo_table' width="100%">
		<TR>
			<TH>名称</TH>
			<TH>操作</TH>
		</TR>
<?php
    $networkmap = Database::get_main_table ( networkmap);
    $sql = "SELECT id,name,config FROM `configs`";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $vm= array ();
    while ( $vm = Database::fetch_row ( $res) ) {
        $vms [] = $vm;
    }
    for ($i= 0;$i<= count($vms)-1; $i++){
        ?>
        <TR id="<?php echo $vms[$i][0];?>">
            <TD><?php echo $vms[$i][1];?></TD>
            <TD align="center">
                <A href="<?php print $_SERVER['PHP_SELF'] ?>?action=show&id=<?php echo $vms[$i][0];?>"><IMG src="images/open.png" width="32" height="32" title="Show this config"></A>
           </TD>
        </TR>

        <?php } ?>


	</TABLE>
<?php
}
/**
 * Function to show one initial configurations
 *
 * @param	int		$id		ID of the initial config
 */
function config_show($id) {
?>
		<PRE><?php print config_get_config($id) ?></PRE>
<?php
}
/**
 * Function to print a lab
 *
 * @param	int		$id		ID of the lab
 */
function lab_show($id) {
?>
	<H3><?php print lab_get_descriptions($id) ?></H3>
	<TABLE width="100%" width="100%">
		<TR valign="top">
			<TD width="1%"><A href="display.php?action=original&id=<?php print $id ?>"><IMG src="display.php?action=netmap&id=<?php print $id ?>" title="Show auto-generated diagram"></A></TD>
		</TR>
		<TR valign="top">
			<TD width="99%">
				<?php device_show_all($id) ?>
			</TD>
		</TR>
	</TABLE>
	<div id="template_info"><?php print lab_get_infos($id) ?></div>
<?php
}
///**
// * Function to print all devices in a lab
// *
// * @param	int		$lab_id		ID of the lab
// */
//
function device_show_alls($lab_id) {
    $sql = "SELECT * FROM devices where lab_id=".$lab_id;
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
    $device_show= array ();

    while ( $device_show = Database::fetch_row ( $result) ) {
        $device_shows [] = $device_show;
    }
    echo "<pre>";var_dump($device_shows);echo "</pre>";
    ?>

<?php
}
/**
 * Function to print a standard header
 */
function page_header() {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="author" content="feilx">
<title><?=api_get_setting ( 'siteName' )?></title>

<link href="templatemo_style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
    _editor_url  = "xinha/"   // (preferably absolute) URL (including trailing slash) where Xinha is installed
    _editor_lang = "en";       // And the language we need to use in the editor.
    _editor_skin = "silva";    // If you want use a skin, add the name (of the folder) here
    _editor_icons = "Classic"; // If you want to use a different iconset, add the name (of the folder, under the `iconsets` folder) here
</script>
<script type="text/javascript" src="xinha/XinhaCore.js"></script>
<script type="text/javascript" src="xinha/XinhaConfig.js"></script>
<script type='text/javascript' src='js/jquery-1.8.2.min.js'></script>
<script type='text/javascript' src='js/jquery-ui-1.8.23.custom.min.js'></script>
<script language="javascript" type="text/javascript">
function clearText(field)
{
    if (field.defaultValue == field.value) field.value = '';
    else if (field.value == '') field.value = field.defaultValue;
}
</script>
<link rel="stylesheet" href="css/nivo-slider.css" type="text/css" media="screen" />


    <link href="<?=WEB_QH_PATH?>index.css" rel="stylesheet" type="text/css">
    <?php
    echo import_assets ( "commons.js" );
    echo import_assets ( "jquery-latest.js" );
    echo import_assets ( "jquery-plugins/Impromptu.css", api_get_path ( WEB_JS_PATH ) );
    echo import_assets ( "jquery-plugins/jquery-impromptu.2.7.min.js" );
    echo import_assets ( "jquery-plugins/jquery.wtooltip.js" );
    echo import_assets ( "js/portal.js", WEB_QH_PATH );

    $userName = $_SESSION ['_user'] ['firstName'];
    $userNo = $_SESSION ['_user'] ['username'];
    ?>
    <script type="text/javascript">
        var web_path="<?=api_get_path(WEB_PATH)?>";
        var web_qh_path="<?=WEB_QH_PATH?>";
        $(document).ready( function() {
            $("a,img").wTooltip({fadeIn:"fast",offsetY:6,className:'wTooltip'});
            $("#confirmExit").click(function(){
                var txt='<?=get_lang("ConfirmExit") ?>';
                $.prompt(txt,{
                    buttons:{'确定':true, '取消':false},
                    callback: function(v,m,f){
                        if(v){
                            //dengxin 20120906
                            location.href="../../main/cloud/cloudvmstop.php?user_id=<?=$user_id?>";
                        }

                    }
                });
            });
        });
    </script>
</head>

<body>
<div class="logo">
<?php

    if (api_is_platform_admin ()) {
        $default_home = api_get_path ( WEB_CODE_PATH ) . "admin/index.php";
    }else{
        $default_home = api_get_path ( WEB_PATH ) . "user_portal.php";
    }
    ?>
    <a href="<?=api_get_path(WEB_QH_PATH)?>">
        <img src="<?=api_get_path(WEB_PATH) ?>panel/default/assets/images/logo3.gif" style="margin-top: 10px;height:50px;">
    </a>
    <?php
    if (api_is_admin ()) :
        ?>
        <div class="helpex dd2"></div>
        <a class="helpex dd2" href="<?=$default_home?>" target="_blank">后台管理</a> <?php endif; ?>
    <div class="helpex dd2">|</div>

    <a class="helpex dd2" id="confirmExit" target="_top" href="javascript:void(0);">[<?=get_lang("ExitSys")?>]</a>


    <?php

    echo '<a class="helpex dd2" href="/lms/portal/sp/user_profile.php">';
    echo get_lang ( "WelcomeTo" ), ":&nbsp;&nbsp;", $userName, "(", $userNo, ")";
    echo '</a>';
    ?>

    <span class="site_name" style="font-size: 16px;line-height: 50px">
                      <p align="center"><b><?=api_get_setting('siteName')?></b></p>
                </span>

</div>
<?php display_tab (labs);?>
<div class="body_banner_down">
    <!--by changzf add 90 line-->
    <div style="float: left; width: 300px;">&nbsp;</div>
    <a href="labs.php" class="label dd2" style="margin-left: 55px; _margin-left: 25px;"><b>实验</b></a>
</div>
<div id="templatemo_wrapper">

<?php
}
/**
 * Function to print an info box after the header (homepage only)
 */
function page_middle_main() {
?>
    <div id="templatemo_middle">

    	<div id="intro">
        	<h2>IOU Web Interface</h2>
            <p>IOU improve your Cisco learning giving you a flexible and faster way to create complex laboratories. This Web Interface gives you the flexibility you need to use Cisco IOU without understanding Linux OS.</p>
            <a class="learn_more" href="labs.php">Go to Labs</a>
        </div>
        <div id="slider">
            <img src="images/example.jpg" alt="Laboratory dashboard." title="Laboratory dashboard." />
        </div>
	</div>

	<div id="tm_top"></div>
<?php
}
/**
 * Main page for lab
 */
function page_main_open_lab($title) {
?>
  <div id="tm_top"></div>
  <div id="templatemo_main">

    	<div class="col_w900 col_w900_last">
            <div>


            <div>

            	<h2><?php print $title; ?></h2>
<?php
}
/**
 * Main page for config
 */
function page_main_open_config($title) {
?>
  <div id="tm_top"></div>
  <div id="templatemo_main">

    	<div class="col_w900 col_w900_last">
            <div>


            <div>

            	<h2><?php print $title; ?></h2>
<?php
}
/**
 * Main page for ios (under tools)
 */
function page_main_open_ios($title) {
?>
  <div id="tm_top"></div>
  <div id="templatemo_main">

    	<div class="col_w900 col_w900_last">
            <div>


            <div>
				<div id="action_buttons">
					<A href="<?php print $_SERVER['PHP_SELF'] ?>?action=ios&add=1"><IMG src="images/add.png" width="32" height="32" title="Add file"></A>
<?php
					if (isset($_GET['add'])) {
?>
					<A href="<?php print $_SERVER['PHP_SELF'] ?>?action=ios"><IMG src="images/list.png" width="32" height="32" title="Show all files"></A>
<?php
					}
?>
				</div>
            	<h2><?php print $title; ?></h2>
<?php
}
/**
 * Main page (simple)
 */
function page_main_open_simple($title) {
?>
  <div id="tm_top"></div>
  <div id="templatemo_main">

    	<div class="col_w900 col_w900_last">
            <div>
            <div>
            	<h2><?php print $title; ?></h2>
<?php
}
/**
 * Main page close
 */
function page_main_close() {
	/* Daily backup */
	//database_backup();
?>
				</div>
			</div>
			<div class="cleaner"></div>
		</div>
		<div class="cleaner"></div>
	</div> <!-- end of main -->
<?php
}
/**
 * Footer
 */
function page_footer() {
?>
	<div id="tm_bottom"></div>
	<div id="templatemo_footer"> </div> <!-- end of footer -->
</div>
</body>
</html>
<?php
}
	?>
