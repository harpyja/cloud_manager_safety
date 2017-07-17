<?php
	header("content-type:text/html;charset=utf-8");

$language_file = array ('admin', 'registration' );
$cidReset = true;

require ('../../inc/global.inc.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');

//require_once (api_get_path ( LIBRARY_PATH ) . 'networkmap.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'database.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$code = $_POST["code"];//request.open中的url传参
$xml = $code ;
//$name = 'dengxin';

//$xml = addlashes($xml);
//var_dump($xml);
//addlashes();
$id = $_GET['id'];
$table_net = Database::get_main_table ( TABLE_MAIN_NET );
//system('echo $id > /xx');
if($_GET['id']){

    $sql_data = array ('id' => $id,
        //'name' => $name,
        'xml' => addslashes($xml)
    );
    $sql = Database::sql_update ($table_net,$sql_data ,"id='$id'");
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
//$des = $content;
//$fh = fopen("a.html","at");
//fwrite($fh,$des);
//fclose($fh);
}
//else{
//
//    $sql_data = array ('name' => $name,
//        'content' => $content
//    );
//    $sql = Database::sql_insert ( $table_net, $sql_data );
//    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
//
//
//}

//var_dump($sql_data);


//	echo ($code);
//$c = $code;
//
//$des = $id;
//$fh = fopen("a.html","at");
//fwrite($fh,$des);
//fclose($fh);

?>