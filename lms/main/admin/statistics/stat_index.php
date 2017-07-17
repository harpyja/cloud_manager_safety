<?php

/**
 ==============================================================================
 * 新统计图表，增加FLASH动态显示，原来为 index.php
 * @package zllms.statistics
 ==============================================================================
 */

$language_file=array('admin','tracking','course_home');
$cidReset = true;

include('../../inc/global.inc.php');

//api_protect_admin_script ();
$required_roles=array(ROLE_TRAINING_ADMIN);
if(validate_role_base_permision($required_roles)===FALSE){
	api_deny_access(TRUE);
}
$restrict_org_id=$_SESSION['_user']['role_restrict'][ROLE_TRAINING_ADMIN];

$this_section = SECTION_PLATFORM_ADMIN;
$display_admin_menushortcuts=TRUE;

//require_once (api_get_path(SYS_CODE_PATH).'admin/admin.lib.inc.php');
include_once(api_get_path(LIB_PATH) . 'FusionCharts/Includes/FusionCharts_Gen.php');
require_once (api_get_path(SYS_CODE_PATH).'admin/statistics/statistics.lib.php');

$interbreadcrumb[] = array ("url" => api_get_path(WEB_ADMIN_PATH)."index.php", "name" => get_lang('PlatformAdmin'));

$tool_name = get_lang('AccessStat');
Display::display_header($tool_name);

if(!$_configuration['tracking_enabled']){
	Display::display_warning_message(get_lang('TrackingDisabled'));
	Display::display_footer();
	exit;
}


$strCourse  = get_lang('Courses');
$strUsers = get_lang('Users');

$myTools['action=recentlogins'] = get_lang('Logins');
$myTools['action=logins&type=month'] = get_lang('Logins').'/'.get_lang('PeriodMonth');
$myTools['action=logins&type=day'] = get_lang('Logins').'/'.get_lang('PeriodDay');
$myTools['action=logins&type=hour'] = get_lang('Logins').'/'.get_lang('PeriodHour');
$myTools['action=courses'] = get_lang('CountCours');
$myTools['action=coursebylanguage'] = get_lang('CountCourseByLanguage');
$myTools['action=tools'] = get_lang('PlatformToolAccess');
$myTools['action=users'] = get_lang('CountUsers');
$myTools['action=pictures'] = get_lang('UserPicture');
$myTools['action=courselastvisit'] = get_lang('LastAccess');

$total_tag_cnt=count($myTools);
$width_percent=round((100-$total_tag_cnt)/($total_tag_cnt+2))."%";

$strAction = (isset($_GET['action'])?getgpc('action','G'):'recentlogins');

if (isset($_GET['type'])) {
	$strType = getgpc('type');
}
$strActionType =(isset($strType)?'action='.$strAction. '&type='. $strType:'action='.$strAction);
$course_categories = statistics::get_course_categories();

echo '<table width="100%" style="margin:0px" height="24"><tr><td align="center"><table width="95%" class="tabTable"><tr>' . "\n";
echo '<td width="'.$width_percent.'" class="tabOther"  height="24">&nbsp;</td>' . "\n";
foreach($myTools as $key => $value)
{
	$strClass = $strActionType == $key ? 'tabSelected' : 'tabUnSelected';
	echo '<td width="'.$width_percent.'" class="' . $strClass . '"><a href="'.$_SERVER['PHP_SELF'].'?' . $key . '">' . $value . "</a></td>\n";
	echo '<td width="1%" class="tabOther">&nbsp;</td>' . "\n";
}
echo '<td class="tabOther">&nbsp;</td>' . "\n";
echo '</tr></table></td></tr></table><br>' . "\n";
//echo '<br/>';

