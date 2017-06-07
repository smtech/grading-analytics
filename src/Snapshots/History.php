<?php

namespace smtech\GradingAnalytics\Snapshots;

use smtech\GradingAnalytics\Snapshots\Exception\SnapshotException;

class History extends CacheableDatabase
{
    protected $courseId;

    protected static $data;

    public function __construct($databaseProvider, $courseId)
    {
        parent::__construct($databaseProvider);

        if (is_numeric($courseId)) {
            $this->courseId = $courseId;
        } else {
            throw new SnapshotException(
                'Numeric Course ID required',
                SnapshotException::COURSE_ID
            );
        }
    }

    public function getCourseId()
    {
        return $this->courseId;
    }

    public function getDepartmentId()
    {
        if ($this->cacheHistory()) {
            return (integer) static::$data[$this->getCourseID()][0]['course[account_id]'];
        }
        return false;
    }

    public function getCurrentTimestamp()
    {
        if ($this->cacheHistory()) {
            return substr(static::$data[$this->getCourseId()][0]['timestamp'], 0, 10);
        }
        return false;
    }

    public function cacheHistory()
    {
        if (empty(static::$data[$this->getCourseId()])) {
            static::$data = $this->getCache()->getCache($this->getCourseId());
            if (empty($this->data)) {
                if ($response = $this->sql->query("
                    SELECT * FROM `course_statistics`
                        WHERE
                            `course[id]` = '" . $this->getCourseId() . "'
                        ORDER BY
                            `timestamp` DESC
                ")) {
                    while ($row = $response->fetch_assoc()) {
                        static::$data[$this->getCourseId()][] = $row;
                    }
                    $this->getCache()->setCache($this->getCourseId(), static::$data[$This->getCourseId()]);
                }
            }
        }
        return (is_array(static::$data) &&
            is_array(static::$data[$this->getCourseId()]) &&
            count(static::$data[$this->getCourseId()]) > 0
        );
    }

    public function getHistory()
    {
        if ($this->cacheHistory()) {
            return $this->data;
        }
        return false;
    }
}
