<?php
$language_file = array ('course_description', 'courses' );
include_once ('../inc/global.inc.php');
api_protect_course_script ();

require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include_once ('desc.inc.php');
if (! $allowed_to_edit) api_not_allowed ();

$ids=intval(getgpc('id'));
$action   = getgpc('action','G'); 
$description_id = isset ( $ids ) ? intval ( $ids ) : null;

if (! is_null ( $description_id )) { //处理相关逻辑:删除,编辑
	if (isset ( $action ) && $action == 'delete') {
		$sql = "DELETE FROM $tbl_course_description WHERE id='$description_id' AND  cc=" . Database::escape ( api_get_course_code () );
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		//liyu: 删除附件
		$sql = "SELECT url FROM " . $tbl_attachment . " WHERE type='COURSE_DESC' AND ref_id='" . $description_id . "' AND  cc=" . Database::escape ( api_get_course_code () );
		$attachment_uri = Database::get_scalar_value ( $sql );
		$attachment_uri = Database::get_one_value ( $res );
		if (isset ( $attachment_uri ) && ! empty ( $attachment_uri )) {
			$old_file_path = $course_dir . "/" . $attachment_uri;
			$del_res = unlink ( $old_file_path );
		}
		//$sql="DELETE FROM ".$tbl_attachment." WHERE type='COURSE_DESC' AND ref_id='".$description_id."'";
		$sql = Database::delete_from_course_table ( $tbl_attachment, "type='COURSE_DESC' AND ref_id='" . $description_id . "'" );
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		api_redirect ( "desc_list.php?message=" . urlencode ( get_lang ( 'CourseDescriptionDeleted' ) ) );
	
	} 

	elseif (is_equal ( $action, 'visible' )) {
		Database::update_course_table ( $tbl_course_description, array ('enabled' => '1' ), "id='$description_id'" );
		api_redirect ( "desc_list.php?message=" . urlencode ( get_lang ( 'OperationSuccess' ) ) );
	} 

	elseif (is_equal ( $action, 'invisible' )) {
		Database::update_course_table ( $tbl_course_description, array ('enabled' => '0' ), "id='$description_id'" );
		api_redirect ( "desc_list.php?message=" . urlencode ( get_lang ( 'OperationSuccess' ) ) );
	} 

	elseif (is_equal ( $action, 'moveUp' )) {
		
		$result = Database::select_from_course_table ( $tbl_course_description, "display_order<" . Database::escape ( getgpc ( 'display_order', 'G' ) ), "id,display_order", "display_order DESC", 1 );
		if ($row = Database::fetch_array ( $result, 'ASSOC' )) {
			//交换位置
			//$sql="UPDATE $tbl_course_description SET display_order='".$row['display_order']."' WHERE id='".Database::escape_string($description_id)."'";
			Database::update_course_table ( $tbl_course_description, array ('display_order' => $row ['display_order'] ), "id=" . Database::escape ( $description_id ) );
			
			//$sql="UPDATE $tbl_course_description SET display_order='".Database::escape_string(getgpc('display_order','G'))."' WHERE id='".$row['id']."'";
			Database::update_course_table ( $tbl_course_description, array ('display_order' => getgpc ( 'display_order', 'G' ) ), "id='" . $row ['id'] . "'" );
		}
		api_redirect ( "desc_list.php?message=" . urlencode ( get_lang ( 'OperationSuccess' ) ) );
	} 

	elseif (is_equal ( $action, 'moveDown' )) {
		//$sql="SELECT id,display_order FROM $tbl_course_description WHERE display_order>".Database::escape(getgpc('display_order','G'))." ORDER BY display_order ASC LIMIT 0,1";
		$result = Database::select_from_course_table ( $tbl_course_description, "display_order>" . Database::escape ( getgpc ( 'display_order', 'G' ) ), "id,display_order", "display_order ASC", 1 );
		
		if ($row = Database::fetch_array ( $result, 'ASSOC' )) {
			//交换位置
			//$sql="UPDATE $tbl_course_description SET display_order='".$row['display_order']."' WHERE id='".Database::escape_string($description_id)."'";
			Database::update_course_table ( $tbl_course_description, array ('display_order' => $row ['display_order'] ), "id=" . Database::escape ( $description_id ) );
			
			//$sql="UPDATE $tbl_course_description SET display_order='".Database::escape_string(getgpc('display_order','G'))."' WHERE id='".$row['id']."'";
			Database::update_course_table ( $tbl_course_description, array ('display_order' => getgpc ( 'display_order', 'G' ) ), "id='" . $row ['id'] . "'" );
		
		}
		api_redirect ( "desc_list.php?message=" . urlencode ( get_lang ( 'OperationSuccess' ) ) );
	}
}

//JQuery,Thickbox
$htmlHeadXtra [] = Display::display_thickbox ();
$htmlHeadXtra [] = '<script type="text/javascript">
	function refresh_tree(){ parent.Tree.location.reload();parent.Tree.d.openAll(); }
	</script>';

