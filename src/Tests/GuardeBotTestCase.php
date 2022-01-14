<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Tests;

require_once 'src/Requires.php';

use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\directoryExists;

class GuardeBotTestCase extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        if(directoryExists('logs'))
        {
            if(file_exists('logs/GuardeBot.log'))
            {
                unlink('logs/GuardeBot.log');
            }
            $removeLogsFolder = (count(scandir('logs')) <= 2);
            if($removeLogsFolder)
            {
                rmdir('logs');
            }
        }
    }
}
