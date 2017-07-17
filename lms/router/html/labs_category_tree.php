<?php
include('includes/conf.php');
$cidReset = true;
$user_id = api_get_user_id ();

require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'dept.lib.inc.php');

$sql = "SELECT `name` FROM `vslab`.`labs_category` WHERE parent_id=0 ORDER BY `tree_pos`";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );

$vm= array ();
$j = 0;

while ( $vm = Database::fetch_row ( $res) ) {
    $j++;
    //$vms [] = $vm;

    $category_tree[$j] = $vm[0];
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <script src="../../res/dtree/dept_tree.js"
            type=text/javascript></script>

    <link href="../../res/dtree/dtree.css"
          type=text/css rel=StyleSheet>

</head>
<body style="finc">

    <table cellspacing="0" cellpadding="0" width="100%" border="0" style="margin-left:15px">
        <tr><td><br>&nbsp;&nbsp;&nbsp;
            <script type=text/javascript>
                        d = new dTree('d');
                d.config.useCookies=true;
                d.add(0,-1,'<?=get_lang ( "全部课程" )?>','labs.php','','tab');
                 <?php

                    foreach ( $category_tree as $k1 => $v1){
                       echo 'd.add('.$k1.',0,"'.$category_tree[$k1].'","labs.php?labs_category='.$category_tree[$k1].'","","tab");'."\n";
                    }
                  ?>
                document.write(d);
            </script>
         </table>
</body>
</html>
