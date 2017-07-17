<?php
$language_file = array ('admin', 'registration', 'userInfo' );
require ('inc/global.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
api_block_anonymous_users ();
$table_user = Database::get_main_table ( TABLE_MAIN_USER );

$user_id = (isset ( $_REQUEST ['uid'] ) ? getgpc ( 'uid' ) : api_get_user_id ());
$deptObj = new DeptManager ();
$user_data = api_get_user_info ( intval($user_id) );

$image = $user_data ['picture_uri'];
$image_file = ((! empty ( $image ) && file_exists ( api_get_path ( SYS_PATH ) . "storage/users_picture/{$image}" )) ? api_get_path ( WEB_PATH ) . "storage/users_picture/{$image}" : api_get_path ( WEB_IMG_PATH ) . 'unknown.jpg');
$img_attributes = 'src="' . $image_file . '" alt="' . $user_data ['lastname'] . ' ' . $user_data ['firstname'] . '" style="padding:20px;" ';

Display::display_header ( get_lang ( 'UserInfo' ), FALSE );
?>
<table width=98% border=0>
    <tr>
        <td colspan="10"><br/></td>
    </tr>
	<tr>
		<td align=left valign=top>
		<table>
			<tr>
				<td><img <?=$img_attributes?> /></td>
			</tr>
			<tr>
				<td align="center"><?=$user_data ['firstname']?></td>
			</tr>
		</table>
		</td>

		<td width=84% valign=top>
		<table align="center" width="100%" cellpadding="4" cellspacing="0">
			<tr class="containerBody">
				<td class="formLabel"><?php
				echo get_lang ( "UserName" )?></td>
				<td class="formTableTd" align="left"><?=$user_data ['username']?></td>
				<td class="formLabel"><?php
				echo get_lang ( "FirstName" )?></td>
				<td class="formTableTd" align="left"><?=$user_data ['firstname']?></td>
			</tr>
			<tr class="containerBody">
				<td class="formLabel"><?php
				echo get_lang ( "OfficialCode" )?></td>
				<td class="formTableTd" align="left"><?=$user_data ['official_code']?></td>
				<td class="formLabel"><?php
				echo get_lang ( "Email" )?></td>
				<td class="formTableTd" align="left"><?=$user_data ['email']?></td>
			</tr>
			<?php
			if (api_is_platform_admin ()) {
				?>
			<tr class="containerBody">
				<td class="formLabel"><?=get_lang ( "CredentialType" )?></td>
				<td class="formTableTd" align="left"><?php
				if ($user_data ['credential_type'] == 1) {
					echo get_lang ( "IDCard" );
				} elseif ($user_data ['credential_type'] == 2) {
					echo get_lang ( "IDCard" );
				} elseif ($user_data ['credential_type'] == 3) {
					echo get_lang ( "StudentCard" );
				} else
					echo get_lang ( "None" );
				?>&nbsp;&nbsp;<?=$user_data ['credential_no']?></td>
				<td class="formLabel"><?php
				echo get_lang ( "Sex" )?></td>
				<td class="formTableTd" align="left"><?php
				if ($user_data ['sex'] == 1) {
					echo get_lang ( 'Male' );
				} elseif ($user_data ['sex'] == 2) {
					echo get_lang ( 'Female' );
				} else
					echo get_lang ( 'Secrect' );
				?></td>
			</tr>
			<?php
			}
			?>
			<tr class="containerBody">
				<td class="formLabel"><?php
				echo get_lang ( "PhoneWithAreaCode" )?></td>
				<td class="formTableTd" align="left"><?=$user_data ['phone']?></td>
				<td class="formLabel"><?php
				echo get_lang ( "MobilePhone" )?></td>
				<td class="formTableTd" align="left"><?=$user_data ['mobile']?></td>
			</tr>

			<tr class="containerBody">
				<td class="formLabel"><?php
				echo get_lang ( "UserInDept" )?></td>
				<td class="formTableTd" align="left"><?=get_dept_path ( $user_data ['dept_id'] )?></td>

				<td class="formLabel"><?php
				echo get_lang ( "RegistrationDate" )?></td>
				<td class="formTableTd" align="left"><?=$user_data ['registration_date']?></td>
			</tr>
<!--			<tr class="containerBody">-->
<!--				<td class="formLabel">--><?php
//				echo get_lang ( "SelfIntroduction" )?><!--</td>-->
<!--				<td class="formTableTd" align="left" colspan=3>--><?//=$user_data ['introduction']?><!--</td>-->
<!--			</tr>-->
		</table>

		</td>
	</tr>
</table>
<br>
<?php
Display::display_footer ();
