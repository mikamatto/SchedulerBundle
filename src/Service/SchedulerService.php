<?php

namespace Mikamatto\Scheduler\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mikamatto\Scheduler\Entity\SchedulerTask;

class SchedulerService
{
    public function __construct(private EntityManagerInterface $em)
    {

    }

    public function setTask(SchedulerTask $task): void
    {
        // Fix the next run timestamp to the next day if the time has already passed
        $task->setTsNextRun($this->getNextRun($task)); 

        $this->em->persist($task);
        $this->em->flush();
    }

    public function resumeTask(SchedulerTask $task): void
    {
        // Reschedule immediately, but leave the Scheduler to clear the status using its finer logic  
        $task->setStatus(SchedulerTask::STATUS_RESUMED);
        $task->setTsNextRun(new \DateTime()); 
        $task->setReattemptsCount(0);
        $task->setActive(true);
        $this->em->flush();
    }
   


    /**
     * Merge the start time from the form's input with today's date (otherwise it will be set to 1970-01-01)
     * OR use tomorrow's date if the time is already past today
     *
     * @param Task $task
     * @return \DateTime
     */
    private function getNextRun(SchedulerTask $task): \DateTime
    {
        $ts_nextRun = $task->getTsNextRun();
        $today = new \DateTime();
        $today->setTime($ts_nextRun->format('H'), $ts_nextRun->format('i'));
        if ($today < new \DateTime()) {
            $today->modify('+1 day');
        }
        return $today;
    }
}

