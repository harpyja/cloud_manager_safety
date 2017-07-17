<?php
/**
 ==============================================================================

 ==============================================================================
 */

$language_file = array ('admin', 'registration' );
$cidReset = true;

include_once ('../../inc/global.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

api_protect_admin_script ();

$main_user_table = Database::get_main_table ( VIEW_USER_DEPT );
$table_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
$table_user = Database::get_main_table ( TABLE_MAIN_USER );
$htmlHeadXtra [] = Display::display_thickbox ();

$action = getgpc ( 'action', 'G' );
$dept_id = isset ( $_GET ['keyword_deptid'] ) ? intval(getgpc ( 'keyword_deptid', 'G' )) : '0';

$sql = "SELECT user_id FROM " . $table_user . " WHERE is_admin=1 AND " . Database::create_in ( $_configuration ['default_administrator_name'], 'username' );
$root_user_id = Database::get_into_array ( $sql, __FILE__, __LINE__ );
$redirect_url = 'main/admin/user/user_list.php';
include_once ('../../inc/header.inc.php');

if($platform==3){
    $nav='userlist';
}else{
    $nav='users';
}
echo '<aside id="sidebar" class="column '.$nav.' open">
       <div id="flexButton" class="closeButton close"></div>
      </aside>';

//部门数据
$objDept = new DeptManager ();

function get_sqlwhere() {
    global $objDept;
    $sql_where = "";
    if (isset ( $_GET ['keyword'] ) && ! empty ( $_GET ['keyword'] )) {
        $keyword = escape ( getgpc ( 'keyword', 'G' ), TRUE );
        if($keyword=='输入搜索关键词'){
            $keyword='';
        }
        $sql_where .= " AND and (firstname LIKE '%" . $keyword . "%'  OR username LIKE '%" . $keyword . "%'  OR official_code LIKE '%" . $keyword . "%'  OR dept_id LIKE '%" . $keyword ."%') ";
    }

    if (isset ( $_GET ['keyword_deptid'] ) and getgpc ( 'keyword_deptid' ) != "0") {
        $dept_id = intval ( escape ( getgpc ( 'keyword_deptid', 'G' ) ) );
        $dept_sn = $objDept->get_sub_dept_sn ( $dept_id );
        if ($dept_sn) $sql_where .= " AND dept_sn LIKE '" . $dept_sn . "%'";
    }

    if (isset ( $_GET ['keyword_org_id'] ) && getgpc ( 'keyword_org_id' )) {
        $sql_where .= " AND org_id=" . escape (intval(getgpc ( "keyword_org_id" )) );
    }

    $action_array = array ('batchChangeDept' );
    if (isset ( $_POST ['id'] ) && is_array ( $_POST ['id'] ) && $_POST ['id']  && (isset ( $_POST ['action'] ) && in_array ( $_POST ['action'], $action_array ))) {
        $post_id= intval( getgpc('id'));
        $sql_where .= " AND user_id IN (" . implode ( ",", $post_id ) . ")";
    }

    if (is_not_blank ( $_GET ['dept_name'] )) {
        $dept_name=trim(htmlspecialchars(getgpc ( 'dept_name', 'G' )) );
        $deptId = Database::getval ("select `id` from `sys_dept` where `dept_name`='".$dept_name."'", __FILE__, __LINE__ );
        $sql_where .= " AND `dept_id`=" . $deptId;     
    }

    $sql_where = trim ( $sql_where );
    if ($sql_where)
        return substr ( ltrim ( $sql_where ), 3 );
    else return "";
}
                
function get_number_of_users() {
	$user_table = Database::get_main_table ( VIEW_USER_DEPT );
	$sql = "SELECT COUNT(user_id) AS total_number_of_items FROM " . $user_table." WHERE  active=1 " ;
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= $sql_where;
	return Database::getval ( $sql, __FILE__, __LINE__ );
}

function get_user_data($from, $number_of_items, $column, $direction) {
	$user_table = Database::get_main_table ( VIEW_USER_DEPT );
	$sql = "SELECT  user_id		AS col0,
                 	username	AS col1,
                 	firstname 	AS col2,
                 	email 	AS col3,
                 	official_code 	AS col4,
                 	lastname 	AS col5,
	dept_name	AS col6,
	seatnumber	    AS col7,
	active		AS col8,
	user_id		AS col9
	FROM  $user_table  where  active=1  ";
	$sql_where = get_sqlwhere ();     
	if ($sql_where) $sql .=   $sql_where;
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
//  echo $sql;
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$users = array ();
	//$objDept = new DeptManager ();
	while ( $user = Database::fetch_row ( $res ) ) {
		/*$objDept->dept_path="";
		$dept_path=$objDept->get_dept_path($user[4],FALSE);
		$dept_path=rtrim($dept_path,"/");
		$user[4]=api_substr($dept_path,0,api_strrpos($dept_path,"/"));	*/
		$users [] = $user;
	}
	return $users;
}

function email_filter($email) {
	return Display::encrypted_mailto_link ( $email, $email );
}

function modify_filter($user_id, $url_params) {
	global $_configuration, $root_user_id;
    $result ='';
	$result .= link_button ( 'synthese_view.gif', 'Info', 'user_information.php?user_id=' . $user_id, '90%', '90%', FALSE );
	//$result .= '<a class="thickbox" href="subscribe_course2user.php?user_id=' . $user_id . '&KeepThis=true&TB_iframe=true&height=420&width=780&modal=true">' . Display::return_icon('enroll.gif', get_lang('AddCoursesToUser'), array('style'=>'vertical-align: middle;')) . '</a>&nbsp;';
	//$result .= '&nbsp;<a href="user_subscribe_course_list.php?user_id=' . $user_id . '" target="_parent">' . Display::return_icon ( 'enroll.gif', get_lang ( 'CourseListSubAndArrange' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>';
	//$result .= '&nbsp;' . link_button ( 'enroll.gif', 'CourseListSubAndArrange', 'user_subscribe_course_list.php?user_id=' . $user_id, '90%', '94%', FALSE );
	$result .= '&nbsp;' . link_button ( 'edit.gif', 'ModifyUserInfo', 'user_edit.php?user_id=' . $user_id, '90%', '80%', FALSE );
	if (api_is_platform_admin () && ! in_array ( $user_id, $root_user_id )) {
		$result .= '&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Delete', 'user_list.php?action=delete_user&user_id=' . $user_id );
	}

	return $result;
}

