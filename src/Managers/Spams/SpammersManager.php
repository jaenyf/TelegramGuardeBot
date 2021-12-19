<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Managers\Spams;

use TelegramGuardeBot\Managers\CsvMembersManager;

class SpammersManager extends CsvMembersManager
{
    private const GlobalListFileName = 'Spammers.lst';

    protected static function createInstance(): CsvMembersManager
    {
        return new SpammersManager();
    }

    protected function getFilename(): string
    {
        return self::GlobalListFileName;
    }
}
