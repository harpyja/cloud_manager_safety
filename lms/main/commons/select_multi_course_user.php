<?php
$language_file = array ('registration', 'admin' );
require_once ("../inc/global.inc.php");
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course_class_manager.lib.php');

//CHECK KEYS
if (! isset ( $_cid )) {
	header ( "location: " . $_configuration ['root_web'] );
}

if (! $is_allowed_in_course) {
	api_not_allowed ();
}

$currentCourseID = $_course ['sysCode'];
$is_allowed_edit = api_is_allowed_to_edit ();

$htmlHeadXtra [] = '<script type="text/javascript" src="' . api_get_path ( WEB_JS_PATH ) . 'utility.js"></script>';
/*$htmlHeadXtra[]='<script Language="JavaScript">
 function Load_Do()
 {
 var parent_window = getOpenner();
 var TO_ID_STR = parent_window.'.getgpc('FORM_NAME').'.TO_ID.value;
 var TO_NAME_STR = parent_window.'.getgpc('FORM_NAME').'.TO_NAME.value;
 if(TO_ID_STR=="" || TO_NAME_STR=="")
 user.location="user.php?MODULE_ID=2&TO_ID=TO_ID&TO_NAME=TO_NAME&FORM_NAME=form1&MANAGE_FLAG=";
 else
 user.location="selected.php?MODULE_ID=2&TO_ID=TO_ID&TO_NAME=TO_NAME&FORM_NAME=form1";
 }
 </script>';*/
/*
if(isset($_GET['TO_ID_STR'])){
	$htmlHeadXtra[]='<script Language="JavaScript">
			var parent_window = getOpenner();
			var to_form = parent_window;
	</script>';
}else{
	$htmlHeadXtra[]='<script Language="JavaScript">
	var parent_window = getOpenner();
	var to_form = parent_window.'.getgpc('FORM_NAME').';	
	</script>';
}*/

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
		user_name=user_info[2]+"("+user_info[1]+")";
		if(TO_VAL.indexOf(","+user_id+",")>0 || TO_VAL.indexOf(user_id+",")==0)
		{
			if(TO_VAL.indexOf(user_id+",")==0)
				to_id.value=to_id.value.replace(user_id+",","");
			else if(TO_VAL.indexOf(","+user_id+",")>0)
				to_id.value=to_id.value.replace(","+user_id+",",",");

			if(TO_NAME.indexOf(user_name+",")==0)
				to_name.value=to_name.value.replace(user_name+",","");
			else if(TO_NAME.indexOf(","+user_name+",")>0)
				to_name.value=to_name.value.replace(","+user_name+",",",");

			borderize_off(target_element);
		}
		else
		{
			to_id.value+=user_id+",";
			to_name.value+=user_name+",";
			borderize_on(target_element);
		}
		//check_one(target_element);
	}

	function borderize_on(target_element)
	{
		targetelement=target_element.parentNode.parentNode;
		color="#FFFFCC";
		targetelement.style.backgroundColor=color;
		/*targetelement.style.borderColor="black";
		 targetelement.style.color="white";
		 targetelement.style.fontWeight="bold";*/
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
  					
          			borderize_on($$(cb_items[i].id));
          		}
          	}  			
  		}
  		if(cb_items.length==cnt_item){
  			if(document.getElementById("allbox_for"))
  				$$("allbox_for").checked=true;
  		}  		
	}
	
	</script>
';

$htmlHeadXtra [] = '<script language="JavaScript" type="text/javascript">
						/*<![CDATA[*/
																							
							function check_all(cb_name){						
										
  								var items0=document.getElementsByName(cb_name);  		
	 							for (var i=0;i<items0.length;i++) {
	 								target_element=items0[i];		 	
	 								target_element.checked=document.getElementById("allbox_for").checked;	 								
	 								if(document.getElementById("allbox_for").checked){
	 									user_info=target_element.value.split("##");
										user_name=user_info[2];
										to_id.value+=user_id+",";
										to_name.value+=user_name+",";
	 								}else{
	 									to_id.value="";
										to_name.value="";
	 								}
	 							}	 							
							}
							
							function check_one(el){
   								if(!el.checked){
      								document.getElementById("allbox_for").checked=false;  
      							}    							
							}
																
							/*]]>*/
				</script>';
Display::display_header ( $tool_name, FALSE );
?>
<body topmargin="1" leftmargin="0" class="bodycolor"
	onload="begin_set();">

<?

