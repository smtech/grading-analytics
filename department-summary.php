<?php

require_once('.ignore.grading-analytics-authentication.inc.php');
define('TOOL_START_PAGE', 'https://' . parse_url(CANVAS_API_URL, PHP_URL_HOST) . "/accounts/{$_REQUEST['department_id']}");

require_once(__DIR__ . '/smcanvaslib/config.inc.php');
require_once(__DIR__ . '/config.inc.php');
require_once(__DIR__ . '/common.inc.php');
require_once(SMCANVASLIB_PATH . '/include/canvas-api.inc.php');
require_once(SMCANVASLIB_PATH . '/include/mysql.inc.php');
require_once(SMCANVASLIB_PATH . '/include/page-generator.inc.php');

$departments = callCanvasApi(
	CANVAS_API_GET,
	"/accounts/{$_REQUEST['department_id']}"
);

displayPage("<h1>{$departments['name']} Summary Page</h1><p>Patience&hellip;</p>")

?>