<?php
$$language_file =array('document');
include_once("../inc/global.inc.php");

api_protect_course_script();
$is_allowed_to_edit = api_is_allowed_to_edit();

$htmlHeadXtra[]='<script type="text/javascript" src="'.api_get_path(WEB_JS_PATH).'jquery_last.js"></script>';
$htmlHeadXtra[]='<script type="text/javascript" src="'.api_get_path(WEB_JS_PATH).'jquery-plugins/jquery.textarearesizer.js"></script>';
$htmlHeadXtra[]='<script type="text/javascript">			
			$(document).ready(function() {				
				$(\'iframe.resizable:not(.processed)\').TextAreaResizer();
			});
		</script>';
$htmlHeadXtra[]='<style type="text/css">
			div.grippie {
				background:#EEEEEE url('.api_get_path(REL_PATH).'themes/js/jquery-plugins/grippie.png) no-repeat scroll center 2px;
				border-color:#DDDDDD;
				border-style:solid;
				border-width:0pt 1px 1px;
				cursor:s-resize;
				height:9px;
				overflow:hidden;
			}
			.resizable-textarea textarea {
				display:block;
				margin-bottom:0pt;
				width:95%;
				height: 20%;
			}
		</style>';
Display::display_header ( NULL );
?>
<iframe class="resizable" src="<?php echo $url;?>"  width="100%" height="500px"></iframe>

<?php
Display::display_footer ();
?>
