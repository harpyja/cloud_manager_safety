function subscribe2Course(code){
//	var txt = '您确认选修该门课程吗?<input type="hidden" id="course_code" name="course_code" value="'+ code +'" />';
//	$.prompt(txt,{
//		buttons:{'确定':true, '取消':false},
//		callback: function(v,m,f){
//			if(v){
				$.post(web_path+'main/course/ajax_actions.php',{ajaxAction:"subscribe",course_code:code,course_class_id:$("#course_class_id").val()},
						function(data){
							if(data=="EnrollToCourseSuccessful"){
								$.prompt('您已提交该课程的选修申请,请等待审核!',{
									buttons:{'确定':true},
									callback: function(v,m,f){
										if(v){ 	//history.go();
											//location.href="<?= WEB_QH_PATH?>course_catalog.php";
											//self.parent.location.reload();
											self.parent.tb_remove();
										}
									}
								});
							}
							else if(data=="EnrollToCourseSuccess"){
								$.prompt("您已成功注册选修了该课程!",{
									buttons:{'确定':true},
									callback: function(v,m,f){
										if(v){ 	//history.go();
											//location.href="<?= WEB_QH_PATH?>course_catalog.php";
											self.parent.location.reload();
											self.parent.tb_remove();
										}
									}
								});
							}
							else if(data=="ErrorContactPlatformAdmin"){	$.prompt("操作失败，如有任何疑问，请联系系统管理员！");}
							else if(data=="YouArtNotAllowedToSubTheCourse"){	$.prompt("对不起,你没有权限选修该课程!");}
				});
//			}
//			else{}
//		}
//	});
}