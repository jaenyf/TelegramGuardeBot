<?php

declare(strict_types=1);

use TelegramGuardeBot\Tests\GuardeBotTestCase;
use TelegramGuardeBot\AppConfig;

final class AppConfigTest extends GuardeBotTestCase
{
    private static function createSut() : AppConfig
    {
        return new AppConfig(self::appConfigFileName);
    }

    /**
     * @group noSetUp
     */
    public function testenvNameIsParsed(): void
    {
        //Act
        $sut = static::createSut();

        //Assert
        $this->assertThat($sut->envName, $this->equalTo("CI"));
    }

    /**
     * @group noSetUp
     */
    public function testBotTokenIsParsed(): void
    {
        //Act
        $sut = static::createSut();

        //Assert
        $this->assertThat($sut->botToken, $this->equalTo("1234567890:ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"));
    }

    /**
     * @group noSetUp
     */
    public function testLogChatIdIsParsed(): void
    {
        //Act
        $sut = static::createSut();

        //Assert
        $this->assertThat($sut->logChatId, $this->equalTo(-123456789));
    }

    /**
     * @group noSetUp
     */
    public function testEnableNewMemberValidationIsParsed(): void
    {
        //Act
        $sut = static::createSut();

        //Assert
        $this->assertThat($sut->enableNewMemberValidation, $this->equalTo(true));
    }

    /**
     * @group noSetUp
     */
    public function testLocaleIsParsed(): void
    {
        //Act
        $sut = static::createSut();

        //Assert
        $this->assertThat($sut->locale, $this->equalTo("EN"));
    }

    /**
     * @group noSetUp
     */
    public function testEnableApiLoggingIsParsed(): void
    {
        //Act
        $sut = static::createSut();

        //Assert
        $this->assertThat($sut->enableApiLogging, $this->equalTo(true));
    }
}
