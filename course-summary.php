<?php

require_once 'common.inc.php';

$statistics = $sql->query("
	SELECT * FROM `course_statistics`
		WHERE
			`course[id]` = '" . $course_id = $toolProvider->user->getResourceLink()->settings['custom_canvas_course_id'] . "'
		ORDER BY
			`timestamp` DESC
		LIMIT 1
");
$statistic = $statistics->fetch_assoc();

$smarty->addStylesheet("{$metadata['APP_URL']}/css/course-summary.css.php");
$smarty->assign('statistic', $statistic);
$smarty->assign('averageTurnAround', averageTurnAround());
$smarty->assign('averageTurnAroundDepartment', averageTurnAround($statistic['course[account_id]']));
$smarty->assign('averageAssignmentCount', averageAssignmentCount());
$smarty->assign('averageAssignmentCountDepartment', averageTurnAround($statistic['course[account_id]']));
$smarty->display('course-summary.tpl');

?>