<?php

require_once 'common.inc.php';

use Battis\DataUtilities;
use smtech\GradingAnalytics\Snapshots\Domain;
use smtech\GradingAnalytics\Snapshots\Average;

$toolbox->getSmarty()->addStylesheet(DataUtilities::URLfromPath(__DIR__ . '/css/index.css.php'));
$toolbox->smarty_assign([
    'statistic' => $toolbox->getSnapshot(Domain::COURSE(), $_SESSION[COURSE_ID]),
    'averageTurnAround' =>
        $toolbox->getSnapshot(Domain::SCHOOL(), $_SESSION[COURSE_ID])
            ->getAverage(Average::TURN_AROUND()),
    'averageTurnAroundDepartment' =>
        $toolbox->getSnapshot(Domain::DEPARTMENT(), $_SESSION[COURSE_ID])
            ->getAverage(Average::TURN_AROUND()),
    'averageAssignmentCount' =>
        $toolbox->getSnapshot(Domain::SCHOOL(), $_SESSION[COURSE_ID])
            ->getAverage(Average::ASSIGNMENT_COUNT()),
    'averageAssignmentCountDepartment' =>
        $toolbox->getSnapshot(Domain::DEPARTMENT(), $_SESSION[COURSE_ID])
            ->getAverage(Average::ASSIGNMENT_COUNT())
]);
$toolbox->getSmarty()->addScript(
    DataUtilities::URLfromPath(__DIR__ . '/../vendor/npm-asset/chart.js/dist/Chart.min.js')
);
$toolbox->getSmarty()->addScript(
    DataUtilities::URLfromPath(__DIR__ . '/js/index.js')
);
$toolbox->smarty_display('course-summary.tpl');
