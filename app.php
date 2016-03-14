<?php

require_once('common.inc.php');

/* replace the contents of this file with your own app logic */

$api = new CanvasPest($_SESSION['apiUrl'], $_SESSION['apiToken']);

include_once 'course-summary.php';

?>