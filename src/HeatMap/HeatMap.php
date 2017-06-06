<?php

namespace smtech\GradingAnalytics\HeatMap;

class HeatMap
{
    private static $levels = [];

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
                        new Level(3, 14, Level::$GREATER_THAN),
                        new Level(2, 7, Level::$GREATER_THAN)
                    ],
                    'highlight' => [
                        new Level (3, 3, Level::$LESS_THAN),
                        new Level (2, 7, Level::$LESS_THAN)
                    ]
                ],
                'average_assignment_lead_time' => [
                    'warning' => [
                        new Level(3, 1, Level::$LESS_THAN),
                        new Level(2, 2, Level::$LESS_THAN)
                    ],
                    'highlight' => [
                        new Level(3, 10, Level::$GREATER_THAN),
                        new Level(2, 7, Level::$GREATER_THAN)
                    ]
                ],
                'average_submissions_graded' => [
                    'warning' => [
                        new Level(3, 0.5, Level::$LESS_THAN),
                        new Level(2, 0.75, Level::$LESS_THAN)
                    ],
                    'highlight' => [
                        new Level(3, 1.0, Level::$GREATER_THAN_OR_EQUAL),
                        new Level(2, 0.9, Level::$GREATER_THAN)
                    ]
                ],
                'dateless_assignment_count' => [
                    'warning' => [
                        new Level(3, 20, Level::$GREATER_THAN),
                        new Level(2, 10, Level::$GREATER_THAN)
                    ],
                    'highlight' => [
                        new Level(3, 1, Level::$LESS_THAN),
                        new Level(2, 5, Level::$LESS_THAN)
                    ]
                ],
                'gradeable_assignment_count' => [
                    'warning' => [
                        new Level(3, 0, Level::$LESS_THAN_OR_EQUAL)
                    ]
                ],
                'graded_assignment_count' => [
                    'warning' => [
                        new Level(3, 0, Level::$LESS_THAN_OR_EQUAL)
                    ]
                ],
                'created_after_due_count' => [
                    'warning' => [
                        new Level(3, 10, Level::$GREATER_THAN),
                        new Level(2, 5, Level::$GREATER_THAN)
                    ],
                    'highlight' => [
                        new Level(3, 1, Level::$LESS_THAN),
                        new Level(2, 5, Level::$LESS_THAN)
                    ]
                ],
                'zero_point_assignment_count' => [
                    'warning' => [
                        new Level(3, 10, Level::$GREATER_THAN_OR_EQUAL),
                        new Level(2, 0, Level::$GREATER_THAN)
                    ]
                ]
            ];
        }

        foreach (self::$levels[$key] as $mode => $modeLevels) {
            foreach ($modeLevels as $level) {
                $match = false;
                switch ($level->comparison) {
                    case Level::$GREATER_THAN:
                        $match = $value > $level->value;
                        break;
                    case Level::$GREATER_THAN_OR_EQUAL:
                        $match = $value >= $level->value;
                        break;
                    case Level::$LESS_THAN:
                        $match = $value < $level->value;
                        break;
                    case Level::$LESS_THAN_OR_EQUAL:
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
