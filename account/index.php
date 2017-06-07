<?php

require_once 'common.inc.php';

use Battis\DataUtilities;
use smtech\StMarksSmarty\StMarksSmarty;
use smtech\ReflexiveCanvasLTI\LTI\ToolProvider;
use smtech\GradingAnalytics\Toolbox;
use smtech\GradingAnalytics\HeatMap\HeatMap;
use smtech\GradingAnalytics\Snapshots\Domain;

/*
 * FIXME this should not be hard-coded -- roles should be configurable.
 */
$unrestrictedRoles = [
    'AccountAdmin',
    'Dean',
    'Department Chair'
];

$toolbox->getSmarty()->enable(StMarksSmarty::MODULE_SORTABLE);

$account_id = $_SESSION[ACCOUNT_ID];

$departments = $toolbox->api_get('/accounts/' . $_SESSION[ACCOUNT_ID]);

/* verify user privileges */
$user_id = $_SESSION[ToolProvider::class]['canvas']['user_id'];
$restricted = true;
$permissionsAccount = $departments;
do {
    $roles = $toolbox->api_get(
        "/accounts/{$permissionsAccount['id']}/admins",
        [
            'user_id[]' => $user_id
        ]
    );
    foreach ($roles as $role) {
        if (in_array($role['role'], $unrestrictedRoles)) {
            $restricted = false;
            break;
        }
    }
    if ($restricted && !empty($permissionsAccount['parent_account_id'])) {
        $permissionsAccount = $toolbox->api_get("/accounts/{$permissionsAccount['parent_account_id']}");
    } else {
        $permissionsAccount = false;
    }
} while ($restricted && $permissionsAccount !== false);

/* figure out what the ID of this LTI is in Canvas */
/*
 * FIXME this works if you have only one placement per tool install, but will
 * fail if the same install is placed in multiple contexts. The "right" thing
 * to do would be to cache the Canvas tool ID per context.
 */
$toolId = $toolbox->config('TOOL_CANVAS_EXTERNAL_TOOL_ID');
if (empty($toolId)) {
    foreach ($toolbox->api_get('accounts/' . $_SESSION[ACCOUNT_ID] . '/external_tools', ['include_parents' => true]) as $tool) {
        if ($tool['url'] == $toolbox->config(Toolbox::TOOL_LAUNCH_URL)) {
            $toolId = $tool['id'];
            break;
        }
    }
    $toolbox->config('TOOL_CANVAS_EXTERNAL_TOOL_ID', $toolId);
}

$toolbox->getSmarty()->addStylesheet(DataUtilities::URLfromPath(__DIR__ .  '/css/index.css'));
$toolbox->getSmarty()->registerPlugin(
    'function',
    'getLevel',
    [
        HeatMap::class,
        'getLevel'
    ]
);
$toolbox->smarty_assign([
    'statistics' => $toolbox->getSnapshot($_SESSION[ACCOUNT_ID], Domain::DEPARTMENT(), false, ($restricted ? $user_id : false)),
    'departments' => $departments
]);
$toolbox->smarty_display('department-summary.tpl');
