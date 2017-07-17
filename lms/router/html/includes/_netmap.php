<?php
/**
 * @author Andrea Dainese
 */
/**
 * Function to get device ids from NETMAP
 *
 * @param	int		$id		Id of the lab
 */
function netmap_get_ids($lab_id) {
 	  $netmap = lab_get_netmap($lab_id);

	// putting router ID in a array
	$tok = strtok($netmap, " :\n");

	$nodes = array();
	$index=0;
	while ($tok !== false) {
		if (is_numeric($tok)) {
			$nodes[$index++] = $tok;
		}
		$tok = strtok(": \n");
	}

	sort($nodes);
	return array_unique($nodes);
}
/**
 * Function to get device hub ids from NETMAP
 *
 * @param	int		$id		Id of the lab
 */
function netmap_get_hub_ids($lab_id) {
	$netmap_array = explode("\n", lab_get_netmap($lab_id));
	$nodes = array();
	$index=0;
	$base_hub = BASE_HUB;

	// Per row analysis
	foreach ($netmap_array as $key => $value) {
		// How many devices?
		$tok = strtok($value, " ");
		$total = 0;
		while ($tok != false) {
			$total++;
			$tok = strtok(" ");
		}
		if ($total > 2) {
			$nodes[$index] = $base_hub;
			$base_hub++;
			$index++;
		}
	}
	return $nodes;
}
/**
 * Function to store NETMAP to bin
 *
 * @param	int		$id		Id of the lab
 */
function netmap_store($lab_id) {
	// remove existent startup-config
	$netmap = BASE_BIN."/NETMAP";

    $sql = "SELECT `netmap` FROM `labs_labs` WHERE id=".$lab_id;
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res);
        $config = $result[0];

	//Exporting to a file
	if (isset($netmap)) {
		$fp = fopen($netmap, 'w');
		fwrite($fp, $result[0]);
		fclose($fp);
	}
}
/**
 * Function to print a dynamic network map (PNG)
 *
 * @param	int		$id		Id of the lab
 */
function netmap_print($id) {
	$graph = new Image_GraphViz(true, array(), 'NETMAP', false);
	$graph->addAttributes(array(
		'layout' => 'dot',
		//'ratio' => 'compress',
		//'size' => '800,800!',
	));

	$node_attributes = array(
		'image' => '',
		'fontcolor' => 'black',
		'fontname' => GRAPHVIZ_FONT_NODE,
		'fontpath' => GRAPHVIZ_FONT_PATH,
		'fontsize' => '12',
		'label' => '',
		'labelloc' => 'b',
		'overlap' => 'false',
		'shape' => 'none',
		'URL'   => '',
	);
	
	$edge_attributes = array(
		'color' => 'black',
		'dir' => 'none',
		'fontname' => GRAPHVIZ_FONT_EDGE,
		'fontpath' => GRAPHVIZ_FONT_PATH,
		'fontsize' => '8',
		'headlabel' => '',
		'shape' => 'none',
		'style' => 'filled',
		'taillabel' => '',
	);

	if (isset($id)) {
		$netmap_array = explode("\n", lab_get_netmap($id));
		$nodes = array();
		$index=0;
		$base_hub = BASE_HUB;

		// Per row analysis
		foreach ($netmap_array as $key => $value) {
			// How many devices?
			$tok = strtok($value, " ");
			$total = 0;
			while ($tok != false) {
				$total++;
				$tok = strtok(" ");
			}
			
			// Now let's paint
			$tok = strtok($value, ": ");
			if (is_numeric($tok)) {
				// First and third are devices;
				// Second and fourth are interfaces;
				$routerA_ID = $tok;
				$routerA_int = strtok(": ");
				// If host specified, remove it
				if (strrpos($routerA_int, '@') > 0) {
					$routerA_int = substr($routerA_int, 0, strrpos($routerA_int, '@'));
				}
				// Check if serial or ethernet
				if (device_int_is_eth($routerA_ID, $_GET['id'], $routerA_int)) {
					$routerA_int = "e".$routerA_int;
				} else {
					$routerA_int = "s".$routerA_int;
				}
				
				$routerB_ID = strtok(": ");
				$routerB_int = strtok(": ");
				// If host specified, remove it
				if (strrpos($routerB_int, '@') > 0 ) {
					$routerB_int = substr($routerB_int, 0, strrpos($routerB_int, '@'));
				}
				// Check if serial or ethernet
				if (device_int_is_eth($routerB_ID, $_GET['id'], $routerB_int)) {
					$routerB_int = "e".$routerB_int;
				} else {
					$routerB_int = "s".$routerB_int;
				}

				$nodes[$index++] = $routerA_ID;
				$nodes[$index++] = $routerB_ID;

				if ($total < 3) {
					// P2P Link
					$edge_attributes['taillabel'] = $routerA_int;
					$edge_attributes['headlabel'] = $routerB_int;
					$graph->addEdge(array($routerA_ID => $routerB_ID), $edge_attributes);
				} else {
					// Shared Link
					//Creating an Hub
					$hub = $base_hub;
					$base_hub++;
					$nodes[$index++] = $hub;

					//Painting first two devices
					$edge_attributes['taillabel'] = $routerA_int;
					$edge_attributes['headlabel'] = '';
					$graph->addEdge(array($routerA_ID => $hub), $edge_attributes);

					$edge_attributes['taillabel'] = $routerB_int;
					$edge_attributes['headlabel'] = '';
					$graph->addEdge(array($routerB_ID => $hub), $edge_attributes);

					// Painting other devices
					$i = 2;
					while($i < $total) {
						$routerX_ID = strtok(": ");
						$routerX_int = strtok(": ");
						// If host specified, remove it
						if (strrpos($routerX_int, '@') > 0) {
							$routerX_int = substr($routerX_int, 0, strrpos($routerX_int, '@'));
						}
						$nodes[$index++] = $routerX_ID;
						$edge_attributes['taillabel'] = '';
						$edge_attributes['headlabel'] = $routerX_int;
						$graph->addEdge(array("$hub" => $routerX_ID), $edge_attributes);

						$i++;
					}
				}
			}
		}
	} else {
		$graph->addNode('N/A');
	}

	$nodes = array_unique($nodes);
	foreach ($nodes as $node) {
		if ($node > 10000) {
			// node is a HUB
			$node_attributes['image'] = BASE_DIR.'/html/images/devices/hub.png';
			$node_attributes['label'] = '';
			$node_attributes['URL'] = '';
			$graph->addNode($node, $node_attributes);
		} else {
			// node is not a hub
			$PORT = BASE_PORT + $node;
			$node_attributes['image'] = BASE_DIR.'/html/images/devices/'.device_get_pictures($node, $id).'.png';
			$node_attributes['label'] = device_get_names($node, $id);
			$node_attributes['URL'] = 'telnet://'.$_SERVER['HTTP_HOST'].':'.$node;
			$graph->addNode($node, $node_attributes);
		}
	}
	$graph->image('png');
}
?>
