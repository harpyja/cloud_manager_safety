<?php
$cidReset = true;
$language_file = array ('courses', 'admin' );
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();

$htmlHeadXtra [] =import_assets('commons.js');

$htmlHeadXtra [] = '<script type="text/javascript">
if(document.attachEvent)  window.attachEvent("onload",  iframeAutoFit);
else  window.addEventListener("load",  iframeAutoFit,  false);
</script>';
Display::display_header ();
if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'labs_category'"))!=1){
    $sql_insert ="CREATE TABLE IF NOT EXISTS `labs_category` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `parent_id` int(11) NOT NULL,
              `description` text NOT NULL,
              `name` varchar(128) NOT NULL,
              `tree_pos` int(11) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
    api_sql_query ( $sql_insert,__FILE__, __LINE__ );
}

?>
<?php
$language_file = 'admin';
$cidReset = true;

include_once ("../../inc/global.inc.php");
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

function description_filter($description){
    $result = "";
    $result.=g_substr($description,150);
    return $result;
}
function labs_count_filter($id){
    $sql="select count(*) from `labs_labs` where `labs_category`=".$id;
    $result=DATABASE::getval($sql,__FILE__,__LINE__);
    return $result;
}
function action_filter($id){
    $action_html='';
    $sql_i="select count(*) from `labs_category` where `parent_id`=".intval($id);
    $cound_pid=DATABASE::getval($sql_i,__FILE__,__LINE__);
    if ($cound_pid!=='0') {
        $action_html .= "&nbsp;&nbsp;".icon_href('folder_document.gif',   "OpenNode" ,$_SERVER ['PHP_SELF'] . "?category=" . $id);
    }
    else {
        $action_html .= "";
    }
    if(isset($_GET['category'])){
        $action_html .= "&nbsp;&nbsp;&nbsp;" . link_button ( 'edit.gif', 'EditNode', 'labs_category_edit.php?action=edit&cate='.  getgpc("category","G").'&id=' . intval($id), '90%', '95%', FALSE );
    }else{
        $action_html .= "&nbsp;&nbsp;&nbsp;" . link_button ( 'edit.gif', 'EditNode', 'labs_category_edit.php?action=edit&id=' . intval($id), '90%', '95%', FALSE );

    }
    if($cound_pid=='0'){
        $action_html .= "&nbsp;&nbsp;&nbsp;" . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'DeleteNode', $_SERVER ['PHP_SELF'] . "?action=delete&cid=" . intval($id) );

    }
    return $action_html;
}
$action=htmlspecialchars($_GET ['action']);

if (isset ($action)) {
    switch ($action) {
        case 'delete' :
            $delete_id=  intval(htmlspecialchars($_GET ['cid']));
            if ( isset($delete_id)){
                $sql = "DELETE FROM `vslab`.`labs_category` WHERE `labs_category`.`id` = ".$delete_id;
                $result = api_sql_query ( $sql, __FILE__, __LINE__ );
//                tb_close ();
            }
            break;
    }
}
function get_sqlwhere() {
    $sql_where = "";
    if (is_not_blank ( $_GET ['keyword'] )) {
        $keyword = Database::escape_string (getgpc("keyword","G"), TRUE );
        $sql_where .= " AND (id LIKE '%" . intval(trim ( $keyword )) . "%' OR name LIKE '%" . trim ( $keyword ) . "%')";
    }
    if(isset($_GET['category']) && $_GET['category']!==''){
        $sql_where.='parent_id = '. intval(getgpc("category","G"));
    }else{
        if(isset($_GET['name']) && $_GET['name']!==''){
            $category_name=trim(getgpc("name","G"));
            $c_sql="select `id` from `labs_category` where `name`='".$category_name."'";
            $cate_id=DATABASE::getval($c_sql,__FILE__,__LINE__);
            $sql_where.='parent_id =  '.$cate_id;
        }else{
         $sql_where.='parent_id =  0';}
    }
    $sql_where = trim ( $sql_where );
    return $sql_where;
}

function get_number_of_labs_devices() {
    $sql = "SELECT COUNT(*) AS total_number_of_items FROM `labs_category`";
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " where ".$sql_where;
    return Database::getval ( $sql, __FILE__, __LINE__ );
}
function get_labs_devices_data($from, $number_of_items, $column, $direction) {
    $sql="select `id`,`name`,`description`,`tree_pos`,`id`,`id` from `labs_category`";
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE ".$sql_where;

    $sql .= " order by `tree_pos`";
    $sql .= " LIMIT $from,$number_of_items";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $arr= array ();
    while ( $arr = Database::fetch_row ( $res) ) {
        $arrs [] = $arr;
    }
    return $arrs;
}

$tool_name = get_lang ( 'CourseList' );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( NULL, FALSE );

$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" ) . "/" . get_lang ( "OfficialCode" );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText', 'title' => $keyword_tip ) );

$form->addElement ( 'select', 'lab_name', get_lang ( '拓扑' ), $labs_device, array ('style' => 'min-width:150px;height:23px;border: 1px solid #999;' ) );

$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );



if (isset ( $_GET ['keyword'] ) && is_not_blank ( $_GET ['keyword'] )) $parameters ['keyword'] = getgpc ( 'keyword' );
if (isset ( $_GET ['lab_name'] ) && is_not_blank ( $_GET ['lab_name'] )) $parameters ['lab_name'] = getgpc ( 'lab_name' );

$table = new SortableTable ( 'labs', 'get_number_of_labs_devices', 'get_labs_devices_data',2, NUMBER_PAGE  );

$idx=0;
$table->set_header ( $idx ++, '编号', false );
$table->set_header ( $idx ++, '分类名称', false, null, array ('style' => 'width:25%;text-align:center' ) );
$table->set_header ( $idx ++, '描述', false, null, array ('style' => 'width:40%;text-align:center' ) );

$table->set_header ( $idx ++, '显示顺序', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, '拓扑总数', false, null, array ('style' => 'width:10%;text-align:center' ) );
$table->set_header ( $idx ++, '操作', false, null, array ('style' => 'width:10%;text-align:center' ) );

$table->set_column_filter ( 2, 'description_filter' );
$table->set_column_filter ( 4, 'labs_count_filter' );
$table->set_column_filter ( 5, 'action_filter' );

//$table->display ();
?>
<aside class="column router open" id="sidebar">
    <div class="closeButton close" id="flexButton">

    </div>
</aside>
<section id="main" class="column">
	<h4 class="page-mark">
  
  	当前位置：<a href="/lms/portal/sp/index.php">首页</a>
       &gt; <a href="/lms/portal/sp/index.php">首页</a>
       &gt;  路由交换课程分类
    </h4>
    <div class="managerSearch">
       <span class="seachtxt right">
         <?php	echo '&nbsp;&nbsp;' . link_button ( 'folder_new.gif', '添加', 'labs_category_add.php?action=add', '90%', '70%' );?>
       </span>
    </div>
		
    <article class="module width_full hidden">
			
    	<?php 
		$table->display ();
		?>
    </article>
</section>
</body>
</html>
