<?php

namespace Mikamatto\Scheduler\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Mikamatto\Scheduler\Repository\SchedulerTaskRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SchedulerTaskRepository::class)]
class SchedulerTask
{
    // Statuses
    public const STATUS_RUNNING = 'OK';
    public const STATUS_RETRYING = 'RETRYING';
    public const STATUS_FAILED = 'FAILED';
    public const STATUS_RESUMED = 'RESUMED';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    #[Assert\NotBlank]
    private ?string $command = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\Positive]
    private int $cycle = 5;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column(length: 32)]
    private ?string $status = self::STATUS_RUNNING;


    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ts_lastRun = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ts_lastSuccess = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ts_nextRun = null;

    #[ORM\Column(nullable: false)]
    private int $reattemptsCount = 0;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(string $command): static
    {
        $this->command = $command;

        return $this;
    }

    public function getCycle(): ?int
    {
        return $this->cycle;
    }

    public function setCycle(int $cycle): static
    {
        $this->cycle = $cycle;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getTsLastRun(): ?\DateTimeInterface
    {
        return $this->ts_lastRun;
    }

    public function setTsLastRun(?\DateTimeInterface $ts_lastRun): static
    {
        $this->ts_lastRun = $ts_lastRun;

        return $this;
    }

    public function getTsLastSuccess(): ?\DateTimeInterface
    {
        return $this->ts_lastSuccess;
    }

    public function setTsLastSuccess(?\DateTimeInterface $ts_lastSuccess): static
    {
        $this->ts_lastSuccess = $ts_lastSuccess;

        return $this;
    }

    public function getTsNextRun(): ?\DateTimeInterface
    {
        return $this->ts_nextRun;
    }

    public function setTsNextRun(?\DateTimeInterface $ts_nextRun): static
    {
        $this->ts_nextRun = $ts_nextRun;

        return $this;
    }

    public function getReattemptsCount(): int
    {
        return $this->reattemptsCount;
    }

    public function setReattemptsCount(int $count): static
    {
        $this->reattemptsCount = $count;

        return $this;
    }

    public function incrementReattemptsCount(): static
    {
        $this->reattemptsCount++;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     *  Used to bring back a task that has failed to the running state, so that it will be fetched again by the repository.
     */
    public function resetFromFailed(): static
    {
        $this->status = self::STATUS_RUNNING;
        $this->ts_nextRun = new \DateTime();
        $this->reattemptsCount = 0;

        return $this;
    }

    public function __toString(): string
    {
        return $this->command;
    }
}

