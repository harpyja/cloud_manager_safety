<?php



/**----------------------------------------------------------------
liyu: 2011-10-20
 *----------------------------------------------------------------*/
$cidReset = true;
include_once ("inc/app.inc.php");
include_once ("../../main/inc/global.inc.php");
include_once ('../../main/assignment/assignment.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileDisplay.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'document.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'tablesort.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . "export.lib.inc.php");
include_once (api_get_path ( SYS_CODE_PATH ) . 'document/document.inc.php');

$offset = getgpc ( "offset", "G" );

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
include_once ("inc/page_header.php");
$username=$_SESSION['_user']['username'];
$times=date('YmdHis',time());

$sql_where = "";
if (isset ( $_GET ['keyword'] )) {
    if($_GET ['keyword']=='输入搜索关键词'){
        $_GET ['keyword']='';
    }
    $keyword = trim ( Database::escape_str ( $_GET ['keyword'], TRUE ) );
    if (! empty ( $keyword )) {
        $sql_where .= " title LIKE '%" . $keyword . "%'";
    }
}

 
$sql1 = "SELECT COUNT(id) from `flag`  where  visible=1 ";
if ($sql_where) $sql1 .= " AND " . $sql_where;
$total_rows = Database::getval ( $sql1, __FILE__, __LINE__ );

//获取系统公告数据
$user_id=$_SESSION['_user']['user_id'];
 
$sql = "SELECT  id,title,created_user ,date_start,content,user FROM  flag where visible=1 ";

//    $sql_where = get_sqlwhere ();
if ($sql_where) $sql .= " and " . $sql_where;
$sql .= sql_limit ( $offset, NUMBER_PAGE );
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$ress = array ();
//$users=array("ab","cd","1");
// if(in_array($user_id,$users )){
//        echo "asdfas";
//    }
while ( $ress = Database::fetch_row ( $res ) ) {
 
     $users=  unserialize($ress[5]);
     $ress[2]=Database::getval("select username from user where user_id=$ress[2]");
    if(in_array($user_id,$users )){
        $announcement_data [] = $ress;
     }
    
}
 
$url = WEB_QH_PATH . "flag.php?" ."keyword=".getgpc("keyword");
$pagination_config = Pagination::get_defult_config ( $total_rows, $url, NUMBER_PAGE );
$pagination = new Pagination ( $pagination_config );

$interbreadcrumb [] = array ("url" => 'index.php', "name" => "首页" );
$interbreadcrumb [] = array ("url" => 'flag.php', "name" => '夺旗位置' );
display_tab ( TAB_LEARNING_CENTER );
?>
<aside id="sidebar" class="column open flag">

    <div id="flexButton" class="closeButton close">

    </div>
</aside><!-- end of sidebar -->

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<?=display_interbreadcrumb ( $interbreadcrumb, null )?></h4>
    <article class="module width_full hidden">
        <header>
            <h3><form>
                <input type="text" name="keyword" value="输入搜索关键词" id="searchkey" onfocus="this.select();" />
                <input type="submit" value=" 搜 索 " class="submit alt_btn"></form>
            </h3>
        </header><br>
        <table cellspacing="0" cellpadding="0" class="p-table" align="center" style="width: 95%;">
            <tr>
                <th>序号</th>
                <th>标题</th>
                <th>发布者</th>
                <th>发布时间</th>
                <th>描述</th>
                
            </tr>
            <?php if (is_array ( $announcement_data ) && $announcement_data) {?>
            <div class="module_content">
                <?php
                foreach ( $announcement_data as $item ) {
                    //$item_id=$item ['0'];
                    ?>
                    <tr>
                    <td width="10%"><?= $item ['0'] ?></td>
                    <td width="20%" align="left"><?= $item ['1'] ?></td>
                    <td width="10%"><?=$item ['2'] ?></td>
                    <td width="15%"><?= $item ['3'] ?></td>
                    <td width="40%">
                        <pre style="text-align:left;"><?= $item ['4'] ?></pre>
                    </td>
                   
                    <?php
                }
                ?>
                <tr><td colspan='10'>
                    <div class="page">
                        <ul class="page-list">
                            <li class="page-num">总计<?=$total_rows?>条记录</li>
                            <?php
                            echo $pagination->create_links ();
                            ?>
                        </ul>
                    </div>
                </td></tr>
            </div>

            <?php
        } else {
            ?>
            <tr><td colspan='10' align="center">没有相关记录</td></tr>
            <?php
        }
            ?>
        </table>
        <br>
    </article>



</section>
</body>
</html>