echo "<center>";
//switch($_GET['action'])
switch($strAction)
{
	case 'courses':
		foreach($course_categories as $cate_id => $name)
		{
			$courses[$name] = statistics::count_courses($cate_id);
		}

		echo "<table><tr valign='middle'><td>";
		//display_stat_chart(get_lang('CountCours'),$courses,'Bar2D');
		echo "</td><td>";
		statistics::print_stats(get_lang('CountCours'),$courses,true,false,true);
		echo "</td></tr></table>";
		break;
	case 'users':
		echo "<table><tr valign='middle'><td>";
		display_stat_chart(
		get_lang('NumberOfUsers'),
		array(
		get_lang('Teachers') => statistics::count_users(1,null,getgpc('count_invisible_courses','G')),
		get_lang('Students') => statistics::count_users(5,null,getgpc('count_invisible_courses','G'))
		),'Pie3D');
		echo "</td><td>";
		statistics::print_stats(
		get_lang('NumberOfUsers'),
		array(
		get_lang('Teachers') => statistics::count_users(1,null,getgpc('count_invisible_courses','G')),
		get_lang('Students') => statistics::count_users(5,null,getgpc('count_invisible_courses','G'))
		)
		);
		echo "</td></tr></table>";
		foreach($course_categories as $code => $name)
		{
			$name = str_replace(get_lang('Department'),"",$name);
			$teachers[$name] = statistics::count_users(1,$code,getgpc('count_invisible_courses','G'));
			$students[$name] = statistics::count_users(5,$code,getgpc('count_invisible_courses','G'));
		}

		echo "<table><tr valign='middle'><td>";
		//display_stat_chart(get_lang('Teachers'),$teachers,'Bar2D');//Column3D
		echo "</td><td>";
		statistics::print_stats(get_lang('Teachers'),$teachers,true,false,true);
		echo "</td></tr></table>";

		echo "<table><tr valign='middle'><td>";
		//display_stat_chart(get_lang('Students'),$students,'Bar2D');//Column3D
		echo "</td><td>";
		statistics::print_stats(get_lang('Students'),$students,true,false,true);
		echo "</td></tr></table>";

		break;

	case 'coursebylanguage':
		echo "<table><tr valign='middle'><td>";
		print_course_by_language_stats();
		echo "</td><td>";
		statistics::print_course_by_language_stats();
		echo "</td></tr></table>";
		break;

	case 'logins':
		echo "<table><tr valign='middle'><td>";
		print_login_stats(getgpc('type','G'));
		echo "</td><td>";
		statistics::print_login_stats(getgpc('type','G'));
		echo "</td></tr></table>";
		break;


	case 'courselastvisit':
		statistics::print_course_last_visit();
		break;

	case 'recentlogins':
		echo "<table><tr valign='middle'><td>";
		print_recent_login_stats();
		echo "</td><td>";
		statistics::print_recent_login_stats();
		echo "</td></tr></table>";
		break;

	case 'pictures':
		echo "<table><tr valign='middle'><td>";
		print_user_pictures_stats();
		echo "</td><td>";
		statistics::print_user_pictures_stats();
		echo "</td></tr></table>";
		break;
}
echo "</center>";

function print_user_pictures_stats()
{
	$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	$sql = "SELECT COUNT(*) AS n FROM $user_table WHERE picture_uri IS NULL OR LENGTH(picture_uri) = 0";
	$res = api_sql_query($sql,__FILE__,__LINE__);
	$count1 = mysql_fetch_object($res);
	$sql = "SELECT COUNT(*) AS n FROM $user_table WHERE picture_uri IS NOT NULL AND LENGTH(picture_uri) > 0";
	$res = api_sql_query($sql,__FILE__,__LINE__);
	$count2 = mysql_fetch_object($res);
	$result[get_lang('NoPic')] = $count1->n;
	$result[get_lang('YesPic')] = $count2->n;
	display_stat_chart(get_lang('CountUsers').' ('.get_lang('UserPicture').')',$result,'Pie3D',450,400);
}


