<?php

define ( "DEPT_NO_BITS", 4 ); //自动生成的编号位数


class DeptManager {
	
	var $all_dept_tree;
	var $sub_dept_tree;
	var $sub_dept_ids;
	var $dept_path;
	var $dept_in_org;

	function __construct() {
		$this->all_dept_tree = array ();
		$this->sub_dept_tree = array ();
		$this->sub_dept_ids = array ();
		$this->dept_path = "";
		$this->dept_in_org = array ();
	
		//$this->init();
	}

	function init() {
		$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
		$sql = "SELECT * FROM " . $table_dept . " WHERE id='" . DEPT_TOP_ID . "'";
		if (Database::if_row_exists ( $sql ) == false) {
			$sql_data = array ('id' => DEPT_TOP_ID, "pid" => "0", "dept_sn" => "0", "dept_no" => "zlms_org", "dept_name" => api_get_setting ( "Institution" ), "enabled" => "1", "dept_pos" => 1, "dept_admin" => 1 );
			$sql = Database::sql_insert ( $table_dept, $sql_data );
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		} else {
			$sql_data = array ("pid" => "0", "dept_sn" => "0", "dept_no" => "zlms_org", "dept_name" => api_get_setting ( "Institution" ), "enabled" => "1", "dept_pos" => 1, "dept_admin" => 1 );
			$sql = Database::sql_update ( $table_dept, $sql_data, "id=" . DEPT_TOP_ID );
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		}
	}

	function get_all_dept_tree($is_contain_top_dept = false) {
		$data = cache ( CACHE_KEY_ADMIN_DEPT, '' );
		if ($data == NULL) {
			$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
			$sql = "SELECT * FROM " . $table_dept . " WHERE pid=0 ORDER BY dept_pos";
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			$has_children = Database::num_rows ( $res ) > 0;
			while ( $dept = Database::fetch_array ( $res, 'ASSOC' ) ) {
				if ($is_contain_top_dept) {
					$dept ['level'] = 1;
					$this->all_dept_tree [] = $dept;
				}
				if ($has_children) {
					$this->get_dept_trees ( $dept ['id'], 0 );
				}
			}
			$data = $this->all_dept_tree;
			cache ( CACHE_KEY_ADMIN_DEPT, $data );
		}
		return $data;
	}

	function get_top_dept() {
		$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
		$sql = "SELECT * FROM " . $table_dept . " WHERE pid=0 ORDER BY dept_pos";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		if ($dept = Database::fetch_array ( $res, 'ASSOC' )) {
			$top_dept = array (
					'id' => $dept ['id'], 
						'pid' => $dept ['pid'], 
						'dept_name' => $dept ['dept_name'], 
						'dept_no' => $dept ['dept_no'], 
						'level' => 1, 
						'enabled' => $dept ['enabled'], 
						'dept_pos' => $dept ['dept_pos'], 
						'dept_desc' => $dept ['dept_desc'], 
						'last_updated_date' => $dept ['last_updated_date'] );
			return $top_dept;
		}
		return false;
	}

	/**
	 * 递归得到部门列表
	 *
	 * @param unknown_type $parent_id
	 * @param unknown_type $level
	 */
	function get_dept_trees($parent_id = 0, $level = 0) {
		$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
		$sql = "SELECT * FROM " . $table_dept . " WHERE pid='" . $parent_id . "' ORDER BY dept_pos ASC";
		$level ++;
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		$has_children = Database::num_rows ( $res ) > 0;
		while ( $dept = Database::fetch_array ( $res, 'ASSOC' ) ) {
			$dept ['level'] = $level;
			$this->all_dept_tree [] = $dept;
			if ($has_children) {
				self::get_dept_trees ( $dept ['id'], $level );
			}
		}
		return $this->all_dept_tree;
	}

