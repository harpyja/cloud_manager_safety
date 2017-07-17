<?php
//changzf 20130326
function device_get_tops($id, $lab_id) {
    $name_sql="select `name` from `labs_labs` where `id`=".$lab_id;
    $name_res=mysql_query($name_sql);
    $name_result=mysql_fetch_array($name_res);

    $top_sql = "SELECT  `top`  FROM  `labs_devices`  WHERE id ='".$id."' AND lab_id = '".$name_result[0]."'";
    $top_res=mysql_query($top_sql);
    $top_result=mysql_fetch_array($top_res);
    if ($top_result[0] != '') {
        return  $top_result[0];
    }else{
        return rand(20, 70);
    }
}

function device_get_lefts($id, $lab_id) {
    $name_sql="select `name` from `labs_labs` where `id`=".$lab_id;
    $name_res=mysql_query($name_sql);
    $name_result=mysql_fetch_array($name_res);

    $left_sql = "SELECT  `left`  FROM  `labs_devices`  WHERE id ='".$id."' AND lab_id = '".$name_result[0]."'";
    $left_res=mysql_query($left_sql);
    $left_result=mysql_fetch_array($left_res);

    if ($left_result[0] != '') {
        return $left_result[0];
    }else{
        return rand(20, 70);
    }
} 

function device_get_names($id, $lab_id) {
    $sql = "SELECT `name` FROM `labs_devices` WHERE id='".$id."' AND `lab_id`='".$lab_id."'";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res);
    return $result[0];
}
function device_get_pictures($id, $lab_id) {
    $sql = "SELECT `picture` FROM `labs_devices` WHERE id=".$id." AND `lab_id`='".$lab_id."'";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res);
    return $result[0];
}
function device_is_clouds($id, $lab_id) {
    $sql = "SELECT `picture` FROM `labs_devices` WHERE `id`=".$id." AND `lab_id`='".$lab_id."'";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res);
    if ($result[0] == "cloud") {
        return true;
    } else {
        return false;
    }
}
function device_get_ioss($id, $lab_id) {
    $sql = "SELECT `ios` FROM  `labs_devices`  WHERE `id` =".$id." AND `lab_id` ='".$lab_id."'";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res);
    return $result[0];
}
function device_get_rams($id, $lab_id) {
    $sql = "SELECT `ram` FROM `labs_devices` WHERE `id` =".$id." AND `lab_id` ='".$lab_id."'";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res);
    return $result[0];
}
function device_get_nvrams($id, $lab_id) {
    $sql = "SELECT `nvram` FROM `labs_devices` WHERE `id` ='".$id."' AND `lab_id` ='".$lab_id."'";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res);
    return $result[0];
}
function device_get_ethernets($id, $lab_id) {
    $sql = "SELECT `ethernet` FROM `labs_devices` WHERE `id` ='".$id."' AND `lab_id` ='".$lab_id."'";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res);
    return $result[0];
}
function device_get_serials($id, $lab_id) {
    $sql = "SELECT `serial` FROM `labs_devices` WHERE `id` ='".$id."' AND `lab_id` ='".$lab_id."'";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res);
    return $result[0];
}
function device_get_confs($id, $lab_id) {
    $sql = "SELECT `conf_id` FROM `labs_devices` WHERE `id` =".$id." AND `lab_id` ='".$lab_id."'";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res);
    return $result[0];
}
function  get_conf_name($id) {
    $sql = "SELECT `name` FROM `configs` WHERE `id` =".$id;
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res);
    return $result[0];
}

function device_update_positions($id, $lab_id, $top, $left) {
    $sql = "UPDATE `devices` SET `top`='".$top."', `left`='".$left."' WHERE `id`='".$id."' AND `lab_id`='".$lab_id."'";
    api_sql_query ( $sql, __FILE__, __LINE__ );
}


 /**
 * Function to get device status picture
 *
 * @param	int		$id		ID of the device
 * @param	int		$lab_id	Lab's ID of the device
 * @return	string			The picture represeting the status of the device
 */
