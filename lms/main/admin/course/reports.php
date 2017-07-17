<?php
/**
==============================================================================
 * 实验报告管理
==============================================================================
 */
$language_file = 'admin';
$cidReset = true;

include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();
$_SESSION['platfrom_type']= $platform;

//Interception of a fixed-length string  @changzf 2013/01/18
function g_substr($str, $len, $dot = true) {
    $i = 0;
    $l = 0;
    $c = 0;
    $a = array();
    while ($l < $len) {
        $t = substr($str, $i, 1);
        if (ord($t) >= 224) {
            $c = 3;
            $t = substr($str, $i, $c);
            $l += 2;
        } elseif (ord($t) >= 192) {
            $c = 2;
            $t = substr($str, $i, $c);
            $l += 2;
        } else {
            $c = 1;
            $l++;
        }
        $i += $c;
        if ($l > $len) break;
        $a[] = $t;
    }
    $re = implode('', $a);
    if (substr($str, $i, 1) !== false) {
        array_pop($a);
        ($c == 1) and array_pop($a);
        $re = implode('', $a);
        $dot and $re .= '...';
    }
    return $re;
}
function content_filter($id){
    $result = "";
    $comment=Database::getval('select `screenshot_file` from `report` where `id`='.  intval(urlencode($id),__FILE__,__LINE__));
    $result.="<a href='reports.php?action=view&id=".intval($id)."'><span style='text-align:left'>".$comment."</span></a>";
    return $result;
}
if($_GET['action']=='view' && $_GET['id']!==''){
    $sql = "SELECT `id`,`report_name`,`user`, `screenshot_file` FROM `report` WHERE id=" . intval(urlencode(htmlspecialchars($_GET['id'])));
    $info = Database::fetch_one_row ( $sql, FALSE, __FILE__, __LINE__ );
    $file = URL_ROOT.'/www/lms/storage/report/'.$info['user'].'/'.$info['screenshot_file'];
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
function comment_filter($screenshot_file){
    $result = "";
    $result.="<span style='text-align:left'>".g_substr($screenshot_file,40)."</span>";
    return $result;
}
function course_filter($code){
    $result=Database::getval("select `title` from course where `code`='".$code."'",__FILE__,__LINE__);
    return "<span style='text-align:left'>".$code."</span>";
}
function return_filter($id){
    $result = "";
    $marking_status=Database::getval("select `marking_status` from `vslab`.`report` where `id`=".intval($id),__FILE__,__LINE__);
    $return=Database::getval("select `return` from `vslab`.`report` where `id`=".intval($id),__FILE__,__LINE__);
    if($marking_status==1){
        if($return==1){
            $result.=' 通过';
        }else{
            $result.='未通过';
        }
    }else{
        $result.='未批改';
    }
    return $result;
}
function status_filter($status){
    $result = "";
    $result.=$status;
    return $result;
}
function action_filter($id){
    $result = "";
    $marking_status =Database::getval("select `marking_status` from `vslab`.`report` where `id`=".intval($id),__FILE__,__LINE__);
    if($marking_status==0){
        $result .=  link_button ( 'plugin.gif', '批改实验报告', 'reports_edit.php?id=' . intval($id), '100%', '70%', FALSE );
    }
    $result .=  confirm_href ( 'delete.gif', '你确定执行此操作？', 'Delete', 'reports.php?action=delete&delete_id=' . intval($id) );
    return $result;
}
$action=htmlspecialchars($_GET ['action']);
if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id=  intval(htmlspecialchars($_GET ['delete_id']));
            if ( isset($delete_id)){
                $sql = "DELETE FROM `vslab`.`report` WHERE id='" . $delete_id . "'";
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                if($result){
                   // echo '删除成功！';
                }

                $redirect_url = "reports.php";
                tb_close ( $redirect_url );
            }
            break;
    }
}
if (isset ( $_POST ['action'] )) {
    switch (getgpc("action","P")) {
        case 'deletes' :
            $number_of_selected_users = count ( getgpc('id') );
            $number_of_deleted_users = 0;
            foreach ( getgpc('id') as $index => $id ) {
                $sql = "DELETE FROM `vslab`.`report` WHERE id='" . intval($id) . "'";
                api_sql_query ( $sql, __FILE__, __LINE__ );

                $log_msg = get_lang('删除所选') . "id=" . intval($id);
                api_logging ( $log_msg, 'labs', 'labs' );
            }
            break;

    }
}
function get_sqlwhere() {
    $sql_where = "";
    if (is_not_blank ( $_GET ['keyword'] )) {
        if($_GET ['keyword']=='输入搜索关键词'){
            $_GET ['keyword']='';
        }
        $keyword = Database::escape_string (getgpc("keyword","G"), TRUE );
        $sql_where .= " AND (id LIKE '%" . intval(trim ( $keyword )) . "%' OR report_name LIKE '%" . trim ( $keyword ) . "%' OR user LIKE '%" . trim ( $keyword ) . "%'
                             OR score LIKE '%" . trim ( $keyword ) . "%' OR submit_date LIKE '%" . trim ( $keyword ) . "%'
                             OR screenshot_file LIKE '%" . trim ( $keyword ) . "%' OR code LIKE '%" . trim ( $keyword ) . "%' )";
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_report() {
    $report = Database::get_main_table ( report );
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $report." where `status`=1 ";
        if($_SESSION['platfrom_type']<1 OR $_SESSION['platfrom_type']>3){
        $sql .= " AND type=1 ";
    }
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND " . $sql_where;
   // echo $sql;//exit;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}

function get_report_data($from, $number_of_items, $column, $direction) {
    $report = Database::get_main_table ( report );
    $sql = "select `id`,`report_name`,`user`,`code`,`id`,`description`,`score`,`comment`,`id` ,`id` FROM  ".$report." where `status`=1 ";

    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND " . $sql_where;
    if($_SESSION['platfrom_type']<1 OR $_SESSION['platfrom_type']>3){
        $sql .= " AND type=1 ";
    }
    // $sql .= " ORDER BY col$column $direction ";
    $sql .= " LIMIT $from,$number_of_items";

    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $vm= array ();

    while ( $vm = Database::fetch_row ( $res) ) {
        $vms [] = $vm;
    }
    return $vms;
}

$tool_name = get_lang ( 'CourseList' );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( $tool_name );

$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText','value'=>'输入搜索关键词','id'=>'searchkey', 'title' => $keyword_tip ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );


if (isset ( $_GET ['keyword'] ) && is_not_blank ( $_GET ['keyword'] )) $parameters ['keyword'] = getgpc ( 'keyword' );
if (isset ( $_GET ['lab_name'] ) && is_not_blank ( $_GET ['lab_name'] )) $parameters ['lab_name'] = getgpc ( 'lab_name' );

$table = new SortableTable ( 'report', 'get_number_of_report', 'get_report_data',2, NUMBER_PAGE  );
$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '序号', false );
$table->set_header ( $idx ++, '实验报告名称', false  ,null, array ('style' => ' text-align:center;width:15%' ));
$table->set_header ( $idx ++, '用户名', false, null, array ('style' => ' text-align:center;width:5%' ));
$table->set_header ( $idx ++, '课程名称', false, null, array ('style' => ' text-align:center;width:20%' ) );
$table->set_header ( $idx ++, '浏览', false, null, array ('style' => ' text-align:center;width:10%' ) );
$table->set_header ( $idx ++, '描述', false, null, array ('style' => ' text-align:center;width:15%' ) );
$table->set_header ( $idx ++, '得分', false, null, array ('style' => ' text-align:center;width:5%' ) );
$table->set_header ( $idx ++, '教师评语', false, null, array ('style' => 'width:15%' ) );
$table->set_header ( $idx ++, '结果', false, null, array ('style' => ' text-align:center;' ) );
$table->set_header ( $idx ++, '操作', false, null, array ('style' => ' text-align:center;' ) );

$table->set_column_filter ( 3, 'course_filter' );
$table->set_column_filter ( 4, 'content_filter' );
$table->set_column_filter ( 7, 'comment_filter' );
$table->set_column_filter ( 8, 'return_filter' );
$table->set_column_filter ( 9, 'action_filter' );
$actions = array ('deletes' => '删除所选项');
$table->set_form_actions ( $actions );
?>

<aside id="sidebar" class="column reports open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt;
        <a href="<?=URL_APPEDND;?>/main/admin/course/reports.php">实验报告管理</a> &gt; 实验报告管理</h4>

    <div class="managerSearch">
        <?php $form->display ();?>
        <span class="searchtxt right">
        <?php
            $report_count=Database::getval('SELECT COUNT(id) FROM `report` where `marking_status`=1 and `type`=1',__FILE__,__LINE__);
            if($report_count > 0){
                echo '&nbsp;&nbsp;' . link_button ( 'return.gif', '导出实验报告', 'reports_export.php', '60%', '50%' );
            }
            ?>
        </span>
    </div>
    <article class="module width_full hidden">
        <form name="form1" method="post" action="">
            <table cellspacing="0" cellpadding="0" class="p-table">

                <?php $table->display ();?>
            </table>
        </form>
    </article>

</section>