$html .= '<input type="checkbox" name="allbox" id="allbox_for" onClick="javascript:check_all(\'userinfo\');"><label for="allbox_for">' . get_lang ( 'SelectAll' ) . '/' . get_lang ( 'UnSelectAll' ) . '</label>&nbsp;&nbsp;';

$form = new FormValidator ( 'search_user', 'get', '', '_self', null, false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$form->add_textfield ( 'keyword', '', false, '  style="width:250px" class="inputText"' );
$form->addElement ( 'submit', 'submit', get_lang ( 'SearchButton' ), 'class="inputSubmit"' );
//$form->addElement('static','selectall','',$html);
$form->addElement ( 'hidden', 'FORM_NAME', getgpc ( 'FORM_NAME' ) );
$form->addElement ( 'hidden', 'TO_ID', getgpc ( 'TO_ID' ) );
$form->addElement ( 'hidden', 'TO_NAME', getgpc ( 'TO_NAME' ) );
$form->display (); //查询表单


display_course_user_list_paging ();

//echo $html;


/**
 * 带分页功能
 *
 */
function display_course_user_list_paging() {
	$table_header [] = array ();
	$table_header [] = array (get_lang ( 'LoginName' ), true );
	$table_header [] = array (get_lang ( 'FirstName' ), true );
	$table_header [] = array (get_lang ( 'OfficialCode' ), true );
	$table_header [] = array (get_lang ( 'UserType' ), true );
	$table_header [] = array (get_lang ( 'Class_of_course' ), true );
	$keyword = escape ( getgpc ( 'keyword' ) );
	
	$sqlwhere = " AND (username LIKE '%" . $keyword . "%' OR firstname LIKE '%" . $keyword . "%')";
	if (is_not_blank ( $_GET ['excludedUsers'] )) $sqlwhere .= " AND user_id NOT " . Database::create_in ( getgpc ( 'excludedUsers' ) );
	$all_course_users = CourseManager::get_course_user_list ( api_get_course_code (), $sqlwhere );
	$all_user_class_info = CourseManager::get_courseclass_rel_user ( api_get_course_code () );
	foreach ( $all_course_users as $user_id => $data ) {
		$row = array ();
		$course_class_info = $all_user_class_info [$user_id];
		
		$row [] = '<input type="checkbox" id="' . $data ['user_id'] . '" name="userinfo" value="' . $user_id . "##" . $data ['username'] . "##" . $data ['firstname'] . '" onclick="javascript:click_user(\'' . $data ['user_id'] . '\');"/>';
		$row [] = $data ['username'];
		$row [] = $data ['firstname'];
		$row [] = $data ['official_code'];
		$row [] = $data ['status'] == STUDENT ? get_lang ( 'Student' ) : get_lang ( 'Teacher' );
		$row [] = $course_class_info ['name'];
		$table_data [] = $row;
	}
	unset ( $data, $row );
	
	$sorting_options = array ('column' => 0, 'default_order_direction' => 'ASC' );
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
	//var_dump($query_vars);
	Display::display_sortable_table ( $table_header, $table_data, $sorting_options, array (), $query_vars );
}

function display_course_user_list() {
	
	$table_header [] = '';
	$table_header [] = get_lang ( 'LoginName' );
	$table_header [] = get_lang ( 'FirstName' );
	$table_header [] = get_lang ( 'OfficialCode' );
	$table_header [] = get_lang ( 'UserType' );
	$table_header [] = get_lang ( 'Class_of_course' );
	Display::display_complex_table_header ( $properties, $table_header );
	//$all_course_users=get_user_data();
	$all_course_users = CourseManager::get_user_list_from_course_code ( $_SESSION ['_course'] ['id'] );
	$all_user_class_info = CourseManager::get_courseclass_rel_user ( $_SESSION ['_course'] ['id'] );
	foreach ( $all_course_users as $user_id => $data ) {
		$row = array ();
		$course_class_info = $all_user_class_info [$user_id];
		
		$row [] = '<input type="checkbox" name="userinfo" value="' . $user_id . "##" . $data ['firstname'] . '"/>';
		$row [] = $data ['username'];
		$row [] = $data ['firstname'];
		$row [] = $data ['official_code'];
		$row [] = $data ['status'] == STUDENT ? get_lang ( 'Student' ) : get_lang ( 'Teacher' );
		$row [] = $course_class_info ['name'];
		
		Display::display_alternating_table_row ( $row, $row_index % 2 );
	}
}

?>
</body>
</html>
