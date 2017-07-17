<?php
$language_file = array ('registration', 'admin' );
require_once ("../inc/global.inc.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');

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
		user_name=user_info[0];
		
			to_id.value=user_id;
			to_name.value=user_name;
			var items0=document.getElementsByName("userinfo");
	 		for (var i=0;i<items0.length;i++) {
	 			borderize_off(items0[i]);
	 		}
			borderize_on(target_element);
			
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
	
	</script>
';

Display::display_header($tool_name,FALSE);
?>
<body topmargin="1" leftmargin="0" class="bodycolor"
	onlode="javascript:begin_set();">
<?

//$html.='<input type="checkbox" name="allbox" id="allbox_for" onClick="javascript:check_all(\'userinfo\');"><label for="allbox_for">'.get_lang('SelectAll').'/'.get_lang('UnSelectAll').'</label>&nbsp;&nbsp;';


$form = new FormValidator ( 'search_user', 'get', '', '_self', null, false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$form->add_textfield ( 'keyword', '', false, '  style="width:200px" class="inputText"' );
$form->addElement ( 'submit', 'submit', get_lang ( 'SearchButton' ), 'class="inputSubmit"' );
//$form->addElement('static','selectall','',$html);
$form->addElement ( 'hidden', 'FORM_NAME', getgpc ( 'FORM_NAME' ) );
$form->addElement ( 'hidden', 'TO_ID', getgpc ( 'TO_ID' ) );
$form->addElement ( 'hidden', 'TO_NAME', getgpc ( 'TO_NAME' ) );
$form->display (); //查询表单


display_course_list_paging ();


//echo $html;


/**
 * 带分页功能
 *
 */
function display_course_list_paging() {
	
	$table_header [] = array ();
	$table_header [] = array (get_lang ( 'CourseTitle' ), true );
	$table_header [] = array (get_lang ( 'Code' ), true );
	$table_header [] = array (get_lang ( 'CourseTitular' ), true );
	$table_header [] = array (get_lang ( 'CourseAdmin' ), true );
	
	$all_courses = get_course_data ();
	foreach ( $all_courses as $data ) {
		$row = array ();
		$row [] = '<input type="radio" id="' . $data ['code'] . '" name="userinfo" value="' . $data ['title'] . "##" . $data ['code'] . '" onclick="javascript:click_user(\'' . $data ['code'] . '\');" ondblclick="javascript:dbl_click_user(\'' . $data ['code'] . '\');" />';
		$row [] = $data ['title'];
		$row [] = $data ['code'];
		$row [] = $data ['tutor_name'];
		$row [] = $data ['tutor_name'];
		$table_data [] = $row;
	}
	unset ( $data, $row );
	
	$sorting_options = array ('column' => 0, 'default_order_direction' => 'ASC' );
	$paging_options = array ('is_display_jump2page_html' => 'false', 'is_display_pagesize_html' => '' );
	$query_string = $_SERVER ["QUERY_STRING"];
	$params = explode ( '&', $_SERVER ['QUERY_STRING'] );
	//var_dump($params);
	$tablename = isset ( $paging_options ['tablename'] ) ? $paging_options ['tablename'] : 'talbename'; //表格名
	$exclude_params = array ($tablename . "_direction", $tablename . "_page_nr", $tablename . "_per_page", $tablename . "_column", "submit" );
	$query_vars = array ();
	foreach ( $params as $param ) {
		list ( $key, $value ) = explode ( '=', $param );
		if (! in_array ( $key, $exclude_params )) $query_vars [$key] = $value;
	}
	//var_dump($query_vars);
	Display::display_sortable_table ( $table_header, $table_data, $sorting_options, $paging_options, $query_vars, null, NAV_BAR_BOTTOM );
}


function get_course_data($condition = '') {
	$keyword = Database::escape_string ( getgpc ( 'keyword' ) );
	//$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	$course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
	
	$sql = "SELECT code,visual_code,title,category_code,
	CONCAT('" . get_lang ( "From" ) . " ',DATE_FORMAT(start_date,'%y-%m-%d'),' " . get_lang ( "To" ) . " ',DATE_FORMAT(expiration_date,'%y-%m-%d')) AS duration,
	subscribe,unsubscribe,tutor_name,IF(fee>0,fee,'" . get_lang ( "Free" ) . "') as free FROM $course_table";
	if (isset ( $_GET ['keyword'] )) {
		$keyword = trim ( Database::escape_string ( $_GET ['keyword'] ) );
		$sql .= " WHERE title LIKE '%" . $keyword . "%' OR code LIKE '%" . $keyword . "%'";
	}
	
	$sql .= " ORDER BY code";
	//echo $sql;
	

	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$courses = array ();
	//$objDept=new DeptManager();
	while ( $course = Database::fetch_array ( $res, 'ASSOC' ) ) {
		//$objDept->dept_path="";
		//$user[5]=$objDept->get_dept_path($user[5],FALSE);
		$courses [] = $course;
	}
	return $courses;
}

?>
</body>
</html>
