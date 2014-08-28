<?php

require_once(__DIR__ . '/.ignore.calendar-ics-authentication.inc.php');
require_once(__DIR__ . '/config.inc.php');

require_once(APP_PATH . '/include/canvas-api.inc.php');
require_once(APP_PATH . '/include/mysql.inc.php');

require_once(__DIR__ . '/common.inc.php');

// FIXME: should filter so that the syncs for the server we're running against (INDEX_WEB_PATH) are called (or is that already happening?)
$schedulesResponse = mysqlQuery("
	SELECT *
		FROM `schedules`
		WHERE
			`schedule` = '" . mysqlEscapeString($argv[INDEX_SCHEDULE]) . "'
		ORDER BY
			`synced` ASC
");

while($schedule = $schedulesResponse->fetch_assoc()) {
	$calendarResponse = mysqlQuery("
		SELECT *
			FROM `calendars`
			WHERE
				`id` = '{$schedule['calendar']}'
	");
	if ($calendar = $calendarResponse->fetch_assoc()) {
		shell_exec('curl -u ' . MYSQL_USER . ':' . MYSQL_PASSWORD . ' -k "https://skunkworks.stmarksschool.org/canvas/ics-sync/import.php?cal=' . urlencode($calendar['ics_url']) . '&canvas_url=' . urlencode($calendar['canvas_url']) . '&schedule=' . urlencode($schedule['id']) . '"');
	}
}

?>