<?php
/** Level class */

namespace smtech\GradingAnalytics\HeatMap;

/**
 * Comparable levels of something
 *
 * Used for generating heatmap-style color-coding
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 */
class Level
{
    /**
     * Level of color-coding (less is better)
     * @var integer
     */
    public $level;

    /**
     * Cut-off value for level
     * @var integer
     */
    public $value;

    /**
     * Comparison direction to determine inclusion in level
     * @var Comparison
     */
    public $comparison;

    /**
     * Construct a Level object
     *
     * @param integer $level Color-coding level
     * @param integer $value Cut-off value
     * @param Comparison $comparison Comparison direction
     */
    public function __construct($level, $value, Comparison $comparison)
    {
        $this->level = $level;
        $this->value = $value;
        $this->comparison = $comparison;
    }
}
