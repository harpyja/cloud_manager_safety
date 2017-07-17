<?php

require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

class Statistics {
                        
	function print_login_stats($type, $dept_id) {
		$table = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LOGIN );
		$table_user = Database::get_main_table ( VIEW_USER_DEPT );
		//$tbl_dept = Database::get_main_table ( VIEW_USER_DEPT );
		$objDept = new DeptManager ();
		$dept_sn = $objDept->get_sub_dept_sn ( $dept_id );
		if ($dept_sn) $sql_where = "  t2.dept_sn LIKE '" . $dept_sn . "%'";
		switch ($type) {
			case 'month' :
				$period = get_lang ( 'PeriodMonth' );
				if (empty ( $dept_id )) {
					$sql = "SELECT DATE_FORMAT( login_date, '%Y-%m' ) AS stat_date , count( login_id ) AS number_of_logins FROM " . $table . " GROUP BY stat_date ORDER BY login_date ";
				} else {
					$sql = "SELECT DATE_FORMAT( login_date, '%Y-%m' ) AS stat_date , count( login_id ) AS number_of_logins FROM " . $table . " AS t1 LEFT JOIN $table_user AS t2 ON t1.login_user_id=t2.user_id  WHERE  " . $sql_where . " GROUP BY stat_date ORDER BY login_date ";
				}
				break;
			case 'hour' :
				$period = get_lang ( 'PeriodHour' );
				if (empty ( $dept_id )) {
					$sql = "SELECT DATE_FORMAT( login_date, '%H' ) AS stat_date , count( login_id ) AS number_of_logins FROM " . $table . " GROUP BY stat_date ORDER BY stat_date ";
				} else {
					$sql = "SELECT DATE_FORMAT( login_date, '%H' ) AS stat_date , count( login_id ) AS number_of_logins FROM " . $table . " AS t1 LEFT JOIN $table_user AS t2 ON t1.login_user_id=t2.user_id WHERE  " . $sql_where . "  GROUP BY stat_date ORDER BY stat_date ";
				}
				break;
			case 'day' :
				$period = get_lang ( 'PeriodDay' );
				if (empty ( $dept_id )) {
					$sql = "SELECT DATE_FORMAT( login_date, '%W' ) AS stat_date , count( login_id ) AS number_of_logins FROM " . $table . " GROUP BY stat_date ORDER BY DATE_FORMAT( login_date, '%w' ) ";
				} else {
					$sql = "SELECT DATE_FORMAT( login_date, '%W' ) AS stat_date , count( login_id ) AS number_of_logins FROM " . $table . " AS t1 LEFT JOIN $table_user AS t2 ON t1.login_user_id=t2.user_id  WHERE " . $sql_where . "  GROUP BY stat_date ORDER BY DATE_FORMAT( login_date, '%w' ) ";
				}
				break;
			case 'week' :
				$period = get_lang ( "PeriodWeek" );
				if (empty ( $dept_id )) {
					$sql = "SELECT DATE_FORMAT( login_date, '%Y-第%u周' ) AS stat_date , count( login_id ) AS number_of_logins FROM " . $table . " WHERE YEAR(login_date)=YEAR(NOW()) GROUP BY stat_date ORDER BY DATE_FORMAT( login_date, '%u' )";
				} else {
					$sql = "SELECT DATE_FORMAT( login_date, '%Y-第%u周' ) AS stat_date , count( login_id ) AS number_of_logins FROM " . $table . " AS t1 LEFT JOIN $table_user AS t2 ON t1.login_user_id=t2.user_id  
						WHERE   YEAR(login_date)=YEAR(NOW()) AND " . $sql_where . "  GROUP BY stat_date ORDER BY DATE_FORMAT( login_date, '%u' )";
				}
				break;
		}
		//echo $sql;
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		$result = array ();
		while ( $obj = Database::fetch_array ( $res, 'ASSOC' ) ) {
			$result [$obj ['stat_date']] = $obj ['number_of_logins'];
		}
		Statistics::print_stats ( get_lang ( 'Logins' ) . ' (' . $period . ')', $result, true );
	}

	function rescale($data, $max = 500) {
		$data_max = 1;
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $index => $value ) {
				$data_max = ($data_max < $value ? $value : $data_max);
			}
		}
		if ($data) reset ( $data );
		$result = array ();
		$delta = $max / $data_max;
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $index => $value ) {
				$result [$index] = ( int ) round ( $value * $delta );
			}
		}
		return $result;
	}

	function print_stats($title, $stats, $show_total = true, $is_file_size = false, $simple_mode = false) {
		$total = 0;
		$data = Statistics::rescale ( $stats );
		echo '<table width="100%"><tr><td align=center><table width="90%"><tr><td>';
		echo '<table class="data_table" cellspacing="0" cellpadding="3">
			  		  <tr><th colspan="' . ($show_total ? '4' : '3') . '">' . $title . '</th></tr>';
		$i = 0;
		if (is_array ( $stats ) && count ( $stats ) > 0) {
			foreach ( $stats as $subtitle => $number ) {
				$total += $number;
			}
			foreach ( $stats as $subtitle => $number ) {
				$i = $i % 13;
				if (strlen ( $subtitle ) > 30) {
					$subtitle = '<acronym title="' . $subtitle . '">' . substr ( $subtitle, 0, 27 ) . '...</acronym>';
				}
				if (! $is_file_size) {
					$number_label = number_format ( $number, 0, ',', '.' );
				} else {
					$number_label = Statistics::make_size_string ( $number );
				}
				echo '<tr class="row_' . ($i % 2 == 0 ? 'odd' : 'even') . '">
								<td width="150">' . $subtitle . '</td>';
				
				if ($simple_mode) {
					echo '<td width="550" align="left">' . Display::return_icon ( 'bar_1u.gif', '', array ('width' => $data [$subtitle], 'height' => '10' ) ) . '</td>';
				}
				
				echo '<td align="right">' . $number_label . '</td>';
				if ($show_total) {
					echo '<td align="right"> ' . number_format ( 100 * $number / $total, 1, ',', '.' ) . '%</td>';
				}
				echo '</tr>';
				$i ++;
			}
		}
		if ($show_total) {
			if (! $is_file_size) {
				$total_label = number_format ( $total, 0, ',', '.' );
			} else {
				$total_label = Statistics::make_size_string ( $total );
			}
			echo '<tr><th  colspan="4" align="right">' . get_lang ( 'Total' ) . ': ' . $total_label . '</td></tr>';
		}
		echo '</table>';
		echo '</td></tr></table></td></tr></table>';
	}

	/**
	 * Print the number of recent logins
	 */
	function print_recent_login_stats() {
		$total_logins = array ();
		$table = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_LOGIN );
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		if (api_is_platform_admin ()) {
			$sql [get_lang ( 'Thisday' )] = "SELECT count(login_user_id) AS number FROM $table WHERE DATE_ADD(login_date, INTERVAL 1 DAY) >= NOW()";
			$sql [get_lang ( 'Last7days' )] = "SELECT count(login_user_id) AS number  FROM $table WHERE DATE_ADD(login_date, INTERVAL 7 DAY) >= NOW()";
			$sql [get_lang ( 'Last10days' )] = "SELECT count(login_user_id) AS number  FROM $table WHERE DATE_ADD(login_date, INTERVAL 10 DAY) >= NOW()";
			$sql [get_lang ( 'Last14days' )] = "SELECT count(login_user_id) AS number  FROM $table WHERE DATE_ADD(login_date, INTERVAL 14 DAY) >= NOW()";
			$sql [get_lang ( 'Last31days' )] = "SELECT count(login_user_id) AS number  FROM $table WHERE DATE_ADD(login_date, INTERVAL 31 DAY) >= NOW()";
			$sql [get_lang ( 'Total' )] = "SELECT count(login_user_id) AS number  FROM $table";
		} else {
			$restrict_org_id = $_SESSION ['_user'] ['role_restrict'] [ROLE_TRAINING_ADMIN];
			$sql [get_lang ( 'Thisday' )] = "SELECT count(login_user_id) AS number FROM $table AS t1 LEFT JOIN $table_user AS t2 ON t1.login_user_id=t2.user_id WHERE DATE_ADD(login_date, INTERVAL 1 DAY) >= NOW() AND t2.org_id=" . Database::escape ( $restrict_org_id );
			$sql [get_lang ( 'Last7days' )] = "SELECT count(login_user_id) AS number  FROM $table AS t1 LEFT JOIN $table_user AS t2 ON t1.login_user_id=t2.user_id  WHERE DATE_ADD(login_date, INTERVAL 7 DAY) >= NOW() AND t2.org_id=" . Database::escape ( $restrict_org_id );
			$sql [get_lang ( 'Last10days' )] = "SELECT count(login_user_id) AS number  FROM $table  AS t1 LEFT JOIN $table_user AS t2 ON t1.login_user_id=t2.user_id WHERE DATE_ADD(login_date, INTERVAL 10 DAY) >= NOW() AND t2.org_id=" . Database::escape ( $restrict_org_id );
			$sql [get_lang ( 'Last14days' )] = "SELECT count(login_user_id) AS number  FROM $table  AS t1 LEFT JOIN $table_user AS t2 ON t1.login_user_id=t2.user_id WHERE DATE_ADD(login_date, INTERVAL 14 DAY) >= NOW() AND t2.org_id=" . Database::escape ( $restrict_org_id );
			$sql [get_lang ( 'Last31days' )] = "SELECT count(login_user_id) AS number  FROM $table  AS t1 LEFT JOIN $table_user AS t2 ON t1.login_user_id=t2.user_id WHERE DATE_ADD(login_date, INTERVAL 31 DAY) >= NOW() AND t2.org_id=" . Database::escape ( $restrict_org_id );
			$sql [get_lang ( 'Total' )] = "SELECT count(login_user_id) AS number  FROM $table  AS t1 LEFT JOIN $table_user AS t2 ON t1.login_user_id=t2.user_id WHERE  t2.org_id=" . Database::escape ( $restrict_org_id );
		}
		foreach ( $sql as $index => $query ) {
			$res = api_sql_query ( $query, __FILE__, __LINE__ );
			$obj = mysql_fetch_object ( $res );
			$total_logins [$index] = $obj->number;
		}
		Statistics::print_stats ( get_lang ( 'Logins' ), $total_logins, false );
	}

	function display_stat_chart($title = '', $data = array(), $chart_type = "Column3D", $width = 640, $height = 560) {
		echo '<script language="javascript" src="' . api_get_path ( WEB_LIB_PATH ) . 'FusionCharts/Charts/FusionCharts.js"></script>';
		$FC = new FusionCharts ( $chart_type, $width, $height );
		$FC->setSWFPath ( api_get_path ( WEB_LIB_PATH ) . "FusionCharts/Charts/" );
		
		$strParam = "caption=" . $title . "';decimalPrecision=0;formatNumberScale=1";
		$FC->setChartParams ( $strParam );
		
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $key => $val ) {
				$FC->addChartData ( $val, "name=" . $key );
			}
			$FC->renderChart ();
		} else {
			unset ( $FC );
		}
	}
}