function active_filter($active, $url_params, $row) {
	global $_user, $_configuration;
	if (isRoot ( $row [1] )) return '';

	if ($active == '1') {
		$action = 'lock';
		$image = 'right';
	}
	if ($active == '0') {
		$action = 'unlock';
		$image = 'wrong';
	}

	if ($row [0] != $_user ['user_id']) {
		//$result = icon_href ( $image . '.gif', ucfirst ( $action ), 'user_list.php?action=' . $action . '&amp;user_id=' . $row ['0'] . '&amp;' . $url_params );
		$result = '<a href="user_list.php?action=' . $action . '&amp;user_id=' . $row ['0'] . '&amp;' . $url_params . '">' . Display::return_icon ( $image . '.gif', get_lang ( ucfirst ( $action ) ), array ('style' => 'vertical-align: middle;' ) ) . '</a>';
	}
	return $result;
}

function lock_unlock_user($status, $user_id) {
	global $_configuration;
	$user_table = Database::get_main_table ( TABLE_MAIN_USER );

	if ($status == 'lock') { //锁定
		$status_db = '0';
		$return_message = get_lang ( 'UserLocked' );
	}
	if ($status == 'unlock') { //解锁
		$status_db = '1';
		$return_message = get_lang ( 'UserUnlocked' );
	}

	if (($status_db == '1' or $status_db == '0') and is_numeric ( $user_id )) {
		$sql = "UPDATE $user_table SET active='" . escape ( $status_db ) . "' WHERE user_id='" . escape ( $user_id ) . "'  AND username !='" . $_configuration ['default_administrator_name'][0] . "'";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );

		api_logging ( $return_message . ": status=" . $status . ",user_id=" . $user_id, 'USER' );
	}

	if ($result > 0) {
		return $return_message;
	}
}

