<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TelegramGuardeBot\DependenciesInitialization;

final class DependenciesInitializationTest extends TestCase
{
    private $container;
    protected function setUp(): void
    {
        $this->container = DependenciesInitialization::InitializeContainer('app.config.ci', 'dev');
    }

    public function testGetLogger(): void
    {
        $logger = $this->container->get('logger');
        $this->assertInstanceOf("Monolog\Logger", $logger);
    }

    public function testGetNewMembersValidationManager(): void
    {
        $manager = $this->container->get('newMembersValidationManager');
        $this->assertInstanceOf("TelegramGuardeBot\Managers\NewMembersValidationManager", $manager);
    }
}
