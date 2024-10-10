<?php

namespace Mikamatto\Scheduler\Command;

use Doctrine\ORM\EntityManagerInterface;
use Mikamatto\Scheduler\Entity\SchedulerTask;
use Mikamatto\Scheduler\Repository\SchedulerTaskRepository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'task-manager',
    description: 'Manages the scheduling and execution of automated tasks.',
)]
class Scheduler extends Command
{
    private SymfonyStyle $io;
    private FilesystemAdapter $cache;

    public function __construct(
        private SchedulerTaskRepository $taskRepository, 
        private EntityManagerInterface $em
        )
    {
        $this->cache = new FilesystemAdapter();
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Manages and executes cron jobs.')
            ->setHelp('This command needs to be ran via cron every minute and it will take care of the entire scheduling and execution of cron jobs.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->writeln('Executing runnable tasks...');

        $tasks = $this->taskRepository->fetchTasksToRun();
        $this->io->writeln('Found ' . count($tasks) . ' tasks to run.');


        foreach ($tasks as $task) {
            $commandName = $task->getCommand();
            try {
                $command = $this->getApplication()->find($commandName);

                $arguments = [
                    'command' => $commandName,
                ];

                $commandInput = new ArrayInput($arguments);
                $commandInput->setInteractive(false);

                $returnCode = $command->run($commandInput, $output);
                $this->manageOutcome($task, $returnCode);

            } catch (\Exception $e) {
                $this->io->error(sprintf('The command "%s" encountered an error: %s', $commandName, $e->getMessage()));
                $this->manageOutcome($task, Command::FAILURE); 
            }
        }
        $this->em->flush();

        // Save the last successful execution time to the cache
        $lastExecTime = $this->cache->getItem('scheduler_last_execution_time');
        $lastExecTime->set(new \DateTime());
        $this->cache->save($lastExecTime);

        $this->io->writeln('Complete.');
        return Command::SUCCESS;
    }

    private function manageOutcome(SchedulerTask $task, $returnCode): void
    {
        $now = new \DateTime();

        if ($returnCode === Command::SUCCESS) {
            $this->io->success(sprintf('The command "%s" was executed successfully.', $task->getCommand()));

            $nextRun = null;
            if ($task->getStatus() === SchedulerTask::STATUS_RUNNING && $task->getReattemptsCount() === 0) {
                // Task was running normally, just schedule its next run based on the current time and the task's cycle
                $nextRun = (clone $now)->modify('+' . $task->getCycle() . ' minutes');
            }
            else {
                // Task was retrying, so we have to reset it and also make sure we don't offset its original execution cycle.
    
                // Realign to the normal cycle using the last successful timestamp (if that was null, use the last run timestamp and we're actually potentially offsetting the cycle)
                /** @var \DateTime  */
                $baseTime = $task->getTsLastSuccess() ?? $task->getTsLastRun() ?? new \DateTime();
                do {
                    $nextRun = $baseTime->modify('+' . $task->getCycle() . ' minutes');
                } while ($nextRun <= $now); // Loop until we get a valid future time
    
                // Reset reattempts count and status
                $task->setReattemptsCount(0);
                $task->setStatus(SchedulerTask::STATUS_RUNNING);

            }
            $task->setTsLastSuccess($now);
            $task->setTsLastRun($now);
            $task->setTsNextRun($nextRun);
        } else {
            // If 3 attempts have been made, mark the task as failed
            if ($task->getReattemptsCount() === 3) {
                $this->io->error(sprintf('The command "%s" failed and will not be executed again.', $task->getCommand()));
                $task->setStatus(SchedulerTask::STATUS_FAILED); // Will stop executing because the repository fetches only non-failed tasks
            } else {
                $this->io->error(sprintf('The command "%s" failed.', $task->getCommand()));
                // Retrying mode
                $task->setStatus(SchedulerTask::STATUS_RETRYING);

                // Exponential backoff for retries
                $nextRun = (clone $now)->modify('+' . pow(10, $task->getReattemptsCount()) . ' minutes');
                $task->setTsNextRun($nextRun); // Set the next run time for retry
                $task->incrementReattemptsCount();
            }
        }
    }
}
