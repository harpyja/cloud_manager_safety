<?php
$cidReset = true;
include_once ("../../../portal/sp/inc/app.inc.php");
echo '<link href="/../../themes/css/layout.css" rel="stylesheet" type="text/css" media="screen" />';
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( null, FALSE);
if(isset($_GET['action']) && $_GET['action']=='info' && $_GET['id']!==''){
    $sql='select id,title,created_user ,date_start,content from `sys_announcement` where `id`='.  intval(trim(htmlspecialchars($_GET ['id'])));
    $result = api_sql_query($sql, __FILE__, __LINE__ );
    while ( $rst = Database::fetch_row ( $result) ) {
        $ste [0] = $rst;
    }
    $id=  intval($ste[0][2]);
    $ste[0][2]=Database::getval("select `firstname` from `user` where `user_id`=$id");
    foreach ( $ste[0] as $k1 => $v1){
        $report[]  = $v1;
    }
}

if( $_GET['action']=='view' && $_GET['ids']!=''){

    $sql = "SELECT `id`,`report_name`,`user`, `screenshot_file` FROM `report` WHERE id=" . intval(getgpc("ids","G"));
    $document_info = Database::fetch_one_row ( $sql, FALSE, __FILE__, __LINE__ );
    $file = URL_ROOT.'/www/lms/storage/report/'.$document_info['user'].'/'.$document_info['screenshot_file'];
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
        exit;
    }
}

?>
<table align="center" width="80%" cellpadding="4" cellspacing="0" id="systeminfo">
    <caption align="top">公告信息</caption>
    <tbody>
    <tr>
        <td align="right" class="formLabel">标题：</td>
        <td align="left"  class="formTableTd"><?=$report[1] ?></td>
    </tr>
    <tr>
        <td align="right" class="formLabel">创建用户：</td>
        <td align="left"  class="formTableTd"><?=$report[2] ?></td>
    </tr>
    <tr>
        <td align="right" class="formLabel">发布时间：</td>
        <td align="left"  class="formTableTd"><?=$report[3] ?></td>
    </tr>
    <tr>
        <td align="right" class="formLabel">内容：</td>
        <td align="left"  class="formTableTd"><?=$report[4] ?></td>
    </tr>
    </tbody>
</table>