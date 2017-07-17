<?php
define('SMS_TYPE_PLATFORM','platform');
define('SMS_TUPE_PRIVATE','private_sms');
class SMSManager{

	/**
	 * 新增SMS
	 *
	 * @param unknown_type $sender
	 * @param unknown_type $content
	 * @param unknown_type $send_time
	 * @param unknown_type $receivers
	 * @param unknown_type $is_to_all
	 * @param unknown_type $type
	 * @return unknown
	 */
	function create_sms($sender,$content,$send_time,$receivers=array(),$is_to_all=false,$type=SMS_TYPE_PLATFORM){
		$table_sys_sms= Database::get_main_table(TABLE_MAIN_SYS_SMS);
		$table_sys_sms_received= Database::get_main_table(TABLE_MAIN_SYS_SMS_RECEIVED);

		$sql_data=array("sender"=>$sender,"content"=>$content,"send_time"=>$send_time,
			"sms_type"=>$type,"is_to_all"=>($is_to_all?"1":"0"),"creation_time"=>date("Y-m-d H:i:s"));
		$sql=Database::sql_select($table_sys_sms,$sql_data);

		$result = api_sql_query($sql);
		$last_id=($result?mysql_insert_id():false);

		if($is_to_all==false && $last_id && is_array($receivers)){
			foreach($receivers as $key){
				$sql_data=array("user_id"=>$key,"sms_id"=>$last_id,"is_read"=>"0");
				$sql=Database::sql_select($table_sys_sms_received,$sql_data);
				api_sql_query($sql, __FILE__, __LINE__);
			}
		}

		return ($result?mysql_insert_id():false);

	}

	/**
	 * 删除SMS
	 *
	 * @param unknown_type $sms_id
	 * @param unknown_type $user_id
	 * @param unknown_type $is_to_all
	 * @return unknown
	 */
	function del_sms($user_id,$sms_id,$is_to_all=false){
		//$table_sys_sms= Database::get_main_table(TABLE_MAIN_SYS_SMS);
		$table_sys_sms_received= Database::get_main_table(TABLE_MAIN_SYS_SMS_RECEIVED);

		if(!$is_to_all){
			$sql="UPDATE ".$table_sys_sms_received." SET is_deleted=1 WHERE user_id='".Database::escape_string($user_id)."' AND
			sms_id='".Database::escape_string($sms_id)."'";
			$result = api_sql_query($sql, __FILE__, __LINE__);
		}else{
			$sql="DELETE FROM ".$table_sys_sms_received." WHERE user_id='".Database::escape_string($user_id)."' AND
			sms_id='".Database::escape_string($sms_id)."'";
			$result = api_sql_query($sql, __FILE__, __LINE__);

			$sql="INSERT INTO ".$table_sys_sms_received." SET is_deleted=1,is_read=1,user_id='".Database::escape_string($user_id)."',
			sms_id='".Database::escape_string($sms_id)."'";
			$result = api_sql_query($sql, __FILE__, __LINE__);
		}


		return true;
	}

	function get_my_sms_list($user_id,$condition=""){
		$table_sys_sms= Database::get_main_table(TABLE_MAIN_SYS_SMS);
		$table_sys_sms_received= Database::get_main_table(TABLE_MAIN_SYS_SMS_RECEIVED);

		$return_array[]=array();

		$sql="SELECT * FROM ".$table_sys_sms." as t1,".$table_sys_sms_received
		." as t2 WHERE t2.user_id='".Database::escape_string($id)
		."' AND t1.id=t2.sms_id ".$condition;
		$result = api_sql_query($sql);
		while($row=Database::fetch_array($result,'ASSOC')){
			$return_array[]=$row;
		}
		return $return_array;
	}


	function get_sms_list($condition=""){
		$table_sys_sms= Database::get_main_table(TABLE_MAIN_SYS_SMS);
		$return_array[]=array();

		$sql="SELECT * FROM ".$table_sys_sms." as t1 ".$condition;
		$result = api_sql_query($sql, __FILE__, __LINE__);
		while($row=Database::fetch_array($result,'ASSOC')){
			$return_array[]=$row;
		}
		return $return_array;
	}