function batch_lock_unlock_user($action, $user_ids = array()) {
	$user_table = Database::get_main_table ( TABLE_MAIN_USER );
	global $_configuration;
	if ($action == 'batchLock') { //锁定
		$status_db = '0';
	}
	if ($action == 'batchUnlock') { //解锁
		$status_db = '1';
	}

	if (is_array ( $user_ids ) && count ( $user_ids )) {
		$sql = "UPDATE $user_table SET active='" . $status_db . "' WHERE user_id IN (" . implode ( ",", $user_ids ) . ") AND username !='" . $_configuration ['default_administrator_name'][0] . "'";
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	}
	api_logging ( get_lang ( "BatchLockUnlockUser" ) . ": action=" . $action . ",user_id=" . implode ( ",", $user_ids ), 'USER' );

	return $result;

}

if (isset ( $_GET ['action'] )) {
    $get_user_id=  intval(getgpc('user_id'));
	switch (getgpc('action','G')) {
		case 'delete_user' :
			if (getgpc ( 'user_id', 'G' ) != api_get_user_id () && UserManager::delete_user ($get_user_id)) {
				api_logging ( get_lang ( 'DelUser' ) . $get_user_id, 'USER' );
                                                                                    tb_close('user_list.php');
                                                                                   //Display::display_msgbox ( get_lang ( 'UserDeleted' ), $redirect_url );
			} else {
                                                                                    tb_close('user_list.php');
                                                                                   //Display::display_msgbox ( get_lang ( 'CannotDeleteUser' ), $redirect_url, 'warning' );
			}
			break;
		case 'lock' :
			$message = lock_unlock_user ( 'lock', $get_user_id );tb_close('user_list.php');
			//Display::display_msgbox ( get_lang ( 'OperationSuccess' ), $redirect_url );
			break;
		case 'unlock' :
			$message = lock_unlock_user ( 'unlock',$get_user_id );tb_close('user_list.php');
			//Display::display_msgbox ( get_lang ( 'OperationSuccess' ), $redirect_url );
			break;
	}
}
if (isset ( $_POST ['action'] )) {
	switch ($_POST ['action']) {
		case 'delete' :
			$number_of_selected_users = count ( $_POST ['id'] );
			$number_of_deleted_users = 0;
                                                                  $del_id= $_POST['id'];
			foreach ($del_id as $index => $user_id ) {
				if ($user_id != api_get_user_id ()) {
					if (UserManager::delete_user ( $user_id )) {
						api_logging ( get_lang ( 'DelUsers' ) . $user_id, 'USER', 'DelUsers' );
						$number_of_deleted_users ++;
					}
				}
			}
			if ($number_of_selected_users == $number_of_deleted_users) {
                                                                                        tb_close('user_list.php');
//				Display::display_msgbox ( get_lang ( 'SelectedUsersDeleted' ), $redirect_url );
			} else {
                                                                                        tb_close('user_list.php');
//				Display::display_msgbox ( get_lang ( 'SomeUsersNotDeleted' ), $redirect_url, 'warning' );
			}
			break;
		case 'resetPassword' :
			$number_of_selected_users = count ( $_POST ['id'] );
			$number_of_deleted_users = 0;
			foreach ( $_POST ['id'] as $index => $user_id ) {
				if ($user_id != api_get_user_id()) {
					if (UserManager::reset_password ( $user_id, api_get_setting ( 'default_password' ) )) {
						api_logging ( get_lang ( 'ResetToDefaultPassword' ) . $user_id, 'USER', 'ResetToDefaultPassword' );
						$number_of_deleted_users ++;
					}
				}
			}
			if ($number_of_selected_users == $number_of_deleted_users) {
                                                                                        tb_close('user_list.php');
//				Display::display_msgbox ( get_lang ( 'SelectedUsersPasswordReseted' ), $redirect_url );
			} else {
                                                                                        tb_close('user_list.php');
//				Display::display_msgbox ( get_lang ( 'SomeUsersPasswordNotReseted' ), $redirect_url );
			}
			break;

		case 'batchChangeDeptSave' : //保存批量修改用户部门
			$user_id_str = getgpc ( 'user_id_str', 'P' );
			$dept_id = intval(getgpc ( 'department_id', 'P' ));
			if ($user_id_str) {
				$sql = "SELECT org_id FROM " . $table_dept . " WHERE id=" . Database::escape ( $dept_id );
				$orgid = Database::get_scalar_value ( $sql );
				$sql_data = array ('dept_id' => $dept_id, 'org_id' => $orgid );
				$sql = Database::sql_update ( $table_user, $sql_data, " user_id IN (" . $user_id_str . ")" );
				api_sql_query ( $sql, __FILE__, __LINE__ );tb_close('user_list.php');
//				Display::display_msgbox ( get_lang ( 'SelectedUsersDeptChanged' ), $redirect_url );
			}
			break;
		case 'batchLock' :
			$number_of_selected_users = count ( $_POST ['id'] );
			$number_of_handled_users = batch_lock_unlock_user ( getgpc ( 'action', 'P' ), $_POST ['id'] );
			if ($number_of_selected_users == $number_of_handled_users) {
                            //tb_close('user_list.php');
//				Display::display_msgbox ( get_lang ( 'SelectedUsersHandled' ), $redirect_url );
			} else {
                            //tb_close('user_list.php');
//				Display::display_msgbox ( get_lang ( 'SomeUsersNotHandled' ), $redirect_url );
			}
			break;
		case 'batchUnlock' :
			$number_of_selected_users = count ( $_POST ['id'] );
			$number_of_handled_users = batch_lock_unlock_user ( getgpc ( 'action', 'P' ), $_POST ['id'] );

			if ($number_of_selected_users == $number_of_handled_users) {
                                                                                        //tb_close('user_list.php');
//				Display::display_msgbox ( get_lang ( 'SelectedUsersHandled' ), $redirect_url );
			} else {
                                                                                        //tb_close('user_list.php');
//				Display::display_msgbox ( get_lang ( 'SomeUsersNotHandled' ), $redirect_url );
			}
			break;
	}
}



