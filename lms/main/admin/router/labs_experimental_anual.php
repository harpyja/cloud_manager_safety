<?php
/**
==============================================================================
 * experimental anual upload
==============================================================================
 */
$language_file = 'admin';
$cidReset = true;

include_once ("../../inc/global.inc.php");
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();

if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'labs_document'"))!=1){
    $sql_insert ="CREATE TABLE IF NOT EXISTS `labs_document` (
                  `id` int(12) NOT NULL AUTO_INCREMENT,
                  `document_name` varchar(128) NOT NULL,
                  `document_size` varchar(128) NOT NULL,
                  `labs_id` varchar(32) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
    api_sql_query ( $sql_insert,__FILE__, __LINE__ );
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
function sizecount($filesize) {
    if($filesize >= 1073741824) {
        $filesize = round($filesize / 1073741824 * 100) / 100 . ' gb';
    } elseif($filesize >= 1048576) {
        $filesize = round($filesize / 1048576 * 100) / 100 . ' mb';
    } elseif($filesize >= 1024) {
        $filesize = round($filesize / 1024 * 100) / 100 . ' kb';
    } else {
        $filesize = $filesize . ' bytes';
    }
    return $filesize;
}

function name_filter($document_name){
    $result = "";
    $result.='<a href="../../../storage/routerdoc/'.$document_name.'" style="color:#4171B5">'.$document_name.'</a>';
    return $result;
}

function size_filter($document_size){
    $result = "";
    $result.=sizecount($document_size);
    return $result;
}

function labs_filter($labs_id){   //获取“拓扑”名
    $result = "";
    $sql='select `name` from `labs_labs` where `id`='.$labs_id;
    $result.= Database::getval($sql,__FILE__,__LINE__);  //连等于
    return $result;
}
function type_filter($type){  //获取“类型”，（0-->实验指导书 , 1-->初始化配置 ）
    $result="";
    if($type==0){
        $result="实验指导书";
        return $result;
    }else{
        $result="初始化配置";
        return $result;
    }
}
//编辑
function edit_filter($id){
    $result = "";
    global $_configuration, $root_user_id;
    if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result = link_button ( 'edit.gif', 'Edit', 'document_edit.php?id='.$id, '90%', '70%', FALSE );
    }
    return $result;
}

function delete_filter($id) {  //删除
    $result = "";
    global $_configuration, $root_user_id;
    if (api_is_platform_admin () && ! in_array ( $id, $root_user_id )) {
        $result .= '&nbsp;' . confirm_href ( 'delete.gif', '您确定要执行此操作吗？', 'Delete', 'labs_experimental_anual.php?delete_id=' . $id );
    }
    return $result;
}
if (isset ($_GET['delete_id'])&& $_GET['delete_id']!=='') {
    $delete_id=intval(getgpc('delete_id','G'));
    if ( isset($delete_id)){
        //delete file
        $filename_sql="SELECT `document_name` from `labs_document` where `id`=".$delete_id;
        $get_filename=Database::getval($filename_sql,__FILE__,__LINE__);    //获取一条记录

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
            $documents = $_POST['documents'];
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
    $sql_where = "";
    if (is_not_blank ( $_GET ['keyword'] )) {
        $keyword = Database::escape_string (getgpc("keyword","G"), TRUE );
        $sql_where .= " AND (id LIKE '%" . trim ( $keyword ) . "%' OR document_name LIKE '%" . trim ( $keyword ) . "%' OR document_size LIKE '%" . trim ( $keyword ) . "%')";
    }
    if (is_not_blank ( $_GET ['id'] )) {
        $sql_where .= " AND id=" . Database::escape ( intval(getgpc ( 'id', 'G' )));
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}

function get_number_of_labs_document() {   //获取记录条数
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM `labs_document`";
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_labs_document_data($from, $number_of_items, $column, $direction) {   //获取记录数据
    $sql = "select `id`,`id`,`document_name`,`type`,`document_size`,`labs_id`,`id`,`id` FROM `labs_document`";

    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;

    $sql .= " order by `id`";
    $sql .= " LIMIT $from,$number_of_items";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $vm= array ();
  //echo $sql;
    while ( $vm = Database::fetch_row ( $res) ) {
        $vms [] = $vm;
    }
//  print_r($vms);
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

$table = new SortableTable ( 'labs', 'get_number_of_labs_document', 'get_labs_document_data',2, NUMBER_PAGE  );
$table->set_additional_parameters ( $parameters );
$idx=0;
$table->set_header ( $idx ++, '', false,null);
$table->set_header ( $idx ++, '编号', false,null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, '名称', false, null, array ('style' => 'width:25%;text-align:center' ) );
$table->set_header ( $idx ++, '类型', false, null, array ('style' => 'width:20%;text-align:center' ));
$table->set_header ( $idx ++, '大小', false, null, array ('style' => 'width:20%;text-align:center' ) );
$table->set_header ( $idx ++, '实训拓扑', false, null, array ('style' => 'width:20%;text-align:center' ) );
$table->set_header ( $idx ++, '编辑', false, null, array ('style' => 'width:20%;text-align:center' ) );
$table->set_header ( $idx ++, '删除', false, null, array ('style' => 'width:20%;text-align:center' ) );
$table->set_form_actions ( array ('deletes' => '删除所选项' ), 'documents' );
$table->set_column_filter ( 2, 'name_filter' );
$table->set_column_filter ( 3, 'type_filter' );
$table->set_column_filter ( 4, 'size_filter' );
$table->set_column_filter ( 5, 'labs_filter' );  //第5列（拓扑名）是根据labs_document表中读出的拓扑id值通过labs_filter方法来从labs_labs表中获取的。
$table->set_column_filter ( 6, 'edit_filter' );
$table->set_column_filter ( 7, 'delete_filter' );
//Display::display_footer ( TRUE );
?>

<aside id="sidebar" class="column router open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/router/labs_ios.php">路由管理</a> &gt; 路由交换课程资料</h4>
    <div class="managerSearch">
        <?php $form->display ();?>
        <span class="searchtxt right">
        <?php
            echo link_button ( 'submit_file.gif', '上传资料', 'document_upload.php?path=' . $curdirpathurl, '50%', '60%' ).'&nbsp;&nbsp;&nbsp;' ;
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
