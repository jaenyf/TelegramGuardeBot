<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Managers\Masters;

use TelegramGuardeBot\Managers\CsvMembersManager;

class MastersManager extends CsvMembersManager
{
    public const GlobalListFileName = 'Masters.lst';
    public const GlobalListLockFileName = 'Masters.lst.lock';

    protected static function createInstance(): CsvMembersManager
    {
        return new MastersManager();
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
