<?php

require_once 'common.inc.php';

use Battis\DataUtilities;
use smtech\StMarksSmarty\StMarksSmarty;
use smtech\ReflexiveCanvasLTI\LTI\ToolProvider;
use smtech\GradingAnalytics\HeatMap\HeatMap;

/*
 * FIXME this should not be hard-coded -- roles should be configurable.
 */
$unrestrictedRoles = [
    'AccountAdmin',
    'Dean',
    'Department Chair'
];

$toolbox->getSmarty()->enable(StMarksSmarty::MODULE_SORTABLE);

$account_id = $_SESSION[ACCOUNT_ID];

$departments = $toolbox->api_get('/accounts/' . $_SESSION[ACCOUNT_ID]);

/* verify user privileges */
$user_id = $_SESSION[ToolProvider::class]['canvas']['user_id'];
$restricted = true;
$permissionsAccount = $departments;
do {
    $roles = $toolbox->api_get(
        "/accounts/{$permissionsAccount['id']}/admins",
        [
            'user_id[]' => $user_id
        ]
    );
    foreach ($roles as $role) {
        if (in_array($role['role'], $unrestrictedRoles)) {
            $restricted = false;
            break;
        }
    }
    if ($restricted && !empty($permissionsAccount['parent_account_id'])) {
        $permissionsAccount = $toolbox->api_get("/accounts/{$permissionsAccount['parent_account_id']}");
    } else {
        $permissionsAccount = false;
    }
} while ($restricted && $permissionsAccount !== false);

/* find the most recent day's timestamp */
$response = $toolbox->mysql_query("
    SELECT * FROM `course_statistics`
        WHERE `course[account_id]` = '$account_id'" .
        ($restricted ? "AND `teacher[id]s` regexp 'a:[0-9]+:\{(i:[0-9]+;i:[0-9]+;)*i:[0-9]+;i:$user_id;'" : '') . "
        ORDER BY
            `timestamp` DESC
        LIMIT 1
");
$row = $response->fetch_assoc();
preg_match('/(\d{4,4}-\d{2,2}-\d{2,2}).*/', $row['timestamp'], $match);

$response = $toolbox->mysql_query("
    SELECT * FROM `course_statistics`
        WHERE
            `course[account_id]` = '$account_id' AND `timestamp` like '{$match[1]}%'" .
            ($restricted ? "AND `teacher[id]s` regexp 'a:[0-9]+:\{(i:[0-9]+;i:[0-9]+;)*i:[0-9]+;i:$user_id;'" : '') . "
        ORDER BY
            `timestamp` DESC,
            `course[name]` ASC
");

$statistics = array();
$firstCourseId = false;
while (($statistic = $response->fetch_assoc()) && ($firstCourseId != $statistic['course[id]'])) {
    if (!$firstCourseId) {
        $firstCourseId = $statistic['course[id]'];
    }
    // FIXME this shouldn't be hard coded!
    $statistic['analytics_page'] = $_SESSION[CANVAS_INSTANCE_URL] . "/courses/{$statistic['course[id]']}/external_tools/1638";
    $statistics[] = $statistic;
}

$toolbox->getSmarty()->addStylesheet(DataUtilities::URLfromPath(__DIR__ .  '/css/index.css'));
$toolbox->getSmarty()->registerPlugin(
    'function',
    'getLevel',
    [
        HeatMap::class,
        'getLevel'
    ]
);
$toolbox->smarty_assign([
    'statistics' => $statistics,
    'departments' => $departments
]);
$toolbox->smarty_display('department-summary.tpl');
