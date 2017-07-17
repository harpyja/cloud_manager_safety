<?php
//changzf 2012/1/7
include_once('conf.php');

function lab_get_names($id) {
    $sql = "SELECT `name` FROM `labs_labs` WHERE `id`='".$id."'";
    $result = api_sql_query ( $sql, __FILE__, __LINE__ );
    $statement = Database::fetch_row ( $result);
    return  $statement[0];
}
function lab_get_descriptions($id) {
    $sql = "SELECT `description` FROM `labs_labs` WHERE `id`='".$id."'";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res);
    return $result[0];
}
function lab_has_images($id) {
    $sql = "SELECT `diagram` FROM `labs_labs` WHERE `id`=".$id." AND `diagram` != ''";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res);

    if (isset($result[0])) {
        return true;
    } else {
         return false;
    }
}
function lab_gets($id) {
    $sql = "SELECT  `name`, `description`, `info`, `netmap` FROM `labs_labs` WHERE `id`='".$id."'";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res);
    return $result ;
}
function lab_get_infos($id) {
    $sql = "SELECT `info` FROM `labs_labs` WHERE `id`='".$id."'";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res);
    return $result[0];

}
/**
 * @author Andrea Dainese
 */
/**
 * Function to list all initial configurations
 */
function lab_list() {
    $sql="SELECT `id`, `name`, `description`,`diagram` FROM `labs_labs` ORDER BY `name`";
    $res = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res);
    return $result;

}
/**
 * Function to insert a lab
 *
 * @param	string	$name			Name of the lab
 * @param	string	$description	Description of the lab
 * @param	string	$info			Additional info of the lab
 * @param	string	$netmap			NETMAP of the lab
 * @return	int						The ID of the last inserted lab
 */
function lab_add($name, $description, $info, $netmap) {
    $sql="INSERT INTO `labs_labs` (`name`, `description`,`info`,`netmap`) VALUES ('".$name."','".$description."','".$info."','".$netmap."');";
    api_sql_query( $sql, __FILE__, __LINE__ );
}
/**
 * Function to update a lab
 *
 * @param	int		$id				Id of the lab to update
 * @param	string	$name			Name of the lab
 * @param	string	$description	Description of the lab
 * @param	string	$info			Additional info of the lab
 * @param	string	$netmap			NETMAP of the lab
 */
function lab_update($id, $name, $description, $info, $netmap) {
    $sql="UPDATE `labs_labs` SET `name`='".$name."',`description`='".$description."',`info`='".$info."',`netmap`='".$netmap."' WHERE `id` ='".$id."'";
     api_sql_query ( $sql, __FILE__, __LINE__ );
}
/**
 * Function to update a lab
 * @param	int		$id				Id of the lab to update
 * @param	string	$diagram		BLOB of the updated image
 */
function lab_update_image($id, $diagram) {
        $sql = "UPDATE `labs_labs` SET `diagram`='".$diagram."' WHERE `id` ='".$id."'";
        api_sql_query ( $sql, __FILE__, __LINE__ );
}

function lab_get_netmap($id) {
    $sql = "SELECT `netmap` FROM `labs_labs` WHERE `id`='".$id."'";
    $res  = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res );
    return  $result[0];
}

/**
 * Function to delete a lab
 *
 * @param	int		$id				Id of the lab to update
 */

function lab_delete($id) {
    $sql = "DELETE FROM `labs_labs` WHERE `id`='".$id."'";
    api_sql_query ( $sql, __FILE__, __LINE__ );
    // Delete the devices
    $sql = "DELETE FROM `labs_devices` WHERE `lab_id`='".$id."'";
    api_sql_query ( $sql, __FILE__, __LINE__ );
}
/**
 * Function to delete an uploaded image from a lab
 *
 * @param	int		$id				Id of the lab to update
 */
function lab_delete_image($id) {
    $sql = "UPDATE `labs_labs` SET `diagram`='' WHERE `id`='".$id."'";
    api_sql_query ( $sql, __FILE__, __LINE__ );
}

/**
 * Function to check if a custom images is present
 *
 * @param	int		$id				Id of the lab to update
 * @return	bool					True if image is present
 */
function lab_has_image($id) {

    $sql = "SELECT `diagram` FROM `labs_labs` WHERE `id`='".$id."' AND `diagram` != ''";
    $res  = api_sql_query ( $sql, __FILE__, __LINE__ );
    $result = Database::fetch_row ( $res );
    return  $result[0];

		if (isset($result[0])) {
			return true;
		} else {
			return false;
		}

}
/**
 * Function to get a custom images
 *
 * @param	int		$id				Id of the lab to update
 * @return	blob					The uploaded image
 */
function lab_get_image($id) {
        $sql = "SELECT `diagram` FROM `labs_labs` WHERE `id`='".$id."' AND `diagram` != ''";
        $res = api_sql_query ( $sql, __FILE__, __LINE__ );
        $result = Database::fetch_row ( $res);
        return  $result[0];
}
/**
 * Function to get last lab id
 *
 * @return	int					The last lab id
 */
function lab_get_last() {
        $sql = "SELECT MAX(id) AS `last` FROM `labs_labs`";
        $res  = api_sql_query ( $sql, __FILE__, __LINE__ );
        $result = Database::fetch_row ( $res );
        return  $result[0];
}
?>
