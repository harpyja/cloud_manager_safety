<?php
$language_file = "link";
include_once ("../inc/global.inc.php");
include_once ("link.inc.php");

api_protect_course_script ();
$is_allowed_to_edit = api_is_allowed_to_edit ();
if (! $is_allowed_to_edit) api_not_allowed ();

require_once (api_get_path ( SYS_CODE_PATH ) . "courseware/cw.lib.inc.php");

$id = intval(getgpc ( 'id') );
$action = getgpc ( 'action', 'G' );

$htmlHeadXtra [] = Display::display_thickbox ();
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

$nameTools = get_lang ( "CourseLinks" );
Display::display_header($nameTools,FALSE);

if (isset ( $_GET ['action'] )) {
	switch ($action) {
		case 'invisible' :
			change_visibility ( $id, 0 );
			break;
		case 'visible' :
			change_visibility ( $id, 1 );
			break;
		case 'deletelink' :
			delete_link ( $id );
			break;
	}
}

echo '<div id="demo" class="yui-navset">';
echo display_cw_action_menus ( 'links' );
echo '<div class="yui-content"><div id="tab1">';

echo '<div class="actions">';
echo link_button ( 'file_html_new.gif', 'LinkAdd', 'link_update.php?action=add', '75%', '70%' );
//	echo "<a href='link_update.php?KeepThis=true&TB_iframe=true&height=300&width=640&modal=true' class=\"thickbox\">" . Display::return_icon('file_html_new.gif') . get_lang("LinkAdd") . "</a>\n";
echo '</div>';

//$table_header [] = array ( );
$table_header [] = array (get_lang ( 'Name' ) );
$table_header [] = array (get_lang ( 'DisplayOrder' ), false, null, array ('width' => '80' ) );
$table_header [] = array (get_lang ( 'MinLearningTime' ), true, null, array ('width' => '120' ) );
//$table_header [] = array (get_lang ( 'Description' ) );
$table_header [] = array (get_lang ( 'CreationDate' ), null, null, array ("width" => "120" ) );
$table_header [] = array (get_lang ( 'Visible' ), false, null, array ('style' => 'width:70px' ) );
$table_header [] = array (get_lang ( 'Actions' ), false, null, array ('style' => 'width:50px' ) );

$sql = "SELECT link.*,link.path AS url FROM " . $table_courseware . " link WHERE link.cc=" . Database::escape ( api_get_course_code () ) . " AND link.cw_type='" . TOOL_LINK . "' ORDER BY link.display_order DESC";
$result = api_sql_query ( $sql, __FILE__, __LINE__ );
while ( $myrow = Database::fetch_array ( $result, 'ASSOC' ) ) {
	$row = array ();
	$myrow ['description'] = text_filter ( $myrow ["description"] );
	$href = 'link_goto.php?' . api_get_cidreq () . "&cw_id=" . $myrow ['id'] . "&link_url=" . urlencode ( $myrow ['url'] );
	$row [] = '<a href="' . $href . '" target="_blank">' . Display::return_icon ( 'file_html.gif', get_lang ( 'Links' ), array ('style' => 'vertical-align: middle;' ) ) . '&nbsp;' . invisible_wrap ( $myrow ['title'], $myrow ['visibility'] == 0 ) . "</a>";
	
	$row [] = invisible_wrap ( $myrow ['display_order'], $myrow ['visibility'] == 0 );
	$row [] = invisible_wrap ( $myrow ['learning_time'], $myrow ['visibility'] == 0 );
	//$row [] = api_trunc_str2 ( invisible_wrap ($myrow ['comment'],$myrow ['visibility'] == 0), 80 );
	$display_date = substr ( $myrow ['created_date'], 0, 10 );
	$row [] = invisible_wrap ( $display_date, $myrow ['visibility'] == 0 );
	
	if ($myrow ['visibility'] == 1) {
		$row [] = '&nbsp;' . "<a href=\"link_list.php?" . api_get_cidreq () . "&action=invisible&amp;id=" . $myrow ['id'] . "\">" . Display::return_icon ( 'visible.gif', get_lang ( 'InVisible' ), array ('style' => 'vertical-align: middle;' ) ) . "</a>";
	}
	if ($myrow ['visibility'] == 0) {
		$row [] = '&nbsp;' . "<a href=\"link_list.php?" . api_get_cidreq () . "&action=visible&amp;id=" . $myrow ['id'] . "\">" . Display::return_icon ( 'invisible.gif', get_lang ( 'Visible' ), array ('style' => 'vertical-align: middle;' ) ) . "</a>";
	}
	
	$actions = '&nbsp;' . link_button ( 'edit.gif', 'LinkMod', "link_update.php?" . api_get_cidreq () . "&action=editlink&amp;id=" . $myrow ['id'], 280, 660, FALSE );
	$href = 'link_list.php?' . api_get_cidreq () . "&action=deletelink&id=" . $myrow ['id'];
	$actions .= '&nbsp;' . confirm_href ( 'delete.gif', 'LinkDelconfirm', 'Delete', $href );
	$row [] = $actions;
	$table_data [] = $row;
}
echo Display::display_table ( $table_header, $table_data );
echo '</div></div></div>';
Display::display_footer ();
