<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Managers\Spams;

use TelegramGuardeBot\Managers\CsvMembersManager;

class SpammersManager extends CsvMembersManager
{
    public const GlobalListFileName = 'Spammers.lst';
    public const GlobalListLockFileName = 'Spammers.lst.lock';

    private static SpammersManager $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new SpammersManager();
        }
        return self::$instance;
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
