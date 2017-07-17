


//  Andy Langton's show/hide/mini-accordion @ http://andylangton.co.uk/jquery-show-hide

// this tells jquery to run the function below once the DOM is ready
$(document).ready(function() {

// choose text for the show/hide link - can contain HTML (e.g. an image)
var showText='展开';
var hideText='关闭';

// initialise the visibility check
var is_visible = false;

// append show/hide links to the element directly preceding the element with a class of "toggle"
$('.toggle').prev().append(' <a href="#" class="toggleLink">'+hideText+'</a>');

// hide all of the elements with a class of 'toggle'
$('.toggle').show();

// capture clicks on the toggle links
$('a.toggleLink').click(function() {

// switch visibility
is_visible = !is_visible;

// change the link text depending on whether the element is shown or hidden
if ($(this).text()==showText) {
$(this).text(hideText);
$(this).parent().next('.toggle').slideDown('slow');
}
else {
$(this).text(showText);
$(this).parent().next('.toggle').slideUp('slow');
}
// return false so any link destination is not followed
return false;

});
});

/*
 * 后台主页选项卡切换
 * Author:Yuhao
 * Date: 2012/12/25
 */
$(function(){
	$("#flexButton").click(function(){
		if($("#sidebar").hasClass("open"))
		{
			 $("#sidebar").animate({width:"1%"}).removeClass("open").attr("class","column close");
			 $(".toggle").hide();
			 $(".navs").hide();
			 $("#main").animate({width:"99%"});
			 $(this).attr("class","closeButton open");
		}else{
             $("#sidebar").animate({width:"15%"}).attr("class","column open");
			 $(".toggle").show();
			 $(".navs").show();
			 $("#main").animate({width:"85%"});
			 $(this).attr("class","closeButton close");
        }
	})
	$("#systeminfo tr:even").css("background","#FFC");
	$("#sidebar").css("height","100%");
	$(".labtable tr:even").css("background","#DEE4E5");
	$(".labtable tr:odd").css("background","#EDF2F5");
	var $div_li = $(".manage-tab li");
	$div_li.click(function(){
		$(this).addClass("selected").siblings().removeClass("selected");
		var index = $div_li.index(this);
		$(".manage-tab-content>div").eq(index).show().siblings().hide();
	}).hover(function(){
		$(this).addClass("hover");
	},function(){
		$(this).removeClass("hover");	
	})
	//lab实验选项卡
	var $nav_dt = $(".nav-list dt");
	$nav_dt.click(function(){
		var $url = $(this).siblings("dd");
		if($url.is(":visible"))
		{
			$url.hide();	
		}else{
			$url.show();		
		}
		return false;
	});
	//后台首页tab
	var $div_dt = $(".pagetab>ul>li");
	$div_dt.click(function(){
		$(this).addClass("selected").siblings().removeClass("selected");
		var index = $div_dt.index(this);
			if(index == 0)
			{
				  $(".f0").animate({left:"0px"},500); 	
			}else if(index == 1){
				  $(".f0").animate({left:"-1500px"},500);
				  $(".f1").animate({left:"0px"},500);		
			}else if(index == 2){
				  $(".f0").animate({left:"-1500px"},500);
				  $(".f1").animate({left:"-1500px"},500);
				  $(".f2").animate({left:"0px"},500);	
			}else if(index == 3){
				  $(".f0").animate({left:"-1500px"},500);
				  $(".f1").animate({left:"-1500px"},500);
				  $(".f2").animate({left:"-1500px"},500);
				  $(".f3").animate({left:"0px"},500);
				  $(".f4").animate({left:"-1500px"},500);	
			}else if(index ==4){
				  $(".f0").animate({left:"-1500px"},500);
				  $(".f1").animate({left:"-1500px"},500);
				  $(".f2").animate({left:"-1500px"},500);
				  $(".f3").animate({left:"-1500px"},500);
				  $(".f4").animate({left:"0px"},500);	
			}
		return false;	
	 });
	 $(".p-table tr:even").css("background-color","#F0F0F0");	
})
//分类管理table JS
$(function(){
		var $tabletr = $(".course-win2");
		$tabletr.click(function(){
			$tr = $(this).parent().siblings("tr");
			if($tr.is(":visible"))
			{
				$tr.hide();	
			}else{
				$tr.show();	
			}
		})
})

