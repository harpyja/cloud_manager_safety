<?php

/**
 * Function to check if a device is running
 *
 * @param	int		$lab	ID of the lab
 * @param	int		$device	ID of the device
 *
 * @return	bool			True if running
 */
function exec_device_is_running($lab, $device) {
	if(device_is_clouds($device, $lab)) {
		$command = "pgrep iou2net";
		exec($command, $output, $pid);
		if ($pid == 0) {
			return true;
		} else {
			return false;
		}
	} else {
		$command = "fuser -n tcp ".device_get_console($device, $lab);
		exec($command, $output, $pid);
		if ($pid == 0) {
			return true;
		} else {
			return false;
		}
	}
}

 ?>
