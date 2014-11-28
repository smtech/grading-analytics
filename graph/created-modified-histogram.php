<?php

require_once(__DIR__ . '/../smcanvaslib/config.inc.php');
require_once(__DIR__ . '/../config.inc.php');
require_once(__DIR__ . '/../common.inc.php');
require_once(__DIR__ . '/../.ignore.grading-analytics-authentication.inc.php');
require_once(SMCANVASLIB_PATH . '/include/mysql.inc.php');
require_once(SMCANVASLIB_PATH . '/include/phpgraphlib/phpgraphlib.php');

$stats = mysqlQuery("
	SELECT `created_modified_histogram` AS `histogram` FROM `course_statistics`
	WHERE
		`course[id]` = '{$_REQUEST['course_id']}'
	ORDER BY
		`timestamp` DESC
	LIMIT
		1
");

$data = $stats->fetch_assoc();
$data = unserialize($data['histogram']);

$graph = new PHPGraphLib(graphWidth(count($data[HISTOGRAM_CREATED])), graphHeight());
$graph->addData($data[HISTOGRAM_CREATED], $data[HISTOGRAM_MODIFIED]);
$graph->setBars(true);
$graph->setBarColor(GRAPH_HIGHLIGHT_COLOR, GRAPH_DATA_COLOR);
$graph->setBarOutline(false);
$graph->setLegend(true);
$graph->setLegendTitle(HISTOGRAM_CREATED, HISTOGRAM_MODIFIED);
$graph->setLegendOutlineColor('white');
$graph->setLine(false);
$graph->setDataPoints(false);
$graph->setXValuesHorizontal(false);
$graph->setupXAxis(15);
$graph->setGrid(false);
$graph->createGraph();

?>