$toolName = get_lang ( "TagManagement" );
$interbreadcrumb [] = array ("url" => "index.php", "name" => get_lang ( 'CourseProgram' ) );
$interbreadcrumb [] = array ("url" => "desc_list.php", "name" => $toolName );

Display::display_header($tool_name,FALSE);

if (isset ( getgpc('message','G') )) {
	Display::display_confirmation_message ( urldecode ( getgpc('message','G') ) );
}

$properties ["width"] = '90%';
$table_header [] = array (get_lang ( 'TagName' ), null, null, array ('width' => '150' ) ); 
$table_header [] = array (get_lang ( 'OldTagName' ), null, null, array ('width' => '100' ) ); 
$table_header [] = get_lang ( 'Comment' );
//$table_header [] = get_lang ( 'DisplayOrder' );
$table_header [] = array (get_lang ( 'Actions' ), null, null, array ('width' => '120' ) );

$sql = "SELECT * FROM " . $tbl_course_description . " WHERE  cc=" . Database::escape ( api_get_course_code () ) . " ORDER BY display_order ASC";
$data = api_sql_query_array_assoc ( $sql, __FILE__, __LINE__ );
if (is_array ( $data ) && $data) {
	$cnt = count ( $data );
	foreach ( $data as $info ) {
		$row = array ();
		$row [] = $info ['title'];
		$row [] = $info ['id'] ? $default_description_titles [$info ['id']] : "";
		$row [] = api_trunc_str ( $info ['summary'], 60 );
		$action = "";
		if ($allowed_to_edit) {
			$action .= '&nbsp;&nbsp;<a class="thickbox" href="desc_update.php?action=edit&id=' . $info ['id'] . '&KeepThis=true&TB_iframe=true&height=90%&width=80%&modal=true">' . Display::return_icon ( 'edit.gif', get_lang ( 'Edit' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>&nbsp;';
			
			if ($info ['id'] != 1) {
				if ($info ['enabled']) {
					$action .= '&nbsp;&nbsp;<a href="desc_list.php?action=invisible&id=' . $info ['id'] . '">' . Display::return_icon ( 'visible.gif', get_lang ( 'Invisible' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>&nbsp;';
				} else {
					$action .= '&nbsp;&nbsp;<a href="desc_list.php?action=visible&id=' . $info ['id'] . '">' . Display::return_icon ( 'invisible.gif', get_lang ( 'Visible1' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>&nbsp;';
				}
			} else {
				$action .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			//$action .= '&nbsp;&nbsp;<a href="desclist.php?action=delete&amp;id=' . $info ['id'] .'" onclick="javascript:if(!confirm(' . "'" . addslashes ( htmlentities ( get_lang ( "ConfirmYourChoice" ), ENT_NOQUOTES, SYSTEM_CHARSET ) ) . "'" . ')) return false;">' . Display::return_icon ( 'delete.gif', get_lang ( 'Delete' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>';
			

			if ($info ['display_order'] == 1 && $cnt != 1) { //首项
				$href = $_SERVER ['PHP_SELF'] . "?action=moveDown&amp;id=" . urlencode ( $info ['id'] ) . "&amp;display_order=" . $info ['display_order'];
				$action .= "&nbsp;&nbsp;<a href='" . $href . "'>" . Display::return_icon ( 'arrow_down_0.gif', get_lang ( 'MoveDown' ), 'align="absmiddle" border="0"' ) . '</a>' . Display::return_icon ( 'blanco.png', '', 'align="absmiddle" border="0"' );
			} elseif ($row_index == $cnt - 1 && $cnt != 1) { //末项
				$href = $_SERVER ['PHP_SELF'] . "?action=moveUp&amp;id=" . urlencode ( $info ['id'] ) . "&amp;display_order=" . $info ['display_order'];
				$action .= "&nbsp;&nbsp;<a href='" . $href . "'>" . Display::return_icon ( 'arrow_up_0.gif', get_lang ( "MoveUp" ) ) . "</a>";
			} elseif ($cnt == 1) {
			
			} else {
				$href = $_SERVER ['PHP_SELF'] . "?action=moveDown&amp;id=" . urlencode ( $info ['id'] ) . "&amp;display_order=" . $info ['display_order'];
				$action .= "&nbsp;&nbsp;<a href='" . $href . "'>" . Display::return_icon ( 'arrow_down_0.gif', get_lang ( 'MoveDown' ), 'align="absmiddle" border="0"' ) . '</a>' . Display::return_icon ( 'blanco.png', '', 'align="absmiddle" border="0"' );
				$action .= "<a href='" . $_SERVER ['PHP_SELF'] . "?action=moveUp&amp;id=" . urlencode ( $info ['id'] ) . "&amp;display_order=" . $info ['display_order'] . "'>" . Display::return_icon ( 'arrow_up_0.gif', get_lang ( "MoveUp" ) ) . "</a>";
			}
		}
		$row [] = $action;
		$table_data[]=$row;
	}
	echo Display::display_table ( $table_header, $table_data );
} else {
	Display::display_normal_message ( get_lang ( 'ThisCourseDescriptionIsEmpty' ) );
}

Display::display_footer ();
