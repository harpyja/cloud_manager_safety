<?php
$language_file = array ('admin');
$cidReset = true;
require_once ('../inc/global.inc.php');
$lib_path=SYS_ROOT."main/inc/lib/";
require_once($lib_path.'dept.lib.inc.php');

/*require_once ("../inc/conf/configuration.php");
require_once($lib_path.'main_api.lib.php');
require_once($lib_path.'database.lib.php');
//连接到主数据库
$zlms_database_connection = @mysql_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'])
or die (mysql_error());
mysql_query("set names utf8;");
$selectResult = mysql_select_db($_configuration['main_database'],$zlms_database_connection)
or  die ('<center><h2>ERROR ! Connect the Main Database Failed!</h2></center>');

api_session_start(TRUE);*/
//api_block_anonymous_users();
if (!(isset ($_user['user_id']) && $_user['user_id']))
{
	//exit;
}
//api_protect_admin_script ();

$action=getgpc('action');
if(isset($action)){
	switch($action){
		case 'get_sub_dept':
			header("Cache-Control: must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header("Expires: ".gmdate("D, d M Y H:i:s", mktime(date("H")-2, date("i"), date("s"), date("m"), date("d"), date("Y")))." GMT");
			header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
			header("Content-Type: text/html;charset=".SYSTEM_CHARSET);			
			sleep(1);			
			
			$pid=(isset($_REQUEST['pid'])?intval(getgpc('pid')):'0');
			$deptObj = new DeptManager ( );
			$sub_depts=$deptObj->get_sub_dept($pid,true);
			if($sub_depts && is_array($sub_depts)){
				foreach($sub_depts as $dept_info){
					if($dept_info['pid']==0){
						
						/*echo "\t".'{ attributes: { id : "'.$dept_info['id'].'"},'
						.($dept_info['has_sub_depts']=='true'?' state: "closed",':"").' data: {title:"'.$dept_info['dept_name'].'",href:"dept_list.php?pid='.$dept_info['id'].'",target:"_blank"  },';*/		
						//echo "\t".'{ attributes: { id : "'.$dept_info['id'].'"},'
						//.($dept_info['has_sub_depts']=='true'?' state: "closed",':"").' data: "'.$dept_info['dept_name'].'"},';		
						
					}else{
					
						$data.="{id:'". $dept_info['id'] ."',pid:'".$dept_info['pid']."',name:'".$dept_info['dept_name']."',url:'dept_list.php?pid=".$dept_info['id']."'".($dept_info['has_sub_depts']=='true'?',hasChild: true':"")."},";								
						
					}	
				}
			}
			echo '['.substr($data, 0, -1).']'."\n";
			exit;
			break;
	}
}


?>
