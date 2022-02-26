<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Workers;

use TelegramGuardeBot\App;
use TelegramGuardeBot\Workers\MemberValidationTask;

class MemberValidationEjectionTask extends MemberValidationTask
{
    private int $validationMessageId;

    public function __construct(int $chatId, int $userId, int $validationMessageId)
    {
        parent::__construct(
            App::getInstance()->getScheduler(),
            App::getInstance()->getDIContainer()->get('newMembersValidationManager'),
            $chatId,
            $userId
        );
        $this->validationMessageId = $validationMessageId;
    }

    public function do()
    {
        if(!App::isInitialized())
        {
            App::initialize();
        }

        if ($this->manager->has($this->chatId, $this->userId)) {
            //Approval task has not removed pending user, it's time to eject it
            $this->manager->remove($this->chatId, $this->userId);
            try
            {
                App::getInstance()->getBot()->deleteMessage($this->chatId, $this->validationMessageId);
            }
            catch(\Throwable $e)
            {

            }
            App::getInstance()->getBot()->ejectMember($this->chatId, $this->userId);
        }
    }
}
