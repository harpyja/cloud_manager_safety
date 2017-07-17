<?php
$language_file = array ('course_info', 'admin', 'create_course', 'course_description' );
$cidReset = true;
include_once ("../../inc/global.inc.php");
api_protect_admin_script ();

require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'add_course.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( SYS_CODE_PATH ) . "admin/admin.lib.inc.php");

//课程默认过期时间
$firstExpirationDelay = 31536000; // <- 86400*365    // 60*60*24 = 1 jour = 86400


$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$table_course_requisition = Database::get_main_table ( TABLE_MAIN_COURSE_REQUISITION );
$tool_name = get_lang ( 'AddCourse' );
$interbreadcrumb [] = array ("url" => api_get_path ( WEB_ADMIN_PATH ) . 'index.php', "name" => get_lang ( 'PlatformAdmin' ) );

$htmlHeadXtra [] = '<script type="text/javascript">
	$(document).ready( function() {
		/*$("#upload_max_filesize").parent().append("<div class=\'onShow\'>' . get_lang ( 'UploadFileSizeLessThan' ) . get_upload_max_filesize ( 0 ) . 'M</div>");*/
		$("#visual_code").parent().append("<div class=\'onShow\'>' . get_lang ( 'AddCourseCodeTip' ) . '</div>");
		$("#credit").parent().append("<div class=\'onShow\'>' . get_lang ( 'CreditTip' ) . '</div>");
		$("#credit_hours").parent().append("<div class=\'onShow\'>' . get_lang ( 'CreditHoursTip' ) . '</div>");

		$("#org_id").attr("disabled","true");
		//$("#payment").attr("disabled","true");
		//$("#payment").hide();

		$("#is_shared1").click(function(){
			$("#org_id").attr("disabled","true");
			$.get("' . api_get_path ( WEB_CODE_PATH ) . 'admin/ajax_actions.php",
				{action:"option_get_org_course_category",org_id:"-1",empty_top:"false"},
				function(data,textStatus){
					$("#category_code").html(data);
				});
		});

		$("#is_shared0").click(function(){
			$("#org_id").removeAttr("disabled");
			$.get("' . api_get_path ( WEB_CODE_PATH ) . 'admin/ajax_actions.php",
				{action:"option_get_org_course_category",org_id:$("#org_id").val(),empty_top:"false"},
				function(data,textStatus){
					$("#category_code").html(data);
				});
		});

		$("#org_id").change(function(){
			$.get("' . api_get_path ( WEB_CODE_PATH ) . 'admin/ajax_actions.php",
				{action:"option_get_org_course_category",org_id:$("#org_id").val(),empty_top:"false"},
				function(data,textStatus){
					$("#category_code").html(data);
				});
		});
	});
	</script>';

$htmlHeadXtra [] = '<script language="JavaScript" type="text/JavaScript">
function fee_switch_radio_button(form, input){
	var NodeList = document.getElementsByTagName("input");
	for(var i=0; i< NodeList.length; i++){
		if(NodeList.item(i).name=="fee[is_free]"  && NodeList.item(i).value=="0"){
			NodeList.item(i).checked=true;
			document.getElementById("is_audit_enabled").checked=false;
		}
	}
}
</script>';
//dengxin
$htmlHeadXtra [] = '<script type="text/javascript">
	$(document).ready( function() {
		$("#org_id").change(function(){
			$.get("' . api_get_path ( WEB_CODE_PATH ) . 'admin/ajax_actions.php",
				{action:"options_get_all_sub_depts",org_id:$("#org_id").val()},
				function(data,textStatus){
					//alert(data);
					$("#dept_id").html(data);
				});
		});
	});
</script>';

$htmlHeadXtra [] = '
<script src="jquery.js" type="text/javascript"></script>
<script language="JavaScript" type="text/JavaScript">

var name = "category_code";

function getarea(){
 var region_id = $("#category_code").val();//获得下拉框中大区域的值

 if(region_id != ""){
  $.ajax({
  type: "post",
  url: "syllabus_check.php",
  data:"region_id="+region_id,
  cache:false,
  beforeSend: function(XMLHttpRequest){
  },
  success: function(data, textStatus){

    //alert(data);
    $("#course").empty();//清空area下拉框
    $("#course").append(data);//给area下拉框添加option

  },
  complete: function(XMLHttpRequest, textStatus){
  },
  error: function(){
   //请求出错处理
  }
 });
 }
}
</script>';



function credit_range_check($inputValue) {
    return (intval ( $inputValue ) > 0);
}

function credit_hours_range_check($inputValue) {
    return (intval ( $inputValue ) > 0);
}

