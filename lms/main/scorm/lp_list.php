<?php
/*
 ==============================================================================

 ==============================================================================
 */
require_once ('learnpathList.class.php');
require_once ('learnpath.class.php');
require_once ('learnpathItem.class.php');
require_once ('back_compat.inc.php');

$is_allowed_to_edit = api_is_allowed_to_edit ();
if (! $is_allowed_to_edit) api_not_allowed ();

require_once (api_get_path ( SYS_CODE_PATH ) . 'reporting/cls.track_stat.php');
require_once (api_get_path ( SYS_CODE_PATH ) . "courseware/cw.lib.inc.php");
$tbl_courseware = Database::get_course_table ( TABLE_COURSEWARE );
$course_code = api_get_course_code ();
$baseWordDir = $course_code . '/scorm';
$display_progress_bar = true;
$objStat = new ScormTrackStat ();

$list = new LearnpathList ( api_get_user_id () );
$flat_list = $list->get_flat_list ();
$max = count ( $flat_list );
$vis_count = 0; //可显示的SCORM总数
if (is_array ( $flat_list )) {
	foreach ( $flat_list as $id => $details ) {
		if ($details ['lp_visibility']) $vis_count ++;
	}
}
include_once ("content_makers.inc.php");

$htmlHeadXtra [] = '<script language="javascript" type="text/javascript">
	function confirmation(name){
		return (confirm("' . trim ( get_lang ( 'AreYouSureToDelete' ) ) . ' "+name+"? ' . get_lang ( "confirm_delete_learnpath" ) . '"));
	}
</script>';

$htmlHeadXtra [] = Display::display_thickbox ();
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';
Display::display_header ( NULL, FALSE );

echo '<div id="demo" class="yui-navset">';
echo display_cw_action_menus ( 'lp' );
echo '<div class="yui-content"><div id="tab1">';

//教师管理员

$g_dialogtype=  getgpc('dialogtype');
if ($is_allowed_to_edit) {
	$dialog_box = $g_dialogtype;
	if (! empty ( $dialog_box )) {
		switch ($g_dialogtype) {
			case 'confirmation' :
				Display::display_confirmation_message ( get_lang ( $dialog_box ) );
				break;
			case 'error' :
				Display::display_error_message ( get_lang ( $dialog_box ) );
				break;
			case 'warning' :
				Display::display_warning_message ( get_lang ( $dialog_box ) );
				break;
			default :
				Display::display_normal_message ( get_lang ( $dialog_box ) );
				break;
		}
	}
	if (api_failure::get_last_failure ()) {
		Display::display_normal_message ( api_failure::get_last_failure () );
	}
	
	echo '<div class="actions">';
	echo str_repeat ( '&nbsp;', 2 ) . link_button ( 'file_zip.gif', 'UploadScorm', api_get_path ( WEB_CODE_PATH ) . 'upload/index.php?' . api_get_cidreq () . '&curdirpath=/&tool=' . TOOL_LEARNPATH, '90%', '90%' );
	echo '</div>';
}

if ($max == 0) { //没有数据时
	Display::display_normal_message ( get_lang ( "NoDisplayItem" ) );
} else {
	$table_header [] = array (get_lang ( 'Name' ) );
	$table_header [] = array (get_lang ( 'DisplayOrder' ), false, null, array ('width' => '80' ) );
	//$table_header [] = array (get_lang ( 'Progress' ), null, array ('width' => '200' ) );
	$table_header [] = array (get_lang ( 'LPLearningTime' ), false, null, array ('width' => '120' ) );
	$table_header [] = array (get_lang ( 'CreationDate' ), false, null, array ('width' => '80' ) );
	$table_header [] = array (get_lang ( 'Origin' ), false, null, array ('width' => '160' ) );
	$table_header [] = array (get_lang ( 'Visible' ), false, null, array ('style' => 'width:70px' ) );
	$table_header [] = array (get_lang ( 'AuthoringOptions' ), false, null, array ('width' => '50' ) );
	
	$table_data = array ();
	if (is_array ( $flat_list )) {
		foreach ( $flat_list as $id => $details ) {
			$row = array ();
			//$sql = "SELECT id FROM $tbl_courseware WHERE cc='" . $course_code . "' AND attribute='" . $id . "'";
			//$cw_id = Database::get_scalar_value ( $sql );
			//$url_start_lp = api_get_path ( WEB_CODE_PATH ) . SCORM_PATH . 'lp_controller.php?' . api_get_cidreq () . '&action=view&lp_id=' . $id;
			$name = Security::remove_XSS ( $details ['lp_name'] );
			$image = Display::return_icon ( 'kcmdf.gif', $name, array ('style' => 'vertical-align: middle;' ) );
			//$row [] = $image . '&nbsp;<a href="' . $url_start_lp . '" target="_blank">' . $name . '</a>';
			$row [] = $image . '&nbsp;' . invisible_wrap ( $details ['lp_name'], $details ['lp_visibility'] == 0 );
			$row [] = (empty ( $details ["lp_learning_order"] ) ? "" : invisible_wrap ( $details ["lp_learning_order"], $details ['lp_visibility'] == 0 ));
			
			$row [] = (empty ( $details ["lp_learning_time"] ) ? "" : invisible_wrap ( $details ["lp_learning_time"], $details ['lp_visibility'] == 0 ));
			
			//创建时间
			list ( $dsp_creation_date, $tmp_creation_time ) = preg_split ( "/\s+/", $details ['lp_creation_date'], 2, PREG_SPLIT_NO_EMPTY ); //explode更快
			$row [] = invisible_wrap ( $dsp_creation_date, $details ['lp_visibility'] == 0 );
			
			//设置描述
			$dsp_desc = '<em>' . invisible_wrap ( $content_origins [$details ['lp_maker']], $details ['lp_visibility'] == 0 ) . '</em>';
			
			$row [] = $dsp_desc;
			
			//显示-隐藏
			if ($details ['lp_visibility'] == 0) {
				$row [] = "<a href=\"" . api_get_self () . "?" . api_get_cidreq () . "&lp_id=$id&action=toggle_visible&new_status=1\">" . Display::return_icon ( 'invisible.gif', get_lang ( 'Show' ), array ('style' => 'vertical-align: middle;' ) ) . "</a>" . "";
			} else {
				$row [] = "<a href='" . api_get_self () . "?" . api_get_cidreq () . "&lp_id=$id&action=toggle_visible&new_status=0'>" . Display::return_icon ( 'visible.gif', get_lang ( 'Hide' ), array ('style' => 'vertical-align: middle;' ) ) . "</a>";
			}
			
			$dsp_edit = link_button ( 'edit.gif', 'EditLPSettings', 'lp_controller.php?' . api_get_cidreq () . '&action=edit&lp_id=' . $id, '70%', '70%', FALSE );
			//删除
			$href = "lp_controller.php?" . api_get_cidreq () . "&action=delete&lp_id=" . $id;
			$dsp_delete = "&nbsp;" . confirm_href ( 'delete.gif', 'AreYouSureToDelete', 'Delete', $href );
			
			$row [] = $dsp_edit . $dsp_delete;
			$table_data [] = $row;
		}
		
		$query_vars ['tabAction'] = 'lp';
		$sorting_options = array ();
		echo Display::display_table ( $table_header, $table_data );
	}
}
echo '</div></div></div>';
Display::display_footer ();
