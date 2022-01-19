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

    /**
     * @group noSetUp
     */
    public function testGetLoggerReturnsAnInstance(): void
    {
        //Act
        $sut = static::initializeAndGetTestInstance();

        //Assert
        $this->assertThat($sut->getLogger(), $this->logicalNot($this->isNull()));
    }

    /**
     * @group noSetUp
     */
    public function testBotTokenIsParsed(): void
    {
        //Act
        $sut = static::initializeAndGetTestInstance();

        //Assert
        $this->assertThat($sut->botToken, $this->equalTo("testBotToken123456"));
    }

    /**
     * @group noSetUp
     */
    public function testLogChatIdIsParsed(): void
    {
        //Act
        $sut = static::initializeAndGetTestInstance();

        //Assert
        $this->assertThat($sut->logChatId, $this->equalTo(-123456789));
    }

    /**
     * @group noSetUp
     */
    public function testLocaleIsParsed(): void
    {
        //Act
        $sut = static::initializeAndGetTestInstance();

        //Assert
        $this->assertThat($sut->locale, $this->equalTo("EN"));
    }

    /**
     * @group noSetUp
     */
    public function testEnableApiLoggingIsParsed(): void
    {
        //Act
        $sut = static::initializeAndGetTestInstance();

        //Assert
        $this->assertThat($sut->enableApiLogging, $this->equalTo(false));
    }
}