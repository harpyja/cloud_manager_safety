<?php
header("content-type:text/html;charset=utf-8");
$language_file = array ('admin', 'registration' );
$cidReset = true;

require ('../../inc/global.inc.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
?>
<html>
<head>
    <title>编辑设备</title>
<style>
body{
    background: #D0DEE9;
}

</style>
<script>

    var  name  =  window.dialogArguments
    //alert("您传递的参数为："  +  name)
//    window.onload=function(){
//        var obj = window.dialogArguments;
//        alert(obj);
////        if(obj != null){
////            document.getElementById("divInfo").innerHTML = "Name:"+obj.Names+"<br/>"+"Age:"+obj.age+"<br/>"+"Address:"+obj.address;
////        }
//    }

    　　
    function foo1(){
	   DeviceFlag = document.getElementById( "Flag").value;
    InterNum = document.getElementById( "InterNum").value;
    off_on = document.getElementById( "off_on");
      off =  off_on.value;
    names = DeviceFlag;

    arr = [names,InterNum,off];
    window.returnValue = names;
    window.returnValue = arr;


    window.close();
	}

</script>
<script type="text/javascript" src="../../../themes/js/jquery.js"></script>
 <script type="text/javascript">
  function checkdesign(){    
           var initName=$("#hidn").val();    
           var values=$("#keywords").val();  
          $.ajax({
              type: "POST",
              url: "design_check.php",
              data:"device_type="+values+"&initNm="+initName,  
              cache:false,
              beforeSend: function(XMLHttpRequest){
              },
              success: function(data){
                        $("#Flags").empty();//清空
                        $("#Flags").append(data);   //给下拉框添加option 
                 },
              complete: function(XMLHttpRequest, textStatus){
              },
              error: function(){

              }
             });
  } 
</script>
</head>
<body>

        <?php
//所有类型
        $device = "select * from device_type ";
        $result = api_sql_query($device, __FILE__, __LINE__ );
        while ( $rst = Database::fetch_row ( $result) ) {
            $ste [] = $rst;
        }
        foreach ( $ste as $k1 => $v1){
            foreach($v1 as $k2 => $v2){
                $arr[$k1][]  = $v2;
            }
        }
$initName = getgpc("initName","G");   

$initName =  urldecode($initName);

foreach ($arr as $k1 => $v1){
    if($initName == $arr[$k1][1]){    
        $sql = "SELECT name FROM vmdisk where CD_mirror!='' &&  category='".$arr[$k1][1]."'";
        $res = api_sql_query ( $sql, __FILE__, __LINE__ );
        $vm= array ();
        while ( $vm = Database::fetch_row ( $res) ) {
            $vms [] = $vm[0];
        }
        $design = array_combine($vms,$vms);  
    }
}
  foreach ( $design as $v1){ 
            $ios.="<option value='$v1'>$v1</option>";
        }
     $designs="<select id='Flag' name='ios_name' onChange='getarea()' > ".$ios." </select>";
 
//        if($initName == 'route_'){
//            $design = array('ros1' => 'ros1', 'ros2'=> 'ros2');
//        }elseif($initName == 'firewall'){
//            $design = array('firewall_1' => 'firewall_1', 'firewall_2'=> 'firewall_2');
//        }elseif($initName == 'windowsxp_')
//        {
//
//            $sql = "SELECT name FROM vmdisk where category=1";
//            $res = api_sql_query ( $sql, __FILE__, __LINE__ );
//            $vm= array ();
//            while ( $vm = Database::fetch_row ( $res) ) {
//                $vms [] = $vm[0];
//            }
//
//            $design = array_combine($vms,$vms);
//        }elseif($initName == 'LABNET_'){
//            $design = array('LABNET_1' => 'LABNET_1','LABNET_2' => 'LABNET_2');
//        }elseif($initName == 'server_'){
//            $design = array('DNS' => 'DNS' ,'Email' => 'Email','FTP' => 'FTP','WEB' =>'WEB');
//        }elseif($initName == 'vpc_'){
//            $design = array('vpc_1' => 'vpc_1' ,'vpc_2' => 'vpc_2');
//        }


        //$design = array('ros' => 'ros', 'linux'=> 'debian');
        $Interface = array();
        for($i = 1 ;$i <7 ;$i++){
            $Interface[$i] = $i;
        }
        $off_on = array('1' => '开启控制台','0' => '关闭控制台');
        $form = new FormValidator ( 'view','post','topodesign.php' );
        $form->addElement ('select','off_on','',$off_on,array ( 'style' => "width:100px;font-weight:bold", 'id' => 'off_on' ) );
      ?>
    <input type="hidden" id="hidn" value='<?=$initName?>' >
      <?php 
      $form->addElement ( 'text', 'keywords', "<b>请输入设备标识关键字：</b>", array ( 'id' => 'keywords' ,onkeyup=>'checkdesign()' ) );
     //   $form->addElement ( 'select', 'Flag', "<b>请选择设备标识：</b>", $design,array ( 'style' => "width:100px;", 'id' => 'Flag' ) );
        $form->addElement ( 'html', '<div id="Flags">'.$designs.'</div>');
       
        $form->addElement ('select','InterNum','<b>请选择设备接口数:</b>',$Interface,array ( 'style' => "width:60px;font-weight:bold", 'id' => 'InterNum' ) );
                $form->addElement ('style_button', 'sub' ,null,array ( 'onclick' => "foo1()", 'value' => '确定','style' => "font-weight:bold" ) );


        $form->display ();

        echo "</div>";

        ?>

</body>
</html>
