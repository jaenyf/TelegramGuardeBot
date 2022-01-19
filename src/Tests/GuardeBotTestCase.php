<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Tests;

require_once 'src/Requires.php';

use PHPUnit\Framework\TestCase;
use DI\Container;
use DI\ContainerBuilder;
use TelegramGuardeBot\App;

class GuardeBotTestCase extends TestCase
{
    protected const appConfigFileName = "src/Tests/AppTest.config";

    private static $isAppInitialized;
    private static $logStub;

    protected function setUp() : void
    {
        if(in_array("noSetUp", $this->getGroups()))
        {
            return;
        }

        if(!isset(self::$isAppInitialized))
        {
            self::$logStub = $this->createMock('Psr\Log\LoggerInterface');
            App::initialize(self::appConfigFileName, $this->InitializeContainer());
            self::$isAppInitialized = true;
        }
    }


    public function InitializeContainer() : Container
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            'logger' => self::$logStub
        ]);

        return $builder->build();
    }
}
