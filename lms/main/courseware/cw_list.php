<?php
$language_file = array ('document' );
include_once ("../inc/global.inc.php");
//api_protect_course_script ();
$is_allowed_to_edit = api_is_allowed_to_edit ();
//if (! $is_allowed_to_edit) api_not_allowed ();
$course_code = api_get_course_code ();

$sys_course_path = api_get_path ( SYS_COURSE_PATH );
$web_media_path = api_get_path ( WEB_COURSE_PATH ) . $course_code . '/document';
$web_html_path = api_get_path ( WEB_COURSE_PATH ) . $course_code . '/html';
$sys_media_path = $sys_course_path . $course_code . '/document';
$sys_html_path = $sys_course_path . $course_code . '/html';
$web_scorm_path = api_get_path ( WEB_COURSE_PATH ) . $course_code . '/scorm';
$table_courseware = Database::get_course_table ( TABLE_COURSEWARE );
$tbl_lp = Database::get_course_table ( TABLE_LP_MAIN );

include_once (api_get_path ( SYS_CODE_PATH ) . "courseware/cw.lib.inc.php");
include_once ("../scorm/content_makers.inc.php");
api_session_unregister ( 'oLP' );
api_session_unregister ( 'lpobject' );

$htmlHeadXtra [] = Display::display_thickbox ();
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

Display::display_header ( NULL, FALSE );
$redirect_url = api_get_path ( WEB_CODE_PATH ) . 'courseware/cw_list.php?' . api_get_cidreq ();
if ($is_allowed_to_edit) {
	if (isset ( $_GET ['action'] )) {
		switch (getgpc('action','G')) {
			case 'set_visible' :
				$sql = "UPDATE $table_courseware SET visibility=1 WHERE cc='" . $course_code . "' AND  id=" . Database::escape ( getgpc ( 'cw_id', 'G' ) );
				api_sql_query ( $sql, __FILE__, __LINE__ );
				api_redirect ( $redirect_url );
				break;
			case 'set_invisible' :
				$sql = "UPDATE $table_courseware SET visibility=0 WHERE cc='" . $course_code . "' AND id=" . Database::escape ( getgpc ( 'cw_id', 'G' ) );
                                api_sql_query ( $sql, __FILE__, __LINE__ );
				api_redirect ( $redirect_url );
				break;
			case 'delete' :
				if (is_equal ( getgpc ('cw_type','G'), 'media' )) {
					delete_courseware ( $_course, getgpc ( 'path', 'G' ), $sys_media_path, TOOL_COURSEWARE_MEDIA );
				} elseif (is_equal ( getgpc('cw_type','G'), 'html' )) {
					delete_courseware ( $_course, getgpc ( 'path', 'G' ), $sys_html_path, TOOL_COURSEWARE_PACKAGE );
				}elseif (is_equal ( getgpc('cw_type','G'), 'swf' )) {

                      $path = getgpc("path","G");
                  $path =   dirname($path);
                  exec("rm -rf /tmp/www$path");
                    delete_courseware ( $_course, getgpc ( 'path', 'G' ), $sys_html_path, TOOL_COURSEWARE_PACKAGE );
                }elseif (is_equal (getgpc ('cw_type','G'), 'link' )) {
					api_sql_query ( "DELETE FROM $table_courseware WHERE id=" . Database::escape ( getgpc ( 'id' ) ) );
				}
				api_redirect ( $redirect_url );
				//break;
		}
	}
}

$pacakge_list = get_all_courseware_data ( api_get_course_code (), null, $is_allowed_to_edit );

$html = "<div class='actions'>";
//$html .= str_repeat ( '&nbsp;', 2 ) . link_button ( 'file_zip.gif', 'UploadCourseware', api_get_path ( WEB_CODE_PATH ) . 'upload/index.php?' . api_get_cidreq () . '&curdirpath=/&tool=' . TOOL_LEARNPATH, '90%', '90%' );
$html .= str_repeat ( '&nbsp;', 2 ) . link_button ( 'file_zip.gif', 'UploadCourseware', api_get_path ( WEB_CODE_PATH ) . 'courseware/swf_update.php?action=add&' . api_get_cidreq (), '90%', '90%' );
$html .= '</div>';
echo $html;

if (isset ( $_GET ['message'] )) Display::display_normal_message ( urldecode ( getgpc ( 'message' ) ) );

