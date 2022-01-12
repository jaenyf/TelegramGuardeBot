<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Tests;

use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\directoryExists;

class GuardeBotTestCase extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        if(directoryExists('logs')){
            if(file_exists('logs/GuardeBot.log')){
                unlink('logs/GuardeBot.log');
            }
            $removeLogsFolder = (count(scandir('logs')) <= 2);
            if($removeLogsFolder){
                rmdir('logs');
            }
        }

        parent::tearDownAfterClass();
    }
}
