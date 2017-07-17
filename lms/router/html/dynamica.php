<?php
include('includes/conf.php');
page_header();
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="template_netmap.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery.contextmenu.js"></script>
<link rel="stylesheet" type="text/css" href="css/jquery.contextmenu.css" media="all">
<style type="text/css">
<?php
	if (isset($_GET['id'])) {
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
	}
?>
</style>
</head>
<body>
<div id="templatemo_wrapper">
<?php
if (isset($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$action = 'list';
}

switch ($action) {
/*************************************************************************
 * Display NETMAP                                                        *
 *************************************************************************/
	case 'show':
		if(isset($_GET['id'])) {
			page_main_open_lab(lab_get_names($_GET['id']));
?>
	<?php

				if (isset($_GET['id'])) {
					$netmap_array = explode("\n", lab_get_netmaps($_GET['id']));
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

					// Print all nodes
					foreach (netmap_get_ids($_GET['id']) as $device) {
?>
						<div class="window" id="node<?php print $device ?>">
							<a href="telnet://<?php print $_SERVER['HTTP_HOST'].':'.(BASE_PORT+$device) ?>"> <IMG class="dev_status" src="ajax_helper.php?action=device_status&lab_id=<?php print $names ?>&size=1&dev_id=<?php print $device ?>" /> </a>
							<div class="name"><?php print device_get_names($device, $names) ?></div>
							<input type="hidden" class="dev_id" value="<?php print $device ?>">
						</div>
					<?php
					} 
					// Print all hubs
					?>
<script type='text/javascript' src='js/jquery-1.8.2.min.js'></script>
<script type='text/javascript' src='js/jquery-ui-1.8.23.custom.min.js'></script>
<script type='text/javascript' src='js/jquery.jsPlumb-1.3.14-all-min.js'></script>
<script type="text/javascript" src="js/jquery.contextmenu.js"></script>
<script type="text/javascript" src="js/HashMap.js"></script>
<script type="text/javascript" src="js/jquery.jsIOUWebNetMap.js"></script>
<SCRIPT type="text/javascript">
      document.onselectstart = function () {  return false; };
		  jsPlumb.bind("ready", function() {
			jsPlumb.importDefaults({
			Anchor : "Continuous",
			Connector : [ "Straight" ],
			Endpoint : "Blank",
			PaintStyle : { lineWidth : 1, strokeStyle : "#000000" },
			cssClass:"link"//,         
			});
						// P2P Link
		  });
      $(document).ready(function() {
      var devices = [
      {devId: 1, devName:'·ÓÆ', type: "router", ports:{e: 2,s: 1, f:0} }
      ];
      jsPlumb.bind("endpointHover", function(connection) {
      	alert('hover');
      });
      jsPlumb.bind("beforeDrop", function(connection) {
      	alert('beforeDrop');
      });
      jsPlumb.bind("mouseenter", function(conn) {
      	alert("mouseenter : " + conn);
      });
      jsPlumb.bind("jsPlumbConnection", function(conn) {
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
             console.log(overlays[0].getLabel());
             jsIOUWebNetMap.logUsePort(sourceId + ":" + overlays[0].getLabel());
             jsIOUWebNetMap.logUsePort(targetId + ":" + overlays[1].getLabel());
             jsIOUWebNetMap.addNetMap(map);
             jsIOUWebNetMap.resetCurrentPort();
            });
            
        $("img#rountAddBtn").bind("click", function() {
            jsIOUWebNetMap.addRouter(devices[0]);
        });
        $("div#mapContainer").bind("mouseup", function(){
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
<?php
			}
		}
		break;
}

page_main_close();
page_footer();
?>
