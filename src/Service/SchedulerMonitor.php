<?php

namespace Mikamatto\Scheduler\Service;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class SchedulerMonitor
{
    private FilesystemAdapter $cache;

    public function __construct()
    {
        $this->cache = new FilesystemAdapter();
    }

    public function getLastExecutionTime(): ?\DateTime
    {
        return $this->cache->get('scheduler_last_execution_time', function () {
            // Return null if there's no cache hit
            return null;
        });
    }
}