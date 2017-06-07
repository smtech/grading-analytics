<?php

require_once 'common.inc.php';

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

$data = (empty($_REQUEST['department_id']) ? $toolbox->getSchoolSnapshot($_REQUEST['course_id']) : $toolbox->getDepartmentSnapshot($_REQUEST['course_id']));
usort($data, function ($left, $right) {
    return ($left['assignments_due_count'] + $left['dateless_assignment_count']) - ($right['assignments_due_count'] + $right['dateless_assignment_count']);
});
foreach ($data as $row) {
    $output['data']['datasets'][] = [
        'data' => [$row['assignments_due_count'] + $row['dateless_assignment_count']],
        'backgroundColor' => ($row['course[id]'] == $_REQUEST['course_id'] ? GRAPH_HIGHLIGHT_COLOR : GRAPH_DATA_COLOR)
    ];
}

echo json_encode($output, JSON_NUMERIC_CHECK);
