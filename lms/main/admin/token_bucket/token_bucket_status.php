<?php
/**
==============================================================================
 * 令牌桶状态
==============================================================================
 */
$language_file = 'admin';
$cidReset = true;
include_once ("../../inc/global.inc.php");
include_once("../../inc/lib/pagination.class.php");
//include_once("../../inc/lib/sortabletable.class.php");
//api_protect_admin_script ();


$status_id=intval(getgpc('id'));
function token_bucketName(){
    $status_id=intval(getgpc('id'));
    if($status_id==''){
        $status_id=Database::getval("select id from token_bucket order by id limit 0,1",__FILE__,__LINE__);
    }
    if(isset($status_id) && $status_id!==''){

        $sql = "select token_bucket_name from token_bucket where id=".$status_id;
        $token_bucket_name = Database::getval( $sql, __FILE__, __LINE__ );
    }
    return $token_bucket_name;

}
//左侧栏令牌桶 查询
$sql0 = "select token_bucket_name FROM  token_bucket ";
$res = api_sql_query ( $sql0, __FILE__, __LINE__ );

$vm= array ();
$j = 0;

while ( $vm = Database::fetch_row ( $res) ) {
    $j++;
    $vms0 [$j] = $vm[0];
}

function get_number_of_labs_ios() {
    $token_bucket_name = token_bucketName();
    $sql1 = "SELECT COUNT(Pid) FROM ".$token_bucket_name ." where status=1";
    return Database::getval ( $sql1, __FILE__, __LINE__ );
}
function get_labs_ios_data($from, $number_of_items, $column, $direction) {
    $token_bucket_name = token_bucketName();
    $sql = "select `Pid`,`status` ,`values` FROM ".$token_bucket_name." where status=1";
    $sql .= " order by `Pid`";
    $sql .= " LIMIT $from,$number_of_items";
    //echo $sql;
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $vm= array ();

    while ( $vm = Database::fetch_row ( $res) ) {
        if($vm[1]==1){
            $vm[1]="已占用";
        }
        $vms [] = $vm;
    }
    return $vms;
}

$tool_name = get_lang ( 'CourseList' );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( $tool_name );

$html = '<div id="demo" class="yui-navset">';
$html .= '<div class="yui-content"><div id="tab1">';
//echo $html;

$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
//$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" ) . "/" . get_lang ( "OfficialCode" );
//$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText','id'=>'searchkey','value'=>'输入搜索关键词' ) );
//$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );

$table = new SortableTable ( 'labs', 'get_number_of_labs_ios', 'get_labs_ios_data',2, NUMBER_PAGE  );
$parameters = array('id'=>$status_id);
$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '端口', false );
$table->set_header ( $idx ++, '状态', false, null, array ('style' => 'width:35%;text-align:center' ) );
$table->set_header ( $idx ++, '值', false, null, array ('style' => 'width:35%;text-align:center' ) );
?>

<aside class="column open" id="sidebar">
    <div class="closeButton close" id="flexButton">

    </div>
    <div class="navs">
        <dl class="nav-list">
            <dt>令牌桶状态</dt>
            <dd class="two-nav-list">
                <ul>
                    <?php foreach ( $vms0 as $k1 => $v1){
                    $sqli="SELECT id FROM  token_bucket where token_bucket_name='".$vms0[$k1]."'";
                    $ids= DATABASE::getval($sqli,__FILE__,__LINE__);
                    ?>
                    <li><a href="token_bucket_status.php?id=<?=$ids?>" status="<?=$vms0[$k1]?>"><?=$vms0[$k1]?></a></li>
                    <?php   }?>
                </ul></dd>
        </dl>
    </div>
</aside>
<section id="main" class="column">
    <h4 class="page-mark">当前位置：
        <a href="/lms/main/admin/index.php">平台首页</a>
        &gt; <a href="/lms/main/admin/cloud_menu.php">云平台</a>
        &gt; <a href="<?=URL_APPEDND;?>/main/admin/token_bucket/token_bucket_list.php">令牌桶管理</a>
        &gt;<?php
        $token_bucket_name = token_bucketName();
        echo '&nbsp;'.$token_bucket_name;?>令牌桶状态
    </h4>
    <article class="module width_full hidden">
        <form name="form1" method="post" action="">
            <table cellspacing="0" cellpadding="0" class="p-table">

                <?php $table->display ();?>
            </table>
        </form>
    </article>

</section>
</body>
</html>
