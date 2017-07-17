<?php
/**
 * Created by JetBrains PhpStorm.
 * User: chang_z_f
 * Date: 13-7-16
 * Time: 上午11:26
 * To change this template use File | Settings | File Templates.
 * 分组对抗报告管理
 */
$language_file = 'admin';
$cidReset = true;


include_once ("../inc/global.inc.php");
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();


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
    $comment=Database::getval('select `screenshot_file` from `reporting_info` where `id`='.urlencode($id),__FILE__,__LINE__);
    $result.="<a href='counterwork_report.php?action=view&id=".$id."'><span style='text-align:left'>".$comment."</span></a>";
    return $result;
}

$g_action=  getgpc('action');
$g_id= intval (  getgpc('id'));
if($g_action=='view' && $g_id!==''){
    $sql = "SELECT `id`,`report_name`,`user`, `screenshot_file` FROM `reporting_info` WHERE id=" . urlencode(htmlspecialchars($_GET['id']));
    $info = Database::fetch_one_row ( $sql, FALSE, __FILE__, __LINE__ );

    $file = URL_ROOT.'/www/'.URL_APPEDND.'/storage/report/counterwork/'.$info['user'].'/'.$info['screenshot_file'];
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
    $marking_status=Database::getval("select `marking_status` from `vslab`.`reporting_info` where `id`=".$id,__FILE__,__LINE__);
    $return=Database::getval("select `return` from `vslab`.`reporting_info` where `id`=".$id,__FILE__,__LINE__);
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
    $marking_status =Database::getval("select `marking_status` from `vslab`.`reporting_info` where `id`=".$id,__FILE__,__LINE__);
    if($marking_status!=1){
        $result .=  link_button ( 'plugin.gif', '分组对抗报告批改', 'reporting_edit.php?id=' . $id, '100%', '70%', FALSE );
    }
    $result .=  confirm_href ( 'delete.gif', '你确定执行此操作？', 'Delete', 'counterwork_report.php?action=delete&delete_id=' . $id );
    return $result;
}
$action=htmlspecialchars($_GET ['action']);
if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id=htmlspecialchars($_GET ['delete_id']);
            if ( isset($delete_id)){
                $file=Database::getval('select `screenshot_file` from `reporting_info` where `id`='.$delete_id,__FILE__,__LINE__);

                $sql = "DELETE FROM `vslab`.`reporting_info` WHERE id='" . $delete_id . "'";
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                if($result){
                    $get_files=URL_ROOT.'/www/'.URL_APPEDND.'/storage/report/counterwork/'.$_SESSION['_user']['username'].'/'.$file;
                    unlink($get_files);
                }

                $redirect_url = "counterwork_report.php";
                tb_close ( $redirect_url );
            }
            break;
    }
}
$g_action=  getgpc('action');
if (isset ( $g_action )) {
    switch ($g_action) {
        case 'deletes' :
            $number_of_selected_users = count ( $_POST ['id'] );
            $number_of_deleted_users = 0;
            foreach ( $_POST ['id'] as $index => $id ) {
                $file=Database::getval('select `screenshot_file` from `reporting_info` where `id`='.$id,__FILE__,__LINE__);

                $sql = "DELETE FROM `vslab`.`reporting_info` WHERE id='" . $id . "'";
                $result= api_sql_query ( $sql, __FILE__, __LINE__ );

                if($result){
                    $get_files=URL_ROOT.'/www/'.URL_APPEDND.'/storage/report/counterwork/'.$_SESSION['_user']['username'].'/'.$file;
                    unlink($get_files);
                }
                $log_msg = get_lang('删除所选') . "id=" . $id;
                api_logging ( $log_msg, 'labs', 'labs' );
            }
            break;

    }
}
function get_sqlwhere() {
    $sql_where = "";
    $g_keyword=  getgpc('keyword');
    if (is_not_blank ( $g_keyword )) {
        if($g_keyword=='输入搜索关键词'){
            $g_keyword='';
        }
        $keyword = Database::escape_string ( $_GET ['keyword'], TRUE );
        $sql_where .= " AND (id LIKE '%" . trim ( $keyword ) . "%' OR report_name LIKE '%" . trim ( $keyword ) . "%' OR user LIKE '%" . trim ( $keyword ) . "%'
                             OR score LIKE '%" . trim ( $keyword ) . "%' OR comment LIKE '%" . trim ( $keyword ) . "%'
                             OR screenshot_file LIKE '%" . trim ( $keyword ) . "%'  )";
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_report() {
    $report = Database::get_main_table ( reporting_info );
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM " . $report." where `status`=1 and type=2 ";
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND " . $sql_where;
    // echo $sql;//exit;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}

function get_report_data($from, $number_of_items, $column, $direction) {
    $report = Database::get_main_table ( reporting_info );
    $sql = "select `id`,`report_name`,`user`,`id`,`key`,`score`,`comment`,`id` ,`id` FROM  ".$report." where `status`=1 and type=2 ";

    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " AND " . $sql_where;
    // $sql .= " ORDER BY col$column $direction ";
    $sql .= " LIMIT $from,$number_of_items";
//echo $sql;
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
include ('../inc/header.inc.php');

$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText','value'=>'输入搜索关键词','id'=>'searchkey', 'title' => $keyword_tip ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );

$g_keyword=  getgpc('keyword');
$g_lab_name=  getgpc('lab_name');        
if (isset ( $g_keyword ) && is_not_blank ( $g_keyword )) $parameters ['keyword'] = getgpc ( 'keyword' );
if (isset ( $g_lab_name ) && is_not_blank ( $g_lab_name )) $parameters ['lab_name'] = getgpc ( 'lab_name' );

$table = new SortableTable ( 'report', 'get_number_of_report', 'get_report_data',2, NUMBER_PAGE  );
$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '序号', false );
$table->set_header ( $idx ++, '分组对抗报告名称', false  ,null, array ('style' => ' text-align:center;width:15%' ));
$table->set_header ( $idx ++, '用户名', false, null, array ('style' => ' text-align:center;width:15%' ));
//$table->set_header ( $idx ++, '课程名称', false, null, array ('style' => ' text-align:center;width:20%' ) );
$table->set_header ( $idx ++, '浏览', false, null, array ('style' => ' text-align:center;width:15%' ) );
$table->set_header ( $idx ++, 'KEY', false, null, array ('style' => ' text-align:center;width:10%' ) );
$table->set_header ( $idx ++, '得分', false, null, array ('style' => ' text-align:center;width:10%' ) );
$table->set_header ( $idx ++, '教师评语', false, null, array ('style' => 'width:20%' ) );
$table->set_header ( $idx ++, '结果', false, null, array ('style' => ' text-align:center;width:5%' ) );
$table->set_header ( $idx ++, '操作', false, null, array ('style' => ' text-align:center;width:7%' ) );

//$table->set_column_filter ( 3, 'course_filter' );
$table->set_column_filter ( 3, 'content_filter' );
$table->set_column_filter ( 6, 'comment_filter' );
$table->set_column_filter ( 7, 'return_filter' );
$table->set_column_filter ( 8, 'action_filter' );
$actions = array ('deletes' => '删除所选项');
$table->set_form_actions ( $actions );
if($platform==3){
    $nav='reporting';
}else{
    $nav='exercice';
}
?>
<aside id="sidebar" class="column control open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt;
        <a href="<?=URL_APPEDND;?>/main/reporting/report.php">报告管理</a>
        &gt;分组对抗报告</h4>

    <div class="managerSearch">
        <?php $form->display ();?>
        <span class="searchtxt right">
        <?php
            $report_count=Database::getval('SELECT COUNT(id) FROM `reporting_info` where `type`=1 and `marking_status`=1',__FILE__,__LINE__);
            if($report_count > 0){
                echo '&nbsp;&nbsp;' . link_button ( 'return.gif', '导出报告', 'fc_report_export.php?type=2', '25%', '25%' );
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