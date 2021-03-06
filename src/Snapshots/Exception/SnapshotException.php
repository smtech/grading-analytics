<?php
/** SnapshotException class */

namespace smtech\GradingAnalytics\Snapshots\Exceptions;

/**
 * Exceptions thrown within the Snapshot namespace
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 */
class SnapshotException extends \smtech\ReflexiveCanvasLTI\Exception\ConfigurationException
{
    const COURSE_ID = 100;
}
