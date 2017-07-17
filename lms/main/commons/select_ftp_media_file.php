<?php
$language_file = array ('admin' );
require_once ('../inc/global.inc.php');
$this_section = SECTION_PLATFORM_ADMIN;
api_block_anonymous_users();

$path=api_get_path(SYS_FTP_ROOT_PATH).'media/'.api_get_user_name().'/';


$_POST['dir'] = urldecode($_POST['dir']);
$encoding=api_get_system_encoding();
if( file_exists($root . getgpc('dir','P')) ) {
	//$_POST['dir']=api_to_system_encoding($_POST['dir'],SYSTEM_CHARSET);
	$files = scandir($root . getgpc('dir','P'));
	natcasesort($files);
	if( count($files) > 2 ) { /* The 2 accounts for . and .. */
		echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
		// All dirs
		/*foreach( $files as $file ) {
			if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && is_dir($root . $_POST['dir'] . $file) ) {
				$filename=api_utf8_encode($file,$encoding);
				echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . ($_POST['dir'] . $filename) . "/\">" . ($filename) . "</a></li>";
			}
		}*/
		// All files
		foreach( $files as $file ) {
			if( file_exists($root . getgpc('dir','P') . $file) && $file != '.' && $file != '..' && !is_dir($root .getgpc('dir','P') . $file) ) {
				$filename=api_utf8_encode($file,$encoding);
				$ext = preg_replace('/^.*\./', '', $file);
				if('flv'==strtolower(getFileExt($file)))			
				echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . (getgpc('dir','P').$filename) . "\">" .$filename . "</a></li>";
			}
		}
		echo "</ul>";	
	}
	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>

<script src="<?=api_get_path(WEB_JS_PATH)?>jquery_last.js" type="text/javascript"></script>
<script src="<?=api_get_path(WEB_JS_PATH)?>jquery-plugins/filetree/jqueryFileTree.js" type="text/javascript"></script>
<link href="<?=api_get_path (WEB_JS_PATH )?>jquery-plugins/filetree/jqueryFileTree.css" rel="stylesheet" type="text/css"
	media="screen" />

	
<script type="text/javascript" src="<?php echo api_get_path(WEB_JS_PATH) ?>utility.js"></script>
<script type="text/javascript">
	var parent_window = getOpenner();
	var to_form = parent_window.<?php echo getgpc('FORM_NAME') ?>;	

	var to_id =   to_form.<?php echo getgpc('TO_ID');?>;
	var to_name = to_form.<?php echo getgpc('TO_NAME');?>;
	
	$(document).ready( function() {				
		$('#fileTreeDemo').fileTree({ root: '<?=$path ?>', script: '<?=api_get_path(WEB_CODE_PATH) ?>commons/select_ftp_media_file.php', folderEvent: 'click', expandSpeed: 750, collapseSpeed: 750, multiFolder: false }, function(file) {
			//alert(file); 
			file_name=file.replace('<?=$path ?>','/');	
			to_id.value=file_name;
			to_name.value=file_name;	
		});				
				
	});
</script>


<style type="text/css">
.NoteBox {
	background: #E2EFF6;
	border: 1px solid #5C9EBF;
	text-align: left;
	letter-spacing: 0px;
	padding: 5px 5px 5px 10px;
	text-indent: 0px;
	line-height: 20px;	
	margin: 0 auto;
	font-size: 12px;
}

</style>
</head>
<body>
<div id="confirmMsg" class="NoteBox"  style="display:none">双击部门名称前单选框：选择一个部门并关闭本窗口；单击部门名称链接：选择一个部门,然后点击底部关闭按钮。<a href="#" onclick="javascript:$$('confirmMsg').style.display='none';"><span style="font-size: 12px; float:right">隐藏</span></a></div>

<div class="example">
<div id="fileTreeDemo" class="demo"></div>
</div>
</body>
</html>