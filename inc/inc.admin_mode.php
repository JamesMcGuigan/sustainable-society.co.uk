<?php

$admin_mode_backup;

function suspend_admin_mode() {
	global $admin_mode, $admin_mode_backup;
	if(isset($admin_mode_backup)) {
		$admin_mode_backup = $admin_mode;
	}
	$admin_mode = false;
}

function restore_admin_mode() {
	global $admin_mode, $admin_mode_backup;
	if(isset($admin_mode_backup)) {
			$admin_mode = $admin_mode_backup;
	}
}

?>