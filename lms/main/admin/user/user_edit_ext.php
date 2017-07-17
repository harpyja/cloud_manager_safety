<?php
/**----------------------------------------------------------------

 liyu: 2012-2-20
 *----------------------------------------------------------------*/
$language_file = array ('admin', 'registration' );
$cidReset = true;
include_once ('../../inc/global.inc.php');
include_once ('../../inc/conf/user.conf.php');
api_protect_admin_script ();

require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
include_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

$table_user = Database::get_main_table ( TABLE_MAIN_USER );
$user_id = intval(getgpc ( 'user_id' ));
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

$sql = "SELECT u.* FROM $table_user u  WHERE u.user_id = '" . $user_id . "'";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
if (Database::num_rows ( $res ) != 1) {
	$redirect_url = "user_list.php";
	tb_close ( $redirect_url );
}
$user_data = Database::fetch_array ( $res, 'ASSOC' );
$form = new FormValidator ( 'user_edit', 'post', '', '' );
$form->addElement ( 'hidden', 'user_id', $user_id );

//$form->addElement ( 'calendar_datetime', 'birthday', '出生日期', array (), array ('show_time' => FALSE ) );
$form->addElement ( 'text', 'age', get_lang ( 'Age' ), array ('maxlength' => 20, 'style' => "width:250px", 'class' => 'inputText' ) );
$form->addElement ( 'text', 'qq', get_lang ( 'QQ' ), array ('maxlength' => 20, 'style' => "width:250px", 'class' => 'inputText' ) );
$form->addElement ( 'text', 'certificate_no_qualification', '资格证号', array ('maxlength' => 20, 'style' => "width:250px", 'class' => 'inputText' ) );
$form->addElement ( 'text', 'certificate_no_grade', '等级证号', array ('maxlength' => 20, 'style' => "width:250px", 'class' => 'inputText' ) );
//$form->addElement ( 'text', 'credit', '分值', array ('maxlength' => 20, 'style' => "width:250px", 'class' => 'inputText' ) );
$form->addElement ( 'select', 'grade', '等级', $_user_grade_options, array ('id' => "dept_id", 'style' => 'height:22px;' ) );
$form->addElement ( 'calendar_datetime', 'annual_auditing_date', '年审日期', array (), array ('show_time' => FALSE ) );
$form->addElement ( 'calendar_datetime', 'issue_date', '发卡日期', array (), array ('show_time' => FALSE ) );
$form->addElement ( 'select', 'lang', '语种', $_user_lang_options, array ('id' => "lang", 'style' => 'height:22px;' ) );
$form->addElement ( 'select', 'nation', '民族', $_user_nation_options, array ('id' => "nation", 'style' => 'height:22px;' ) );
$form->addElement ( 'select', 'academic', '学历', $_user_academic_options, array ('id' => "academic", 'style' => 'height:22px;' ) );
//$form->addElement ( 'text', 'company', '所在单位', array ('maxlength' => 20, 'style' => "width:250px", 'class' => 'inputText', 'readonly' => 'true' ) );
$form->addElement ( 'select', 'work_type', '工作性质', $_user_worktype_options, array ('id' => "work_type", 'style' => 'height:22px;' ) );
$form->addElement ( 'checkbox', 'is_sign_contract', '劳动合同', '有' );
$form->addElement ( 'checkbox', 'is_insurance1', '医疗保险金', '有' );
$form->addElement ( 'checkbox', 'is_insurance2', '养老保险金', '有' );
$form->addElement ( 'checkbox', 'is_insurance3', '失业保险金', '有' );
$form->addElement ( 'select', 'avoid_exam', '免考条件', $_user_avoid_exam_options , array ('id' => "lang", 'style' => 'height:22px;' ));

$group = array ();
$group [] = $form->createElement ( 'submit', 'submit', get_lang ( 'Ok' ), 'class="inputSubmit"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

$form->applyFilter ( '__ALL__', 'trim' );
$form->setDefaults ( $user_data );
Display::setTemplateBorder ( $form, '98%' );

if ($form->validate ()) {
	$user = $form->getSubmitValues ();
	$sql_row = array ('certificate_no_qualification' => $user ['certificate_no_qualification'], 
			'certificate_no_grade' => $user ['certificate_no_grade'], 
			'grade' => $user ['grade'], 
			'annual_auditing_date' => ($user ['annual_auditing_date']), 
			'issue_date' => ($user ['issue_date']), 
			//'birthday' => excelTime ( $user ['birthday'] ), 
			'age' => intval ( $user ['age'] ), 
			'qq' => $user ['qq'], 
			'lang' => $user ['lang'], 
			'credit' => $user ['credit'], 
			'nation' => $user ['nation'], 
			'academic' => $user ['academic'], 
			'company' => $user ['company'], 
			'work_type' => $user ['work_type'], 
			'avoid_exam' => $user ['avoid_exam'], 
			'is_sign_contract' => $user ['is_sign_contract'] ? 1 : 0, 
			'is_insurance1' => $user ['is_insurance1'] ? 1 : 0 ,
			'is_insurance2' => $user ['is_insurance2'] ? 1 : 0 ,
			'is_insurance3' => $user ['is_insurance3'] ? 1 : 0 ,
	
	);
	$sql = Database::sql_update ( $table_user, $sql_row, "user_id=" . Database::escape ( $user ['user_id'] ) );
	api_sql_query ( $sql, __FILE__, __LINE__ );
	api_redirect ( 'user_edit_ext.php?user_id=' . $user ['user_id'] );
}
Display::display_header ( '', FALSE );

$html = '<div id="demo" class="yui-navset">';
$html .= '<ul class="yui-nav">';
$html .= '<li><a href="user_edit.php?user_id=' . $user_id . '"><em>基本信息</em></a></li>';
$html .= '<li  class="selected"><a href="user_edit_ext.php?user_id=' . $user_id . '"><em>扩展信息</em></a></li>';
$html .= '</ul>';
$html .= '<div class="yui-content"><div id="tab1">';
echo $html;

$form->display ();

echo '</div></div></div>';
Display::display_footer ();