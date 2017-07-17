// JScript 文件
// 考试页面顶部页面的JavaScript函数


var bCanMessage = true;
//形成按钮
function output()
{
	var outputString = "";
	for(i=0;i<output.arguments.length;i++)
	{
		switch(output.arguments[i].toLowerCase())	{
			case "handsave":
				outputString += "<td width='75' height='49'><div align='center'><a href='#' onMouseOut='MM_swapImgRestore()' onMouseOver='MM_swapImage(\"Image7\",\"\",\"../../App_Image/Learning/sgbc_botton_on.gif\",1)' onClick='HandSaveExam();'><img src='../../App_Image/Learning/sgbc_botton.gif' name='Image7' width='51' height='49' border='0'></a></div></td>";
				break;
			case "checkpaper":
				outputString += "<td width='75' height='49'><div align='center'><a href='#' onMouseOut='MM_swapImgRestore()' onMouseOver='MM_swapImage(\"Image5\",\"\",\"../../App_Image/Learning/jcsj_botton_on.gif\",1)' onClick='CheckPaper();'><img src='../../App_Image/Learning/jcsj_botton.gif' name='Image5' width='51' height='49' border='0'></a></div></td>";
				break;
			case "save":
				outputString += "<td width='75' height='49'><div align='center'><a href='#' onMouseOut='MM_swapImgRestore()' onMouseOver='MM_swapImage(\"Image6\",\"\",\"../../App_Image/Learning/tjsj_botton_on.gif\",1)' onClick='return SubmitExam();'><img src='../../App_Image/Learning/tjsj_botton.gif' name='Image6' width='51' height='49' border='0'></a></div></td>";
				break;
		    case "researchsave":
		        outputString += "<td width='75' height='49'><div align='center'><a href='#' onMouseOut='MM_swapImgRestore()' onMouseOver='MM_swapImage(\"Image6\",\"\",\"../../App_Image/Learning/tjsj_botton_on.gif\",1)' onClick='return ResearchSubmitExam();'><img src='../../App_Image/Learning/tjsj_botton.gif' name='Image6' width='51' height='49' border='0'></a></div></td>";
				break;
			case "exit":
				outputString += "<td width='75' height='49'><div align='center'><a href='#' onMouseOut='MM_swapImgRestore()' onMouseOver='MM_swapImage(\"Image8\",\"\",\"../../App_Image/Learning/close_botton_on.gif\",1)' onClick='ExitExam()'><img src='../../App_Image/Learning/close_botton.gif' name='Image8' width='51' height='49' border='0'></a></div></td>";
	            break;
		}
	}
	document.write(outputString);
}

//中途保存
function HandSaveExam()
{
    
    parent.document.getElementsByName("mainFrame")[0].contentWindow.btnCancel_onclick();
}

//监察答题情况
function CheckPaper()
{
    parent.document.getElementsByName("mainFrame")[0].contentWindow.btnDetect_onclick();
}

//中途退考
function ExitExam()
{    
   parent.document.getElementsByName("mainFrame")[0].contentWindow.ExitExam();
}

//提交考试答卷
function SubmitExam()
{
    parent.document.getElementsByName("mainFrame")[0].contentWindow.initialize();
	/*parent.document.getElementsByName("mainFrame")[0].contentWindow.document.getElementById("MoveOutEnabled").value=0;
	if(window.confirm("确定要交卷吗？"))
	{			
		document.getElementById("lblWarning").innerHTML="正在交卷，可能要花几分钟时间，请勿做任何操作，耐心等候...";
		parent.document.getElementsByName("mainFrame")[0].contentWindow.btnSumbit_onclick();
	}	
	parent.document.getElementsByName("mainFrame")[0].contentWindow.document.getElementById("MoveOutEnabled").value=1;
	*/
}

//提交调查的答卷
function ResearchSubmitExam()
{
    if(parent.document.getElementsByName("mainFrame")[0].contentWindow.check_finish())
    {
	    parent.document.getElementsByName("mainFrame")[0].contentWindow.document.getElementById("MoveOutEnabled").value=0;
	    if(window.confirm("确定要问卷调查答卷吗？"))
	    {			
		    document.getElementById("lblWarning").innerHTML="正在交卷，可能要花几分钟时间，请勿做任何操作，耐心等候...";
		    parent.document.getElementsByName("mainFrame")[0].contentWindow.btnSumbit_onclick();
	    }	
	    parent.document.getElementsByName("mainFrame")[0].contentWindow.document.getElementById("MoveOutEnabled").value=1;
	}
	else
	{
	    alert("你只有做完所有调查才可以提交！");
	}
}