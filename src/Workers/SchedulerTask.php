<?php

namespace TelegramGuardeBot\Workers;

use TelegramGuardeBot\App;
use TelegramGuardeBot\Workers\Task;
use TelegramGuardeBot\Workers\BackgroundProcessInstanciator;

/**
 * A task set to be run by the Scheduler, has a BackgroundProcessInstanciator to deal with persistence
 */
abstract class SchedulerTask extends Task
{
    public function __construct(BackgroundProcessInstanciator $instanciator)
    {
        parent::__construct($instanciator);
    }

    public function setUp() : void
    {
        App::initialize();
    }

    public function tearDown() : void
    {
        App::getInstance()->getScheduler()->removeTaskById($this->getUid());
    }

}
