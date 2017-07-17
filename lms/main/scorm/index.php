<?php
$use_anonymous = true;
require_once ('back_compat.inc.php');
api_redirect ( 'lp_controller.php?' . api_get_cidReq () . '&action=list' );