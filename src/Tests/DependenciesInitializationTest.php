<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TelegramGuardeBot\DependenciesInitialization;

final class DependenciesInitializationTest extends TestCase
{
    private $container;
    protected function setUp(): void
    {
        $this->container = DependenciesInitialization::InitializeContainer();
    }

    public function testGetLoggerInstanceRetrieveMonologLogger(): void
    {
        $logger = $this->container->get('logger');
        $this->assertInstanceOf("Monolog\Logger", $logger);
    }
}
