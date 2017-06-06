<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/constants.inc.php';

use smtech\GradingAnalytics\Toolbox;
use smtech\ReflexiveCanvasLTI\LTI\ToolProvider;
use Battis\DataUtilities;

@session_start(); // TODO I don't feel good about suppressing warnings

/* prepare the toolbox */
if (empty($_SESSION[Toolbox::class])) {
    $_SESSION[Toolbox::class] =& Toolbox::fromConfiguration(CONFIG_FILE);
}
$toolbox =& $_SESSION[Toolbox::class];
$toolbox->smarty_assign([
    'category' => DataUtilities::titleCase(preg_replace('/[\-_]+/', ' ', basename(__DIR__)))
]);

/* set the Tool Consumer's instance URL, if present */
if (empty($_SESSION[CANVAS_INSTANCE_URL]) &&
    !empty($_SESSION[ToolProvider::class]['canvas']['api_domain'])
) {
    $_SESSION[CANVAS_INSTANCE_URL] = 'https://' . $_SESSION[ToolProvider::class]['canvas']['api_domain'];
}

if (!empty($_SESSION[CANVAS_INSTANCE_URL])) {
    $toolbox->smarty_assign('canvasInstanceUrl', $_SESSION[CANVAS_INSTANCE_URL]);
}