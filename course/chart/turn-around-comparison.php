<?php

require_once 'common.inc.php';

use smtech\GradingAnalytics\Snapshots\Domain;

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

$data = (empty($_REQUEST['department_id']) ? $toolbox->getSnapshot($_REQUEST['course_id'], Domain::SCHOOL()) : $toolbox->getSnapshot($_REQUEST['course_id'], Domain::DEPARTMENT()));
usort($data, function ($left, $right) {
    $compare = $left['average_grading_turn_around'] - $right['average_grading_turn_around'];
    if ($compare < 0) {
        return -1;
    } elseif ($compare > 0) {
        return 1;
    }
    return 0;
});
foreach ($data as $row) {
    $output['data']['datasets'][] = [
        'data' => [$row['average_grading_turn_around']],
        'backgroundColor' => ($row['course[id]'] == $_REQUEST['course_id'] ? GRAPH_HIGHLIGHT_COLOR : GRAPH_DATA_COLOR)
    ];
}

echo json_encode($output, JSON_NUMERIC_CHECK);
