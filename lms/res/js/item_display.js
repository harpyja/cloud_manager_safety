//任务各种操作的确认
   function confirmOperation(url){
   
    if(confirm("您确认要进行此操作吗？")){  	
  	window.open(url,'_self');
  	}
   	
   } 
   

//批量下达

function assign(form){
 if(confirm("您确认要进行 批量下达 操作吗？")){	
    with(form){
    	var j=0;
    	
    	var max = form.taskcheck.length;

    	for (var idx = 0; idx < max; idx++) {
			if (eval("document.queryplan.taskcheck[" + idx + "].checked") == true) {
			j =j+1;
			}
		}
		    if(eval("document.queryplan.taskcheck.checked") == true)
		    {		    
		        j = j +1;
		    }			
	    if(j==0){ 
			window.alert("请先选中要下达的任务!");
			return false;
	    }
    }
	window.alert("提示:只有未下达的任务才可下达!");
	form.action="TaskServlet?action=assignmoretask";
	
	form.submit();
 }	
}

//批量反馈

function feedbackmore(form){
 if(confirm("您确认要进行 批量反馈 操作吗？")){	
    with(form){
    	var j=0;
    	
    	var max = form.taskcheck.length;

    	for (var idx = 0; idx < max; idx++) {
			if (eval("document.queryplan.taskcheck[" + idx + "].checked") == true) {
			j =j+1;
			}
		}
		    if(eval("document.queryplan.taskcheck.checked") == true)
		    {		    
		        j = j +1;
		    }			
	if(j==0){ 
			window.alert("请先选中要反馈的任务!");
			return false;
	}
    }
	window.alert("提示:只有未反馈的任务才可反馈!");
	form.action="AdviceServlet?urlaction=feedbackmoretask";
	
	form.submit();
 }	
}

//批量审核

function auditmore(form){
 if(confirm("您确认要进行 批量审核 操作吗？")){	
    with(form){
    	var j=0;
    	
    	var max = form.taskcheck.length;

    	for (var idx = 0; idx < max; idx++) {
			if (eval("document.queryplan.taskcheck[" + idx + "].checked") == true) {
			j =j+1;
			}
		}
		    if(eval("document.queryplan.taskcheck.checked") == true)
		    {		    
		        j = j +1;
		    }			
	    if(j==0){ 
			window.alert("请先选中要审核的任务!");
			return false;
	    }
    }
	window.alert("提示:只有未审核的任务才可审核!");
	form.action="AdviceServlet?urlaction=auditmoretask";
	
	form.submit();
 }	
}

//拷贝选定的任务
function paste(form)
{
    if(confirm("您确认要进行 批量拷贝 操作吗？"))
    {
	    with(form)
	    {
		    var j=0;
    	    var max = form.taskcheck.length;
    	
		    for (var idx = 0; idx <max; idx++) 
		    {
			
			    if (eval("document.queryplan.taskcheck[" + idx + "].checked") == true) {
			    j =j+1;
			    }
		    }
		    if(eval("document.queryplan.taskcheck.checked") == true)
		    {		    
		        j = j +1;
		    }		
		    if(j == 0)
		    { 
			    window.alert("请先选中要拷贝的任务!");
			    return false;
		    }
        }
        //alert("j:"+j);
	    form.action="TaskServlet?action=paste";
	    form.submit();
    }	
}

//从我的任务列表批量删除选定的任务
function deleteNotSend(form)
{
    if(confirm("您确认要进行  批量删除 操作吗？"))
    {
	    with(form)
	    {
		    var j = 0;
    	    var max = form.taskcheck.length;    	
		    for(var idx = 0; idx < max; idx++) 
		    {		   			
			    if(eval("document.queryplan.taskcheck[" + idx + "].checked") == true) 
			    {
			        j = j+1;
			    }
		    }
		    if(eval("document.queryplan.taskcheck.checked") == true)
		    {		    
		        j = j +1;
		    }
		    if(j==0)
		    { 
			    window.alert("请先选中要删除的任务!");
			    return false;
		    }
        }    
	    form.action="TaskServlet?action=deleteMoreTask";
	    form.submit();
    }
}

//在新窗口中打开
function toUrl(s)
 {
 	var url =s;
	window.open(url);
 }
 
//在新窗口中打开
function url_in_newwindow(url,windowname,actiontext){
	var newurl="<a href='#' onclick='javascript:window.open(\""+url+"\",\""+windowname+"\",\"toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,left=100,top=150,width=600,height=350\")' class='link1'>"+actiontext+"</a>";
	
	document.location.href=newurl;
}

