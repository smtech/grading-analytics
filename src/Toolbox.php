<?php
/** Toolbox class */

namespace smtech\GradingAnalytics;

use smtech\LTI\Configuration\Option;
use smtech\GradingAnalytics\Snapshots\Domain;
use smtech\GradingAnalytics\Snapshots\History;
use smtech\GradingAnalytics\Snapshots\Snapshot;

/**
 * A reflexive Canvas LTI toolbox
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 */
class Toolbox extends \smtech\StMarksReflexiveCanvasLTI\Toolbox
{
    const TOOL_CANVAS_EXTERNAL_TOOL_ID = 'TOOL_CANVAS_EXTERNAL_TOOL_ID';
    const TOOL_CANVAS_ACCOUNT_ID = 'TOOL_CANVAS_ACCOUNT_ID';

    /**
     * Course statistics history
     * @var History
     */
    protected $history;

    /**
     * Course statistics snapshots
     * @var Statistic[]
     */
    protected $snapshots;

    /**
     * Configure LTI configuration XML generator to include course and account
     * navigation placements.
     *
     * {@inheritDoc}
     *
     * @return \smtech\LTI\Configuration\Generator
     */
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

    /**
     * Extend load configuration to reset `TOOL_CANVAS_EXTERNAL_TOOL_ID`
     *
     * @param string $configFilePath
     * @param  boolean $forceRecache
     * @return void
     */
    protected function loadConfiguration($configFilePath, $forceRecache = false)
    {
        parent::loadConfiguration($configFilePath, $forceRecache);

        if ($forceRecache) {
            $this->clearConfig(self::TOOL_CANVAS_EXTERNAL_TOOL_ID);
            $this->clearConfig(self::TOOL_CANVAS_ACCOUNT_ID);
        }
    }

    /**
     * Get the course statistics history for a particular course
     *
     * @link https://smtech.github.io/grading-analytics/definitions.html Online
     *       documentation of course statistic fields
     *
     * @param string|integer $courseId Numerica Canvas course ID
     * @return array|false One row per course statistic collected daily, as
     *                         described in the documentation, or `FALSE` if no
     *                         statistics are available
     */
    public function getHistory($courseId)
    {
        if (!is_a($this->history, History::class)) {
            $this->history = new History($this, $courseId);
        }
        return $this->history->getHistory();
    }

    /**
     * Get a snapshot across a particular domain relevant to a course or
     * department
     *
     * @link https://smtech.github.io/grading-analytics/definitions.html Online
     *       documentation of course statistic fields
     *
     * @param string|integer $courseOrDepartmentId A numeric Canvas course or
     *        ccount ID (course is is assumed unless explicitly specified by
     *        `$isCourseId`)
     * @param  Domain $domain Domain across which to take snapshot
     * @param  boolean $isCourseId (Optional, defaults to `TRUE`) whether or
     *                             not `$courseOrDepartmentId` is a course
     *                             (`TRUE`) or department (`FALSE`) Id
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
    public function getSnapshot($courseOrDepartmentId, Domain $domain, $isCourseId = true, $teacherFilter = false)
    {
        $d = $domain->getValue();
        if (empty($this->snapshots[$d])) {
            $this->snapshots[$d] = new Snapshot($this, $domain, $courseOrDepartmentId, $isCourseId);
        }
        return $this->snapshots[$d]->getSnapshot($teacherFilter);
    }
}
