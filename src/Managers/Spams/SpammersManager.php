<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Managers\Spams;

use TelegramGuardeBot\Managers\CsvMembersManager;

class SpammersManager extends CsvMembersManager
{
    public const GlobalListFileName = 'Spammers.lst';
    public const GlobalListLockFileName = 'Spammers.lst.lock';

    protected static function createInstance(): CsvMembersManager
    {
        return new SpammersManager();
    }

    protected function getFilename(): string
    {
        return self::GlobalListFileName;
    }

    protected function getLockFilename(): string
    {
        return self::GlobalListLockFileName;
    }
}
