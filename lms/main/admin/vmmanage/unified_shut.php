<?php
$language_file = array ('courses','admin');
$cidReset=true;
include_once ("../../inc/global.inc.php");
api_block_anonymous_users ();
if (! api_is_admin ()) api_not_allowed ();
include_once(api_get_path(SYS_CODE_PATH).'course/course.inc.php');
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . "pagination.class.php");

$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header();
//$vmaddres = $_GET['vmaddres'];

if($_GET['action']=='shutdown' && $_GET['id']!==''){
    $ids=intval(getgpc('id','G'));
    $addres=Database::getval('select `addres` from `vmtotal` where `id`='.$ids,__FILE__,__LINE__);
    $sql1 = "select `id`,`vmid`,`user_id`,`addres` FROM  `vmtotal` where `addres`= '".$addres."'";
    $res = api_sql_query ( $sql1, __FILE__, __LINE__ );
    $arr= array ();
    while ($arr = Database::fetch_row ( $res)) {
        $arrs [] = $arr;
    }
//    echo '<pre>';var_dump($arrs);echo '</pre>';
    foreach ( $arrs as $k1 => $v1){
        $vmid = $v1[1];
        $vmaddres = $v1[3];
       // echo $vmid.'<br>';
        if($vmid && $addres){
            $output = exec("sudo -u root /usr/bin/ssh root@$addres /sbin/cloudvmstop.sh $vmid");
            //echo 'sudo -u root /usr/bin/ssh root@'.$addres.' /sbin/cloudvmstop.sh '.$vmid;
        }
    }
    $sqla = "delete  FROM  `vmtotal` where `addres` ='".$addres."'";
    api_sql_query ( $sqla, __FILE__, __LINE__ );
}

$offset = getgpc ( "offset", "G" );
/*
 *
 * 获取远程服务器信息
 */
$table = Database::get_main_table ( vmtotal);
$sql = "select distinct `addres`,`id` FROM  $table group by `addres`";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$vm= array ();
$j = 0;
while ( $vm = Database::fetch_row ( $res) ) {
    $j++;
    $vms [$j] = $vm[0];
}
function get_sqlwhere() {
    $sql_where = "";
    $g_keyword=  getgpc('keyword');
    if (is_not_blank ( $g_keyword )) {
        if($g_keyword=='输入搜索关键词'){
            $g_keyword='';
        }
        $keyword = Database::escape_string ( $_GET ['keyword'], TRUE );
        $sql_where .= " AND (id LIKE '%" . trim ( $keyword ) . "%' OR addres LIKE '%" . trim ( $keyword )."%')";
    }
    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}
if (isset ( $g_keyword ) && is_not_blank ( $g_keyword )) {
    $keyword = escape ( urldecode ( $_GET ['keyword'] ), TRUE );
    $condition [] = " id LIKE '%" . $keyword . "%'";
    $param .= "&keyword=" . urlencode ( $keyword );
}

$sql_where = get_sqlwhere ();
if ($sql_where) $sql1 .= " AND " . $sql_where;
$total_rows =  count($vms);

if ($sql_where) $sql .= " AND " . $sql_where;
$sql .= " ORDER BY id ";
$sql .= sql_limit ( $offset, NUMBER_PAGE );

$data_list = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
//echo '<pre>';var_dump($data_list);echo '</pre>';
$url = 'http://'.$_SERVER['SERVER_NAME'].URL_APPEDND.'/main/admin/vmmanage/unified_shut.php'. $param;
$pagination_config = Pagination::get_defult_config ( $total_rows, $url, NUMBER_PAGE );
$pagination = new Pagination ( $pagination_config );



?>
<aside id="sidebar" class="column cloud open">
    <div id="flexButton" class="closeButton close"></div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt;
        <a href="<?=URL_APPEDND;?>/main/admin/cloud_menu.php">云平台</a> &gt;虚拟化统一关机</h4>
    <article class="module width_full hidden ip">
        <h3 class="vmip"><img src="/lms/themes/default/images/base1.gif" align="absmiddle" >  节点地址</h3>
        <div class="vmContent">

            <?php
            foreach ( $vms as $k1 => $v1){
                echo "<dl><dt>$k1</dt><dd><a href=vmmanage_iframe.php?vmaddres=$vms[$k1]>$v1</a></dd></dl>";
            }?>
        </div>
    </article>
    <article class="module width_full hidden">
        <?php if (is_array ( $data_list ) && $data_list) {?>
            <table cellspacing="0" cellpadding="0" class="p-table">
                <tr>
                    <th>编号</th>
                    <th>节点地址</th>
                    <th>操作</th>
                </tr>
                <?php
                foreach ( $data_list as $item ) {
                    $item_id=$item ['id'];
                    ?>
                    <tr>
                        <td><?= $item ['id'] ?></td>
                        <td><?= $item ['addres'] ?></td>
                        <td>
                            <?php
                            echo  confirm_href ( 'stop_small.png', 'ConfirmYourChoice', '', 'unified_shut.php?action=shutdown&id='.$item ['id'] );
                            ?>
                        </td>

                    </tr>
                    <?php
                }
                ?>

            </table>
            <div class="page">
                <ul class="page-list">
                    <li class="page-num">总计<?=$total_rows?>条记录</li>
                    <?php
                    echo $pagination->create_links ();
                    ?>
                </ul>
            </div>


        <?php
    } else {
        ?>
          <table cellspacing="0" cellpadding="0" class="p-table">
            <tr>
                <th>编号</th>
                <th>节点地址</th>
                <th>操作</th>
            </tr>
            <tr><td colspan="3" class="error">没有相关数据</td></tr>
          </table>
        <?php
    }
        ?>
    </article>

</section>
</body>
</html>