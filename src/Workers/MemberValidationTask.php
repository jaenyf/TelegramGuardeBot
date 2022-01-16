<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Workers;

use TelegramGuardeBot\Workers\BackgroundProcessInstanciator;
use TelegramGuardeBot\Workers\SchedulerTask;
use TelegramGuardeBot\Managers\NewMembersValidationManager;

abstract class MemberValidationTask extends SchedulerTask
{
    protected NewMembersValidationManager $manager;
    protected int $chatId;
    protected int $userId;

    public function __construct(BackgroundProcessInstanciator $instanciator, int $chatId, int $userId)
    {
        parent::__construct($instanciator);
        $this->manager = new NewMembersValidationManager();
        $this->chatId = $chatId;
        $this->userId = $userId;
    }
}
