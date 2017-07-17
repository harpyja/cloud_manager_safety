var oPopup = window.createPopup();	//全局变量
function ShowPOPUP(){
	with (oPopup.document.body) 
	{
		style.backgroundColor="lightyellow";
		style.border="solid black 1px";
		innerHTML="<img src='../images/pros.jpg' />";
	}
			
	oPopup.show(screen.availWidth /2 - window.screenLeft -130, screen.availHeight /2 - window.screenTop - 25, 309, 50, document.body);
}