function device_get_picture_status($id, $lab_id) {
	if(exec_device_is_running($lab_id, $id)) {
		return BASE_WWW."/images/devices/".device_get_pictures($id, $lab_id)."_running.png";
	} else {
		return BASE_WWW."/images/devices/".device_get_pictures($id, $lab_id)."_stopped.png";
	}
}
 /**
 * Function to get device color (status)  picture
 *
 * @param	int		$id		ID of the device
 * @param	int		$lab_id	Lab's ID of the device
 * @return	string			The picture represeting the status of the device
 */
function device_get_picture_color($id, $lab_id) {
	if(exec_device_is_running($lab_id, $id)) {
        return BASE_WWW."/images/devices/".device_get_pictures($id, $lab_id)."_red.png";

	} else {
        return BASE_WWW."/images/devices/".device_get_pictures($id, $lab_id).".png";
	}
}

 /**
 * Function to get device's ios filename
 *
 * @param	int		$id		ID of the device
 * @param	int		$lab_id	Lab's ID of the device
 * @return	string			IOS (filename) of the device
 */

function device_get_iosbin($id, $lab_id) {
    $sql = "SELECT `bins`.`name` as `bin` FROM `bins`, `devices` WHERE `devices`.`ios`=`bins`.`alias` AND `devices`.`id`=".$id." AND `devices`.`lab_id`=".$lab_id;
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res);
    return $result[0];
}

 /**
 * Function to check is interface is ethernet
 *
 * @param	int		$id		ID of the device
 * @param	int		$lab_id	Lab's ID of the device
 * @param	string	$int	Interface (i.e. 2/1)
 * @return	bool			Return true if int is Ethernet
 */
function device_int_is_eth($id, $lab_id, $int) {
	$eth = device_get_ethernets($id, $lab_id);
	// if eth is not set, use the default
	if ($eth == '') $eth = 1;
	
	$ser = device_get_serials($id, $lab_id);
	// if ser is not set, use the default
	if ($ser == '') $ser = 1;
	
	// get the portgroup number
	$portgroup = substr($int, 0, strpos($int, '/'));
	
	if ($portgroup <= $eth - 1) {
		return true;
	} else {
		return false;
	}
}

 /**
 * Function to get device's console port
 *
 * @param	int		$id		ID of the device
 * @param	int		$lab_id	Lab's ID of the device
 * @return	int				Number of console port of the device
 */
function device_get_console($id, $lab_id) {
	return BASE_PORT + $id;
}

/**
 * Insert or Update a device in a laboratory
 *
 * @param	int		$id			ID of the device
 * @param	int		$lab_id		Lab's ID of the device
 * @param	string	$name		device's name
 * @param	string	$ios		device's IOS (alias)
 * @param	int		$ram		device's ram
 * @param	int		$nvram		device's nvram
 * @param	int		$ethernet	device's ethernet
 * @param	int		$serial		device's serial
 * @param	string	$picture	device's picture
 * @param	int		$conf		device's conf ID
 */
function device_update($id, $lab_id, $name, $ios, $ram, $nvram, $ethernet, $serial, $picture, $conf_id, $top, $left) {

//    $sql = "INSERT  INTO `devices` (`id`,`lab_id`,`name`,`ram`,`nvram`,`ios`,`ethernet`,`serial`,`picture`,`conf_id`,`top`,`left`) VALUES ('".$id."','".$lab_id."','".$name."','".$ios."','".$ram."','".$nvram."','".$ethernet."','".$serial."','".$picture."','".$conf_id."','".$top."','".$left."')";
    $sql = "UPDATE `devices` SET `name`='".$name."',`ios`='".$ios."',`ram`='".$ram."',`nvram`='".$nvram."',`ethernet`='".$ethernet."',`serial`='".$serial."',`picture`='".$picture."',`conf_id`='".$conf_id."',`top`='".$top."',`left`='".$left."' WHERE `id` ='".$id."' and `lab_id`='".$lab_id."'";
    $result=api_sql_query ( $sql, __FILE__, __LINE__ );
    if ($result==''){
        echo "执行SQL失败:$sql<BR>错误:".mysql_error();
    }else{
        echo "<br>".$lab_id."&nbsp;";
        echo $name."&nbsp;";
        echo $ios."&nbsp;";
        echo $ram."&nbsp;";
    }

}


 /**
 * Function to get startup-config of a device
 *
 * @param	int		$id		ID of the device
 * @param	int		$lab_id	Lab's ID of the device
 */