function print_recent_login_stats()
{
	$total_logins = array();
	$table = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_LOGIN);
	$sql[get_lang('Thisday')] = "SELECT count(login_user_id) AS number FROM $table WHERE DATE_ADD(login_date, INTERVAL 1 DAY) >= NOW()";
	$sql[get_lang('Last7days')] = "SELECT count(login_user_id) AS number  FROM $table WHERE DATE_ADD(login_date, INTERVAL 7 DAY) >= NOW()";
	$sql[get_lang('Last10days')] = "SELECT count(login_user_id) AS number  FROM $table WHERE DATE_ADD(login_date, INTERVAL 10 DAY) >= NOW()";
	$sql[get_lang('Last14days')] = "SELECT count(login_user_id) AS number  FROM $table WHERE DATE_ADD(login_date, INTERVAL 14 DAY) >= NOW()";
	$sql[get_lang('Last31days')] = "SELECT count(login_user_id) AS number  FROM $table WHERE DATE_ADD(login_date, INTERVAL 31 DAY) >= NOW()";
	$sql[get_lang('Total')] = "SELECT count(login_user_id) AS number  FROM $table";
	foreach($sql as $index => $query)
	{
		$res = api_sql_query($query,__FILE__,__LINE__);
		$obj = mysql_fetch_object($res);
		$total_logins[$index] = $obj->number;
	}
	display_stat_chart(get_lang('Logins'),$total_logins,'Bar2D');
}



function print_login_stats($type)
{
	$table = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_LOGIN);
	switch($type)
	{
		case 'month':
			$period = get_lang('PeriodMonth');
			$sql = "SELECT DATE_FORMAT( login_date, '%Y-%m' ) AS stat_date , count( login_id ) AS number_of_logins FROM ".$table." GROUP BY stat_date ORDER BY login_date ";
			break;
		case 'hour':
			$period = get_lang('PeriodHour');
			$sql = "SELECT DATE_FORMAT( login_date, '%H' ) AS stat_date , count( login_id ) AS number_of_logins FROM ".$table." GROUP BY stat_date ORDER BY stat_date ";
			break;
		case 'day':
			$period = get_lang('PeriodDay');
			$sql = "SELECT DATE_FORMAT( login_date, '%W' ) AS stat_date , count( login_id ) AS number_of_logins FROM ".$table." GROUP BY stat_date ORDER BY DATE_FORMAT( login_date, '%w' ) ";
			break;
	}
	$res = api_sql_query($sql,__FILE__,__LINE__);
	$result = array();
	while($obj = mysql_fetch_object($res))
	{
		$result[$obj->stat_date] = $obj->number_of_logins;
	}
	display_stat_chart(get_lang('Logins').' ('.$period.')',$result);
}

function print_course_by_language_stats()
{
	$table = Database::get_main_table(TABLE_MAIN_COURSE);
	$sql = "SELECT course_language, count( code ) AS number_of_courses FROM $table GROUP BY course_language ";
	$res = api_sql_query($sql,__FILE__,__LINE__);
	$result = array();
	while($obj = mysql_fetch_object($res))
	{
		$result[$obj->course_language] = $obj->number_of_courses;
	}
	display_stat_chart(get_lang('CountCourseByLanguage'),$result);
}

function display_stat_chart($title='',$data=array(),$chart_type="Column3D",$width=450,$height=400){
	echo '<script language="javascript" src="'.api_get_path(WEB_LIB_PATH).'FusionCharts/Charts/FusionCharts.js"></script>
	';
	$FC = new FusionCharts($chart_type,$width,$height);
	$FC->setSWFPath(api_get_path(WEB_LIB_PATH)."FusionCharts/Charts/");

	$strParam="caption=".$title."';decimalPrecision=0;formatNumberScale=1";
	$FC->setChartParams($strParam);

	if(is_array($data) && count($data)>0){
		foreach($data as $key=>$val){
			$FC->addChartData($val,"name=".$key);
		}
		$FC->renderChart();
	}else{
		unset($FC);
	}
}

Display::display_footer();
?>