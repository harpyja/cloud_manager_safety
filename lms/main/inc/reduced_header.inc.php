<?php
header ( 'Content-Type: text/html; charset=' . SYSTEM_CHARSET );
if (isset ( $httpHeadXtra ) && $httpHeadXtra) {
	foreach ( $httpHeadXtra as $thisHttpHead ) {
		header ( $thisHttpHead );
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>
<?php

if (! empty ( $nameTools )) {
	echo $nameTools . ' - ';
}

if (! empty ( $_course ['official_code'] )) {
	echo $_course ['official_code'] . ' - ';
}

echo get_setting ( 'siteName' );
if (isset ( $tool_name )) echo $tool_name;
?>
</title>

<style type="text/css" media="screen, projection">
/*<![CDATA[*/
@import
"<?=api_get_path ( WEB_CSS_PATH ) . 'default.css';?>";
/*]]>*/
</style>
<link rel="shortcut icon"
	href="<?=api_get_path ( WEB_PATH );?>favicon.gif" type="image/x-icon" />
<meta http-equiv="Content-Type"
	content="text/html; charset=<?= SYSTEM_CHARSET?>" />
<?php
echo import_assets("jquery-latest.js");
echo import_assets("commons.js");
if (isset ( $htmlHeadXtra ) && $htmlHeadXtra) {
	foreach ( $htmlHeadXtra as $this_html_head ) {
		echo ($this_html_head);
	}
}
?>
</head><body><div>