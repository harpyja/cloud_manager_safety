<?php
if (!defined('IN_QH')) exit('Access Denied !');
$sql = "SELECT * FROM  " . $tbl_document . "  AS docs	 WHERE docs.path LIKE '/%' AND docs.path NOT LIKE '/%/%'

		 			AND path!='/learnpath'	AND docs.cc='" . $cidReq . "' ORDER BY docs.display_order ASC,docs.filetype DESC";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	?>

<div class="course_title_frm" style="margin-top: 10px;">
<div class="course_title">
<div style="float: left;" class="de4"></div>
</div>
</div>
<script language="JavaScript" type="text/JavaScript">
function show_hide(ID){
	if(G('tr'+ID) && G('img'+ID)){
		if(G('tr'+ID).style.display=='none')
		{
	   		G('tr'+ID).style.display=''
	   		G('img'+ID).src='<?=api_get_path ( WEB_IMG_PATH )?>folderopen.gif';
	   		return;
		}

		G('tr'+ID).style.display='none';
		G('img'+ID).src='<?=api_get_path ( WEB_IMG_PATH )?>folderclosed.gif';
	}	
}
</script><style>.tab_content ul li{display:block; } .tab_content  td{border-top:0;}</style>
<!--<div class="tab_content de1" style="border-top: #b9cde5 1px solid;min-height:250px">-->
<table cellspacing="0"  style="width: 100%" border='0'>
	<!-- <tr>
		<th class="dd2">文档名称</th>
		<th class="dd4">大小</th>
		<th class="dd2">操作</th>
	</tr> -->
<?php
	while ($row = Database::fetch_array ( $result, "ASSOC" )) {
		if ($row ["filetype"] == 'folder') {
			$sql = "SELECT docs.* FROM  " . $tbl_document . "  AS docs
		 		WHERE docs.path LIKE '" . $row ["path"] . "/%' AND docs.path NOT LIKE '" . $row ["path"] . "/%/%'
		 		AND docs.cc='" . $cidReq . "' ORDER BY docs.display_order ASC,docs.filetype DESC";
			$result1 = api_sql_query ( $sql, __FILE__, __LINE__ );
			$sub_files_cnt = Database::num_rows ( $result1 );
			?>
	<tr>
		<td>
		<div style="padding-left: 10px"><?php
			echo Display::return_icon ( 'folderclosed.gif', '点击展开/关闭文件夹', array ('onclick' => "show_hide('" . $row ["id"] . "');", 'style' => 'cursor:hand;', 'id' => 'img' . $row ["id"] ) );
			?>
		<a href="javascript:show_hide('<?=$row ["id"]?>');"><?=$row ["title"]?></a>&nbsp;&nbsp;(<?php
			echo $sub_files_cnt?>文件)</div>
		</td>
	</tr>
	<?php
			if ($sub_files_cnt > 0) {
				echo '<tr  id="tr' . $row ["id"] . '" style="display: none;"><td><div>';
				while ( $row1 = Database::fetch_array ( $result1, "ASSOC" ) ) {
					$forcedownload_link = $_SERVER ['PHP_SELF'] . '?cidReq=' . $cidReq . '&action=documents&todo=download&id=' . urlencode ( $row1 ['path'] );
					?>
	<ul style="width: 600px">
		<li style="padding-left: 40px; float: left;"><?php
					echo build_document_icon_tag ( $row1 ['filetype'], $row1 ['path'] );
					?>&nbsp;&nbsp;<a
			href="<?=$forcedownload_link?>"><?=$row1 ["title"]?></a>&nbsp;&nbsp;(
		<?php
					echo format_file_size ( $row1 ["size"] );
					?>)</li>
		<li><a
			href="<?=$forcedownload_link?>"><?=Display::return_icon ( "filesave.gif" )?>下载</a></li>
	</ul>
	<?php
				}
				echo '</div></td></tr>';
			}
		} else {
			$forcedownload_link = 'course_home.php?cidReq=' . $cidReq . '&action=documents&todo=download&id=' . urlencode ( $row ['path'] );
			?>
	<tr>
		<td>
		<div>
		<ul style="width: 100%" class="course-book">
			<li style="float: left;"><?php
			echo build_document_icon_tag ( $row ['filetype'], $row ['path'] );
			?>&nbsp;&nbsp;<a
				href="<?=$forcedownload_link?>" title="<?=strip_tags($row ["comment"])?>"><?=api_trunc_str2($row ["title"])?></a>&nbsp;&nbsp;(
			<?php
			echo format_file_size ( $row ["size"] );
			?>)</li>
			<li style="float: right; margin-right: 20px"><a
				href="<?=$forcedownload_link?>"><?=Display::return_icon ( "filesave.gif" ,'', array ('style' => 'vertical-align: middle' ))?>下载</a></li>
		</ul>
		</div>
		</td>
	</tr>

	<?php
		}
	}
	?>
</table>
<!--</div>-->
<style>
    .course-book li a{
        font-size:14px;
    }
</style>