//准备列表数据

if (is_array ( $pacakge_list ) && count ( $pacakge_list ) > 0) {
	$sortable_data = array ();
	foreach ( $pacakge_list as $cw_id => $data ) {
		$row = array ();
		$visibility_icon = ($data ['visibility'] == 0) ? 'invisible' : 'visible';
		$visibility_command = ($data ['visibility'] == 0) ? 'set_visible' : 'set_invisible';
		$row [] = invisible_wrap ( $data ["display_order"], $data ['visibility'] == 0 );
		$vsql="select visibility from crs_courseware where cc='{$course_code}' and id='{$data ['id']}'";
                $vsarr=mysql_fetch_assoc(mysql_query($vsql));
                if ($data ['cw_type'] == 'scorm') {
			$lp_url = api_get_path ( WEB_SCORM_PATH ) . 'lp_controller.php?cidReq=' . $course_code . '&action=read&lp_id=' . $data ['attribute'] . '&cw_id=' . $cw_id;
			$image = Display::return_icon ( 'kcmdf.gif', $data ['title'], array ('style' => 'vertical-align: middle;' ) );
			if($vsarr['visibility']){
                           $row [] = '<a href="' . $lp_url . '" target="_blank">' . $image . '&nbsp;' . invisible_wrap ( $data ['title'], $data ['visibility'] == 0 ) . "</a>";
                        }else{
                           $row[]= '<a>' . $image . '&nbsp;' . invisible_wrap ( $data ['title'], $data ['visibility'] == 0 ) . "</a>";
                        }
                        $row [] = invisible_wrap ( get_lang ( 'LearnpathCW' ), $data ['visibility'] == 0 );
			
			$row [] = invisible_wrap ( $data ['learning_time'], $data ['visibility'] == 0 );
			$row [] = invisible_wrap ( format_file_size ( $data ["size"] ), $data ['visibility'] == 0 );
			$row [] = invisible_wrap ( substr ( $data ['created_date'], 0, 10 ), $data ['visibility'] == 0 );
			$sql = "SELECT content_maker FROM " . $tbl_lp . " WHERE id=" . Database::escape ( $data ['attribute'] );
			$lp_maker = Database::getval ( $sql, __FILE__, __LINE__ );
			$row [] = '<em>' . invisible_wrap ( get_lang ( 'AuthoringOptions' ) . ': ' . $content_origins [$lp_maker], $data ['visibility'] == 0 ) . '</em>';
			
			$row [] = icon_href ( $visibility_icon . '.gif', 'Visible', 'cw_list.php?action=' . $visibility_command . '&cw_id=' . $data ['id'] . '&cw_type=' . $data ['cw_type'] );
			if($vsarr['cw_type']){
			   $dsp_edit = link_button ( 'edit.gif', 'Edit', api_get_path ( WEB_CODE_PATH ) . 'scorm/lp_controller.php?' . api_get_cidreq () . '&action=edit&lp_id=' . $data ['attribute'], '60%', '70%', FALSE );
			   $href = api_get_path ( WEB_CODE_PATH ) . 'scorm/lp_controller.php?' . api_get_cidreq () . "&action=delete&lp_id=" . $data ['attribute'];
			   $dsp_delete = "&nbsp;" . confirm_href ( 'delete.gif', 'AreYouSureToDelete', 'Delete', $href );
			   $row [] = '&nbsp;' . $dsp_edit . $dsp_delete;
                             }else{
                           $row[]="<img src='".api_get_path ( WEB_IMAGE_PATH )."edit.gif' style='vertical-align: middle;'/><img src='".api_get_path ( WEB_IMAGE_PATH )."delete.gif' style='vertical-align: middle;'/>";           
                           }
		} elseif ($data ['cw_type'] == 'media') {

			 if($vsarr['visibility']){
                             $row [] = create_media_link ( $web_media_path, $data );
                                }else{
                              $icon = Display::return_icon ( 'videos.gif', $data['title'], array ('style' => 'float:left' ) );            
                              $row[]=$icon."<a style='float:left;'>&nbsp;&nbsp;{$data['title']}</a>";
                         }
                         $image = Display::return_icon ( 'file_flash.gif', $data ['title'], array ('style' => 'vertical-align: middle;' ) );

			$row [] = invisible_wrap ( get_lang ( 'MultiMediaCourseware' ), $data ['visibility'] == 0 );
			$row [] = invisible_wrap ( $data ['learning_time'], $data ['visibility'] == 0 );
			$row [] = invisible_wrap ( format_file_size ( $data ["size"] ), $data ['visibility'] == 0 );
			$row [] = invisible_wrap ( substr ( $data ['created_date'], 0, 10 ), $data ['visibility'] == 0 );
                        //$row [] = '<em>' . invisible_wrap ( get_lang ( 'PlayTime' ) . ': ' . $data ['attribute'] . " s", $data ['visibility'] == 0 ) . '</em>';
			$row[]="<em>{$data['title']}</em>";
			$row [] = icon_href ( $visibility_icon . '.gif', 'Visible', 'cw_list.php?action=' . $visibility_command . '&cw_id=' . $data ['id'] . '&cw_type=media' );
			if($vsarr['visibility']){
			   $row [] = build_media_courseware_action_icons ( $data ['path'], $data ['visibility'], $cw_id, $data ['title'] );
                        }else{
                           $row[]="<img src='".api_get_path ( WEB_IMAGE_PATH )."edit.gif' style='vertical-align: middle;'/><img src='".api_get_path ( WEB_IMAGE_PATH )."delete.gif' style='vertical-align: middle;'/>"; 
                        }
                        
            } elseif ($data ['cw_type'] == 'html') {
			
                        if($vsarr['visibility'])
                        {
                              $row [] = create_package_link ( $web_html_path, $data );
                                }else{
                              $icon = Display::return_icon ( 'file_html.gif', $data['title'], array ('style' => 'vertical-align: middle;' ) );            
                              $row[]=$icon."<a><span class='invisible'>{$data['title']}</span></a>";
                        }
                        $image = Display::return_icon ( 'file_html.gif', $data ['title'], array ('style' => 'vertical-align: middle;' ) );
			$row [] = invisible_wrap ( get_lang ( 'HTMLPackageCourseware' ), $data ['visibility'] == 0 );
			$row [] = invisible_wrap ( $data ['learning_time'], $data ['visibility'] == 0 );
			$row [] = invisible_wrap ( format_file_size ( $data ["size"] ), $data ['visibility'] == 0 );
			$row [] = invisible_wrap ( substr ( $data ['created_date'], 0, 10 ), $data ['visibility'] == 0 );
			$row [] = '<em>' . invisible_wrap ( get_lang ( 'DefaultPage' ) . ': ' . $data ['attribute'], $data ['visibility'] == 0 ) . '</em>';
			$row [] = icon_href ( $visibility_icon . '.gif', 'Visible', 'cw_list.php?action=' . $visibility_command . '&cw_id=' . $data ['id'] . '&cw_type=' . $data ['cw_type'] );
			 if($vsarr['visibility']){
                           $row [] = build_action_icons ( $data ['filetype'], $data ['path'], $data ['visibility'], $cw_id, $data ['title'] );
                             }else{
                           $row[]="<img src='".api_get_path ( WEB_IMAGE_PATH )."edit.gif' style='vertical-align: middle;'/><img src='".api_get_path ( WEB_IMAGE_PATH )."delete.gif' style='vertical-align: middle;'/>";  
                         }
                        } elseif ($data ['cw_type'] == 'swf') {
                        if($vsarr['visibility'])
                        {
                            $row [] = create_swf_link ( $web_media_path, $data );
                               }else{
                            $icon = Display::return_icon ( 'file_html.gif', $data['title'], array ('style' => 'vertical-align: middle;' ) );            
                            $row[]=$icon."<a><span class='invisible'>{$data['title']}</span></a>";
                        }
            $image = Display::return_icon ( 'videos.gif', $data ['title'], array ('style' => 'vertical-align: middle;' ) );
            $row [] = invisible_wrap ( get_lang ( '普通课件' ), $data ['visibility'] == 0 );
            $row [] = invisible_wrap ( $data ['learning_time'], $data ['visibility'] == 0 );
            $row [] = invisible_wrap ( format_file_size ( $data ["size"] ), $data ['visibility'] == 0 );
            $row [] = invisible_wrap ( substr ( $data ['created_date'], 0, 10 ), $data ['visibility'] == 0 );
            $row [] = '<em>' . invisible_wrap ( get_lang ( 'DefaultPage' ) . ': ' . $data ['attribute'], $data ['visibility'] == 0 ) . '</em>';
            $row [] = icon_href ( $visibility_icon . '.gif', 'Visible', 'cw_list.php?action=' . $visibility_command . '&cw_id=' . $data ['id'] . '&cw_type=' . $data ['cw_type'] );
            if($vsarr['visibility']){
                 $row [] = build_action_icons ( $data ['filetype'], $data ['path'], $data ['visibility'], $cw_id, $data ['title'],$data['cw_type']);
                     }else{
                 $row[]="<img src='".api_get_path ( WEB_IMAGE_PATH )."edit.gif' style='vertical-align: middle;'/><img src='".api_get_path ( WEB_IMAGE_PATH )."delete.gif' style='vertical-align: middle;'/>";        
            }          
       }elseif ($data ['cw_type'] == 'link') {
			$href = 'link_goto.php?' . api_get_cidreq () . "&cw_id=" . $cw_id . "&link_url=" . urlencode ( $data ['path'] );
			$image = Display::return_icon ( 'file_html.gif', $data ['title'], array ('style' => 'vertical-align: middle;' ) );
			if($vsarr['visibility']){
                              $row [] = '<a href="' . $href . '" target="_blank">' . Display::return_icon ( 'file_html.gif', get_lang ( 'Links' ), array ('style' => 'vertical-align: middle;' ) ) . '&nbsp;' . invisible_wrap ( $data ['title'], $data ['visibility'] == 0 ) . "</a>";
                                  }else{
                              $row [] ='<a>' . Display::return_icon ( 'file_html.gif', get_lang ( 'Links' ), array ('style' => 'vertical-align: middle;' ) ) . '&nbsp;' . invisible_wrap ( $data ['title'], $data ['visibility'] == 0 ) . "</a>";
                        }
                        $row [] = invisible_wrap ( get_lang ( 'CourseLinks' ), $data ['visibility'] == 0 );
			$row [] = invisible_wrap ( $data ['learning_time'], $data ['visibility'] == 0 );
			$row [] = '';
			$row [] = invisible_wrap ( substr ( $data ['created_date'], 0, 10 ), $data ['visibility'] == 0 );
			$row [] = '<em>' . invisible_wrap ( $data ['path'], $data ['visibility'] == 0 ) . '</em>';
			$row [] = icon_href ( $visibility_icon . '.gif', 'Visible', 'cw_list.php?action=' . $visibility_command . '&cw_id=' . $data ['id'] . '&cw_type=' . $data ['cw_type'] );
			
			if($vsarr['visibility']){
                              $actions = '&nbsp;' . link_button ( 'edit.gif', 'Edit', "link_update.php?" . api_get_cidreq () . "&action=editlink&id=" . $data ['id'], 280, 660, FALSE );
			      $href = 'cw_list.php?' . api_get_cidreq () . "&action=delete&cw_type=link&id=" . $data ['id'];
                              $actions .= '&nbsp;' . confirm_href ( 'delete.gif', 'AreYouSureToDelete', 'Delete', $href );
                                  }else{
                              $actions="<img src='".api_get_path ( WEB_IMAGE_PATH )."edit.gif' style='vertical-align: middle;'/><img src='".api_get_path ( WEB_IMAGE_PATH )."delete.gif' style='vertical-align: middle;'/>";;        
                        }
                        $row [] = $actions;
		}

		$sortable_data [] = $row;
	}

}

$table_header [] = array (get_lang ( 'DisplayOrder' ) );
$table_header [] = array (get_lang ( 'CwTitle' ) );
$table_header [] = array (get_lang ( 'CwType' ) );
$table_header [] = array (get_lang ( 'MinLearningTime' ), true );
$table_header [] = array (get_lang ( 'Size' ) ); //,null,array('width'=>'10%'));
$table_header [] = array (get_lang ( 'UploadTime' ) ); //,null,array('width'=>'10%'));
$table_header [] = array (get_lang ( 'CwAttr' ), false );
$table_header [] = array (get_lang ( 'Visible' ), false );
$table_header [] = array (get_lang ( 'Actions' ), false, null, array ('style' => 'width:50px' ) );
echo Display::display_table ( $table_header, $sortable_data );

echo '</div></div></div>';
Display::display_footer ();
