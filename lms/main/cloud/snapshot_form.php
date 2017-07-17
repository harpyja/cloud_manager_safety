<?php
header("content-type:text/html;charset=utf-8");
?>
<html>
    <head>
        <title>图片描述输入窗口</title>
    </head>
<body>
<table class="tablebox">
    <center><h4>请在下面输入图片描述</h4></center>
<center><form name= "desc" action="cloudsnapshot.php" method='post'> 
         <textarea id="edtInputWord" name ="desc" rows="3" cols="30" style="overflow-x:hidden;overflow-y:hidden"></textarea>
         <br>
         <input type="hidden" name="host" value="<?= $_GET['host'] ?>">
          <input type="hidden" name="port" value="<?= $_GET['port'] ?>">
           <input type="submit" value="确定">
          </form>
 </center>
</table>
</body>
</html>
