<?php
/** Snapshot class */

namespace smtech\GradingAnalytics\Snapshots;

/**
 * A snapshot of course statistics relevant to a particular domain
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 */
class Snapshot extends CacheableDatabase
{
    const NOT_ASSOCIATED_WITH_COURSE = '-';

    /**
     * History of the course specified by `$courseOrDepartmentId` in constructor
     * @var History
     */
    protected $history;

    /**
     * Department ID given as `$courseOrDepartmentId` in constructor
     * @var string|integer|false
     */
    protected $departmentId = false;

    /**
     * The domain of this snapshot
     * @var Domain
     */
    protected $domain;

    /**
     * Date timestamp of this snapshot
     * @var string|false
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

    /**
     * Temporary storage for teacher filters
     * @var string|integer|false
     */
    public static $teacherFilter;

    /**
     * Construct a Snapshot object
     *
     * Note that the snapshots are queried and/or cached from the database
     * "just in time", and not during instantiation.
     *
     * If the snapshot is constructed relative to a course ID, the domain will
     * being snapshotted will will be snapshotted at the time of that course's
     * most recent statistic collection (so, if it's June 20, and the last
     * statistic collected for the course was May 20, the department snapshot
     * will be for May 20, even if more recent snapshots are available within
     * the department).
     *
     * @param \mysqli|\smtech\ReflexiveCanvasLTI\Toolbox\CacheableDatabase
     *        $databaseProvider An object containing a mysqli database access
     *          object
     * @param Domain $domain The domain of the snapshot
     * @param string|integer $courseOrDepartmentId A numeric Canvas course or
     *        ccount ID (course is is assumed unless explicitly specified by
     *        `$isCourseId`)
     * @param boolean $isCourseId (Optional, defaults to `TRUE`) Whether
     *                            `$courseOrDepartmentId` is course ID (`TRUE`)
     *                             or an account ID (`FALSE`)
     */
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

    /**
     * Get the numeric Canvas course ID relevant to this snapshot
     *
     * There may be no course relevant to this snapshot if it was created from
     * a department ID.
     *
     * @return string|integer Returns `Snapshot::NOT_ASSOCIATED_WITH_COURSE` if
     *                                no course is associated with the snapshot.
     */
    public function getCourseId()
    {
        if (!empty($this->history)) {
            return $this->history->getCourseId();
        }
        return self::NOT_ASSOCIATED_WITH_COURSE;
    }

    /**
     * Get the numeric Canvas account ID relevant to this snapshot
     *
     * @return string|integer|false Will return `FALSE` if the snapshot is
     *                                   associated with a course for which
     *                                   there are not yet any statistics
     *                                   available.
     */
    public function getDepartmentId()
    {
        if ($this->departmentId === false) {
            $this->departmentId = $this->history->getDepartmentId();
        }
        return $this->departmentId;
    }

