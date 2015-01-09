<?php

require_once(__DIR__ . '/.ignore.calendar-ics-authentication.inc.php');
require_once(__DIR__ . '/config.inc.php');

require_once(APP_PATH . '/include/page-generator.inc.php');
require_once(APP_PATH . '/include/canvas-api.inc.php');
require_once(APP_PATH . '/include/mysql.inc.php');

$eventsApi = new CanvasApiProcess(CANVAS_API_URL, CANVAS_API_TOKEN);
$api = new CanvasApiProcess(CANVAS_API_URL, CANVAS_API_TOKEN);

// TODO work nicely with the cache (purge uncached events, or only cached events, etc.)

$events = $eventsApi->get('calendar_events',
	array(
		'type' => 'event',
		'all_events' => true,
		'context_codes[]' => preg_replace('|.*/courses/(\d+)/?.*|', "course_$1", $_REQUEST['course_url'])
	)
);
do {
	foreach($events as $event) {
		$api->delete("calendar_events/{$event['id']}",
			array(
				'cancel_reason' => TOOL_NAME . " course_url={$_REQUEST['course_url']}"
			)
		);
	}
} while($events = $eventsApi->nextPage());

echo('Calendar purged.');

?>