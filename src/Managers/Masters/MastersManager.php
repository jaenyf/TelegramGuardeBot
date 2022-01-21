<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Managers\Masters;

use TelegramGuardeBot\Managers\CsvMembersManager;

class MastersManager extends CsvMembersManager
{
    public const GlobalListFileName = 'Masters.lst';
    public const GlobalListLockFileName = 'Masters.lst.lock';

    private static MastersManager $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new MastersManager();
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
