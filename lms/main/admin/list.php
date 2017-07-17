<?php
header("Content-Type:text/html;charset=utf-8");
include ('../../main/inc/global.inc.php');
$ip_location=$_POST['ip_location'];

$allresult=mysql_query("select *from run_chart where ip_location='{$ip_location}' order by id asc limit 0,20");

while ($row=mysql_fetch_assoc($allresult))
        {

                            $allcpu[]=$row['cpu'];
                            //$arr=explode(" ",$row['time']);
                            $time=substr($row['time'],8);
                            //$time= explode("-",$times);
                            $alltime[]=$time;
                            $allmemory[]=$row['memory'];
                            $alldisc_1[]=$row['disc_1'];
                            $alldisc_2[]=$row['disc_2'];
                            $allvir[]=$row['virtual_number'];
                            $allonline[]=$row['online_number'];
        }
?>        
<script type='text/javascript'>
  $(function () {
        $('#Coludlab-map-cpu').highcharts({
            chart: {
                type: 'line'
            },
            credits:{
     enabled: false
},
            title: {
                text: '<div style=\"font-size:20px;\">CPU 、内存 、磁盘 走势</div>'
            },
            xAxis: {
                categories: [
<?php
         $timenumber=count($alltime)-1;       
        foreach($alltime as $k=>$v){
            if($timenumber==$k){
            echo "'".$v[0].$v[1]."时".$v[2].$v[3]."分".$v[4].$v[5]."秒"."'";
            }else{
            echo "'".$v[0].$v[1]."时".$v[2].$v[3]."分".$v[4].$v[5]."秒"."',";
            }
        }
?>
]
            },
            yAxis: {
                title: {
                    text: '百 分 比'
                }
                
            },
			tooltip: {
                crosshairs:true,
                shared:true
            },
            plotOptions: {
                line: {
                    dataLabels: {
                        enabled:false
                    },
                    enableMouseTracking:true
                }
            },
			
            series: [{
                name: 'C P U',
                data: [

<?php
        $cpunumber=count($allcpu)-1;
        foreach($allcpu as $v2=>$k2){
          if($v2==$cpunumber){
              echo $k2;
          }else{
              echo $k2.",";
          }  
        }
?>                   
]},{name:'内 存',data:[
                   
<?php            $memonumber=count($allmemory)-1;
                foreach($allmemory as $k3=>$v3){
                    if($k3==$memonumber){
                        echo $v3;
                    }else{
                        echo $v3.",";
                    }
                }
?> 
 ]},{name:'磁盘1',data:[
                   
<?php              $discnum_1=count($alldisc_1)-1;   
                   foreach($alldisc_1 as $k4=>$v4){
                      if($k4==$discnum_1){
                        echo $v4;
                    }else{
                        echo $v4.",";
                    } 
                   }
 ?>                       
]},{name:'磁盘2',data:[
                   
<?php              $discnum_2=count($alldisc_2)-1;   
                   foreach($alldisc_2 as $k5=>$v5){
                      if($k5==$discnum_2){
                        echo $v5;
                    }else{
                        echo $v5.",";
                    } 
                   }
?>                   
]
            }
]
        });
        $('#Coludlab-map-online').highcharts({
            chart: {
                type: 'line'
             },
           credits:{
     enabled: false
},
            title: {
                text: '<div style="font-size:20px;">在线用户 、在线虚拟机 走势图</div>'
            },
            xAxis: {
                categories: [
<?php
         $timenumber=count($alltime)-1;       
         foreach($alltime as $k=>$v){
            if($timenumber==$k){
            echo "'".$v[0].$v[1]."时".$v[2].$v[3]."分".$v[4].$v[5]."秒"."'";
            }else{
            echo "'".$v[0].$v[1]."时".$v[2].$v[3]."分".$v[4].$v[5]."秒"."',";
            }
        }
 ?>             ]
            },
            yAxis: {
                title: {
                    text: '百 分 比'
                },
               
                min:0
                },
			tooltip: {
                crosshairs:true,
                shared:true
            },
            plotOptions: {
                line: {
                    dataLabels: {
                        enabled:true
                    },
                    enableMouseTracking:true
                }
            },
			
            series: [{
                    name:'在线虚拟机',data:[

<?php              $virnum=count($allvir)-1;   
                   foreach($allvir as $k6=>$v6){
                      if($k6==$virnum){
                        echo $v6;
                    }else{
                        echo $v6.",";
                    } 
                   }
               ?>    
]
            },{
                    name:'在线用户',data:[
<?php              $counline=count($allonline)-1;   
                   foreach($allonline as $k7=>$v7){
                      if($k7==$counline){
                        echo $v7;
                    }else{
                        echo $v7.",";
                    } 
                   }
?>                        
                  ]
            }]
        });
    });
</script>