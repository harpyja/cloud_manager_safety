<?php
include('includes/conf.php');
//page_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" http://www.w3.org/TR/html4/loose.dtd>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="template_netmap.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery.contextmenu.js"></script>
<link rel="stylesheet" type="text/css" href="css/jquery.contextmenu.css" media="all">
<style type="text/css">
    <?php

    foreach (netmap_get_ids($_GET['id']) as $device) {
        ?>
    #node<?php print $device ?> { top: <?php print device_get_tops($device, $_GET['id']) ?>%; left: <?php print device_get_lefts($device, $_GET['id']) ?>%; }
        <?php
    }
    foreach (netmap_get_hub_ids($_GET['id']) as $device) {
        ?>
    #node<?php print $device ?> { top: <?php print device_get_tops($device, $_GET['id']) ?>%; left: <?php print device_get_lefts($device, $_GET['id']) ?>%; }
        <?php

    }
    ?>
</style>
<script type='text/javascript' src='js/jquery-1.8.2.min.js'></script>
<script type='text/javascript' src='js/jquery-ui-1.8.23.custom.min.js'></script>
<script type='text/javascript' src='js/jquery.jsPlumb-1.3.14-all-min.js'></script>
<script type="text/javascript" src="js/jquery.contextmenu.js"></script>
<script type='text/javascript' src='js/netmapJs.php?id=<?php print $_GET['id'] ?>'></script>
<script type="text/javascript" src="js/HashMap.js"></script>
<script type="text/javascript" src="js/jquery.jsIOUWebNetMap.js"></script>

<SCRIPT type="text/javascript">
    document.onselectstart = function () {  return false; };
    var targetNode, sourceNode , sourcePort ,targetPort;
    function addLiMouseUpListener(d){
        $(d).find("ul li").bind("mouseup", function() {
            targetNode = $(this).parent().parent().attr("id");
            targetPort = $(this).find("a").html();
            // alert("target: " + targetNode);
            //if (targetNode === sourceNode && sourcePort === targetPort) {
            //  $(".window ul").hide();
            //return;
            //}

            // Verify this node
            if (jsIOUWebNetMap.isUsedPort(targetNode + ":" + targetPort)) {
                sourceNode = "";
                sourcePort = "";
                alert("This target port is used!");
                return;
            }

            //   console.log("starting build connection: " + targetPort + sourcePort);

            jsPlumb.connect({
                source: sourceNode ,
                target: targetNode ,
                //     paintStyle:{lineWidth:3,strokeStyle:'rgb(0,0,0)'},
                overlays:[
                    [ "Label", {label:sourcePort, location:0.15, cssClass:"label"}],
                    [ "Label", {label:targetPort, location:0.85, cssClass:"label"}]
                ]
            });

            jsIOUWebNetMap.hidePopWindows();
            jsIOUWebNetMap.resetCurrentPort();
        });
    }

    function imageListener(imgDom){
        var currentNode =  $(imgDom).parent().attr("id");
        var ulDom =   $(imgDom).parent().find("ul");
        ulDom.show();     // show this router's popWin

        // set popWin used ports' color
        $.each(ulDom.find("li"), function(index, obj) {
            var aDom =    $(obj).find("a");
            var currentPort = aDom.html();

            var nodePort = currentNode + ":" +  currentPort;

            console.log("Port: " + nodePort + "isUsed: "+jsIOUWebNetMap.isUsedPort(nodePort));

            if (jsIOUWebNetMap.isUsedPort(nodePort)) {
                console.log("used port add color");
                aDom.css("color", "green");
            }
        });   // end each
    }

    jsPlumb.bind("ready", function() {
        jsPlumb.importDefaults({
            Anchor : "Continuous",
            //Connector : [ "Bezier", { curviness: 50 } ],
            Connector : [ "Straight" ],
            Endpoint : "Blank",
            //  paintStyle:{lineWidth:3,strokeStyle:'rgb(0,0,0)'},
            PaintStyle : { lineWidth : 1, strokeStyle : "#000000" },
            cssClass:"link"//,
        });

    });


    $(document).ready(function() {
        // var curNodeIndex = 1 ,
        var    devices = [
            {devId: 1, devName:'路由器', type: "router", ports:{e: 2,s: 1, f:0} }
        ];

        jsPlumb.bind("endpointHover", function(connection) {
            alert('hover');
        });
        jsPlumb.bind("beforeDrop", function(connection) {
            alert('beforeDrop');
        });

        jsPlumb.bind("mouseenter", function(conn) {
            //alert('before drop');
            alert("mouseenter : " + conn);
        });

        jsPlumb.bind("jsPlumbConnection", function(conn) {
            // alert("jsPlumbConnection");
            $("div.window ul").hide();
            var connection =     conn.connection;
            var sourceId =    connection.sourceId;
            var targetId =    connection.targetId;
            console.log("build connection: source: " + sourceId + " targetId: " + targetId);
            if(sourceId === targetId)  {  // it connect itself, return
                jsPlumb.detach(conn);
                return;
            }

            var overlays =    connection.overlays

            var map = sourceId + ":" +overlays[0].getLabel() + " "+targetId +":"+  overlays[1].getLabel() ;
            //     var sourcePort = overlays[0].getLabel();
            //  console.log("sourcePort: " +  sourcePort);
            console.log(overlays[0].getLabel());
            jsIOUWebNetMap.logUsePort(sourceId + ":" + overlays[0].getLabel());
            jsIOUWebNetMap.logUsePort(targetId + ":" + overlays[1].getLabel());
            jsIOUWebNetMap.addNetMap(map);

            jsIOUWebNetMap.resetCurrentPort();
        });


        $("img#rountAddBtn").bind("click", function() {
            jsIOUWebNetMap.addRouter(devices[0]);
            //  jsIOUWebNetMap.initMakeTarget();
        });
        $("div#mapContainer").bind("mouseup", function(){
//              alert("container clear");
            $("div.window ul").hide();
            jsIOUWebNetMap.resetCurrentPort();
        });
    });
    function updateAllNode() {
        updateNodePosition(1);
        updateNodePosition(2);
        updateNodePosition(3);
        updateNodePosition(5);
    }
    function updateNodePosition(node) {
        var p = $("#node" + node);
        var position = p.position();
        $('#' + node + 'left').val(100 * position.left / $(window).width());
        $('#' + node + 'top').val(100 * position.top / $(window).height());
    }
