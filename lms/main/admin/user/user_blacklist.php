<?php

$language_file = 'admin';
$cidReset = true;

include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");

$tool_name = get_lang ( 'CourseList' );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( $tool_name );

function active_filter($user_id) {
    global $_user, $_configuration;
    if (isRoot ($user_id)) return ''; 
    if (api_is_platform_admin () && ! in_array ( $user_id, $root_user_id )) {
		$result .= '&nbsp;' . confirm_href ( 'wrong.gif', 'ConfirmYourChoice', '加入白名单', "user_blacklist.php?action=unlock&amp;user_id=$user_id" );
	}
 //    $result = '<a href="user_blacklist.php?action=unlock&amp;user_id=' .$user_id . '">
  //  <img src="../../../themes/img/wrong.gif" alt="Unlock" title="加入白名单" style="vertical-align: middle;"></a>';
    return $result;
}

if (isset ( $_GET ['action'] )) {
    $get_user_id=  intval(getgpc('user_id'));
    switch ($_GET ['action']) {
        case 'unlock' :
            $sql= 'UPDATE  `vslab`.`sys_user_dept` SET  `active` =  "1" WHERE  `sys_user_dept`.`user_id` ="'.$get_user_id.'"';
            $result = api_sql_query ( $sql, __FILE__, __LINE__ );
            header("Location: ".$redirect_url);
           break;
    }
}

function get_sqlwhere() {
   // global $restrict_org_id, $objCrsMng;
   // $sql_where = "";
    if (is_not_blank ( $_GET ['keyword'] )) {

        $keyword = Database::escape_string ( $_GET ['keyword'], TRUE );
        echo $keyword;
        $sql_where .= " AND (username LIKE '%" . trim ( $keyword ) . "%'
        or email  LIKE '%" . trim ( $keyword ) . "%'
        or official_code  LIKE '%" . trim ( $keyword ) . "%')";
    }
//lastname , dept_name
    if (is_not_blank ( $_GET ['id'] )) {
        $sql_where .= " Add id=" . Database::escape (intval( getgpc ( 'id', 'G' )) );
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 1 );
    else return "";
}

function get_number_of_blacklist() {
    $sql = "SELECT COUNT(user_id) AS total_number_of_items FROM `vslab`.`sys_user_dept` where active=0  AND `user_id`!=1 ";
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND " . $sql_where;
    //echo $sql;exit;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}


function get_blacklist_data($from, $number_of_items, $column, $direction) {
    $sql = "SELECT   username , firstname , email , official_code , lastname  , dept_name , user_id FROM `vslab`.`sys_user_dept` where active=0  AND `user_id`!=1 ";

    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND " . $sql_where;

    $sql .= " LIMIT $from,$number_of_items";
//echo $sql;
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $vm= array ();

    while ( $vm = Database::fetch_row ( $res) ) {
        $vms [] = $vm;
    }
    return $vms;
}
$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText','id'=>'searchkey','value'=>'输入搜索关键词' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );


$table = new SortableTable ( 'blacklist', 'get_number_of_blacklist', 'get_blacklist_data',2, NUMBER_PAGE  );
$idx=0;
$table->set_header ( $idx ++, '登录名', false );
$table->set_header ( $idx ++, '姓名', false, null, array ('style' => ' text-align:center' ));
$table->set_header ( $idx ++, '电子邮件', false, null, array ('style' => ' text-align:center' ));
$table->set_header ( $idx ++, '用户编号', false, null, array ('style' => ' text-align:center' ));
$table->set_header ( $idx ++, '职务/职位', false, null, array ('style' => ' text-align:center' ));
$table->set_header ( $idx ++, '所属部门', false, null, array ('style' => ' text-align:center' ));
$table->set_header ( $idx ++, '加入白名单', false, null, array ('style' => ' text-align:center' ));
$table->set_column_filter ( 6, 'active_filter' );
?>

<aside id="sidebar" class="column users open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>
<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/user/user_list_iframe.php">用户管理</a> &gt; 用户黑名单</h4>

<!--    <div class="managerSearch">-->
<!--        <form action="#" method="post" id="searchform">-->
<!--            --><?php // $form->display ();  ?>
<!--        </form>-->
<!--    </div>-->
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