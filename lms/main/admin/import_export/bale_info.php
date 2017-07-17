<?php
 $language_file = 'admin';
$cidReset = true;
include_once ("../../inc/global.inc.php");
$this_section = SECTION_PLATFORM_ADMIN;
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

echo '<link href="../../../themes/css/layout.css" rel="stylesheet" type="text/css" media="screen" />';
$htmlHeadXtra [] = import_assets ( "yui/tabview/assets/skins/sam/tabview.css", api_get_path ( WEB_JS_PATH ) ); 
$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( null, FALSE);

$category_id=  intval(htmlspecialchars(getgpc("category_id","G"))); 
 
//$paths="/tmp/mnt/www";//server
$paths=URL_ROOT."/www/lms/storage"; 
 
$files1 = glob($paths."/coursesnew/".$category_id.'_*_readme');
$readme_path=$files1[0]; 
    
echo '<table align="center" width="90%" cellpadding="4" cellspacing="0" id="systeminfo">';
if(file_exists($readme_path)){
    $readme_var= file_get_contents($readme_path);
    $readme_data=  unserialize($readme_var); 
   $date=$readme_data['date'];
   $filename=$readme_data['filename'];
   $category_name=$readme_data['category_name'];
   $coursename=$readme_data['coursename'];
   $filename=$readme_data['filename']; 
   $time=substr($date,0,4)."年".substr($date,4,2)."月".substr($date,6,2)."日&nbsp;".substr($date,8,2).":".substr($date,10,2).":".substr($date,12,2);
  
   $courses='';
   for($i=0;$i<count($coursename);$i++){
       $j=$i+1;
       $courses.=$j."、".$coursename[$i]."<br>";
   }

echo '<caption align="top"><b>课件升级包信息</b></caption>
        <tr>
            <td align="right" class="formLabel">课件升级包名称</td>
            <td align="left"  class="formTableTd">'.$filename.'</td>
        </tr>
        <tr>
            <td align="right" class="formLabel">课件分类名称</td>
            <td align="left"  class="formTableTd">'.$category_name.'</td>
        </tr>  
        <tr>
            <td align="right" class="formLabel">升级包版本时间</td>
            <td align="left"  class="formTableTd">'.$time.'</td>
        </tr>
        <tr>
            <td align="right" class="formLabel">课件列表</td>
            <td align="left"  class="formTableTd">'.$courses.'</td>
        </tr> ';
}else{
   echo ' <tr> <td colspan="10" align="center"  style="border-left: 1px dotted #e1e1e1; border-right: 1px dotted #e1e1e1; border-bottom: 1px dotted #e1e1e1; background-color: #F7F7F7 !important; border-right: none !important; font-weight: 500; height: 40px;"><b>没有课件升级包信息！</b></td> </tr>';
}
echo '</table>'; 
?>

       