<?php

namespace smtech\GradingAnalytics;

use smtech\LTI\Configuration\Option;
use smtech\GradingAnalytics\Snapshots\Domain;
use smtech\GradingAnalytics\Snapshots\History;
use smtech\GradingAnalytics\Snapshots\Snapshot;

class Toolbox extends \smtech\StMarksReflexiveCanvasLTI\Toolbox
{
    const TOOL_CANVAS_EXTERNAL_TOOL_ID = 'TOOL_CANVAS_EXTERNAL_TOOL_ID';

    protected $history;

    protected $snapshots;

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

    protected function loadConfiguration($configFilePath, $forceRecache = false)
    {
        parent::loadConfiguration($configFilePath, $forceRecache);

        if ($forceRecache) {
            $this->clearConfig(self::TOOL_CANVAS_EXTERNAL_TOOL_ID);
        }
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

    public function getHistory($courseId)
    {
        if (!is_a($this->history, History::class)) {
            $this->history = new History($this, $courseId);
        }
        return $this->history->getHistory();
    }

    public function getSnapshot($courseOrDepartmentId, Domain $domain, $isCourseId = true, $teacherFilter = false)
    {
        $d = $domain->getValue();
        if (empty($this->snapshots[$d])) {
            $this->snapshots[$d] = new Snapshot($this, $domain, $courseOrDepartmentId, $isCourseId);
        }
        return $this->snapshots[$d]->getSnapshot($teacherFilter);
    }
}
