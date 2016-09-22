<?php

require_once __DIR__ . '/constants.inc.php';

$_SESSION['canvasInstanceUrl'] = 'https://' . $_SESSION['toolProvider']->user->getResourceLink()->settings['custom_canvas_api_domain'];

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