	/**
	 * 获取最后的一条末读信息
	 *
	 * @param unknown_type $user_id
	 * @return unknown
	 */
	function has_not_read_sms($user_id=NULL){
		$table_user=Database::get_main_table(TABLE_MAIN_USER);
		$table_sys_sms= Database::get_main_table(TABLE_MAIN_SYS_SMS);
		$table_sys_sms_received= Database::get_main_table(TABLE_MAIN_SYS_SMS_RECEIVED);

		$sql="SELECT t1.id,t1.is_to_all,t1.sender,t1.send_time,t1.content FROM ".$table_sys_sms
		." as t1 where is_to_all=1 AND t1.id not in(select distinct(sms_id) from "
		.$table_sys_sms_received." where is_read=1 AND user_id='".$user_id."')
			UNION      
			SELECT t1.id,t1.is_to_all,t1.sender,t1.send_time,t1.content FROM ".$table_sys_sms
		." as t1,".$table_sys_sms_received." as t2 where is_to_all=0 AND t2.is_read=0 AND
			t2.is_deleted=0 AND t1.id =t2.sms_id AND t2.user_id='".$user_id."'";
		//echo $sql;
		$sql="SELECT * FROM (".$sql.") t ORDER BY t.send_time limit 1";
		$rs=api_sql_query($sql, __FILE__, __LINE__);
		if($rs){
			return api_store_result($rs);
		}else{
			return false;
		}
	}

	/**
	 * 得到末读取信息的总数
	 *
	 * @param unknown_type $user_id
	 * @return unknown
	 */
	function get_not_read_sms_count($user_id=NULL){
		$table_user=Database::get_main_table(TABLE_MAIN_USER);
		$table_sys_sms= Database::get_main_table(TABLE_MAIN_SYS_SMS);
		$table_sys_sms_received= Database::get_main_table(TABLE_MAIN_SYS_SMS_RECEIVED);

		$sql="SELECT t1.id FROM ".$table_sys_sms
		." as t1 where is_to_all=1 AND t1.id not in(select distinct(sms_id) from "
		.$table_sys_sms_received." where is_read=1 AND user_id='".$user_id."')
			UNION      
			SELECT t1.id FROM ".$table_sys_sms
		." as t1,".$table_sys_sms_received." as t2 where is_to_all=0 AND t2.is_read=0 AND
			t2.is_deleted=0 AND t1.id =t2.sms_id AND t2.user_id='".$user_id."'";
		//echo $sql;
		//$sql="SELECT * FROM (".$sql.") t ORDER BY t.send_time limit 1";
		$rs=api_sql_query($sql, __FILE__, __LINE__);
		return Database::num_rows($rs);		
	}


	/**
	 * 未读信息列表
	 *
	 * @param unknown_type $user_id
	 * @param unknown_type $start
	 */
	function display_sms_list_notread($user_id=NULL,$start=0)
	{
		$table_user=Database::get_main_table(TABLE_MAIN_USER);
		$table_sys_sms= Database::get_main_table(TABLE_MAIN_SYS_SMS);
		$table_sys_sms_received= Database::get_main_table(TABLE_MAIN_SYS_SMS_RECEIVED);
		$all_sms_array=array();

		//$all_sms_array=SMSManager::get_sms_list();
		//对于所送给所有用户的信息
		$sql="SELECT t1.id,t1.is_to_all,t1.sender,t1.send_time,t1.content FROM ".$table_sys_sms
		." as t1 where is_to_all=1 AND t1.id not in(select distinct(sms_id) from "
		.$table_sys_sms_received." where is_read=1 AND user_id='".$user_id."')
			UNION      
			SELECT t1.id,t1.is_to_all,t1.sender,t1.send_time,t1.content FROM ".$table_sys_sms
		." as t1,".$table_sys_sms_received." as t2 where is_to_all=0 AND t2.is_read=0 AND
			t2.is_deleted=0 AND t1.id =t2.sms_id AND t2.user_id='".$user_id."'";
			
		//echo $sql;
		$sql_count="SELECT COUNT(*) FROM (".$sql.") t";
		$total=Database::get_scalar_value($sql_count);
		//echo $total;

		$sql="SELECT t.*,t3.user_id,t3.username,t3.firstname FROM (".$sql.") t left join ".$table_user." as t3 ON t.sender=t3.user_id";

		//echo $sql;
		if(!isset($start) || $start == 0)
		{
			$sql .= " ORDER BY send_time DESC LIMIT 0," . NUMBER_PAGE;
		}
		else
		{
			$sql .= " ORDER BY send_time DESC LIMIT " . $start*(int)NUMBER_PAGE . "," . NUMBER_PAGE;
		}

		$rs=api_sql_query($sql, __FILE__, __LINE__);
		while($row=Database::fetch_array($rs,'ASSOC')){
			$all_sms_array[]=$row;
		}

		if ($total)
		{
			$html = "<div class='systemAnnouncementList'>
					 <a name='top'></a>
					 <table width='100%'>
					 <tr><td>
					";	
			$html.=display_nav_page_bar($total,$start,'&action=notread');
			$html .= "</td></tr> </table> <table class='data_table'>
					<tr align=center><th></th><th><B>".get_lang('Sender')
			."</B></th><th>".get_lang('Content')."</th><th><B>"
			.get_lang('SendTime')."</B></th><th><B>".get_lang("Actions")."</B></th></tr>";

			$idx=0;
			foreach($all_sms_array as $sms)
			{
				$class_style=($idx%2==0?'row_even':'row_odd');
				$html .= "<tr class='".$class_style."'>
				<td width='5%'><a name='".$sms['id']."'></a>" . Display::return_icon('valves.gif') . "</td>
						<td>".$sms['firstname']."</td>";

				$html.="<td align='left'>".($sms['is_to_all']==2?Display::return_icon('add.gif','点击展开消息记录',
				array('onclick'=>"show_msg(".$sms['id'].");",'style'=>'cursor:hand;','id'=>'img'.$sms['id'])):'')."
						<a href='?todo=view&id=".$sms['id']."' target='_blank'>".$sms['content']."</a></td>";

				$html.="<td width='15%' align=center>".$sms['send_time']."</td>";
				$html.="<td align=center><a href='".api_get_path(REL_CODE_PATH)."sms/index.php?action=markread&sms_id=".$sms['id']."&is_to_all=".$sms['is_to_all']."'>".get_lang("MarkRead")."</a></td>";
				$html.="</tr>";
				$html.="<tr id=\"tr".$sms['id']."\" style=\"display:none;\" class=\"TableData\"><td colspan=\"6\"><div id=\"msg"
				.$sms['id']."\" class=\"msg\"></div></td></tr>";
				$idx++;
			}
			$html .= "</table>
					 <table width='100%'>
					 <tr><td>
					 ";			
			//$html .= SystemAnnouncementManager::display_fleche($user_id, $total);
			$html.=display_nav_page_bar($total,$start,'&action=notread');
			$html .= "</td></tr>
					 </table>
					 </div>
					 ";

			echo $html;
		}
		else
		{
			Display :: display_normal_message(get_lang('NoSMSs'));
		}
	}


