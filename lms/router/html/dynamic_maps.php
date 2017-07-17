<?php
$cidReset = true;
include('includes/conf.php');
include_once('hub.php');
include_once ("../../portal/sp/inc/app.inc.php");
$my_courses_all = CourseManager::get_user_subscribe_courses_code ( $user_id );
include_once ("../../portal/sp/inc/page_header.php");
$USERID=$_SESSION['_user']['user_id'];
?>
<link href="template_netmap.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery.contextmenu.js"></script>
<link rel="stylesheet" type="text/css" href="css/jquery.contextmenu.css" media="all">
<style type="text/css">
	html,body{height:100%;_height:100%;}
	html{overflow:sroll;} 
	.labtitle,.labContent,#tab{margin:0 22px;}
	.labtitle{background:#6E7471;  position:relative; height:40px;border:1px solid #6E7471;}
	.labtitle h3{background:#6DA8A6;color:#fff;padding:10px 20px 10px 20px;position:absolute;left:10px;top:10px;border-radius:5px 5px 5px; z-index:22;}
	.labtitle p{float:right;margin-right:20px;margin-top:8px;}
	.labtitle p img{ vertical-align:middle;}
	.labtitle p a.console{margin-left:10px;color:#030E1A;font-weight:bold;}
        .labContent{top:-5px;width:96%;background:url("<?=WEB_QH_PATH ?>/main/topo/demo/editors/images/grid.gif") repeat;position:relative;overflow:scroll; height:400px; border:5px solid #585F5B;}
	#tab{width:97%;margin-top:10px;}
	#tab caption{ height:45px;line-height:45px; text-align:left; font-weight:bold; text-indent:2em; background:url("<?=WEB_QH_PATH ?>/themes/images/secondary_bar.png") repeat-x;border:1px solid #9BA0AF; border-radius:5px 5px 0 0 ;}
	.Pagination{margin:10px 0;}
        .labtitle p a.openColor,.labtitle p a.closeColor{color:#030E1A; font-weight: bold;}
        .page-mark a:hover{color:black;font-weight: bold;}
       
<?php
	if (isset($_GET['id'])) {
		foreach (netmap_get_ids($_GET['id']) as $device) {
?>
		#node<?php print $device ?> { top: <?php print device_get_tops($device, $_GET['id']) ?>%; left: <?php print device_get_lefts($device, $_GET['id']) ?>% !important; }
<?php
		}
		foreach (netmap_get_hub_ids($_GET['id']) as $device) {
?>
		#node<?php print $device ?> { top: <?php print device_get_tops($device, $_GET['id']) ?>%; left: <?php print device_get_lefts($device, $_GET['id']) ?>% !important; }
<?php
		}
	}
?>
</style>
<script type="text/javascript">
    $(function(){
        $("#tm_bottom").remove();
    })
</script>
</head>
<body>
<?php
if($_GET['action']=='show' && isset($_GET['dev_id']) && isset($_GET['lab_id'])){
    $lab_id=htmlspecialchars(trim($_GET['lab_id']));
    $dev_id=htmlspecialchars(trim($_GET['dev_id']));

    $sql_d="select `name` from `labs_labs` where id=".$lab_id;
    $lab_name= DATABASE::getval($sql_d,__FILE__,__LINE__);

    $e_sql="select `id`,`labs_name`,`USERID`,`DEVICEID`,`DEVICEDTYPE`,`PORT`,`ROUTEMOD`,`DEVICEDNAME` from `labs_run_devices` where `DEVICEID`=".$dev_id." and `labs_name`='".$lab_name."' and `USERID`=".$USERID;
    $res=api_sql_query($e_sql,__FILE__,__LINE__);
    while ( $e_device = Database::fetch_row ( $res) ) {
        $e_device_arr[] = $e_device;
    }
    $d_id     =$e_device_arr[0][0];
    $labsName =$e_device_arr[0][1];
    $userId   =$e_device_arr[0][2];
    $deviceId =$e_device_arr[0][3];
    $DEVICEDTYPE =$e_device_arr[0][4];
    $PORT =$e_device_arr[0][5];
    $ROUTEMOD =$e_device_arr[0][6];
    $DEVICEDNAME =$e_device_arr[0][7];
    $config_export="cd /tmp/mnt/iostmp/$USERID/$lab_id/$DEVICEDTYPE"."_$d_id/$ROUTEMOD"."_i0_nvram";
    echo $config_export;
}
if($_GET['action']=='show' && $_GET['id']!==''){
    $ids=$_GET['id'];
    $sql_d="select `name` from `labs_labs` where id=".$ids;
    $names= DATABASE::getval($sql_d,__FILE__,__LINE__);
}
if(isset($_GET['status']) && $_GET['status']=='open' && $_GET['action']=='show' && $_GET['id']!==''){

        $sql_run_count="select count(*) from `vslab`.`labs_run_devices` where `labs_name`='".$names."' and `USERID`='".$USERID."'";
        $status=DATABASE::getval($sql_run_count,__FILE__,__LINE__);

        if($status==0){
            run_labs($USERID,$ids,$names,$DEVICEDNAME);
		exec("/tmp/www/$USERID'_'$ids'.sh' > /dev/null & ");
		exec("sleep 2 ;rm -rf  /tmp/www/$USERID'_'$ids'.sh'");
        }
}

    if(isset($_GET['status']) && $_GET['status']=='stop' && $_GET['action']=='show' && $_GET['id']!==''){
       
        $stop_sql="select `id`,`labs_name`,`USERID`,`DEVICEID`, `DEVICEDTYPE` , `PORT` from `labs_run_devices` where `labs_name`='".$names."' and `USERID`=".$USERID;
        $res=api_sql_query($stop_sql,__FILE__,__LINE__);

        $uport_stop_sql="select `uport` from `labs_run_devices` where  labs_name ='".$names."' and `USERID`=".$USERID;
        $ress=DATABASE::getval($uport_stop_sql,__FILE__,__LINE__);
        $status_array=explode(";",$ress); 
 	if(count($status_array)!==0){
		  for($i=0;$i<=count($status_array)-1;$i++){
			if($status_array[$i]!==''){
			  $delete_uport_sql="UPDATE  `vslab`.`uporthub` SET  `status` =  '0',`values`='0' WHERE  `Pid` =".$status_array[$i];
          	          api_sql_query($delete_uport_sql,__FILE__,__LINE__);
			} 
                }
	}
        

        while ( $stops = Database::fetch_row ( $res) ) {
            $stop_device[] = $stops;
        }
        foreach ( $stop_device as $device){
            $d_id     =$device[0];
            $labsName =$device[1];
            $userId   =$device[2];
            $deviceId =$device[3];
            $DEVICEDTYPE =$device[4];
            $PORT =$device[5];
            $delete_sql="DELETE FROM `vslab`.`labs_run_devices` WHERE `labs_run_devices`.`id` = ".$d_id;
            api_sql_query($delete_sql,__FILE__,__LINE__);

            $deletecommand="sudo -u root /sbin/cloudvmroutestop.sh  ".$userId." ".$ids." ".$userId."_".$ids."_".$DEVICEDTYPE."_".$deviceId." ".$userId."_".$ids."_".$PORT;
            exec("$deletecommand  >/dev/null &");

            //delete port
            $delete_tport_sql="UPDATE  `vslab`.`tporthub` SET  `status` =  '0',`values`='0' WHERE  `Pid` =".$PORT;
            api_sql_query($delete_tport_sql,__FILE__,__LINE__);
        }


    }
?>
    <br>
<h4 class="page-mark"> 
  <a href="<?=URL_APPEDND ?>/router/html/labs.php" title="路由实训">路由实训</a>  &gt;  
  <a href="" title="<?=lab_get_names($_GET['id'])?>"><?=lab_get_names($_GET['id'])?></a>
</h4>
<div id="templatemo_wrapper">
<?php
	if (isset($_GET['action'])) {
		$action = $_GET['action'];
	} else {
		$action = 'list';
	}

switch ($action) {
	default:
		// default is redirect to home page
			header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
			header("Location: ".$BASE_WWW."/");
			break;
/*************************************************************************
 * Display NETMAP                                                        *
 *************************************************************************/
	case 'show':
		if(isset($_GET['id'])) {
echo "<div class='labtitle'><h3>".lab_get_names($_GET['id'])."</h3>";
            create_run_device();
            $sql1="select count(*) from `labs_run_devices` where `labs_name`='".$names."' and `USERID`=".$USERID;
            $run_devices_count=DATABASE::getval($sql1,__FILE__,__LINE__);

            echo '<p><b><a  href="labs_document.php?id='.$_GET['id'].'"  title="打开实验手册" target="_blank">打开实验手册</a></b>&nbsp;&nbsp;&nbsp;';

            if($run_devices_count==0 ){
?>
		<!--	<p>-->

				<a href="dynamic_maps.php?action=show&status=open&id=<?=$ids ?>" title="打开实验" class="openColor"><img title="打开实验" width="20" height="20" src="images/play.png"><strong>加载试验</strong></a>
				<?php
            		}else{
             	?>
             	<a href="dynamic_maps.php?action=show&status=stop&id=<?=$ids ?>" title="关闭实验" class="closeColor"><img   title="关闭实验" width="20" height="20" src="images/stop.png"><strong>关闭实验</strong></a>
             	<a href="/<?=$USERID?>_<?=$ids?>.html" target="_blank" class="console">打开试验控制台</a>
				<?php
            }
				echo '</p></div><div class="labContent">';
				if (isset($_GET['id'])) {
					$netmap_array = explode("\n", lab_get_netmap($_GET['id']));
					$base_hub = BASE_HUB;
					
					// Count shared links (hubs)
					$hubs = 0;
					foreach ($netmap_array as $key => $value) {
						$tok = strtok($value, " ");
						$total = 0;
						while ($tok != false) {
							$total++;
							$tok = strtok(" ");
						}
						if ($total > 2) {
							$hubs++;
						}
					 }

					// Print all nodes
					foreach (netmap_get_ids($_GET['id']) as $device) {
        				$runportsql="select `PORT`,`status` from `labs_run_devices` where `DEVICEID` ='".$device."' and `labs_name`='".$names."' and `USERID`=".$USERID;
				        $resrunport=api_sql_query($runportsql,__FILE__,__LINE__);
				        $stops = Database::fetch_row ( $resrunport) ;
					$PORT=$stops[0];
                    $status=$stops[1];
?>

						<div class="window" id="node<?php print $device ?>">
						<?php
						if (isset($PORT)){//echo $PORT;
						?>
							<a href="<?php print '/'.$USERID.'_'.$ids.'_'.$PORT.'.html' ?>" target="_blank">
						<?php
						}
                                   			$deviceid="select `picture` from `labs_devices` where `id`=".$device;
                                   			$deviceType=DATABASE::getval($deviceid,__FILE__,__LINE__);
						if (isset($PORT) && $status==1){
						?>
                              			  <IMG class="dev_status" src="images/devices/<?=$deviceType ?>.png" />
						</a>
						<?php
						} else {
						?>
                               			 <IMG class="dev_status" src="images/devices/<?=$deviceType ?>_red.png" />
								<?php

						}
						?>
							<div class="name"><?php print device_get_names($device, $names) ?></div>
							<input type="hidden" class="dev_id" value="<?php print $device ?>">
						</div>
<?php
					}
					
					// Print all hubs
					$curr_hub = $base_hub;
					while ($hubs > 0) {
?>
						<div class="window" id="node<?php print $curr_hub ?>"><img src="images/devices/hub.png" alt="Hub" title="Hub"></div>
<?php
						$curr_hub++;
						$hubs--;
					}
?>

				<script type='text/javascript' src='js/jquery-1.8.2.min.js'></script>
				<script type='text/javascript' src='js/jquery-ui-1.8.23.custom.min.js'></script>
				<script type='text/javascript' src='js/jquery.jsPlumb-1.3.14-all-min.js'></script><!--changzf-->
				<script type="text/javascript" src="js/jquery.contextmenu.js"></script>
				<script type='text/javascript' src='js/netmap.js.php?id=<?php print $_GET['id'] ?>'></script>
				<SCRIPT type="text/javascript">
					$(document).ready(function() {
						setInterval('updateDeviceStatus()', 2000);
					});
					function updateDeviceStatus() {
						$('.dev_status').each(function() {
							var url = $(this).attr('src').split(':')[0];
//							$(this).attr('src', url + ':' + Math.random());
							$(this).attr('src', url);
						})
					}
				</SCRIPT>
                <script type="text/javascript">
                    var lab_id = '<?php print $_GET['id'] ?>';
                    var menu = [
                        { '启动': {
                            onclick:function(){
                                var dev_id = $(this).closest('.window').find('.dev_id').val();
                                startDevice(lab_id, dev_id);
                            },
                            icon:'/images/play_small.png'
                        } },
                        $.contextMenu.separator,
                        { '停止': {
                            onclick:function(){
                                var dev_id = $(this).closest('.window').find('.dev_id').val();
                                stopDevice(lab_id, dev_id);
                            },
                            icon:'/images/stop_small.png'
                        } }/**,
                        $.contextMenu.separator,
                        { '下载配置': {
                            onclick:function(){
                                var dev_id = $(this).closest('.window').find('.dev_id').val();
                                exportConfig(lab_id, dev_id);
                            },
                            icon:'/images/export_small.png'
                        } },
                        $.contextMenu.separator,
                        { '备份镜像': {
                            onclick:function(){
                                var dev_id = $(this).closest('.window').find('.dev_id').val();
                                backupConfig(lab_id, dev_id);
                            },
                            icon:'/images/backup_small.png'
                        } },
                        $.contextMenu.separator,
                        { '恢复配置': {
                            onclick:function(){
                                var dev_id = $(this).closest('.window').find('.dev_id').val();
                                restoreConfig(lab_id, dev_id);
                            },
                            icon:'/images/restore_small.png'
                        } },
                        $.contextMenu.separator,
                        { '抓包': {
                            onclick:function(){
                                var dev_id = $(this).closest('.window').find('.dev_id').val();
                                wipeConfig(lab_id, dev_id);
                            },
                            icon:'/images/wipe_small.png'
                        } },
                        $.contextMenu.separator,
                        { '打开控制台': {
                            onclick:function(){
                                var dev_id = $(this).closest('.window').find('.dev_id').val();
                                var telnet_port = (parseInt(dev_id) + <?php print BASE_PORT ?>);
                                document.location.href = 'telnet://<?php print $_SERVER['HTTP_HOST'] ?>:' + telnet_port;
                            },
                            icon:'/images/console_small.png'
                        } }**/
                    ];
                    $(function() {
                        $('.dev_status').contextMenu(menu,{theme:'vista'});
                    });


                    function startDevice(lab_id, dev_id) {
                        var url = 'ajax_helper.php?action=device_start&lab_id=' + lab_id + '&dev_id=' + dev_id;
                        window.location.href=url;
                    }
                    function stopDevice(lab_id, dev_id) {
                        var url = 'ajax_helper.php?action=device_stop&lab_id=' + lab_id + '&dev_id=' + dev_id;
                        window.location.href=url;
                    }/**
                    function exportConfig(lab_id, dev_id) {
                        var url = 'dynamic_maps.php?action=show&id=12&lab_id=' + lab_id + '&dev_id=' + dev_id;
                        window.location.href=url;
                    }
                    function backupConfig(lab_id, dev_id) {
                        var url = 'ajax_helper.php?action=config_backup&lab_id=' + lab_id + '&dev_id=' + dev_id;
                        window.location.href=url;
                    }
                    function restoreConfig(lab_id, dev_id) {
                        var url = 'ajax_helper.php?action=config_restore&lab_id=' + lab_id + '&dev_id=' + dev_id;
                        window.location.href=url;
                    }
                    function wipeConfig(lab_id, dev_id) {
                        var url = 'ajax_helper.php?action=config_wipe&lab_id=' + lab_id + '&dev_id=' + dev_id;
                        window.location.href=url;
                    }

                    function deleteLab(lab_id) {
                        var url = 'ajax_helper.php?action=lab_delete&lab_id=' + lab_id;
                        $.get(url);
                        $("body").fadeOut(1000, function() {
                            document.location.href = 'labs.php';
                        });
                    }**/
                </script>

	
<?php
			}
		}
		break;

}
   $cidReset = true;
  // include_once ("../portal/sp/inc/page_header.php");

   $sql_name="SELECT `name` from `labs_labs` where `id`=".$_GET['id'];
   $device_name=DATABASE::getval($sql_name,__FILE__,__LINE__);

   $page_size='10';
   $sql1 = "SELECT COUNT(*) FROM `labs_devices` WHERE  `lab_id`= '".$device_name."'";
//   $sql1 = "SELECT COUNT(*) FROM `configs`";
   if ($sql_where) $sql1 .=$sql_where;
   $total_rows = Database::get_scalar_value ( $sql1 );

   $sql1 = "SELECT `id`,`name`,`ios`,`slot`,`picture` FROM  `labs_devices` WHERE  `lab_id`= '".$device_name."'";
//   $sql1 = "SELECT * FROM  `configs`";
   if ($sql_where) $sql1 .=$sql_where;
   $offset = $_GET['offset'];
   if (isset($page_size)) $sql1 .= " LIMIT " . (empty ( $offset ) ? 0 : $offset) . "," . $page_size;

   $c = api_sql_query_array_assoc ( $sql1, __FILE__, __LINE__ );
   $rtn_data=array ("data_list" => $c, "total_rows" => $total_rows );

    $personal_course_list = $rtn_data ["data_list"];
    $total_rows = $rtn_data ["total_rows"];
    $url = WEB_QH_PATH . "syllabus.php?" . $param;
    $pagination_config = Pagination::get_defult_config ( $total_rows, $url, NUMBER_PAGE );
    $pagination = new Pagination ( $pagination_config );

?>
</div>
   <div id="tab">
   <table cellspacing="0" class="p-table" cellpadding="0" style="width: 100%">
    <caption> 网络设备信息</caption>
       <tr>
           <th class="dd2">编号</th>
           <th class="dd2">名称</th>
           <th class="dd2">设备型号</th>
           <th class="dd2">模块设置</th>
           <th class="dd4">设备类型</th>
       </tr>
   <?php
   if (is_array ( $personal_course_list ) && $personal_course_list) {
       ?>
       <?php
           for($i=0;$i<count($personal_course_list);$i++){
               ?>
           <tr>
               <td class="dd2" style="text-align: center;"><?=$personal_course_list[$i]['id']?></td>
               <td class="dd2" style="text-align: center;"><?=$personal_course_list[$i]['name']?></td>
               <td class="dd2" style="text-align: center;"><?=$personal_course_list[$i]['ios']?></td>
               <td class="dd2" style="text-align: center;"><?=$personal_course_list[$i]['slot']?></td>
               <td class="dd2" style="text-align: center;"><?=$personal_course_list[$i]['picture']?></td>
           </tr>
               <?php
           }
       ?>
       <?php
   } else { ?>
       <tr><td colspan="100" align="center">没有相关设备</td></tr>
       <?php  }  ?>
   </table>
       <div class="Pagination" style="float: right"><span class="f_l f6" style="margin-right: 10px;">总计 <b><?=$total_rows?></b> 条记录</span><?php echo $pagination->create_links (); ?></div>
   </div>
</body>
</html>