	function get_sub_dept_ids($parent_id = 0) {
		$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
		$sql = "SELECT * FROM " . $table_dept . " WHERE pid='" . $parent_id . "' ORDER BY dept_pos";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		$has_children = Database::num_rows ( $res ) > 0;
		while ( $dept = Database::fetch_array ( $res, 'ASSOC' ) ) {
			$this->sub_dept_ids [] = ($dept ['id']);
			if ($has_children) {
				self::get_sub_dept_ids ( $dept ['id'] );
			}
		}
		return $this->sub_dept_ids;
	}

	function get_sub_dept_ids2($parent_id = 0) {
		$dept_sn = $this->get_sub_dept_sn ( $parent_id );
		if ($dept_sn) {
			$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
			$sql = "SELECT * FROM " . $table_dept . " WHERE dept_sn LIKE '" . $dept_sn . "%' ORDER BY dept_sn";
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			$has_children = Database::num_rows ( $res ) > 0;
			while ( $dept = Database::fetch_array ( $res, 'ASSOC' ) ) {
				$this->sub_dept_ids [] = ($dept ['id']);
			}
			return $this->sub_dept_ids;
		}
		return array ();
	}

	function get_sub_dept_sn($parent_id = 0) {
		$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
		$sql = "SELECT dept_sn FROM " . $table_dept . " WHERE id='" . $parent_id . "'";
		return Database::get_scalar_value ( $sql );
	}

	/**
	 * 取子部门信息
	 *
	 * @param int $parent_id 上级部门ID
	 * @param boolean $contain_parent 是否包含父部门
	 * @param boolean $is_fetch_one_sub 是否只取下一级。true:只取一级
	 * @return array()
	 */
	function get_sub_dept_tree($parent_id = 0, $contain_parent = false, $is_fetch_one_sub = FALSE) {
		$sub_dept_list = array ();
		if (! $is_fetch_one_sub) { //取所有子部门
			unset ( $this->sub_dept_ids );
			$sub_dept_ids = $this->get_sub_dept_ids ( $parent_id );
			if (! $sub_dept_ids) $sub_dept_ids = array ();
			
			unset ( $this->all_dept_tree );
			$all_dept_tree = $this->get_all_dept_tree ( true );
			if ($contain_parent) {
				array_unshift ( $sub_dept_ids, $parent_id );
			}
			if ($parent_id != 0) {
				foreach ( $all_dept_tree as $dept_info ) {
					foreach ( $sub_dept_ids as $sub_dept_id ) {
						if ($dept_info ['id'] == $sub_dept_id) {
							$sub_dept_list [$dept_info ['id']] = $dept_info;
						}
					}
				}
				//$sub_dept_list = array_slice ( $all_dept_tree, $sub_parent_index );
				if ($contain_parent) {
					return $sub_dept_list;
				} else {
					array_shift ( $sub_dept_list );
					return $sub_dept_list;
				}
			}
		} else { //只取下一级子部门
			$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
			$parent_dept = $this->get_dept_info ( $parent_id );
			$sub_dept_list [$parent_dept ['id']] = $parent_dept;
			if ($parent_id) {
				$sql = "SELECT * FROM " . $table_dept . " WHERE pid='" . Database::escape_string ( $parent_id ) . "' ORDER BY dept_pos";
			} else { //$parent_id==0时
				$sql = "SELECT * FROM " . $table_dept . " WHERE pid='" . $parent_dept ['id'] . "' ORDER BY dept_pos";
			}
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			while ( $dept_info = Database::fetch_array ( $res, 'ASSOC' ) ) {
				$sub_dept_list [$dept_info ['id']] = $dept_info;
			}
			
			if ($contain_parent) {
				return $sub_dept_list;
			} else {
				array_shift ( $sub_dept_list );
				return $sub_dept_list;
			}
		}
	}