	/**
	 * 已接收信息列表
	 *
	 * @param unknown_type $user_id
	 * @param unknown_type $start
	 */
	function display_sms_list_received($user_id=Null,$start=0)
	{
		$table_user=Database::get_main_table(TABLE_MAIN_USER);
		$table_sys_sms= Database::get_main_table(TABLE_MAIN_SYS_SMS);
		$table_sys_sms_received= Database::get_main_table(TABLE_MAIN_SYS_SMS_RECEIVED);
		$all_sms_array=array();

		//$all_sms_array=SMSManager::get_sms_list();
		//对于所送给所有用户的信息
		$sql="SELECT t1.id,t1.is_to_all,t1.sender,t1.send_time,t1.content FROM ".$table_sys_sms
		." as t1 where is_to_all=1 AND t1.send_time<=NOW()
			UNION      
			SELECT t1.id,t1.is_to_all,t1.sender,t1.send_time,t1.content FROM ".$table_sys_sms
		." as t1,".$table_sys_sms_received." as t2 where is_to_all=0 AND  t1.send_time<=NOW() AND
			t2.is_deleted=0 AND t1.id =t2.sms_id AND t2.user_id='".api_get_user_id()."'";
			
		$sql_count="SELECT COUNT(*) FROM (".$sql.") t";
		$total=Database::get_scalar_value($sql_count);
		//echo $total;

		$sql="SELECT t.*,t3.user_id,t3.username,t3.firstname FROM (".$sql.") t left join ".$table_user." as t3 ON t.sender=t3.user_id";

		//echo $sql;
		if(!isset($start) || $start == 0)
		{
			$sql .= " ORDER BY id DESC LIMIT 0," . NUMBER_PAGE;
		}
		else
		{
			$sql .= " ORDER BY id DESC LIMIT " . $start*(int)NUMBER_PAGE . "," . NUMBER_PAGE;
		}

		$rs=api_sql_query($sql, __FILE__, __LINE__);
		while($row=Database::fetch_array($rs,'ASSOC')){
			$all_sms_array[]=$row;
		}

		if ($total)
		{
			$html = "<div class='systemAnnouncementList'>
					 <a name='top'></a>
					 <table width='100%'>
					 <tr><td>
					";	
			$html.=display_nav_page_bar($total,$start,'&action=received');
			$html .= "</td></tr> </table> <table class='data_table'>
					<tr align=center><th></th><th><B>".get_lang('Sender')
			."</B></th><th>".get_lang('Content')."</th><th><B>"
			.get_lang('SendTime')."</B></th><th><B>".get_lang("Actions")."</B></th></tr>";

			$idx=0;
			foreach($all_sms_array as $sms)
			{
				$class_style=($idx%2==0?'row_even':'row_odd');
				$html .= "<tr class='".$class_style."'>
				<td width='5%'><a name='".$sms['id']."'></a>" . Display::return_icon('valves.gif') . "</td>
						<td>".$sms['firstname']."</td>";
				$html.="<td align='left'>".($sms['is_to_all']==2?Display::return_icon('add.gif','点击展开消息记录',
				array('onclick'=>"show_msg(".$sms['id'].");",'style'=>'cursor:hand;','id'=>'img'.$sms['id'])):'')."
						<a href='?todo=view&id=".$sms['id']."' target='_blank'>".$sms['content']."</a></td>";
				$html.="<td width='15%' align=center>".$sms['send_time']."</td>";
				if($sms['is_to_all']==0){
					$html.="<td align=center><a href='send.php?send_to_user=".$sms['sender']."' target='_blank'>".get_lang('Feedback')."</a></td>";
				}else{
					$html.="<td></td>";
				}
				$html.="</tr>";
				$html.="<tr id=\"tr".$sms['id']."\" style=\"display:none;\" class=\"TableData\"><td colspan=\"6\"><div id=\"msg"
				.$sms['id']."\" class=\"msg\"></div></td></tr>";
				$idx++;
			}
			$html .= "</table>
					 <table width='100%'>
					 <tr><td>
					 ";			
			//$html .= SystemAnnouncementManager::display_fleche($user_id, $total);
			$html.=display_nav_page_bar($total,$start,'&action=received');
			$html .= "</td></tr>
					 </table>
					 </div>
					 ";

			echo $html;
			echo '<script>window.setTimeout("this.location.reload();",60000);</script>';

		}
		else
		{
			Display :: display_normal_message(get_lang('NoSMSs'));
		}
	}


