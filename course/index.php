<?php

require_once 'common.inc.php';

use Battis\DataUtilities;
use smtech\GradingAnalytics\Toolbox;

$toolbox->getSmarty()->addStylesheet(DataUtilities::URLfromPath(__DIR__ . '/css/index.css.php'));
$toolbox->smarty_assign([
    'statistic' => $toolbox->getCourseSnapshot($_SESSION[COURSE_ID]),
    'averageTurnAround' => $toolbox->averageTurnAround($_SESSION[COURSE_ID], Toolbox::SCHOOL),
    'averageTurnAroundDepartment' => $toolbox->averageTurnAround($_SESSION[COURSE_ID], Toolbox::DEPT),
    'averageAssignmentCount' => $toolbox->averageAssignmentCount($_SESSION[COURSE_ID], Toolbox::SCHOOL),
    'averageAssignmentCountDepartment' => $toolbox->averageAssignmentCount($_SESSION[COURSE_ID], Toolbox::DEPT)
]);
$toolbox->getSmarty()->addScript(
    DataUtilities::URLfromPath(__DIR__ . '/../vendor/npm-asset/chart.js/dist/Chart.min.js')
);
$toolbox->getSmarty()->addScript(
    DataUtilities::URLfromPath(__DIR__ . '/js/index.js')
);
$toolbox->smarty_display('course-summary.tpl');
