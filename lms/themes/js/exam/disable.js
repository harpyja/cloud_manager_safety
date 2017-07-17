function document.oncontextmenu(){
    event.returnValue=false;
}
function document.onselectstart(){
    event.returnValue=false;
}
function window.onhelp(){return false} 
function document.onmousewheel()
{
	if(event.shiftKey || event.ctrlKey)
	{
		event.keyCode=0; 
		event.returnValue=false; 		
	}
}
function document.onkeydown() 
{ 
  if ((window.event.altKey)&& 
      ((window.event.keyCode==37)||   
       (window.event.keyCode==39)))  
  { 
     event.returnValue=false; 
  } 
  if ((event.keyCode==116)||
      (event.ctrlKey && event.keyCode==82)){ 
     event.keyCode=0; 
     event.returnValue=false; 
     } 
  if(event.keyCode==32 || event.keyCode==8)	
  {
    if(!(event.srcElement.tagName=="INPUT" && event.srcElement.type=="text") && event.srcElement.tagName!="TEXTAREA")
    {
     event.keyCode=0; 
     event.returnValue=false; 
    }
  }
  if (event.keyCode==27){event.keyCode=0;event.returnValue=false;}  
  
  if (event.keyCode==114){event.keyCode=0;event.returnValue=false;} 
  if (event.keyCode==122){event.keyCode=0;event.returnValue=false;} 
  if(event.ctrlKey && event.keyCode==67) {event.keyCode=0;event.returnValue=false;}	
  if(event.ctrlKey && event.keyCode==86) {event.keyCode=0;event.returnValue=false;}	
  if(event.ctrlKey && event.keyCode==70) {event.keyCode=0;event.returnValue=false;}	
  if(event.ctrlKey && event.keyCode==87) {event.keyCode=0;event.returnValue=false;}	
  if(event.ctrlKey && event.keyCode==69) {event.keyCode=0;event.returnValue=false;}	
  if(event.ctrlKey && event.keyCode==72) {event.keyCode=0;event.returnValue=false;}	
  if(event.ctrlKey && event.keyCode==73) {event.keyCode=0;event.returnValue=false;}	
  if(event.ctrlKey && event.keyCode==79) {event.keyCode=0;event.returnValue=false;}	
  if(event.ctrlKey && event.keyCode==76) {event.keyCode=0;event.returnValue=false;}	
  if(event.ctrlKey && event.keyCode==80) {event.keyCode=0;event.returnValue=false;}	
  if(event.ctrlKey && event.keyCode==66) {event.keyCode=0;event.returnValue=false;}	
  if (event.ctrlKey && event.keyCode==78) {event.keyCode=0;event.returnValue=false;}
  if (event.shiftKey && event.keyCode==121){event.keyCode=0;event.returnValue=false;}
  if (window.event.srcElement.tagName == "A" && window.event.shiftKey) {event.keyCode=0;event.returnValue=false;}
} 