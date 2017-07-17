<?php
/*
 ==============================================================================
 教学大纲的编辑与显示
 ==============================================================================
 */
$language_file = array ('course_description' );
include_once ('../inc/global.inc.php');
api_protect_course_script ();

include_once ('desc.inc.php');
if (! $allowed_to_edit) api_not_allowed ();

$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );

$htmlHeadXtra [] = Display::display_thickbox ( TRUE );
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) );
$htmlHeadXtra [] = '<script type="text/javascript">$(document).ready( function() {$("body").addClass("yui-skin-sam");});</script>';

Display::display_header(null,FALSE);

$description_id = isset ( $_REQUEST ['description_id'] ) ? intval ( getgpc ( 'description_id' ) ) : 0;

$sql = "SELECT description, description1, description2,description3,description4,description5,description6,description7,description8,description9,description10,description11,description12,description13 FROM " . $tbl_course . " WHERE code=" . Database::escape ( api_get_course_code () );
list ( $description, $description1, $description2,$description3,$description4,$description5,$description6,$description7,$description8,$description9,$description10,$description11,$description12,$description13 ) = api_sql_query_one_row ( $sql, __FILE__, __LINE__ );

$lessonid=getgpc('cidReq','G');
$html = '<div id="demo" class="yui-navset">';
$html .= '<ul class="yui-nav">';
$html .= '<li  ' . ($description_id == 10 ? 'class="selected"' : '') . '><a href="../admin/course/course_edit.php?cidReq='.$lessonid.'&description_id=' . 10 . '"><em>' . get_lang ( '课程设置' ) . '</em></a></li>';

$html .= '<li  ' . ($description_id == 0 ? 'class="selected"' : '') . '><a href="index.php?cidReq='.$lessonid.'&description_id=' . 0 . '"><em>' . get_lang ( 'CourseInfo' ) . '</em></a></li>';
$html .= '<li  ' . ($description_id == 8 ? 'class="selected"' : '') . '><a href="index.php?cidReq='.$lessonid.'&description_id=' . 8 . '"><em>' . get_lang ( 'Sybzh' ) . '</em></a></li>';
//$html .= '<li  ' . ($description_id == 9 ? 'class="selected"' : '') . '><a href="step.php?cidReq='.$lessonid.'"><em>' . get_lang ( '模拟仿真实验' ) . '</em></a></li>';
$html .= '<li  ' . ($description_id == 7 ? 'class="selected"' : '') . '><a href="index.php?cidReq='.$lessonid.'&description_id=' . 7 . '"><em>' . '教学大纲' . '</em></a></li>';
$html .= '<li  ' . ($description_id == 14 ? 'class="selected"' : '') . '><a href="lessontop.php?cidReq='.$lessonid.'"><em>' . get_lang ( 'Topology') . '</em></a></li>';

$html .= '<li style="float:right;margin-right:10px">' . link_button ( 'edit.gif', 'Edit', 'desc_update.php?action=edit&description_id=' . $description_id, '100%', '98%' ) . '</li>';
$html .= '</ul>';
$html .= '<div class="yui-content"><div id="tab1">';
echo $html;

if ($description_id == 0){
					$content = $description;
					$content1 = $description1;
					$content2 = $description2;
					//$content3 = $description3;
					$content4 = $description4;
					$content5 = $description5;
					$content6 = $description6;
					$content7 = $description7;
					$content8 = $description8;
					$content9 = $description9;
					$content10 = $description10;
					$content11 = $description11;
					$content12 = $description12;
				
				echo '<div class="courseDescriptionContent" style="background-color:#F4FDFF;border:#CCC 1px dotted;padding:10px">';
				echo '<table border="2" cellpadding="0" cellspacing="0" bordercolor="gray" width="100%" height="80%">';
				echo '<tr><td width="10%">实验等级</td><td class="lefttext">';
                if(text_filter ( $content )==0){
                    echo get_lang ( 'primary' );
                }if(text_filter ( $content )==1){
                    echo get_lang ( 'intermediate' );
                }if(text_filter ( $content )==2){
                    echo get_lang ( 'advanced' );
                }
				echo '</td></tr><tr><td width="10%">实验目的</td><td class="lefttext">&nbsp;';
				 $content1=str_replace("\n","",$content1);
				echo $content1;
				echo '</td></tr><tr><td width="10%">预备知识</td><td  class="lefttext">&nbsp;';
				 $content2=str_replace("\n","",$content2);
				echo text_filter ( $content2 );
//				echo '</td></tr><tr><td width="10%">需求分析</td><td  class="lefttext">&nbsp;';
//				 $content3=str_replace("\n","<br>",$content3);
//				echo text_filter ( $content3 );
				echo '</td></tr><tr><td width="10%">实验内容</td><td  class="lefttext">&nbsp;';
				 $content4=str_replace("\n","<br>",$content4);
				echo text_filter ( $content4 );
				echo '</td></tr><tr><td width="10%">实验原理</td><td  class="lefttext">&nbsp;';
				 $content5=str_replace("\n","",$content5);
				echo text_filter ( $content5 );
				echo '</td></tr><tr><td width="10%">实验环境描述</td><td class="lefttext">&nbsp;';
				 $content6=str_replace("\n","",$content6);
				echo text_filter ( $content6 );
				echo '</td></tr>';
				echo '</table>';
				echo "</div>";
				
				echo '</div></div></div>';
				Display::display_footer ();

}
if ($description_id == 8){
    $content8 = $description8;
    $content13 = $description13;

    echo '<div class="courseDescriptionContent" style="background-color:#F4FDFF;border:#CCC 1px dotted;padding:0px">';
    echo '<table border="2" cellpadding="0" cellspacing="0" bordercolor="gray" width="100%" height="80%">';

    echo '<tr><td width="10%">选择网络拓扑</td><td  class="lefttext">';
    echo text_filter ( $content13 );
    echo '</td></tr><tr><td width="10%">实验步骤</td><td  class="lefttext">&nbsp;';
    $content8=str_replace("\n","",$content8);
    echo text_filter ( $content8 );
    echo '</td></tr>';
    echo '</table>';
    echo "</div>";

    echo '</div></div></div>';
    Display::display_footer ();
}
if ($description_id == 7){
    $content7 = $description7;

    echo '<div class="courseDescriptionContent" style="background-color:#F4FDFF;border:#CCC 1px dotted;padding:10px">';
    echo '<table border="2" cellpadding="0" cellspacing="0" bordercolor="gray" width="100%" height="80%">';

    echo '<tr><td width="10%">教学大纲</td><td>&nbsp;';
    $content7=str_replace("\n","",$content7);
    echo text_filter ( $content7 );
    echo '</td></tr>';
    echo '</table>';
    echo "</div>";

    echo '</div></div></div>';
    Display::display_footer ();
}
/**
elseif ($description_id == 2)
	$content = $description2;
elseif ($description_id == 3)
	$content = $description3;*/

