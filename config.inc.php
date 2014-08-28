<?php

/* this will break if there are mod_rewrites, but will do for now... */
define('APP_URL', 'http://' . $_SERVER['SERVER_NAME'] . str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath(__DIR__)));
define('APP_PATH', realpath(__DIR__));

/* customize generated pages */
define('SCHOOL_NAME', 'St. Mark&rsquo;s School');
define('SCHOOL_URL', 'http://www.stmarksschool.org');
define('SCHOOL_CANVAS_INSTANCE', 'https://stmarksschool.instructure.com');
define('SCHOOL_DEPT', 'Academic Technology');
define('SCHOOL_DEPT_URL', 'http://area51.stmarksschool.org');
define('SCHOOL_ADDRESS', '25 Marlboro Road, Southborough, Massachusetts, 01772');
define('SCHOOL_COLOR_LIGHT', 'white'); // masthead foreground
define('SCHOOL_COLOR_DARK', '#003359'); // masthead background and link colors

define('TOOL_NAME', 'Canvas Calendar &harr; ICS Tool');
define('TOOL_START_PAGE', dirname($_SERVER['PHP_SELF']));

define('LOCAL_TIMEZONE', 'US/Eastern'); // TODO: Can we detect the timezone for the Canvas instance and use it?
define('SEPARATOR', '_'); // used when concatenating information in the cache database
define('SYNC_TIMESTAMP_FORMAT', 'Y-m-d\TH:iP'); // same as CANVAS_TIMESTAMP_FORMAT, FWIW

define('WORKING_DIR', '/var/www-data/canvas/ics-sync/');

define('API_CLIENT_ERROR_RETRIES', 1);
define('API_SERVER_ERROR_RETRIES', 3);

?>