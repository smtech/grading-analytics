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

class Level {
	public static $GREATER_THAN = 0;
	public static $GREATER_THAN_OR_EQUAL = 1;
	public static $LESS_THAN_OR_EQUAL = 2;
	public static $LESS_THAN = 3;
	
	public $level;
	public $value;
	public $comparison;
	
	public function __construct($level, $value, $comparison) {
		$this->level = $level;
		$this->value = $value;
		$this->comparison = $comparison;
	}
}

$levels = array(
	'average_grading_turn_around' => array(
		'warning' => array(
			new Level(3, 14, Level::$GREATER_THAN),
			new Level(2, 7, Level::$GREATER_THAN)
		),
		'highlight' => array(
			new Level (3, 3, Level::$LESS_THAN),
			new Level (2, 7, Level::$LESS_THAN)
		)
	),
	'average_assignment_lead_time' => array(
		'warning' => array(
			new Level(3, 1, Level::$LESS_THAN),
			new Level(2, 2, Level::$LESS_THAN)
		),
		'highlight' => array(
			new Level(3, 10, Level::$GREATER_THAN),
			new Level(2, 7, Level::$GREATER_THAN)
		)
	),
	'average_submissions_graded' => array(
		'warning' => array(
			new Level(3, 0.5, Level::$LESS_THAN),
			new Level(2, 0.75, Level::$LESS_THAN)
		),
		'highlight' => array(
			new Level(3, 1.0, Level::$GREATER_THAN_OR_EQUAL),
			new Level(2, 0.9, Level::$GREATER_THAN)
		)
	),
	'dateless_assignment_count' => array(
		'warning' => array(
			new Level(3, 20, Level::$GREATER_THAN),
			new Level(2, 10, Level::$GREATER_THAN)
		),
		'highlight' => array(
			new Level(3, 1, Level::$LESS_THAN),
			new Level(2, 5, Level::$LESS_THAN)
		)
	),
	'gradeable_assignment_count' => array(
		'warning' => array(
			new Level(3, 0, Level::$LESS_THAN_OR_EQUAL)
		)
	),
	'graded_assignment_count' => array(
		'warning' => array(
			new Level(3, 0, Level::$LESS_THAN_OR_EQUAL)
		)
	),
	'created_after_due_count' => array(
		'warning' => array(
			new Level(3, 10, Level::$GREATER_THAN),
			new Level(2, 5, Level::$GREATER_THAN)
		),
		'highlight' => array(
			new Level(3, 1, Level::$LESS_THAN),
			new Level(2, 5, Level::$LESS_THAN)
		)
	),
	'zero_point_assignment_count' => array(
		'warning' => array(
			new Level(3, 10, Level::$GREATER_THAN_OR_EQUAL),
			new Level(2, 0, Level::$GREATER_THAN)
		)
	)
);

function getLevel($key, $value) {
	global $levels;
	foreach ($levels[$key] as $mode => $modeLevels)
	{
		foreach ($modeLevels as $level) {
			$match = false;
			switch ($level->comparison) {
				case Level::$GREATER_THAN:
					$match = $value > $level->value;
					break;
				case Level::$GREATER_THAN_OR_EQUAL:
					$match = $value >= $level->value;
					break;
				case Level::$LESS_THAN:
					$match = $value < $level->value;
					break;
				case Level::$LESS_THAN_OR_EQUAL:
					$match = $value <= $level->value;
					break;
			}
			if ($match) {
				return " class=\"$mode level-{$level->level}\"";
			}
		}
	}
	return "";
}

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
			<td>" . implode("<br />",array_unique(unserialize($statistic['teacher[sortable_name]s']))) . "</td>
			<td><a target=\"_blank\" href=\"" . SCHOOL_CANVAS_INSTANCE . "/courses/{$statistic['course[id]']}\">" . implode("<br />", explode(": ",$statistic['course[name]'])) . "</a></td>
			<td>" . $statistic['student_count'] . "</td>
			<td><span" . ($statistic['graded_assignment_count'] > 0 ? getLevel('average_grading_turn_around', $statistic['average_grading_turn_around']) : " class=\"warning level-3\"") . ">" . ($statistic['graded_assignment_count'] > 0 ? round($statistic['average_grading_turn_around'], 1) . " days" : 'No grades'). "</span></td>
			<td><span" . getLevel('average_assignment_lead_time', round($statistic['average_assignment_lead_time'])) . ">" . round($statistic['average_assignment_lead_time'], 1) . " days</span></td>
			<td><span" . getLevel('average_submissions_graded', $statistic['average_submissions_graded']) . ">" . round($statistic['average_submissions_graded']*100) . "%</span></td>
			<td>" . $statistic['assignments_due_count'] . "</td>
			<td><span" . getLevel('dateless_assignment_count', round($statistic['dateless_assignment_count'], 1)) . ">" . $statistic['dateless_assignment_count'] . "</span></td>
			<td><span" . getLevel('created_after_due_count', $statistic['created_after_due_count']) . ">" . $statistic['created_after_due_count'] . "</span></td>
			<td><span" . getLevel('gradeable_assignment_count', $statistic['gradeable_assignment_count']) . ">" . $statistic['gradeable_assignment_count'] .  "</span></td>
			<td><span" . getLevel('graded_assignment_count', $statistic['graded_assignment_count']) . ">" . $statistic['graded_assignment_count'] .  "</span></td>
			<td>" . (strlen($statistic['oldest_ungraded_assignment_name']) > 0 ? "<a href=\"{$statistic['oldest_ungraded_assignment_url']}\">{$statistic['oldest_ungraded_assignment_name']}</a><br />(due " . $oldestDueDate->format('F j, Y') . ")" : "-") . "</td>
			<td><span" . getLevel('zero_point_assignment_count', $statistic['zero_point_assignment_count']) . ">" . $statistic['zero_point_assignment_count'] . "</span></td>
			<td><a target=\"_blank\" href=\"{$statistic['gradebook_url']}\">Gradebook</a></td>
			<td><a target=\"_blank\" href=\"{$statistic['analytics_page']}\">Grading<br />Analytics</a></td>
		";
}
displayPage("
	<style type=\"text/css\">
		table {
			border-spacing: 0px 3px;
			border-collapse: collapse;
		}
	
		th, td {
			padding: .5em;
		}
		
		th {
			font-size: smaller;
		}
	
		td {
			white-space: nowrap;
		}
		
		.warning, .highlight {
			font-weight: bold;
			border-radius: 8px;
			padding: .25em .5em;
			white-space: nowrap;
		}
		
		.highlight.level-3 {
			background-color: #bbffbb;
			color: #006600;
		}
		
		.highlight.level-2 {
			background-color: #eeffee;
			color: #003300;
		}
		
		.warning.level-2 {
			background-color: #ffeebb;
			color: #665500;
		}
		
		.warning.level-3 {
			background-color: #ffbbbb;
			color: #990000;
		}
	</style>

	<h1>{$departments['name']} Daily Snapshot</h1>
	<p>" . $timestamp->format('F j, Y') . "</p>
	
	<table class=\"striped\">
		<tr>
			<th>Teacher(s)</th>
			<th>Course</th>
			<th>Students</th>
			<th>Turn-Around</th>
			<th>Lead Time</th>
			<th>Graded</th>
			<th>Due</th>
			<th>No Due Dates</th>
			<th>Retroactive Assignments</th>
			<th>Gradeable Assignments</th>
			<th>Graded Assignments</th>
			<th>Oldest Ungraded</th>
			<th>Zero-Point Assignments</th>
			<th colspan=\"2\">Course Links</th>
		</tr>
" . $snapshot . "
	</table>");

?>