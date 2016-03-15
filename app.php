<?php

require_once('common.inc.php');

/* replace the contents of this file with your own app logic */

$api = new CanvasPest($_SESSION['apiUrl'], $_SESSION['apiToken']);

if (null !== $course_id = $toolProvider->user->getResourceLink()->settings['custom_canvas_course_id']) {
	include_once 'course-summary.php';
} elseif (null !== $course_id = $toolProvider->user->getResourceLink()->settings['custom_canvas_account_id']) {
	include_once 'department-summary.php';
} else {
	$smarty->display('no-summary.tpl');
}

?>