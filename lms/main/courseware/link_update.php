<?php
$language_file = "link";
include_once ("../inc/global.inc.php");
include_once ("link.inc.php");
api_protect_course_script ();
$is_allowed_to_edit = api_is_allowed_to_edit ();
if (! $is_allowed_to_edit) api_not_allowed ();

$tbl_cw = Database::get_course_table ( TABLE_COURSEWARE );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
$nameTools = get_lang ( "CourseLinks" );
Display::display_header ( $nameTools, FALSE );

$strLink = (isset ( $_GET ['id'] ) ? get_lang ( "LinkMod" ) : get_lang ( "LinkAdd" ));

if (is_equal ( $_GET ['action'], 'editlink' ) && isset ( $_GET ['id'] )) {
	$sql = "SELECT * FROM  " . $tbl_cw . " AS t1 WHERE t1.id=" . Database::escape ( getgpc ( 'id', 'G' ) );
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$defaults = Database::fetch_array ( $res, 'ASSOC' );
	$urllink = $defaults ['path'];
}

$form = new FormValidator ( 'link_update', 'POST' );

//$form->addElement ( 'header', 'header', $strLink );
$form->addElement ( 'text', 'urllink', '链接地址', array ('style' => "width:80%", 'class' => 'inputText' ) );
$form->addRule ( 'urllink', get_lang ( 'ThisFieldIsRequired' ), 'required' );
$defaults ['urllink'] = $defaults ['path'];
if (isset ( $_GET ['action'] ) && $_GET ['action'] == "editlink") {
	$form->addElement ( 'hidden', 'action', 'edit_save' );
	$form->addElement ( 'hidden', 'id', intval(getgpc ( 'id', 'G' ) ));
} else {
	$form->addElement ( 'hidden', 'action', 'add_save' );
}
//$defaults ['urllink'] = (empty ( $urllink ) ? 'http://' : htmlentities ( $urllink, ENT_NOQUOTES, SYSTEM_CHARSET ));


$form->addElement ( 'text', 'title', get_lang ( "LinkName" ), array ('style' => "width:80%", 'class' => 'inputText' ) );
//$defaults['title']=htmlentities($title, ENT_NOQUOTES, SYSTEM_CHARSET);

//最小学习时间
$form->add_textfield ( 'learning_time', get_lang ( 'MinLearningTime' ), true, array ('id' => 'learning_time', 'style' => "width:60px;text-align:right;", 'class' => 'inputText', 'maxlength' => 60 ) );
$form->addRule ( 'learning_time', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'learning_time', get_lang ( 'MustLargerThan0' ), 'callback', 'range_check' );
if (! (isset ( $_GET ['action'] ) && $_GET ['action'] == "editlink")) $defaults ['learning_time'] = 30;

//显示顺序
$form->add_textfield ( 'display_order', get_lang ( 'DisplayOrder' ), true, array ('id' => 'learning_order', 'style' => "width:60px;text-align:right;", 'class' => 'inputText', 'maxlength' => 60 ) );
$form->addRule ( 'display_order', get_lang ( 'ThisFieldIsRequiredNumeric' ), 'numeric' );
$form->addRule ( 'display_order', get_lang ( 'MustLargerThan0' ), 'callback', 'range_check' );
if (is_equal ( $_GET ['action'], 'add' )) $defaults ["display_order"] = get_next_disp_order ();

$form->addElement ( 'textarea', 'description', get_lang ( 'Description' ), array ('cols' => 40, 'rows' => 4, 'class' => 'inputText' ) );

$group = array ();
$group [] = & HTML_QuickForm::createElement ( 'style_submit_button', 'submit', get_lang ( 'Save' ), 'class="save"' );
$group [] = & HTML_QuickForm::createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, null, '&nbsp;', '&nbsp;&nbsp;' );
$form->applyFilter ( '__ALL__', 'trim' );

$form->setDefaults ( $defaults );
Display::setTemplateBorder ( $form, '100%' );

if ($form->validate ()) {
	$data = $form->exportValues ();
	$action = $data ['action'] ? $data ['action'] : '';
	$id = Database::escape_string ( intval(getgpc("id","P")) );
	$urllink = Database::escape_string ( $data ['urllink'] );
	$title = Database::escape_string ( $data ['title'] );
	$description = Database::escape_string ( $data ['description'] );
	$order = intval ( $data ['display_order'] );
	//$urllink = (empty ( $urllink ) ? 'http://' : htmlentities ( $urllink, ENT_NOQUOTES, SYSTEM_CHARSET ));
	//if (! strstr ( $urllink, '://' )) $urllink = "http://" . $urllink;
	

	if (empty ( $title )) $title = $urllink;
	if ($action == 'add_save') {
		$sql_data = array ('path' => $urllink, 'title' => $title, 'comment' => $description, 'display_order' => $order, 'cw_type' => 'link', 'learning_time' => intval ( $data ["learning_time"] ) );
		$sql_data ['cc'] = api_get_course_code ();
		$sql_data ['created_date'] = date ( 'Y-m-d H:i:s' );
		$sql = Database::sql_insert ( $table_courseware, $sql_data );
		$new_id = Database::get_last_insert_id ();
		api_sql_query ( $sql, __FILE__, __LINE__ );
		unset ( $urllink, $title, $description );
		api_item_property_update ( $_course, TOOL_LINK, $new_id, "LinkAdded", api_get_user_id () );
		$msgErr = get_lang ( 'LinkAdded' );
	}
	
	if ($action == 'edit_save') {
		$sql_data = array ('path' => $urllink, 'title' => $title, 'comment' => $description, 'display_order' => $order, 'learning_time' => intval ( $data ["learning_time"] ) );
		$sql_where = " id='" . escape ( $id ) . "' AND cc='" . api_get_course_code () . "' ";
		$sql = Database::sql_update ( $table_courseware, $sql_data, $sql_where );
		api_sql_query ( $sql, __FILE__, __LINE__ );
		api_item_property_update ( $_course, TOOL_LINK, $id, "LinkUpdated", api_get_user_id () );
	}
	tb_close ();
}
if (is_equal ( getgpc('action','G'), 'add' )) {
	echo '<div id="demo" class="yui-navset" style="margin:10px">';
	echo display_cw_type_tab ( 'links' );
	echo '<div class="yui-content"><div id="tab1">';
}
$form->display ();
if (is_equal ( getgpc('action','G'), 'add' )) echo '</div></div></div>';
Display::display_footer ();