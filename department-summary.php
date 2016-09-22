<?php

require_once 'common.inc.php';

use smtech\StMarksSmarty\StMarksSmarty;

$smarty->enable(StMarksSmarty::MODULE_SORTABLE);

$account_id = $course_id = $toolProvider->user->getResourceLink()->settings['custom_canvas_account_id'];

$departments = $api->get("/accounts/$account_id");

/* find the most recent day's timestamp */
$response = $sql->query("
    SELECT * FROM `course_statistics`
        WHERE `course[account_id]` = '$account_id'
        ORDER BY
            `timestamp` DESC
        LIMIT 1
");
$row = $response->fetch_assoc();
preg_match('/(\d{4,4}-\d{2,2}-\d{2,2}).*/', $row['timestamp'], $match);

$response = $sql->query("
	SELECT * FROM `course_statistics`
		WHERE
			`course[account_id]` = '$account_id' AND `timestamp` like '{$match[1]}%'
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

$statistics = array();
$firstCourseId = false;
while (($statistic = $response->fetch_assoc()) && ($firstCourseId != $statistic['course[id]'])) {
	if (!$firstCourseId) {
		$firstCourseId = $statistic['course[id]'];
	}
	// FIXME this shouldn't be hard coded!
	$statistic['analytics_page'] = "{$_SESSION['canvasInstanceUrl']}/courses/{$statistic['course[id]']}/external_tools/1174";
	$statistics[] = $statistic;
}

$smarty->addStylesheet("{$metadata['APP_URL']}/css/department-summary.css");
if (isset($smarty->register_function)) {
	$smarty->register_function('getLevel', 'getLevel');
}
$smarty->assign('statistics', $statistics);
$smarty->assign('departments', $departments);
$smarty->display('department-summary.tpl');

?>
