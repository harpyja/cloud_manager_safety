<?php
/**
 ==============================================================================
 待审核的用户列表
 ==============================================================================
 */

$language_file = array ('admin', 'registration' );
$cidReset = true;
include ('../../inc/global.inc.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');

api_protect_admin_script ();

$action = getgpc ( 'action', 'G' );

$tbl_reg_user = Database::get_main_table ( TABLE_MAIN_USER_REGISTER );
$sql = "SELECT reg_status,COUNT(*) FROM " . $tbl_reg_user;
$reg_user_cnt = Database::get_into_array2 ( $sql, __FILE__, __LINE__ );

$htmlHeadXtra [] = Display::display_thickbox ();

function get_sqlwhere() {
                $sql_where = "";
                $get_word=  getgpc('keyword');
	if (isset ( $get_word )) {
                            if($get_word=="输入搜索关键词"){
                               $get_word='';
                            }
                            $parameters ['keyword'] = $get_word;
                            $keyword = trim ( Database::escape_string ($get_word) );
	        $sql_where .= " AND  (firstname LIKE '%" . $keyword . "%'  OR username LIKE '%" . $keyword . "%'  OR official_code LIKE '%" . $keyword . "%')";
	}
	
	if (isset ( $_GET ['status'] )) {
		switch (getgpc ( "status", 'G' )) {
			case '1' :
				$sql_where .= " AND reg_status=0";
				break;
			case '2' :
				$sql_where .= " AND reg_status=1";
				break;
			case '3' : //通过
				$sql_where .= " AND reg_status=2";
				break;
		}
	}
	$sql_where = trim ( $sql_where );
	if ($sql_where)
		return substr ( ltrim ( $sql_where ), 3 );
	else return "";
}

/**
 * Get the total number of users on the platform
 * @see SortableTable#get_total_number_of_items()
 */
function get_number_of_users() {
	global $tbl_reg_user;
	$sql = "SELECT COUNT(user_id) AS total_number_of_items FROM $tbl_reg_user ";
	
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " WHERE " . $sql_where;
	
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$obj = mysql_fetch_object ( $res );
	return $obj->total_number_of_items;
}

function get_user_data($from, $number_of_items, $column, $direction) {
	global $tbl_reg_user;
	
	$sql = "SELECT user_id , username , firstname , email , mobile , credential_no , registration_date , reg_ip , reg_status , user_id FROM  $tbl_reg_user  ";
	
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " WHERE " . $sql_where;
	
	$sql .= " ORDER BY user_id ";
	$sql .= " LIMIT $from,$number_of_items";
	 //echo $sql;
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$users = array ();
	while ( $user = Database::fetch_row ( $res ) ) {
		$reg_status = $user [8];
		$user [8] = active_filter ( $reg_status );
		$user [9] = modify_filter ( $user [9], $reg_status );
		$users [] = $user;
	}
	return $users;
}

function email_filter($email) {
	return Display::encrypted_mailto_link ( $email, $email );
}

