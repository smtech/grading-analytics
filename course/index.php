<?php

require_once 'common.inc.php';

use Battis\DataUtilities;
use smtech\GradingAnalytics\Snapshots\Domain;
use smtech\GradingAnalytics\Snapshots\Average;
use smtech\GradingAnalytics\Snapshots\Snapshot;

$id = $_SESSION[COURSE_ID];
$department = new Snapshot($toolbox, Domain::DEPARTMENT(), $id);
$school = new Snapshot($toolbox, Domain::SCHOOL(), $id);

$toolbox->getSmarty()->addStylesheet(DataUtilities::URLfromPath(__DIR__ . '/css/index.css.php'));
$toolbox->smarty_assign([
    'statistic' => $toolbox->getSnapshot($id, Domain::COURSE()),
    'averageTurnAround' =>
        $school->getAverage(Average::TURN_AROUND()),
    'averageTurnAroundDepartment' =>
        $department->getAverage(Average::TURN_AROUND()),
    'averageAssignmentCount' =>
        $school->getAverage(Average::ASSIGNMENT_COUNT()),
    'averageAssignmentCountDepartment' =>
        $department->getAverage(Average::ASSIGNMENT_COUNT())
]);
$toolbox->getSmarty()->addScript(
    DataUtilities::URLfromPath(__DIR__ . '/../vendor/npm-asset/chart.js/dist/Chart.min.js')
);
$toolbox->getSmarty()->addScript(
    DataUtilities::URLfromPath(__DIR__ . '/js/index.js')
);
$toolbox->smarty_display('course-summary.tpl');