	function get_sub_dept_ddl($parent_id = 0) {
		$sub_dept_list = array ();
		$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
		if ($parent_id) {
			$sql = "SELECT dept_sn FROM " . $table_dept . " WHERE id='" . $parent_id . "'";
			$dept_sn = Database::get_scalar_value ( $sql );
			if (empty ( $dept_sn )) $dept_sn = '';
			if ($dept_sn) {
				$sql = "SELECT *,LENGTH(dept_sn) AS `level` FROM " . $table_dept . " WHERE dept_sn LIKE '" . $dept_sn . "%' ORDER BY dept_sn";
			} else {
				$sql = "SELECT *,LENGTH(dept_sn) AS `level` FROM " . $table_dept . " ORDER BY dept_sn";
			}
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			$has_children = Database::num_rows ( $res ) > 0;
			while ( $dept_info = Database::fetch_array ( $res, 'ASSOC' ) ) {
				$sub_dept_list [$dept_info ['id']] = $dept_info;
			}
		} else {
			$sql = "SELECT *,LENGTH(dept_sn) AS `level` FROM " . $table_dept . " ORDER BY dept_sn";
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			$has_children = Database::num_rows ( $res ) > 0;
			while ( $dept_info = Database::fetch_array ( $res, 'ASSOC' ) ) {
				$sub_dept_list [$dept_info ['id']] = $dept_info;
			}
		}
		return $sub_dept_list;
	}

	function get_sub_dept_ddl2($parent_id = 0, $rtn_type = 'str') {
		$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
		$sub_dept_list = $this->get_sub_dept_ddl ( $parent_id );
		if ($rtn_type == 'str') {
			$html_option = '';
			foreach ( $sub_dept_list as $item ) {
				if ($item ['pid'] == DEPT_TOP_ID) {
					$html_option .= '<option value="' . $item ['id'] . '">---' . get_lang ( 'Org' ) . ": " . $item ['dept_name'] . '---</option>';
				} else {
					$html_option .= '<option value="' . $item ['id'] . '">' . str_repeat ( "&nbsp;&nbsp;", intval ( $item ['level'] / 2 ) ) . $item ['dept_name'] . '</option>';
				}
			}
			return $html_option;
		} elseif ($rtn_type == 'array') {
			$options = array ();
			foreach ( $sub_dept_list as $item ) {
				$options [$item ['id']] = str_repeat ( "&nbsp;&nbsp;", intval ( $item ['level'] / 2 ) ) . $item ['dept_name'];
			}
			return $options;
		}
	}

	function get_dept_info($dept_id = "0") {
		$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		if ($dept_id) {
			//$sql = "SELECT * FROM " . $table_dept . " WHERE id=" . Database::escape ( $dept_id );
			$sql = "SELECT t1.*,t2.username,t2.firstname FROM " . $table_dept . " AS t1 LEFT JOIN " . $table_user . " AS t2 ON t1.dept_admin=t2.user_id WHERE id=" . Database::escape ( $dept_id );
		} else { //取顶级部门
			$sql = "SELECT * FROM " . $table_dept . " WHERE pid='0'";
		}
		$dept_info = Database::fetch_one_row ( $sql, TRUE, __FILE__, __LINE__ );
		return $dept_info;
	}

