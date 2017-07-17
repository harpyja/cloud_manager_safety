<?php
$cidReset = true;
include('includes/conf.php');
include_once('hub.php');
include_once ("../../portal/sp/inc/app.inc.php");
include_once ("../../portal/sp/inc/page_header.php");
$my_courses_all = CourseManager::get_user_subscribe_courses_code ( $user_id );
$USERID=$_SESSION['_user']['user_id'];
$id = $_GET['id'];
?>
<div style="height:100%; background:red;">
<iframe src="/lms/router/html/dynamic_maps.php?action=show&id=<?=$id?>"  frameborder="0" scrolling="no" width="100%" height="100%"></iframe>
</div>
</body>
</html>
