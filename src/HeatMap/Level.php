<?php

namespace smtech\GradingAnalytics\HeatMap;

class Level
{
    public static $GREATER_THAN = 0;
    public static $GREATER_THAN_OR_EQUAL = 1;
    public static $LESS_THAN_OR_EQUAL = 2;
    public static $LESS_THAN = 3;

    public $level;
    public $value;
    public $comparison;

    public function __construct($level, $value, $comparison)
    {
        $this->level = $level;
        $this->value = $value;
        $this->comparison = $comparison;
    }
}
