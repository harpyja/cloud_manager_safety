 <?php
header("content-type:text/html;charset=utf-8");
require_once ('../inc/global.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');

$port =$_GET['port'];   
$system =$_GET['system'];   
$str='';
$str2='';
   if(isset($_GET['host']) && $_GET['host']!==''){
       $str.="?host=".$_GET['host'];
       if(isset($_GET['port']) && $_GET['port']!==''){
                $str.="&port=".$_GET['port'];
       }       
   }
   if(isset($_GET['host']) && $_GET['host']!==''){
       $str2.="host=".$_GET['host'];
       if(isset($_GET['port']) && $_GET['port']!==''){
                $str2.="&port=".$_GET['port'];
       }       
   } 
$sql="select user_id,port,vmid from vmtotal where proxy_port=$port";
$ress = api_sql_query ( $sql, __FILE__, __LINE__ );
$vm = Database::fetch_row ( $ress);
$sql_snap="select id from `snapshot` where `user_id`='".$vm[0]."' and  `type`=2 and `port`='".$vm[1]."' and `vmid`='".$vm[2]."' and `status`='1' ";
$dada=Database::getval( $sql_snap,__FILE__,__LINE__);

$course_code = $_GET['lessonId'];
$tbl_courseware = Database::get_course_table ( TABLE_COURSEWARE );
$sql = "SELECT * FROM $tbl_courseware WHERE cc='" . $course_code . "'" ;
$item = Database::fetch_one_row ( $sql, false, __FILE__, __LINE__ );
$cw_type=$item['cw_type'];
switch ($cw_type) {
        case 'link' :
                $link_url = Security::remove_XSS ( $item ['path'] );
                event_link ( $cw_id );
                break;
        case 'html' :
                $http_www = api_get_path ( WEB_COURSE_PATH ) . api_get_course_path () . '/html';
                $path=(substr($item['path'],-1)!='/'?$item['path'].'/':$item['path']);
                $link_url=$http_www.$path.$item['attribute'];
                break;
}
?>
<!DOCTYPE html>
<head>
    <title><?=$system?></title>
<script LANGUAGE="JavaScript"> 
function openwin1() {
    var url = location.search;
    url = "/lms/main/cloud/snapshot_form.php"+url;
    window.open (url, "newwindow", "height=200, width=400, top=100px,left=0,toolbar=no, menubar=no, scrollbars=no, resizable=no,alwaysRaised=yes,dependent=yes,location=no, status=no,directories=no");
} 
function openwin2() {
    var url = location.search;
    url = "/lms/main/cloud/rec_form.php"+url;
    window.open (url, "newwindow", "height=200, width=400, top=100px,left=0,toolbar=no, menubar=no, scrollbars=no, resizable=no,alwaysRaised=yes,dependent=yes,location=no, status=no,directories=no");
} 
function openwin3() {
    var url = location.search;
    url = "/lms/main/cloud/cloudvmrec.php<?=$str?>"+url;
    window.open (url, "newwindow", "height=200, width=400, top=100px,left=0,toolbar=no, menubar=no, scrollbars=no, resizable=no,alwaysRaised=yes,dependent=yes,location=no, status=no,directories=no");
} 
</script>
<style>
    a{   color:black;    font-size:12px;     text-decoration: none;  padding-left:30px;display:block;height:18px;line-height:18px;float:left}
    a:hover{   color:black;  font-size:14px;  text-decoration: none; }
    .content1{  background:url(clound.png) no-repeat 0px -16px; }
    .content2{ background:url(clound.png) no-repeat 0px -33px;}
    .content3{background:url(clound.png) no-repeat 0px -49px; }
    .content4{ background:url(clound.png) no-repeat 0px -69px; }
    .content5{background:url(clound.png) no-repeat 0px -88px;}
    .content6{  background:url(clound.png) no-repeat 0px -107px;}
    
    .content11{background:url(clound2.png) no-repeat 0px -17px;}
    .content21{  background:url(clound2.png) no-repeat 0px -34px;}
    .content31{ background:url(clound2.png) no-repeat 0px -51px;}
    .content41{background:url(clound2.png) no-repeat 0px -70px;}
   .content51{background:url(clound2.png) no-repeat 0px -90px;}
   .content61{ background:url(clound2.png) no-repeat 0px -109px;}
