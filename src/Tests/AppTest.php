<?php

declare(strict_types=1);

use TelegramGuardeBot\Tests\GuardeBotTestCase;
use TelegramGuardeBot\App;

final class AppTest extends GuardeBotTestCase
{
    private static function initializeAndGetTestInstance() : App
    {
        if(!App::isInitialized())
        {
            App::initialize(self::appConfigFileName);
        }

        return App::getInstance();
    }

    public static function tearDownAfterClass(): void
    {
        App::dispose();
    }

    /**
     * @group noSetUp
     */
    public function testInitializeDoesNotThrow(): void
    {
        //Act / Assert
        $this->assertThat(App::initialize(self::appConfigFileName), $this->isNull());
    }

    /**
     * @group noSetUp
     */
    public function testGetInstanceReturnsAnInstance(): void
    {
        //Act
        $sut = App::getInstance();

        //Assert
        $this->assertThat($sut, $this->logicalNot($this->isNull()));
    }

    /**
     * @group noSetUp
     */
    public function testGetBotReturnsAnInstance(): void
    {
        //Act
        $sut = static::initializeAndGetTestInstance();

        //Assert
        $this->assertThat($sut->getBot(), $this->logicalNot($this->isNull()));
    }
}