function fee_check($inputValue) {

    if (isset ( $inputValue ) && is_array ( $inputValue )) {
        if ($inputValue ['is_free'] == '0') {
            return floatval ( $inputValue ['payment'] ) > 0;
        } else {
            return true;
        }
    }
    return false;
}

function upload_max_filesize_check($inputValue) {
    return (intval ( $inputValue ) > 0 && intval ( $inputValue ) <= get_upload_max_filesize ( 0 ));
}



$deptObj = new DeptManager ();
$objCrsMng = new CourseManager ();

$form = new FormValidator ( 'update_course' );

$objCrsMng = new CourseManager ();
$category_tree = $objCrsMng->get_all_categories_treei ( TRUE );
foreach ( $category_tree as $item ) {
    $parent_cate_option [$item ['id']] = str_repeat ( "&nbsp;&nbsp;&nbsp;&nbsp;", intval ( $item ['level'] ) + 1 ) . $item ['name'];
}
$type =array("必修","选修");
array_push($parent_cate_option,'请选择课程分类');
arsort($parent_cate_option);

$form->addElement ( 'select', 'category_code', get_lang ( "CourseFaculty" ), $parent_cate_option, array ('id' => "category_code", 'style' => 'height:22px;', 'onChange' => "getarea()") );
//$form->addElement ( 'select', 'category_code', get_lang ( "CourseFaculty" ), $parent_cate_option, array ('id' => "category_code", 'style' => 'height:22px;', 'onChange' => "changeselect1(this.value)") );

//dengxin  area
$form->addElement ( 'select', 'course', get_lang ( "课程" ), $vms, array ('id' => "course", 'style' => 'height:22px;') );
$form->addRule ( 'course', get_lang ( 'ThisFieldIsRequired' ), 'required' );

$form->addElement ( 'select', 'type', get_lang ( "类型" ), $type, array ('id' => "type", 'style' => 'height:22px;') );

$form->addElement ( 'text','week', get_lang ( '起止周' ), true, array ('style' => "width:10%", 'class' => 'inputText' ) );


$form->addElement ( 'text','class', get_lang ( '班级' ), true, array ('style' => "width:10%", 'class' => 'inputText' ) );

$form->addElement ( 'text','professional', get_lang ( '专业' ), true, array ('style' => "width:10%", 'class' => 'inputText' ) );


$form->addElement ( 'text','credit', get_lang ( '学分' ), true, array ('style' => "width:10%", 'class' => 'inputText' ) );

$form->addElement ( 'text','sum_credit_hours', get_lang ( '总学时' ), true, array ('style' => "width:10%", 'class' => 'inputText' ) );


$form->addElement ( 'text','credit_hours', get_lang ( '学时' ), true, array ('style' => "width:10%", 'class' => 'inputText' ) );


$form->addElement ( 'text','teacher', get_lang ( '讲师' ), true, array ('style' => "width:10%", 'class' => 'inputText' ) );

$form->addElement ( 'text','room', get_lang ( '教室' ), true, array ('style' => "width:10%", 'class' => 'inputText' ) );

$form->addElement ( 'text','number_of_people', get_lang ( '人数' ), true, array ('style' => "width:10%", 'class' => 'inputText' ) );

$form->addElement ( 'text','remarks', get_lang ( '备注' ), true, array ('style' => "width:10%", 'class' => 'inputText' ) );


$form->addElement ( 'checkbox', 'monday','星期一','', array ('id' => 'mon' ) );
$group = array ();
$group [] =  $form->createElement ( 'checkbox', 'class1', null,'第一节', array ('id' => 'mon1' ) );
$group [] =  $form->createElement ( 'checkbox', 'class2', null,'第二节', array ('id' => 'mon2' ) );
$group [] =  $form->createElement ( 'checkbox', 'class3', null,'第三节', array ('id' => 'mon3' ) );
$group [] =  $form->createElement ( 'checkbox', 'class4', null,'第四节', array ('id' => 'mon4' ) );
$group [] =  $form->createElement ( 'checkbox', 'class5', null,'晚自习', array ('id' => 'mon5' ) );
$form->addGroup ( $group, 'mon', '选择课节', '&nbsp;' );


$form->addElement ( 'checkbox', 'tuesday','星期二','', array ('id' => 'tue' ) );
$group = array ();
$group [] =  $form->createElement ( 'checkbox', 'class1', null,'第一节', array ('id' => 'tue1' ) );
$group [] =  $form->createElement ( 'checkbox', 'class2', null,'第二节', array ('id' => 'tue2' ) );
$group [] =  $form->createElement ( 'checkbox', 'class3', null,'第三节', array ('id' => 'tue3' ) );
$group [] =  $form->createElement ( 'checkbox', 'class4', null,'第四节', array ('id' => 'tue4' ) );
$group [] =  $form->createElement ( 'checkbox', 'class5', null,'晚自习', array ('id' => 'tue5' ) );
$form->addGroup ( $group, 'tue', '选择课节', '&nbsp;' );

