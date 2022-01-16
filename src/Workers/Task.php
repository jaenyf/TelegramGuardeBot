<?php

namespace TelegramGuardeBot\Workers;

use TelegramGuardeBot\Workers\BackgroundProcessWorker;
use TelegramGuardeBot\Workers\BackgroundProcessInstanciator;

abstract class Task extends BackgroundProcessWorker
{
    private int $nextRunTime;
    private int $lastRunTime;

    public function __construct(?BackgroundProcessInstanciator $instanciator = null)
    {
        parent::__construct($instanciator);
        $this->withSingleRun(true);
        $this->setLastRunTime(0);
        $this->setNextRunTime(time());
    }

    public function getLastRunTime(): int
    {
        return $this->lastRunTime;
    }

    public function setLastRunTime(int $value)
    {
        $this->lastRunTime = $value;
    }

    public function getNextRunTime(): int
    {
        return $this->nextRunTime;
    }

    public function setNextRunTime(int $value)
    {
        $this->nextRunTime = $value;
    }
}
