<?php

declare(strict_types=1);

use TelegramGuardeBot\App;
use TelegramGuardeBot\Tests\GuardeBotTestCase;
use TelegramGuardeBot\Workers\MemberValidationEjectionTask;


class MemberValidationEjectionTaskTest extends GuardeBotTestCase
{
    /**
     * @testWith [1,2,3]
     *           [3,4,5]
     */
    public function testDoCheckHasNemMemberFromManager(int $chatId, int $userId) {
        //Arrange
        $sut = new MemberValidationEjectionTask($chatId, $userId, 0);

        App::getInstance()->getDIContainer()->get('newMembersValidationManager')
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo($chatId, $userId));

        //Act / Assert
        $sut->do();
    }

    /**
     * @testWith [1,2,3]
     *           [3,4,5]
     */
    public function testDoRemoveNemMemberFromManager(int $chatId, int $userId) {
        //Arrange
        $sut = new MemberValidationEjectionTask($chatId, $userId, 0);

        $managerMock = App::getInstance()->getDIContainer()->get('newMembersValidationManager');
        $managerMock->method('has')->with($chatId, $userId)->willReturn(true);

        $managerMock
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo($chatId, $userId));

        //Act / Assert
        $sut->do();
    }

    /**
     * @testWith [1,2]
     *           [3,4]
     */
    public function testDoCallBotEjectMember(int $chatId, int $userId) {
        //Arrange
        $sut = new MemberValidationEjectionTask($chatId, $userId, 0);

        $managerMock = App::getInstance()->getDIContainer()->get('newMembersValidationManager');
        $managerMock->method('has')->with($chatId, $userId)->willReturn(true);

        App::getInstance()->getDIContainer()->get('bot')
            ->expects($this->once())
            ->method('ejectMember')
            ->with($this->equalTo($chatId, $userId));

        //Act / Assert
        $sut->do();
    }

    /**
     * @testWith [1,2]
     *           [3,4]
     */
    public function testDoCallBotDeleteMessage(int $chatId, int $validationMessageId) {
        //Arrange
        $sut = new MemberValidationEjectionTask($chatId,0, $validationMessageId);

        $managerMock = App::getInstance()->getDIContainer()->get('newMembersValidationManager');
        $managerMock->method('has')->with($chatId, 0)->willReturn(true);

        App::getInstance()->getDIContainer()->get('bot')
            ->expects($this->once())
            ->method('deleteMessage')
            ->with($this->equalTo($chatId, $validationMessageId));

        //Act / Assert
        $sut->do();
    }
}
