<?php
//$language_file = array ('admin' );
require_once ('../../inc/global.inc.php');





$table = Database::get_main_table ( vmtotal);
$sql = "select distinct addres,id FROM  $table group by addres";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );

$vm= array ();
$j = 0;
//var_dump($res);
while ( $vm = Database::fetch_row ( $res) ) {
    $j++;
    //$vms [] = $vm;

    $vms [$j] = $vm[0];
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title></title>
    <script src="<?=api_get_path ( WEB_PATH )?>res/dtree/dept_tree.js"
            type=text/javascript></script>
    <link href="<?=api_get_path ( WEB_PATH )?>res/dtree/dtree.css"
          type=text/css rel=StyleSheet>

</head>
<body>
<div class=dtree>
    <table cellspacing="5">
        <table cellspacing="5">

            <tr>
                <td><script type=text/javascript>
                    <!--
                    d = new dTree('d');
                    /*d.config.closeSameLevel=true;*/
                    
                    d.add(0,-1,'<?="地址"?>','vmmanage_list.php','','List');
                    <?php

                            foreach ( $vms as $k1 => $v1){
                                echo 'd.add('.$k1.',0,"'.$vms[$k1].'","vmmanage_list.php?vmaddres='.$vms[$k1].'","","List");'."\n";
                            }

                    ?>
                    document.write(d);

                    //-->
                </script></td>
            </tr>
        </table>
</div>
</body>
</html>