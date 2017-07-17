<?php
$language_file = 'document';
include_once ("../inc/global.inc.php");
api_block_anonymous_users ();
api_protect_course_script ();

$cw_id =intval( getgpc ( 'cw_id', 'G' ));
$user_id = api_get_user_id ();
$course_code = api_get_course_code ();
$tbl_courseware = Database::get_course_table ( TABLE_COURSEWARE );
$sql = "SELECT * FROM $tbl_courseware WHERE cc='" . $course_code . "' AND id=" . Database::escape ( $cw_id );
$item = Database::fetch_one_row ( $sql, false, __FILE__, __LINE__ );
if (empty ( $item ) or $item ['cw_type'] != 'media') exit ();
$flv_path = $item ['path'];

$web_base_path = api_get_path ( WEB_COURSE_PATH ) . $course_code . "/document";

if ($flv_path) {
	$flv_file_url = $web_base_path . $flv_path;
	evnet_courseware ( $course_code, $user_id, $cw_id, 0, 'add' );
	event_cw_access_times ( $course_code, $user_id, $cw_id );
	event_cw_progress ( $course_code, $user_id, $cw_id, 100 );
}

$htmlHeadXtra [] = '<style type="text/css">
.example {	float: left;	margin: 15px;}
.demo {	width: 240px;	height: 400px;	border: solid 1px #000;background: #FFF;	overflow: scroll;	padding: 5px;}
</style>';

Display::display_header ( NULL, FALSE );

$player_swf = "player.swf";

?>
<!-- START OF THE PLAYER EMBEDDING TO COPY-PASTE -->
<?php
if ($flv_path) {
	?>
<center>
<div style="margin-top: 50px; width: 80%"><object
	type="application/x-shockwave-flash"
	data="player/vcastr3.swf?xml=<vcastr>
	<channel>
		<item>
			<source><?=$flv_file_url?></source>
			<duration></duration>
			<title></title>
		</item>
	</channel>
	<config>
<bufferTime>5</bufferTime>
<contralPanelAlpha>0.75</contralPanelAlpha>
<controlPanelBgColor>0xB31000</controlPanelBgColor>
<controlPanelBtnColor>0xffffff</controlPanelBtnColor>
<contralPanelBtnGlowColro>0xffff00</contralPanelBtnGlowColro>
<controlPanelMode>float</controlPanelMode>
<defautVolume>0.8</defautVolume>
<isAutoPlay>true</isAutoPlay>
<isLoadBegin>true</isLoadBegin>
<isShowAbout>true</isShowAbout>
<scaleMode>showAll</scaleMode>
</config>

 </vcastr>"
	width="650" height="500" id="vcastr3">
	<param name="movie"
		value="player/vcastr3.swf?xml=<vcastr>
	<channel>
		<item>
			<source><?=$flv_file_url?></source>
			<duration></duration>
			<title></title>
		</item>
	</channel>
	<config>
<bufferTime>5</bufferTime>
<contralPanelAlpha>0.75</contralPanelAlpha>
<controlPanelBgColor>0xB31000</controlPanelBgColor>
<controlPanelBtnColor>0xffffff</controlPanelBtnColor>
<contralPanelBtnGlowColro>0xffff00</contralPanelBtnGlowColro>
<controlPanelMode>bottom</controlPanelMode>
<defautVolume>0.8</defautVolume>
<isAutoPlay>true</isAutoPlay>
<isLoadBegin>true</isLoadBegin>
<isShowAbout>true</isShowAbout>
<scaleMode>showAll</scaleMode>
</config>
 </vcastr>" />
	<param name="allowFullScreen" value="true" />
</object></div>
</center>
<?php
}
?>
<!-- END OF THE PLAYER EMBEDDING -->

<?php
Display::display_footer ();
