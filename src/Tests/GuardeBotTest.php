<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TelegramGuardeBot\GuardeBot;
use TelegramGuardeBot\TelegramApi;

final class GuardeBotTest extends TestCase
{
    private const logChatTestId = 123456;
    private const hookTestName = 'test';

    public function testCannotBeCreatedFromNullHookName(): void
    {
        //Arrange
        $this->expectException(Exception::class);

        $telegramApiStub = $this->createStub(TelegramApi::class);

        //Act / Assert
        new GuardeBot($telegramApiStub, (string)null, GuardeBotTest::logChatTestId);
    }

    public function testCannotBeCreatedFromEmptyHookName(): void
    {
        //Arrange
        $this->expectException(Exception::class);

        $telegramApiStub = $this->createStub(TelegramApi::class);

        //Act / Assert
        new GuardeBot($telegramApiStub, '', GuardeBotTest::logChatTestId);
    }
}
