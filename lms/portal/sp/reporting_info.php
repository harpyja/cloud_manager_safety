<?php
$cidReset = true;
include_once ("inc/app.inc.php");
echo '<link href="/../../themes/css/layout.css" rel="stylesheet" type="text/css" media="screen" />';
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( null, FALSE);
$get_action=  getgpc('action');
$get_id=  getgpc('id');
$get_ids=  getgpc('ids');
if(isset($get_action) && $get_action=='info' && $get_id!==''){
    $sql='select * from `vslab`.`reporting_info` where `id`='.trim(htmlspecialchars($_GET ['id']));
    $result = api_sql_query($sql, __FILE__, __LINE__ );
    while ( $rst = Database::fetch_row ( $result) ) {
        $ste [0] = $rst;
    }
    foreach ( $ste[0] as $k1 => $v1){
            $report[]  = $v1;
    }
}
if( $get_action=='view' && $get_ids!=''){

    $sql = "SELECT `id`,`report_name`,`user`, `screenshot_file`,`type` FROM `reporting_info` WHERE id=" . $get_ids;
    $document_info = Database::fetch_one_row ( $sql, FALSE, __FILE__, __LINE__ );
    if($document_info['type']==1){
        $t_file='flag';
    }if($document_info['type']==2){
        $t_file='counterwork';
    }
    $file = URL_ROOT.'/www/'.URL_APPEDND.'/storage/report/'.$t_file.'/'.$document_info['user'].'/'.$document_info['screenshot_file'];
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
if($report[11]==1){
    $type='夺旗';
}if($report[11]==2){
    $type='分组对抗';
}
?>

<table align="center" width="90%" cellpadding="4" cellspacing="0" id="systeminfo">
    <caption align="top">我的<?=$type?>报告</caption>
    <tbody>
        <tr>
            <td align="right" style="width:200px" class="formLabel" ><?=$type?>报告名称</td>
            <td align="left"  class="formTableTd"><?=$report[1] ?></td>
        </tr>
        
        <tr>
            <td align="right" class="formLabel">文件</td>
            <td align="left"  class="formTableTd"><a href='reporting_info.php?action=view&ids=<?=$report[0] ?>'><?=$report[4] ?></a></td>
        </tr>
        <tr>
            <td align="right" class="formLabel">描述</td>
            <td align="left"  class="formTableTd"><?=$report[10] ?></td>
        </tr>
        <tr>
            <td align="right" class="formLabel">KEY</td>
            <td align="left"  class="formTableTd"><?=$report[12] ?></td>
        </tr>
        <tr>
            <td align="right" class="formLabel">提交时间</td>
            <td align="left"  class="formTableTd"><?=$report[3] ?></td>
        </tr>
        <tr>
            <td align="right" class="formLabel">教师评语</td>
            <td align="left"  class="formTableTd"><?=$report[7] ?></td>
        </tr>
        <tr>
            <td align="right" class="formLabel">得分</td>
            <td align="left"  class="formTableTd"><?=$report[6] ?></td>
        </tr>
        <tr>
            <td align="right" class="formLabel">结果</td>
            <td align="left"  class="formTableTd">
                <?php
                if($report[9]==1){
                    if($report[8]==1){
                        echo '通过';
                    }else{
                        echo '未通过';
                    }
                }else{
                    echo '未批改，请耐心等待！';
                }
                ?>
            </td>
        </tr>
    </tbody>
</table>