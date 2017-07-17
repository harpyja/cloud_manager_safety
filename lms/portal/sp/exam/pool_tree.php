<?php
$language_file = array ('exercice','admin');
include_once ('../inc/global.inc.php');
api_protect_admin_script();
include('cls.question_pool.php');
//include_once(api_get_path(SYS_CODE_PATH)."exam/examination.inc.php");
//$restrict_org_id=protect_exam_script();

$objQuestionPool=new QuestionPool();
$pool_tree=$objQuestionPool->get_all_pool_tree();

//$tbl_question=Database::get_main_table(TABLE_MAIN_EXAM_QUESTION);
//$sql="SELECT pool_id,COUNT(id) FROM $tbl_question WHERE pid=0 GROUP BY pool_id";
//$category_cnt=Database::get_into_array2($sql,__FILE__,__LINE__);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
<?php //echo import_assets("commons.js");
echo import_assets ( "jquery-latest.js" );
?>
<script src="<?=api_get_path ( WEB_PATH )?>res/dtree/dept_tree2.js"
	type=text/javascript></script>
<link href="<?=api_get_path ( WEB_PATH )?>res/dtree/dtree.css"
	type=text/css rel=StyleSheet>



<script language="JavaScript" type="text/JavaScript">
	var is_show=0;
	function show_hide_oc(){
		if(is_show==1){
			d.closeAll();
			$("#oc").html("<?=get_lang('OpenAll') ?>");
			is_show=0;
		}else{
			d.openAll();
			$("#oc").html("<?=get_lang('CloseAll') ?>");
			
			is_show=1;
		}
	}
</script>

<style type="text/css">
html {
	SCROLLBAR-ARROW-COLOR: #88bad3;
	SCROLLBAR-FACE-COLOR: #dff1f5;
	SCROLLBAR-DARKSHADOW-COLOR: #f1fcff;
	SCROLLBAR-HIGHLIGHT-COLOR: #f1fcff;
	SCROLLBAR-3DLIGHT-COLOR: #a3c7df;
	SCROLLBAR-SHADOW-COLOR: #a3c7df;
	SCROLLBAR-TRACK-COLOR: #f2f7f9;
}
</style>
</head>
<body>
<div style="float:right;width:100px"><a href="javascript:window.location.reload();"><?=get_lang('Refresh')?></a>
<a href='javascript:show_hide_oc();'>
<div id='oc' style='float: right;font-size:12px'><?=get_lang('OpenAll') ?></div>
</a>
</div>
<div class=dtree>
<table cellspacing="5">
	<tr>
		<td align="left"></td>
	</tr>
	<tr>
		<td><script type=text/javascript>		
<!--
d = new dTree('d');
/*d.config.closeSameLevel=true;*/
d.config.useCookies=true;
d.config.useIcons=false;
d.add(0,-1,'<?=get_lang ( "PoolManagement" )?>','pool_list.php','','List');
<?php
	foreach ($pool_tree as $pool){		
		$url="/lms/main/exercice/question_base.php?pool_id=".$pool['id'];
		$pool_name=api_trunc_str2(trim($pool['pool_name']),10).($category_cnt[$pool['id']]?' ('.$category_cnt[$pool['id']].')':'');
		echo 'd.add(' . $pool ['id'] . ',0,"' . $pool_name. '","'.$url.'","'.trim($pool['pool_name']).'","List");'."\n";
	}
?>
document.write(d);

//-->
</script></td>
	</tr>
</table>
</div>
</body>
</html>
