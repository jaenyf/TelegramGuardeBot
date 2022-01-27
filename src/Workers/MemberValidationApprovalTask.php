<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Workers;

use TelegramGuardeBot\App;
use TelegramGuardeBot\Workers\MemberValidationTask;

class MemberValidationApprovalTask extends MemberValidationTask
{
    public function __construct(int $chatId, int $userId)
    {
        parent::__construct(
            App::getInstance()->getScheduler(),
            App::getInstance()->getDIContainer()->get('newMembersValidationManager'),
            $chatId,
            $userId
        );
    }

    public function do()
    {
        if(!App::isInitialized())
        {
            App::initialize();
        }

        $this->manager->remove($this->chatId, $this->userId);
        App::getInstance()->getBot()->unmuteMember($this->chatId, $this->userId);
    }
}
