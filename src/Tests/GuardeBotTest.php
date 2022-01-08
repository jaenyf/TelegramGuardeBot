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

    public function testIsHookedUseApi(): void
    {
        //Arrange
        $telegramApiStub = $this->createMock(TelegramApi::class);

        $telegramApiStub->expects($this->once())->method('getWebhookInfo');

        //Act / Assert
        $sut = new GuardeBot($telegramApiStub, GuardeBotTest::hookTestName, GuardeBotTest::logChatTestId);
        $sut->isHooked();
    }

    public function testHookUseApi(): void
    {
        //Arrange
        $url = 'http://web.ho.ok';
        $certificate = 'cert';
        $dropPendingUpdates = true;
        $telegramApiStub = $this->createStub(TelegramApi::class);

        $webHookInfo = new \stdClass();
        $webHookInfo->url = '';
        $telegramApiStub->method('getWebhookInfo')
             ->willReturn($webHookInfo);

        $telegramApiStub->expects($this->once())
            ->method('setWebhook')
            ->with($this->equalTo($url, $certificate, null, null, null, $dropPendingUpdates));

        //Act / Assert
        $sut = new GuardeBot($telegramApiStub, GuardeBotTest::hookTestName, GuardeBotTest::logChatTestId);
        $sut->hook($url, $certificate, $dropPendingUpdates);
    }

    public function testSayUseApi(): void
    {
        //Arrange
        $telegramApiStub = $this->createMock(TelegramApi::class);

        $telegramApiStub->expects($this->once())
            ->method('sendMessage')
            ->with($this->equalTo(['chat_id' => GuardeBotTest::logChatTestId, 'text' => 'hello world !']));

        //Act / Assert
        $sut = new GuardeBot($telegramApiStub, GuardeBotTest::hookTestName, GuardeBotTest::logChatTestId);
        $sut->say(GuardeBotTest::logChatTestId, 'hello world !');
    }

    public function testUnhookUseApi(): void
    {
        //Arrange
        $dropPendingUpdates = true;
        $telegramApiStub = $this->createStub(TelegramApi::class);

        // Configure getWebhookInfo
        $webHookInfo = new \stdClass();
        $webHookInfo->url = 'http://web.ho.ok';
        $telegramApiStub->method('getWebhookInfo')
             ->willReturn($webHookInfo);

        $telegramApiStub->expects($this->once())
            ->method('deleteWebhook')
            ->with($this->equalTo($dropPendingUpdates));

        //Act / Assert
        $sut = new GuardeBot($telegramApiStub, GuardeBotTest::hookTestName, GuardeBotTest::logChatTestId);
        $sut->unHook($dropPendingUpdates);
    }
}
