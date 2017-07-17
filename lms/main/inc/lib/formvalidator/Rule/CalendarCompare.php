<?php
/*
 ==============================================================================
 liyu:20090802 日期比较
 ==============================================================================
 */
require_once 'HTML/QuickForm/Rule/Compare.php';
/**
 * QuickForm rule to compare 2 dates
 */
class HTML_QuickForm_Rule_CalendarCompare extends HTML_QuickForm_Rule_Compare
{
	/**
	 * Validate 2 dates
	 * @param array $values Array with the 2 dates. Each element in this array
	 * should be an array width keys  F (month), d (day) and Y (year)
	 * @param string $operator The operator to use (default '==')
	 * @return boolean True if the 2 given dates match the operator
	 */
	function validate($values, $operator = null)
	{
		//liyu: 20090802
		$date1 = $values[0];
		$date2 = $values[1];
		if($date1 && $date2)
		{
			if(preg_match('/(\d+-\d+-\d+\s+\d+:\d+:\d+)$/',$date1) && preg_match('/(\d+-\d+-\d+\s+\d+:\d+:\d+)$/',$date2)){
				$format='Y-m-d H:i:s';
			}
			if(preg_match('/(\d+-\d+-\d+)$/',$date1) && preg_match('/(\d+-\d+-\d+)$/',$date2)){
				$format='Y-m-d';
			}
				
			if($format=='Y-m-d')
			{
				list($year1,$month1,$day1)=explode('-',$date1);
				list($year2,$month2,$day2)=explode('-',$date2);
				$time1=mktime(0,0,0,$month1,$day1,$year1);
				$time2=mktime(0,0,0,$month2,$day2,$year2);
			}
			elseif($format=='Y-m-d H:i:s')
			{
				list($date_tmp,$time_tmp)=preg_split('/\s+/',$date1);
				list($year1,$month1,$day1)=explode('-',$date_tmp);
				list($hour1,$min1,$sec1)=explode(':',$time_tmp);
				$time1=mktime($hour1,$min1,$sec1,$month1,$day1,$year1);
				
				list($date_tmp,$time_tmp)=preg_split('/\s+/',$date2);
				list($year2,$month2,$day2)=explode('-',$date_tmp);
				list($hour2,$min2,$sec2)=explode(':',$time_tmp);
				$time2=mktime($hour2,$min2,$sec2,$month2,$day2,$year2);
			}
			
			return parent::validate(array($time1,$time2),$operator);
		}

		return false;
	}
}
?>