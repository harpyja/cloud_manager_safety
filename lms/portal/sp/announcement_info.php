<?php
$cidReset = true;
include_once ("inc/app.inc.php");
echo '<link href="/../../themes/css/layout.css" rel="stylesheet" type="text/css" media="screen" />';
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( null, FALSE);
$id=getgpc('id','G');$id=(int)$id;
if($id!==''){
    $sql='select title,end_date,content from `crs_announcement` where `id`='.$id;
    $result = api_sql_query($sql, __FILE__, __LINE__ );
    while ( $rst = Database::fetch_row ( $result) ) {
        $ste [] = $rst;
    }
//    var_dump($ste);  
}
 
?>
<table align="center" width="80%" cellpadding="4" cellspacing="0" id="systeminfo">
    <caption align="top">课程公告</caption>
    <tbody>
        <tr>
            <td align="right" class="formLabel">标题：</td>
            <td align="left"  class="formTableTd"><?=$ste[0][0] ?></td>
        </tr> 
        <tr>
            <td align="right" class="formLabel">发布时间：</td>
            <td align="left"  class="formTableTd"><?=$ste[0][1] ?></td>
        </tr>
        <tr>
            <td align="right" class="formLabel">内容：</td>
            <td align="left"  class="formTableTd"><?=$ste[0][2] ?></td>
        </tr>
    </tbody>
</table>