</SCRIPT>
<SCRIPT type="text/javascript">
    $(document).ready(function() {
        setInterval('updateDeviceStatus()', 2000);

        $('.window').find("ul li.eq").each(function(i, e) {
            //  alert($(e).html());
            jsPlumb.makeSource($(e), {
                parent: $(e).parent().parent(),
                anchor:"Continuous",
                connector:[ "Straight" ],
                connectorStyle:{ strokeStyle:'rgb(243,230,18)', lineWidth:3 }/*,
                    maxConnections:5,
                    onMaxConnections:function(info, e) {
                        alert("Maximum connections (" + info.maxConnections + ") reached");
                    }*/
            });
        });

        $('.window').find("img").bind("mouseover", function() {
            // if (sourcePort != "") {
            $(this).parent().find("ul").show();
            imageListener(this);
            //}
        });

        addLiMouseUpListener('.window');
        liMouseDownListener('.window ul li') ;
    });

    function liMouseDownListener(li) {
        $(li).bind('mousedown', function() {
            //	alert($(this).html());
            sourceNode = $(this).parent().parent().attr("id");
            sourcePort = $(this).find("a").html();
            //  alert("source: "+ sourceNode);
            console.log(sourceNode + ":" + sourcePort);
            if (jsIOUWebNetMap.isUsedPort(sourceNode + ":" + sourcePort)) {
                sourceNode = "";
                sourcePort = "";
                alert("This source port is used!");
            }
        });


    }

    function updateDeviceStatus() {
        $('.dev_status').each(function() {
            var url = $(this).attr('src').split(':')[0];
//							$(this).attr('src', url + ':' + Math.random());
            $(this).attr('src', url);
        })
    }

    function popWin(){
        var ulDom = $(this);
        alert(ulDom.html());
        //ulDom.show();
    }
    function getnetmap(){
        var netmap=jsIOUWebNetMap.getNetMap().toString();
        document.cookie="data="+netmap;
        document.cookie="id=<?php echo  $_GET['id']?>";
        window.location.href="labs_netmap_s.php";
    }

