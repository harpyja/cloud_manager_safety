function submitPaper(){
	if(is_exam_finish(total)){
		$.prompt(langConfirmSubmitQuiz,	{ 
					buttons:{'确定':true, '取消':false},
					callback: function(v,m,f){						
						if(v){	
							btnSumbit_onclick();		
							$.prompt(langSubmittingPlsDoNothing);
							return true;
						}else{
							return false;
						}						
					}
		});
	}
}

window.onunload=function(){ btnSumbit_onclick();};
	

// 提交试卷
function btnSumbit_onclick()	
{   
	$("#sub").hide();
	$("#formSub").val("1");
	$("#frm_exam").submit();// 将答卷提交到ExamSave.php里面进行后台数据库操作
}

function is_question_finish(idx){
	var qt=$("#qt_"+idx).val(); //console.info($("#qt_"+idx));
	if(qt==1 || qt==2){
		for(var i=1;i<=10;i++){
			if(G("q_"+idx+"_"+i)){
				//console.info(G("q_"+idx+"_"+i).checked);
				if(G("q_"+idx+"_"+i).checked) return true;
			}
		}
	}else{
		if(G("q_"+idx)){
			if($("#q_"+idx).val().replace(new RegExp(" ","g"),"")!=""){
				return true;
			}
		}
	}
	return false;
}

function is_exam_finish(total){
	// 遍历所有的题目年检查
	for(var i=1;i<=total;i++)
	{
		//console.info(i+"---"+is_question_finish(i));
		if(!is_question_finish(i))
		{
		    // 如果没有完成，在进行提示
			$.prompt("第 " + i  + " 题还没做");			
			return false;
		}
	}
	return true;
}