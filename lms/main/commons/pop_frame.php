<?php
require_once ('../inc/global.inc.php');
$type = getgpc ( 'MODULE_ID' );

$left_frame_url = "";
$right_frame_url = "";
if (isset ( $_REQUEST ['MODULE_ID'] )) {
	switch ($type) {
		
		case 'DEPT_ADD_ADMIN' :
			$left_frame_url = "dept_tree.php?url=select_mutli_users.php&" . $_SERVER ["QUERY_STRING"];
			$right_frame_url = api_get_path ( WEB_CODE_PATH ) . "commons/select_mutli_users.php?" . $_SERVER ["QUERY_STRING"];
			break;
		
		/*		case 'COURSE_UPDATE':
			$left_frame_url="dept_tree.php?url=select_mutli_users.php&status=".COURSEMANAGER."&".$_SERVER["QUERY_STRING"];
			$right_frame_url=api_get_path(WEB_CODE_PATH)."commons/select_mutli_users.php?status=".COURSEMANAGER."&".$_SERVER["QUERY_STRING"];
			break;*/
		
		case 'COURSE_UPDATE' :
		case 'EXAM_MANAGER' :
			$left_frame_url = "dept_tree.php?url=select_single_user.php&status=" . COURSEMANAGER . "&" . $_SERVER ["QUERY_STRING"];
			$right_frame_url = api_get_path ( WEB_CODE_PATH ) . "commons/select_single_user.php?status=1&" . $_SERVER ["QUERY_STRING"];
			break;
			
		default :
			$left_frame_url = "_blank";
			$right_frame_url = "_blank";
	}
}

$htmlHeadXtra [] = '<script type="text/javascript">
function iframeAutoFit()
{
	var ex;
	try
	{
		if(window!=parent)
		{
		    var parentHeight = screen.availHeight - 480;
			var a = parent.document.getElementsByTagName("IFRAME");
			for(var i=0; i<a.length; i++)
			{
				if(a[i].contentWindow==window)
				{
					a[i].style.height = "485px";
					var h1=0, h2=0;
					if(document.documentElement&&document.documentElement.scrollHeight)
					{
						h1=document.documentElement.scrollHeight;
					}
					if(document.body) h2=document.body.scrollHeight;

					var h=Math.max(h1, h2);
					if(h<parentHeight) h=parentHeight;
					if(document.all) {h += 12;}
					if(window.opera) {h += 4;}
					a[i].style.height = h +"px";
				}
			}
		}
	}
	catch (ex){}
}
if(document.attachEvent)  window.attachEvent("onload",  iframeAutoFit);
else  window.addEventListener("load",  iframeAutoFit,  false);

</script>
	
<style type="text/css">
.framePage {
	/*border:#CACACA solid 1px;*/
	border-top-style:none;
	width:100%;
	padding-top:0px;
	text-align:left;
	
}

#Resources {
	width:100%;
}
#Resources #treeview {
	float:left;
	border:#999 solid 1px;
	width:30%;
	height:380px;
}
#Resources #frm {
	float:left;
	width:69%;
}
</style>
';

Display::display_header ( $tool_name, FALSE );
?>

<body>
<center>
<div class="framePage stud">
<div id="Resources">
<div id="treeview"><iframe id="Tree" name="Tree"
	src="<?=$left_frame_url?>" frameborder="0" width="100%"
	height="380px;"></iframe></div>
<div id="frm"><iframe id="List" name="mainFrame"
	src="<?=$right_frame_url?>" frameborder="0" width="100%"
	height="380px;"></iframe></div>
</div>
</div>
</center>
</body>
</html>

