<?php
/** HeatMap class */

namespace smtech\GradingAnalytics\HeatMap;

/**
 * Generate heatmap-style color-coding CSS
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 */
class HeatMap
{
    /**
     * Level specs to drive color-coding by fields
     *
     * @var array
     */
    private static $levels = [];

    /**
     * Generate CSS based on a value
     *
     * @link https://smtech.github.io/grading-analytics/definitions.html Online
     *       documentation of course statistic fields
     *
     * @param array $params An array of the format `['key' => 'field name',
     *                      'value' => 'field value']`, where the field is one
     *                      of those in course statistics
     * @param \Smarty $smarty The Smarty instance making the call
     * @return string
     */
    public static function getLevel($params, $smarty)
    {
        $key = false;
        $value = false;
        foreach ($params as $k => $v) {
            switch ($k) {
                case 'key':
                    $key = $v;
                    break;
                case 'value':
                    $value = $v;
                    break;
            }
        }
        if ($key === false || $value == false) {
            return "";
        }

        if (empty(self::$levels)) {
            self::$levels = [
                'average_grading_turn_around' => [
                    'warning' => [
                        new Level(3, 14, Comparison::GT()),
                        new Level(2, 7, Comparison::GT())
                    ],
                    'highlight' => [
                        new Level (3, 3, Comparison::LT()),
                        new Level (2, 7, Comparison::LT())
                    ]
                ],
                'average_assignment_lead_time' => [
                    'warning' => [
                        new Level(3, 1, Comparison::LT()),
                        new Level(2, 2, Comparison::LT())
                    ],
                    'highlight' => [
                        new Level(3, 10, Comparison::GT()),
                        new Level(2, 7, Comparison::GT())
                    ]
                ],
                'average_submissions_graded' => [
                    'warning' => [
                        new Level(3, 0.5, Comparison::LT()),
                        new Level(2, 0.75, Comparison::LT())
                    ],
                    'highlight' => [
                        new Level(3, 1.0, Comparison::GTE()),
                        new Level(2, 0.9, Comparison::GT())
                    ]
                ],
                'dateless_assignment_count' => [
                    'warning' => [
                        new Level(3, 20, Comparison::GT()),
                        new Level(2, 10, Comparison::GT())
                    ],
                    'highlight' => [
                        new Level(3, 1, Comparison::LT()),
                        new Level(2, 5, Comparison::LT())
                    ]
                ],
                'gradeable_assignment_count' => [
                    'warning' => [
                        new Level(3, 0, Comparison::LTE())
                    ]
                ],
                'graded_assignment_count' => [
                    'warning' => [
                        new Level(3, 0, Comparison::LTE())
                    ]
                ],
                'created_after_due_count' => [
                    'warning' => [
                        new Level(3, 10, Comparison::GT()),
                        new Level(2, 5, Comparison::GT())
                    ],
                    'highlight' => [
                        new Level(3, 1, Comparison::LT()),
                        new Level(2, 5, Comparison::LT())
                    ]
                ],
                'zero_point_assignment_count' => [
                    'warning' => [
                        new Level(3, 10, Comparison::GTE()),
                        new Level(2, 0, Comparison::GT())
                    ]
                ]
            ];
        }

        foreach (self::$levels[$key] as $mode => $modeLevels) {
            foreach ($modeLevels as $level) {
                $match = false;
                switch ($level->comparison->getValue()) {
                    case Comparison::GT:
                        $match = $value > $level->value;
                        break;
                    case Comparison::GTE:
                        $match = $value >= $level->value;
                        break;
                    case Comparison::LT:
                        $match = $value < $level->value;
                        break;
                    case Comparison::LTE:
                        $match = $value <= $level->value;
                        break;
                }
                if ($match) {
                    return " class=\"$mode level-{$level->level}\"";
                }
            }
        }
        return "";
    }
}
