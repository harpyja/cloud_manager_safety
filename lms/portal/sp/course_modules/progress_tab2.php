<?php
include_once ("../inc/app.inc.php");
$user_id = api_get_user_id ();
include_once (api_get_path ( SYS_CODE_PATH ) . 'reporting/cls.track_stat.php');
$is_course_user = (isset ( $_SESSION ["is_allowed_in_course"] ) && $_SESSION ["is_allowed_in_course"] ? TRUE : FALSE);
if (! $is_course_user) {
	api_not_allowed ();
}

$tbl_lp = Database::get_course_table ( TABLE_LP_MAIN );
$tbl_track_cw = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_CW );
$tbl_courseware = Database::get_course_table ( TABLE_COURSEWARE );

$course_code = (isset ( $_GET ["code"] ) ? getgpc ( "code", "G" ) : $cidReq);
if (empty ( $course_code )) $course_code = api_get_course_code ();

//$objStat = new ScormTrackStat ();


?>
<table cellspacing="0" class="tbl_course" style="width: 940px">
	<tr>
		<th class="dd2">课件名称</th>
		<th class="dd2">学习总时间</th>
		<th class="dd2">上次学习时间</th>
		<th class="dd4">学习进度</th>
	</tr>
		<?php
		
		$sqlwhere = " t1.cc='" . escape ( $course_code ) . "' AND t2.user_id='" . escape ( $user_id ) . "'";
		$sql2 = "SELECT t1.*,DATE_FORMAT(FROM_UNIXTIME(last_access_time),'%Y-%m-%d %H:%i') AS last_learn_time,t1.title,t1.cw_type,t2.total_time,t2.progress FROM " . $tbl_courseware . " AS t1 LEFT JOIN " . $tbl_track_cw . " AS t2 ON t2.cw_id=t1.id WHERE " . $sqlwhere;
		//echo $sql2;
		$res2 = api_sql_query ( $sql2, __FILE__, __LINE__ );
		while ( $data2 = Database::fetch_array ( $res2, "ASSOC" ) ) {
			$progress = ($data2 ['progress'] ? $data2 ['progress'] : '0');
			?>
		<tr>
		<td style="padding-left: 30px;">
				<?php
			echo stripslashes ( $data2 ['title'] );
			?></a></td>
		<td style="text-align: center;"><?php
			
			echo api_time_to_hms ( $data2 ['total_time'] );
			?></td>
		<td style="text-align: center;"><?php
			echo $data2 ['last_learn_time']?></td>
		<td style="text-align: left; width: 200px; padding-left: 10px"
			class="percent">
		<div class="tiao"
			style="border: 1px solid black; width: 140px; float: left; margin-top: 5px">
		<div style="background: rgb(0,49,92); height: 14px; width: <?=$progress?>%;"></div>
		</div>
		<div style="float: left; width: 30px;"><?=$progress?>%</div>
		</td>
	</tr>
		<?php
		}
		?>
	</table>