//Display::display_header (NULL, FALSE);

if (isset ( $_POST ['action'] )) {
	switch ($_POST ['action']) {
		case 'batchChangeDept' : //批量修改用户部门表单
			$number_of_selected_users = count ( $_POST ['id'] );
			if ($number_of_selected_users > 0) {
				$user_id_str = implode ( ",", $_POST ['id'] );
				$form = new FormValidator ( 'batch_change_dept', 'post' );
				//$form->addElement('header', 'header', get_lang('SearchAUser'));
				$form->addElement ( "hidden", "action", 'batchChangeDeptSave' );
				$form->addElement ( "hidden", "user_id_str", $user_id_str );
				$renderer = $form->defaultRenderer ();
				$renderer->setElementTemplate ( '<span>{element}</span> ' );
				$form->addElement ( 'static', 'text1', null, get_lang ( "PlsSelectTheDeptMoveTo" ) );
				//unset($dept_options[0]);
				$dept_options [0] = NULL;
				//$form->addElement ( 'select', 'org_id', get_lang ( 'InOrg' ), $orgs, array ('id' => "org_id", 'style' => 'height:22px;' ) );
				$depts = $objDept->get_sub_dept_ddl2 ( 0, 'array' );
				$form->addElement ( 'select', 'department_id', get_lang ( 'UserInDept' ), $depts, array ('id' => "department_id", 'style' => 'height:22px;' ) );
				$form->addElement ( 'submit', 'submit', get_lang ( 'Ok' ), 'class="inputSubmit"' );
				$form->addElement ( 'button', 'btn', get_lang ( 'Cancel' ), 'class="cancel" onclick="javascript:location.href=\'user_list.php\';"' );
				echo '<div class="actions">';
				$form->display ();
				echo '</div>';
			} else {
                            tb_close('user_list.php');
//				Display::display_msgbox ( get_lang ( 'PlsSelectedUsers' ), $redirect_url );
			}
			exit ();
			break;
	}
}

$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" ) . "/" . get_lang ( "OfficialCode" );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "", 'class' => 'inputText', 'title' => $keyword_tip,'value'=>'输入搜索关键词','id'=>'searchkey') );

$sql1 = "SELECT `id`,`dept_name` FROM `vslab`.`sys_dept`";
$result1 = api_sql_query ( $sql1, __FILE__, __LINE__ );
$arr= array ();

