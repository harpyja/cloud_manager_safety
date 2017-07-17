<?php
/**
==============================================================================
 * experimental anual upload
==============================================================================
 */
$language_file = 'admin';
$cidReset = true;

include_once ("../inc/global.inc.php");
//api_protect_admin_script ();
//if (! isRoot ()) api_not_allowed ();
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");
$redirect_url = 'main/exam/exam_list.php';
$pro_id=getgpc ( 'project_id' );
$pro_name=Database::getval ("select name from project where id=$pro_id", __FILE__, __LINE__ );
if (isset ( $_GET ['action'] ) && $_GET ['action']=='delete') {
                $id = intval(getgpc('id'));
    //delete exam_main
                $sql = "delete from assess where id=$id";
                api_sql_query ( $sql, __FILE__, __LINE__ );
                  $resoult= mysql_affected_rows();
//                $s='select count(id) from `exam_main` where `type`='.$id;
//                $count_exam=Database::getval($s,__FILE__,__LINE__);
                if($resoult>0){
    //delete exam_type
                    $sql = "delete from check_items where assess_id=$id";
                    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                }
                tb_close ( 'method_list.php?project_id='.$pro_id );
}

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

//文件大小转换格式 chang
        
function name_filter($document_name){
    $result = "";
    $result.='<a href="../../../storage/routerdoc/'.$document_name.'" style="color:#4171B5">'.$document_name.'</a>';
    return $result;
}
        
function delete_filter($id) {
    $result = "";
    global $_configuration, $root_user_id;
    if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result .= '&nbsp;' . confirm_href ( 'delete.gif', '您确定要执行此操作吗？', 'Delete', 'labs_experimental_anual.php?delete_id=' . $id );
    }
    return $result;
}
if (isset ($_GET['delete_id'])&& $_GET['delete_id']!=='') {
    $delete_id=intval(htmlspecialchars($_GET ['delete_id']));
    if ( isset($delete_id)){
        //delete file
        $filename_sql="SELECT `document_name` from `labs_document` where `id`=".$delete_id;
        $get_filename=Database::getval($filename_sql,__FILE__,__LINE__);

//        unlink(URL_ROOT."/www/lms/storage/routerdoc/".$get_filename);


        $sql = "DELETE FROM `vslab`.`labs_document` WHERE `labs_document`.`id` = ".$delete_id;
        $result = api_sql_query ( $sql, __FILE__, __LINE__ );

        if($result){
            unlink(URL_ROOT."/www/lms/storage/routerdoc/".$get_filename);
        }
        api_redirect ("labs_experimental_anual.php");
    }
}

if (isset ( $_POST ['action'] )) {
    switch ($_POST ['action']) {
        case 'deletes' :
            $documents = getgpc('documents');
            if (count ( $documents ) > 0) {
                foreach ( $documents as $index => $id ) {
                    //delete file 
                    $filename_sql="SELECT `document_name` from `labs_document` where `id`=".$id;
                    $get_filename=Database::getval($filename_sql,__FILE__,__LINE__);
                    //echo $get_filename.'&nbsp;';
                    unlink(URL_ROOT."/www/lms/storage/routerdoc/".$get_filename);
                    if(!file_exists($path_name)){
                        $sql = "DELETE FROM `vslab`.`labs_document` WHERE id=".$id;
                        $result = api_sql_query ( $sql, __FILE__, __LINE__ );
                    }
                    $log_msg = get_lang('删除所选') . "id=" . $id;
                    api_logging ( $log_msg, 'documents', 'documents' );
                }
            }
            break;
    }
}

function get_sqlwhere() {
    $sql = '';
	if (is_not_blank ( $_GET ['project_id'] )) {
		$sql .= "   pro_id='" . Database::escape_string ( intval(getgpc ( 'project_id', 'G' )) ) . "'";
	}
	if (is_not_blank ( $_GET ['question_type'] )) {
		$sql .= " AND type='" . Database::escape_string ( intval(getgpc ( 'question_type', 'G' )) ) . "'";
	}
	if (is_not_blank ( $_GET ['level'] )) {
		$sql .= " AND level=" . Database::escape ( getgpc ( 'level' ) );
	}
    if($_GET['keyword']="输入搜索关键字"){$keyword="";}
        else if (is_not_blank ( $_GET ['keyword'] )) {
            $keyword = Database::escape_str ( getgpc ( 'keyword', "G" ), TRUE );
            $sql .= " AND (question_code LIKE '%" . $keyword . "%' OR question LIKE '%" . $keyword . "%')";
            }
	return $sql;
}

