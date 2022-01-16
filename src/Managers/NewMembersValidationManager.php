<?php

namespace TelegramGuardeBot\Managers;

use TelegramGuardeBot\Managers\CsvManager;

/**
 * Manage the pending new members validation
 */
class NewMembersValidationManager extends CsvManager
{
    private const CsvFileName = 'NewMembersValidation.lst';
    private const CsvLockFileName = 'NewMembersValidation.lst.lock';
    private const Headers = ['CHAT_ID', 'USER_ID'];

    public function __construct()
    {
        parent::__construct(NewMembersValidationManager::Headers);
    }

    protected function getFilename(): string
    {
        return self::CsvFileName;
    }

    protected function useLocking(): bool
    {
        return true;
    }

    protected function getLockFilename(): string
    {
        return NewMembersValidationManager::CsvLockFileName;
    }

    public function has(int $chatId, int $userId)
    {
        return $this->hasFields([$chatId, $userId]);
    }

    public function add(int $chatId, int $userId)
    {
        $this->addFields([$chatId, $userId]);
    }

    public function remove(int $chatId, int $userId)
    {
        $this->removeFields([$chatId, $userId]);
    }
}
