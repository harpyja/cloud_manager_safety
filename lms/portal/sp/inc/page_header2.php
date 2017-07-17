<?php
if (! defined ( 'IN_QH' )) exit ( 'Access Denied !' );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=api_get_setting ( 'siteName' )?></title>
<script type="text/javascript">
	var web_path="<?=api_get_path(WEB_PATH)?>";
	var web_qh_path="<?=(WEB_QH_PATH)?>";
</script>
<?php
echo import_assets ( "index.css",WEB_QH_PATH );
echo import_assets ( "commons.js" );
echo import_assets ( "jquery-latest.js" );
echo import_assets ( "jquery-plugins/Impromptu.css", api_get_path ( WEB_JS_PATH ) );
echo import_assets ( "jquery-plugins/jquery-impromptu.2.7.min.js" );
echo import_assets ( "jquery-plugins/jquery.wtooltip.js" );
echo import_assets ( "js/portal.js" ,WEB_QH_PATH);

if ($htmlHeadXtra && is_array ( $htmlHeadXtra )) {
	foreach ( $htmlHeadXtra as $head_html ) {
		echo $head_html;
	}
}
?>
</head>