function get_number_of_labs_document() {
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM `labs_document`";
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_pro_data($from, $number_of_items, $column, $direction) {
    $sql = "select * FROM `assess`";

    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;

    $sql .= " order by `id`";
    $sql .= " LIMIT $from,$number_of_items";
   $result = api_sql_query ( $sql, __FILE__, __LINE__ );
while ( $row = Database::fetch_array ( $result, 'ASSOC' ) ) {
    $arr[]=$row;
    
}

foreach ( $arr as $va ) {
    $row_render= array ();
   $row_render[]= $va['id'];
    $row_render[] = $va['class'];
    $row_render[] = $va['check'];
    $row_render[] = $pro_name;
    $check_num=Database::getval ("select count(*) from check_items where assess_id=".$va['id'], __FILE__, __LINE__ );
    $row_render[] = $check_num;

 
    $action = "";
    if (isRoot ()) {
        $action .= '&nbsp;&nbsp;' . link_button ( 'edit.gif', 'Edit', 'method_edit.php?action=edit&id=' . $va ['id'], '90%', '70%', FALSE );
        $href = 'method_list.php?action=delete&amp;id=' . $va ["id"].'&amp;project_id='.$pro_id;
        $action .= '&nbsp;&nbsp;' . confirm_href ( 'delete.gif', '确定删除该条记录吗？', 'Delete', $href );
    }
    $row_render[] = $action;
    $table_data [] = $row_render;
    
}
return $table_data;
}

$tool_name = get_lang ( 'CourseList' );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( $tool_name );

$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" ) . "/" . get_lang ( "OfficialCode" );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText', 'title' => $keyword_tip,'id'=>'searchkey','value'=>'输入搜索关键词' )  );

$sql1 = "SELECT `name` FROM `vslab`.`labs_labs`";
$result1 = api_sql_query ( $sql1, __FILE__, __LINE__ );
$device_show= array ();

while ( $device_show = Database::fetch_row ( $result1) ) {
    $device_shows [] = $device_show;
}
foreach ( $device_shows as $k1 => $v1){
    foreach($v1 as $k2 => $v2){
        $labs_device[$v2]  = $v2;
    }
}
$labs_device[""] = "---所有拓扑---";
ksort($labs_device);
foreach ( $category_cnt as $item ) {
    $cate_name = $item ['name'] . (($category_cnt [$item ['id']]) ? "&nbsp;(" . $category_cnt [$item ['id']] . ")" : "");
    $cate_options [$item ['id']] = str_repeat ( "&nbsp;&nbsp;&nbsp;&nbsp;", intval ( $item ['level'] ) + 1 ) . trim ( $cate_name );
}
//$form->addElement ( 'select', 'lab_name', get_lang ( '拓扑' ), $labs_device, array ('style' => 'min-width:150px;height:23px;border: 1px solid #999;' ) );

$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );


if (isset ( $_GET ['keyword'] ) && is_not_blank ( $_GET ['keyword'] )) $parameters ['keyword'] = getgpc ( 'keyword' );
if (isset ( $_GET ['lab_name'] ) && is_not_blank ( $_GET ['lab_name'] )) $parameters ['lab_name'] = getgpc ( 'lab_name' );

$table = new SortableTable ( 'labs', 'get_number_of_labs_document', 'get_pro_data',2, NUMBER_PAGE  );
$table->set_additional_parameters ( $parameters );
 $header_idx = 0;
 $table->set_header ( $header_idx ++, '', false );
 
$table->set_header ( $header_idx ++, get_lang ( '类别' ), false, null, array ('width' => '15%' ) );
$table->set_header ( $header_idx ++, "当前评估名称", false, null, array ('width' => '15%' ) );
$table->set_header ( $header_idx ++, get_lang ( '检查项数量' ), true, null, array ('width' => '15%' ) );

$table->set_header ( $header_idx ++, get_lang ( 'Actions' ), false, null, array ('width' => '15%' ) );
$table->set_form_actions ( array ('deletes' => '删除所选项' ), 'documents' );
        
//Display::display_footer ( TRUE );
?>

<aside id="sidebar" class="column router open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/router/labs_ios.php">路由管理</a> &gt; 路由实验手册</h4>
    <div class="managerSearch">
        <?php $form->display ();?>
        <span class="searchtxt right">
       
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
