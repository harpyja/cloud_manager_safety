<?php
$language_file = array ('courses','admin');
include_once ("../../inc/global.inc.php");
$this_section = SECTION_PLATFORM_ADMIN;
api_block_anonymous_users();

api_protect_admin_script ();

require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'dept.lib.inc.php');

$sql = "SELECT `name` FROM `vslab`.`labs_category` WHERE parent_id=0 ORDER BY `tree_pos`";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );

$vm= array ();
$j = 0;

while ( $vm = Database::fetch_row ( $res) ) {
    $j++;
    $category_tree[$j] = $vm[0];
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <script src="<?=api_get_path ( WEB_PATH )?>res/dtree/dept_tree.js"
            type=text/javascript></script>

    <link href="<?=api_get_path ( WEB_PATH )?>res/dtree/dtree.css"
          type=text/css rel=StyleSheet>

</head>
<body>

    <table cellspacing="0" cellpadding="0" width="100%" border="0">
        <tr><td><br>&nbsp;&nbsp;&nbsp;
            <script type=text/javascript>
                        d = new dTree('d');
                d.config.useCookies=true;
                d.add(0,-1,'<?=get_lang ( "CourseCategories" )?>','labs_category.php','','LabsList');
                 <?php

                    foreach ( $category_tree as $k1 => $v1){
                       echo 'd.add('.$k1.',0,"'.$category_tree[$k1].'","labs_category.php?name='.$category_tree[$k1].'","","LabsList");'."\n";
                    }
                  ?>
                document.write(d);
            </script>
         </table>

</body>
</html>
