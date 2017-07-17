
<?php
header("content-type:text/html;charset=utf-8");
?>
<html>
        <head>
        <title>录像描述输入窗口</title>
    </head>
<body>
<table class="tablebox">  
<center>
    <center><h4>请在下面输入录像描述</h4></center>
    <form name= "desc" action="cloudvmrec.php" method='post'> 
         <textarea id="edtInputWord" name ="desc" rows="3" cols="30" style="overflow-x:hidden;overflow-y:hidden"><?=trim($_GET['system'])?></textarea>
          <br>
          <input type="hidden" name="host" value="<?= trim($_GET['host']) ?>">
          <input type="hidden" name="port" value="<?= trim($_GET['port']) ?>">
          <center><input type="submit" value="确定"></center>
          </form>
 </center>
</table>
</body>
</html>