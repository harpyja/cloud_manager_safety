<?php
$language_file = array ('registration', 'admin' );
require_once ("../inc/global.inc.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course_class_manager.lib.php');

$htmlHeadXtra [] = '<script type="text/javascript" src="' . api_get_path ( WEB_JS_PATH ) . 'utility.js"></script>';
$htmlHeadXtra [] = '<script Language="JavaScript">
	var parent_window = getOpenner();
	var to_form = parent_window.' . getgpc ( 'FORM_NAME' ) . ';	

	var to_id =   to_form.' . getgpc ( 'TO_ID' ) . ';
	var to_name = to_form.' . getgpc ( 'TO_NAME' ) . ';
	
	function click_user(user_id)
	{
		TO_VAL=to_id.value;
		TO_NAME=to_name.value;
		target_element=$$(user_id);
		user_info=target_element.value.split("##");
		user_name=user_info[2];
		/*if(TO_VAL==user_id || TO_VAL==user_id)
		{			
			borderize_off(target_element);
		}
		else
		{*/
			to_id.value=user_id;
			to_name.value=user_name;
			var items0=document.getElementsByName("userinfo");  		
	 		for (var i=0;i<items0.length;i++) {	 			
	 			borderize_off(items0[i]);
	 		}		 	
			borderize_on(target_element);
		//}		
	}
	
	function dbl_click_user(user_id){
		click_user(user_id);
		top.window.close();
	}

	function borderize_on(target_element)
	{
		targetelement=target_element.parentNode.parentNode;
		color="#FFFFCC";
		targetelement.style.backgroundColor=color;
	}

	function borderize_off(target_element)
	{
		targetelement=target_element.parentNode.parentNode;
		targetelement.style.backgroundColor="";
		targetelement.style.borderColor="";
		targetelement.style.color="";
		targetelement.style.fontWeight="";
	}

	function begin_set()
	{
  		TO_VAL=to_id.value;
  		  		  
		var to_val_items=TO_VAL.split(",");
  		var cb_items=document.getElementsByName("userinfo");
  		
  		var cnt_item=0;
  		for(var i=0;i<cb_items.length;i++){  			
  			if(cb_items[i].value){
  				user_info=cb_items[i].value.split("##");
  				user_id=user_info[0];
  				if(TO_VAL.indexOf(","+user_id+",")>0 || TO_VAL.indexOf(user_id+",")==0){
  					cb_items[i].checked=true;
  					cnt_item++;
          			borderize_on($(cb_items[i].id));
          		}
          	}  			
  		}
  		if(cb_items.length==cnt_item){
  			//document.getElementById("allbox_for").checked=true;
  		}  		
	}
	
	</script>';
Display::display_header ( '', FALSE );

$deptObj = new DeptManager ();
?>
<body topmargin="1" leftmargin="0" class="bodycolor"
	onlode="javascript:begin_set();">
<?

//$html.='<input type="checkbox" name="allbox" id="allbox_for" onClick="javascript:check_all(\'userinfo\');"><label for="allbox_for">'.get_lang('SelectAll').'/'.get_lang('UnSelectAll').'</label>&nbsp;&nbsp;';


$form = new FormValidator ( 'search_user', 'get', '', '_self', null, false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$form->add_textfield ( 'keyword', '', false, '  style="width:200px" class="inputText"' );

$all_course_class=CourseClassManager::get_all_classes_info (null,false);
$all_course_class = array_insert_first ( $all_course_class, array ('' => '所有课程班级' ) );
$form->addElement ( 'select', 'class_id', null, $all_course_class );

$form->addElement ( 'hidden', 'FORM_NAME', getgpc ( 'FORM_NAME' ) );
$form->addElement ( 'hidden', 'TO_ID', getgpc ( 'TO_ID' ) );
$form->addElement ( 'hidden', 'TO_NAME', getgpc ( 'TO_NAME' ) );
$form->addElement ( 'hidden', 'status', getgpc ( 'status' ) );

$form->addElement ( 'submit', 'submit', get_lang ( 'SearchButton' ), 'class="inputSubmit"' );
$form->display (); //查询表单


if (getgpc ( 'pid' ) != NULL and getgpc ( 'pid' ) != "0") {
	$dept_path = $deptObj->get_dept_path ( intval(getgpc ( 'pid' )), TRUE );
	echo "<p>" . get_lang ( "CurrentDept" ) . ": " . $dept_path . ",&nbsp;&nbsp;";
}

$table_header [] = array ();
$table_header [] = array (get_lang ( 'LoginName' ), true );
$table_header [] = array (get_lang ( 'FirstName' ), true );
$table_header [] = array (get_lang ( 'OfficialCode' ), true );
$table_header [] = array (get_lang ( 'Class_of_course' ), true );
// $table_header [] = array (get_lang ( 'UserType' ), true );
$table_header [] = array (get_lang ( 'UserInDept' ), true );

$all_users = get_user_data ();
foreach ( $all_users as $data ) {
	$row = array ();
	$row [] = '<input type="radio" id="' . $data ['user_id'] . '" name="userinfo" value="' . $data['user_id'] . "##" . $data ['username'] . "##" . $data ['firstname'] . '" onclick="javascript:click_user(\'' . $data ['user_id'] . '\');" ondblclick="javascript:dbl_click_user(\'' . $data ['user_id'] .
			 '\');" />';
	$row [] = $data ['username'];
	$row [] = $data ['firstname'];
	$row [] = $data ['official_code'];
	$row [] =$all_course_class[$data ['class_id']];
	//$row [] = $data ['user_type'];
	//$row[] = $deptObj->get_dept_path($data['dept_id'],FALSE);
	$row [] = $data ['dept_name'];
	$table_data [] = $row;
}
unset ( $data, $row );

$sorting_options = array ('column' => 0, 'default_order_direction' => 'ASC' );
$paging_options = array ('is_display_jump2page_html' => 'false', 'is_display_pagesize_html' => '' );
$query_string = $_SERVER ["QUERY_STRING"];
$params = explode ( '&', $_SERVER ['QUERY_STRING'] );
//var_dump($params);
$tablename = isset ( $paging_options ['tablename'] ) ? $paging_options ['tablename'] : 'tablename'; //表格名
$exclude_params = array ($tablename . "_direction", $tablename . "_page_nr", $tablename . "_per_page", $tablename . "_column", "submit", "cidReq" );
$query_vars = array ();
foreach ( $params as $param ) {
	list ( $key, $value ) = explode ( '=', $param );
	if (! in_array ( $key, $exclude_params )) $query_vars [$key] = $value;
}
//$query_vars['status']=getgpc('status');
//var_dump($query_vars);
Display::display_sortable_table ( $table_header, $table_data, $sorting_options, $paging_options, $query_vars, null, NAV_BAR_BOTTOM );

function get_user_data($condition = '') {
	global $deptObj;
	
	$sqlwhere = "";
	if (isset ( $_GET ['keyword'] ) && is_not_blank ( $_GET ['keyword'] )) {
		$keyword = trim ( Database::escape_string ( $_GET ['keyword'] ) );
		$sqlwhere .= " AND (user.firstname LIKE '%" . $keyword . "%'  OR user.username LIKE '%" . $keyword . "%')";
	}
	
	if (is_not_blank ( $_GET ['class_id'] )) {
		$sqlwhere .= " AND class_id=" . Database::escape ( intval(getgpc ( 'class_id', 'G' ) ));
	}
	
	if (is_not_blank ( $_GET ['status'] )) {
		$sqlwhere .= " AND user.status='" . Database::escape_string ( getgpc ( 'status', 'G' ) ) . "'";
	}
	
	if (getgpc ( 'pid' ) != NULL and getgpc ( 'pid' ) != "0") {
		//$sql .=" AND dept_id='".Database::escape_string(getgpc('pid'))."'";
		$dept_id = intval ( Database::escape_string ( getgpc ( 'pid', 'G' ) ) );
		$dept_sn = $deptObj->get_sub_dept_sn ( $dept_id );
		if ($dept_sn) $sqlwhere .= " AND user.dept_sn LIKE '" . $dept_sn . "%'";
	}
	
	$users = api_get_course_users ( api_get_course_code (), $sqlwhere );
	
	return $users;
}

?>
</body>
</html>
