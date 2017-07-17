<?php
$language_file = array ('index', 'fx' );
include ("../../main/inc/global.inc.php");
api_block_anonymous_users ();
$ctx_path = api_get_path ( REL_PATH );


function get_menu_top() {
	$main_menu_table = Database::get_main_table ( TABLE_MAIN_MENU );
	$sql = "select * from " . $main_menu_table . " where is_enabled=1 and menu_no like '__' order by menu_no ";
	$sql_result = api_sql_query ( $sql, __FILE__, __LINE__ );
	$menuList = api_store_result_array ( $sql_result );
	return $menuList;
}

?>
<ul>
	<li><a href="../../main/admin/index.php" class="top" target="main"><?=get_lang ( "CampusHomepage" )?></a>
	<ul>
		<li><a href="../../portal/sp/" target="_blank">前台首页</a></li>
	</ul>
	</li>
	<li><a href="../../user_portal.php" target="main">我管理的课程</a></li>
	<?php
	if (api_is_platform_admin ()) :
		$menuList = get_menu_top (); //var_dump($menuList);
		foreach ( $menuList as $menu_id => $menu ) {
			if (is_display_menu_item ( $menu ['priv_roles'], $menu ['priv_status'] )) {
				$menuList2 = get_menu_item ( $menu ["menu_no"], 2 );
				$menuCount2 = count ( $menuList2 );
				echo '<li>';
				if (empty ( $menu ['menu_url'] )) {
					echo '<a href="#">' . $menu ["menu_name"] . '</a>';
				} else {
					echo '<a href="' . $ctx_path . $menu ['menu_url'] . '" class="top" target="main">' . $menu ["menu_name"] . '</a>';
				}
				if ($menuCount2 > 0) {
					echo '<ul>';
					foreach ( $menuList2 as $row2 ) {
						$menuList3 = get_menu_item ( $row2 ["menu_no"], 2 );
						$menuCount3 = count ( $menuList3 );
						echo '<li>';
						echo '<a href="' . (empty ( $row2 ['menu_url'] ) ? "#" : $ctx_path . $row2 ['menu_url']) . '"';
						if (! empty ( $row2 ['menu_url'] )) echo 'target="main"';
						echo '>' . $row2 ['menu_name'] . '</a>';
						if ($menuCount3 > 0) {
							echo '<ul>';
							foreach ( $menuList3 as $row3 ) {
								echo '<li><a href="' . $ctx_path . $row3 ['menu_url'] . '" target="main">' . $row3 ['menu_name'] . '</a></li>';
							}
							echo '</ul>';
						}
						echo '</li>';
					}
					echo '</ul>';
				}
				echo '</li>';
			}
		}
	
	endif;
	?>
</ul>


<div style="float: right">
<ul>
	<!-- <li><a href="#"
		onclick="LoadDialogWindow('version.php', self,(window.screen.width-520)/2,(window.screen.height-380)/2,520,380);">关于</a></li> -->
	<li><a href="#" onclick="javascript:top.main.location.reload();">刷新</a></li>	
	<li><a class="helpex dd2" id="confirmExit" target="_top"
		href="javascript:confirmExit();"><b>退出</b></a></li>
</ul>
</div>
<br style="clear: left" />