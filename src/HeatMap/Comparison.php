<?php
/** Comparison enumerated type */

namespace smtech\GradingAnalytics\HeatMap;

use MyCLabs\Enum\Enum;

/**
 * An enumerated list of types of comparison
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 */
class Comparison extends Enum
{
    /**
     * Greater than
     * @var integer
     */
    const GT = 0;

    /**
     * Greater than or equal
     * @var integer
     */
    const GTE = 1;

    /**
     * Less than or equal
     * @var integer
     */
    const LTE = 2;

    /**
     * Less than
     * @var integer
     */
    const LT = 3;
}