function openfullwindow(url,windowname,actiontext){
	var newurl="<a href='#' onclick='javascript:window.open(\""+url+"\",\""+windowname+"\",\"toolbar=yes,location=no,directories=no,status=yes,menubar=yes,scrollbars=yes,resizable=yes,fullscreen=yes\")' class='link1'>"+actiontext+"</a>";
	
	document.location.href=newurl;
}
//选择通讯录
function choose_address(form)
{
	with(form){
	 var idx = document.all.address.selectedIndex ;
     var value=document.all.address.options[idx].value;
     var text=document.all.address.options[idx].text;  
     document.all.address.value=value;
     document.all.addressname.value=text;    
    }  
    form.submit();
}
//选择月份时提交表单
function choose_taskmonth(form)
{
	with(form){
	 var idx = document.all.taskmonth.selectedIndex ;
     var value=document.all.taskmonth.options[idx].value;
     var text=document.all.taskmonth.options[idx].text;  
     document.all.taskmonth.value=value;
     document.all.taskmonth.text=text;
     form.submit();
    }  
}
//搜索事业部下的部门,科室,人员姓名
//选择事业部,刷新表单
function choose_entp(form)
{
	with(form){
	 var idx = document.all.ENTPdept.selectedIndex ;
     var value=document.all.ENTPdept.options[idx].value;
     var text=document.all.ENTPdept.options[idx].text;  
     document.all.ENTPdept.value=value;
     document.all.ENTPdeptname.value=text;
     form.submit();
    }  
}
//选择部门,刷新表单
function choose_dept(form)
{ with(form){
	 var idx = document.all.deptId.selectedIndex ;
     var value=document.all.deptId.options[idx].value;
     var text=document.all.deptId.options[idx].text;
     document.all.deptId.value=value;
     document.all.deptname.value=text;
     form.submit();
    }  
}
//选择科室,刷新表单
function choose_sect(form)
{ with(form){
	 var idx = document.all.sectId.selectedIndex ;
     var value=document.all.sectId.options[idx].value;
     var text=document.all.sectId.options[idx].text;
     document.all.sectId.value=value;
     document.all.sectname.value=text;
     form.submit();
    }  
}
//选择产品类别
function choose_productType(form)
{
	with(form){
	 var idx = document.all.productType.selectedIndex ;
     var value=document.all.productType.options[idx].value;
     var text=document.all.productType.options[idx].text;  
     document.all.productType.value=value;
     document.all.productTypeName.value=text;
     document.all.formAction.value='productType';
     form.submit();
    }  
}
//选择项目大类
function choose_pjtType(form)
{
	with(form){
	 var idx = document.all.pjtType.selectedIndex ;
     var value=document.all.pjtType.options[idx].value;
     var text=document.all.pjtType.options[idx].text;  
     document.all.pjtType.value=value;
     document.all.pjtTypeName.value=text;
     document.all.formAction.value='pjtType';
     form.submit();
    }  
}
//选择项目小类
function choose_pjtSubType(form)
{
	with(form){
	 var idx = document.all.pjtSubType.selectedIndex ;
     var value=document.all.pjtSubType.options[idx].value;
     var text=document.all.pjtSubType.options[idx].text;  
     document.all.pjtSubType.value=value;
     document.all.pjtSubTypeName.value=text;
     document.all.formAction.value='pjtSubType';
     form.submit();
    }  
}
//选择人
function choose_person(form)
{ with(form){
	 var idx = document.all.personId.selectedIndex ;
     var value=document.all.personId.options[idx].value;
     var text=document.all.personId.options[idx].text;
     document.all.personId.value=value;
     document.all.personName.value=text;
     //form.submit();
    }  
}
//选择产品
function choose_product(form)
{ with(form){
	 var idx = document.all.productId.selectedIndex ;
     var value=document.all.productId.options[idx].value;
     var text=document.all.productId.options[idx].text;
     document.all.productId.value=value;
     document.all.productName.value=text;
     //form.submit();
   }  
}
//搜索报表类型
function choose_report(form)
{ with(form){
	 var idx = document.all.reportId.selectedIndex ;
     var value=document.all.reportId.options[idx].value;
     var text=document.all.reportId.options[idx].text;
     document.all.reportId.value=value;
     document.all.reportname.value=text;
     //form.submit();
    }  
}

function doSearch(form,userid,username){
   if(username.value=="")
     alert("请输入要查询的人名!");
   else
   	 window.open("../newPersonFind.jsp?form="+form.name+"&userid="+userid.name+"&username="+username.name+"&employeeName="+username.value,'SearchPerson','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,left=100,top=150,width=600,height=400');
}

