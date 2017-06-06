<?php

namespace smtech\GradingAnalytics;

use smtech\LTI\Configuration\Option;

class Toolbox extends \smtech\StMarksReflexiveCanvasLTI\Toolbox
{
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

    public function averageTurnAround($departmentId = false)
    {
        $stats = $this->mysql_query("
            SELECT * FROM `course_statistics`
                WHERE
                    `average_grading_turn_around` > 0 " .
                    (
                        $departmentId ?
                            "AND `course[account_id]` = '$departmentId' " :
                            ''
                    ) . "
                GROUP BY
                    `course[id]`
                ORDER BY
                    `timestamp` DESC
        ");

        $total = 0;
        $divisor = 0;
        while ($row = $stats->fetch_assoc()) {
            $total += $row['average_grading_turn_around'] * $row['student_count'] * $row['graded_assignment_count'];
            $divisor += $row['student_count'] * $row['graded_assignment_count'];
        }
        return $total / $divisor;
    }

    public function averageAssignmentCount($departmentId = false)
    {
        $stats = $this->mysql_query("
            SELECT * FROM (
                SELECT * FROM `course_statistics`" .
                    (
                        $departmentId ? "
                            WHERE
                                `course[account_id]` = '$departmentId'" :
                            ''
                    ) . "
                    ORDER BY
                        `timestamp` DESC
            ) AS `stats`
                GROUP BY
                    `course[id]`
        ");

        $total = 0;
        while ($row = $stats->fetch_assoc()) {
            $total += $row['assignments_due_count'] + $row['dateless_assignment_count'];
        }
        return $total / $stats->num_rows;
    }
}