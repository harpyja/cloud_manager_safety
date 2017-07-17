<?php
$language_file = array ('admin' );
$cidReset = true;
include_once ('../../inc/global.inc.php');
api_protect_admin_script ();
if (! isRoot ()) api_not_allowed ();

function get_sqlwhere() {
    $sql = "";
    if (is_not_blank ( $_GET ['keyword'] )) {
        $keyword = trim ( Database::escape_str (getgpc("keyword","G"), TRUE ) );
        $sql .= " AND  (display_name LIKE '%" . $keyword . "%'  OR username LIKE '%" . $keyword . "%'  OR message LIKE '%" . $keyword . "%')";
    }
    if (is_not_blank ( $_GET ['keyword_start'] ) && $_GET ['keyword_start']) {
        $keyword_start = trim ( Database::escape_string ( getgpc ( 'keyword_start', 'G' ) ) ) . " 00:00:00";
        $sql .= " AND log_date>='" . $keyword_start . "'";
    }

    if (is_not_blank ( $_GET ['keyword_end'] )) {
        $keyword_end = trim ( Database::escape_string ( getgpc ( 'keyword_end', 'G' ) ) ) . " 23:59:59";
        $sql .= " AND log_date<='" . $keyword_end . "'";
    }
    $sql = trim ( $sql );
    return substr ( $sql, 3 );
}

function get_number_of_data() {
    $table_sys_logging = Database::get_main_table ( TABLE_MAIN_SYS_LOGGING );
    $sql = "SELECT COUNT(id) AS total_number_of_items FROM $table_sys_logging ";
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    return Database::get_scalar_value ( $sql );
}

function get_data($from, $number_of_items, $column, $direction) {
    $table_sys_logging = Database::get_main_table ( TABLE_MAIN_SYS_LOGGING );
    $sql = "SELECT
	id				AS col0,
	log_date		AS col1,
	username		AS col2,
	display_name	AS col3,
	message		    AS col4,
	access_uri		AS col5,
	id				AS col6
	FROM  $table_sys_logging ";
    $sql_where = get_sqlwhere ();
    if ($sql_where) $sql .= " WHERE " . $sql_where;
    $sql .= " ORDER BY col$column $direction ";
    $sql .= " LIMIT $from,$number_of_items";
    //echo $sql;
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $data = array ();
    while ( $adata = Database::fetch_array ( $res, 'NUM' ) ) {
        $data [] = $adata;
    }
    return $data;
}

