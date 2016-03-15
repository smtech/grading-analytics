<?php

require_once('common.inc.php');

/* replace the contents of this file with your own app logic */

$api = new CanvasPest($_SESSION['apiUrl'], $_SESSION['apiToken']);

$settings = $toolProvider->user->getResourceLink()->settings;
if (isset($settings['custom_canvas_course_id'])) {
	include_once 'course-summary.php';
} elseif (isset($settings['custom_canvas_account_id'])) {
	include_once 'department-summary.php';
} else {
	$smarty->display('no-summary.tpl');
}

?>