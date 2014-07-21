// JavaScript Document

function mischandler(){
return false;
}

function mousehandler(e){
var myevent = (isNS) ? e : event;
var eventbutton = (isNS) ? myevent.which : myevent.button;
if((eventbutton==2)||(eventbutton==3)) return false;
}
document.oncontextmenu = mischandler;
document.onmousedown = mousehandler;
document.onmouseup = mousehandler;
var isCtrl = false;
    document.onkeyup=function(e)
    {
    if(e.which == 17)
    isCtrl=false;
    }

    document.onkeydown=function(e)
    {
    if(e.which == 17)
    isCtrl=true;
    if((e.which == 85) || (e.which == 67) && isCtrl == true)
    {
   // alert("Keyboard shortcuts are cool!");
    return false;
    }
    }
//ends here*********************
document.onmousedown=disableclick;
status="Right Click Disabled";
function disableclick(event)
{
  if(event.button==2)
   {
    // alert(status);
     return false;    
   }
}
