<?php
include('includes/conf.php');

/*************************************************************************
 * Display the network map                                               *
 *************************************************************************/
if (isset($_GET['action'])) {
	header('Content-type: image/png');
	switch ($_GET['action']) {
	default:
		// default is redirect to home page
			header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
			header("Location: ".$BASE_WWW."/");
			break;
		case 'netmap':
			// If there is an uploaded image print that
			$custom_img = lab_get_image($_GET['id']);
			if(isset($custom_img)) {
				print $custom_img;
			} else {
			// Else create a dynamic map
				netmap_print($_GET['id']);
			}
			break;
		case 'original':
			netmap_print($_GET['id']);
			break;
	}
}	
?>