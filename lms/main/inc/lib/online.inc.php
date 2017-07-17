<?php 
/**
 * 写入在线用户表
 * @param unknown_type $uid
 * @param unknown_type $statistics_database
 */


function LoginCheck($uid) {
    global $_course;
    $online_table = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_ONLINE );
    if (! empty ( $uid )) {
        LoginDelete ( $uid );
        $login_ip = real_ip ();
        if ($_course) {
            $query = "INSERT IGNORE INTO " . $online_table . " (login_id,login_user_id,login_date,login_ip, course) VALUES ('" . session_id () . "',$uid,NOW(),'$login_ip', '" . $_course ['id'] . "')";
        } else {
            $query = "INSERT IGNORE INTO " . $online_table . " (login_id,login_user_id,login_date,login_ip) VALUES ('" . session_id () . "',$uid,NOW(),'$login_ip')";
            $sqlb = "show tables like 'vmtotal'"; 
            $res = Database::getval( $sqlb, __FILE__, __LINE__ );
            if($res){
                $sql = "select `vmid`,`addres` FROM  `vmtotal` where `user_id`= '{$uid}' and  `manage`='0'"; 
                $res = api_sql_query ( $sql, __FILE__, __LINE__ );
                $vm= array (); 
                while ($vm = Database::fetch_row ( $res)) { 
                    $vms [] = $vm; 
                }
                foreach ( $vms as $k1 => $v1){ 
                        $vmid = $v1[0];
                        $vmaddres = $v1[1];
                        if($vmid && $vmaddres){
                            $platforms=file_get_contents(URL_ROOT.'/www'.URL_APPEDND.'/storage/DATA/platform.conf');
                            $platform_array=explode(':',$platforms);
                            $platform=intval(trim($platform_array[1]));

                            if($platform>3){
                                $output = exec("sudo -u root /usr/bin/ssh root@$vmaddres /sbin/cloudvmstop.sh $vmid");
                                $sqla = "delete  FROM  vmtotal where user_id= '{$uid}'";
                                api_sql_query ( $sqla, __FILE__, __LINE__ );
                            } 
                        }
                } 
            }
        }
        @api_sql_query ( $query, __FILE__, __LINE__ );
    }
}

/**
 * 删除在线用户表中的一条记录
 * @param unknown_type $user_id
 */
function LoginDelete($user_id) {
	$online_table = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_ONLINE );
	$query = "DELETE FROM " . $online_table . " WHERE login_user_id = '" . Database::escape_string ( $user_id ) . "'";
	@api_sql_query ( $query, __FILE__, __LINE__ );
}

/**
 * 在线用户列表
 * @todo remove parameter $statistics_database which is no longer necessary
 */
function WhoIsOnline($uid, $statistics_database, $valid) {
	global $restrict_org_id;
	$track_online_table = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_ONLINE );
	$user_table = Database::get_main_table ( TABLE_MAIN_USER );
	$view_user_dept = Database::get_main_table ( VIEW_USER_DEPT );
	
	if (api_is_platform_admin ()) {
		$query = "SELECT login_user_id,login_date,login_id,t2.* FROM " . $track_online_table . " AS t1," .
            $view_user_dept . " AS t2 WHERE t1.login_user_id=t2.user_id AND DATE_ADD(login_date,INTERVAL $valid MINUTE) >= NOW()  ";
	} else {
		$query = "SELECT login_user_id,login_date,login_id,t2.* FROM " . $track_online_table . " AS t1 LEFT JOIN
		$view_user_dept AS t2 ON t1.login_user_id=t2.user_id WHERE t2.org_id='" . $restrict_org_id . "'
				AND DATE_ADD(login_date,INTERVAL $valid MINUTE) >= NOW()";
	}
	//echo $query;
	$result = @api_sql_query ( $query, __FILE__, __LINE__ );
	return api_store_result ( $result );
}

function get_online_uesr_list($valid = 3) {
	global $restrict_org_id;
	$track_online_table = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_ONLINE );
	$query = "SELECT login_user_id,login_date FROM " . $track_online_table . " WHERE DATE_ADD(login_date,INTERVAL $valid MINUTE) >= NOW()  ";
	$result = Database::get_into_array2 ( $query, __FILE__, __LINE__ );
	return $result;
}