$form->addElement ( 'checkbox', 'wednesday','星期三','', array ('id' => 'wed' ) );
$group = array ();
$group [] =  $form->createElement ( 'checkbox', 'class1', null,'第一节', array ('id' => 'wed1' ) );
$group [] =  $form->createElement ( 'checkbox', 'class2', null,'第二节', array ('id' => 'wed2' ) );
$group [] =  $form->createElement ( 'checkbox', 'class3', null,'第三节', array ('id' => 'wed3' ) );
$group [] =  $form->createElement ( 'checkbox', 'class4', null,'第四节', array ('id' => 'wed4' ) );
$group [] =  $form->createElement ( 'checkbox', 'class5', null,'晚自习', array ('id' => 'web5' ) );
$form->addGroup ( $group, 'wed', '选择课节', '&nbsp;' );

$form->addElement ( 'checkbox', 'thursday','星期四','', array ('id' => 'thu' ) );
$group = array ();
$group [] =  $form->createElement ( 'checkbox', 'class1', null,'第一节', array ('id' => 'thu1' ) );
$group [] =  $form->createElement ( 'checkbox', 'class2', null,'第二节', array ('id' => 'thu2' ) );
$group [] =  $form->createElement ( 'checkbox', 'class3', null,'第三节', array ('id' => 'thu3' ) );
$group [] =  $form->createElement ( 'checkbox', 'class4', null,'第四节', array ('id' => 'thu4' ) );
$group [] =  $form->createElement ( 'checkbox', 'class5', null,'晚自习', array ('id' => 'thu5' ) );
$form->addGroup ( $group, 'thu', '选择课节', '&nbsp;' );



$form->addElement ( 'checkbox', 'friday','星期五','', array ('id' => 'fri' ) );
$group = array ();
$group [] =  $form->createElement ( 'checkbox', 'class1', null,'第一节', array ('id' => 'fri1' ) );
$group [] =  $form->createElement ( 'checkbox', 'class2', null,'第二节', array ('id' => 'fri2' ) );
$group [] =  $form->createElement ( 'checkbox', 'class3', null,'第三节', array ('id' => 'fri3' ) );
$group [] =  $form->createElement ( 'checkbox', 'class4', null,'第四节', array ('id' => 'fri4' ) );
$group [] =  $form->createElement ( 'checkbox', 'class5', null,'晚自习', array ('id' => 'fri5' ) );
$form->addGroup ( $group, 'fri', '选择课节', '&nbsp;' );

$form->addElement ( 'checkbox', 'saturday','星期六','', array ('id' => 'sat' ) );
$group = array ();
$group [] =  $form->createElement ( 'checkbox', 'class1', null,'第一节', array ('id' => 'sat1' ) );
$group [] =  $form->createElement ( 'checkbox', 'class2', null,'第二节', array ('id' => 'sat2' ) );
$group [] =  $form->createElement ( 'checkbox', 'class3', null,'第三节', array ('id' => 'sat3' ) );
$group [] =  $form->createElement ( 'checkbox', 'class4', null,'第四节', array ('id' => 'sat4' ) );
$group [] =  $form->createElement ( 'checkbox', 'class5', null,'晚自习', array ('id' => 'sat5' ) );
$form->addGroup ( $group, 'sat', '选择课节', '&nbsp;' );

$form->addElement ( 'checkbox', 'sunday','星期日','', array ('id' => 'sun' ) );
$group = array ();
$group [] = $form->createElement ( 'checkbox', 'class1', null,'第一节', array ('id' => 'sun1' ) );
$group [] =  $form->createElement ( 'checkbox', 'class2', null,'第二节', array ('id' => 'sun2' ) );
$group [] =  $form->createElement ( 'checkbox', 'class3', null,'第三节', array ('id' => 'sun3' ) );
$group [] =  $form->createElement ( 'checkbox', 'class4', null,'第四节', array ('id' => 'sun4' ) );
$group [] =  $form->createElement ( 'checkbox', 'class5', null,'晚自习', array ('id' => 'sun5' ) );
$form->addGroup ( $group, 'sun', '选择课节', '&nbsp;' );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Ok' ), 'class="add"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, 'submit', '&nbsp;', null, false );

//$values ["default_learing_days"] = DEFAULT_LEARNING_DAYS;
//$mon = unserialize($default['mon']);
//$default['mon']['monday'] = $mon['monday'];