function modify_filter($log_id, $url_params) {
    $result .= '<a href="logging_list.php?action=delete_log&amp;id=' . intval($log_id) . '&amp;' . $url_params . '" onclick="javascript:if(!confirm(' . "'" . addslashes ( htmlentities ( get_lang ( "ConfirmYourChoice" ), ENT_NOQUOTES, SYSTEM_CHARSET ) ) . "'" . ')) return false;">' . Display::return_icon (
        'delete.gif', get_lang ( 'Delete' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>';
    return $result;
}

function delete_log($log_id) {
    $table_sys_logging = Database::get_main_table ( TABLE_MAIN_SYS_LOGGING );
    $sql = "DELETE FROM $table_sys_logging WHERE id='" . Database::escape_string ( intval($log_id) ) . "'";
    return api_sql_query ( $sql, __FILE__, __LINE__ );
}
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
Display::display_header ( NULL );

if (isset ( $_GET ['action'] )) {
    switch (getgpc("action","G")) {
        case 'show_message' :
            Display::display_normal_message ( stripslashes (getgpc("message","G") ) );
            break;
        case 'delete_log' : //删除单条记录
            if (delete_log ( getgpc('id') )) {
                Display::display_normal_message ( get_lang ( 'LogDeleted' ) );
            } else {
                Display::display_error_message ( get_lang ( 'CannotDeleteLog' ) );
            }
            break;
    }
}
if (isset ( $_POST ['action'] )) {
    switch (getgpc("action","P")) {
        case 'delete' : //批量删除
            $number_of_selected_items = count ( getgpc('id') );
            $number_of_deleted_items = 0;
            foreach ( getgpc('id') as $index => $item_id ) {
                if (delete_log ( $item_id )) {
                    $number_of_deleted_items ++;
                }
            }
            if ($number_of_selected_items == $number_of_deleted_items) {
                Display::display_normal_message ( get_lang ( 'SelectedItemsDeleted' ) );
            } else {
                Display::display_error_message ( get_lang ( 'SomeItemNotDeleted' ) );
            }
            break;
    }
}



$form = new FormValidator ( 'search_simple', 'get', '', '', null, false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( ' {element} ' );

//$form->addElement ( 'calendar_datetime', 'keyword_start', get_lang ( "From" ), array ('title' => get_lang ( "OperationDateDuration" ) . get_lang ( "StartTime" ) ), array ('show_time' => false , 'class' => 'searchtxt') );
//$form->addElement ( 'calendar_datetime', 'keyword_end', get_lang ( "To" ), array ('title' => get_lang ( "OperationDateDuration" ) . get_lang ( "EndTime" ) ), array ('show_time' => false , 'class' => 'searchtxt') );
//$form->addRule('valid_date', get_lang('StartDateShouldBeBeforeEndDate'),'callback','calendar_compare_lte');
$values ['keyword_start'] = date ( 'Y-m-d', time () - (7 * 24 * 3600) );
$values ['keyword_end'] = date ( 'Y-m-d', time () );

$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:150px", 'class' => 'searchtxt', 'title' => get_lang ( 'keyword' ) ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Search' ), 'class="inputSubmit"' );
$form->setDefaults ( $values );


if (is_not_blank ( $_GET ['keyword'] )) $parameters ['keyword'] = getgpc('keyword');
if (is_not_blank ( $_GET ['keyword_start'] )) $parameters ['keyword_start'] = getgpc('keyword_start');
if (is_not_blank ( $_GET ['keyword_end'] )) $parameters ['keyword_end'] = getgpc('keyword_end');

$table = new SortableTable ( 'adminLoggings', 'get_number_of_data', 'get_data', 1, NUMBER_PAGE, 'DESC' );
$table->set_additional_parameters ( $parameters );
$header_idx = 0;
$table->set_header ( $header_idx ++, '', false );
$table->set_header ( $header_idx ++, get_lang ( 'OperationDate' ), true, null, array ('style' => 'width:160px' ) );
$table->set_header ( $header_idx ++, get_lang ( 'LoginName' ), true, null, array ('width' => '80' ) );
$table->set_header ( $header_idx ++, get_lang ( 'FirstName' ), true, null, array ('width' => '80' ) );
$table->set_header ( $header_idx ++, get_lang ( 'LogMessage' ) );
$table->set_header ( $header_idx ++, get_lang ( 'AccessPath' ) );
$table->set_header ( $header_idx ++, get_lang ( 'Actions' ), false, null, array ('width' => '30' ) );
$table->set_column_filter ( 6, 'modify_filter' );
$table->set_form_actions ( array ('delete' => get_lang ( 'BatchDelete' ) ) );
//$table->set_dispaly_style_navigation_bar(NAV_BAR_BOTTOM);


//Display::display_footer ( TRUE );
?>

<aside id="sidebar" class="column systeminfo open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/systeminfo.php">系统管理</a> &gt; 系统日志</h4>
    <ul class="manage-tab boxPublic">

        <?php
        $html .= '<li  class="selected"><a href="logging_list.php"><em>' . get_lang ( "LoggingList" ) . '</em></a></li>';
        $html .= '<li><a href="access_login_logs.php"><em>' . get_lang ( "AccessLoginLog" ) . '</em></a></li>';
        echo $html;
        ?>
    </ul>
    <div class="manage-tab-content">
        <div class="manage-tab-content-list" >
            <div class="managerSearch">
                <?php $form->display ();?>

            </div>
            <article class="module width_full hidden">
                <?php $table->display ();?>

            </article>
        </div>
        </div>
</section>
</body>
</html>