<?php
$cidReset = true;
include_once ("inc/app.inc.php");
echo '<link href="/../../themes/css/layout.css" rel="stylesheet" type="text/css" media="screen" />';
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( null, FALSE);

$id = trim(htmlspecialchars($_GET ['id']));$id=(int)$id;
$g_action=  getgpc("action");
$g_id=  getgpc("id");$g_id=(int)$g_id;
if($g_action=='info' && $g_id!==''){

    $sql='select * from `vslab`.`tools` where `id`='.$id;
    $result = api_sql_query($sql, __FILE__, __LINE__ );
    while ( $rst = Database::fetch_row ( $result) ) {
        $ste [0] = $rst;
    }
    foreach ( $ste[0] as $k1 => $v1){
        $report[]  = $v1;
    }
}
//echo '<pre>';var_dump($report);echo '</pre>';

if( $g_action=='view' && $g_id!=''){
$g_ids=  getgpc("ids");$g_ids=(int)$g_ids;
    $sql = "SELECT `id`,`title`, `file` FROM `vslab`.`tools` WHERE `id`=" . $g_ids;
    $document_info = Database::fetch_one_row ( $sql, FALSE, __FILE__, __LINE__ );

    $file =   URL_ROOT.'/www'.URL_APPEDND.'/storage/tools/'.$document_info['file'];
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
    <caption align="top">导调工具</caption>
    <tbody>
    <tr>
        <td align="right" class="formLabel">工具名称</td>
        <td align="left"  class="formTableTd"><?=$report[1] ?></td>
    </tr>
    <tr>
        <td align="right" class="formLabel">描述</td>
        <td align="left"  class="formTableTd"><?=$report[5] ?></td>
    </tr>
    <tr>
        <td align="right" class="formLabel" title='工具下载'>软件</td>
        <td align="left"  class="formTableTd"><a href='tools_info.php?action=view&ids=<?=$report[0] ?>'><?=$report[6] ?></a></td>
    </tr>
    <tr>
        <td align="right" class="formLabel">创建时间</td>
        <td align="left"  class="formTableTd"><?=$report[3] ?></td>
    </tr>
    <tr>
        <td align="right" class="formLabel">状态</td>
        <td align="left"  class="formTableTd">
            <?php
            if($report[4]==1){
                echo '开放';
            }else{
                echo '关闭';
            }
            ?>
        </td>
    </tr>
    </tbody>
</table>