//$form->setDefaults ( $default );
$form->add_progress_bar ();
Display::setTemplateBorder ( $form, '100%' );

if ($form->validate ()) {

    $syllabus = $form->getSubmitValues ();

    $category_code = $syllabus['category_code'];
    $course_code = $syllabus ['course'];
    $type = $syllabus ['type'];
    $class = $syllabus ['class'];
    $week = $syllabus ['week'];
    $credit = $syllabus['credit'];
    $sum_credit_hours = $syllabus['sum_credit_hours'];
    $credit_hours = $syllabus['credit_hours'];
    $teacher = $syllabus['teacher'];
    $room = $syllabus['room'];
    $remarks = $syllabus['remarks'];
    $number_of_people = $syllabus['number_of_people'];
    $professional = $syllabus['professional'];

    $syllabus['mon']['monday']=$syllabus['monday'];
    $syllabus['tue']['tuesday']=$syllabus['tuesday'];
    $syllabus['wed']['wednesday']=$syllabus['wednesday'];
    $syllabus['thu']['thursday']=$syllabus['thursday'];
    $syllabus['fri']['friday']=$syllabus['friday'];
    $syllabus['sat']['saturday']=$syllabus['saturday'];
    $syllabus['sun']['sunday']=$syllabus['sunday'];

    $mon = serialize($syllabus['mon']);
    $tue = serialize($syllabus['tue']);
    $wed = serialize($syllabus['wed']);
    $thu = serialize($syllabus['thu']);
    $fri = serialize($syllabus['fri']);
    $sat = serialize($syllabus['sat']);
    $sun = serialize($syllabus['sun']);


    $syllabus_data = array (
        'category_code' => $category_code,
        'code' => $course_code,
        'type' => $type,
        'class' => $class,
        'week' => $week,
        'credit' => $credit,
        'sum_credit_hours' => $sum_credit_hours ,
        'credit_hours' => $credit_hours ,
        'teacher' => $teacher ,
        'room' => $room  ,
        'remarks' => $remarks  ,
        'number_of_people'=> $number_of_people,
        'professional' => $professional  ,
        'mon' => $mon,
        'tue' => $tue,
        'wed' => $wed,
        'thu' => $thu,
        'fri' => $fri,
        'sat' => $sat,
        'sun' => $sun,
    );
    $table_syllabus = Database::get_main_table (syllabus);
    //$result = create_course ( $code, "", api_get_user_id (), $course_admin, $title, $course_data );
    $sql = Database::sql_insert ( $table_syllabus, $syllabus_data );
    //echo $sql;
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );

    $redirect_url = "../syllabus/syllabus_list.php";
    tb_close ( $redirect_url );

}
//chanzgf
$htmlHeadXtra [] = '<script type="text/javascript">
	$(document).ready( function() {
		$("tr.containerBody:eq(14)").hide();
		$("tr.containerBody:eq(16)").hide();
		$("tr.containerBody:eq(18)").hide();
		$("tr.containerBody:eq(20)").hide();
		$("tr.containerBody:eq(22)").hide();
		$("tr.containerBody:eq(24)").hide();
		$("tr.containerBody:eq(26)").hide();

		$("#mon").click(function(){
			if($("#mon").attr("checked")){ $("tr.containerBody:eq(14)").show();
			}else{ $("tr.containerBody:eq(14)").hide(); }
		});
		$("#tue").click(function(){
		    if($("#tue").attr("checked")){ $("tr.containerBody:eq(16)").show();
			}else{ $("tr.containerBody:eq(16)").hide(); }
		});
		$("#wed").click(function(){
			if($("#wed").attr("checked")){ $("tr.containerBody:eq(18)").show();
			}else{ 	$("tr.containerBody:eq(18)").hide(); }
		});
		$("#thu").click(function(){
			if($("#thu").attr("checked")){ $("tr.containerBody:eq(20)").show();
			}else{ $("tr.containerBody:eq(20)").hide(); }
		});
		$("#fri").click(function(){
			if($("#fri").attr("checked")){ $("tr.containerBody:eq(22)").show();
			}else{ $("tr.containerBody:eq(22)").hide(); }
		});
		$("#sat").click(function(){
			if($("#sat").attr("checked")){ $("tr.containerBody:eq(24)").show();
			}else{ $("tr.containerBody:eq(24)").hide(); }
		});
		$("#sun").click(function(){
			if($("#sun").attr("checked")){ $("tr.containerBody:eq(26)").show();
			}else{ $("tr.containerBody:eq(26)").hide(); }
		});

	});</script>';

Display::display_header ( null, FALSE );
$form->display ();
Display::display_footer ();
?>
