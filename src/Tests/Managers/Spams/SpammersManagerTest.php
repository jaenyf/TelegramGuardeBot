<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Tests\Spams\Managers;

use TelegramGuardeBot\Tests\Managers\CsvMembersManagerTest;
use TelegramGuardeBot\Managers\CsvMembersManager;
use TelegramGuardeBot\Managers\Spams\SpammersManager;

class SpammersManagerTest extends CsvMembersManagerTest
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function getSut() : CsvMembersManager
    {
        return SpammersManager::getInstance();
    }

    protected function getTestFileName(): string
    {
        return SpammersManager::GlobalListFileName;
    }

    protected function getFileNamesToClean() : array
    {
        return [SpammersManager::GlobalListFileName, SpammersManager::GlobalListLockFileName];
    }

}
