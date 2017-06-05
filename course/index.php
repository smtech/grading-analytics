<?php

require_once 'common.inc.php';

use Battis\DataUtilities;

$statistics = $toolbox->mysql_query("
	SELECT * FROM `course_statistics`
		WHERE
			`course[id]` = '" . $_SESSION[COURSE_ID] . "'
		ORDER BY
			`timestamp` DESC
		LIMIT 1
");
$statistic = $statistics->fetch_assoc();

$toolbox->getSmarty()->addStylesheet(DataUtilities::URLfromPath(__DIR__ . '/css/index.css.php'));
$toolbox->smarty_assign([
    'statistic' => $statistic,
    'averageTurnAround' => $toolbox->averageTurnAround(),
    'averageTurnAroundDepartment' => $toolbox->averageTurnAround($statistic['course[account_id]']),
    'averageTurnAroundDepartment' => $toolbox->averageTurnAround($statistic['course[account_id]']),
    'averageAssignmentCount' => $toolbox->averageAssignmentCount(),
    'averageAssignmentCountDepartment' => $toolbox->averageTurnAround($statistic['course[account_id]'])
]);
$toolbox->smarty_display('course-summary.tpl');
