<?php 
$filename=$_GET['filename'];
$w=$_GET['w'];
$h=$_GET['h'];
if($w==NULL||$h==NULL){
    $w=800;
    $h=600;
}
?>
<center><APPLET codebase="./applet" CODE="com.tightvnc.rfbplayer.RfbPlayer.class" ARCHIVE="RfbPlayer.jar" width=<?=$w+20?> height=<?=$h+20?> > 
<PARAM NAME="URL" VALUE="<?=$filename?>"> 
<PARAM NAME="Autoplay" VALUE="Yes"> 
<PARAM NAME="Open new window" VALUE="No"> 
<PARAM NAME="DISPLAY_WIDTH" VALUE="<?=$w?>"> 
<PARAM NAME="DISPLAY_HEIGHT" VALUE="<?=$h?>"> 
<PARAM NAME="Speed" VALUE="2"> 
<PARAM NAME="Show controls" VALUE="no"> 
</APPLET></center>