</SCRIPT>

</head>
<body>

<div id="mapContainer" class="col_w900 col_w900_last" style="border: 1px solid green; ">
    <div id="action_buttons">
        <!--            <button onclick="alert(jsIOUWebNetMap.getNodes().toString())">export nodes</button>-->
        <form name="form1" method="post" action="netmap.php">
            <a href="#" onclick="getnetmap()">导出拓扑</a>
        </form>
        <!--            双击添加:<img id="rountAddBtn" src="images/devices/router.png" alt="添加一个人" title="添加一个人">-->
    </div>
    <?php
   $device_name=$_GET['name'];


    $action = 'show';
    switch ($action) {
        case 'show':
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

                $ids=$_GET['id'];
                $sql="select `name` from `labs_labs` where id=".$ids;
                $names= DATABASE::getval($sql,__FILE__,__LINE__);

                $sql="SELECT `id`,`lab_id`,`name`,`ios`,`slot`,`picture`,`conf_id` FROM  `labs_devices` WHERE `lab_id` = '".$device_name."'";
                $res = api_sql_query ( $sql, __FILE__, __LINE__ );
                while($ss = Database::fetch_array ( $res )){
                    $default[] = $ss;
                }

                // Print all nodes
                foreach (netmap_get_ids($_GET['id']) as $device) {
                    ?>
                    <div class="window" id="node<?php print $device ?>">

                        <?php
                        $deviceid="select `picture` from `labs_devices` where `id`=".$device;
                        $deviceType=DATABASE::getval($deviceid,__FILE__,__LINE__);
                        ?>
                        <IMG  src="images/devices/<?=$deviceType ?>.png" title="<?php print device_get_names($device, $names) ?>"  onclick="$('#ulnode<?php print $device ?>').show();"/>

                        <div class="name"><?php print device_get_names($device, $names) ?></div>
                        <ul id="ulnode<?php print $device ?>" style="display: none; ">
                            <?php
//                            echo $device.'&nbsp;';
                            if(isset($_GET['action']) && $device_name!==''){
//                                echo count($default).'条记录&nbsp;';
                                for($i=0;$i<=count($default);$i++){
                                    if($default[$i]['id']==$device){
                                         $slots=explode(';',$default[$i]['slot']);
                                        array_pop( $slots);
                                        $p=0;
                                        foreach($slots as $mod){

                                            $sqls="select `size`,`interface_type` from `labs_mod` where `mod_name`='".$mod."'";

                                            $sqlss = api_sql_query ( $sqls, __FILE__, __LINE__ );

                                            $vm= array ();
                                            while ( $vm = Database::fetch_row ( $sqlss) ) {
                                                //port number
                                                $size=explode(',',$vm[0]);
                                                for($q=0;$q<=$size[1];$q++){
                                                    //port type
                                                   if($vm[1]=='以太网口'){?>
                                                        <li class="eq" id="jsPlumb_<?php print $device ?>_<?php print $q ?>"><a href="#" title="e<?php print $p ?>/<?php print $q ?>">e<?php print $p ?>/<?php print $q ?></a></li>
<?php                                                    }
                                                 if($vm[1]=='串口'){     ?>
                                                        <li class="eq" id="jsPlumb_<?php print $device ?>_<?php print $q ?>"><a href="#" title="s<?php print $p ?>/<?php print $q ?>">s<?php print $p ?>/<?php print $q ?></a></li>
<?php                                                    }
                                                }

                                            }$p=$p+1;
                                        }
                                    }
                               }
                            }
                            ?></ul>
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

            }

            break;
    }


    ?>
    <div id="nodePanel"></div>
</div>
<?php


page_main_close();
page_footer();

?>