	function get_dept_user_count() {
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		$sql = "SELECT dept_id,COUNT(user_id) AS cnt FROM " . $table_user . " GROUP BY dept_id";
		$rtn = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );
		return $rtn;
	}

	/**
	 * 获取某一部门下所有部门的用户总数
	 * @param $dept_id
	 * @return unknown_type
	 */
	function get_department_user_count($dept_id = '1') {
		$dept_info = $this->get_dept_info ( $dept_id );
		if ($dept_info) {
			$view_sys_user_dept = Database::get_main_table ( 'sys_user_dept' );
			$dept_sn = $dept_info ['dept_sn'];
			if ($dept_sn == '0') {
				$sql = "SELECT COUNT(*) FROM " . $view_sys_user_dept . " WHERE dept_id<>0 AND pid IS NOT NULL";
			} else {
				$sql = "SELECT COUNT(*) FROM " . $view_sys_user_dept . " WHERE dept_id<>0 AND pid IS NOT NULL AND dept_sn LIKE '" . strval ( $dept_sn ) . "%'";
			}
			//echo $sql;
			return Database::getval ( $sql, __FILE__, __LINE__ );
		}
		return 0;
	}

	function delete_depts($dept_id = "1") {
		$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
		if ($dept_id) {
			$sub_dept_list = $this->get_sub_dept_tree ( $dept_id, TRUE );
			if (is_array ( $sub_dept_list ) && count ( $sub_dept_list ) > 0) {
				$delete_ids = array_keys ( $sub_dept_list );
			}
			if ($delete_ids && is_array ( $delete_ids ) && count ( $delete_ids ) > 0) {
				foreach ( $delete_ids as $delete_id ) {
					if ($this->can_del_dept ( $delete_id ) == 1) {
						Database::delete ( $table_dept, "id=" . Database::escape ( $delete_id ) );
					}
				}
			}
		}
	}

	function del_dept($dept_id) {
		$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		if ($dept_id) {
			$sql = "SELECT COUNT(*) FROM " . $table_dept . " WHERE pid='" . Database::escape_string ( $dept_id ) . "'";
			if (Database::getval ( $sql, __FILE__, __LINE__ ) > 0) {return - 1;} //有子部门
			

			$sql = "SELECT COUNT(*) FROM " . $table_user . " WHERE dept_id='" . Database::escape_string ( $dept_id ) . "'";
			if (Database::getval ( $sql, __FILE__, __LINE__ ) > 0) {return - 2;} //该部门有人员相关
			

			$sql = "DELETE FROM " . $table_dept . " WHERE id='" . Database::escape_string ( $dept_id ) . "'";
			//$sql="UPDATE ".$table_dept. " SET enabled=0 WHERE id='".Database::escape_string($dept_id)."'";
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			
			global $_objCache;
			if ($_objCache && is_object ( $_objCache )) {
				$_objCache->remove ( CACHE_KEY_ADMIN_DEPT );
				$_objCache->save ( self::get_all_dept_tree (), CACHE_KEY_ADMIN_DEPT );
			}
			return 1;
		}
	}

	/**
	 *
	 * @param unknown_type $dept_id
	 * @param unknown_type $is_contain_top 包含最顶级部门
	 */
	function get_dept_path($dept_id, $is_contain_top = false) {
		$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
		if (empty ( $dept_id )) {
			$sql = "SELECT dept_name FROM " . $table_dept . " WHERE pid='0'";
			return Database::getval ( $sql, __FILE__, __LINE__ ) . "/";
		} else {
			$sql = "SELECT dept_name,pid FROM " . $table_dept . " WHERE id='" . Database::escape_string ( $dept_id ) . "'";
			//echo $sql;
			//$rs= api_sql_query ( $sql, __FILE__, __LINE__ );
			$dept_info_arr = api_sql_query_array_assoc ( $sql );
			if ($dept_info_arr && is_array ( $dept_info_arr ) && count ( $dept_info_arr ) > 0) {
				$dept_info = $dept_info_arr [0];
				if ($is_contain_top) {
					$this->dept_path .= $dept_info ['dept_name'] . "/";
				}
				if ($dept_info ['pid'] != 0) {
					if (! $is_contain_top) {
						$this->dept_path .= $dept_info ['dept_name'] . "/";
					}
					$this->get_dept_path ( $dept_info ['pid'], $is_contain_top );
				}
				return $this->dept_path;
			}
		}
	}

	function get_dept_in_org($dept_id, $is_contain_top = false) {
		$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
		if (empty ( $dept_id )) {
			$sql = "SELECT id,pid,dept_name FROM " . $table_dept . " WHERE pid='0'";
			$dept_info_arr = api_sql_query_array_assoc ( $sql );
			$this->dept_in_org [] = $dept_info_arr;
			return $this->dept_in_org;
		} else {
			$sql = "SELECT id,dept_name,pid FROM " . $table_dept . " WHERE id='" . escape ( $dept_id ) . "'";
			$dept_info_arr = api_sql_query_array_assoc ( $sql );
			if ($dept_info_arr && is_array ( $dept_info_arr ) && count ( $dept_info_arr ) > 0) {
				$dept_info = $dept_info_arr [0];
				if ($is_contain_top) {
					$this->dept_in_org [] = $dept_info;
				}
				if ($dept_info ['pid'] != 0) {
					if (! $is_contain_top) {
						$this->dept_in_org [] = $dept_info;
					}
					$this->get_dept_in_org ( $dept_info ['pid'], $is_contain_top );
				}
				return $this->dept_in_org;
			}
		}
	}

	function lock_unlock_dept($action, $dept_id) {
	
	}

	function get_sub_dept($parent_id = '0', $if_has_sub_dept = false) {
		$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
		$sql = "SELECT * FROM " . $table_dept . " WHERE pid=" . Database::escape ( $parent_id );
		$sql .= " ORDER BY dept_pos";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		$sub_depts = array ();
		while ( $dept = Database::fetch_array ( $res, 'ASSOC' ) ) {
			$dept ['level'] = count ( $dept ['dept_sn'] ) / 4;
			if ($if_has_sub_dept) {
				$sql1 = "SELECT * FROM " . $table_dept . " WHERE pid='" . $dept ['id'] . "'";
				$has_sub_depts = Database::if_row_exists ( $sql1 );
				$dept ['has_sub_depts'] = ($has_sub_depts ? "true" : "false");
				$sub_depts [] = $dept;
			} else {
				$sub_depts [] = $dept;
			}
		}
		return $sub_depts;
	}

	function dept_add($parent_id, $dept_no, $dept_name, $dept_desc, $enabled = 1, $dept_admin = 0, $org_id = 0) {
		$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
		$table_dept_admins = Database::get_main_table ( TABLE_MAIN_DEPT_ADMINS );
		$top_dept = $this->get_top_dept ();
		if (empty ( $parent_id ) && $top_dept) $parent_id = $top_dept ['id']; //如果没有上级部门，则以顶级部门为其上级部门
		

		//计算自动的编号
		define ( "DEPT_SN_DESC", false );
		if (DEPT_SN_DESC == true) { //降序
			if ($parent_id) {
				$sql = "SELECT dept_sn FROM " . $table_dept . " WHERE id=" . Database::escape ( $parent_id ); //父结点的自动编号
				$parent_detp_sn = Database::get_scalar_value ( $sql );
				if (empty ( $parent_detp_sn )) $parent_detp_sn = "";
				
				//同级别部门中
				$sql = "SELECT dept_sn FROM " . $table_dept . " WHERE dept_sn LIKE '" . $parent_detp_sn . str_repeat ( "_", DEPT_NO_BITS ) . "' ORDER BY dept_sn LIMIT 1";
				$p_dept_sn = Database::get_scalar_value ( $sql );
				if ($p_dept_sn) {
					$tmp_sub_sn = intval ( substr ( $p_dept_sn, - DEPT_NO_BITS ) );
					$dept_sn = strval ( $tmp_sub_sn - 1 );
					$dept_sn = substr ( $p_dept_sn, 0, - DEPT_NO_BITS ) . strval ( $dept_sn );
				} else {
					$dept_sn = strval ( $parent_detp_sn ) . str_repeat ( "9", DEPT_NO_BITS );
				}
			} else {
				$dept_sn = '0';
			}
		} else { //升序
			if ($parent_id) {
				$sql = "SELECT dept_sn FROM " . $table_dept . " WHERE id=" . Database::escape ( $parent_id );
				$parent_detp_sn = Database::get_scalar_value ( $sql );
				if (empty ( $parent_detp_sn )) $parent_detp_sn = "";
				
				$sql = "SELECT dept_sn FROM " . $table_dept . " WHERE dept_sn LIKE '" . $parent_detp_sn . str_repeat ( "_", DEPT_NO_BITS ) . "' ORDER BY dept_sn DESC LIMIT 1";
				$p_dept_sn = Database::get_scalar_value ( $sql );
				if ($p_dept_sn) {
					$tmp_sub_sn = intval ( substr ( $p_dept_sn, - DEPT_NO_BITS ) );
					$dept_sn = strval ( $tmp_sub_sn + 1 );
					$dept_sn = substr ( $p_dept_sn, 0, - DEPT_NO_BITS ) . strval ( $dept_sn );
				} else {
					$dept_sn = strval ( $parent_detp_sn ) . "1" . str_repeat ( "0", DEPT_NO_BITS - 1 );
				}
			
			} else {
				$dept_sn = '0';
			}
		}
		
		//创建机构时
		if ($parent_id && $parent_id == DEPT_TOP_ID && empty ( $org_id )) {
			$row = array ('pid' => DEPT_TOP_ID, 'dept_no' => $dept_no, 'dept_name' => $dept_name, 'dept_desc' => $dept_desc, 'enabled' => $enabled, 'dept_admin' => $dept_admin, 'dept_pos' => $dept_pos, 'dept_sn' => $dept_sn, "org_id" => $org_id, 'last_updated_date' => date ( "Y-m-d H:i:s" ) );
			$sql = Database::sql_insert ( $table_dept, $row );
			api_sql_query ( $sql, __FILE__, __LINE__ );
			$new_dept_id = Database::get_last_insert_id ();
		
		//创建目录
		/*if(!file_exists(api_get_path(SYS_ORG_DATA_PATH))){
				mkdir(api_get_path(SYS_ORG_DATA_PATH),CHMOD_NORMAL);
			}
			mkdir(api_get_path(SYS_ORG_DATA_PATH).strval($new_dept_id)."_".$dept_sn,CHMOD_NORMAL);
			mkdir(api_get_path(SYS_ORG_DATA_PATH).strval($new_dept_id)."_".$dept_sn."/ftp_root",CHMOD_NORMAL);*/
		} else { //创建机构下部门时
			$sql = "SELECT * FROM " . $table_dept . " WHERE dept_no='" . escape ( $dept_no ) . "' AND org_id='" . escape ( $org_id ) . "'";
			if (Database::if_row_exists ( $sql ) == false) {
				$sql = "SELECT MAX(dept_pos) FROM " . $table_dept . " WHERE pid='" . $parent_id . "'";
				$dept_pos = Database::get_scalar_value ( $sql ) + 1;
				
				$row = array ('pid' => $parent_id, 'dept_no' => $dept_no, 'dept_name' => $dept_name, 'dept_desc' => $dept_desc, 'enabled' => $enabled,/*'dept_admin'=>$dept_admin,*/ 'dept_pos' => $dept_pos, 'dept_sn' => $dept_sn, "org_id" => $org_id, 'last_updated_date' => date ( "Y-m-d H:i:s" ) );
				$sql = Database::sql_insert ( $table_dept, $row );
				api_sql_query ( $sql, __FILE__, __LINE__ );
				$new_dept_id = Database::get_last_insert_id ();
			
		//增加部门管理员
			/*$admin_arr=explode(",",$dept_admin);
				if($admin_arr && is_array($admin_arr)){
					foreach($admin_arr as $admin_id) {
						if($new_dept_id && $admin_id){
							$row=array('dept_id'=>$new_dept_id,'dept_admin_id'=>$admin_id);
							$sql=Database::sql_insert($table_dept_admins,$row,TRUE);
							api_sql_query ( $sql, __FILE__, __LINE__ );
						}
					}
				}*/
			}
		}
		
		$log_msg = get_lang ( 'AddDeptInfo' ) . $dept_name . "(" . $dept_no . ",id=" . $new_dept_id . ")";
		api_logging ( $log_msg, 'DEPT', 'AddDeptInfo' );
		
		global $_objCache;
		if ($_objCache && is_object ( $_objCache )) {
			$_objCache->remove ( CACHE_KEY_ADMIN_DEPT );
			$_objCache->save ( $this->get_all_dept_tree (), CACHE_KEY_ADMIN_DEPT );
		}
		
		return $new_dept_id;
	}

	/**
	 * 获取部门的管理员
	 * @param unknown_type $dept_id
	 */
	function get_dept_admins($dept_id) {
		/*$table_user = Database :: get_main_table(TABLE_MAIN_USER);
		$table_dept_admins = Database::get_main_table ( TABLE_MAIN_DEPT_ADMINS );
		$sql="SELECT user_id,CONCAT(firstname,'(',username,')') as ustr FROM "
			.$table_dept_admins." AS t1 LEFT JOIN ".$table_user
			." AS t2 ON t1.dept_admin_id=t2.user_id WHERE t1.dept_id=".Database::escape($dept_id);
		//echo $sql."<br/>";
		return Database::get_into_array2( $sql, __FILE__, __LINE__ );*/
	}

	//---------------------------机构管理相关函数
	

	function get_all_org() {
		$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
		//$table_user = Database::get_main_table(TABLE_MAIN_USER);
		//$sql = "SELECT t1.*,t2.username,t2.firstname FROM " . $table_dept . " AS t1 LEFT JOIN ".$table_user." AS t2 ON t1.dept_admin=t2.user_id WHERE pid=" . DEPT_TOP_ID . " ORDER BY dept_pos";
		$sql = "SELECT t1.* FROM " . $table_dept . " AS t1 WHERE pid=" . DEPT_TOP_ID . " ORDER BY dept_pos";
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		return api_store_result_array ( $res );
	}

	function get_org_dept_tree($org_id = NULL) {
		if ($org_id) {
			$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
			$sql = "SELECT * FROM " . $table_dept . " WHERE org_id=" . Database::escape ( $org_id ) . " ORDER BY dept_sn";
			//echo $sql;exit;
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			return api_store_result_array ( $res );
		}
		return array ();
	
	}

	function org_del($org_id) {
		global $_configuration;
		$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
		$table_user = Database::get_main_table ( TABLE_MAIN_USER );
		$view_user_dept = Database::get_main_table ( VIEW_USER_DEPT );
		
		$dept_id_arr = $this->get_sub_dept_ids2 ( $org_id );
		
		//删除所有部门
		$sql = "DELETE FROM " . $table_dept . " WHERE org_id=" . Database::escape ( $org_id );
		api_sql_query ( $sql, __FILE__, __LINE__ );
		
		//删除所有用户
		if ($dept_id_arr && is_array ( $dept_id_arr )) {
			$dept_ids = Database::create_in ( $dept_id_arr, "dept_id" );
			//$sql="SELECT user_id FROM ".$view_user_dept." WHERE dept_sn LIKE '".$dept_sn."%'";
			$sql = "SELECT user_id FROM " . $table_user . " WHERE " . $dept_ids;
			$res = api_sql_query ( $sql, __FILE__, __LINE__ );
			while ( $row = Database::fetch_array ( $res, 'ASSOC' ) ) {
				UserManager::delete_user ( $row ['user_id'] );
			}
		}
		
		$sql = "SELECT user_id FROM $table_user WHERE org_id=" . Database::escape ( $org_id );
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );
		while ( $row = Database::fetch_array ( $res, 'ASSOC' ) ) {
			UserManager::delete_user ( $row ['user_id'] );
		}
		
		global $_objCache;
		if ($_objCache && is_object ( $_objCache )) {
			$_objCache->remove ( CACHE_KEY_ADMIN_DEPT );
			$_objCache->save ( $this->get_all_dept_tree (), CACHE_KEY_ADMIN_DEPT );
		}
		
		return 1;
	}
}
