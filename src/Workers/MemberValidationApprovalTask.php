<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Workers;

use TelegramGuardeBot\App;
use TelegramGuardeBot\Workers\MemberValidationTask;

class MemberValidationApprovalTask extends MemberValidationTask
{
    public function __construct(int $chatId, int $userId)
    {
        parent::__construct(App::getInstance()->getScheduler(), $chatId, $userId);
    }

    public function do()
    {
        App::initialize();
        $this->manager->remove($this->chatId, $this->userId);
        App::getInstance()->getBot()->approveMember($this->chatId, $this->userId);
    }
}
