<?php
/*
 ==============================================================================
 INIT SECTION
 ==============================================================================
 */

// name of the language file that needs to be included
$language_file = array ('assignment', 'admin' );

require ('../inc/global.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'formvalidator/FormValidator.class.php');
require_once (api_get_path ( SYS_CODE_PATH ) . 'assignment/assignment.lib.php');

api_protect_course_script ();
$user_id = api_get_user_id ();
$course_code = api_get_course_code ();

$id = intval(getgpc ( 'id' ));
$assignment_id = getgpc ( 'assignment_id' ); //提交作业后,作业的ID
$action = getgpc ( 'action' );
$display_assignment_form = getgpc ( 'display_assignment_form' );
$strAction = (isset ( $_GET ['action'] ) ? getgpc ( 'action', 'G' ) : 'assign_info');
$strType = (isset ( $_GET ['type'] ) ? getgpc ( 'type', 'G' ) : '');
$strActionType = (empty ( $strType ) ? 'action=' . $strAction . '&type=' . $strType : 'action=' . $strAction);

//$is_course_member = (CourseManager::is_user_subscribe ( $course_code, $user_id ) or api_is_platform_admin ());
$base_work_dir = api_get_path ( SYS_COURSE_PATH ) . $course_code . '/assignment';
$http_www = api_get_path ( WEB_COURSE_PATH ) . $course_code . '/assignment';

//下载附件
$download_file_not_found = false;
if (isset ( $_GET ['action'] )) {
	switch ($action) {
		case 'download' :
			$res = get_assignment_info ( $id );
			if ($file_row = Database::fetch_array ( $res, 'ASSOC' )) {
				$download_name = eregi_replace ( "([[:blank:]])", "", $file_row ['attachment_name'] );
				$full_file_name = $base_work_dir . "/" . $id . "/" . $file_row ['attachment_uri'];
				DocumentManager::file_send_for_download ( $full_file_name, true, $download_name );
				exit ();
			} else {
				$download_file_not_found = true;
			}
			break;
		case 'download_sub_file' :
			$submission_id = intval(getgpc ( 'submission_id', 'G' ));
			$sql = "SELECT * FROM " . $assignment_submission_table . " WHERE id='" . Database::escape_string ( $submission_id ) . "'";
			$res = api_sql_query ( $sql, __FILE__, __LINE___ );
			if ($file_row = Database::fetch_array ( $res, 'ASSOC' )) {
				$download_name = eregi_replace ( "([[:blank:]])", "", $file_row ['attachment_name'] );
				$full_file_name = $base_work_dir . "/" . $id . "/" . $file_row ['attachment_uri'];
				DocumentManager::file_send_for_download ( $full_file_name, true, $download_name );
				exit ();
			} else {
				$download_file_not_found = true;
			}
			
			break;
		case 'download_fb_file' :
			$submission_id = intval(getgpc ( 'submission_id', 'G' ));
			$feedback_id =intval( getgpc ( 'feedback_id', 'G' ));
			$sql = "SELECT * FROM " . $assignment_feedback_table . " where id='" . Database::escape_string ( $feedback_id ) . "'";
			$res = api_sql_query ( $sql, __FILE__, __LINE___ );
			if ($file_row = Database::fetch_array ( $res, 'ASSOC' )) {
				$download_name = eregi_replace ( "([[:blank:]])", "", $file_row ['attachment_name'] );
				//$download_name=str_replace(" ","",$file_row['attachment_name']);
				$full_file_name = $base_work_dir . "/" . $id . "/" . $file_row ['attachment_uri'];
				DocumentManager::file_send_for_download ( $full_file_name, true, $download_name );
				exit ();
			} else {
				$download_file_not_found = true;
			}
			
			break;
	}
}
if ($download_file_not_found) {
	//file not found!
	header ( "HTTP/1.0 404 Not Found" );
	$error404 = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">';
	$error404 .= '<html><head>';
	$error404 .= '<title>404 Not Found</title>';
	$error404 .= '</head><body>';
	$error404 .= '<h1>Not Found</h1>';
	$error404 .= '<p>The requested URL was not found on this server.</p>';
	$error404 .= '<hr>';
	$error404 .= '</body></html>';
	echo ($error404);
	exit ();
}

if($action==go){
    tb_close();
}

$htmlHeadXtra [] = Display::display_thickbox ();
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

Display::display_header ( null, false );

$myTools ['assign_info'] = get_lang ( 'AssignmentInfo' );
$myTools ['mysubmitted'] = get_lang ( 'MySubmitted' );
$myTools ['feedback_display'] = get_lang ( 'FeedbackDisplay' );
//$myTools ['stud_sub_list'] = '其它学员提交的作业';

$tab_html = '<div id="demo" class="yui-navset" style="margin:10px">';
$tab_html .= '<ul class="yui-nav">';
foreach ( $myTools as $key => $value ) {
	$strClass = ($strAction == $key ? 'class="selected"' : '');
	$tab_html .= '<li  ' . $strClass . '><a href="assignment_info_stud.php?action=' . $key . '&id=' . $id . '"><em>' . $value . '</em></a></li>';
}
$tab_html .= '</ul>';
$tab_html .= '<div class="yui-content"><div id="tab1">';
echo $tab_html;

$submission_id = "0";
$priv_status = - 1;

$is_draft = 0;
$sql = "select * from " . $assignment_submission_table . " where assignment_id='" . Database::escape_string ( $id ) . "' and student_id='" . Database::escape_string ( $user_id ) . "' AND cc=" . Database::escape ( $course_code );
$submission_info = Database::fetch_one_row ( $sql, true, __FILE__, __LINE__ );
$is_draft = $submission_info ["is_draft"];
$sub_id = $submission_info ["id"];

$isSubmitted = is_submitted_assignment ( $user_id, $id );

switch ($strAction) {
	case 'assign_info' :
		$result = get_assignment_info ( $id );
		if ($row = mysql_fetch_array ( $result )) { //作业信息	
			//$priv_status=$row['priv_status'];
			echo '<blockquote>';
			if ($isSubmitted) { //没有提交时
//				echo "<a href=\"#\" disabled>" . Display::return_icon ( 'submit_file_na.gif' ) . " " . get_lang ( "SubmitAssignment" ) . "</a> ";
			} else {
				$href = "assignment_sub.php?assignment_id=" . $id . ($is_draft == 1 ? '&action=assign_sub_edit&id=' . $sub_id : '');
//				echo link_button ( 'submit_file.gif', 'SubmitAssignment', $href, '80%', '90%' );
			}
			?>
<table class="data_table">
	<tr class="row_even">
		<td><b><?=get_lang ( 'TitleWork' )?></b></td>
		<td><span><?=$row ['title']?></span></td>
	</tr>
	<tr class="row_odd">
		<td><b><?=get_lang ( 'Authors' )?></b></td>
		<td><?=$row ['author']?></td>
	</tr>
	<tr class="row_even">
		<td><b><?=get_lang ( 'PublishedTime' )?></b></td>
		<td><span><?=$row ['published_time']?></span></td>
	</tr>
	<tr class="row_odd">
		<td><b><?=get_lang ( 'Deadline' )?></b></td>
		<td><?=$row ['deadline']?></td>
	</tr>
	<tr class="row_even">
		<td><b><?=get_lang ( 'AssignmentType' )?></b></td>
		<td><?=$row ['assignment_type'] == 'INDIVIDUAL' ? get_lang ( 'IndividualWork' ) : get_lang ( 'GroupWork2' )?></td>
	</tr>
	<tr class="row_odd">
		<td><b><?=get_lang ( 'IsAllowedSubLate' )?></b></td>
		<td><span><?=$row ['is_allow_late_submission'] == 1 ? get_lang ( 'AllowLateSub' ) : get_lang ( 'NotAllowLateSub' )?></span></td>
	</tr>
	<tr class="row_even">
		<td><b><?=get_lang ( 'DownloadFile' )?></b></td>
		<td><?php
			if (isset ( $row ['attachment_uri'] ) && ! empty ( $row ['attachment_uri'] )) {
				?>
		<!-- <a href="<?=$http_www . "/" . $id?>/<?=$row ['attachment_uri']?>"><span><?=$row ['attachment_name']?></span>&nbsp;&nbsp;(<?=( int ) ($row ['attachment_size'] / 1024)?>KB)</a>-->
		<span><?=$row ['attachment_name']?></span>&nbsp;&nbsp;(<?=( int ) ($row ['attachment_size'] / 1024)?>KB)
		&nbsp;&nbsp; <a
			href="<?=$_SERVER ['PHP_SELF']?>?action=download&id=<?=$id?>"><?=get_lang ( 'Download' )?></a>
		</td>
		<?php
			}
			?>
	</tr>
	<tr class="row_odd">
		<td colspan=2><b><?=get_lang ( 'Content' )?></b>
		<p><span><?=$row ['content']?></span>
		
		</td>
	</tr>
	<?php
			if ($isSubmitted) {
				$sql = "SELECT * FROM " . $assignment_feedback_table . " where submission_id='" . $row ['id'] . "'";
				$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
				if ($row2 = mysql_fetch_array ( $rs )) {
					?>
	<tr class="row_even">
		<td><b><?=get_lang ( 'FeedbackContent' )?></b></td>
		<td><span><?=$row2 ['content']?></span></td>
	</tr>
	<tr class="row_odd">
		<td><b><?=get_lang ( 'Score' )?></b></td>
		<td><span><?=$row2 ['score']?></span></td>
	</tr>
	<?php
				}
			}
			?>
</table>
<p><?php
			if ($isSubmitted) { //没有提交时
//				ssecho "<a href=\"#\" disabled>" . Display::return_icon ( 'submit_file_na.gif' ) . " " . get_lang ( "SubmitAssignment" ) . "</a> ";
			} else {
				echo link_button ( 'submit_file.gif', 'SubmitAssignment', $href, '80%', '90%' );
			}
			echo '</blockquote>';
		} else { //作业信息
			echo '<br/>' . Display::display_normal_message ( get_lang ( 'ThisContentIsEmpty' ) );
		}
		break;
	case "mysubmitted" :
		if ($isSubmitted) { //我已提交作业的信息
			$sql = "SELECT * FROM " . $assignment_submission_table . " WHERE id='" . escape( $sub_id ) . "'";
			$result3 = api_sql_query ( $sql, __FILE__, __LINE__ );
			if ($row3 = Database::fetch_array ( $result3 )) {
                            $title=  Database::getval("SELECT  `title` FROM `course` WHERE `code`=".$row3 ['cc'], __FILE__, __LINE__);
                            $submission_id = $row3 ['id'];
				echo '<blockquote>';
				?>
<table class="data_table">
	<!-- <tr class="row_odd">
		<th width="15%"><?=get_lang ( 'ItemName' )?></th>
		<th><?=get_lang ( 'ItemValue' )?></th>
	</tr> -->
	<tr class="row_even">
		<td><b><?=get_lang ( 'TitleWork' )?></b></td>
		<td><span><?=$title?></span></td>
	</tr>
	<tr class="row_odd">
		<td><b><?=get_lang ( 'Authors' )?></b></td>
		<td><?=$row3 ['author']?></td>
	</tr>
	<tr class="row_even">
		<td><b><?=get_lang ( 'CreationTime' )?></b></td>
		<td><span><?=$row3 ['creation_time']?></span></td>
	</tr>
	<tr class="row_odd">
		<td><b><?=get_lang ( 'DownloadFile' )?></b></td>
                <?php  if($row3 ['attachment_name']!=''){?>
		<td><!-- <a href="<?=$http_www . "/" . $id?>/<?=$row3 ['attachment_uri']?>"><span><?=$row3 ['attachment_name']?></span></a>&nbsp;&nbsp;(<?=( int ) ($row3 ['attachment_size'] / 1024)?>KB) -->
		<span><?=$row3 ['attachment_name']?></span>&nbsp;&nbsp;(<?=( int ) ($row3 ['attachment_size'] / 1024)?>KB)
		&nbsp;&nbsp; <a
			href="<?=$_SERVER ['PHP_SELF']?>?action=download_sub_file&submission_id=<?=$sub_id?>&id=<?=$id?>"><?=get_lang ( 'Download' )?></a>
		</td>
                <?php } ?>
	</tr>
	<tr class="row_even">
		<td colspan=2><b><?=get_lang ( 'Content' )?></b>
		<p><span><?=$row3 ['content']?></span>
		</td>
	</tr>
</table>

<?php
echo '</blockquote>';
					}
				} else {
					echo '<br/>' . Display::display_normal_message ( get_lang ( 'ThisContentIsEmpty' ) );
				}
				break;
			case "feedback_display" :
				$is_display_empty_info_box = true;
				if ($isSubmitted) { //批改结果显示
					$is_display_empty_info_box = false;
					$sql = "SELECT * FROM " . $assignment_feedback_table . " where submission_id='" . $sub_id . "' AND is_draft=0";
					//echo $sql;
					$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
					if ($row2 =Database::fetch_array ( $rs ,'ASSOC')) {
						echo '<blockquote>';
						?>
<table class="data_table">
	<tr class="row_even">
		<td><b><?=get_lang ( 'Score' )?></b></td>
		<td><span><?=$row2 ['score']?></span></td>
	</tr>
	<tr class="row_odd">
		<td><b><?=get_lang ( 'CorrectionTime' )?></b></td>
		<td><span><?=$row2 ['correction_time']?></span></td>
	</tr>
	<tr class="row_even">
		<td><b><?=get_lang ( 'DownloadFile' )?></b></td>
		<td><?php
						if (is_not_blank ( $row2 ['attachment_uri'] )) {
							?> <!-- <a href="<?=$http_www . "/" . $id?>/<?=$row2 ['attachment_uri']?>"><span><?=$row2 ['attachment_name']?></span></a>&nbsp;&nbsp;(<?=( int ) ($row2 ['attachment_size'] / 1024)?>KB) -->
		<span><?=$row2 ['attachment_name']?></span>&nbsp;&nbsp;(<?=( int ) ($row2 ['attachment_size'] / 1024)?>KB)
		&nbsp;&nbsp; <a
			href="<?=$_SERVER ['PHP_SELF']?>?action=download_fb_file&feedback_id=<?=$row2 ['id']?>&submission_id=<?=$sub_id?>&id=<?=$id?>"><?=get_lang ( 'Download' )?></a>
			<?php
						}
						?></td>
	</tr>
	<tr class="row_odd">
		<td colspan=2><b><?=get_lang ( 'FeedbackContent' )?></b>
		<p><span><?=$row2 ['content']?></span>
		
		</td>
	</tr>
</table>

<?php
	echo '</blockquote>';
					} else {
						echo '<br/>' . Display::display_normal_message ( get_lang ( 'ThisContentIsEmpty' ) );
					}
				} else {
					echo '<br/>' . Display::display_normal_message ( get_lang ( 'YouHavenontSubmitedTheAssignment' ) );
				}
				break;
			case "stud_sub_list" :
				$is_display_sub_list = false;
				mysql_free_result ( $result );
				$result = get_assignment_info ( $id );
				if ($row = mysql_fetch_array ( $result )) {
					$priv_status = $row ['priv_status'];
				}
				switch ($priv_status) { //判断提交作业列表显示权限
					case 2 :
						if ($is_course_member)
							$is_display_sub_list = true;
						else $is_display_sub_list = false;
						break;
					case 1 :
						if ($is_allowed_to_edit || $isSubmitted)
							$is_display_sub_list = true;
						else $is_display_sub_list = false;
						break;
					case 0 :
						if ($is_allowed_to_edit)
							$is_display_sub_list = true;
						else $is_display_sub_list = false;
						break;
					default :
						$is_display_sub_list = false;
				}
				//echo $is_display_sub_list?"true":"false";
				//$is_display_sub_list=true;
				if ($is_display_sub_list) {
					?>
<!-- <h4><?=get_lang ( 'AssignmentStudentSubList' )?></h4> -->
<blockquote><?php
					$assignment_submission_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_SUBMISSION );
					$assignment_feedback_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_FEEDBACK );
					$sql1 = "select t1.id,t1.assignment_id,t1.title,t1.creation_time,t1.author,
	if(status=1,'" . get_lang ( 'Feedbacked' ) . "','" . get_lang ( 'ToFeedback' ) . "') as status ,assignment_id,
	if(t2.score is NULL,'',t2.score) as score 
	from " . $assignment_submission_table . " t1
	left join " . $assignment_feedback_table . " t2 on t1.id=t2.submission_id 
	where t1.assignment_id=" . Database::escape_string ( $id ) . " and t1.is_draft=0 order by author,creation_time";
					//echo $sql1;
					$result1 = api_sql_query ( $sql1, __FILE__, __LINE__ );
					
					$table_header [] = array (get_lang ( 'Submitter' ), true );
					$table_header [] = array (get_lang ( 'TitleWork' ), true );
					$table_header [] = array (get_lang ( 'CreationTime' ), true );
					$table_header [] = array (get_lang ( 'AssignmentStatus' ), true );
					$table_header [] = array (get_lang ( 'Score' ), true );
					//$table_header[] = array(get_lang('Actions'),false);
					

					$table_data = array ();
					while ( $data = mysql_fetch_object ( $result1 ) ) {
						$row_data = array ();
						$row_data [] = $data->author;
						$row_data [] = $data->title;
						$row_data [] = $data->creation_time;
						$row_data [] = $data->status;
						$row_data [] = $data->score;
						//if($data->score==0)
						//$actionHtml='<a href="assignment_feedback.php?assignment_id='.$data->assignment_id.'&submission_id='.$data->id.'">' . Display::return_icon('edit.gif', get_lang('AssignmentFeedBack')) . '</a>';;
						//$row_data[]=$actionHtml;
						$table_data [] = $row_data;
					}
					$query_vars = array ('id' => intval(getgpc("id","G")), 'action' => 'stud_sub_list' );
					Display::display_sortable_table ( $table_header, $table_data, array (), array (), $query_vars );
					echo '</blockquote>';
				} else {
					echo '<br/>' . Display::display_normal_message ( get_lang ( 'ThisContentIsEmpty' ) );
				}
				break;
		}
		echo '</div></div></div>';
		Display::display_footer ();
