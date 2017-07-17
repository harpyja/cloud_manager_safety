<?php
/*
 ==============================================================================
 课程分类管理
 ==============================================================================
 */

$language_file = 'admin';
$cidReset = true;
include_once ("../../inc/global.inc.php");
$this_section = SECTION_PLATFORM_ADMIN;

api_protect_admin_script ();

require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( SYS_CODE_PATH ) . 'admin/course/course_category.inc.php');

$org_id = (isset ( $_REQUEST ["org_id"] ) ? intval(getgpc ( 'org_id' )) : "-1");
$category = (isset ( $_REQUEST ["category"] ) ? intval(getgpc ( 'category' )) : "0");
$action = getgpc ( 'action' );

$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$tbl_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );

$sql = "SELECT parent_id FROM $tbl_category WHERE id=" . Database::escape (intval(getgpc("id","G")));
$parent_id = Database::get_scalar_value ( $sql );

if (! empty ( $action )) {
	if ($action == 'delete') {
		$rtn=deleteNode ( intval(getgpc("id","G")) );
		if($rtn==101){
			
		}
		api_redirect ( $_SERVER ['PHP_SELF'] . '?category=' . $parent_id . "&result=success" );
	} 

	elseif ($action == 'moveUp') {
		moveNodeUp ( intval(getgpc("id","G")), getgpc("tree_pos","G"), $category );
		api_redirect ( $_SERVER ['PHP_SELF'] . '?category=' . $parent_id . "&result=success" );
	}
}

$htmlHeadXtra [] = Display::display_thickbox ();
$htmlHeadXtra [] = '<script type="text/javascript">
	function refresh_tree(){ parent.CategoryTree.location.reload();/*parent.CategoryTree.d.openAll();*/ }
	</script>';

Display::display_header ( NULL ,FALSE);

if (empty ( $action )) {
	$sql = "SELECT t1.id,t1.name,t1.code,t1.parent_id,t1.tree_pos,t1.children_count,COUNT(DISTINCT t3.code) AS nbr_courses
		 FROM $tbl_category t1 LEFT JOIN $tbl_category t2 ON t1.id=t2.parent_id 
		 LEFT JOIN $tbl_course t3 ON t3.category_code=t1.id 
		 WHERE t1.parent_id =" . Database::escape ( $category ) . " GROUP BY t1.name,t1.parent_id,t1.tree_pos,t1.children_count 
		 ORDER BY t1.tree_pos";
	//echo $sql;
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	$Categories = api_store_result ( $result );
}

if (! empty ( $category ) && empty ( $action )) {
	$result = api_sql_query ( "SELECT parent_id,name FROM $tbl_category WHERE id='$category'", __FILE__, __LINE__ );
	list ( $parent_id, $categoryName ) = mysql_fetch_row ( $result );
}

$objCrsMng = new CourseManager ();
$category_tree = $objCrsMng->get_all_categories_tree ( TRUE );

function _get_course_count($parent_id) {
	$GLOBALS ['objCrsMng']->sub_category_ids = array ();
	$sub_category_ids = $GLOBALS ['objCrsMng']->get_sub_category_tree_ids ( $parent_id, TRUE );
	$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	$sql = "SELECT COUNT(*) FROM " . $tbl_course . " WHERE category_code " . Database::create_in ( $sub_category_ids );
	//echo $sql;
	return Database::get_scalar_value ( $sql );
}

//添加分类链接
echo '<div class="actions">';
//echo link_button ( 'folder_new.gif', 'AddACategory', 'course_category_add_edit.php?action=add&category=' . $category, 240, 540 );
echo link_button ( 'folder_new.gif', 'AddACategory', 'course_category_add_edit.php?action=add&category=' . $category,'90%','95%');
echo '</div>';

$table_header [] = array (get_lang ( 'CategoryName' ) );
$table_header [] = array (get_lang ( 'CategoryCode' ) );
//$table_header [] = array (get_lang ( 'SubCategoryCount' ), false, null, array ('style' => 'width:100px' ) );
$table_header [] = array (get_lang ( 'CourseCount' ), false, null, array ('style' => 'width:80px' ) );
$table_header [] = array (get_lang ( 'Actions' ), false, null, array ('style' => 'width:80px' ) );

foreach ( $Categories as $enreg ) {
	$row = array ();
	$course_count = _get_course_count ( intval($enreg ['id']) );
	//if ($enreg ['children_count']) {
	//$row [] = "<a href='" . $_SERVER ['PHP_SELF'] . "?category=" . urlencode ( $enreg ['id'] ) . "'>" . $enreg ['name'] . "</a>";
	$row [] = $enreg ['name'];
	$row [] = $enreg ['code'];
	//$row [] = $enreg ['children_count'];
	//} 
	$row [] = ($course_count ? $course_count : "");
	
	if ($enreg ['children_count']) {
		$action_html = "&nbsp;".icon_href('folder_document.gif',   "OpenNode" ,$_SERVER ['PHP_SELF'] . "?category=" . urlencode ( intval($enreg ['id']) ));
	} else {
		$action_html = "";
	}
	
	$action_html .= "&nbsp;&nbsp;" . link_button ( 'edit.gif', 'EditNode', 'course_category_add_edit.php?action=edit&category=' . urlencode ( intval($enreg ['id']) ), '90%', '95%', FALSE );
	$action_html .= "&nbsp;&nbsp;" . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'DeleteNode', $_SERVER ['PHP_SELF'] . "?category=" . urlencode ( intval($enreg ['id']) ) . "&action=delete&id=" . urlencode ( intval($enreg ['id']) ) );
	
	//$action_html .= "&nbsp;<a href='" . $_SERVER ['PHP_SELF'] . "?category=" . urlencode ( $enreg ['parent_id'] ) . "&amp;action=moveUp&amp;id=" . urlencode ( $enreg ['id'] ) . "&amp;tree_pos=" . $enreg ['tree_pos'] . "'>" . Display::return_icon ( 'up.gif', get_lang ( "UpInSameLevel" ) ) . "</a>";
	$row [] = $action_html;
	$table_data [] = $row;
}
unset ( $Categories );
echo Display::display_table ( $table_header, $table_data );
Display::display_footer ();