<?php

require_once 'common.inc.php';

use smtech\GradingAnalytics\Snapshots\Domain;

$snapshot = $toolbox->getSnapshot($_REQUEST['course_id'], Domain::COURSE());
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
