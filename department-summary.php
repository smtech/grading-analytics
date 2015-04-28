<?php

require_once('.ignore.grading-analytics-authentication.inc.php');
define('TOOL_START_PAGE', 'https://' . parse_url(CANVAS_API_URL, PHP_URL_HOST) . "/accounts/{$_REQUEST['department_id']}");

require_once(__DIR__ . '/smcanvaslib/config.inc.php');
require_once(__DIR__ . '/config.inc.php');
require_once(__DIR__ . '/common.inc.php');
require_once(SMCANVASLIB_PATH . '/include/canvas-api.inc.php');
require_once(SMCANVASLIB_PATH . '/include/mysql.inc.php');
require_once(SMCANVASLIB_PATH . '/include/page-generator.inc.php');

$departments = callCanvasApi(
	CANVAS_API_GET,
	"/accounts/{$_REQUEST['department_id']}"
);

$statistics = mysqlQuery("
	SELECT * FROM `course_statistics`
		WHERE
			`course[account_id]` = '{$_REQUEST['department_id']}'
		ORDER BY
			`timestamp` DESC,
			`course[name]` ASC
");

$snapshot = "";
$firstCourseId = false;
while (($statistic = $statistics->fetch_assoc()) && ($firstCourseId != $statistic['course[id]'])) {
	if (!$firstCourseId) {
		$firstCourseId = $statistic['course[id]'];
	}
	
	$timestamp = new DateTime($statistic['timestamp']);
	$oldestDueDate = new DateTime($statistic['oldest_ungraded_assignment_due_date']);

	$snapshot .= "
		<tr>
			<td>" . $timestamp->format('F j, Y') . "</td>
			<td>" . implode(", ",unserialize($statistic['teacher[sortable_name]s'])) . "</td>
			<td><a target=\"_blank\" href=\"" . SCHOOL_CANVAS_INSTANCE . "/courses/{$statistic['course[id]']}\">" . $statistic['course[name]'] . "</a></td>
			<td>" . $statistic['student_count'] . "</td>
			<td>" . round($statistic['average_assignment_lead_time'], 1) . " days</td>
			<td>" . round($statistic['average_grading_turn_around'], 1) . " days</td>
			<td>" . round($statistic['average_submissions_graded']*100) . "%</td>
			<td>" . $statistic['assignments_due_count'] . "</td>
			<td>" . $statistic['dateless_assignment_count'] . "</td>
			<td>" . $statistic['created_after_due_count'] . "</td>
			<td>" . $statistic['gradeable_assignment_count'] . "</td>
			<td>" . $statistic['graded_assignment_count'] . "</td>
			<td>" . (strlen($statistic['oldest_ungraded_assignment_name']) > 0 ? "<a href=\"{$statistic['oldest_ungraded_assignment_url']}\">{$statistic['oldest_ungraded_assignment_name']}</a> (due " . $oldestDueDate->format('F j, Y') . ")" : "-") . "</td>
			<td>" . $statistic['zero_point_assignment_count'] . "</td>
			<td><a target=\"_blank\" href=\"{$statistic['gradebook_url']}\">Gradebook</a></td>
			<td><a target=\"_blank\" href=\"{$statistic['analytics_page']}\">Grading Analytics</a></td>
		";
}
displayPage("
	<h1>{$departments['name']} Daily Snapshot</h1>
	
	<table class=\"striped\">
		<tr>
			<th>Timestamp</th>
			<th>Teacher(s)</th>
			<th>Course</th>
			<th>Students</th>
			<th>Average Grading Turn-Around</th>
			<th>Average Assignment Lead Time</th>
			<th>Average Submissions Graded per Assignment</th>
			<th>Assignments Due</th>
			<th>Assignments without Due Dates</th>
			<th>Retroactive Assignments</th>
			<th>Gradeable Assignments</th>
			<th>Graded Assignments</th>
			<th>Oldest Ungraded Assignment</th>
			<th>Zero-Point Assignments</th>
			<th colspan=\"2\">Course Links</th>
		</tr>
" . $snapshot . "
	</table>");

?>