	/**
	 * 显示已发送SMS列表
	 *
	 * @param unknown_type $user_id
	 * @param unknown_type $start
	 */
	function display_sms_list_send($user_id=Null,$start=0)
	{
		$table_user=Database::get_main_table(TABLE_MAIN_USER);
		$table_sys_sms= Database::get_main_table(TABLE_MAIN_SYS_SMS);
		$table_sys_sms_received= Database::get_main_table(TABLE_MAIN_SYS_SMS_RECEIVED);
		$all_sms_array=array();

		//$all_sms_array=SMSManager::get_sms_list();
		//对于所送给所有用户的信息
		$sql="SELECT t1.id,t1.is_to_all,t1.sender,t1.send_time,t1.content FROM ".$table_sys_sms
		." as t1 where t1.sender='".api_get_user_id()."'";
			
		$sql_count="SELECT COUNT(*) FROM (".$sql.") t";
		$total=Database::get_scalar_value($sql_count);
		//echo $total;

		$sql="SELECT t.*,t3.user_id,t3.username,t3.firstname FROM (".$sql.") t left join ".$table_user." as t3 ON t.sender=t3.user_id";

		//echo $sql;
		if(!isset($start) || $start == 0)
		{
			$sql .= " ORDER BY id DESC LIMIT 0," . NUMBER_PAGE;
		}
		else
		{
			$sql .= " ORDER BY id DESC LIMIT " . $start*(int)NUMBER_PAGE . "," . NUMBER_PAGE;
		}

		$rs=api_sql_query($sql, __FILE__, __LINE__);
		while($row=Database::fetch_array($rs,'ASSOC')){
			$all_sms_array[]=$row;
		}


		if ($total)
		{
			$html = "<div class='systemAnnouncementList'>
					 <a name='top'></a>
					 <table width='100%'>
					 <tr><td>
					";	
			$html.=display_nav_page_bar($total,$start,'&action=sent');
			$html .= "</td></tr> </table> <table class='data_table'>
					<tr align=center><th></th><th><B>".get_lang('Sender')
			."</B></th><th>".get_lang('Content')."</th><th><B>"
			.get_lang('SendTime')."</B></th></tr>";

			$idx=0;
			foreach($all_sms_array as $sms)
			{
				$class_style=($idx%2==0?'row_even':'row_odd');
				$html .= "<tr class='".$class_style."'>
				<td width='5%'><a name='".$sms['id']."'></a>" . Display::return_icon('valves.gif') . "</td>
						<td>".$sms['firstname']."</td>";
				$html.="<td align='left'>".($sms['is_to_all']==2?Display::return_icon('add.gif','点击展开消息记录',
				array('onclick'=>"show_msg(".$sms['id'].");",'style'=>'cursor:hand;','id'=>'img'.$sms['id'])):'')."
						<a href='?todo=view&id=".$sms['id']."' target='_blank'>".$sms['content']."</a></td>";
				$html.="<td width='15%' align=center>".$sms['send_time']."</td></tr>";
				$html.="<tr id=\"tr".$sms['id']."\" style=\"display:none;\" class=\"TableData\"><td colspan=\"6\"><div id=\"msg"
				.$sms['id']."\" class=\"msg\"></div></td></tr>";
				$idx++;
			}
			$html .= "</table>
					 <table width='100%'>
					 <tr><td>
					 ";			
			//$html .= SystemAnnouncementManager::display_fleche($user_id, $total);
			$html.=display_nav_page_bar($total,$start,'&action=sent');
			$html .= "</td></tr>
					 </table>
					 </div>
					 ";

			echo $html;
			echo '<script>window.setTimeout("this.location.reload();",60000);</script>';

		}
		else
		{
			Display :: display_normal_message(get_lang('NoSMSs'));
		}
	}

