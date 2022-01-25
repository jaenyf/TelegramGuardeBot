<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Tests;

require_once 'src/Requires.php';

use PHPUnit\Framework\TestCase;
use DI\Container;
use DI\ContainerBuilder;

use TelegramGuardeBot\App;
use TelegramGuardeBot\AppConfig;

class GuardeBotTestCase extends TestCase
{
    protected const appConfigFileName = "app.config.ci";


    public static function setupBeforeClass() : void
    {
        //Required for loading rbx files
        ini_set('memory_limit', '-1');
    }

    protected function setUp() : void
    {
        if(in_array("noSetUp", $this->getGroups()))
        {
            return;
        }

        if(!App::isInitialized())
        {
            App::initialize(self::appConfigFileName, $this->InitializeContainerWithMocks());
        }
    }

    protected function tearDown(): void
    {
        App::dispose();
    }

    protected function InitializeContainerWithMocks() : Container
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            'logger' => $this->createMock(\Psr\Log\LoggerInterface::class),
            'bot' => $this->createMock(\TelegramGuardeBot\GuardeBot::class),
            'telegramApi' => $this->createMock(\TelegramGuardeBot\TelegramApi::class),
            'appConfig' => new AppConfig(self::appConfigFileName),
            'scheduler' => $this->createMock(\TelegramGuardeBot\Workers\Scheduler::class),
            'newMembersValidationManager' => $this->createMock(\TelegramGuardeBot\Managers\NewMembersValidationManager::class)
        ]);

        return $builder->build();
    }
}
