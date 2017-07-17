<?php
/* Custom variables */
date_default_timezone_set('Asia/Shanghai');
define('BASE_DIR', '/tmp/www/lms/router');
define('BASE_WWW', '/lms/router/html');
define('BASE_PORT', '200');	// Change if you need different TELNET port.
define('BCK_RETENTION', '10');

/* GraphViz Font Configuration: should be OK */
define('GRAPHVIZ_FONT_PATH', '/usr/share/fonts/gnu-free');
define('GRAPHVIZ_FONT_EDGE', 'FreeMono');
define('GRAPHVIZ_FONT_NODE', 'FreeSansBold');

/* Don't touch */
define('BASE_BIN', BASE_DIR.'/bin');
define('DATABASE', BASE_DIR.'/data/database.sdb');
define('WRAPPER', BASE_DIR.'/bin/wrapper-linux');
define('BASE_HUB', 10001);

require_once(BASE_DIR."/html/includes/_devices.php");
require_once(BASE_DIR."/html/includes/_exec.php");
require_once(BASE_DIR."/html/includes/_labs.php");
require_once(BASE_DIR."/html/includes/_netmap.php");
require_once(BASE_DIR."/html/includes/_web.php");

include_once ("../../../main/inc/global.inc.php");//changzf 2012/1/5
?>
