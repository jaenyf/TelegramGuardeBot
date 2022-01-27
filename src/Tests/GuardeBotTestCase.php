<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Tests;

require_once 'src/Requires.php';

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use DI\Container;
use DI\ContainerBuilder;
use TelegramGuardeBot\App;
use TelegramGuardeBot\AppConfig;
use TelegramGuardeBot\GuardeBot;

class GuardeBotTestCase extends TestCase
{
    protected const appConfigFileName = "app.config.ci";

    private static $isAppInitialized;

    protected function setUp() : void
    {
        if(in_array("noSetUp", $this->getGroups()))
        {
            return;
        }

        if(!isset(self::$isAppInitialized))
        {
            App::initialize(self::appConfigFileName, $this->InitializeContainer());
            self::$isAppInitialized = true;
        }
    }

    public function InitializeContainer() : Container
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            'logger' => $this->createMock('Psr\Log\LoggerInterface'),
            'bot' => (function(ContainerInterface $c){
                return new GuardeBot($c->get('telegramApi'), $c->get('appConfig')->locale);
            }),
            'telegramApi' => $this->createMock('TelegramGuardeBot\TelegramApi'),
            'appConfig' => new AppConfig(self::appConfigFileName)
        ]);

        return $builder->build();
    }
}