while ( $arr = Database::fetch_row ( $result1) ) {
    $arrs [] = $arr;
}
foreach ( $arrs as  $v){
    $arrss[$v[1]]=$v[1];
}

array_unshift($arrss,"---所有部门---");
$form->addElement ( 'select', 'dept_name', get_lang ( 'UserInDept' ), $arrss, array ('style' => 'min-width:150px;height:30px;border: 1px solid #999;' ) );

$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );

//by changzf
echo '<section id="main" class="column">';
echo '<h4 class="page-mark" >当前位置：平台首页  &gt; 用户管理</h4>';
echo '<div class="managerSearch">';
$form->display (); //searc form
echo '<span class="searchtxt right">';
$url_add_user='user_add.php?keyword_deptid='.(is_not_blank($_GET['keyword_deptid'])?intval(getgpc('keyword_deptid','G')):intval(getgpc('keyword_orgid','G')));
echo link_button ( 'add_user_big.gif', 'AddUsers', $url_add_user, '90%', '90%' );
echo link_button ( 'excel.gif', 'ImportUserListXMLCSV',($_configuration ['enable_user_ext_info']?'user_import2.php':'user_import.php'), '70%', '80%' );
echo link_button ( 'excel.gif', 'ExportUserListXMLCSV', 'user_export.php', '50%', '80%' );
echo '</span>';
echo "</div>";

if (isset ( $_GET ['keyword'] ) && is_not_blank ( $_GET ["keyword"] )) {
	$parameters ['keyword'] = getgpc ( 'keyword', 'G' );
	$parameters = array ('keyword' => $_GET ['keyword'], 'keyword_status' => $_GET ['keyword_status'], 'keyword_org_id' => intval($_GET ["keyword_org_id"]) );
}

if (is_not_blank ( $_GET ["keyword_org_id"] )) $parameters ['keyword_org_id'] = intval( getgpc("keyword_org_id",'G') );
if ($_GET ['dept_name']) $parameters ['dept_name'] = $_GET ['dept_name'];

$table = new SortableTable ( 'admin_users', 'get_number_of_users', 'get_user_data', 0, NUMBER_PAGE, 'DESC' );
$table->set_additional_parameters ( $parameters );
$idx = 0;
$table->set_header ( $idx ++, '',  false, null, null);
$table->set_header ( $idx ++, get_lang ( 'LoginName' ), false, null, array ('style' => 'width:15%' ));
$table->set_header ( $idx ++, get_lang ( 'FirstName' ), false, null, array ('style' => 'width:15%' ));
$table->set_header ( $idx ++, get_lang ( 'Email' ), false, null, array ('style' => 'width:15%' ));
$table->set_header ( $idx ++, get_lang ( 'OfficialCode' ), false, null, array ('style' => 'width:10%' ));
$table->set_header ( $idx ++, get_lang ( 'JobTitle' ), false, null, array ('style' => 'width:10%' ));
$table->set_header ( $idx ++, get_lang ( 'InDept' ), false, null, array ('style' => 'width:18%' ));
$table->set_header ( $idx ++, get_lang ( '座位号' ), false, null, array ('style' => 'width:5%' ) );
$table->set_header ( $idx ++, get_lang ( 'Active' ), false, null, array ('style' => 'width:5%' ));
$table->set_header ( $idx ++, get_lang ( 'Actions' ), false, null, array ('style' => 'width:10%' ) );
//$table->set_column_filter ( 7, 'seatnumber_filter' );
$table->set_column_filter ( 8, 'active_filter' );
$table->set_column_filter ( 9, 'modify_filter' );
$actions = array ('delete' => get_lang ( 'BatchDelete' ),
    'resetPassword' => get_lang ( 'ResetToDefaultPassword' ),
    'batchChangeDept' => get_lang ( "BatchChangeUsersDept" ),
    'batchLock' => get_lang ( "BatchLockUser" ),
    'batchUnlock' => get_lang ( "BatchUnLockUser" ) );
$table->set_form_actions ( $actions );
//$table->display ();

?>
    <article class="module width_full">
        <table cellspacing="0" cellpadding="0" class="p-table">
	   <?php $table->display ();?>
<!--            <div class="actions">  --><?php //$form->display ();?><!-- </div>-->
        </table>
    </article>
</section>
<?php