</style>



    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <link rel="apple-touch-startup-image" href="images/screen_320x460.png" />
    <link rel="apple-touch-icon" href="images/screen_57x57.png">
    <link rel="stylesheet" href="include/base.css" title="plain">
    <script src="include/util.js"></script>
    <style type="text/css">
        header{background-color: #fff;height: 25px;margin: 0px;font-family: 'Microsoft YaHei';}
        header a{color: #000;text-decoration: none;}
        header a:hover{color: #f00;text-decoration: underline;}
        header ul, header li{margin: 0px;padding: 0px;}
        header ul li{list-style: none;float: left;width: 70px;height: 20px;line-height: 20px;text-align: center;margin: 0px 5px;}
        #showMenu{position: absolute;left: 80px;background-color: #869dbc;height:10px;color: #5B6368;line-height:10px;display: none;}
        #bootStrap{width: 100%;height: 0;position: absolute;left: 0;top: 50px;z-index: 999;background-color: #e6e6e6;overflow: hidden;}
        .tabbable-custom{height:85%;}
        .tab-content,.tab-pane {height:100%;}
    </style>
    <script src="include/jquery-1.7.2.min.js"></script>
    <script type="text/javascript">
        

    </script>
</head>


<body style="margin: 0px;">
    <header id="header">
        <ul>
        	    <!--<li><a href="#" onclick="openwin1()"  class="content11">截屏</a></li>--> 
        	    <?php 
//                    if($dada!=null ){
//        	    	echo '<li><a  href="#" onclick="openwin3()"   class="content2"  title="停止录屏">停止</a></li>';
//        	    }else{
//        	    	echo '<li><a  href="#" onclick="openwin2()"  class="content21">录屏</a></li>';
//        	    } 
                    ?>

<!--            <li><a href="/lms/main/cloud/cloudvmstatus.php?status=suspend&<?=$str2?>"  class="content31" onclick="desktop.siderLin1k()">暂停</a></li>
            <li><a href="/lms/main/cloud/cloudvmstatus.php?status=resume&<?=$str2?>" class="content41" onclick="desktop.siderLin2k()">恢复</a></li>
            <li><a href="/lms/main/cloud/cloudvmstatus.php?status=stop&<?=$str2?>" class="content51" onclick="desktop.siderLin3k()">关闭</a></li>
            <li><a href="/lms/main/cloud/cloudvmstatus.php?status=reset&<?=$str2?>" class="content61" onclick="desktop.siderLin4k()">重启</a></li>-->
            <li><a href="javascript:void(0)" onclick="desktop.fullScreen()">全屏</a></li>
            <li><a href="javascript:void(0)" onclick="desktop.sliderMenu(true)">隐藏</a></li>

        </ul>
    </header>
    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
	<li><a id="showMenu" href="javascript:void(0)" onclick="desktop.sliderMenu(false)">显示菜单</a> </li>
	<li><div id="noVNC_buttons"> <input type="button" value="Ctrl+Alt+Del" class="noVNC_status_button" id="sendCtrlAltDelButton"> </div></li>
	<div id="noVNC_status_bar" class="noVNC_status_bar" style="margin-top: 0px;"> <div id="noVNC_status">Loading</div></div>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="tab_1_1">
                <div id="noVNC_screen">
                    <canvas id="noVNC_canvas" width="640px" height="20px">Canvas not supported.
                    </canvas>
                </div>
            </div>
            <div class="tab-pane" id="tab_1_2">
                 <iframe src="<?=$link_url?>" width="100%" height="100%"></iframe>
            </div>
        </div>
    </div>
    <div id="bootStrap">
        <a href="javascript:desktop.showGallery(false);">关闭</a>
        <div class="Style">
            <link id="bs-css" href="include/bootstrap-cerulean.css" rel="stylesheet">
        </div>
        <div class="Script">
            <script src="include/bootstrap-tab.js"></script>
            <script src="include/bootstrap-tooltip.js"></script>
            <script src="include/bootstrap-popover.js"></script>
        </div>
    </div>
    <script src="include/tab.js"></script>
    <script src="include/app.js"></script>
    <script>
        /*jslint white: false */
        /*global window, $, Util, RFB, */
        "use strict";

        // Load supporting scripts
        Util.load_scripts(["webutil.js", "base64.js", "websock.js", "des.js",
                           "input.js", "display.js", "jsunzip.js", "rfb.js"]);

        var rfb;

        function passwordRequired(rfb) {

            var msg;
            msg = '<form onsubmit="return setPassword();"';
            msg += '  style="margin-bottom: 0px">';
            msg += 'Password Required: ';
            msg += '<input type=password size=10 id="password_input" class="noVNC_status">';
            msg += '<\/form>';
            $D('noVNC_status_bar').setAttribute("class", "noVNC_status_warn");
            $D('noVNC_status').innerHTML = msg;
        }
        function setPassword() {
            rfb.sendPassword($D('password_input').value);
            return false;
        }
        function sendCtrlAltDel() {
            rfb.sendCtrlAltDel();
            return false;
        }
        function updateState(rfb, state, oldstate, msg) {
            var s, sb, cad, level;
            s = $D('noVNC_status');
            sb = $D('noVNC_status_bar');
            cad = $D('sendCtrlAltDelButton');
            switch (state) {
                case 'failed': level = "error"; break;
                case 'fatal': level = "error"; break;
                case 'normal': level = "normal"; break;
                case 'disconnected': level = "normal"; break;
                case 'loaded': level = "normal"; break;
                default: level = "warn"; break;
            }

            if (state === "normal") { cad.disabled = false; }
            else { cad.disabled = true; }

            if (typeof (msg) !== 'undefined') {
                sb.setAttribute("class", "noVNC_status_" + level);
                s.innerHTML = msg;
            }
        }

        window.onscriptsload = function () {
            var host, port, password, path, token;

            $D('sendCtrlAltDelButton').style.display = "inline";
            $D('sendCtrlAltDelButton').onclick = sendCtrlAltDel;

            WebUtil.init_logging(WebUtil.getQueryVar('logging', 'warn'));
            //document.title = unescape(WebUtil.getQueryVar('title', 'cloud'));
            // By default, use the host and port of server that served this file
            host = WebUtil.getQueryVar('host', window.location.hostname);
            port = WebUtil.getQueryVar('port', window.location.port);

            // if port == 80 (or 443) then it won't be present and should be
            // set manually
            if (!port) {
                if (window.location.protocol.substring(0, 4) == 'http') {
                    port = 80;
                }
                else if (window.location.protocol.substring(0, 5) == 'https') {
                    port = 443;
                }
            }

            // If a token variable is passed in, set the parameter in a cookie.
            // This is used by nova-novncproxy.
            token = WebUtil.getQueryVar('token', null);
            if (token) {
                WebUtil.createCookie('token', token, 1)
            }

            password = WebUtil.getQueryVar('password', '');
            path = WebUtil.getQueryVar('path', 'websockify');

            if ((!host) || (!port)) {
                updateState('failed',
                    "Must specify host and port in URL");
                return;
            }

            rfb = new RFB({
                'target': $D('noVNC_canvas'),
                'encrypt': WebUtil.getQueryVar('encrypt',
                         (window.location.protocol === "https:")),
                'repeaterID': WebUtil.getQueryVar('repeaterID', ''),
                'true_color': WebUtil.getQueryVar('true_color', true),
                'local_cursor': WebUtil.getQueryVar('cursor', true),
                'shared': WebUtil.getQueryVar('shared', true),
                'view_only': WebUtil.getQueryVar('view_only', false),
                'updateState': updateState,
                'onPasswordRequired': passwordRequired
            });
            rfb.connect(host, port, password, path);
        };

        //this one
        //auto.html?host=221.180.148.99&port=8795
        var Desktop = function () {

            var $$ = Desktop.prototype;

            $$.siderLin1k = function () {
                window.location = "/lms/main/cloud/cloudvmstatus.php?status=suspend&<?=$str2?>";
            }
            $$.siderLin2k = function () {
                window.location = "/lms/main/cloud/cloudvmstatus.php?status=resume&<?=$str2?>";
            }
            $$.siderLin3k = function () {
                window.location = "/lms/main/cloud/cloudvmstatus.php?status=stop&<?=$str2?>";
            }
            $$.siderLin4k = function () {
                window.location = "/lms/main/cloud/cloudvmstatus.php?status=reset&<?=$str2?>";
            }

            $$.fullScreen = function () {
                var el = document.documentElement;
                var rfs = el.requestFullScreen || el.webkitRequestFullScreen || el.mozRequestFullScreen || el.msRequestFullScreen;

                if (typeof rfs != "undefined" && rfs) {
                    rfs.call(el);
                } else if (typeof window.ActiveXObject != "undefined") {
                    // for Internet Explorer 
                    var wscript = new ActiveXObject("WScript.Shell");
                    if (wscript != null) {
                        wscript.SendKeys("{F11}");
                    }
                }
            }

            $$.sliderMenu = function (v) {
                if (v) {
                    $("#header").slideUp(); $("#showMenu").show();
                    //document.getElementById("header").style.display = "none"; document.getElementById("showMenu").style.display = "block";
                }
                else {
                    $("#header").slideDown(); $("#showMenu").hide();
                    //document.getElementById("header").style.display = "block"; document.getElementById("showMenu").style.display = "none";
                }
            }

            $$.showGallery = function (v) {
                if (v)
                    $("#bootStrap").animate({ height: document.body.offsetHeight - 50 + "px" }, 500);
                else
                    $("#bootStrap").animate({ height: '0' }, 500);
            }

        }
        var desktop = new Desktop();

        $(function () {
            App.handleTabs;
        });
    </script>

</body>
</html>