<?php

namespace smtech\GradingAnalytics;

use smtech\LTI\Configuration\Option;
use Battis\HierarchicalSimpleCache;

class Toolbox extends \smtech\StMarksReflexiveCanvasLTI\Toolbox
{
    protected $courseHistory = [];

    const DEPT = 0;
    const SCHOOL = 1;

    protected $snapshots = [[], []];

    const AVERAGE_TURN_AROUND = 0;
    const AVERAGE_ASSIGNMENT_COUNT = 1;

    protected $numbers = [[], []];

    public function getGenerator()
    {
        parent::getGenerator();

        $this->generator->setOptionProperty(
            Option::COURSE_NAVIGATION(),
            'visibility',
            'admins'
        );
        $this->generator->setOptionProperty(
            Option::ACCOUNT_NAVIGATION(),
            'visibility',
            'admins'
        );

        return $this->generator;
    }

    private $GRAPH_DATA_COUNT = 0;
    public function graphWidth($dataCount = false)
    {
        if ($dataCount) {
            $this->GRAPH_DATA_COUNT = $dataCount;
        }
        return max(GRAPH_MIN_WIDTH, $this->GRAPH_DATA_COUNT * GRAPH_BAR_WIDTH);
    }

    public function graphHeight($dataCount = false)
    {
        if ($dataCount) {
            $this->GRAPH_DATA_COUNT = $dataCount;
        }
        return $this->graphWidth() * GRAPH_ASPECT_RATIO;
    }

    public function getMostCurrentCourseTimestamp($courseId)
    {
        return substr($this->getCourseHistory($courseId)[0]['timestamp'], 0, 10);
    }

    public function getDepartmentId($courseId)
    {
        return $this->getCourseHistory($courseId)[0]['course[account_id]'];
    }

    public function getCourseHistory($courseId)
    {
        if (empty($this->courseHistory)) {
            $cache = new HierarchicalSimpleCache($this->getMySql(), $this->config(self::TOOL_ID));
            $cache->pushKey('course');
            $this->courseHistory = $cache->getCache($courseId);
            if (empty($this->courseHistory)) {
                if ($response = $this->mysql_query("
                    SELECT * FROM `course_statistics`
                        WHERE
                            `course[id]` = '$courseId'
                        ORDER BY
                            `timestamp` DESC
                ")) {
                    while ($row = $response->fetch_assoc()) {
                        $this->courseHistory[] = $row;
                    }
                    $cache->setCache($courseId, $this->courseHistory);
                }
            }
        }
        return $this->courseHistory;
    }

    public function getCourseSnapshot($courseId)
    {
        $history = $this->getCourseHistory($courseId);
        if (!empty($history)) {
            return $history[0];
        }
        return false;
    }

    public function getDepartmentSnapshot($courseId)
    {
        return $this->getSnapshot($courseId, self::DEPT);
    }

    public function getSchoolSnapshot($courseId)
    {
        return $this->getSnapshot($courseId, self::SCHOOL);
    }

    public function getSnapshot($courseId, $domain = self::DEPT)
    {
        if (empty($this->snapshots[$domain])) {
            $cache = new HierarchicalSimpleCache($this->getMySql(), $this->config(self::TOOL_ID));
            $cache->pushKey('snapshot');
            if ($domain === self::DEPT) {
                $cache->pushKey('account');
            }
            $key = ($domain === self::DEPT ? $this->getDepartmentId($courseId) : 'school');
            $this->snapshots[$domain] = $cache->getCache($key);
            $this->numbers[$domain] = $cache->getCache("$key-numbers");
            if (empty($this->snapshots[$domain])) {
                if ($response = $this->mysql_query("
                    SELECT * FROM `course_statistics`
                        WHERE
                            " . ($domain === self::DEPT ? "`course[account_id]` = '" . $this->getDepartmentId($courseId) . "' AND" : '') . "
                            `timestamp` LIKE '" . $this->getMostCurrentCourseTimestamp($courseId) . "%'
                        GROUP BY
                            `course[id]`
                        ORDER BY
                            `timestamp` DESC
                ")) {
                    $totalTurnAround = 0;
                    $divisorTurnAround = 0;
                    $totalAssignmentCount = 0;

                    while ($row = $response->fetch_assoc()) {
                        $this->snapshots[$domain][] = $row;

                        /* average turn-around */
                        $totalTurnAround += $row['average_grading_turn_around'] * $row['student_count'] * $row['graded_assignment_count'];
                        $divisorTurnAround += $row['student_count'] * $row['graded_assignment_count'];

                        /* average assignment count */
                        $totalAssignmentCount += $row['assignments_due_count'] + $row['dateless_assignment_count'];
                    }
                    $this->numbers[$domain][self::AVERAGE_TURN_AROUND] = $totalTurnAround / $divisorTurnAround;
                    $this->numbers[$domain][self::AVERAGE_ASSIGNMENT_COUNT] = $totalAssignmentCount / $response->num_rows;

                    $cache->setCache($key, $this->snapshots[$domain]);
                    $cache->setCache("$key-numbers", $this->numbers[$domain]);
                }
            }
        }
        return $this->snapshots[$domain];
    }

    public function averageTurnAround($courseId, $domain = self::DEPT)
    {
        $this->getSnapshot($courseId, $domain);
        if (!empty($this->numbers[$domain][self::AVERAGE_TURN_AROUND])) {
            return $this->numbers[$domain][self::AVERAGE_TURN_AROUND];
        }
        return false;
    }

    public function averageAssignmentCount($courseId, $domain = self::DEPT)
    {
        $this->getSnapshot($courseId, $domain);
        if (!empty($this->numbers[$domain][self::AVERAGE_ASSIGNMENT_COUNT])) {
            return $this->numbers[$domain][self::AVERAGE_ASSIGNMENT_COUNT];
        }
        return false;
    }
}