function modify_filter($user_id, $reg_status) {
       global $tbl_reg_user;
        if ($reg_status == 0){
            $result .= link_button ( 'edit.gif', 'AuditUserRegister', 'user_audit_do.php?user_id=' . $user_id, 500, 750, FALSE );
        }
        //if ($reg_status != 2){
            $result .= '<a href="user_list_audit.php?action=delete_user&amp;user_id=' . $user_id . '&amp;' . $url_params . '" onclick="javascript:if(!confirm(' . "'" . get_lang ( "ConfirmYourChoice" ) . "'" . ')) return false;">' .
        Display::return_icon ( 'delete.gif', get_lang ( 'Delete' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>';
      //  }
        return $result;
}

function active_filter($active, $url_params, $row) {
	switch ($active) {
		case 0 :
			$result = get_lang ( 'CourseStatus0' );
			break;
		case 2 : //通过
			$result = get_lang ( 'CourseStatus1' );
			break;
		case 1 : //末通过
			$result = get_lang ( 'CourseStatus2' );
			break;
		default :
			break;
	
	}
	return $result;
}
include_once ('../../inc/header.inc.php');

if (isset ( $_GET ["message"] )) {
    $prompt=urldecode ( getgpc("message") );
	//Display::display_normal_message ( urldecode ( trim ( $_GET ["message"] ) ) );
}

if (isset ( $_GET ['action'] )) {
    $get_uid=  intval(getgpc('user_id'));
	switch ($_GET ['action']) {
		case 'show_message' :$prompt='1';
			Display::display_normal_message ( stripslashes (getgpc("message")) );
			break;
		case 'delete_user' : //删除单个用户
			if (getgpc('user_id') != api_get_user_id() && UserManager::delete_user_register ( intval($_GET ['user_id']) )) {
                            $get_uid=  intval(getgpc('user_id'));
				$log_msg = get_lang ( 'DelRegUser' ) . "reg_user_id=" . $get_uid;
				api_logging ( $log_msg, 'REGUSER', 'DelRegUser' );
                            $prompt='用户已删除!';
				//Display::display_normal_message ( get_lang ( 'UserDeleted' ) );
			} else {
                            $prompt='您不能删除该用户';
				Display::display_error_message ( get_lang ( 'CannotDeleteUser' ) );
			}
			break;
		case 'lock' :
			$message = UserManager::lock_unlock_user ( 'lock', $get_uid );
			$log_msg = get_lang ( 'RegUserAuditNotPass' ) . "reg_user_id=" . $get_uid;
			api_logging ( $log_msg, 'REGUSER', 'RegUserAuditNotPass' );
                            $prompt=$message;
			//Display::display_normal_message ( $message );
			break;
		case 'unlock' : //审核通过
			$message = UserManager::lock_unlock_user ( 'unlock', $get_uid );
			$log_msg = get_lang ( 'RegUserAuditPass' ) . "reg_user_id=" . $get_uid;
			api_logging ( $log_msg, 'REGUSER', 'RegUserAuditPass' );
                            $prompt=$message;
			//Display::display_normal_message ( $message );
			break;
	
	}
}
if (isset ( $_POST ['action'] )) {
	switch ($_POST ['action']) {
		case 'delete' : //批量删除
			$number_of_selected_users = count ( $_POST ['id'] );
			$number_of_deleted_users = 0;
                        $del_id=intval(getgpc('id','G'));
			foreach ( $del_id  as $index => $user_id ) {
				if ($user_id != $_user ['user_id']) {
					if (UserManager::delete_user_register ( $user_id )) {
						$number_of_deleted_users ++;
						$batch_user_ids = $user_id . ",";
					}
				}
			}
			if ($number_of_selected_users == $number_of_deleted_users) {
				$log_msg = get_lang ( 'DelRegUsers' ) . "reg_user_ids=" . $batch_user_ids;
				api_logging ( $log_msg, 'REGUSER', 'DelRegUsers' );
                $prompt= '选择删除的用户!';
				//Display::display_normal_message ( get_lang ( 'SelectedUsersDeleted' ) );
			} else {
                $prompt= '一些用户没有删除，可能是系统不允许您这样做';
				//Display::display_error_message ( get_lang ( 'SomeUsersNotDeleted' ) );
			}
			break;
		case 'active' : //批量审核通过
			$number_of_selected_users = count ( $_POST ['id'] );
			$number_of_deleted_users = 0;
			foreach ( $_POST ['id'] as $index => $user_id ) {
				if (UserManager::audit_reg_user_passed ( $user_id )) {
					$number_of_deleted_users ++;
					$batch_user_ids2 = $user_id . ",";
				}
			
			}
			if ($number_of_selected_users == $number_of_deleted_users) {
				$log_msg = get_lang ( 'BatchRegUserAuditPass' ) . "reg_user_ids=" . $batch_user_ids2;
				api_logging ( $log_msg, 'REGUSER', 'BatchRegUserAuditPass' );
                $prompt= '用户全部审核通过!';
				//Display::display_normal_message ( get_lang ( 'SelectedUsersAuditPassed' ) );
			} else {
                $prompt= '一些用户没有审核通过，可能是系统不允许您这样做!';
				//Display::display_error_message ( get_lang ( 'SomeUsersNotAuditPassed' ) );
			}
			break;
	}
}

$reg_user_cnt ['all'] = array_sum ( array_values ( $reg_user_cnt ) );

//$myTools ['all'] = get_lang ( 'TabViewAll' ) . '(' . ($reg_user_cnt ['all'] ? $reg_user_cnt ['all'] : 0) . ')';
//$myTools ['1'] = get_lang ( 'TabNewRegistration' ) . '(' . ($reg_user_cnt [0] ? $reg_user_cnt [0] : 0) . ')';
//$myTools ['2'] = get_lang ( 'TabAuditNotPass' ) . '(' . ($reg_user_cnt [1] ? $reg_user_cnt [1] : 0) . ')';
//$myTools ['3'] = get_lang ( 'TabAuditPass' ) . '(' . ($reg_user_cnt [2] ? $reg_user_cnt [2] : 0) . ')';
$myTools ['all'] = get_lang ( 'TabViewAll' ). '(' . ($reg_user_cnt ['all'] ? $reg_user_cnt ['all'] : 0) . ')';
$myTools ['1'] = get_lang ( 'TabNewRegistration' );
$myTools ['2'] = get_lang ( 'TabAuditNotPass' );
$myTools ['3'] = get_lang ( 'TabAuditPass' );
$strActionType = getgpc ( 'status', 'G' );
if (! $strActionType) $strActionType = '1';
$parameters ['status'] = $strActionType;

$form = new FormValidator ( 'search_simple', 'get', '', '', null, false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText'  ) );
$form->addElement ( 'select', 'status', get_lang ( 'Status1' ), $myTools );
$form->addElement ( 'submit', 'submit', get_lang ( 'Search' ), 'class="inputSubmit"' );


//$form->addElement('static','search_advanced_link',null,'&nbsp;&nbsp;<a href="user_list.php?search=advanced">'.get_lang('AdvancedSearch').'</a>');

$table = new SortableTable ( 'admin_users', 'get_number_of_users', 'get_user_data', 1, NUMBER_PAGE );
$table->set_additional_parameters ( $parameters );

$idx = 0;
$table->set_header ( $idx ++, '', false );
$table->set_header ( $idx ++, get_lang ( 'LoginName' ), false, null, array ('style' => 'width:10%' ) );
$table->set_header ( $idx ++, get_lang ( 'FirstName' ), false, null, array ('style' => 'width:10%' ) );
$table->set_header ( $idx ++, get_lang ( 'Email' ), false, null, array ('style' => 'width:13%' ) );
$table->set_header ( $idx ++, get_lang ( 'MobilePhone' ), false, null, array ('style' => 'width:13%' ) );
$table->set_header ( $idx ++, '学习卡号', false, null, array ('style' => 'width:12%' ) );
//$table->set_header (  $idx++, get_lang ( 'Status' ), false );
$table->set_header ( $idx ++, get_lang ( 'RegistrationTime' ), false, null, array ('style' => 'width:13%' ) );
$table->set_header ( $idx ++, get_lang ( 'RegIP' ), false, null, array ('style' => 'width:10%' ) );
$table->set_header ( $idx ++, get_lang ( 'CourseReqStatus' ), false, null, array ('style' => 'width:8%' ) );
$table->set_header ( $idx ++, get_lang ( 'Action' ), false, null, array ('style' => 'width:8%' ) );
//$table->set_column_filter ( 4, 'email_filter' );
//$table->set_column_filter ( 7, 'active_filter' );
//$table->set_column_filter ( 8, 'modify_filter' );
$table->set_form_actions ( array ('active' => get_lang ( 'UserActive' )//'unactive' => get_lang('UserUnactive'),'delete' => get_lang ( 'DeleteFromPlatform' ) 
));


if($platform==3){
    $nav='userlist';
}else{
    $nav='users';
}
?>
<aside id="sidebar" class="column <?=$nav?> open">
    <div id="flexButton" class="closeButton close">
    </div>
</aside>
<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; <a href="<?=URL_APPEDND;?>/main/admin/user/user_list_iframe.php">用户管理</a> &gt;审核用户</h4>
    <?php
     if($prompt){
        echo '<div class="managerSearch" style="color:red"><b>'.$prompt.'</b></div>';
        tb_close("user_list_audit.php");
     }
    ?>

    <div class="managerSearch">
        <form>
            <?php $form->display();?>
        </form>
    </div>
    <article class="module width_full hidden">
        <table cellspacing="0" cellpadding="0" class="p-table">
           <?php
            $table->display ();?>
        </table>
    </article>
</section>
</body>
</html>
