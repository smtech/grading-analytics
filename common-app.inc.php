<?php

define('SCHOOL_TIME_ZONE', 'America/New_York'); // Go Sox! Stupid PHP.

define('DATA_COLLECTION_CRONTAB', '0 0 * * *'); // collect data every night at midnight

/* graph coloring (tied into CSS styling of summary text, so be sure to use
   values supported by PHPGraphLib _and_ CSS!
   http://www.ebrueggeman.com/phpgraphlib/documentation/function-reference#supported_colors */
define('GRAPH_2_WEEK_COLOR', 'red');
define('GRAPH_2_WEEK_STYLE', 'solid');
define('GRAPH_1_WEEK_COLOR', 'lime');
define('GRAPH_1_WEEK_STYLE', 'solid');
define('GRAPH_AVERAGE_COLOR', 'blue');
define('GRAPH_AVERAGE_STYLE', 'dashed');
define('GRAPH_HIGHLIGHT_COLOR', 'red');
define('GRAPH_DATA_COLOR', 'silver');

define('GRAPH_MIN_WIDTH', 1000); // the smallest a graph should be allowed to be in pixels
define('GRAPH_BAR_WIDTH', 10); // how many pixels to allocate per data point in a bar graph
define('GRAPH_ASPECT_RATIO', 0.375); // generic aspect ratio for all graphs
define('GRAPH_INSET_WIDTH', '40%'); // width of the inset departmental graphs in the column

define('HISTOGRAM_CREATED', 'Created');
define('HISTOGRAM_MODIFIED', 'Modified');


$GRAPH_DATA_COUNT = 0;
function graphWidth($dataCount = false) {
	if ($dataCount) {
		$GLOBALS['GRAPH_DATA_COUNT'] = $dataCount;
	}
	return max(GRAPH_MIN_WIDTH, $GLOBALS['GRAPH_DATA_COUNT'] * GRAPH_BAR_WIDTH);
}

function graphHeight($dataCount = false) {
	if ($dataCount) {
		$GLOBALS['GRAPH_DATA_COUNT'] = $dataCount;
	}
	return graphWidth() * GRAPH_ASPECT_RATIO;
}

function averageTurnAround($departmentId = false) {
	global $sql;
	$stats = $sql->query("
		SELECT * FROM `course_statistics`
			WHERE
				`average_grading_turn_around` > 0 " .
				(
					$departmentId ?
						"AND `course[account_id]` = '$departmentId' " :
						''
				) . "
			GROUP BY
				`course[id]`
			ORDER BY
				`timestamp` DESC
	");

	$total = 0;
	$divisor = 0;
	while ($row = $stats->fetch_assoc()) {
		$total += $row['average_grading_turn_around'] * $row['student_count'] * $row['graded_assignment_count'];
		$divisor += $row['student_count'] * $row['graded_assignment_count'];
	}
	return $total / $divisor;
}

function averageAssignmentCount($departmentId = false) {
	global $sql;
	$stats = $sql->query("
		SELECT * FROM (
			SELECT * FROM `course_statistics`" .
				(
					$departmentId ? "
						WHERE
							`course[account_id]` = '$departmentId'" :
						''
				) . "
				ORDER BY
					`timestamp` DESC
		) AS `stats`
			GROUP BY
				`course[id]` 
	");

	$total = 0;
	while ($row = $stats->fetch_assoc()) {
		$total += $row['assignments_due_count'] + $row['dateless_assignment_count'];
	}
	return $total / $stats->num_rows;
}