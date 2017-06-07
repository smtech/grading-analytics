<?php

namespace smtech\GradingAnalytics\Snapshots;

use mysqli;
use smtech\ReflexiveCanvasLTI\Toolbox;
use smtech\GradingAnalytics\Snapshots\Exception\SnapshotException;
use Battis\HierarchicalSimpleCache;

class CacheableDatabase
{
    /**
     * MySQL database access
     * @var mysqli
     */
    protected $sql;

    /**
     * Caching for queried data
     * @var HierarchicalSimpleCache
     */
    protected $cache;

    public function __construct($databaseProvider)
    {
        $this->sql = false;
        if (is_a($databaseProvider, mysqli::class)) {
            $this->sql = $databaseProvider;
        } elseif (is_a($databaseProvider, CacheableDatabase::class)) {
            $this->sql = $databaseProvider->sql;
        } elseif (is_a($databaseProvider, Toolbox::class)) {
            $this->sql = $databaseProvider->getMySql();
        }

        if (!is_a($this->sql, mysqli::class)) {
            throw new SnapshotException(
                'Database provider not recognized (instance of ' . (is_object($databaseProvider) ?
                        get_class($databaseProvider) :
                        gettype($databaseProvider)
                    ) .')',
                SnapshotException::MYSQL
            );
        }
    }

    protected function getMySql()
    {
        return $this->sql;
    }

    protected function getCache()
    {
        if (empty($this->cache)) {
            $this->cache = new HierarchicalSimpleCache(
                $this->getMySql(),
                get_class($this),
                true
            );
        }
        return $this->cache;
    }
}
