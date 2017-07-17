<?php
$language_file = array('admin');
$cidReset = true;

include_once ('../../inc/global.inc.php');
require_once (api_get_path(INCLUDE_PATH).'lib/mail.lib.inc.php');
require_once (api_get_path(LIBRARY_PATH).'dept.lib.inc.php');
$this_section=SECTION_EXAM;

api_protect_admin_script ();
/*$required_roles=array(ROLE_TRAINING_ADMIN);
if(validate_role_base_permision($required_roles)===FALSE){
	api_deny_access(TRUE);
}
$restrict_org_id=$_SESSION['_user']['role_restrict'][ROLE_TRAINING_ADMIN];*/

//部门数据
$deptObj = new DeptManager ( );

$this_module='position';
$tbl_category=Database::get_main_table ( TABLE_CATEGORY );
$table_position = Database :: get_main_table(TABLE_MAIN_SYS_POSITION);

if(isset($_REQUEST['id'])){
	$sql="SELECT * FROM ".$table_position." WHERE id=".Database::escape(intval(getgpc("id")));
	$item=Database::fetch_one_row($sql,false,__FILE__,__LINE__);
}

$htmlHeadXtra[]='<script type="text/javascript" src="'.api_get_path(WEB_JS_PATH).'commons.js"></script>';
$htmlHeadXtra[] = '
<script language="JavaScript" type="text/JavaScript">
<!--
//-->
</script>';
if(!empty($_GET['message'])){
	$message = urldecode(getgpc('message'));
}
//$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('Exam'));
$tool_name=get_lang('AddPosition');

$form = new FormValidator('position_ae');


$form->addElement ( 'header', 'header', is_equal($_GET['action'],'edit')?get_lang("EditPosition"):get_lang ( 'AddPosition' ) );
$form->addElement ( 'hidden', 'action', is_equal($_GET['action'],'edit')?'edit_save':'add_save' );
$form->addElement ( 'hidden', 'id', intval(getgpc('id')) );

$form->addElement('text', 'code', get_lang('PositionCode'),array('maxlength'=>60,'style'=>"width:70%",'class'=>'inputText'));
$form->addElement('text', 'name', get_lang('PositionName'),array('maxlength'=>60,'style'=>"width:70%",'class'=>'inputText'));
$form->addElement('text', 'en_name', get_lang('PositionEnName'),array('maxlength'=>60,'style'=>"width:70%",'class'=>'inputText'));
$form->addRule('name', get_lang('ThisFieldIsRequired'), 'required');

//分类
$sql = "SELECT * FROM " . $tbl_category . " WHERE parent_id=0 AND module='".$this_module."' ORDER BY sort_order";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$category_options=array();
while ( $row = Database::fetch_array ( $res, 'ASSOC' ) ) {
	$category_options[$row['id']]=$row['name'];
}
$form->addElement('select','category_id',get_lang("Category"),$category_options,array('style'=>"width: 50%;height:22px;"));
if(isset($_GET['category_id'])) $item['category_id']=  intval(getgpc('category_id','G'));

//所属机构
/*if(api_is_platform_admin()){
	$all_org=$deptObj->get_all_org();
	foreach($all_org as $org){
		$orgs[$org['id']]=$org['dept_name'];
	}
}else{
	$org_info=$deptObj->get_dept_info($restrict_org_id);
	$orgs[$restrict_org_id]=$org_info['dept_name'];
}
$form->addElement('select','org_id',get_lang('InOrg'),$orgs,array('id'=>"org_id",'style'=>'height:22px;width:25%'));
//$form->addRule('org_id', get_lang('ThisFieldIsRequired'), 'required');*/

$form->addElement('text', 'level', get_lang('PositionLevel'),array('maxlength'=>5,'style'=>"width:60px",'class'=>'inputText'));


//其它说明
if (api_get_setting('html_editor')=='simple') {
	$form->addElement('textarea', 'description', get_lang('Remark'),array('cols'=>50,'rows'=>5,'class'=>'inputText'));
} else {
	$fck_attribute['Width'] = '100%';
	$fck_attribute['Height'] = '100';
	$fck_attribute['ToolbarSet'] = 'Comment';
	$fck_attribute["ToolbarStartExpanded"]="false";
	$form->add_html_editor('description', get_lang('Remark'),false);
}

//提交按钮
$group = array ();
$group[] = $form->createElement('style_submit_button', 'submit', get_lang('Ok'), 'class="save"');
//$group[] = $form->createElement('style_button', 'button',get_lang('Back'), array('type'=>'button','value'=>get_lang("Back"),'class'=>"save",'onclick'=>'location.href=\'pool_list.php\';'));
$group[] =$form->createElement('style_button', 'cancle',null,array('type'=>'button','class'=>"cancel",'value'=>get_lang('Cancel'),'onclick'=>'javascript:self.parent.tb_remove();'));
$form->addGroup($group, 'submit', '&nbsp;', null, false);

$form->setDefaults($item);

Display::setTemplateBorder($form, '98%');

// Validate form
if( $form->validate())
{
	$data=$form->exportValues();
	$code=Database::escape_string($data['code']);
	$name=Database::escape_string($data['name']);
	$en_name=Database::escape_string($data['en_name']);
	$category_id=Database::escape_string($data['category_id']);
	$org_id=$data['org_id'];
	$desc=Database::escape_string($data['description']);
	if(is_equal($_REQUEST['action'],'add_save')){
		$sql_data=array('name'=>$name,'en_name'=>$en_name,'code'=>$code,'category_id'=>$category_id,'description'=>$desc,
				'created_date'=>date('Y-m-d H:i:s'),'org_id'=>$org_id);
		$sql=Database::sql_insert($table_position,$sql_data);
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );

		$redirect_url='position_list.php';
		echo '<script>self.parent.location.href="'.$redirect_url.'";self.parent.tb_remove();self.parent.refresh_tree();</script>';	exit;
		//api_redirect($redirect_url);
	}

	if(is_equal($_REQUEST['action'],'edit_save')){
		$id=  intval($data['id']);
		$sql_data=array('name'=>$name,'en_name'=>$en_name,'code'=>$code,'category_id'=>$category_id,'description'=>$desc,'org_id'=>$org_id);
		$sql=Database::sql_update($table_position,$sql_data," id=".Database::escape($id));
		$res = api_sql_query ( $sql, __FILE__, __LINE__ );

		$redirect_url='position_list.php';
		echo '<script>self.parent.location.href="'.$redirect_url.'";self.parent.tb_remove();self.parent.refresh_tree();</script>';	exit;
	}

}

Display::display_header($tool_name,FALSE);

if(!empty($message)){
	Display::display_normal_message(urldecode(stripslashes($message)));
}


$form->display();
Display::display_footer();