    /**
     * Get the domain of the snapshot
     *
     * @return Domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Get the date of the snapshot
     *
     * @see Snapshot::__construct() `Snapshot::__construct()` for a fuller
     *      discussion of timestamp selection
     *
     * @return string|false Returns the date in `YYYY-MM-DD` format or `FALSE`
     *                      if a timestamp could not be calculated (if no
     *                      statistics are available for the current
     *                      domain/course combination, for example)
     */
    public function getTimestamp()
    {
        if ($this->timestamp === false) {
            if (!empty($this->history)) {
                $this->timestamp = $this->history->getCurrentTimestamp();
            } else {
                if ($response = $this->getMySql()->query("
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

    /**
     * Get a particular average calculated across the snapshot
     *
     * @see Average `Average` for available averages
     *
     * @param  Average $average
     * @return float
     */
    public function getAverage(Average $average)
    {
        if ($this->cacheSnapshot()) {
            return $this->averages[$average->getValue()];
        }
        return false;
    }

    /**
     * Get the snapshotted course statistics data
     *
     * @link https://smtech.github.io/grading-analytics/definitions.html Online
     *       documentation of course statistic fields
     *
     * @param string|integer|boolean $teacherFilter (Optional, defaults to
     *                                              `FALSE`) An optional filter
     *                                              to limit the snapshot data
     *                                              to courses taught by a
     *                                              particular numerica Canvas
     *                                              user ID
     * @return array|false One row per course statistic collected daily, as
     *                         described in the documentation, or `FALSE` if no
     *                         statistics are available
     */
    public function getSnapshot($teacherFilter = false)
    {
        if ($this->domain == Domain::COURSE()) {
            $snapshot = $this->history->getHistory();
            if (is_array($snapshot) && count($snapshot) > 0) {
                return $snapshot[0];
            }
        } elseif ($this->cacheSnapshot()) {
            $d = $this->getDomain()->getValue();
            if ($teacherFilter) {
                self::$teacherFilter = $teacherFilter;
                return array_filter(
                    static::$data[$this->getCourseId()][$d][$this->getTimestamp()],
                    function ($elt) {
                        return array_search(
                            self::$teacherFilter,
                            unserialize($elt['teacher[id]s'])
                        ) !== false;
                    }
                );
            }
            return static::$data[$this->getCourseId()][$d][$this->getTimestamp()];
        }
        return false;
    }

    /**
     * Trigger a caching of snapshotted course statistics, if not already cached
     *
     * @return boolean `TRUE` if there is a non-empty cache of course
     *                        statistics to work with, `FALSE` otherwise
     */
    public function cacheSnapshot()
    {
        $domain = $this->getDomain();
        if ($domain == Domain::COURSE()) {
            return $this->history->cacheHistory();
        } else {
            $courseId = $this->getCourseId();
            $d = $domain->getValue();
            $timestamp = $this->getTimestamp();
            if (empty(static::$data[$courseId][$d][$timestamp])) {
                $this->getCache()->pushKey($courseId);
                $this->getCache()->pushKey(($domain == Domain::DEPARTMENT() ? $this->getDepartmentId() : 'school'));
                static::$data[$courseId][$d][$timestamp] = $this->getCache()->getCache($timestamp);
                $this->averages = $this->getCache()->getCache("$timestamp-averages");
                if (empty(static::$data[$courseId][$d][$timestamp])) {
                    if ($response = $this->getMySql()->query("
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
                            Average::TURN_AROUND => 0,
                            Average::ASSIGNMENT_COUNT => 0
                        ];
                        $divisor = [
                            Average::TURN_AROUND => 0,
                            Average::ASSIGNMENT_COUNT => $response->num_rows
                        ];

                        while ($row = $response->fetch_assoc()) {
                            static::$data[$courseId][$d][$timestamp][] = $row;

                            $total[Average::TURN_AROUND] +=
                                $row['average_grading_turn_around'] *
                                $row['student_count'] *
                                $row['graded_assignment_count'];
                            $divisor[Average::TURN_AROUND] +=
                                $row['student_count'] *
                                $row['graded_assignment_count'];

                            $total[Average::ASSIGNMENT_COUNT] +=
                                $row['assignments_due_count'] +
                                $row['dateless_assignment_count'];
                        }

                        foreach (Average::values() as $avg) {
                            $i = $avg->getValue();
                            $this->averages[$i] = ($divisor[$i] !== 0 ?
                                    $total[$i] / $divisor[$i] :
                                    0
                                );
                        }

                        $this->getCache()->setCache($timestamp, static::$data[$courseId][$d][$timestamp]);
                        $this->getCache()->setCache("$timestamp-averages", $this->averages);
                    }
                }
            }
            return is_array(static::$data) &&
                is_array(static::$data[$courseId]) &&
                is_array(static::$data[$courseId][$d]) &&
                is_array(static::$data[$courseId][$d][$timestamp]) &&
                count(static::$data[$courseId][$d][$timestamp]) > 0;
        }
    }
}
