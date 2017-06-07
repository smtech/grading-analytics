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
        $courseId = $this->getCourseId();
        if (empty(static::$data[$courseId])) {
            static::$data = $this->getCache()->getCache($courseId);
            if (empty($this->data)) {
                if ($response = $this->sql->query("
                    SELECT * FROM `course_statistics`
                        WHERE
                            `course[id]` = '$courseId'
                        ORDER BY
                            `timestamp` DESC
                ")) {
                    while ($row = $response->fetch_assoc()) {
                        static::$data[$courseId][] = $row;
                    }
                    $this->getCache()->setCache($courseId, static::$data[$courseId]);
                }
            }
        }
        return (is_array(static::$data) &&
            is_array(static::$data[$courseId]) &&
            count(static::$data[$courseId]) > 0
        );
    }

    public function getHistory()
    {
        if ($this->cacheHistory()) {
            return static::$data[$this->getCourseId()];
        }
        return false;
    }
}
