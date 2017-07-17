<?php
 include_once ("../inc/global.inc.php");    
 $id= intval(getgpc('usid'));
 $msql="select count(id) from message where recipient = $id and status=0";
 $i= Database::getval($msql);
 if($i==0){echo "";}
 else{
 echo "(".$i.")";}
 ?>