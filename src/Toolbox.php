<?php

namespace smtech\GradingAnalytics;

use smtech\LTI\Configuration\Option;

class Toolbox extends \smtech\StMarksReflexiveCanvasLTI\Toolbox
{
    protected $courseHistory = [];

    const DEPT = 0;
    const SCHOOL = 1;

    protected $snapshots = [[], []];

    const AVERAGE_TURN_AROUND = 0;
    const AVERAGE_ASSIGNMENT_COUNT = 1;

    protected $numbers = [];

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
        return $this->getCourseHistory($courseId)[0]['timestamp'];
    }

    public function getDepartmentId($courseId)
    {
        return substr($this->getCourseHistory($courseId)[0]['course[account_id]'], 0, 10);
    }

    public function getCourseHistory($courseId)
    {
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
            if ($response = $this->mysql_query("
                SELECT * FROM `course_statistics`
                    WHERE
                        " . ($domain === self::DEPT ? "`course[account_id]` = '" . $this->getDepartmentId($courseId) . "' AND" : '') . "
                        `timestamp` LIKE '" . $this->getMostCurrentCourseTimestamp($courseId) . "%'
                    GROUP BY
                        `course_id`
                    ORDER BY
                        `timestamp` DESC
            ")) {
                while ($row = $response->fetch_assoc()) {
                    $this->schoolSnapshot[] = $row;

                    /* average turn-around */
                    $totalTurnAround += $row['average_grading_turn_around'] * $row['student_count'] * $row['graded_assignment_count'];
                    $divisorTurnAroud += $row['student_count'] * $row['graded_assignment_count'];

                    /* average assignment count */
                    $totalAssignmentCount += $row['assignments_due_count'] + $row['dateless_assignment_count'];
                }
                $this->numbers[self::DEPT][self::AVERAGE_TURN_AROUND] = $totalTurnAround / $divisorTurnAround;
                $this->numbers[self::DEPT][self::AVERAGE_ASSIGNMENT_COUNT] = $total / $response->num_rows;
            }
        }
        return $this->schoolSnapshot;
    }

    public function averageTurnAround($courseId, $domain = self::DEPT)
    {
        $this->getSnapshot($courseId, $domain);
        return $this->numbers[$domain][self::AVERAGE_TURN_AROUND];
    }

    public function averageAssignmentCount($courseId, $domain = self::DEPT)
    {
        $this->getSnapshot($courseId, $domain);
        return $this->numbers[$domain][self::AVERAGE_ASSIGNMENT_COUNT];
    }
}
