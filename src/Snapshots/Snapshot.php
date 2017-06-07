<?php

namespace smtech\GradingAnalytics\Snapshots;

use smtech\GradingAnalytics\Snapshots\Exception\SnapshotException;

class Snapshot extends CacheableDatabase
{
    const NOT_ASSOCIATED_WITH_COURSE = 'n/a';

    /**
     * History of the course specified by `$courseOrDepartmentId` in constructor
     * @var History
     */
    protected $history;

    /**
     * Department ID given as `$courseOrDepartmentId` in constructor
     * @var [type]
     */
    protected $departmentId = false;

    /**
     * The domain of this snapshot
     * @var Domain
     */
    protected $domain;

    /**
     * Date timestamp of this snapshot
     * @var string
     */
    protected $timestamp = false;

    /**
     * Course statistics records that make up the snapshot
     * @var array
     */
    protected static $data;

    /**
     * Averages calculated from the data
     * @var array
     */
    protected $averages;

    public function __construct($databaseProvider, Domain $domain, $courseOrDepartmentId, $isCourseId = true)
    {
        parent::__construct($databaseProvider);

        $this->domain = $domain;

        if ($isCourseId) {
            $this->history = new History($this, $courseOrDepartmentId);
        } elseif (is_numeric($courseOrDepartmentId)) {
            $this->departmentId = $courseOrDepartmentId;
        }
    }

    public function getCourseId()
    {
        if (!empty($this->history)) {
            return $this->history->getCourseId();
        }
        return self::NOT_ASSOCIATED_WITH_COURSE;
    }

    public function getDepartmentId()
    {
        if ($this->departmentId === false) {
            $this->departmentId = $this->history->getDepartmentId();
        }
        return $this->departmentId;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getTimestamp()
    {
        if ($this->timestamp === false) {
            if (!empty($this->history)) {
                $this->timestamp = $this->history->getCurrentTimestamp();
            } else {
                if ($response = $this->sql->query("
                    SELECT * FROM `course_statistics`
                        WHERE
                            `course[account_id]` = '" . $this->getDepartmentId() . "'
                        ORDER BY
                            `timestamp` DESC
                        LIMIT 1
                ")) {
                    if ($row = $response->fetch_assoc()) {
                        $this->timestamp = substr($row['timestamp'], 0, 10);
                    }
                }
            }
        }
        return $this->timestamp;
    }

    public function getAverage(Average $average)
    {
        if ($this->cacheSnapshot()) {
            return $this->averages[$average];
        }
        return false;
    }
    public function getSnapshot($teacherFilter = false)
    {
        if ($this->domain == Domain::COURSE()) {
            $snapshot = $this->history->getHistory();
            if (is_array($snapshot) && count($snapshot) > 0) {
                return $snapshot[0];
            }
        } elseif ($this->cacheSnapshot()) {
            if ($teacherFilter) {
                return array_filter(
                    static::$data[$this->getCourseId()][$this->getDomain()][$this->getTimestamp()],
                    function ($elt) {
                        return array_search(
                            $teacherFilter,
                            unserialize($elt['teacher[id]s'])
                        ) !== false;
                    }
                );
            }
            return static::$data[$this->getCourseId()][$this->getDomain()][$this->getTimestamp()];
        }
        return false;
    }

    /*
     * need to cache snapshots per timestamp, since different courses
     * may have different most recent records that require comparison
     * to different snapshots (e.g. a course ending June 1 should be
     * compared to June 1 snapshots, but a course ending January 20
     * should be compared to January 20 snapshots)
     */
    public function cacheSnapshot()
    {
        $domain = $this->getDomain();
        if ($domain == Domain::Course()) {
            return $this->history->cacheHistory();
        } else {
            $courseId = $this->getCourseId();
            $timestamp = $this->getTimestamp();
            if (empty(static::$data[$courseId][$domain][$timestamp])) {
                $cache->pushKey(($domain == Domain::DEPARTMENT() ? $this->getDepartmentId() : 'school'));
                static::$data[$courseId][$domain][$timestamp] = $cache->getCache($timestamp);
                $this->averages = $cache->getCache("$timestamp-averages");
                if (empty(static::$data[$courseId][$domain][$timestamp])) {
                    if ($response = $this->mysql_query("
                        SELECT * FROM `course_statistics`
                            WHERE
                                " . ($domain == Domain::DEPARTMENT() ?
                                        "`course[account_id]` = '" . $this->getDepartmentId() . "' AND" :
                                        ''
                                    ) . "
                                `timestamp` LIKE '$timestamp%'
                            GROUP BY
                                `course[id]`
                            ORDER BY
                                `timestamp` DESC
                    ")) {
                        $total = [
                            self::AVERAGE_TURN_AROUND => 0,
                            self::AVERAGE_ASSIGNMENT_COUNT => 0
                        ];
                        $divisor = [
                            self::AVERAGE_TURN_AROUND => 0,
                            self::AVERAGE_ASSIGNMENT_COUNT => $response->num_rows
                        ];

                        while ($row = $response->fetch_assoc()) {
                            static::$data[$courseId][$domain][$timestamp][] = $row;

                            $total[self::AVERAGE_TURN_AROUND] +=
                                $row['average_grading_turn_around'] *
                                $row['student_count'] *
                                $row['graded_assignment_count'];
                            $divisor[self::AVERAGE_TURN_AROUND] +=
                                $row['student_count'] *
                                $row['graded_assignment_count'];

                            $total[self::AVERAGE_ASSIGNMENT_COUNT] +=
                                $row['assignments_due_count'] +
                                $row['dateless_assignment_count'];
                        }

                        for ($i = 0; $i < count($total); $i++) {
                            $this->averages[$i] = $total[$i] / $divisor[$i];
                        }

                        $cache->setCache($timestamp, static::$data[$courseId][$domain][$timestamp]);
                        $cache->setCache("$timestamp-averages", $this->averages);
                    }
                }
            }
            return is_array(static::$data) &&
                is_array(static::$data[$courseId]) &&
                is_array(static::$data[$courseId][$domain]) &&
                is_array(static::$data[$courseId][$domain][$timestamp]) &&
                count(static::$data[$courseId][$domain][$timestamp]) > 0;
        }
    }
}
