<?php

require_once 'common.inc.php';

$output = [
    'type' => 'line',
    'data' => [
        'labels' => [],
        'datasets' => [
            [
                'data' => [],
                'pointRadius' => 0
            ]
        ]
    ],
    'options' => [
        'legend' => [
            'display' => false
        ]
    ]
];

$data = $toolbox->getCourseHistory($_REQUEST['course_id']);
usort($data, function ($left, $right) {
    return strtotime($left['timestamp']) - strtotime($right['timestamp']);
});

foreach ($data as $row) {
    $date = new DateTime($row['timestamp']);
    $output['data']['labels'][] = $date->format('M. j');
    $output['data']['datasets'][0]['data'][] = $row['average_grading_turn_around'];
}

echo json_encode($output, JSON_NUMERIC_CHECK);