function doSearchEmployee(form,userid,username){
	if(username.value==""){
        alert("请输入要查询的人名!");
    }else{
    	window.open("./wbs/Plan_Select_Executor.jsp?formName="+form+"&condition="+username.value,'选择人员','width=450,height=450,status=yes,resizable=yes,top=200,left=200,scrollbars=yes');
    	
	}
   //选择执行人
}


function doSearch2(form,userid,username,validname){
   if(username.value=="")
     alert("请输入要查询的人名!");
   else
   	 window.open("../newPersonFind.jsp?form="+form.name+"&userid="+userid.name+"&username="+username.name+"&employeeName="+username.value+"&validname="+validname.name,'SearchPerson','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,left=100,top=150,width=600,height=400');
}

//搜索个人群组
function doSearchTeam(form,userid,username,appname,modulename,relatedId){
   window.open("../TeamMemberFind.jsp?form="+form.name+"&userid="+userid.name+"&username="+username.name+"&appname="+appname+"&modulename="+modulename+"&relatedId="+relatedId,'SearchPerson','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,left=100,top=150,width=600,height=400');
}


//删除
function Delete(url) {
 if(confirm("确认要删除吗？")){
   document.location.href=url;
  }
}

function noToolBarWindow(url){ 
 newpage = window.open(url,'newpage','toolbar=no'); 
 newpage.focus(); 
 self.close(); 
} 


//为每一种显示方式做一个函数,也可以为每一个ID写一个显示函数,一个隐藏函数
    //重点工作 1, 其他工作 2,版本计划11,随机资料12
   function TASKPROPERTY_D1(){
    	TASK_NAME.style.display="block";
    	//TASK_TARGET.style.display="none";
    	/*
    	MONEY.style.display="none";
    	MONEYUSE.style.display="none";
    	HR_REQUEST.style.display="none";
    	HR_NUM.style.display="none";
    	HR_DESC.style.display="none";    	
    	*/
    	ACCEPT_CRITERION.style.display="none";
    }
    //培训工作 3,三化 6,6sigma 7,专利标准 8,流程优化 9,CMM 10,
	function TASKPROPERTY_D2(){    
    	TASK_NAME.style.display="block";
    	//TASK_TARGET.style.display="none";  
    	/*  	
    	MONEY.style.display="none";
    	MONEYUSE.style.display="none";
    	HR_REQUEST.style.display="none";
    	HR_NUM.style.display="none";
    	HR_DESC.style.display="none";  
    	*/
    	TASK_CLASS.style.display="none";
    	MONITOR.style.display="none";  	
    	
    	ACCEPT_CRITERION.style.display="none";
    }
    //临时任务 13
    function TASKPROPERTY_D5(){    
    	TASK_NAME.style.display="block";
    	TASK_CLASS.style.display="none";
    	MONITOR.style.display="none";
    	
    	PLAN_BEGIN_TIME.style.display="block";
    	PLAN_END_TIME.style.display="block";  	
    	COMPLETE_STYLE.style.display="none"; 
    	
    	TASK_CONTENT.style.display="block";
    	
    	ACCEPT_CRITERION.style.display="block";
    }
    
 function SELECT_TASKFROM(taskfrom){
     if( taskfrom=="1" ){
     	taskfromsec.style.display="block"; 
	taskfromproject.style.display="none"; 
     }else if(taskfrom=="2"){
        taskfromsec.style.display="none";  
        taskfromproject.style.display="block"; 
     }    
 }
   
//判断是否按下回车键,如果按下回车键,则弹出人员选择窗口
function form_onkeyup(form,personId,personName)
{ 
	key = window.event.keyCode; 		
	if(key == 13)//判断是否按下回车键
	{
	    doSearch(form, personId, personName);
	    return false;   
	}else{
	    return key;
	}
}

//判断是否按下回车键,如果按下回车键,到下一个输入框
function form_onkeyup2(form,personId,personName,validName)
{ 
		key=window.event.keyCode; 
		if(key==13)//判断是否按下回车键
		{   
		    //搜索人名
		    doSearch2(form,personId,personName,validName);
		    personName.focus();
		    return false; 
	     } 
}

//提交表单时,所有操作无效
function  ini(curForm){
	 len = curForm.elements.length;
		var i=0;
		for (i=0; i<len; i++) {
				curForm.elements[i].disabled=true;
		}
}

//查寻人员
function searchEmployee(form,personId,personName){
	key=window.event.keyCode; 
	if(key==13){ //判断是否按下回车键
		//搜索人名
		doSearchEmployee(form,personId,personName);
		//personName.focus();
		return false; 
	}   
}