	function get_sms_info($sms_id){
		$table_user=Database::get_main_table(TABLE_MAIN_USER);
		$table_sys_sms= Database::get_main_table(TABLE_MAIN_SYS_SMS);
		$sql="SELECT t1.*,t2.username,t2.firstname,t2.status FROM ".$table_sys_sms
		." as t1 left join ".$table_user." as t2 ON t1.sender=t2.user_id WHERE t1.id='"
		.Database::escape_string($sms_id)."'";
		//echo $sql;
		$rs=api_sql_query($sql, __FILE__, __LINE__);
		return $rs?Database::fetch_array($rs,'ASSOC'):false;
	}

	function is_sms_read($user_id,$sms_id,$is_to_all=false){
		$table_user=Database::get_main_table(TABLE_MAIN_USER);
		$table_sys_sms= Database::get_main_table(TABLE_MAIN_SYS_SMS);
		$table_sys_sms_received= Database::get_main_table(TABLE_MAIN_SYS_SMS_RECEIVED);
		if(!$is_to_all){
			$sql="SELECT is_read from ".$table_sys_sms_received."  WHERE sms_id='"
			.Database::escape_string($sms_id)."' AND user_id='"
			.Database::escape_string($user_id)."'";
			return (Database::get_scalar_value($sql)==1?true:false);
		}else{
			$sql="SELECT * from ".$table_sys_sms_received."  WHERE sms_id='"
			.Database::escape_string($sms_id)."' AND user_id='"
			.Database::escape_string($user_id)."'";
			return (Database::if_row_exists($sql)?true:false);
		}
	}

	/**
	 * 点击”我知道了“后处理动作，即”不再提醒“
	 *
	 * @param unknown_type $user_id
	 * @param unknown_type $sms_id
	 * @param unknown_type $is_to_all
	 * @return unknown
	 */
	function read_sms($user_id,$sms_id,$is_to_all){
		$table_sys_sms_received= Database::get_main_table(TABLE_MAIN_SYS_SMS_RECEIVED);
		if($is_to_all=='0'){
			$sql="UPDATE ".$table_sys_sms_received." SET is_read=1,read_time=NOW() WHERE sms_id='"
			.Database::escape_string($sms_id)."' AND user_id='"
			.Database::escape_string($user_id)."'";
		}
		if($is_to_all=='1'){
			$sql="INSERT IGNORE INTO ".$table_sys_sms_received." SET is_read=1,read_time=NOW(),sms_id='"
			.Database::escape_string($sms_id)."' , user_id='"
			.Database::escape_string($user_id)."'";
		}
		$rs=api_sql_query($sql, __FILE__, __LINE__);
		return true;
	}

	function get_user_list($condition=''){
		$table_user = Database :: get_main_table(TABLE_MAIN_USER);
		$sql = "SELECT user_id,lastname,firstname FROM $table_user WHERE 1=1 ".$condition;
		$res = api_sql_query($sql,__FILE__,__LINE__);
		$users = array();
		while($obj = mysql_fetch_object($res))
		{
			$users[$obj->user_id] = $obj->firstname.' '.$obj->lastname;
		}
		return $users;
	}
}
?>