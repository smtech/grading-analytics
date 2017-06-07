<?php

require_once 'common.inc.php';

$snapshot = $toolbox->getCourseSnapshot($_REQUEST['course_id']);
$data = unserialize($snapshot['created_modified_histogram']);

$output = [
    'type' => 'bar',
    'data' => [
        'labels' => array_keys($data[HISTOGRAM_CREATED]),
        'datasets' => [
            [
                'label' => 'Created',
                'data' => array_values($data[HISTOGRAM_CREATED]),
                'backgroundColor' => GRAPH_HIGHLIGHT_COLOR
            ], [
                'label' => 'Modified',
                'data' => array_values($data[HISTOGRAM_MODIFIED]),
                'backgroundColor' => GRAPH_DATA_COLOR
            ]
        ]
    ]
];

echo json_encode($output, JSON_NUMERIC_CHECK);
