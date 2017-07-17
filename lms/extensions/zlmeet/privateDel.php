<?php
header("Content-Type:text/xml;charset=UTF-8");
$fileName=trim($_GET['fileName']);
unlink("./upload/temp/{$fileName}");
?>

