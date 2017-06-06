<?php

require_once 'common.inc.php';

$query = "SELECT * FROM `course_statistics` WHERE `course[id]` = '" . $_REQUEST['course_id'] . "' ORDER BY `timestamp` DESC LIMIT 1";
$response = $toolbox->mysql_query($query);
$course = $response->fetch_assoc();
$timestamp = preg_replace('/^(.*)T.*$/', '$1', $course['timestamp']);

$query = "
    SELECT * FROM `course_statistics`
        WHERE
            `average_grading_turn_around` > 0 AND
            `timestamp` LIKE '$timestamp%' " .
            (
                isset($_REQUEST['department_id']) ? "
                AND `course[account_id]` = '{$_REQUEST['department_id']}'
                " :
                ''
            ) . "
        GROUP BY
            `course[id]`
        ORDER BY
            `average_grading_turn_around` ASC
";

$output = [
    'type' => 'bar',
    'data' => [
        'labels' => [''],
        'data' => []
    ],
    'options' => [
        'legend' => [
            'display' => false
        ]
    ]
];

if ($stats = $toolbox->mysql_query($query)) {
    while ($row = $stats->fetch_assoc()) {
        $output['data']['datasets'][] = [
            'data' => [$row['average_grading_turn_around']],
            'backgroundColor' => ($row['course[id]'] == $_REQUEST['course_id'] ? GRAPH_HIGHLIGHT_COLOR : GRAPH_DATA_COLOR)
        ];
    }
}

echo json_encode($output, JSON_NUMERIC_CHECK);
