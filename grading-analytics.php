<?php

header('Content-Type: application/javascript');

require_once('.ignore.custom-prefs-authentication.inc.php');
require_once(__DIR__ . '/config.inc.php');
require_once(SMCANVASLIB_PATH . '/include/mysql.inc.php');
require_once(SMCANVASLIB_PATH . '/include/cache.inc.php');

if (isset($_REQUEST['user_id'])) {
	if (is_numeric($_REQUEST['user_id'])) {
		$userId = $_REQUEST['user_id'];
	} else {
		$userId = preg_replace('|.*users/(\d+)/?.*|', '$1', $_REQUEST['user_id']);
	}
}

if (!isset($userId) || !strlen($userId)) {
	exit;
}

/* get the current user's preferences */
$response = mysqlQuery("
	SELECT * FROM `users`
		WHERE
			`id` = '" . $userId . "'
");
$userPrefs = $response->fetch_assoc();
$userPrefs['groups'] = unserialize($userPrefs['groups']);

if ($userPrefs['role'] != 'faculty') {
	exit;
}

?>
function stmarks_addGradingAnalyticsButton(courseSecondary) {
	var analyticsUrl = /courses\/\d+\/analytics/;
	var courseAnalyticsButton = null;
	var courseOptions = courseSecondary.getElementsByClassName('course-options')[0].children;
	for (var i = 0; i < courseOptions.length; i++) { if (analyticsUrl.test(courseOptions[i].href)) { courseAnalyticsButton = courseOptions[i]; } }
	if (courseAnalyticsButton != null) {
		var courseUrl = /.*\/courses\/(\d+).*/;
		var courseId = document.location.href.match(courseUrl)[1];
		var gradingAnalyticsButton = document.createElement('a');
		gradingAnalyticsButton.target = '_blank';
		gradingAnalyticsButton.href = '<?= APP_URL ?>/course-summary.php?course_id=' + courseId;
		gradingAnalyticsButton.className = 'button-sidebar-wide';
		gradingAnalyticsButton.innerHTML = '<i class="icon-analytics"></i> View Grading Analytics';
		courseAnalyticsButton.parentElement.appendChild(gradingAnalyticsButton);
	}
}

function stmarks_gradingAnalytics() {
	stmarks_waitForDOMById(/courses\/\d+/, 'course_show_secondary', stmarks_addGradingAnalyticsButton);
}