function device_store_startup($id, $lab_id) {
    // remove existent startup-config
    $startup = device_get_running_path($lab_id, $id)."/config-".sprintf("%05d", $id);
    if (file_exists($startup)) {
        unlink($startup);
    }
    $sql = "SELECT `configs`.`config` as  `config` FROM `devices`, `configs` WHERE `configs`.`id`=`devices`.`conf_id` AND `devices.id`='".$id."' AND `devices`.`lab_id`='".$lab_id."'";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res);
    $config = $result[0];

    //Exporting to a file
    if (isset($config)) {
        $fp = fopen($startup, 'w');
        fwrite($fp, $result['config']);
        fclose($fp);
    }
}
 /**
 * Function to export startup-config from a device to DB
 *
 * @param	int		$id		ID of the device
 * @param	int		$lab_id	Lab's ID of the device
 */
function device_export_startup($lab_id, $id) {
	//Import running, if doesn't exist import startup and if it doesn't exist impost initial config.
	$running_config = device_get_running_path($lab_id, $id)."/running-config";
	$startup_config = device_get_running_path($lab_id, $id)."/startup-config";
	$initial_config = device_get_running_path($lab_id, $id)."/config-".sprintf("%05d", $id);
	$name = "Export: ".lab_get_names($lab_id)." - ".device_get_names($id, $lab_id);

	if (file_exists($running_config)) {
		$import_file = $running_config;
	} elseif (file_exists($startup_config)) {
		$import_file = $startup_config;
	} else {
		$import_file = $initial_config;
	}
	
	$fp = fopen($import_file, 'r') or die("<p>Cannot open file $import_file.</p>");
	$content = fread($fp, filesize($import_file));
	try {
		$config_id = config_add($name, $content);
	} catch(PDOException $e) {
		echo $e->getMessage();
	}
	fclose($fp);

}
/**
 * Function to prepare IOU environment for an isolated instance
 *
 * @param	int		$id		ID of the device
 * @param	int		$lab_id	Lab's ID of the device
 */
function device_prepare_environment($lab_id, $id) {
	$dev_path = device_get_running_path($lab_id, $id);

	// Populating the new environment (iourc, iou binary and wrapper)
	if (!file_exists($dev_path."/iourc")) {
		symlink(BASE_BIN."/iourc", $dev_path."/iourc");
	}
	if (!file_exists($dev_path."/".device_get_iosbin($id, $lab_id))) {
		symlink(BASE_BIN."/".device_get_iosbin($id, $lab_id), $dev_path."/".device_get_iosbin($id, $lab_id));
	}

	// Builinf the NETMAP
	// sed command must have a single \ escaped with a \
	// cat /opt/iou/bin/NETMAP | grep -v ^# | sed 's/\([0-9]*\):\([0-9]*\)\/\([0-9]*\)/\1:\2\/\3@dev_\.iou/g' | sed 's/@dev_id.iou//g' > NETMAP
	$command1 = "cat /opt/iou/bin/NETMAP | grep -v ^# | sed 's/\\([0-9]*\\):\\([0-9]*\\)\\/\\([0-9]*\\)/\\1:\\2\\/\\3@dev_\\1.webiol/g' | sed 's/@dev_".$id.".webiol//g' | sed 's/dev_[0-9]*\.webiol@webiol/webiol/g' > ".$dev_path."/NETMAP";
	exec($command1, $output, $pid);

	return $dev_path;
}

?>
