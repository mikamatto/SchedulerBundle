<?php

namespace Mikamatto\Scheduler\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mikamatto\Scheduler\Entity\SchedulerTask;

/**
 * @extends ServiceEntityRepository<SchedulerTask>
 */
class SchedulerTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SchedulerTask::class);
    }

    /**
     * Fetches tasks that are ready to run.
     *
     * @return array The tasks to run as command names
     */
    public function fetchTasksToRun(): array
    {
        $qb = $this->createQueryBuilder('t');

        $qb->andWhere('t.active = true')
            ->andWhere('t.ts_nextRun <= :now')
            ->andWhere('t.status != :failed')
            ->setParameter('now', new \DateTime())
            ->setParameter('failed', SchedulerTask::STATUS_FAILED)
            ->orderBy('t.command', 'ASC');

        return $qb->getQuery()
            ->getResult();
    }
}
