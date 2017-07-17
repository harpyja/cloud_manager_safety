<?php
$cidReset = true;
include_once ("inc/app.inc.php");
echo '<link href="/../../themes/css/layout.css" rel="stylesheet" type="text/css" media="screen" />';
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( null, FALSE);

$platform=htmlspecialchars($_GET ['report_type']);
$ids = trim(htmlspecialchars($_GET ['id']));
$get_action=  getgpc('action');
$get_id=  getgpc('id');
$get_ids=  getgpc('ids');
if(isset($get_action) && $get_action=='info' && $get_id!==''){
    $sql='select * from `vslab`.`report` where `id`='.trim(htmlspecialchars($_GET ['id']));
    $result = api_sql_query($sql, __FILE__, __LINE__ );
    while ( $rst = Database::fetch_row ( $result) ) {
        $ste [0] = $rst;
    }
    foreach ( $ste[0] as $k1 => $v1){
            $report[]  = $v1;
    }
}
if( $get_action=='view' && $get_ids!=''){

    $sql = "SELECT `id`,`report_name`,`user`, `screenshot_file` FROM `report` WHERE id=" . $get_ids;
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
<table align="center" width="90%" cellpadding="4" cellspacing="0" id="systeminfo">
    <caption align="top">我的实验报告</caption>
    <tbody>
        <tr>
            <td align="right" class="formLabel">实验报告名称</td>
            <td align="left"  class="formTableTd"><?=$report[1] ?></td>
        </tr>
        <?php
           if($platform!=3){
               echo '<tr>
                        <td align="right" class="formLabel">课程名称</td>
                        <td align="left"  class="formTableTd">'.$report[3].'</td>
                    </tr>';
           }
        ?>
        <tr>
            <td align="right" class="formLabel">文件</td>
            <td align="left"  class="formTableTd">
                <?php
                $file=$report[3];
                if($file ==''){
                    echo ' 无 ';
                }else{?>
               
                   <a href='report_info.php?action=view&ids=<?=$ids?>'><?=$report[5]?></a>
              <?php    }
                ?>
                </td>
        </tr>
        <tr>
            <td align="right" class="formLabel">描述</td>
            <td align="left"  class="formTableTd"><?=$report[11] ?></td>
        </tr>
        <tr>
            <td align="right" class="formLabel">KEY</td>
            <td align="left"  class="formTableTd"><?=$report[12] ?></td>
        </tr>
        <tr>
            <td align="right" class="formLabel">提交时间</td>
            <td align="left"  class="formTableTd"><?=$report[4] ?></td>
        </tr>
        <tr>
            <td align="right" class="formLabel">教师评语</td>
            <td align="left"  class="formTableTd"><?=$report[8] ?></td>
        </tr>
        <tr>
            <td align="right" class="formLabel">得分</td>
            <td align="left"  class="formTableTd"><?=$report[7] ?></td>
        </tr>
        <tr>
            <td align="right" class="formLabel">结果</td>
            <td align="left"  class="formTableTd">
                <?php
                if($report[10]==1){
                    if($report[9]==1){
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