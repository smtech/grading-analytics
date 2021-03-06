<?php
/** Average enumerated type */

namespace smtech\GradingAnalytics\Snapshots;

use MyCLabs\Enum\Enum;

/**
 * An enumeration of different types of averages that are calculated for
 * snapshots.
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 */
class Average extends Enum
{
    const TURN_AROUND = 0;
    const ASSIGNMENT_COUNT = 1;
}
